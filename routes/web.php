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

    // Student Cards
    Route::get('/student-card/{student}', [\App\Http\Controllers\StudentCardController::class, 'print'])->name('student.card');
    Route::get('/student-cards/bulk', [\App\Http\Controllers\StudentCardController::class, 'bulkPrint'])->name('student.cards.bulk');
    Route::get('/student-cards/rombel/{studyGroupId}', [\App\Http\Controllers\StudentCardController::class, 'printByStudyGroup'])->name('student.cards.rombel');
    
    // Reports
    Route::get('/reports/attendance', [\App\Http\Controllers\ReportController::class, 'attendance'])->name('reports.attendance');
    Route::get('/reports/schedule', [\App\Http\Controllers\ReportController::class, 'schedule'])->name('reports.schedule');

    // Standalone QR Scan
    Route::get('/scan-presensi', \App\Livewire\QrScanStandalone::class)->name('scan-presensi');
});
