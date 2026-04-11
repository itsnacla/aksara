<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Portal\PortalController;

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
});
