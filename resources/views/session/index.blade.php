@extends('layouts.app')

@section('title', 'Sesión de Impresión - ' . $session->session_id)
@section('description', 'Sube tus documentos y selecciona la impresora para comenzar el proceso de impresión.')

@push('styles')
<style>
    .file-drop-zone {
        border: 2px dashed var(--border-light);
        transition: all var(--transition-normal);
    }
    
    .file-drop-zone.dragover {
        border-color: var(--primary-blue);
        background-color: var(--bg-blue-light);
        transform: scale(1.02);
    }
    
    .file-item {
        transition: all var(--transition-normal);
    }
    
    .file-item:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }
    
    .printer-selector {
        transition: all var(--transition-normal);
    }
    
    .printer-selector:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }
    
    .printer-selector.selected {
        border-color: var(--primary-blue);
        background-color: var(--bg-blue-light);
        box-shadow: var(--shadow-lg);
    }
    
    .upload-progress {
        transition: width var(--transition-slow);
    }
    
    .session-timer {
        animation: pulse 2s infinite;
    }
    
    .file-preview {
        max-height: 200px;
        overflow-y: auto;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-secondary py-8 px-4">
    <div class="max-w-6xl mx-auto">
        <!-- Session Header -->
        <div class="bg-primary rounded-xl shadow-lg p-6 mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="mb-4 md:mb-0">
                    <h1 class="text-2xl md:text-3xl font-bold text-primary mb-2">
                        🖨️ Sesión de Impresión
                    </h1>
                    <p class="text-secondary">
                        ID: <span class="font-mono text-primary">{{ $session->session_id }}</span>
                    </p>
                </div>
                
                <!-- Session Timer -->
                <div class="bg-white rounded-lg p-4 text-center">
                    <div class="text-sm text-secondary mb-1">Tiempo restante</div>
                    <div class="text-2xl font-bold text-primary session-timer" id="session-timer">
                        {{ $session->remaining_time ?? '29:45' }}
                    </div>
                    <div class="text-xs text-muted">minutos</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- File Upload Section -->
            <div class="space-y-6">
                <div class="card">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-primary">
                            📄 Subir Documentos
                        </h2>
                        <span class="text-sm text-secondary">
                            PDF, DOCX (máx. 50MB)
                        </span>
                    </div>

                    <!-- File Drop Zone -->
                    <div class="file-drop-zone rounded-lg p-8 text-center mb-6" 
                         id="file-drop-zone"
                         ondrop="handleFileDrop(event)" 
                         ondragover="handleDragOver(event)"
                         ondragleave="handleDragLeave(event)">
                        <div class="mb-4">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-2xl">📁</span>
                            </div>
                            <h3 class="text-lg font-semibold text-primary mb-2">
                                Arrastra tus archivos aquí
                            </h3>
                            <p class="text-secondary mb-4">
                                o haz clic para seleccionar archivos
                            </p>
                        </div>
                        
                        <input type="file" 
                               id="file-input" 
                               class="hidden" 
                               multiple 
                               accept=".pdf,.docx,.doc"
                               onchange="handleFileSelect(event)">
                        
                        <button type="button" 
                                class="btn-primary"
                                onclick="document.getElementById('file-input').click()">
                            Seleccionar Archivos
                        </button>
                    </div>

                    <!-- Upload Progress -->
                    <div id="upload-progress" class="hidden mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-secondary">Subiendo archivos...</span>
                            <span class="text-sm text-primary" id="upload-percentage">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-primary-blue h-2 rounded-full upload-progress" 
                                 id="progress-bar" style="width: 0%"></div>
                        </div>
                    </div>

                    <!-- File List -->
                    <div id="file-list" class="space-y-3">
                        @if(isset($files) && count($files) > 0)
                            @foreach($files as $file)
                            <div class="file-item bg-gray-50 rounded-lg p-4 flex items-center justify-between" 
                                 data-file-id="{{ $file['id'] }}">
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
                                            {{ $file['size'] }} • {{ $file['pages'] ?? 'N/A' }} páginas
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                        ✓ Listo
                                    </span>
                                    <button onclick="removeFile('{{ $file['id'] }}')" 
                                            class="text-red-500 hover:text-red-700 p-1">
                                        🗑️
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        @endif
                    </div>

                    <!-- File Summary -->
                    <div id="file-summary" class="mt-6 p-4 bg-blue-50 rounded-lg {{ isset($files) && count($files) > 0 ? '' : 'hidden' }}">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-secondary">Total de archivos:</span>
                            <span class="font-medium text-primary" id="total-files">{{ count($files ?? []) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm mt-1">
                            <span class="text-secondary">Total de páginas:</span>
                            <span class="font-medium text-primary" id="total-pages">{{ array_sum(array_column($files ?? [], 'pages')) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Printer Selection Section -->
            <div class="space-y-6">
                <div class="card">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-primary">
                            🖨️ Seleccionar Impresora
                        </h2>
                        <button onclick="refreshPrinters()" 
                                class="text-primary-blue hover:text-primary-dark text-sm">
                            🔄 Actualizar
                        </button>
                    </div>

                    <!-- Printer Grid -->
                    <div class="space-y-4" id="printer-list">
                        @forelse($printers ?? [] as $printer)
                        <div class="printer-selector card-hover cursor-pointer" 
                             data-printer-id="{{ $printer['id'] }}"
                             onclick="selectPrinter('{{ $printer['id'] }}')">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                        <span class="text-lg">🖨️</span>
                                    </div>
                                    
                                    <div>
                                        <h3 class="font-semibold text-primary">{{ $printer['name'] }}</h3>
                                        <p class="text-sm text-secondary">{{ $printer['model'] ?? 'EPSON L325' }}</p>
                                        
                                        <!-- Printer Status -->
                                        <div class="flex items-center space-x-2 mt-1">
                                            <div class="status-indicator 
                                                @switch($printer['status'])
                                                    @case('online') status-online @break
                                                    @case('offline') status-offline @break
                                                    @case('error') status-error @break
                                                    @case('maintenance') status-maintenance @break
                                                    @case('busy') status-busy @break
                                                    @default status-offline
                                                @endswitch"></div>
                                            <span class="text-xs
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
                                </div>
                                
                                <div class="text-right">
                                    <!-- Queue Info -->
                                    <div class="text-sm text-secondary mb-1">
                                        Cola: {{ $printer['queue_count'] ?? 0 }} trabajos
                                    </div>
                                    
                                    <!-- Capabilities -->
                                    <div class="flex space-x-1">
                                        @if($printer['supports_color'] ?? true)
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">Color</span>
                                        @endif
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full">B&N</span>
                                    </div>
                                    
                                    <!-- Speed -->
                                    <div class="text-xs text-muted mt-1">
                                        {{ $printer['speed'] ?? '15' }} ppm
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Selection Indicator -->
                            <div class="hidden selected-indicator mt-3 p-2 bg-blue-50 rounded-lg">
                                <div class="flex items-center space-x-2">
                                    <span class="text-blue-600">✓</span>
                                    <span class="text-sm text-blue-800 font-medium">Impresora seleccionada</span>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-2xl">🖨️</span>
                            </div>
                            <h3 class="text-lg font-semibold text-primary mb-2">No hay impresoras disponibles</h3>
                            <p class="text-secondary">Intenta actualizar la lista o contacta al administrador.</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <h3 class="text-lg font-semibold text-primary mb-4">⚡ Acciones Rápidas</h3>
                    
                    <div class="space-y-3">
                        <button onclick="selectBestPrinter()" 
                                class="w-full btn-secondary text-left flex items-center justify-between">
                            <span>🎯 Seleccionar mejor impresora</span>
                            <span class="text-xs text-muted">Automático</span>
                        </button>
                        
                        <button onclick="showPrinterDetails()" 
                                class="w-full btn-secondary text-left flex items-center justify-between">
                            <span>📊 Ver detalles de impresoras</span>
                            <span class="text-xs text-muted">Info</span>
                        </button>
                        
                        <button onclick="refreshSession()" 
                                class="w-full btn-secondary text-left flex items-center justify-between">
                            <span>🔄 Extender sesión</span>
                            <span class="text-xs text-muted">+15 min</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
            <button onclick="goBack()" 
                    class="btn-secondary px-8 py-3">
                ← Volver al Inicio
            </button>
            
            <button id="continue-btn" 
                    onclick="continueToConfiguration()" 
                    class="btn-primary px-8 py-3 disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled>
                Continuar a Configuración →
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Variables globales
    let selectedPrinter = null;
    let uploadedFiles = @json($files ?? []);
    let sessionTimer = null;
    let sessionEndTime = new Date('{{ $session->expires_at ?? date("Y-m-d H:i:s", strtotime("+30 minutes")) }}');

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

    // Manejo de drag and drop
    function handleDragOver(event) {
        event.preventDefault();
        document.getElementById('file-drop-zone').classList.add('dragover');
    }

    function handleDragLeave(event) {
        event.preventDefault();
        document.getElementById('file-drop-zone').classList.remove('dragover');
    }

    function handleFileDrop(event) {
        event.preventDefault();
        document.getElementById('file-drop-zone').classList.remove('dragover');
        
        const files = event.dataTransfer.files;
        processFiles(files);
    }

    // Manejo de selección de archivos
    function handleFileSelect(event) {
        const files = event.target.files;
        processFiles(files);
    }

    // Procesar archivos seleccionados
    async function processFiles(files) {
        const validFiles = [];
        const maxSize = 50 * 1024 * 1024; // 50MB
        
        for (let file of files) {
            // Validar tipo de archivo
            if (!file.type.includes('pdf') && !file.name.endsWith('.docx') && !file.name.endsWith('.doc')) {
                showNotification(`Archivo ${file.name} no es válido. Solo se permiten PDF y DOCX.`, 'error');
                continue;
            }
            
            // Validar tamaño
            if (file.size > maxSize) {
                showNotification(`Archivo ${file.name} es muy grande. Máximo 50MB.`, 'error');
                continue;
            }
            
            validFiles.push(file);
        }
        
        if (validFiles.length > 0) {
            await uploadFiles(validFiles);
        }
    }

    // Subir archivos
    async function uploadFiles(files) {
        const progressContainer = document.getElementById('upload-progress');
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('upload-percentage');
        
        progressContainer.classList.remove('hidden');
        
        const formData = new FormData();
        formData.append('session_id', '{{ $session->session_id }}');
        
        for (let file of files) {
            formData.append('files[]', file);
        }
        
        try {
            const response = await fetch('/api/files/upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Actualizar lista de archivos
                uploadedFiles = [...uploadedFiles, ...data.files];
                updateFileList();
                updateContinueButton();
                
                progressBar.style.width = '100%';
                progressText.textContent = '100%';
                
                setTimeout(() => {
                    progressContainer.classList.add('hidden');
                    progressBar.style.width = '0%';
                    progressText.textContent = '0%';
                }, 1000);
                
                showNotification(`${files.length} archivo(s) subido(s) exitosamente`, 'success');
            } else {
                throw new Error(data.message || 'Error al subir archivos');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al subir archivos: ' + error.message, 'error');
            progressContainer.classList.add('hidden');
        }
    }

    // Actualizar lista de archivos
    function updateFileList() {
        const fileList = document.getElementById('file-list');
        const fileSummary = document.getElementById('file-summary');
        
        if (uploadedFiles.length === 0) {
            fileList.innerHTML = '';
            fileSummary.classList.add('hidden');
            return;
        }
        
        fileList.innerHTML = uploadedFiles.map(file => `
            <div class="file-item bg-gray-50 rounded-lg p-4 flex items-center justify-between" 
                 data-file-id="${file.id}">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <span class="text-sm">
                            ${file.name.endsWith('.pdf') ? '📄' : '📝'}
                        </span>
                    </div>
                    <div>
                        <h4 class="font-medium text-primary">${file.name}</h4>
                        <p class="text-sm text-secondary">
                            ${file.size} • ${file.pages || 'N/A'} páginas
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                        ✓ Listo
                    </span>
                    <button onclick="removeFile('${file.id}')" 
                            class="text-red-500 hover:text-red-700 p-1">
                        🗑️
                    </button>
                </div>
            </div>
        `).join('');
        
        // Actualizar resumen
        const totalPages = uploadedFiles.reduce((sum, file) => sum + (file.pages || 0), 0);
        document.getElementById('total-files').textContent = uploadedFiles.length;
        document.getElementById('total-pages').textContent = totalPages;
        fileSummary.classList.remove('hidden');
    }

    // Remover archivo
    async function removeFile(fileId) {
        try {
            const response = await fetch(`/api/files/${fileId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                uploadedFiles = uploadedFiles.filter(file => file.id !== fileId);
                updateFileList();
                updateContinueButton();
                showNotification('Archivo eliminado', 'success');
            } else {
                throw new Error(data.message || 'Error al eliminar archivo');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al eliminar archivo', 'error');
        }
    }

    // Seleccionar impresora
    function selectPrinter(printerId) {
        // Remover selección anterior
        document.querySelectorAll('.printer-selector').forEach(el => {
            el.classList.remove('selected');
            el.querySelector('.selected-indicator').classList.add('hidden');
        });
        
        // Seleccionar nueva impresora
        const printerElement = document.querySelector(`[data-printer-id="${printerId}"]`);
        printerElement.classList.add('selected');
        printerElement.querySelector('.selected-indicator').classList.remove('hidden');
        
        selectedPrinter = printerId;
        updateContinueButton();
        
        showNotification('Impresora seleccionada', 'success');
    }

    // Seleccionar mejor impresora automáticamente
    async function selectBestPrinter() {
        try {
            const response = await fetch('/api/printers/best', {
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken
                }
            });
            
            const data = await response.json();
            
            if (data.success && data.printer) {
                selectPrinter(data.printer.id);
                showNotification(`Impresora óptima seleccionada: ${data.printer.name}`, 'success');
            } else {
                showNotification('No se pudo determinar la mejor impresora', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al seleccionar impresora automáticamente', 'error');
        }
    }

    // Actualizar estado del botón continuar
    function updateContinueButton() {
        const continueBtn = document.getElementById('continue-btn');
        const canContinue = uploadedFiles.length > 0 && selectedPrinter;
        
        continueBtn.disabled = !canContinue;
        
        if (canContinue) {
            continueBtn.classList.remove('disabled:opacity-50', 'disabled:cursor-not-allowed');
        } else {
            continueBtn.classList.add('disabled:opacity-50', 'disabled:cursor-not-allowed');
        }
    }

    // Actualizar lista de impresoras
    async function refreshPrinters() {
        try {
            const response = await fetch('/api/printers/status');
            const data = await response.json();
            
            if (data.success) {
                // Actualizar la lista de impresoras
                location.reload(); // Simplificado para esta demo
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al actualizar impresoras', 'error');
        }
    }

    // Extender sesión
    async function refreshSession() {
        try {
            const response = await fetch('/api/session/extend', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({
                    session_id: '{{ $session->session_id }}'
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                sessionEndTime = new Date(data.new_expiry);
                showNotification('Sesión extendida por 15 minutos', 'success');
            } else {
                throw new Error(data.message || 'Error al extender sesión');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al extender sesión', 'error');
        }
    }

    // Continuar a configuración
    function continueToConfiguration() {
        if (!uploadedFiles.length || !selectedPrinter) {
            showNotification('Selecciona archivos y una impresora para continuar', 'error');
            return;
        }
        
        // Guardar estado en sessionStorage
        sessionStorage.setItem('selectedPrinter', selectedPrinter);
        sessionStorage.setItem('uploadedFiles', JSON.stringify(uploadedFiles));
        
        // Redirigir a configuración
        window.location.href = `/session/{{ $session->session_id }}/configure`;
    }

    // Volver al inicio
    function goBack() {
        if (uploadedFiles.length > 0) {
            if (confirm('¿Estás seguro? Se perderán los archivos subidos.')) {
                window.location.href = '/';
            }
        } else {
            window.location.href = '/';
        }
    }

    // Mostrar detalles de impresoras
    function showPrinterDetails() {
        // Implementar modal con detalles de impresoras
        showNotification('Función en desarrollo', 'info');
    }

    // Inicialización
    document.addEventListener('DOMContentLoaded', function() {
        initSessionTimer();
        updateFileList();
        updateContinueButton();
        
        // Configurar eventos de drag and drop en toda la zona
        const dropZone = document.getElementById('file-drop-zone');
        dropZone.addEventListener('click', () => {
            document.getElementById('file-input').click();
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