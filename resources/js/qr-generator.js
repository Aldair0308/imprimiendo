/**
 * Generador de códigos QR para Imprimeindo
 * Maneja la generación, actualización y gestión de códigos QR
 */

class QRGenerator {
    constructor() {
        this.currentQR = null;
        this.refreshTimer = null;
        this.countdownTimer = null;
        this.config = {
            refreshInterval: 30000, // 30 segundos
            warningTime: 300000,    // 5 minutos antes de expirar
            apiEndpoint: '/api/session/generate'
        };
    }

    /**
     * Inicializar el generador de QR
     */
    async init() {
        console.log('🔄 Inicializando generador de QR...');
        await this.generateNewQR();
        this.setupEventListeners();
        this.startAutoRefresh();
    }

    /**
     * Generar un nuevo código QR
     */
    async generateNewQR() {
        try {
            this.setLoadingState(true);
            
            const response = await fetch(this.config.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success) {
                this.currentQR = data.data;
                this.displayQR(data.data);
                this.startCountdown(data.data.expires_at);
                this.showNotification('✅ Nuevo código QR generado', 'success');
                console.log('✅ QR generado exitosamente:', data.data.session_token);
            } else {
                throw new Error(data.message || 'Error al generar QR');
            }
        } catch (error) {
            console.error('❌ Error generando QR:', error);
            this.showNotification('❌ Error al generar código QR: ' + error.message, 'error');
            this.displayError(error.message);
        } finally {
            this.setLoadingState(false);
        }
    }

