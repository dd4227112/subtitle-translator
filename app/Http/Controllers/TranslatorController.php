<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Media\Document;

class TranslatorController extends Controller
{
    /**
     * Display the welcome page.
     */
    public function index()
    {
        return view('welcome');
    }

    /**
     * Handle AI request for data extraction from SMS content.
     */
    public function aiRequest()
    {
        $content = 'TZS 100.00 Credited to ac XX009038 REF:R90iiei253390005 REF:INC016225NQR1253390505 EPHRAIM SOLOMON SWILLA  EcobankPay Payment to ABDULKARIM OMAR SHUNULA  on 05-DEC-25. Your Available Balance is TZS 1,512.00. Keep your Password & PIN confidential.';
        
        $text = 'You are a data extraction assistant. Extract the following fields from the given SMS content:

1. account → the value that appears immediately after the word "ac" (example: from "ac XX009038" extract "XX009038")
2. reference → the value after the FIRST occurrence of "REF:" (example: from "REF:R90iiei253390005" extract "R90iiei253390005")
3. student → the full name that appears after "Payment to" and before "on" (example: from "Payment to ABDULKARIM OMAR SHUNULA on" extract "ABDULKARIM OMAR SHUNULA")
4. date → the date that appears after the word "on" (example: from "on 05-DEC-25" extract "05-DEC-25")
5. amount → the amount from the beginning of the message (example: from "TZS 100.00 Credited" extract "100.00")
6. currency → the currency code (example: from "TZS 100.00" extract "TZS")

Return the result ONLY as JSON with this format:

{
  "account": "",
  "reference": "",
  "student": "",
  "date": "",
  "amount": "",
  "currency": ""
}

Here is the SMS content:
' . $content;

        $response = Prism::text()
            ->using(Provider::Gemini, 'gemini-2.5-flash')
            ->withPrompt($text)
            ->asText();

        return response($response->text);
    }

    /**
     * Show the document translation form.
     */
    public function showTranslateForm()
    {
        return view('translate-form');
    }

    /**
     * Show the Ollama translation form.
     */
    public function showTranslateFormOllama()
    {
        return view('translate-form-ollama');
    }

