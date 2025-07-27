@php
    $updateInterval = 60000; // 10 segundos
@endphp

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
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
        
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 1rem;
        }
        
        .chart-container canvas {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            padding: 10px;
        }
        
        @media (max-width: 992px) {
            .chart-container {
                height: 250px;
            }
        }
        
        @media (max-width: 768px) {
            .chart-container {
                height: 200px;
                margin-bottom: 2rem;
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
                    Monitoreo en tiempo real de temperatura y humedad
                </h1>
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

        <!-- Gráficos -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="sensor-card p-4">
                    <h3 class="text-center mb-4">
                        <i class="bi bi-graph-up"></i>
                        Histórico de Mediciones
                    </h3>
                    <div class="row">
                        <!-- Gráfico de Temperatura -->
                        <div class="col-lg-6 mb-4">
                            <div class="chart-container">
                                <h5 class="text-center text-danger mb-3">
                                    <i class="bi bi-thermometer-half"></i>
                                    Temperatura (°C) -Ultimas 20 mediciones
                                </h5>
                                <canvas id="temperatureChart" width="400" height="300"></canvas>
                            </div>
                        </div>
                        
                        <!-- Gráfico de Humedad -->
                        <div class="col-lg-6 mb-4">
                            <div class="chart-container">
                                <h5 class="text-center text-info mb-3">
                                    <i class="bi bi-droplet-half"></i>
                                    Humedad (%) -Ultimas 20 mediciones
                                </h5>
                                <canvas id="humidityChart" width="400" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            {{-- <i class="bi bi-clock"></i> --}}
                            {{--  --}}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos de 12 Horas -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="sensor-card p-4">
                    <h3 class="text-center mb-4">
                        <i class="bi bi-clock-history"></i>
                        Histórico de las Últimas 12 Horas
                    </h3>
                    <div class="row">
                        <!-- Gráfico de Temperatura 12 Horas -->
                        <div class="col-lg-6 mb-4">
                            <div class="chart-container">
                                <h5 class="text-center text-danger mb-3">
                                    <i class="bi bi-thermometer-half"></i>
                                    Temperatura (°C) - 12 Horas (promedio cada 10 min)
                                </h5>
                                <canvas id="temperature12HChart" width="400" height="300"></canvas>
                            </div>
                        </div>
                        
                        <!-- Gráfico de Humedad 12 Horas -->
                        <div class="col-lg-6 mb-4">
                            <div class="chart-container">
                                <h5 class="text-center text-info mb-3">
                                    <i class="bi bi-droplet-half"></i>
                                    Humedad (%) - 12 Horas (promedio cada 10 min)
                                </h5>
                                <canvas id="humidity12HChart" width="400" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i>
                            Los datos se agrupan en intervalos de 10 minutos para una mejor visualización
                        </small>
                    </div>
                </div>
            </div>
        </div>


        <!-- Info adicional -->
        <div class="row my-4">
            <div class="col-12">
                <div class="sensor-card p-4">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <i class="bi bi-arrow-clockwise text-primary" style="font-size: 2rem;"></i>
                            <h5 class="mt-2">Actualización automática</h5>
                            <p class="text-muted">Cada {{ $updateInterval / 1000 }} segundos</p>
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
        const UPDATE_INTERVAL = {{ $updateInterval }}; // 10 segundos
        const API_ENDPOINT = '/api/sensors/latest';
        const CHART_API_ENDPOINT = '/api/sensors/chart-data';
        const CHART_12H_API_ENDPOINT = '/api/sensors/12hour-chart-data';
        
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
        
        // Variables para los gráficos
        let temperatureChart = null;
        let humidityChart = null;
        let temperature12HChart = null;
        let humidity12HChart = null;
        
        // Configuración de los gráficos
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: false,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            },
            animation: {
                duration: 750
            }
        };
        
        // Función para inicializar los gráficos
        function initializeCharts() {
            // Gráfico de Temperatura
            const tempCtx = document.getElementById('temperatureChart').getContext('2d');
            temperatureChart = new Chart(tempCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Temperatura (°C)',
                        data: [],
                        borderColor: '#ff6b6b',
                        backgroundColor: 'rgba(255, 107, 107, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#ff6b6b',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5
                    }]
                },
                options: chartOptions
            });
            
            // Gráfico de Humedad
            const humCtx = document.getElementById('humidityChart').getContext('2d');
            humidityChart = new Chart(humCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Humedad (%)',
                        data: [],
                        borderColor: '#4ecdc4',
                        backgroundColor: 'rgba(78, 205, 196, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#4ecdc4',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5
                    }]
                },
                options: chartOptions
            });
            
            // Gráfico de Temperatura 12 Horas
            const temp12HCtx = document.getElementById('temperature12HChart').getContext('2d');
            temperature12HChart = new Chart(temp12HCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Temperatura (°C)',
                        data: [],
                        borderColor: '#ff6b6b',
                        backgroundColor: 'rgba(255, 107, 107, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointBackgroundColor: '#ff6b6b',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1,
                        pointRadius: 3
                    }]
                },
                options: chartOptions
            });
            
            // Gráfico de Humedad 12 Horas
            const hum12HCtx = document.getElementById('humidity12HChart').getContext('2d');
            humidity12HChart = new Chart(hum12HCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Humedad (%)',
                        data: [],
                        borderColor: '#4ecdc4',
                        backgroundColor: 'rgba(78, 205, 196, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointBackgroundColor: '#4ecdc4',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1,
                        pointRadius: 3
                    }]
                },
                options: chartOptions
            });
        }
        
        // Función para obtener datos de los gráficos
        async function fetchChartData() {
            try {
                const response = await fetch(CHART_API_ENDPOINT, {
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
                    // Actualizar gráfico de temperatura
                    temperatureChart.data.labels = data.data.labels;
                    temperatureChart.data.datasets[0].data = data.data.temperature;
                    temperatureChart.update('none');
                    
                    // Actualizar gráfico de humedad
                    humidityChart.data.labels = data.data.labels;
                    humidityChart.data.datasets[0].data = data.data.humidity;
                    humidityChart.update('none');
                    
                    console.log('Gráficos actualizados:', data.data);
                }
                
            } catch (error) {
                console.error('Error al obtener datos de gráficos:', error);
            }
        }
        
        // Función para obtener datos de los gráficos de 12 horas
        async function fetch12HourChartData() {
            try {
                const response = await fetch(CHART_12H_API_ENDPOINT, {
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
                    // Actualizar gráfico de temperatura 12 horas
                    temperature12HChart.data.labels = data.data.labels;
                    temperature12HChart.data.datasets[0].data = data.data.temperature;
                    temperature12HChart.update('none');
                    
                    // Actualizar gráfico de humedad 12 horas
                    humidity12HChart.data.labels = data.data.labels;
                    humidity12HChart.data.datasets[0].data = data.data.humidity;
                    humidity12HChart.update('none');
                    
                    console.log('Gráficos de 12 horas actualizados:', data.data);
                } else {
                    console.warn('No hay datos de 12 horas disponibles:', data.message);
                }
                
            } catch (error) {
                console.error('Error al obtener datos de gráficos de 12 horas:', error);
            }
        }
        
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
        
        // Función para actualizar todos los datos (sensores + gráficos)
        async function updateAllData() {
            await Promise.all([
                fetchLatestSensorData(),
                fetchChartData(),
                fetch12HourChartData()
            ]);
        }
        
        // Función para inicializar la actualización automática
        function startAutoUpdate() {
            // Inicializar gráficos
            initializeCharts();
            
            // Obtener datos inmediatamente
            updateAllData();
            
            // Configurar actualización cada 10 segundos
            setInterval(updateAllData, UPDATE_INTERVAL);
            
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