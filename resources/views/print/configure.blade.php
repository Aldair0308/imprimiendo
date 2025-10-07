@extends('layouts.app')

@section('title', 'Configurar Impresión - ' . $session->session_id)
@section('description', 'Configura las opciones de impresión para tus documentos.')

@push('styles')
<style>
    .config-section {
        transition: all var(--transition-normal);
    }
    
    .config-section:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }
    
    .option-card {
        transition: all var(--transition-normal);
        cursor: pointer;
    }
    
    .option-card:hover {
        transform: scale(1.02);
        box-shadow: var(--shadow-md);
    }
    
    .option-card.selected {
        border-color: var(--primary-blue);
        background-color: var(--bg-blue-light);
        box-shadow: var(--shadow-lg);
    }
    
    .price-calculator {
        position: sticky;
        top: 20px;
    }
    
    .file-preview-item {
        transition: all var(--transition-normal);
    }
    
    .file-preview-item:hover {
        background-color: var(--bg-gray-light);
    }
    
    .range-slider {
        -webkit-appearance: none;
        appearance: none;
        height: 6px;
        border-radius: 3px;
        background: var(--bg-gray-light);
        outline: none;
    }
    
    .range-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--primary-blue);
        cursor: pointer;
        box-shadow: var(--shadow-sm);
    }
    
    .range-slider::-moz-range-thumb {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--primary-blue);
        cursor: pointer;
        border: none;
        box-shadow: var(--shadow-sm);
    }
    
    .advanced-options {
        max-height: 0;
        overflow: hidden;
        transition: max-height var(--transition-slow);
    }
    
    .advanced-options.expanded {
        max-height: 500px;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-secondary py-8 px-4">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-primary rounded-xl shadow-lg p-6 mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="mb-4 md:mb-0">
                    <h1 class="text-2xl md:text-3xl font-bold text-primary mb-2">
                        ⚙️ Configurar Impresión
                    </h1>
                    <p class="text-secondary">
                        Sesión: <span class="font-mono text-primary">{{ $session->session_id }}</span>
                        • Impresora: <span class="font-medium text-primary">{{ $printer->name }}</span>
                    </p>
                </div>
                
                <!-- Session Timer -->
                <div class="bg-white rounded-lg p-4 text-center">
                    <div class="text-sm text-secondary mb-1">Tiempo restante</div>
                    <div class="text-2xl font-bold text-primary session-timer" id="session-timer">
                        {{ $session->remaining_time ?? '25:30' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Configuration Options -->
            <div class="lg:col-span-2 space-y-6">
                <!-- File Preview -->
                <div class="config-section card">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-primary">
                            📄 Archivos a Imprimir
                        </h2>
                        <span class="text-sm text-secondary">
                            {{ count($files) }} archivo(s) • {{ array_sum(array_column($files, 'pages')) }} páginas
                        </span>
                    </div>

                    <div class="space-y-3 max-h-60 overflow-y-auto">
                        @foreach($files as $index => $file)
                        <div class="file-preview-item p-4 rounded-lg border border-gray-200" 
                             data-file-id="{{ $file['id'] }}">
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
                                            {{ $file['pages'] }} páginas • {{ $file['size'] }}
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-4">
                                    <!-- Page Range -->
                                    <div class="text-right">
                                        <label class="text-xs text-secondary">Páginas</label>
                                        <select class="block w-24 text-sm border-gray-300 rounded-md page-range-select" 
                                                data-file-id="{{ $file['id'] }}"
                                                onchange="updatePageRange('{{ $file['id'] }}', this.value)">
                                            <option value="all">Todas</option>
                                            <option value="odd">Impares</option>
                                            <option value="even">Pares</option>
                                            <option value="custom">Rango</option>
                                        </select>
                                        <input type="text" 
                                               class="hidden mt-1 block w-24 text-sm border-gray-300 rounded-md custom-range-input" 
                                               placeholder="1-5,8"
                                               data-file-id="{{ $file['id'] }}">
                                    </div>
                                    
                                    <!-- Copies -->
                                    <div class="text-right">
                                        <label class="text-xs text-secondary">Copias</label>
                                        <input type="number" 
                                               class="block w-16 text-sm border-gray-300 rounded-md copies-input" 
                                               value="1" 
                                               min="1" 
                                               max="10"
                                               data-file-id="{{ $file['id'] }}"
                                               onchange="updateCopies('{{ $file['id'] }}', this.value)">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Print Quality -->
                <div class="config-section card">
                    <h2 class="text-xl font-semibold text-primary mb-6">
                        🎨 Calidad de Impresión
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="option-card p-4 border-2 border-gray-200 rounded-lg" 
                             data-option="quality" 
                             data-value="draft"
                             onclick="selectOption('quality', 'draft', this)">
                            <div class="text-center">
                                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <span class="text-lg">⚡</span>
                                </div>
                                <h3 class="font-semibold text-primary mb-2">Borrador</h3>
                                <p class="text-sm text-secondary mb-2">Rápido y económico</p>
                                <div class="text-lg font-bold text-green-600">$0.05/página</div>
                            </div>
                        </div>

                        <div class="option-card p-4 border-2 border-gray-200 rounded-lg selected" 
                             data-option="quality" 
                             data-value="normal"
                             onclick="selectOption('quality', 'normal', this)">
                            <div class="text-center">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <span class="text-lg">📄</span>
                                </div>
                                <h3 class="font-semibold text-primary mb-2">Normal</h3>
                                <p class="text-sm text-secondary mb-2">Calidad estándar</p>
                                <div class="text-lg font-bold text-blue-600">$0.10/página</div>
                            </div>
                        </div>

                        <div class="option-card p-4 border-2 border-gray-200 rounded-lg" 
                             data-option="quality" 
                             data-value="high"
                             onclick="selectOption('quality', 'high', this)">
                            <div class="text-center">
                                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <span class="text-lg">✨</span>
                                </div>
                                <h3 class="font-semibold text-primary mb-2">Alta</h3>
                                <p class="text-sm text-secondary mb-2">Máxima calidad</p>
                                <div class="text-lg font-bold text-purple-600">$0.20/página</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Color Options -->
                <div class="config-section card">
                    <h2 class="text-xl font-semibold text-primary mb-6">
                        🌈 Opciones de Color
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="option-card p-4 border-2 border-gray-200 rounded-lg selected" 
                             data-option="color" 
                             data-value="bw"
                             onclick="selectOption('color', 'bw', this)">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                                    <span class="text-lg">⚫</span>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-primary mb-1">Blanco y Negro</h3>
                                    <p class="text-sm text-secondary mb-2">Ideal para documentos de texto</p>
                                    <div class="text-lg font-bold text-gray-600">Precio base</div>
                                </div>
                            </div>
                        </div>

                        <div class="option-card p-4 border-2 border-gray-200 rounded-lg" 
                             data-option="color" 
                             data-value="color"
                             onclick="selectOption('color', 'color', this)">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-gradient-to-r from-red-400 to-blue-400 rounded-full flex items-center justify-center">
                                    <span class="text-lg text-white">🌈</span>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-primary mb-1">Color</h3>
                                    <p class="text-sm text-secondary mb-2">Para imágenes y gráficos</p>
                                    <div class="text-lg font-bold text-blue-600">+$0.15/página</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Paper Size -->
                <div class="config-section card">
                    <h2 class="text-xl font-semibold text-primary mb-6">
                        📏 Tamaño de Papel
                    </h2>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="option-card p-4 border-2 border-gray-200 rounded-lg selected" 
                             data-option="paper_size" 
                             data-value="a4"
                             onclick="selectOption('paper_size', 'a4', this)">
                            <div class="text-center">
                                <div class="w-8 h-10 bg-gray-200 rounded mx-auto mb-2"></div>
                                <h3 class="font-medium text-primary">A4</h3>
                                <p class="text-xs text-secondary">210×297mm</p>
                            </div>
                        </div>

                        <div class="option-card p-4 border-2 border-gray-200 rounded-lg" 
                             data-option="paper_size" 
                             data-value="letter"
                             onclick="selectOption('paper_size', 'letter', this)">
                            <div class="text-center">
                                <div class="w-8 h-10 bg-gray-200 rounded mx-auto mb-2"></div>
                                <h3 class="font-medium text-primary">Carta</h3>
                                <p class="text-xs text-secondary">216×279mm</p>
                            </div>
                        </div>

                        <div class="option-card p-4 border-2 border-gray-200 rounded-lg" 
                             data-option="paper_size" 
                             data-value="legal"
                             onclick="selectOption('paper_size', 'legal', this)">
                            <div class="text-center">
                                <div class="w-8 h-12 bg-gray-200 rounded mx-auto mb-2"></div>
                                <h3 class="font-medium text-primary">Legal</h3>
                                <p class="text-xs text-secondary">216×356mm</p>
                            </div>
                        </div>

                        <div class="option-card p-4 border-2 border-gray-200 rounded-lg" 
                             data-option="paper_size" 
                             data-value="a3"
                             onclick="selectOption('paper_size', 'a3', this)">
                            <div class="text-center">
                                <div class="w-10 h-12 bg-gray-200 rounded mx-auto mb-2"></div>
                                <h3 class="font-medium text-primary">A3</h3>
                                <p class="text-xs text-secondary">297×420mm</p>
                                <p class="text-xs text-orange-600">+$0.25</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advanced Options -->
                <div class="config-section card">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-primary">
                            🔧 Opciones Avanzadas
                        </h2>
                        <button onclick="toggleAdvancedOptions()" 
                                class="text-primary-blue hover:text-primary-dark text-sm">
                            <span id="advanced-toggle-text">Mostrar</span> ▼
                        </button>
                    </div>

                    <div class="advanced-options" id="advanced-options">
                        <div class="space-y-6">
                            <!-- Orientation -->
                            <div>
                                <label class="block text-sm font-medium text-primary mb-3">Orientación</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="option-card p-3 border-2 border-gray-200 rounded-lg selected" 
                                         data-option="orientation" 
                                         data-value="portrait"
                                         onclick="selectOption('orientation', 'portrait', this)">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-6 h-8 bg-gray-200 rounded"></div>
                                            <span class="text-sm font-medium">Vertical</span>
                                        </div>
                                    </div>
                                    <div class="option-card p-3 border-2 border-gray-200 rounded-lg" 
                                         data-option="orientation" 
                                         data-value="landscape"
                                         onclick="selectOption('orientation', 'landscape', this)">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-6 bg-gray-200 rounded"></div>
                                            <span class="text-sm font-medium">Horizontal</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Duplex -->
                            <div>
                                <label class="block text-sm font-medium text-primary mb-3">Impresión</label>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="option-card p-3 border-2 border-gray-200 rounded-lg selected" 
                                         data-option="duplex" 
                                         data-value="simplex"
                                         onclick="selectOption('duplex', 'simplex', this)">
                                        <div class="text-center">
                                            <span class="text-sm font-medium">Una cara</span>
                                        </div>
                                    </div>
                                    <div class="option-card p-3 border-2 border-gray-200 rounded-lg" 
                                         data-option="duplex" 
                                         data-value="duplex_long"
                                         onclick="selectOption('duplex', 'duplex_long', this)">
                                        <div class="text-center">
                                            <span class="text-sm font-medium">Doble cara (largo)</span>
                                        </div>
                                    </div>
                                    <div class="option-card p-3 border-2 border-gray-200 rounded-lg" 
                                         data-option="duplex" 
                                         data-value="duplex_short"
                                         onclick="selectOption('duplex', 'duplex_short', this)">
                                        <div class="text-center">
                                            <span class="text-sm font-medium">Doble cara (corto)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Scaling -->
                            <div>
                                <label class="block text-sm font-medium text-primary mb-3">
                                    Escala: <span id="scale-value">100%</span>
                                </label>
                                <input type="range" 
                                       class="range-slider w-full" 
                                       min="50" 
                                       max="200" 
                                       value="100" 
                                       step="5"
                                       oninput="updateScale(this.value)">
                                <div class="flex justify-between text-xs text-secondary mt-1">
                                    <span>50%</span>
                                    <span>100%</span>
                                    <span>200%</span>
                                </div>
                            </div>

                            <!-- Margins -->
                            <div>
                                <label class="block text-sm font-medium text-primary mb-3">Márgenes</label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div class="option-card p-3 border-2 border-gray-200 rounded-lg" 
                                         data-option="margins" 
                                         data-value="none"
                                         onclick="selectOption('margins', 'none', this)">
                                        <div class="text-center text-sm">Sin márgenes</div>
                                    </div>
                                    <div class="option-card p-3 border-2 border-gray-200 rounded-lg selected" 
                                         data-option="margins" 
                                         data-value="normal"
                                         onclick="selectOption('margins', 'normal', this)">
                                        <div class="text-center text-sm">Normal</div>
                                    </div>
                                    <div class="option-card p-3 border-2 border-gray-200 rounded-lg" 
                                         data-option="margins" 
                                         data-value="narrow"
                                         onclick="selectOption('margins', 'narrow', this)">
                                        <div class="text-center text-sm">Estrechos</div>
                                    </div>
                                    <div class="option-card p-3 border-2 border-gray-200 rounded-lg" 
                                         data-option="margins" 
                                         data-value="wide"
                                         onclick="selectOption('margins', 'wide', this)">
                                        <div class="text-center text-sm">Amplios</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Price Calculator -->
            <div class="lg:col-span-1">
                <div class="price-calculator card">
                    <h2 class="text-xl font-semibold text-primary mb-6">
                        💰 Calculadora de Precio
                    </h2>

                    <!-- Summary -->
                    <div class="space-y-4 mb-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary">Páginas totales:</span>
                            <span class="font-medium text-primary" id="total-pages">{{ array_sum(array_column($files, 'pages')) }}</span>
                        </div>
                        
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary">Copias totales:</span>
                            <span class="font-medium text-primary" id="total-copies">{{ count($files) }}</span>
                        </div>
                        
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary">Calidad:</span>
                            <span class="font-medium text-primary" id="selected-quality">Normal</span>
                        </div>
                        
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary">Color:</span>
                            <span class="font-medium text-primary" id="selected-color">Blanco y Negro</span>
                        </div>
                        
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary">Papel:</span>
                            <span class="font-medium text-primary" id="selected-paper">A4</span>
                        </div>
                    </div>

                    <!-- Price Breakdown -->
                    <div class="border-t border-gray-200 pt-4 mb-6">
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-secondary">Precio base:</span>
                                <span class="text-primary" id="base-price">$2.50</span>
                            </div>
                            
                            <div class="flex justify-between text-sm">
                                <span class="text-secondary">Calidad:</span>
                                <span class="text-primary" id="quality-price">$0.00</span>
                            </div>
                            
                            <div class="flex justify-between text-sm">
                                <span class="text-secondary">Color:</span>
                                <span class="text-primary" id="color-price">$0.00</span>
                            </div>
                            
                            <div class="flex justify-between text-sm">
                                <span class="text-secondary">Papel especial:</span>
                                <span class="text-primary" id="paper-price">$0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="border-t border-gray-200 pt-4 mb-6">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold text-primary">Total:</span>
                            <span class="text-2xl font-bold text-primary" id="total-price">$2.50</span>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-primary mb-3">Método de Pago</label>
                        <div class="space-y-2">
                            <div class="option-card p-3 border-2 border-gray-200 rounded-lg selected" 
                                 data-option="payment" 
                                 data-value="cash"
                                 onclick="selectOption('payment', 'cash', this)">
                                <div class="flex items-center space-x-3">
                                    <span class="text-lg">💵</span>
                                    <span class="text-sm font-medium">Efectivo</span>
                                </div>
                            </div>
                            
                            <div class="option-card p-3 border-2 border-gray-200 rounded-lg" 
                                 data-option="payment" 
                                 data-value="card"
                                 onclick="selectOption('payment', 'card', this)">
                                <div class="flex items-center space-x-3">
                                    <span class="text-lg">💳</span>
                                    <span class="text-sm font-medium">Tarjeta</span>
                                </div>
                            </div>
                            
                            <div class="option-card p-3 border-2 border-gray-200 rounded-lg" 
                                 data-option="payment" 
                                 data-value="transfer"
                                 onclick="selectOption('payment', 'transfer', this)">
                                <div class="flex items-center space-x-3">
                                    <span class="text-lg">📱</span>
                                    <span class="text-sm font-medium">Transferencia</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Special Instructions -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-primary mb-2">
                            Instrucciones Especiales
                        </label>
                        <textarea class="w-full p-3 border border-gray-300 rounded-lg text-sm" 
                                  rows="3" 
                                  placeholder="Ej: Grapado, perforado, etc."
                                  id="special-instructions"></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-3">
                        <button onclick="continueToConfirmation()" 
                                class="w-full btn-primary py-3">
                            Continuar a Confirmación →
                        </button>
                        
                        <button onclick="saveConfiguration()" 
                                class="w-full btn-secondary py-2 text-sm">
                            💾 Guardar Configuración
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
            <button onclick="goBack()" 
                    class="btn-secondary px-8 py-3">
                ← Volver a Archivos
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Variables globales
    let sessionEndTime = new Date('{{ $session->expires_at ?? date("Y-m-d H:i:s", strtotime("+25 minutes")) }}');
    let sessionTimer = null;
    let printConfiguration = {
        quality: 'normal',
        color: 'bw',
        paper_size: 'a4',
        orientation: 'portrait',
        duplex: 'simplex',
        scale: 100,
        margins: 'normal',
        payment: 'cash',
        files: @json($files),
        special_instructions: ''
    };

    // Precios base
    const pricing = {
        quality: {
            draft: 0.05,
            normal: 0.10,
            high: 0.20
        },
        color: {
            bw: 0,
            color: 0.15
        },
        paper_size: {
            a4: 0,
            letter: 0,
            legal: 0.05,
            a3: 0.25
        }
    };

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
    }

    // Seleccionar opción
    function selectOption(category, value, element) {
        // Remover selección anterior en la categoría
        document.querySelectorAll(`[data-option="${category}"]`).forEach(el => {
            el.classList.remove('selected');
        });
        
        // Seleccionar nueva opción
        element.classList.add('selected');
        printConfiguration[category] = value;
        
        // Actualizar calculadora
        updatePriceCalculator();
        
        // Actualizar display específico
        updateDisplayValues(category, value);
    }

    // Actualizar valores mostrados
    function updateDisplayValues(category, value) {
        const displayMap = {
            quality: {
                draft: 'Borrador',
                normal: 'Normal',
                high: 'Alta'
            },
            color: {
                bw: 'Blanco y Negro',
                color: 'Color'
            },
            paper_size: {
                a4: 'A4',
                letter: 'Carta',
                legal: 'Legal',
                a3: 'A3'
            }
        };
        
        if (displayMap[category] && displayMap[category][value]) {
            const elementId = `selected-${category.replace('_', '-')}`;
            const element = document.getElementById(elementId);
            if (element) {
                element.textContent = displayMap[category][value];
            }
        }
    }

    // Actualizar rango de páginas
    function updatePageRange(fileId, rangeType) {
        const customInput = document.querySelector(`[data-file-id="${fileId}"].custom-range-input`);
        
        if (rangeType === 'custom') {
            customInput.classList.remove('hidden');
            customInput.focus();
        } else {
            customInput.classList.add('hidden');
        }
        
        // Actualizar configuración del archivo
        const fileIndex = printConfiguration.files.findIndex(f => f.id === fileId);
        if (fileIndex !== -1) {
            printConfiguration.files[fileIndex].page_range = rangeType;
            if (rangeType !== 'custom') {
                printConfiguration.files[fileIndex].custom_range = '';
            }
        }
        
        updatePriceCalculator();
    }

    // Actualizar copias
    function updateCopies(fileId, copies) {
        const fileIndex = printConfiguration.files.findIndex(f => f.id === fileId);
        if (fileIndex !== -1) {
            printConfiguration.files[fileIndex].copies = parseInt(copies);
        }
        
        updatePriceCalculator();
    }

    // Actualizar escala
    function updateScale(value) {
        document.getElementById('scale-value').textContent = value + '%';
        printConfiguration.scale = parseInt(value);
    }

    // Toggle opciones avanzadas
    function toggleAdvancedOptions() {
        const options = document.getElementById('advanced-options');
        const toggleText = document.getElementById('advanced-toggle-text');
        
        if (options.classList.contains('expanded')) {
            options.classList.remove('expanded');
            toggleText.textContent = 'Mostrar';
        } else {
            options.classList.add('expanded');
            toggleText.textContent = 'Ocultar';
        }
    }

    // Actualizar calculadora de precio
    function updatePriceCalculator() {
        let totalPages = 0;
        let totalCopies = 0;
        
        // Calcular páginas y copias totales
        printConfiguration.files.forEach(file => {
            const copies = file.copies || 1;
            let pages = file.pages;
            
            // Ajustar páginas según el rango seleccionado
            if (file.page_range === 'odd' || file.page_range === 'even') {
                pages = Math.ceil(pages / 2);
            } else if (file.page_range === 'custom' && file.custom_range) {
                // Simplificado: asumir que el rango personalizado es válido
                pages = file.pages; // En una implementación real, parsear el rango
            }
            
            totalPages += pages * copies;
            totalCopies += copies;
        });
        
        // Calcular precios
        const qualityPrice = totalPages * pricing.quality[printConfiguration.quality];
        const colorPrice = totalPages * pricing.color[printConfiguration.color];
        const paperPrice = totalPages * pricing.paper_size[printConfiguration.paper_size];
        const basePrice = totalPages * 0.05; // Precio base mínimo
        
        const total = basePrice + qualityPrice + colorPrice + paperPrice;
        
        // Actualizar display
        document.getElementById('total-pages').textContent = totalPages;
        document.getElementById('total-copies').textContent = totalCopies;
        document.getElementById('base-price').textContent = '$' + basePrice.toFixed(2);
        document.getElementById('quality-price').textContent = '$' + qualityPrice.toFixed(2);
        document.getElementById('color-price').textContent = '$' + colorPrice.toFixed(2);
        document.getElementById('paper-price').textContent = '$' + paperPrice.toFixed(2);
        document.getElementById('total-price').textContent = '$' + total.toFixed(2);
    }

    // Guardar configuración
    async function saveConfiguration() {
        try {
            printConfiguration.special_instructions = document.getElementById('special-instructions').value;
            
            const response = await fetch('/api/print/save-config', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({
                    session_id: '{{ $session->session_id }}',
                    configuration: printConfiguration
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showNotification('Configuración guardada', 'success');
            } else {
                throw new Error(data.message || 'Error al guardar configuración');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al guardar configuración', 'error');
        }
    }

    // Continuar a confirmación
    function continueToConfirmation() {
        printConfiguration.special_instructions = document.getElementById('special-instructions').value;
        
        // Guardar en sessionStorage
        sessionStorage.setItem('printConfiguration', JSON.stringify(printConfiguration));
        
        // Redirigir
        window.location.href = `/session/{{ $session->session_id }}/confirm`;
    }

    // Volver atrás
    function goBack() {
        window.location.href = `/session/{{ $session->session_id }}`;
    }

    // Inicialización
    document.addEventListener('DOMContentLoaded', function() {
        initSessionTimer();
        updatePriceCalculator();
        
        // Configurar eventos para rangos personalizados
        document.querySelectorAll('.custom-range-input').forEach(input => {
            input.addEventListener('input', function() {
                const fileId = this.dataset.fileId;
                const fileIndex = printConfiguration.files.findIndex(f => f.id === fileId);
                if (fileIndex !== -1) {
                    printConfiguration.files[fileIndex].custom_range = this.value;
                    updatePriceCalculator();
                }
            });
        });
    });

    // Limpiar timer al salir
    window.addEventListener('beforeunload', function() {
        if (sessionTimer) {
            clearInterval(sessionTimer);
        }
    });
</script>
@endpush