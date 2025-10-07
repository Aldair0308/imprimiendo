/**
 * Imprimeindo - Sistema de Impresión con QR
 * JavaScript Principal para funcionalidades interactivas
 */

// Configuración global
window.Imprimeindo = {
    config: {
        apiUrl: '/api',
        refreshInterval: 5000,
        qrRefreshInterval: 30000,
        sessionTimeout: 1800000, // 30 minutos
        maxFileSize: 10485760, // 10MB
        allowedFileTypes: ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png']
    },
    
    // Estado global de la aplicación
    state: {
        currentSession: null,
        selectedPrinter: null,
        uploadedFiles: [],
        isLoading: false,
        timers: {}
    }
};

/**
 * Utilidades generales
 */
const Utils = {
    // Mostrar notificaciones
    showNotification(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type} fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300`;
        
        const icon = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        }[type] || 'ℹ️';
        
        notification.innerHTML = `
            <div class="flex items-center space-x-3">
                <span class="text-lg">${icon}</span>
                <span class="text-white font-medium">${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animar entrada
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Auto-remover
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => notification.remove(), 300);
        }, duration);
    },

    // Formatear bytes
    formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    },

    // Validar archivo
    validateFile(file) {
        const extension = file.name.split('.').pop().toLowerCase();
        
        if (!Imprimeindo.config.allowedFileTypes.includes(extension)) {
            throw new Error(`Tipo de archivo no permitido: ${extension}`);
        }
        
        if (file.size > Imprimeindo.config.maxFileSize) {
            throw new Error(`Archivo muy grande. Máximo: ${this.formatBytes(Imprimeindo.config.maxFileSize)}`);
        }
        
        return true;
    },

    // Realizar petición AJAX
    async request(url, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        };

        const config = { ...defaultOptions, ...options };
        
        try {
            const response = await fetch(url, config);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Error en la petición');
            }
            
            return data;
        } catch (error) {
            console.error('Error en petición:', error);
            throw error;
        }
    }
};

/**
 * Gestión de códigos QR
 */
const QRManager = {
    currentQR: null,
    refreshTimer: null,

    // Generar nuevo QR
    async generateQR() {
        try {
            Imprimeindo.state.isLoading = true;
            this.updateLoadingState();

            const response = await Utils.request('/api/session/generate', {
                method: 'POST'
            });

            if (response.success) {
                this.currentQR = response.data;
                this.displayQR(response.data);
                this.startRefreshTimer();
                Utils.showNotification('Nuevo código QR generado', 'success');
            }
        } catch (error) {
            Utils.showNotification('Error al generar QR: ' + error.message, 'error');
        } finally {
            Imprimeindo.state.isLoading = false;
            this.updateLoadingState();
        }
    },

    // Mostrar QR en la interfaz
    displayQR(qrData) {
        const qrContainer = document.getElementById('qr-container');
        const qrInfo = document.getElementById('qr-info');
        
        if (qrContainer) {
            qrContainer.innerHTML = `
                <div class="qr-code-wrapper text-center">
                    <div class="qr-code bg-white p-4 rounded-lg shadow-lg inline-block">
                        <img src="data:image/png;base64,${qrData.qr_image}" 
                             alt="Código QR" 
                             class="w-64 h-64 mx-auto">
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-gray-600">Código de sesión:</p>
                        <p class="font-mono text-lg font-bold text-blue-600">${qrData.session_token}</p>
                    </div>
                </div>
            `;
        }
        
        if (qrInfo) {
            const expiresAt = new Date(qrData.expires_at);
            qrInfo.innerHTML = `
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Expira:</span>
                        <span class="font-medium">${expiresAt.toLocaleString()}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Estado:</span>
                        <span class="font-medium text-green-600">Activo</span>
                    </div>
                </div>
            `;
        }
        
        this.startCountdown(qrData.expires_at);
    },

    // Iniciar temporizador de actualización
    startRefreshTimer() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
        }
        
        this.refreshTimer = setInterval(() => {
            this.generateQR();
        }, Imprimeindo.config.qrRefreshInterval);
    },

    // Countdown hasta expiración
    startCountdown(expiresAt) {
        const countdownElement = document.getElementById('qr-countdown');
        if (!countdownElement) return;

        const timer = setInterval(() => {
            const now = new Date().getTime();
            const expiry = new Date(expiresAt).getTime();
            const distance = expiry - now;

            if (distance < 0) {
                clearInterval(timer);
                countdownElement.innerHTML = '<span class="text-red-600 font-bold">EXPIRADO</span>';
                this.generateQR(); // Auto-renovar
                return;
            }

            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            countdownElement.innerHTML = `
                <span class="text-blue-600 font-mono">
                    ${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}
                </span>
            `;
        }, 1000);
    },

    // Actualizar estado de carga
    updateLoadingState() {
        const refreshBtn = document.getElementById('refresh-qr-btn');
        if (refreshBtn) {
            refreshBtn.disabled = Imprimeindo.state.isLoading;
            refreshBtn.innerHTML = Imprimeindo.state.isLoading 
                ? '<span class="animate-spin">⟳</span> Generando...'
                : '🔄 Nuevo QR';
        }
    }
};