    /**
     * Mostrar el código QR en la interfaz
     */
    displayQR(qrData) {
        const qrContainer = document.getElementById('qr-container');
        const qrInfo = document.getElementById('qr-info');
        
        if (qrContainer) {
            qrContainer.innerHTML = `
                <div class="qr-display text-center">
                    <div class="qr-image-container bg-white p-6 rounded-xl shadow-lg inline-block border-4 border-blue-100">
                        <img src="data:image/png;base64,${qrData.qr_image}" 
                             alt="Código QR de sesión" 
                             class="w-64 h-64 mx-auto"
                             id="qr-image">
                    </div>
                    
                    <div class="qr-details mt-6 space-y-3">
                        <div class="session-token bg-blue-50 p-4 rounded-lg">
                            <p class="text-sm text-blue-600 font-medium mb-1">Código de Sesión:</p>
                            <p class="font-mono text-xl font-bold text-blue-800 tracking-wider">${qrData.session_token}</p>
                        </div>
                        
                        <div class="qr-actions flex justify-center space-x-3">
                            <button onclick="qrGenerator.copySessionToken()" 
                                    class="copy-btn bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                📋 Copiar Código
                            </button>
                            <button onclick="qrGenerator.downloadQR()" 
                                    class="download-btn bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                💾 Descargar QR
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }
        
        if (qrInfo) {
            const expiresAt = new Date(qrData.expires_at);
            const createdAt = new Date(qrData.created_at);
            
            qrInfo.innerHTML = `
                <div class="qr-metadata grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="info-item bg-gray-50 p-3 rounded-lg">
                        <span class="text-gray-600 block mb-1">Creado:</span>
                        <span class="font-medium text-gray-800">${createdAt.toLocaleString()}</span>
                    </div>
                    <div class="info-item bg-gray-50 p-3 rounded-lg">
                        <span class="text-gray-600 block mb-1">Expira:</span>
                        <span class="font-medium text-gray-800">${expiresAt.toLocaleString()}</span>
                    </div>
                    <div class="info-item bg-green-50 p-3 rounded-lg">
                        <span class="text-green-600 block mb-1">Estado:</span>
                        <span class="font-medium text-green-800">🟢 Activo</span>
                    </div>
                    <div class="info-item bg-blue-50 p-3 rounded-lg">
                        <span class="text-blue-600 block mb-1">Tiempo restante:</span>
                        <span class="font-medium text-blue-800" id="qr-countdown">Calculando...</span>
                    </div>
                </div>
            `;
        }
    }

    /**
     * Mostrar error en la interfaz
     */
    displayError(message) {
        const qrContainer = document.getElementById('qr-container');
        if (qrContainer) {
            qrContainer.innerHTML = `
                <div class="qr-error text-center py-12">
                    <div class="error-icon text-6xl mb-4">❌</div>
                    <h3 class="text-xl font-bold text-red-600 mb-2">Error al generar código QR</h3>
                    <p class="text-gray-600 mb-6">${message}</p>
                    <button onclick="qrGenerator.generateNewQR()" 
                            class="retry-btn bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        🔄 Intentar de nuevo
                    </button>
                </div>
            `;
        }
    }

    /**
     * Iniciar cuenta regresiva hasta la expiración
     */
    startCountdown(expiresAt) {
        if (this.countdownTimer) {
            clearInterval(this.countdownTimer);
        }

        const countdownElement = document.getElementById('qr-countdown');
        if (!countdownElement) return;

        this.countdownTimer = setInterval(() => {
            const now = new Date().getTime();
            const expiry = new Date(expiresAt).getTime();
            const distance = expiry - now;

            if (distance < 0) {
                clearInterval(this.countdownTimer);
                countdownElement.innerHTML = '<span class="text-red-600 font-bold">⏰ EXPIRADO</span>';
                this.handleExpiredQR();
                return;
            }

            const hours = Math.floor(distance / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            let timeString = '';
            if (hours > 0) {
                timeString = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            } else {
                timeString = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }

            // Cambiar color según tiempo restante
            let colorClass = 'text-blue-600';
            if (distance < 300000) { // Menos de 5 minutos
                colorClass = 'text-red-600';
            } else if (distance < 600000) { // Menos de 10 minutos
                colorClass = 'text-yellow-600';
            }

            countdownElement.innerHTML = `<span class="${colorClass} font-mono font-bold">⏱️ ${timeString}</span>`;

            // Mostrar advertencia cuando queden 5 minutos
            if (distance < this.config.warningTime && distance > this.config.warningTime - 1000) {
                this.showNotification('⚠️ El código QR expirará en 5 minutos', 'warning');
            }
        }, 1000);
    }

    /**
     * Manejar QR expirado
     */
    handleExpiredQR() {
        this.showNotification('⏰ Código QR expirado. Generando uno nuevo...', 'warning');
        setTimeout(() => {
            this.generateNewQR();
        }, 2000);
    }

    /**
     * Configurar estado de carga
     */
    setLoadingState(isLoading) {
        const refreshBtn = document.getElementById('refresh-qr-btn');
        const generateBtn = document.getElementById('generate-qr-btn');
        
        [refreshBtn, generateBtn].forEach(btn => {
            if (btn) {
                btn.disabled = isLoading;
                if (isLoading) {
                    btn.innerHTML = '<span class="animate-spin inline-block">⟳</span> Generando...';
                    btn.classList.add('opacity-50', 'cursor-not-allowed');
                } else {
                    btn.innerHTML = btn.dataset.originalText || '🔄 Nuevo QR';
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }
        });
    }

    /**
     * Configurar event listeners
     */
    setupEventListeners() {
        // Botón de refresh
        const refreshBtn = document.getElementById('refresh-qr-btn');
        if (refreshBtn) {
            refreshBtn.dataset.originalText = refreshBtn.innerHTML;
            refreshBtn.addEventListener('click', () => this.generateNewQR());
        }

        // Botón de generar
        const generateBtn = document.getElementById('generate-qr-btn');
        if (generateBtn) {
            generateBtn.dataset.originalText = generateBtn.innerHTML;
            generateBtn.addEventListener('click', () => this.generateNewQR());
        }

        // Tecla F5 para refresh
        document.addEventListener('keydown', (e) => {
            if (e.key === 'F5' && e.ctrlKey) {
                e.preventDefault();
                this.generateNewQR();
            }
        });
    }

    /**
     * Iniciar auto-refresh
     */
    startAutoRefresh() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
        }
        
        this.refreshTimer = setInterval(() => {
            console.log('🔄 Auto-refresh del código QR');
            this.generateNewQR();
        }, this.config.refreshInterval);
    }

    /**
     * Detener auto-refresh
     */
    stopAutoRefresh() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
            this.refreshTimer = null;
        }
        if (this.countdownTimer) {
            clearInterval(this.countdownTimer);
            this.countdownTimer = null;
        }
    }

    /**
     * Copiar código de sesión al portapapeles
     */
    async copySessionToken() {
        if (!this.currentQR) return;
        
        try {
            await navigator.clipboard.writeText(this.currentQR.session_token);
            this.showNotification('📋 Código copiado al portapapeles', 'success');
        } catch (error) {
            console.error('Error copiando al portapapeles:', error);
            this.showNotification('❌ Error al copiar código', 'error');
        }
    }

    /**
     * Descargar imagen del QR
     */
    downloadQR() {
        if (!this.currentQR) return;
        
        try {
            const link = document.createElement('a');
            link.href = `data:image/png;base64,${this.currentQR.qr_image}`;
            link.download = `qr-session-${this.currentQR.session_token}.png`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            this.showNotification('💾 Código QR descargado', 'success');
        } catch (error) {
            console.error('Error descargando QR:', error);
            this.showNotification('❌ Error al descargar QR', 'error');
        }
    }

    /**
     * Mostrar notificación
     */
    showNotification(message, type = 'info', duration = 5000) {
        // Remover notificaciones existentes
        const existingNotifications = document.querySelectorAll('.qr-notification');
        existingNotifications.forEach(n => n.remove());

        const notification = document.createElement('div');
        notification.className = `qr-notification fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transform translate-x-full transition-all duration-300 ${this.getNotificationClass(type)}`;
        
        notification.innerHTML = `
            <div class="flex items-center space-x-3">
                <span class="notification-icon text-lg">${this.getNotificationIcon(type)}</span>
                <span class="notification-message text-white font-medium">${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" 
                        class="notification-close text-white hover:text-gray-200 ml-2">
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
    }

    /**
     * Obtener clase CSS para notificación
     */
    getNotificationClass(type) {
        const classes = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };
        return classes[type] || classes.info;
    }

    /**
     * Obtener icono para notificación
     */
    getNotificationIcon(type) {
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        return icons[type] || icons.info;
    }

    /**
     * Destruir instancia
     */
    destroy() {
        this.stopAutoRefresh();
        console.log('🔄 Generador de QR destruido');
    }
}

// Crear instancia global
let qrGenerator = null;

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('qr-container')) {
        qrGenerator = new QRGenerator();
        qrGenerator.init();
        console.log('🚀 Generador de QR inicializado');
    }
});

// Limpiar al salir de la página
window.addEventListener('beforeunload', function() {
    if (qrGenerator) {
        qrGenerator.destroy();
    }
});

// Exponer globalmente
window.QRGenerator = QRGenerator;
window.qrGenerator = qrGenerator;