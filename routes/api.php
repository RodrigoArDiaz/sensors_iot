<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SensorController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Rutas para sensores de temperatura y humedad
Route::prefix('sensors')->group(function () {
    // POST /api/sensors - Recibir datos del sensor (PROTEGIDO con API key)
    Route::post('/', [SensorController::class, 'store'])->middleware('api.key');
    
    // GET /api/sensors - Obtener últimas lecturas (SIN protección)
    Route::get('/', [SensorController::class, 'index']);
    
    // POST /api/sensors/latest - Obtener la última medición (SIN protección)
    Route::post('/latest', [SensorController::class, 'getLatest']);
    
    // POST /api/sensors/chart-data - Obtener datos para gráficos (SIN protección)
    Route::post('/chart-data', [SensorController::class, 'getChartData']);
    
    // POST /api/sensors/12hour-chart-data - Obtener datos de gráficos de 12 horas (SIN protección)
    Route::post('/12hour-chart-data', [SensorController::class, 'get12HourChartData']);
});

// Ruta de prueba (sin protección)
Route::get('/test', function () {
    return response()->json([
        'message' => 'API funcionando correctamente',
        'timestamp' => now()->format('Y-m-d H:i:s')
    ]);
}); 