/**
 * Gestión de impresoras
 */
const PrinterManager = {
    printers: [],
    statusTimer: null,

    // Cargar lista de impresoras
    async loadPrinters() {
        try {
            const response = await Utils.request('/api/printers');
            if (response.success) {
                this.printers = response.data;
                this.displayPrinters();
                this.startStatusMonitoring();
            }
        } catch (error) {
            Utils.showNotification('Error al cargar impresoras: ' + error.message, 'error');
        }
    },

    // Mostrar impresoras en la interfaz
    displayPrinters() {
        const container = document.getElementById('printers-list');
        if (!container) return;

        container.innerHTML = this.printers.map(printer => `
            <div class="printer-card bg-white rounded-lg shadow-md p-4 border-2 ${printer.is_active ? 'border-green-200' : 'border-gray-200'} 
                 ${Imprimeindo.state.selectedPrinter?.id === printer.id ? 'ring-2 ring-blue-500' : ''}"
                 onclick="PrinterManager.selectPrinter(${printer.id})">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-bold text-lg">${printer.name}</h3>
                    <span class="status-badge px-2 py-1 rounded-full text-xs font-medium
                           ${this.getStatusClass(printer.status)}">
                        ${this.getStatusText(printer.status)}
                    </span>
                </div>
                <div class="text-sm text-gray-600 space-y-1">
                    <p><strong>Modelo:</strong> ${printer.model}</p>
                    <p><strong>Ubicación:</strong> ${printer.location}</p>
                    <p><strong>Cola:</strong> ${printer.queue_count} trabajos</p>
                    ${printer.supports_color ? '<span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Color</span>' : ''}
                    ${printer.supports_duplex ? '<span class="inline-block bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Dúplex</span>' : ''}
                </div>
            </div>
        `).join('');
    },

    // Seleccionar impresora
    selectPrinter(printerId) {
        const printer = this.printers.find(p => p.id === printerId);
        if (printer && printer.is_active) {
            Imprimeindo.state.selectedPrinter = printer;
            this.displayPrinters(); // Re-renderizar para mostrar selección
            Utils.showNotification(`Impresora seleccionada: ${printer.name}`, 'success');
        } else {
            Utils.showNotification('Impresora no disponible', 'warning');
        }
    },

    // Obtener clase CSS para estado
    getStatusClass(status) {
        const classes = {
            'online': 'bg-green-100 text-green-800',
            'offline': 'bg-red-100 text-red-800',
            'busy': 'bg-yellow-100 text-yellow-800',
            'maintenance': 'bg-gray-100 text-gray-800',
            'error': 'bg-red-100 text-red-800'
        };
        return classes[status] || 'bg-gray-100 text-gray-800';
    },

    // Obtener texto para estado
    getStatusText(status) {
        const texts = {
            'online': 'En línea',
            'offline': 'Desconectada',
            'busy': 'Ocupada',
            'maintenance': 'Mantenimiento',
            'error': 'Error'
        };
        return texts[status] || 'Desconocido';
    },

    // Monitoreo de estado en tiempo real
    startStatusMonitoring() {
        if (this.statusTimer) {
            clearInterval(this.statusTimer);
        }
        
        this.statusTimer = setInterval(() => {
            this.loadPrinters();
        }, Imprimeindo.config.refreshInterval);
    }
};

