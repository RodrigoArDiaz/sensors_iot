<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Obtener la API key de la configuración
        $validApiKey = config('app.sensor_api_key');
        
        if (!$validApiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key no configurada en el servidor'
            ], 500);
        }

        // Obtener la API key del request (puede venir en header o query parameter)
        $apiKey = $request->header('X-API-Key') ?? $request->get('api_key');

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key requerida. Envíe la API key en el header X-API-Key o como parámetro api_key'
            ], 401);
        }

        if ($apiKey !== $validApiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key inválida'
            ], 401);
        }

        return $next($request);
    }
} 