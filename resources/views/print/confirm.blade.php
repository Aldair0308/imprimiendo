@extends('layouts.app')

@section('title', 'Confirmar Impresión - ' . $session->session_id)
@section('description', 'Revisa y confirma tu pedido de impresión antes de proceder al pago.')

@push('styles')
<style>
    .confirmation-section {
        transition: all var(--transition-normal);
    }
    
    .confirmation-section:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }
    
    .file-summary-item {
        transition: all var(--transition-normal);
    }
    
    .file-summary-item:hover {
        background-color: var(--bg-gray-light);
    }
    
    .payment-method {
        transition: all var(--transition-normal);
        cursor: pointer;
    }
    
    .payment-method:hover {
        transform: scale(1.02);
        box-shadow: var(--shadow-md);
    }
    
    .payment-method.selected {
        border-color: var(--primary-blue);
        background-color: var(--bg-blue-light);
        box-shadow: var(--shadow-lg);
    }
    
    .qr-code-container {
        animation: pulse 2s infinite;
    }
    
    .processing-animation {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .print-preview {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .status-indicator {
        animation: pulse 2s infinite;
    }
    
    .countdown-timer {
        font-variant-numeric: tabular-nums;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-secondary py-8 px-4">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="bg-primary rounded-xl shadow-lg p-6 mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="mb-4 md:mb-0">
                    <h1 class="text-2xl md:text-3xl font-bold text-primary mb-2">
                        ✅ Confirmar Impresión
                    </h1>
                    <p class="text-secondary">
                        Sesión: <span class="font-mono text-primary">{{ $session->session_id }}</span>
                        • Impresora: <span class="font-medium text-primary">{{ $printer->name }}</span>
                    </p>
                </div>
                
                <!-- Session Timer -->
                <div class="bg-white rounded-lg p-4 text-center">
                    <div class="text-sm text-secondary mb-1">Tiempo restante</div>
                    <div class="text-2xl font-bold text-primary countdown-timer" id="session-timer">
                        {{ $session->remaining_time ?? '20:15' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Order Summary -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Files Summary -->
                <div class="confirmation-section card">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-primary">
                            📄 Resumen de Archivos
                        </h2>
                        <button onclick="editFiles()" 
                                class="text-primary-blue hover:text-primary-dark text-sm">
                            ✏️ Editar
                        </button>
                    </div>

                    <div class="print-preview space-y-3">
                        @foreach($files as $file)
                        <div class="file-summary-item p-4 rounded-lg border border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <span class="text-sm">
                                            @if(str_ends_with($file['name'], '.pdf'))
                                                📄
                                            @else
                                                📝
                                            @endif
                                        </span>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-primary">{{ $file['name'] }}</h4>
                                        <p class="text-sm text-secondary">
                                            {{ $file['pages'] }} páginas • {{ $file['copies'] ?? 1 }} copia(s)
                                        </p>
                                        @if(isset($file['page_range']) && $file['page_range'] !== 'all')
                                            <p class="text-xs text-blue-600">
                                                Páginas: {{ ucfirst($file['page_range']) }}
                                                @if($file['page_range'] === 'custom' && isset($file['custom_range']))
                                                    ({{ $file['custom_range'] }})
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="text-right">
                                    <div class="text-sm font-medium text-primary">
                                        {{ ($file['pages'] * ($file['copies'] ?? 1)) }} páginas
                                    </div>
                                    <div class="text-xs text-secondary">
                                        ${{ number_format(($file['pages'] * ($file['copies'] ?? 1)) * 0.10, 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Files Total -->
                    <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-primary">Total de páginas a imprimir:</span>
                            <span class="text-xl font-bold text-primary" id="total-print-pages">
                                {{ array_sum(array_map(function($file) { 
                                    return $file['pages'] * ($file['copies'] ?? 1); 
                                }, $files)) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Print Configuration -->
                <div class="confirmation-section card">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-primary">
                            ⚙️ Configuración de Impresión
                        </h2>
                        <button onclick="editConfiguration()" 
                                class="text-primary-blue hover:text-primary-dark text-sm">
                            ✏️ Editar
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-secondary">Calidad:</span>
                                <span class="font-medium text-primary" id="config-quality">
                                    {{ ucfirst($configuration['quality'] ?? 'Normal') }}
                                </span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-secondary">Color:</span>
                                <span class="font-medium text-primary" id="config-color">
                                    {{ $configuration['color'] === 'bw' ? 'Blanco y Negro' : 'Color' }}
                                </span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-secondary">Papel:</span>
                                <span class="font-medium text-primary" id="config-paper">
                                    {{ strtoupper($configuration['paper_size'] ?? 'A4') }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-secondary">Orientación:</span>
                                <span class="font-medium text-primary" id="config-orientation">
                                    {{ $configuration['orientation'] === 'portrait' ? 'Vertical' : 'Horizontal' }}
                                </span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-secondary">Impresión:</span>
                                <span class="font-medium text-primary" id="config-duplex">
                                    @switch($configuration['duplex'] ?? 'simplex')
                                        @case('simplex') Una cara @break
                                        @case('duplex_long') Doble cara (largo) @break
                                        @case('duplex_short') Doble cara (corto) @break
                                        @default Una cara
                                    @endswitch
                                </span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-secondary">Escala:</span>
                                <span class="font-medium text-primary" id="config-scale">
                                    {{ $configuration['scale'] ?? 100 }}%
                                </span>
                            </div>
                        </div>
                    </div>

                    @if(isset($configuration['special_instructions']) && !empty($configuration['special_instructions']))
                    <div class="mt-6 p-4 bg-yellow-50 rounded-lg">
                        <h4 class="font-medium text-primary mb-2">📝 Instrucciones Especiales:</h4>
                        <p class="text-sm text-secondary">{{ $configuration['special_instructions'] }}</p>
                    </div>
                    @endif
                </div>

                <!-- Printer Information -->
                <div class="confirmation-section card">
                    <h2 class="text-xl font-semibold text-primary mb-6">
                        🖨️ Información de la Impresora
                    </h2>

                    <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                        <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">🖨️</span>
                        </div>
                        
                        <div class="flex-1">
                            <h3 class="font-semibold text-primary">{{ $printer->name }}</h3>
                            <p class="text-sm text-secondary">{{ $printer->model ?? 'EPSON L325' }}</p>
                            
                            <div class="flex items-center space-x-4 mt-2">
                                <div class="flex items-center space-x-1">
                                    <div class="status-indicator status-online"></div>
                                    <span class="text-xs text-green-600">En línea</span>
                                </div>
                                
                                <span class="text-xs text-secondary">
                                    Cola: {{ $printer->queue_count ?? 2 }} trabajos
                                </span>
                                
                                <span class="text-xs text-secondary">
                                    Velocidad: {{ $printer->speed ?? 15 }} ppm
                                </span>
                            </div>
                        </div>
                        
                        <div class="text-right">
                            <div class="text-sm text-secondary">Tiempo estimado</div>
                            <div class="text-lg font-bold text-primary" id="estimated-time">
                                {{ ceil((array_sum(array_map(function($file) { 
                                    return $file['pages'] * ($file['copies'] ?? 1); 
                                }, $files)) / ($printer->speed ?? 15)) * 60) }} min
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment & Confirmation -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Price Summary -->
                <div class="card">
                    <h2 class="text-xl font-semibold text-primary mb-6">
                        💰 Resumen de Pago
                    </h2>

                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary">Páginas ({{ array_sum(array_map(function($file) { return $file['pages'] * ($file['copies'] ?? 1); }, $files)) }}):</span>
                            <span class="text-primary" id="pages-cost">${{ number_format($pricing['base_cost'] ?? 2.50, 2) }}</span>
                        </div>
                        
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary">Calidad:</span>
                            <span class="text-primary" id="quality-cost">${{ number_format($pricing['quality_cost'] ?? 0.00, 2) }}</span>
                        </div>
                        
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary">Color:</span>
                            <span class="text-primary" id="color-cost">${{ number_format($pricing['color_cost'] ?? 0.00, 2) }}</span>
                        </div>
                        
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary">Papel especial:</span>
                            <span class="text-primary" id="paper-cost">${{ number_format($pricing['paper_cost'] ?? 0.00, 2) }}</span>
                        </div>
                        
                        @if(isset($pricing['discount']) && $pricing['discount'] > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-green-600">Descuento:</span>
                            <span class="text-green-600">-${{ number_format($pricing['discount'], 2) }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="border-t border-gray-200 pt-4 mb-6">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold text-primary">Total a Pagar:</span>
                            <span class="text-2xl font-bold text-primary" id="final-total">
                                ${{ number_format($pricing['total'] ?? 2.50, 2) }}
                            </span>
                        </div>
                    </div>

                    <!-- Payment Method Selection -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-primary mb-3">Método de Pago</label>
                        <div class="space-y-3">
                            <div class="payment-method p-3 border-2 border-gray-200 rounded-lg selected" 
                                 data-payment="cash"
                                 onclick="selectPaymentMethod('cash', this)">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <span class="text-lg">💵</span>
                                        <span class="font-medium">Efectivo</span>
                                    </div>
                                    <span class="text-sm text-green-600">Disponible</span>
                                </div>
                            </div>
                            
                            <div class="payment-method p-3 border-2 border-gray-200 rounded-lg" 
                                 data-payment="card"
                                 onclick="selectPaymentMethod('card', this)">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <span class="text-lg">💳</span>
                                        <span class="font-medium">Tarjeta</span>
                                    </div>
                                    <span class="text-sm text-blue-600">Visa/MC</span>
                                </div>
                            </div>
                            
                            <div class="payment-method p-3 border-2 border-gray-200 rounded-lg" 
                                 data-payment="transfer"
                                 onclick="selectPaymentMethod('transfer', this)">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <span class="text-lg">📱</span>
                                        <span class="font-medium">Transferencia</span>
                                    </div>
                                    <span class="text-sm text-purple-600">QR</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code for Payment (if transfer selected) -->
                    <div id="payment-qr" class="hidden mb-6">
                        <div class="text-center p-6 bg-gray-50 rounded-lg">
                            <div class="qr-code-container mb-4">
                                <div class="w-32 h-32 bg-white border-2 border-gray-300 rounded-lg mx-auto flex items-center justify-center">
                                    <span class="text-4xl">📱</span>
                                </div>
                            </div>
                            <p class="text-sm text-secondary mb-2">Escanea para pagar</p>
                            <p class="text-lg font-bold text-primary">${{ number_format($pricing['total'] ?? 2.50, 2) }}</p>
                        </div>
                    </div>

                    <!-- Confirmation Button -->
                    <button id="confirm-print-btn" 
                            onclick="confirmPrintJob()" 
                            class="w-full btn-primary py-4 text-lg font-semibold">
                        <span id="confirm-btn-text">🖨️ Confirmar e Imprimir</span>
                        <span id="confirm-btn-loading" class="hidden">
                            <span class="processing-animation inline-block">⏳</span> Procesando...
                        </span>
                    </button>

                    <!-- Processing Status -->
                    <div id="processing-status" class="hidden mt-4 p-4 bg-blue-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <span class="processing-animation text-blue-600">⏳</span>
                            <div>
                                <div class="font-medium text-blue-800">Procesando impresión...</div>
                                <div class="text-sm text-blue-600" id="processing-message">
                                    Enviando archivos a la impresora
                                </div>
                            </div>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="mt-3">
                            <div class="w-full bg-blue-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full transition-all duration-500" 
                                     id="processing-progress" 
                                     style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <h3 class="text-lg font-semibold text-primary mb-4">⚡ Acciones Rápidas</h3>
                    
                    <div class="space-y-3">
                        <button onclick="saveForLater()" 
                                class="w-full btn-secondary text-left flex items-center justify-between">
                            <span>💾 Guardar para después</span>
                            <span class="text-xs text-muted">24h</span>
                        </button>
                        
                        <button onclick="duplicateOrder()" 
                                class="w-full btn-secondary text-left flex items-center justify-between">
                            <span>📋 Duplicar pedido</span>
                            <span class="text-xs text-muted">Nueva sesión</span>
                        </button>
                        
                        <button onclick="requestSupport()" 
                                class="w-full btn-secondary text-left flex items-center justify-between">
                            <span>🆘 Solicitar ayuda</span>
                            <span class="text-xs text-muted">Soporte</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
            <button onclick="goBack()" 
                    class="btn-secondary px-8 py-3">
                ← Volver a Configuración
            </button>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="success-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 text-center">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-2xl">✅</span>
        </div>
        
        <h2 class="text-2xl font-bold text-primary mb-2">¡Impresión Confirmada!</h2>
        <p class="text-secondary mb-6">
            Tu trabajo de impresión ha sido enviado exitosamente.
        </p>
        
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="text-sm text-secondary mb-1">Código de seguimiento</div>
            <div class="text-lg font-mono font-bold text-primary" id="tracking-code">
                #IMP-{{ date('Ymd') }}-001
            </div>
        </div>
        
        <div class="space-y-3">
            <button onclick="viewPrintStatus()" 
                    class="w-full btn-primary">
                Ver Estado de Impresión
            </button>
            
            <button onclick="startNewSession()" 
                    class="w-full btn-secondary">
                Nueva Sesión
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Variables globales
    let sessionEndTime = new Date('{{ $session->expires_at ?? date("Y-m-d H:i:s", strtotime("+20 minutes")) }}');
    let sessionTimer = null;
    let selectedPaymentMethod = 'cash';
    let isProcessing = false;

    // Inicializar timer de sesión
    function initSessionTimer() {
        sessionTimer = setInterval(updateSessionTimer, 1000);
        updateSessionTimer();
    }

    // Actualizar timer de sesión
    function updateSessionTimer() {
        const now = new Date();
        const timeLeft = sessionEndTime - now;
        
        if (timeLeft <= 0) {
            clearInterval(sessionTimer);
            showNotification('La sesión ha expirado. Redirigiendo...', 'error');
            setTimeout(() => {
                window.location.href = '/';
            }, 3000);
            return;
        }
        
        const minutes = Math.floor(timeLeft / 60000);
        const seconds = Math.floor((timeLeft % 60000) / 1000);
        
        document.getElementById('session-timer').textContent = 
            `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
        // Cambiar color cuando quedan menos de 5 minutos
        const timerElement = document.getElementById('session-timer');
        if (minutes < 5) {
            timerElement.classList.add('text-red-600');
            timerElement.classList.remove('text-primary');
        }
    }

    // Seleccionar método de pago
    function selectPaymentMethod(method, element) {
        // Remover selección anterior
        document.querySelectorAll('.payment-method').forEach(el => {
            el.classList.remove('selected');
        });
        
        // Seleccionar nuevo método
        element.classList.add('selected');
        selectedPaymentMethod = method;
        
        // Mostrar/ocultar QR de pago
        const qrContainer = document.getElementById('payment-qr');
        if (method === 'transfer') {
            qrContainer.classList.remove('hidden');
            generatePaymentQR();
        } else {
            qrContainer.classList.add('hidden');
        }
        
        showNotification(`Método de pago seleccionado: ${getPaymentMethodName(method)}`, 'success');
    }

    // Obtener nombre del método de pago
    function getPaymentMethodName(method) {
        const names = {
            cash: 'Efectivo',
            card: 'Tarjeta',
            transfer: 'Transferencia'
        };
        return names[method] || method;
    }

    // Generar QR de pago
    function generatePaymentQR() {
        // En una implementación real, aquí se generaría el QR real
        const qrContainer = document.querySelector('.qr-code-container .w-32');
        qrContainer.innerHTML = `
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=128x128&data=PAY:{{ $pricing['total'] ?? 2.50 }}:{{ $session->session_id }}" 
                 alt="QR de Pago" 
                 class="w-full h-full object-contain">
        `;
    }

    // Confirmar trabajo de impresión
    async function confirmPrintJob() {
        if (isProcessing) return;
        
        isProcessing = true;
        
        // Cambiar estado del botón
        const confirmBtn = document.getElementById('confirm-print-btn');
        const btnText = document.getElementById('confirm-btn-text');
        const btnLoading = document.getElementById('confirm-btn-loading');
        const processingStatus = document.getElementById('processing-status');
        
        confirmBtn.disabled = true;
        btnText.classList.add('hidden');
        btnLoading.classList.remove('hidden');
        processingStatus.classList.remove('hidden');
        
        try {
            // Simular proceso de impresión
            await simulatePrintProcess();
            
            // Enviar solicitud al servidor
            const response = await fetch('/api/print/confirm', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({
                    session_id: '{{ $session->session_id }}',
                    printer_id: '{{ $printer->id }}',
                    payment_method: selectedPaymentMethod,
                    configuration: @json($configuration ?? {}),
                    files: @json($files ?? [])
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Mostrar modal de éxito
                document.getElementById('tracking-code').textContent = data.tracking_code || '#IMP-{{ date("Ymd") }}-001';
                document.getElementById('success-modal').classList.remove('hidden');
                
                // Limpiar timer
                if (sessionTimer) {
                    clearInterval(sessionTimer);
                }
            } else {
                throw new Error(data.message || 'Error al procesar la impresión');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al confirmar impresión: ' + error.message, 'error');
            
            // Restaurar botón
            confirmBtn.disabled = false;
            btnText.classList.remove('hidden');
            btnLoading.classList.add('hidden');
            processingStatus.classList.add('hidden');
            isProcessing = false;
        }
    }

    // Simular proceso de impresión
    async function simulatePrintProcess() {
        const messages = [
            'Validando archivos...',
            'Enviando a la impresora...',
            'Procesando configuración...',
            'Iniciando impresión...',
            'Confirmando pago...'
        ];
        
        const progressBar = document.getElementById('processing-progress');
        const messageElement = document.getElementById('processing-message');
        
        for (let i = 0; i < messages.length; i++) {
            messageElement.textContent = messages[i];
            progressBar.style.width = `${((i + 1) / messages.length) * 100}%`;
            
            await new Promise(resolve => setTimeout(resolve, 1000));
        }
    }

    // Editar archivos
    function editFiles() {
        window.location.href = `/session/{{ $session->session_id }}`;
    }

    // Editar configuración
    function editConfiguration() {
        window.location.href = `/session/{{ $session->session_id }}/configure`;
    }

    // Guardar para después
    async function saveForLater() {
        try {
            const response = await fetch('/api/print/save-for-later', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({
                    session_id: '{{ $session->session_id }}',
                    configuration: @json($configuration ?? {}),
                    files: @json($files ?? [])
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showNotification('Pedido guardado por 24 horas', 'success');
            } else {
                throw new Error(data.message || 'Error al guardar');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al guardar pedido', 'error');
        }
    }

    // Duplicar pedido
    function duplicateOrder() {
        if (confirm('¿Crear una nueva sesión con la misma configuración?')) {
            // Guardar configuración en localStorage
            localStorage.setItem('duplicateOrder', JSON.stringify({
                configuration: @json($configuration ?? {}),
                files: @json($files ?? [])
            }));
            
            window.location.href = '/';
        }
    }

    // Solicitar soporte
    function requestSupport() {
        showNotification('Función de soporte en desarrollo', 'info');
    }

    // Ver estado de impresión
    function viewPrintStatus() {
        const trackingCode = document.getElementById('tracking-code').textContent;
        window.location.href = `/print/status/${trackingCode.replace('#', '')}`;
    }

    // Nueva sesión
    function startNewSession() {
        window.location.href = '/';
    }

    // Volver atrás
    function goBack() {
        window.location.href = `/session/{{ $session->session_id }}/configure`;
    }

    // Cerrar modal
    function closeModal() {
        document.getElementById('success-modal').classList.add('hidden');
    }

    // Inicialización
    document.addEventListener('DOMContentLoaded', function() {
        initSessionTimer();
        
        // Configurar eventos del modal
        document.getElementById('success-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Verificar si hay configuración guardada
        const savedConfig = sessionStorage.getItem('printConfiguration');
        if (savedConfig) {
            console.log('Configuración cargada:', JSON.parse(savedConfig));
        }
    });

    // Limpiar timer al salir
    window.addEventListener('beforeunload', function() {
        if (sessionTimer) {
            clearInterval(sessionTimer);
        }
    });

    // Prevenir cierre accidental durante procesamiento
    window.addEventListener('beforeunload', function(e) {
        if (isProcessing) {
            e.preventDefault();
            e.returnValue = '¿Estás seguro? La impresión está en proceso.';
        }
    });
</script>
@endpush