/**
 * Gestión de archivos
 */
const FileManager = {
    // Manejar selección de archivos
    handleFileSelect(event) {
        const files = Array.from(event.target.files);
        
        files.forEach(file => {
            try {
                Utils.validateFile(file);
                this.addFileToQueue(file);
            } catch (error) {
                Utils.showNotification(error.message, 'error');
            }
        });
        
        // Limpiar input
        event.target.value = '';
    },

    // Agregar archivo a la cola
    addFileToQueue(file) {
        const fileData = {
            id: Date.now() + Math.random(),
            file: file,
            name: file.name,
            size: file.size,
            type: file.type,
            status: 'pending'
        };
        
        Imprimeindo.state.uploadedFiles.push(fileData);
        this.displayFiles();
        Utils.showNotification(`Archivo agregado: ${file.name}`, 'success');
    },

    // Mostrar archivos en la interfaz
    displayFiles() {
        const container = document.getElementById('files-list');
        if (!container) return;

        if (Imprimeindo.state.uploadedFiles.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <p>No hay archivos seleccionados</p>
                    <p class="text-sm">Arrastra archivos aquí o haz clic para seleccionar</p>
                </div>
            `;
            return;
        }

        container.innerHTML = Imprimeindo.state.uploadedFiles.map(file => `
            <div class="file-item bg-white rounded-lg shadow-sm border p-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="file-icon text-2xl">
                        ${this.getFileIcon(file.name)}
                    </div>
                    <div>
                        <p class="font-medium">${file.name}</p>
                        <p class="text-sm text-gray-500">${Utils.formatBytes(file.size)}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="status-badge px-2 py-1 rounded text-xs ${this.getFileStatusClass(file.status)}">
                        ${this.getFileStatusText(file.status)}
                    </span>
                    <button onclick="FileManager.removeFile('${file.id}')" 
                            class="text-red-500 hover:text-red-700">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        `).join('');
    },

    // Remover archivo
    removeFile(fileId) {
        Imprimeindo.state.uploadedFiles = Imprimeindo.state.uploadedFiles.filter(f => f.id != fileId);
        this.displayFiles();
        Utils.showNotification('Archivo removido', 'info');
    },

    // Obtener icono de archivo
    getFileIcon(filename) {
        const extension = filename.split('.').pop().toLowerCase();
        const icons = {
            'pdf': '📄',
            'doc': '📝',
            'docx': '📝',
            'txt': '📄',
            'jpg': '🖼️',
            'jpeg': '🖼️',
            'png': '🖼️'
        };
        return icons[extension] || '📄';
    },

    // Obtener clase de estado de archivo
    getFileStatusClass(status) {
        const classes = {
            'pending': 'bg-yellow-100 text-yellow-800',
            'uploading': 'bg-blue-100 text-blue-800',
            'uploaded': 'bg-green-100 text-green-800',
            'error': 'bg-red-100 text-red-800'
        };
        return classes[status] || 'bg-gray-100 text-gray-800';
    },

    // Obtener texto de estado de archivo
    getFileStatusText(status) {
        const texts = {
            'pending': 'Pendiente',
            'uploading': 'Subiendo',
            'uploaded': 'Subido',
            'error': 'Error'
        };
        return texts[status] || 'Desconocido';
    }
};

/**
 * Validaciones de formularios
 */
const FormValidator = {
    // Validar formulario de configuración de impresión
    validatePrintConfig(formData) {
        const errors = [];
        
        if (!Imprimeindo.state.selectedPrinter) {
            errors.push('Debe seleccionar una impresora');
        }
        
        if (Imprimeindo.state.uploadedFiles.length === 0) {
            errors.push('Debe seleccionar al menos un archivo');
        }
        
        if (!formData.get('copies') || formData.get('copies') < 1) {
            errors.push('Número de copias debe ser mayor a 0');
        }
        
        return errors;
    },

    // Validar formulario de login admin
    validateAdminLogin(formData) {
        const errors = [];
        
        if (!formData.get('email')) {
            errors.push('Email es requerido');
        }
        
        if (!formData.get('password')) {
            errors.push('Contraseña es requerida');
        }
        
        return errors;
    }
};

/**
 * Inicialización de la aplicación
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Imprimeindo - Sistema iniciado');
    
    // Inicializar componentes según la página actual
    const currentPage = document.body.dataset.page;
    
    switch(currentPage) {
        case 'home':
            QRManager.generateQR();
            PrinterManager.loadPrinters();
            break;
            
        case 'session':
            PrinterManager.loadPrinters();
            initializeFileUpload();
            break;
            
        case 'admin-dashboard':
            initializeAdminDashboard();
            break;
    }
    
    // Event listeners globales
    setupGlobalEventListeners();
});

/**
 * Configurar event listeners globales
 */
function setupGlobalEventListeners() {
    // Botón de refresh QR
    const refreshQRBtn = document.getElementById('refresh-qr-btn');
    if (refreshQRBtn) {
        refreshQRBtn.addEventListener('click', () => QRManager.generateQR());
    }
    
    // Input de archivos
    const fileInput = document.getElementById('file-input');
    if (fileInput) {
        fileInput.addEventListener('change', FileManager.handleFileSelect);
    }
    
    // Drag and drop para archivos
    const dropZone = document.getElementById('drop-zone');
    if (dropZone) {
        setupDragAndDrop(dropZone);
    }
}

/**
 * Configurar drag and drop
 */
function setupDragAndDrop(dropZone) {
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });
    
    dropZone.addEventListener('drop', handleDrop, false);
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    function highlight(e) {
        dropZone.classList.add('drag-over');
    }
    
    function unhighlight(e) {
        dropZone.classList.remove('drag-over');
    }
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        Array.from(files).forEach(file => {
            try {
                Utils.validateFile(file);
                FileManager.addFileToQueue(file);
            } catch (error) {
                Utils.showNotification(error.message, 'error');
            }
        });
    }
}

/**
 * Inicializar upload de archivos
 */
function initializeFileUpload() {
    FileManager.displayFiles();
}

/**
 * Inicializar dashboard administrativo
 */
function initializeAdminDashboard() {
    // Cargar estadísticas
    loadDashboardStats();
    
    // Configurar auto-refresh
    setInterval(loadDashboardStats, Imprimeindo.config.refreshInterval);
}

/**
 * Cargar estadísticas del dashboard
 */
async function loadDashboardStats() {
    try {
        const response = await Utils.request('/api/admin/stats');
        if (response.success) {
            updateDashboardStats(response.data);
        }
    } catch (error) {
        console.error('Error cargando estadísticas:', error);
    }
}

/**
 * Actualizar estadísticas en el dashboard
 */
function updateDashboardStats(stats) {
    const elements = {
        'total-printers': stats.total_printers,
        'active-printers': stats.active_printers,
        'total-jobs': stats.total_jobs,
        'pending-jobs': stats.pending_jobs
    };
    
    Object.entries(elements).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    });
}

// Exponer funciones globales
window.QRManager = QRManager;
window.PrinterManager = PrinterManager;
window.FileManager = FileManager;
window.Utils = Utils;