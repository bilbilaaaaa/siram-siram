<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JadwalController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::view('/kontrol', 'pages.kontrol', ['modeAktif' => 'manual'])
    ->name('kontrol');

Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal');
