<?php

use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect('/admin'));

// QR Attendance Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/attendance/scan', [AttendanceController::class, 'scanPage'])->name('attendance.scan');
    Route::post('/attendance/scan', [AttendanceController::class, 'processQr'])->name('attendance.process');
    Route::get('/students/{student}/qr-card', [AttendanceController::class, 'showCard'])->name('student.qr-card');
});