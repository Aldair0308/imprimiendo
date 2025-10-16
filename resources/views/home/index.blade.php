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
                        Válido por 2 horas
                    </p>
                </div>

                <!-- QR Code Display -->
                <div class="bg-white p-6 rounded-xl mb-6 flex items-center justify-center">
                    <div id="qr-code-container" class="text-center">
                        <div id="qrcode" class="w-48 h-48 mx-auto flex items-center justify-center"></div>
                    </div>
                </div>

                <!-- Session Info -->
                <div class="text-center">
                    <p class="text-sm text-secondary mb-2">
                        ID de Sesión: <span class="font-mono text-primary" id="session-id">{{ $session->session_code ?? 'Generando...' }}</span>
                    </p>
                    <button onclick="generateQRCode()" 
                            class="btn-secondary text-sm px-4 py-2 hover:bg-gray-50 transition-colors duration-150">
                        🔄 Generar Nuevo Código
                    </button>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-2xl mx-auto">
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4 text-white">
                    <div class="text-2xl font-bold" id="active-printers">{{ $systemStatus['active_printers'] ?? 0 }}</div>
                    <div class="text-sm text-blue-200">Impresoras Activas</div>
                </div>
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4 text-white">
                    <div class="text-2xl font-bold" id="queue-jobs">{{ $systemStatus['queue_jobs'] ?? 0 }}</div>
                    <div class="text-sm text-blue-200">Trabajos en Cola</div>
                </div>
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4 text-white">
                    <div class="text-2xl font-bold" id="avg-wait-time">{{ $systemStatus['avg_wait_time'] ?? '0' }}min</div>
                    <div class="text-sm text-blue-200">Tiempo Promedio</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Printer Status Section -->
    @if(isset($availablePrinters) && $availablePrinters->count() > 0)
    <section class="py-12 px-4">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-3xl font-bold text-primary text-center mb-8">
                Estado de Impresoras
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($availablePrinters as $printer)
                <div class="printer-card bg-primary rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-primary">{{ $printer->name }}</h3>
                        <div class="flex items-center">
                            @if($printer->is_available && $printer->status && $printer->status->is_online)
                                <div class="w-3 h-3 bg-success-green rounded-full status-pulse"></div>
                                <span class="text-sm text-success-green ml-2">En línea</span>
                            @else
                                <div class="w-3 h-3 bg-error-red rounded-full"></div>
                                <span class="text-sm text-error-red ml-2">Fuera de línea</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary">Ubicación:</span>
                            <span class="text-primary">{{ $printer->location ?? 'No especificada' }}</span>
                        </div>
                        
                        @if($printer->status)
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary">Papel:</span>
                            <span class="text-primary">{{ $printer->status->paper_level ?? 0 }}%</span>
                        </div>
                        
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary">Tinta:</span>
                            <span class="text-primary">{{ $printer->status->ink_level ?? 0 }}%</span>
                        </div>
                        
                        @if($printer->status->status_message)
                        <div class="mt-3 p-2 bg-light rounded text-sm text-secondary">
                            {{ $printer->status->status_message }}
                        </div>
                        @endif
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- How it Works Section -->
    <section class="py-16 px-4 bg-light">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl font-bold text-primary text-center mb-12">
                ¿Cómo funciona?
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Step 1 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary-blue rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white font-bold text-xl">1</span>
                    </div>
                    <h3 class="font-semibold text-primary mb-2">Escanea el QR</h3>
                    <p class="text-sm text-secondary">Usa tu teléfono para escanear el código QR</p>
                </div>

                <!-- Step 2 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary-blue rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white font-bold text-xl">2</span>
                    </div>
                    <h3 class="font-semibold text-primary mb-2">Sube archivos</h3>
                    <p class="text-sm text-secondary">Carga tus documentos PDF, imágenes o texto</p>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary-blue rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white font-bold text-xl">3</span>
                    </div>
                    <h3 class="font-semibold text-primary mb-2">Configura</h3>
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
    let currentSessionId = '{{ $session->session_code ?? '' }}';
    let updateInterval;

    // Función para generar nuevo QR
    async function generateNewQR() {
        showLoading();
        
        try {
            const response = await fetch('/api/session/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                currentSessionId = data.session_code;
                document.getElementById('session-id').textContent = data.session_code;
                
                // Actualizar QR code
                const qrContainer = document.getElementById('qr-code-container');
                qrContainer.innerHTML = `<img src="${data.qr_code_url}" alt="Código QR de sesión" class="w-48 h-48 mx-auto">`;
                
                showNotification('✅ Nuevo código QR generado', 'success');
            } else {
                showNotification('❌ Error al generar nuevo código', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('❌ Error de conexión', 'error');
        }
        
        hideLoading();
    }

    // Función para mostrar loading
    function showLoading() {
        const qrContainer = document.getElementById('qr-code-container');
        qrContainer.innerHTML = `
            <div class="w-48 h-48 bg-gray-100 rounded-lg flex items-center justify-center">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-blue mx-auto mb-2"></div>
                    <span class="text-sm text-secondary">Generando QR...</span>
                </div>
            </div>
        `;
    }

    // Función para ocultar loading
    function hideLoading() {
        // El loading se oculta cuando se actualiza el contenido
    }

    // Función para mostrar notificaciones
    function showNotification(message, type = 'info') {
        // Crear elemento de notificación
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 ${
            type === 'success' ? 'bg-success-green text-white' : 
            type === 'error' ? 'bg-error-red text-white' : 
            'bg-primary-blue text-white'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Remover después de 3 segundos
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Actualizar estadísticas cada 30 segundos
    function updateStats() {
        fetch('/api/system/status')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('active-printers').textContent = data.system_status.active_printers || 0;
                    document.getElementById('queue-jobs').textContent = data.system_status.queue_jobs || 0;
                    document.getElementById('avg-wait-time').textContent = (data.system_status.avg_wait_time || 0) + 'min';
                }
            })
            .catch(error => console.error('Error updating stats:', error));
    }

    // Inicializar actualizaciones automáticas
    document.addEventListener('DOMContentLoaded', function() {
        // Actualizar estadísticas cada 30 segundos
        updateInterval = setInterval(updateStats, 30000);
        
        // Actualizar una vez al cargar
        updateStats();
        
        // Generar QR Code
        generateQRCode();
    });

    // Función para generar QR Code
    function generateQRCode() {
        const sessionCode = '{{ $session->session_code }}';
        const qrUrl = `{{ url('/session') }}/${sessionCode}`;
        
        // Limpiar contenedor
        const qrContainer = document.getElementById('qrcode');
        qrContainer.innerHTML = '';
        
        // Generar QR usando una API pública
        const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=192x192&data=${encodeURIComponent(qrUrl)}`;
        
        const img = document.createElement('img');
        img.src = qrApiUrl;
        img.alt = 'Código QR de sesión';
        img.className = 'w-48 h-48 mx-auto';
        img.onload = function() {
            qrContainer.appendChild(img);
        };
        img.onerror = function() {
            // Fallback: mostrar URL como texto
            qrContainer.innerHTML = `
                <div class="text-center p-4">
                    <p class="text-sm text-gray-600 mb-2">Escanea este código:</p>
                    <p class="text-xs font-mono bg-gray-100 p-2 rounded">${qrUrl}</p>
                </div>
            `;
        };
    }

    // Limpiar interval al salir de la página
    window.addEventListener('beforeunload', function() {
        if (updateInterval) {
            clearInterval(updateInterval);
        }
    });
</script>
@endpush