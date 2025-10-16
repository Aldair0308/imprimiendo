@extends('layouts.app')

@section('title', 'Sesión de Impresión - ' . $session->session_code)
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

    .status-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }
    
    .status-online { background-color: #10b981; }
    .status-busy { background-color: #f59e0b; }
    .status-offline { background-color: #ef4444; }
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
                        Código: <span class="font-mono text-primary font-bold">{{ $session->session_code }}</span>
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
                            PDF, DOCX (máx. {{ $limits['max_file_size'] ?? '50' }}MB)
                        </span>
                    </div>

                    <!-- File Drop Zone -->
                    <div class="file-drop-zone rounded-lg p-8 text-center mb-6" 
                         id="file-drop-zone"
                         ondrop="handleFileDrop(event)" 
                         ondragover="handleDragOver(event)"
                         ondragleave="handleDragLeave(event)">
                        
                        <div class="mb-4">
                            <svg class="w-12 h-12 text-muted mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                        </div>
                        
                        <p class="text-lg font-medium text-primary mb-2">
                            Arrastra tus archivos aquí
                        </p>
                        <p class="text-secondary mb-4">
                            o haz clic para seleccionar
                        </p>
                        
                        <input type="file" 
                               id="file-input" 
                               multiple 
                               accept=".pdf,.docx,.doc,.txt,.jpg,.jpeg,.png"
                               class="hidden"
                               onchange="handleFileSelect(event)">
                        
                        <button onclick="document.getElementById('file-input').click()" 
                                class="btn btn-primary">
                            Seleccionar Archivos
                        </button>
                    </div>

                    <!-- Upload Progress -->
                    <div id="upload-progress" class="hidden mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-secondary">Subiendo archivos...</span>
                            <span class="text-sm text-primary" id="upload-percentage">0%</span>
                        </div>
                        <div class="w-full bg-light rounded-full h-2">
                            <div class="bg-primary-blue h-2 rounded-full upload-progress" 
                                 id="progress-bar" style="width: 0%"></div>
                        </div>
                    </div>

                    <!-- File List -->
                    <div id="file-list" class="space-y-3">
                        @if($files && count($files) > 0)
                            @foreach($files as $file)
                            <div class="file-item bg-light rounded-lg p-4 flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-primary-blue rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-primary">{{ $file->original_name }}</p>
                                        <p class="text-sm text-secondary">{{ number_format($file->file_size / 1024, 1) }} KB</p>
                                    </div>
                                </div>
                                <button onclick="removeFile('{{ $file->id }}')" 
                                        class="text-red-500 hover:text-red-700 p-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                            @endforeach
                        @else
                            <div class="text-center py-8 text-secondary">
                                <svg class="w-12 h-12 mx-auto mb-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p>No hay archivos subidos aún</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Printer Selection & Settings -->
            <div class="space-y-6">
                <!-- Printer Selection -->
                <div class="card">
                    <h2 class="text-xl font-semibold text-primary mb-6">
                        🖨️ Seleccionar Impresora
                    </h2>
                    
                    <div class="space-y-4" id="printer-list">
                        @if($availablePrinters && count($availablePrinters) > 0)
                            @foreach($availablePrinters as $printer)
                            <div class="printer-selector border border-light rounded-lg p-4 cursor-pointer"
                                 onclick="selectPrinter('{{ $printer->id }}')"
                                 data-printer-id="{{ $printer->id }}">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 bg-primary-blue rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="font-medium text-primary">{{ $printer->name }}</h3>
                                            <p class="text-sm text-secondary">{{ $printer->location }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="flex items-center mb-1">
                                            <span class="status-indicator status-{{ $printer->status->status ?? 'offline' }}"></span>
                                            <span class="text-sm text-secondary capitalize">
                                                {{ $printer->status->status ?? 'Desconectada' }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-muted">
                                            Cola: {{ $printer->queue_count ?? 0 }} trabajos
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="text-center py-8 text-secondary">
                                <svg class="w-12 h-12 mx-auto mb-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                <p>No hay impresoras disponibles</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Print Settings -->
                <div class="card">
                    <h2 class="text-xl font-semibold text-primary mb-6">
                        ⚙️ Configuración de Impresión
                    </h2>
                    
                    <div class="space-y-4">
                        <!-- Copies -->
                        <div>
                            <label class="block text-sm font-medium text-primary mb-2">
                                Número de copias
                            </label>
                            <input type="number" 
                                   id="copies" 
                                   min="1" 
                                   max="10" 
                                   value="1"
                                   class="w-full px-3 py-2 border border-light rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-blue">
                        </div>

                        <!-- Color Mode -->
                        <div>
                            <label class="block text-sm font-medium text-primary mb-2">
                                Modo de color
                            </label>
                            <select id="color-mode" class="w-full px-3 py-2 border border-light rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-blue">
                                <option value="color">Color</option>
                                <option value="grayscale">Escala de grises</option>
                                <option value="bw">Blanco y negro</option>
                            </select>
                        </div>

                        <!-- Paper Size -->
                        <div>
                            <label class="block text-sm font-medium text-primary mb-2">
                                Tamaño de papel
                            </label>
                            <select id="paper-size" class="w-full px-3 py-2 border border-light rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-blue">
                                <option value="A4">A4</option>
                                <option value="Letter">Carta</option>
                                <option value="Legal">Legal</option>
                            </select>
                        </div>

                        <!-- Orientation -->
                        <div>
                            <label class="block text-sm font-medium text-primary mb-2">
                                Orientación
                            </label>
                            <div class="flex space-x-4">
                                <label class="flex items-center">
                                    <input type="radio" name="orientation" value="portrait" checked class="mr-2">
                                    <span class="text-sm text-secondary">Vertical</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="orientation" value="landscape" class="mr-2">
                                    <span class="text-sm text-secondary">Horizontal</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-4">
                    <button id="print-button" 
                            onclick="startPrinting()" 
                            disabled
                            class="w-full btn btn-primary btn-lg disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Iniciar Impresión
                    </button>
                    
                    <a href="{{ route('home') }}" 
                       class="block w-full text-center btn btn-secondary">
                        Cancelar y Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let selectedPrinter = null;
let uploadedFiles = [];

// Session timer
function updateSessionTimer() {
    const timerElement = document.getElementById('session-timer');
    if (timerElement) {
        // Aquí podrías implementar la lógica del timer real
        // Por ahora solo es visual
    }
}

// File handling
function handleDragOver(e) {
    e.preventDefault();
    document.getElementById('file-drop-zone').classList.add('dragover');
}

function handleDragLeave(e) {
    e.preventDefault();
    document.getElementById('file-drop-zone').classList.remove('dragover');
}

function handleFileDrop(e) {
    e.preventDefault();
    document.getElementById('file-drop-zone').classList.remove('dragover');
    
    const files = e.dataTransfer.files;
    handleFiles(files);
}

function handleFileSelect(e) {
    const files = e.target.files;
    handleFiles(files);
}

function handleFiles(files) {
    const maxSize = {{ ($limits['max_file_size'] ?? 50) * 1024 * 1024 }}; // Convert MB to bytes
    const allowedTypes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword', 'text/plain', 'image/jpeg', 'image/png', 'image/jpg'];
    
    for (let file of files) {
        if (file.size > maxSize) {
            showNotification(`El archivo ${file.name} es demasiado grande. Máximo {{ $limits['max_file_size'] ?? 50 }}MB.`, 'error');
            continue;
        }
        
        if (!allowedTypes.includes(file.type)) {
            showNotification(`El archivo ${file.name} no es un tipo permitido.`, 'error');
            continue;
        }
        
        uploadFile(file);
    }
}

function uploadFile(file) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('session_code', '{{ $session->session_code }}');
    formData.append('_token', '{{ csrf_token() }}');
    
    // Show progress
    document.getElementById('upload-progress').classList.remove('hidden');
    
    fetch('{{ route("session.upload") }}', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            addFileToList(data.file);
            updatePrintButton();
        } else {
            showNotification('Error al subir el archivo: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al subir el archivo', 'error');
    })
    .finally(() => {
        document.getElementById('upload-progress').classList.add('hidden');
    });
}

function addFileToList(file) {
    const fileList = document.getElementById('file-list');
    const emptyMessage = fileList.querySelector('.text-center');
    if (emptyMessage) {
        emptyMessage.remove();
    }
    
    const fileElement = document.createElement('div');
    fileElement.className = 'file-item bg-light rounded-lg p-4 flex items-center justify-between';
    fileElement.innerHTML = `
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-primary-blue rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div>
                <p class="font-medium text-primary">${file.original_name}</p>
                <p class="text-sm text-secondary">${(file.file_size / 1024).toFixed(1)} KB</p>
            </div>
        </div>
        <button onclick="removeFile('${file.id}')" class="text-red-500 hover:text-red-700 p-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
        </button>
    `;
    
    fileList.appendChild(fileElement);
    uploadedFiles.push(file);
}

function removeFile(fileId) {
    if (confirm('¿Estás seguro de que quieres eliminar este archivo?')) {
        fetch(`{{ route("session.remove-file") }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                file_id: fileId,
                session_code: '{{ $session->session_code }}'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Refresh to update file list
            } else {
                showNotification('Error al eliminar el archivo', 'error');
            }
        });
    }
}

// Printer selection
function selectPrinter(printerId) {
    // Remove previous selection
    document.querySelectorAll('.printer-selector').forEach(el => {
        el.classList.remove('selected');
    });
    
    // Add selection to clicked printer
    const printerElement = document.querySelector(`[data-printer-id="${printerId}"]`);
    printerElement.classList.add('selected');
    
    selectedPrinter = printerId;
    updatePrintButton();
}

function updatePrintButton() {
    const printButton = document.getElementById('print-button');
    const hasFiles = uploadedFiles.length > 0 || document.querySelectorAll('.file-item').length > 0;
    const hasPrinter = selectedPrinter !== null;
    
    printButton.disabled = !(hasFiles && hasPrinter);
}

function startPrinting() {
    if (!selectedPrinter) {
        showNotification('Por favor selecciona una impresora', 'warning');
        return;
    }
    
    const hasFiles = uploadedFiles.length > 0 || document.querySelectorAll('.file-item').length > 0;
    if (!hasFiles) {
        showNotification('Por favor sube al menos un archivo', 'warning');
        return;
    }
    
    const printSettings = {
        printer_id: selectedPrinter,
        copies: document.getElementById('copies').value,
        color_mode: document.getElementById('color-mode').value,
        paper_size: document.getElementById('paper-size').value,
        orientation: document.querySelector('input[name="orientation"]:checked').value,
        session_code: '{{ $session->session_code }}'
    };
    
    // Disable button and show loading
    const printButton = document.getElementById('print-button');
    printButton.disabled = true;
    printButton.innerHTML = `
        <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Procesando...
    `;
    
    fetch('{{ route("session.print") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(printSettings)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('¡Impresión iniciada correctamente!', 'success');
            setTimeout(() => {
                window.location.href = '{{ route("home") }}';
            }, 2000);
        } else {
            showNotification('Error al iniciar la impresión: ' + data.message, 'error');
            printButton.disabled = false;
            printButton.innerHTML = `
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Iniciar Impresión
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al procesar la solicitud', 'error');
        printButton.disabled = false;
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updatePrintButton();
    setInterval(updateSessionTimer, 1000);
});
</script>
@endpush
@endsection