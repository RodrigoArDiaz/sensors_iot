<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SensorController;

Route::get('/', function () {
    return view('welcome');
});

// Ruta para mostrar el dashboard de sensores
Route::get('/dashboard', [SensorController::class, 'dashboard'])->name('sensor.dashboard');
