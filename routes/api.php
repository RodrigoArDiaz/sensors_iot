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
    // POST /api/sensors - Recibir datos del sensor
    Route::post('/', [SensorController::class, 'store']);
    
    // GET /api/sensors - Obtener últimas lecturas
    Route::get('/', [SensorController::class, 'index']);
    
    // POST /api/sensors/latest - Obtener la última medición (para peticiones dinámicas)
    Route::post('/latest', [SensorController::class, 'getLatest']);
});

// Ruta de prueba
Route::get('/test', function () {
    return response()->json([
        'message' => 'API funcionando correctamente',
        'timestamp' => now()->format('Y-m-d H:i:s')
    ]);
}); 