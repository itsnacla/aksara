<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Portal\PortalController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [PortalController::class, 'index'])->name('dashboard');
    Route::post('/logout', [PortalController::class, 'logout'])->name('logout');
});
