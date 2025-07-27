<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SensorTemperatureHumidity;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class SensorController extends Controller
{
    /**
     * Show the sensors dashboard view
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        // Obtener la última medición para mostrar inicialmente
        $latestSensor = SensorTemperatureHumidity::orderBy('created_at', 'desc')->first();
        
        // Convertir la fecha a zona horaria de Tucumán si existe el registro
        if ($latestSensor) {
            $latestSensor->formatted_date_tucuman = Carbon::parse($latestSensor->created_at)
                ->setTimezone('America/Argentina/Tucuman')
                ->format('d/m/Y H:i:s');
        }
        
        return view('sensors.dashboard', compact('latestSensor'));
    }

    /**
     * Get the latest sensor reading (for AJAX requests)
     *
     * @return JsonResponse
     */
    public function getLatest(): JsonResponse
    {
        try {
            $latestSensor = SensorTemperatureHumidity::orderBy('created_at', 'desc')->first();
            
            if (!$latestSensor) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay datos disponibles'
                ], 404);
            }
            
            // Convertir fecha a zona horaria de Tucumán
            $tucumanTime = Carbon::parse($latestSensor->created_at)
                ->setTimezone('America/Argentina/Tucuman');
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $latestSensor->id,
                    'temperature' => $latestSensor->temperature,
                    'humidity' => $latestSensor->humidity,
                    'created_at' => $latestSensor->created_at->format('Y-m-d H:i:s'), // UTC
                    'formatted_date' => $tucumanTime->format('d/m/Y H:i:s'), // Tucumán
                    'timezone' => 'America/Argentina/Tucuman'
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store sensor data (temperature and humidity)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validar los datos recibidos
            $validated = $request->validate([
                'temperature' => 'required|string|max:10',
                'humidity' => 'required|string|max:10',
            ]);

            // Crear el registro en la base de datos
            $sensor = SensorTemperatureHumidity::create([
                'temperature' => $validated['temperature'],
                'humidity' => $validated['humidity'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Datos del sensor guardados correctamente',
                'data' => [
                    'id' => $sensor->id,
                    'temperature' => $sensor->temperature,
                    'humidity' => $sensor->humidity,
                    'created_at' => $sensor->created_at->format('Y-m-d H:i:s')
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get latest sensor readings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            
            $sensors = SensorTemperatureHumidity::orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($sensor) {
                    return [
                        'id' => $sensor->id,
                        'temperature' => $sensor->temperature,
                        'humidity' => $sensor->humidity,
                        'created_at' => $sensor->created_at->format('Y-m-d H:i:s')
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $sensors
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get chart data for temperature and humidity graphs
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getChartData(Request $request): JsonResponse
    {
        try {
            // Obtener los últimos 20 registros para el gráfico
            $limit = $request->get('limit', 20);
            
            $sensors = SensorTemperatureHumidity::orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->reverse() // Revertir para mostrar cronológicamente
                ->values(); // Reindexar
            
            $labels = [];
            $temperatures = [];
            $humidities = [];
            
            foreach ($sensors as $sensor) {
                // Convertir fecha a zona horaria de Tucumán
                $tucumanTime = Carbon::parse($sensor->created_at)
                    ->setTimezone('America/Argentina/Tucuman');
                
                $labels[] = $tucumanTime->format('H:i:s');
                $temperatures[] = (float) $sensor->temperature;
                $humidities[] = (float) $sensor->humidity;
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'temperature' => $temperatures,
                    'humidity' => $humidities,
                    'timezone' => 'America/Argentina/Tucuman'
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos del gráfico',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get 12-hour chart data grouped by intervals
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function get12HourChartData(Request $request): JsonResponse
    {
        try {
            // Obtener mediciones de las últimas 12 horas
            $twelveHoursAgo = Carbon::now()->subHours(12);
            
            $sensors = SensorTemperatureHumidity::where('created_at', '>=', $twelveHoursAgo)
                ->orderBy('created_at', 'asc')
                ->get();
            
            if ($sensors->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay datos disponibles para las últimas 12 horas'
                ], 404);
            }
            
            // Agrupar por intervalos de 10 minutos
            $intervalMinutes = 10;
            $groupedData = [];
            
            foreach ($sensors as $sensor) {
                $tucumanTime = Carbon::parse($sensor->created_at)
                    ->setTimezone('America/Argentina/Tucuman');
                
                // Agrupar por intervalos de 10 minutos
                $intervalStart = $tucumanTime->copy()
                    ->minute(intval($tucumanTime->minute / $intervalMinutes) * $intervalMinutes)
                    ->second(0);
                
                $key = $intervalStart->format('Y-m-d H:i:s');
                
                if (!isset($groupedData[$key])) {
                    $groupedData[$key] = [
                        'timestamp' => $intervalStart,
                        'temperatures' => [],
                        'humidities' => []
                    ];
                }
                
                $groupedData[$key]['temperatures'][] = (float) $sensor->temperature;
                $groupedData[$key]['humidities'][] = (float) $sensor->humidity;
            }
            
            $labels = [];
            $temperatures = [];
            $humidities = [];
            
            foreach ($groupedData as $data) {
                $labels[] = $data['timestamp']->format('H:i');
                $temperatures[] = round(array_sum($data['temperatures']) / count($data['temperatures']), 1);
                $humidities[] = round(array_sum($data['humidities']) / count($data['humidities']), 1);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'temperature' => $temperatures,
                    'humidity' => $humidities,
                    'timezone' => 'America/Argentina/Tucuman',
                    'interval_minutes' => $intervalMinutes,
                    'total_points' => count($labels)
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos de 12 horas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