    /**
     * Process file translation.
     */
    public function translate(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:srt,txt|max:2048',
            'target_language' => 'required|string|max:50'
        ]);

        try {
            $file = $request->file('file');
            $content = file_get_contents($file->getPathname());
            $targetLanguage = $request->input('target_language');

            // Step 1: Split file into subtitle blocks (separated by double newlines)
            $blocks = preg_split("/\R{2,}/", trim($content));

            $chunks = [];
            $currentChunk = '';
            $maxLength = 1000;

            foreach ($blocks as $block) {
                // +2 accounts for "\n\n" separator we'll add when joining
                if (strlen($currentChunk) + strlen($block) + 2 > $maxLength) {
                    $chunks[] = trim($currentChunk);
                    $currentChunk = $block;
                } else {
                    $currentChunk .= ($currentChunk ? "\n\n" : '') . $block;
                }
            }

            // Add the last chunk
            if ($currentChunk !== '') {
                $chunks[] = trim($currentChunk);
            }

            $translatedChunks = [];
            foreach ($chunks as $batch) {
                $response = Prism::text()
                    ->using(Provider::Gemini, 'gemini-2.5-flash')
                    ->withPrompt(
                        "Translate the following subtitle blocks to {$targetLanguage}. IMPORTANT: Keep the exact same format including timestamps, line numbers, and structure. Only translate the actual subtitle text, not the timestamps or numbers:",
                        [
                            Document::fromRawContent(
                                rawContent: $batch,
                                mimeType: 'text/plain'
                            )
                        ]
                    )
                    ->asText();

                $translatedChunks[] = trim($response->text);

                // Small delay to avoid rate limiting
                usleep(100000); // 0.1 second delay
            }

            $fullTranslation = implode("\n\n", $translatedChunks);

            return response()->json([
                'success' => true,
                'translated_text' => $fullTranslation,
                'original_filename' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Translation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download the translated file.
     */
    public function downloadTranslation(Request $request)
    {
        $request->validate([
            'translated_text' => 'required|string',
            'filename' => 'required|string|max:255'
        ]);

        $translatedText = $request->input('translated_text');
        $filename = $request->input('filename', 'translated_subtitles');

        // Clean filename and ensure .srt extension
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
        $filename = $filename . '_translated.srt';

        return response($translatedText)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Handle AI request for data extraction using Ollama.
     */
    public function aiRequestOllama()
    {
        $content = 'TZS 100.00 Credited to ac XX009038 REF:R90iiei253390005 REF:INC016225NQR1253390505 EPHRAIM SOLOMON SWILLA  EcobankPay Payment to ABDULKARIM OMAR SHUNULA  on 05-DEC-25. Your Available Balance is TZS 1,512.00. Keep your Password & PIN confidential.';
        
        $text = 'You are a data extraction assistant. Extract the following fields from the given SMS content:

1. account → the value that appears immediately after the word "ac" (example: from "ac XX009038" extract "XX009038")
2. reference → the value after the FIRST occurrence of "REF:" (example: from "REF:R90iiei253390005" extract "R90iiei253390005")
3. student → the full name that appears after "Payment to" and before "on" (example: from "Payment to ABDULKARIM OMAR SHUNULA on" extract "ABDULKARIM OMAR SHUNULA")
4. date → the date that appears after the word "on" (example: from "on 05-DEC-25" extract "05-DEC-25")
5. amount → the amount from the beginning of the message (example: from "TZS 100.00 Credited" extract "100.00")
6. currency → the currency code (example: from "TZS 100.00" extract "TZS")

Return the result ONLY as JSON with this format:

{
  "account": "",
  "reference": "",
  "student": "",
  "date": "",
  "amount": "",
  "currency": ""
}

Here is the SMS content:
' . $content;

        try {
            $response = Http::timeout(600)
                ->connectTimeout(30)
                ->post('http://localhost:11434/api/chat', [
                    'model' => 'llama3',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $text
                        ]
                    ],
                    'stream' => false
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $aiResponse = $data['message']['content'] ?? '';
                return response($aiResponse);
            }

            return response('Failed to get response from Ollama', 500);
        } catch (\Exception $e) {
            return response('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Process file translation using Ollama.
     */
    public function translateOllama(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:srt,txt|max:2048',
            'target_language' => 'required|string|max:50'
        ]);

        try {
            $file = $request->file('file');
            $content = file_get_contents($file->getPathname());
            $targetLanguage = $request->input('target_language');

            // Step 1: Split file into subtitle blocks (separated by double newlines)
            $blocks = preg_split("/\R{2,}/", trim($content));

            $chunks = [];
            $currentChunk = '';
            $maxLength = 1000;

            foreach ($blocks as $block) {
                // +2 accounts for "\n\n" separator we'll add when joining
                if (strlen($currentChunk) + strlen($block) + 2 > $maxLength) {
                    $chunks[] = trim($currentChunk);
                    $currentChunk = $block;
                } else {
                    $currentChunk .= ($currentChunk ? "\n\n" : '') . $block;
                }
            }

            // Add the last chunk
            if ($currentChunk !== '') {
                $chunks[] = trim($currentChunk);
            }

            $translatedChunks = [];
            foreach ($chunks as $batch) {
                $prompt = "Translate the following subtitle blocks to {$targetLanguage}. IMPORTANT: Keep the exact same format including timestamps, line numbers, and structure. Only translate the actual subtitle text, not the timestamps or numbers:\n\n{$batch}";
                
                $response = Http::timeout(600)
                    ->connectTimeout(30)
                    ->post('http://localhost:11434/api/chat', [
                        'model' => 'gemma3',
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => $prompt
                            ]
                        ],
                        'stream' => false
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $translatedText = $data['message']['content'] ?? '';
                    $translatedChunks[] = trim($translatedText);
                } else {
                    throw new \Exception('Ollama API request failed');
                }

                // Small delay to avoid overloading
                usleep(100000); // 0.1 second delay
            }

            $fullTranslation = implode("\n\n", $translatedChunks);

            return response()->json([
                'success' => true,
                'translated_text' => $fullTranslation,
                'original_filename' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Translation failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
