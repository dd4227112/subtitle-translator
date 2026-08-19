<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\TranslatorController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
});

Route::middleware('auth')->post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/', [TranslatorController::class, 'index']);
Route::get('/ai-request', [TranslatorController::class, 'aiRequest']);
Route::get('/ai-document', [TranslatorController::class, 'showTranslateForm']);
Route::post('/ai-translate', [TranslatorController::class, 'translate']);
Route::post('/download-translation', [TranslatorController::class, 'downloadTranslation']);

// Ollama AI routes
Route::get('/ai-document-ollama', [TranslatorController::class, 'showTranslateFormOllama']);
Route::get('/ai-request-ollama', [TranslatorController::class, 'aiRequestOllama']);
Route::post('/ai-translate-ollama', [TranslatorController::class, 'translateOllama']);
