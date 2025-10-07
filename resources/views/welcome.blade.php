@extends('layouts.app')

@section('title', 'Inicio - Sistema de Impresiones')
@section('description', 'Sistema de impresiones inteligente multi-impresora. Escanea el código QR para comenzar a imprimir tus documentos.')

@push('styles')
<style>
    .qr-container {
        background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
    }
    
    .printer-card {
        transition: all var(--transition-normal);
    }
    
    .printer-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-xl);
    }
    
    .status-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    
    .qr-code-animation {
        animation: fadeIn 1s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-secondary">
    <!-- Hero Section con QR Code -->
    <section class="qr-container py-16 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <div class="mb-8">
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-4">
                    🖨️ Imprimeindo
                </h1>
                <p class="text-xl md:text-2xl text-blue-100 mb-2">
                    Sistema de Impresiones Inteligente
                </p>
                <p class="text-lg text-blue-200">
                    Escanea el código QR para comenzar
                </p>
            </div>

            <!-- QR Code Card -->
            <div class="bg-primary rounded-2xl shadow-2xl p-8 mb-8 max-w-md mx-auto qr-code-animation">
                <div class="mb-6">
                    <h2 class="text-2xl font-semibold text-primary mb-2">
                        Código de Sesión
                    </h2>
                    <p class="text-secondary text-sm">
                        Válido por {{ config('app.session_duration', 30) }} minutos
                    </p>
                </div>

                <!-- QR Code Display -->
                <div class="bg-white p-6 rounded-xl mb-6 flex items-center justify-center">
                    <div id="qr-code-container" class="text-center">
                        @if(isset($qrCode))
                            {!! $qrCode !!}
                        @else
                            <div class="w-48 h-48 bg-gray-100 rounded-lg flex items-center justify-center">
                                <div class="text-center">
                                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-blue mx-auto mb-2"></div>
                                    <span class="text-sm text-secondary">Generando QR...</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Session Info -->
                <div class="text-center">
                    <p class="text-sm text-secondary mb-2">
                        ID de Sesión: <span class="font-mono text-primary" id="session-id">{{ $sessionId ?? 'Generando...' }}</span>
                    </p>
                    <button onclick="generateNewQR()" 
                            class="btn-secondary text-sm px-4 py-2 hover:bg-gray-50 transition-colors duration-150">
                        🔄 Generar Nuevo Código
                    </button>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-2xl mx-auto">
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4 text-white">
                    <div class="text-2xl font-bold" id="active-printers">{{ $stats['active_printers'] ?? 0 }}</div>
                    <div class="text-sm text-blue-200">Impresoras Activas</div>
                </div>
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4 text-white">
                    <div class="text-2xl font-bold" id="queue-jobs">{{ $stats['queue_jobs'] ?? 0 }}</div>
                    <div class="text-sm text-blue-200">Trabajos en Cola</div>
                </div>
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4 text-white">
                    <div class="text-2xl font-bold" id="active-sessions">{{ $stats['active_sessions'] ?? 0 }}</div>
                    <div class="text-sm text-blue-200">Sesiones Activas</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Printer Status Section -->
    <section class="py-16 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-primary mb-4">
                    Estado de Impresoras
                </h2>
                <p class="text-lg text-secondary">
                    Monitoreo en tiempo real del parque de impresoras
                </p>
            </div>

            <!-- Printers Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="printers-grid">
                @forelse($printers ?? [] as $printer)
                <div class="printer-card card" data-printer-id="{{ $printer['id'] }}">
                    <!-- Printer Header -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                <span class="text-lg">🖨️</span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-primary">{{ $printer['name'] }}</h3>
                                <p class="text-sm text-secondary">{{ $printer['model'] ?? 'EPSON L325' }}</p>
                            </div>
                        </div>
                        
                        <!-- Status Indicator -->
                        <div class="flex items-center space-x-2">
                            <div class="status-indicator 
                                @switch($printer['status'])
                                    @case('online') status-online @break
                                    @case('offline') status-offline @break
                                    @case('error') status-error @break
                                    @case('maintenance') status-maintenance @break
                                    @case('busy') status-busy @break
                                    @default status-offline
                                @endswitch
                                status-pulse"></div>
                            <span class="text-sm font-medium
                                @switch($printer['status'])
                                    @case('online') text-green-600 @break
                                    @case('offline') text-yellow-600 @break
                                    @case('error') text-red-600 @break
                                    @case('maintenance') text-purple-600 @break
                                    @case('busy') text-orange-600 @break
                                    @default text-gray-600
                                @endswitch">
                                {{ ucfirst($printer['status']) }}
                            </span>
                        </div>
                    </div>

                    <!-- Printer Details -->
                    <div class="space-y-3">
                        <!-- Capabilities -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary">Capacidades:</span>
                            <div class="flex space-x-2">
                                @if($printer['supports_color'] ?? true)
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">Color</span>
                                @endif
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full">B&N</span>
                            </div>
                        </div>

                        <!-- Queue Status -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary">Cola:</span>
                            <span class="text-sm font-medium text-primary">
                                {{ $printer['queue_count'] ?? 0 }} trabajos
                            </span>
                        </div>

                        <!-- Speed -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary">Velocidad:</span>
                            <span class="text-sm font-medium text-primary">
                                {{ $printer['speed'] ?? '15' }} ppm
                            </span>
                        </div>

                        <!-- Last Activity -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary">Última actividad:</span>
                            <span class="text-sm text-muted">
                                {{ $printer['last_activity'] ?? 'Hace 2 min' }}
                            </span>
                        </div>

                        <!-- Progress Bar (if busy) -->
                        @if(($printer['status'] ?? '') === 'busy')
                        <div class="mt-4">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs text-secondary">Progreso:</span>
                                <span class="text-xs text-secondary">{{ $printer['progress'] ?? 45 }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-orange-500 h-2 rounded-full transition-all duration-300" 
                                     style="width: {{ $printer['progress'] ?? 45 }}%"></div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <!-- No Printers Available -->
                <div class="col-span-full text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl">🖨️</span>
                    </div>
                    <h3 class="text-lg font-semibold text-primary mb-2">No hay impresoras disponibles</h3>
                    <p class="text-secondary">Las impresoras se mostrarán aquí cuando estén conectadas al sistema.</p>
                </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Instructions Section -->
    <section class="py-16 px-4 bg-primary">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-primary mb-4">
                    ¿Cómo funciona?
                </h2>
                <p class="text-lg text-secondary">
                    Sigue estos sencillos pasos para imprimir tus documentos
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Step 1 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary-blue rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white font-bold text-xl">1</span>
                    </div>
                    <h3 class="font-semibold text-primary mb-2">Escanea el QR</h3>
                    <p class="text-sm text-secondary">Usa la cámara de tu dispositivo para escanear el código QR</p>
                </div>

                <!-- Step 2 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary-blue rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white font-bold text-xl">2</span>
                    </div>
                    <h3 class="font-semibold text-primary mb-2">Sube tus archivos</h3>
                    <p class="text-sm text-secondary">Arrastra o selecciona tus documentos PDF o DOCX</p>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary-blue rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white font-bold text-xl">3</span>
                    </div>
                    <h3 class="font-semibold text-primary mb-2">Configura la impresión</h3>
                    <p class="text-sm text-secondary">Selecciona impresora, páginas, copias y opciones</p>
                </div>

                <!-- Step 4 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-success-green rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white font-bold text-xl">✓</span>
                    </div>
                    <h3 class="font-semibold text-primary mb-2">¡Listo!</h3>
                    <p class="text-sm text-secondary">Confirma el pago y recoge tu impresión</p>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    // Variables globales
    let currentSessionId = '{{ $sessionId ?? '' }}';
    let updateInterval;

    // Función para generar nuevo QR
    async function generateNewQR() {
        showLoading();
        
        try {
            const response = await fetch('/api/session/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                currentSessionId = data.session_id;
                document.getElementById('session-id').textContent = data.session_id;
                document.getElementById('qr-code-container').innerHTML = data.qr_code;
                
                // Animar el nuevo QR
                const container = document.getElementById('qr-code-container');
                container.classList.add('qr-code-animation');
                
                showNotification('Nuevo código QR generado exitosamente', 'success');
            } else {
                showNotification('Error al generar nuevo código QR', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error de conexión', 'error');
        } finally {
            hideLoading();
        }
    }

    // Función para actualizar estado del sistema
    async function updateSystemStatus() {
        try {
            const response = await fetch('/api/system/status');
            const data = await response.json();
            
            if (data.success) {
                // Actualizar estadísticas
                document.getElementById('active-printers').textContent = data.stats.active_printers;
                document.getElementById('queue-jobs').textContent = data.stats.queue_jobs;
                document.getElementById('active-sessions').textContent = data.stats.active_sessions;
                
                // Actualizar estado de impresoras
                updatePrintersStatus(data.printers);
            }
        } catch (error) {
            console.error('Error updating system status:', error);
        }
    }

    // Función para actualizar estado de impresoras
    function updatePrintersStatus(printers) {
        printers.forEach(printer => {
            const printerCard = document.querySelector(`[data-printer-id="${printer.id}"]`);
            if (printerCard) {
                // Actualizar indicador de estado
                const statusIndicator = printerCard.querySelector('.status-indicator');
                const statusText = printerCard.querySelector('.status-indicator + span');
                
                // Remover clases anteriores
                statusIndicator.className = 'status-indicator status-pulse';
                
                // Agregar nueva clase de estado
                switch (printer.status) {
                    case 'online':
                        statusIndicator.classList.add('status-online');
                        statusText.className = 'text-sm font-medium text-green-600';
                        break;
                    case 'offline':
                        statusIndicator.classList.add('status-offline');
                        statusText.className = 'text-sm font-medium text-yellow-600';
                        break;
                    case 'error':
                        statusIndicator.classList.add('status-error');
                        statusText.className = 'text-sm font-medium text-red-600';
                        break;
                    case 'maintenance':
                        statusIndicator.classList.add('status-maintenance');
                        statusText.className = 'text-sm font-medium text-purple-600';
                        break;
                    case 'busy':
                        statusIndicator.classList.add('status-busy');
                        statusText.className = 'text-sm font-medium text-orange-600';
                        break;
                }
                
                statusText.textContent = printer.status.charAt(0).toUpperCase() + printer.status.slice(1);
                
                // Actualizar cola
                const queueElement = printerCard.querySelector('.text-sm.font-medium.text-primary');
                if (queueElement) {
                    queueElement.textContent = `${printer.queue_count} trabajos`;
                }
            }
        });
    }

    // Inicialización
    document.addEventListener('DOMContentLoaded', function() {
        // Actualizar estado inicial
        updateSystemStatus();
        
        // Configurar actualización automática cada 30 segundos
        updateInterval = setInterval(updateSystemStatus, 30000);
        
        // Generar QR inicial si no existe
        if (!currentSessionId) {
            generateNewQR();
        }
    });

    // Limpiar interval al salir de la página
    window.addEventListener('beforeunload', function() {
        if (updateInterval) {
            clearInterval(updateInterval);
        }
    });
</script>
@endpush
