<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Sensores IoT</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sensor-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .sensor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .sensor-value {
            font-size: 3rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .temperature-card {
            border-left: 5px solid #ff6b6b;
        }
        
        .humidity-card {
            border-left: 5px solid #4ecdc4;
        }
        
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        
        .status-online {
            background-color: #28a745;
            animation: pulse 2s infinite;
        }
        
        .status-offline {
            background-color: #dc3545;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .last-update {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .sensor-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .temperature-icon {
            color: #ff6b6b;
        }
        
        .humidity-icon {
            color: #4ecdc4;
        }
        
        @media (max-width: 768px) {
            .sensor-value {
                font-size: 2.5rem;
            }
            
            .sensor-icon {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container my-2">
        <!-- Header -->
        <div class="row mb-3">
            <div class="col-12 text-center">
                <h1 class="text-white mb-3">
                    <i class="bi bi-cpu"></i>
                    Dashboard de Sensores IoT
                </h1>
                <p class="text-white-50 lead">
                    Monitoreo en tiempo real de temperatura y humedad
                </p>
                <div class="mt-3">
                    <span class="status-indicator status-online" id="connectionStatus"></span>
                    <span class="text-white" id="connectionText">Conectado</span>
                </div>
            </div>
        </div>

        <!-- Sensor Cards -->
        <div class="row g-4">
            <!-- Temperatura -->
            <div class="col-md-6 col-lg-6">
                <div class="sensor-card temperature-card p-4 h-100">
                    <div class="text-center">
                        <i class="bi bi-thermometer-half sensor-icon temperature-icon"></i>
                        <h3 class="mb-3">Temperatura</h3>
                        <div class="sensor-value text-danger" id="temperature">
                            @if($latestSensor)
                                {{ $latestSensor->temperature }}°C
                            @else
                                --°C
                            @endif
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="bi bi-clock"></i>
                                Última actualización
                            </small>
                            <div class="last-update" id="temperatureTime">
                                @if($latestSensor)
                                    {{ $latestSensor->formatted_date_tucuman }}
                                    <br><small class="text-muted">(Tucumán, Argentina)</small>
                                @else
                                    Sin datos
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Humedad -->
            <div class="col-md-6 col-lg-6">
                <div class="sensor-card humidity-card p-4 h-100">
                    <div class="text-center">
                        <i class="bi bi-droplet-half sensor-icon humidity-icon"></i>
                        <h3 class="mb-3">Humedad</h3>
                        <div class="sensor-value text-info" id="humidity">
                            @if($latestSensor)
                                {{ $latestSensor->humidity }}%
                            @else
                                --%
                            @endif
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="bi bi-clock"></i>
                                Última actualización
                            </small>
                            <div class="last-update" id="humidityTime">
                                @if($latestSensor)
                                    {{ $latestSensor->formatted_date_tucuman }}
                                    <br><small class="text-muted">(Tucumán, Argentina)</small>
                                @else
                                    Sin datos
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info adicional -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="sensor-card p-4">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <i class="bi bi-arrow-clockwise text-primary" style="font-size: 2rem;"></i>
                            <h5 class="mt-2">Actualización automática</h5>
                            <p class="text-muted">Cada 10 segundos</p>
                        </div>
                        <div class="col-md-4">
                            <i class="bi bi-wifi text-success" style="font-size: 2rem;"></i>
                            <h5 class="mt-2">Conectividad</h5>
                            <p class="text-muted">Tiempo real</p>
                        </div>
                        <div class="col-md-4">
                            <i class="bi bi-shield-check text-info" style="font-size: 2rem;"></i>
                            <h5 class="mt-2">Estado</h5>
                            <p class="text-muted" id="systemStatus">Operacional</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript para actualización automática -->
    <script>
        // Configuración
        const UPDATE_INTERVAL = 10000; // 10 segundos
        const API_ENDPOINT = '/api/sensors/latest';
        
        // Elementos del DOM
        const temperatureElement = document.getElementById('temperature');
        const humidityElement = document.getElementById('humidity');
        const temperatureTimeElement = document.getElementById('temperatureTime');
        const humidityTimeElement = document.getElementById('humidityTime');
        const connectionStatusElement = document.getElementById('connectionStatus');
        const connectionTextElement = document.getElementById('connectionText');
        const systemStatusElement = document.getElementById('systemStatus');
        
        // Token CSRF para las peticiones POST
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Función para actualizar el estado de conexión
        function updateConnectionStatus(isOnline) {
            if (isOnline) {
                connectionStatusElement.className = 'status-indicator status-online';
                connectionTextElement.textContent = 'Conectado';
                systemStatusElement.textContent = 'Operacional';
            } else {
                connectionStatusElement.className = 'status-indicator status-offline';
                connectionTextElement.textContent = 'Desconectado';
                systemStatusElement.textContent = 'Error de conexión';
            }
        }
        
        // Función para obtener los últimos datos del sensor
        async function fetchLatestSensorData() {
            try {
                const response = await fetch(API_ENDPOINT, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success && data.data) {
                    // Actualizar temperatura
                    temperatureElement.textContent = `${data.data.temperature}°C`;
                    temperatureTimeElement.innerHTML = `${data.data.formatted_date}<br><small class="text-muted">(Tucumán, Argentina)</small>`;
                    
                    // Actualizar humedad
                    humidityElement.textContent = `${data.data.humidity}%`;
                    humidityTimeElement.innerHTML = `${data.data.formatted_date}<br><small class="text-muted">(Tucumán, Argentina)</small>`;
                    
                    // Actualizar estado de conexión
                    updateConnectionStatus(true);
                    
                    console.log('Datos actualizados:', data.data);
                } else {
                    console.warn('No hay datos disponibles:', data.message);
                    updateConnectionStatus(false);
                }
                
            } catch (error) {
                console.error('Error al obtener datos del sensor:', error);
                updateConnectionStatus(false);
            }
        }
        
        // Función para inicializar la actualización automática
        function startAutoUpdate() {
            // Obtener datos inmediatamente
            fetchLatestSensorData();
            
            // Configurar actualización cada 10 segundos
            setInterval(fetchLatestSensorData, UPDATE_INTERVAL);
            
            console.log(`Actualización automática iniciada cada ${UPDATE_INTERVAL / 1000} segundos`);
        }
        
        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            startAutoUpdate();
        });
        
        // Manejar errores de red
        window.addEventListener('online', function() {
            console.log('Conexión restaurada');
            updateConnectionStatus(true);
        });
        
        window.addEventListener('offline', function() {
            console.log('Conexión perdida');
            updateConnectionStatus(false);
        });
    </script>
</body>
</html> 