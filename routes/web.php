<?php

use Illuminate\Support\Facades\Route;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Media\Document;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/ai-request', function () {
    $response = Prism::text()
        ->using(Provider::Gemini, 'gemini-2.5-flash')
        ->withPrompt('Tell me a short story about a brave knight.')
        ->asText();

    return response($response->text);
});

Route::get('/ai-document', function () {
    return view('translate-form');
});

Route::post('/ai-translate', function (Illuminate\Http\Request $request) {
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
            // +2 accounts for "\n\n" separator we’ll add when joining
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
});

Route::post('/download-translation', function (Illuminate\Http\Request $request) {
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
});
