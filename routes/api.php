<?php

use App\Http\Controllers\Api\SinkronPerangkatController;
use App\Http\Middleware\VerifikasiTokenPerangkat;
use Illuminate\Support\Facades\Route;

Route::post('/perangkat/sinkron', SinkronPerangkatController::class)
    ->middleware([VerifikasiTokenPerangkat::class, 'throttle:60,1'])
    ->name('api.perangkat.sinkron');
