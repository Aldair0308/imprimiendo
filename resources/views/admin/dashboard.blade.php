@extends('layouts.app')

@section('title', 'Panel Administrativo - Imprimeindo')
@section('description', 'Dashboard principal para la gestión del sistema de impresión.')

@push('styles')
<style>
    .admin-header {
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-dark) 100%);
    }
    
    .stat-card {
        transition: all var(--transition-normal);
        background: var(--bg-white);
        border: 1px solid var(--border-light);
    }
    
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
        border-color: var(--primary-blue);
    }
    
    .stat-icon {
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-light) 100%);
    }
    
    .chart-container {
        background: var(--bg-white);
        border: 1px solid var(--border-light);
        transition: all var(--transition-normal);
    }
    
    .chart-container:hover {
        box-shadow: var(--shadow-md);
    }
    
    .printer-status-online {
        background: var(--status-success);
    }
    
    .printer-status-busy {
        background: var(--status-warning);
    }
    
    .printer-status-offline {
        background: var(--status-error);
    }
    
    .printer-status-maintenance {
        background: var(--status-info);
    }
    
    .activity-item {
        transition: all var(--transition-normal);
        border-left: 3px solid transparent;
    }
    
    .activity-item:hover {
        background-color: var(--bg-gray-light);
        border-left-color: var(--primary-blue);
    }
    
    .progress-bar {
        background: var(--bg-gray-light);
        overflow: hidden;
    }
    
    .progress-fill {
        background: linear-gradient(90deg, var(--primary-blue) 0%, var(--primary-light) 100%);
        transition: width var(--transition-normal);
    }
    
    .alert-card {
        border-left: 4px solid var(--status-warning);
        background: var(--bg-warning-light);
    }
    
    .quick-action-btn {
        transition: all var(--transition-normal);
        background: var(--bg-white);
        border: 2px solid var(--border-light);
    }
    
    .quick-action-btn:hover {
        border-color: var(--primary-blue);
        background: var(--bg-blue-light);
        transform: translateY(-2px);
    }
    
    .refresh-btn {
        animation: none;
        transition: all var(--transition-normal);
    }
    
    .refresh-btn.spinning {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .notification-badge {
        background: var(--status-error);
        animation: pulse 2s infinite;
    }
    
    .real-time-indicator {
        animation: pulse 2s infinite;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-secondary">
    <!-- Admin Header -->
    <div class="admin-header text-white p-6 shadow-lg">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="mb-4 md:mb-0">
                    <h1 class="text-3xl font-bold mb-2">
                        🎛️ Panel Administrativo
                    </h1>
                    <p class="text-blue-100">
                        Bienvenido, <span class="font-semibold">{{ $admin->name ?? 'Administrador' }}</span>
                        • Último acceso: {{ $admin->last_login ?? 'Primer acceso' }}
                    </p>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Real-time indicator -->
                    <div class="flex items-center space-x-2">
                        <div class="real-time-indicator w-3 h-3 bg-green-400 rounded-full"></div>
                        <span class="text-sm text-blue-100">En tiempo real</span>
                    </div>
                    
                    <!-- Notifications -->
                    <button onclick="showNotifications()" 
                            class="relative p-2 bg-white bg-opacity-20 rounded-lg hover:bg-opacity-30 transition-all">
                        <span class="text-xl">🔔</span>
                        <span class="notification-badge absolute -top-1 -right-1 w-5 h-5 text-xs text-white rounded-full flex items-center justify-center">
                            {{ $notifications_count ?? 3 }}
                        </span>
                    </button>
                    
                    <!-- Refresh -->
                    <button onclick="refreshDashboard()" 
                            id="refresh-btn"
                            class="refresh-btn p-2 bg-white bg-opacity-20 rounded-lg hover:bg-opacity-30">
                        <span class="text-xl">🔄</span>
                    </button>
                    
                    <!-- Logout -->
                    <button onclick="logout()" 
                            class="p-2 bg-red-500 bg-opacity-80 rounded-lg hover:bg-opacity-100 transition-all">
                        <span class="text-xl">🚪</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto p-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Active Printers -->
            <div class="stat-card rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-secondary mb-1">Impresoras Activas</p>
                        <p class="text-3xl font-bold text-primary" id="active-printers">
                            {{ $stats['active_printers'] ?? 8 }}
                        </p>
                        <p class="text-xs text-green-600 mt-1">
                            ↗️ +2 desde ayer
                        </p>
                    </div>
                    <div class="stat-icon w-12 h-12 rounded-lg flex items-center justify-center">
                        <span class="text-white text-xl">🖨️</span>
                    </div>
                </div>
            </div>

            <!-- Print Jobs Today -->
            <div class="stat-card rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-secondary mb-1">Trabajos Hoy</p>
                        <p class="text-3xl font-bold text-primary" id="jobs-today">
                            {{ $stats['jobs_today'] ?? 156 }}
                        </p>
                        <p class="text-xs text-green-600 mt-1">
                            ↗️ +23% vs ayer
                        </p>
                    </div>
                    <div class="stat-icon w-12 h-12 rounded-lg flex items-center justify-center">
                        <span class="text-white text-xl">📄</span>
                    </div>
                </div>
            </div>

            <!-- Revenue Today -->
            <div class="stat-card rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-secondary mb-1">Ingresos Hoy</p>
                        <p class="text-3xl font-bold text-primary" id="revenue-today">
                            ${{ number_format($stats['revenue_today'] ?? 1247.50, 2) }}
                        </p>
                        <p class="text-xs text-green-600 mt-1">
                            ↗️ +18% vs ayer
                        </p>
                    </div>
                    <div class="stat-icon w-12 h-12 rounded-lg flex items-center justify-center">
                        <span class="text-white text-xl">💰</span>
                    </div>
                </div>
            </div>

            <!-- Active Sessions -->
            <div class="stat-card rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-secondary mb-1">Sesiones Activas</p>
                        <p class="text-3xl font-bold text-primary" id="active-sessions">
                            {{ $stats['active_sessions'] ?? 24 }}
                        </p>
                        <p class="text-xs text-blue-600 mt-1">
                            📱 12 móviles, 12 web
                        </p>
                    </div>
                    <div class="stat-icon w-12 h-12 rounded-lg flex items-center justify-center">
                        <span class="text-white text-xl">👥</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Printer Status Overview -->
                <div class="chart-container rounded-xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-primary">
                            🖨️ Estado de Impresoras
                        </h2>
                        <button onclick="managePrinters()" 
                                class="text-primary-blue hover:text-primary-dark text-sm">
                            Ver todas →
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($printers ?? [
                            ['id' => 1, 'name' => 'HP LaserJet Pro', 'status' => 'online', 'queue' => 3, 'location' => 'Planta Baja'],
                            ['id' => 2, 'name' => 'EPSON L3250', 'status' => 'busy', 'queue' => 7, 'location' => 'Primer Piso'],
                            ['id' => 3, 'name' => 'Canon PIXMA', 'status' => 'online', 'queue' => 1, 'location' => 'Segundo Piso'],
                            ['id' => 4, 'name' => 'Brother DCP', 'status' => 'maintenance', 'queue' => 0, 'location' => 'Planta Baja']
                        ] as $printer)
                        <div class="p-4 border border-gray-200 rounded-lg hover:shadow-md transition-all">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-medium text-primary">{{ $printer['name'] }}</h3>
                                <div class="flex items-center space-x-2">
                                    <div class="printer-status-{{ $printer['status'] }} w-3 h-3 rounded-full"></div>
                                    <span class="text-xs text-secondary capitalize">{{ $printer['status'] }}</span>
                                </div>
                            </div>
                            
                            <div class="space-y-2 text-sm text-secondary">
                                <div class="flex justify-between">
                                    <span>📍 {{ $printer['location'] }}</span>
                                    <span>Cola: {{ $printer['queue'] }}</span>
                                </div>
                                
                                @if($printer['status'] === 'busy')
                                <div class="progress-bar h-2 rounded-full">
                                    <div class="progress-fill h-full rounded-full" style="width: 65%"></div>
                                </div>
                                <div class="text-xs text-blue-600">Imprimiendo... 65% completado</div>
                                @endif
                            </div>
                            
                            <div class="mt-3 flex space-x-2">
                                <button onclick="viewPrinter({{ $printer['id'] }})" 
                                        class="text-xs bg-blue-50 text-blue-600 px-2 py-1 rounded hover:bg-blue-100">
                                    Ver
                                </button>
                                @if($printer['status'] !== 'maintenance')
                                <button onclick="pausePrinter({{ $printer['id'] }})" 
                                        class="text-xs bg-yellow-50 text-yellow-600 px-2 py-1 rounded hover:bg-yellow-100">
                                    Pausar
                                </button>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="chart-container rounded-xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-primary">
                            📊 Actividad Reciente
                        </h2>
                        <button onclick="viewAllActivity()" 
                                class="text-primary-blue hover:text-primary-dark text-sm">
                            Ver todo →
                        </button>
                    </div>

                    <div class="space-y-4">
                        @foreach($recent_activity ?? [
                            ['type' => 'print', 'message' => 'Trabajo de impresión completado', 'printer' => 'HP LaserJet Pro', 'time' => '2 min', 'user' => 'Usuario #1234'],
                            ['type' => 'error', 'message' => 'Error de papel en impresora', 'printer' => 'EPSON L3250', 'time' => '5 min', 'user' => null],
                            ['type' => 'maintenance', 'message' => 'Mantenimiento programado iniciado', 'printer' => 'Brother DCP', 'time' => '15 min', 'user' => 'Admin'],
                            ['type' => 'print', 'message' => 'Nuevo trabajo en cola', 'printer' => 'Canon PIXMA', 'time' => '18 min', 'user' => 'Usuario #5678'],
                            ['type' => 'system', 'message' => 'Sistema actualizado correctamente', 'printer' => null, 'time' => '1 hora', 'user' => 'Sistema']
                        ] as $activity)
                        <div class="activity-item p-4 rounded-lg">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 mt-1">
                                    @switch($activity['type'])
                                        @case('print')
                                            <span class="text-green-500">✅</span>
                                            @break
                                        @case('error')
                                            <span class="text-red-500">⚠️</span>
                                            @break
                                        @case('maintenance')
                                            <span class="text-blue-500">🔧</span>
                                            @break
                                        @case('system')
                                            <span class="text-purple-500">⚙️</span>
                                            @break
                                        @default
                                            <span class="text-gray-500">📄</span>
                                    @endswitch
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-primary">{{ $activity['message'] }}</p>
                                    <div class="flex items-center space-x-4 mt-1 text-xs text-secondary">
                                        @if($activity['printer'])
                                            <span>🖨️ {{ $activity['printer'] }}</span>
                                        @endif
                                        @if($activity['user'])
                                            <span>👤 {{ $activity['user'] }}</span>
                                        @endif
                                        <span>🕒 Hace {{ $activity['time'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Performance Chart -->
                <div class="chart-container rounded-xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-primary">
                            📈 Rendimiento Semanal
                        </h2>
                        <select onchange="updateChart(this.value)" 
                                class="text-sm border border-gray-300 rounded px-3 py-1">
                            <option value="week">Esta semana</option>
                            <option value="month">Este mes</option>
                            <option value="year">Este año</option>
                        </select>
                    </div>

                    <!-- Simple chart representation -->
                    <div class="space-y-4">
                        @foreach(['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $index => $day)
                        @php
                            $values = [85, 92, 78, 95, 88, 45, 32];
                            $value = $values[$index];
                        @endphp
                        <div class="flex items-center space-x-4">
                            <div class="w-12 text-sm text-secondary">{{ $day }}</div>
                            <div class="flex-1 progress-bar h-6 rounded">
                                <div class="progress-fill h-full rounded flex items-center justify-end pr-2" 
                                     style="width: {{ $value }}%">
                                    <span class="text-xs text-white font-medium">{{ $value }}%</span>
                                </div>
                            </div>
                            <div class="w-16 text-sm text-secondary text-right">
                                {{ rand(50, 200) }} jobs
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="chart-container rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-primary mb-4">
                        ⚡ Acciones Rápidas
                    </h2>
                    
                    <div class="space-y-3">
                        <button onclick="addPrinter()" 
                                class="quick-action-btn w-full p-3 rounded-lg text-left flex items-center space-x-3">
                            <span class="text-lg">➕</span>
                            <span class="font-medium">Agregar Impresora</span>
                        </button>
                        
                        <button onclick="viewReports()" 
                                class="quick-action-btn w-full p-3 rounded-lg text-left flex items-center space-x-3">
                            <span class="text-lg">📊</span>
                            <span class="font-medium">Ver Reportes</span>
                        </button>
                        
                        <button onclick="systemSettings()" 
                                class="quick-action-btn w-full p-3 rounded-lg text-left flex items-center space-x-3">
                            <span class="text-lg">⚙️</span>
                            <span class="font-medium">Configuración</span>
                        </button>
                        
                        <button onclick="backupSystem()" 
                                class="quick-action-btn w-full p-3 rounded-lg text-left flex items-center space-x-3">
                            <span class="text-lg">💾</span>
                            <span class="font-medium">Backup Sistema</span>
                        </button>
                        
                        <button onclick="viewLogs()" 
                                class="quick-action-btn w-full p-3 rounded-lg text-left flex items-center space-x-3">
                            <span class="text-lg">📋</span>
                            <span class="font-medium">Ver Logs</span>
                        </button>
                    </div>
                </div>

                <!-- System Alerts -->
                <div class="alert-card rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-primary mb-4">
                        🚨 Alertas del Sistema
                    </h2>
                    
                    <div class="space-y-4">
                        <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-start space-x-2">
                                <span class="text-yellow-500 mt-0.5">⚠️</span>
                                <div>
                                    <p class="text-sm font-medium text-yellow-800">Tóner bajo</p>
                                    <p class="text-xs text-yellow-600">HP LaserJet Pro - 15% restante</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex items-start space-x-2">
                                <span class="text-red-500 mt-0.5">🔴</span>
                                <div>
                                    <p class="text-sm font-medium text-red-800">Impresora offline</p>
                                    <p class="text-xs text-red-600">Brother DCP - Sin conexión</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-start space-x-2">
                                <span class="text-blue-500 mt-0.5">ℹ️</span>
                                <div>
                                    <p class="text-sm font-medium text-blue-800">Mantenimiento programado</p>
                                    <p class="text-xs text-blue-600">Canon PIXMA - Mañana 9:00 AM</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button onclick="viewAllAlerts()" 
                            class="w-full mt-4 text-sm text-primary-blue hover:text-primary-dark">
                        Ver todas las alertas →
                    </button>
                </div>

                <!-- System Status -->
                <div class="chart-container rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-primary mb-4">
                        💻 Estado del Sistema
                    </h2>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary">CPU</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-20 progress-bar h-2 rounded-full">
                                    <div class="progress-fill h-full rounded-full" style="width: 45%"></div>
                                </div>
                                <span class="text-xs text-secondary">45%</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary">Memoria</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-20 progress-bar h-2 rounded-full">
                                    <div class="progress-fill h-full rounded-full" style="width: 68%"></div>
                                </div>
                                <span class="text-xs text-secondary">68%</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary">Disco</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-20 progress-bar h-2 rounded-full">
                                    <div class="progress-fill h-full rounded-full" style="width: 32%"></div>
                                </div>
                                <span class="text-xs text-secondary">32%</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary">Red</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                                <span class="text-xs text-green-600">Estable</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="text-xs text-secondary">
                            <p>Uptime: 15 días, 8 horas</p>
                            <p>Última actualización: {{ date('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notifications Modal -->
<div id="notifications-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full max-h-96 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-primary">🔔 Notificaciones</h2>
                <button onclick="closeNotifications()" class="text-gray-400 hover:text-gray-600">
                    <span class="text-xl">✕</span>
                </button>
            </div>
        </div>
        
        <div class="p-6 overflow-y-auto max-h-80">
            <div class="space-y-4">
                <div class="p-3 bg-blue-50 rounded-lg">
                    <p class="text-sm font-medium text-blue-800">Nueva impresora conectada</p>
                    <p class="text-xs text-blue-600">HP LaserJet 1020 - Hace 5 min</p>
                </div>
                
                <div class="p-3 bg-green-50 rounded-lg">
                    <p class="text-sm font-medium text-green-800">Backup completado</p>
                    <p class="text-xs text-green-600">Sistema respaldado - Hace 1 hora</p>
                </div>
                
                <div class="p-3 bg-yellow-50 rounded-lg">
                    <p class="text-sm font-medium text-yellow-800">Mantenimiento pendiente</p>
                    <p class="text-xs text-yellow-600">3 impresoras requieren atención</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Variables globales
    let refreshInterval = null;
    let isRefreshing = false;

    // Inicializar dashboard
    document.addEventListener('DOMContentLoaded', function() {
        initDashboard();
        startAutoRefresh();
    });

    // Inicializar dashboard
    function initDashboard() {
        updateSystemTime();
        loadDashboardData();
        
        // Configurar eventos
        setupEventListeners();
    }

    // Configurar event listeners
    function setupEventListeners() {
        // Cerrar modal al hacer clic fuera
        document.getElementById('notifications-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeNotifications();
            }
        });
        
        // Atajos de teclado
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeNotifications();
            }
            
            if (e.ctrlKey && e.key === 'r') {
                e.preventDefault();
                refreshDashboard();
            }
        });
    }

    // Actualizar hora del sistema
    function updateSystemTime() {
        setInterval(() => {
            const now = new Date();
            const timeString = now.toLocaleTimeString('es-ES');
            // Actualizar elementos de tiempo si existen
        }, 1000);
    }

    // Cargar datos del dashboard
    async function loadDashboardData() {
        try {
            const response = await fetch('/api/admin/dashboard-data', {
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                updateStatistics(data.stats);
                updatePrinterStatus(data.printers);
                updateRecentActivity(data.activity);
            }
        } catch (error) {
            console.error('Error loading dashboard data:', error);
        }
    }

    // Actualizar estadísticas
    function updateStatistics(stats) {
        if (stats) {
            document.getElementById('active-printers').textContent = stats.active_printers || 0;
            document.getElementById('jobs-today').textContent = stats.jobs_today || 0;
            document.getElementById('revenue-today').textContent = '$' + (stats.revenue_today || 0).toFixed(2);
            document.getElementById('active-sessions').textContent = stats.active_sessions || 0;
        }
    }

    // Actualizar estado de impresoras
    function updatePrinterStatus(printers) {
        // Implementar actualización de estado de impresoras
        console.log('Updating printer status:', printers);
    }

    // Actualizar actividad reciente
    function updateRecentActivity(activity) {
        // Implementar actualización de actividad reciente
        console.log('Updating recent activity:', activity);
    }

    // Refrescar dashboard
    async function refreshDashboard() {
        if (isRefreshing) return;
        
        isRefreshing = true;
        const refreshBtn = document.getElementById('refresh-btn');
        refreshBtn.classList.add('spinning');
        
        try {
            await loadDashboardData();
            showNotification('Dashboard actualizado', 'success');
        } catch (error) {
            showNotification('Error al actualizar dashboard', 'error');
        } finally {
            isRefreshing = false;
            refreshBtn.classList.remove('spinning');
        }
    }

    // Auto-refresh
    function startAutoRefresh() {
        refreshInterval = setInterval(() => {
            if (!isRefreshing) {
                loadDashboardData();
            }
        }, 30000); // Cada 30 segundos
    }

    // Mostrar notificaciones
    function showNotifications() {
        document.getElementById('notifications-modal').classList.remove('hidden');
    }

    // Cerrar notificaciones
    function closeNotifications() {
        document.getElementById('notifications-modal').classList.add('hidden');
    }

    // Gestionar impresoras
    function managePrinters() {
        window.location.href = '/admin/printers';
    }

    // Ver impresora específica
    function viewPrinter(printerId) {
        window.location.href = `/admin/printers/${printerId}`;
    }

    // Pausar impresora
    async function pausePrinter(printerId) {
        if (!confirm('¿Pausar esta impresora?')) return;
        
        try {
            const response = await fetch(`/api/admin/printers/${printerId}/pause`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                showNotification('Impresora pausada', 'success');
                refreshDashboard();
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            showNotification('Error al pausar impresora: ' + error.message, 'error');
        }
    }

    // Acciones rápidas
    function addPrinter() {
        window.location.href = '/admin/printers/create';
    }

    function viewReports() {
        window.location.href = '/admin/reports';
    }

    function systemSettings() {
        window.location.href = '/admin/settings';
    }

    function backupSystem() {
        if (confirm('¿Iniciar backup del sistema?')) {
            showNotification('Backup iniciado en segundo plano', 'info');
        }
    }

    function viewLogs() {
        window.location.href = '/admin/logs';
    }

    function viewAllActivity() {
        window.location.href = '/admin/activity';
    }

    function viewAllAlerts() {
        window.location.href = '/admin/alerts';
    }

    // Actualizar gráfico
    function updateChart(period) {
        showNotification(`Actualizando gráfico para: ${period}`, 'info');
        // Implementar actualización de gráfico
    }

    // Logout
    async function logout() {
        if (!confirm('¿Cerrar sesión administrativa?')) return;
        
        try {
            const response = await fetch('/api/admin/logout', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken
                }
            });
            
            if (response.ok) {
                window.location.href = '/admin/login';
            }
        } catch (error) {
            console.error('Error during logout:', error);
            window.location.href = '/admin/login';
        }
    }

    // Limpiar intervalos al salir
    window.addEventListener('beforeunload', function() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    });
</script>
@endpush