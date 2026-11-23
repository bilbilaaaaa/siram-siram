<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\KoneksiController;
use App\Http\Controllers\KontrolController;
use App\Http\Controllers\StatistikController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/kontrol', [KontrolController::class, 'index'])->name('kontrol');

Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal');

Route::get('/statistik', [StatistikController::class, 'index'])->name('statistik');

Route::get('/koneksi', [KoneksiController::class, 'index'])->name('koneksi');
