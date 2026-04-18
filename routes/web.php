<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Portal\PortalController;
use App\Http\Controllers\ChatbotController;

// Root redirect to Admin (Makes the app start with login)
Route::get('/', function () {
    return redirect('/admin');
});

// Alias login for middleware compatibility
Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

// Portal (Students/Parents)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [PortalController::class, 'index'])->name('dashboard');
    Route::post('/logout', [PortalController::class, 'logout'])->name('logout');

    // AI Chatbot
    Route::get('/chatbot/config', [ChatbotController::class, 'config'])->name('chatbot.config');
    Route::post('/chatbot/chat', [ChatbotController::class, 'chat'])->name('chatbot.chat');
});
