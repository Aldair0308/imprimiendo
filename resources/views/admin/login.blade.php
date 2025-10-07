@extends('layouts.app')

@section('title', 'Acceso Administrativo - Imprimeindo')
@section('description', 'Panel de administración para gestión de impresoras y sistema.')

@push('styles')
<style>
    .admin-login-container {
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-dark) 100%);
        min-height: 100vh;
    }
    
    .login-card {
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .login-form-group {
        position: relative;
    }
    
    .login-input {
        transition: all var(--transition-normal);
        border: 2px solid var(--border-light);
        background: var(--bg-white);
    }
    
    .login-input:focus {
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        outline: none;
    }
    
    .login-input:invalid {
        border-color: var(--status-error);
    }
    
    .login-btn {
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-dark) 100%);
        transition: all var(--transition-normal);
        position: relative;
        overflow: hidden;
    }
    
    .login-btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }
    
    .login-btn:active {
        transform: translateY(0);
    }
    
    .login-btn.loading {
        pointer-events: none;
    }
    
    .login-btn .spinner {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .floating-shapes {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        pointer-events: none;
    }
    
    .shape {
        position: absolute;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        animation: float 6s ease-in-out infinite;
    }
    
    .shape:nth-child(1) {
        width: 80px;
        height: 80px;
        top: 20%;
        left: 10%;
        animation-delay: 0s;
    }
    
    .shape:nth-child(2) {
        width: 120px;
        height: 120px;
        top: 60%;
        right: 10%;
        animation-delay: 2s;
    }
    
    .shape:nth-child(3) {
        width: 60px;
        height: 60px;
        bottom: 20%;
        left: 20%;
        animation-delay: 4s;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(180deg); }
    }
    
    .security-badge {
        background: linear-gradient(135deg, var(--status-success) 0%, #10b981 100%);
        animation: pulse 2s infinite;
    }
    
    .error-message {
        animation: shake 0.5s ease-in-out;
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    
    .password-toggle {
        cursor: pointer;
        transition: all var(--transition-normal);
    }
    
    .password-toggle:hover {
        color: var(--primary-blue);
    }
    
    .login-footer {
        background: rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(5px);
    }
</style>
@endpush

@section('content')
<div class="admin-login-container flex items-center justify-center p-4">
    <!-- Floating Shapes Background -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="login-card rounded-2xl shadow-2xl p-8 w-full max-w-md relative z-10">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                <span class="text-3xl text-white">🔐</span>
            </div>
            
            <h1 class="text-2xl font-bold text-primary mb-2">Panel Administrativo</h1>
            <p class="text-secondary text-sm">Acceso seguro al sistema de gestión</p>
            
            <!-- Security Badge -->
            <div class="security-badge inline-flex items-center space-x-2 px-3 py-1 rounded-full text-white text-xs font-medium mt-3">
                <span>🛡️</span>
                <span>Conexión Segura</span>
            </div>
        </div>

        <!-- Login Form -->
        <form id="admin-login-form" onsubmit="handleLogin(event)" class="space-y-6">
            @csrf
            
            <!-- Username Field -->
            <div class="login-form-group">
                <label for="username" class="block text-sm font-medium text-primary mb-2">
                    👤 Usuario Administrativo
                </label>
                <div class="relative">
                    <input type="text" 
                           id="username" 
                           name="username" 
                           required
                           autocomplete="username"
                           class="login-input w-full px-4 py-3 rounded-lg pl-12 text-primary placeholder-gray-400"
                           placeholder="Ingresa tu usuario">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-400 text-lg">👤</span>
                    </div>
                </div>
            </div>

            <!-- Password Field -->
            <div class="login-form-group">
                <label for="password" class="block text-sm font-medium text-primary mb-2">
                    🔑 Contraseña
                </label>
                <div class="relative">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required
                           autocomplete="current-password"
                           class="login-input w-full px-4 py-3 rounded-lg pl-12 pr-12 text-primary placeholder-gray-400"
                           placeholder="Ingresa tu contraseña">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-400 text-lg">🔑</span>
                    </div>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <span class="password-toggle text-gray-400 text-lg" 
                              onclick="togglePassword()"
                              id="password-toggle">
                            👁️
                        </span>
                    </div>
                </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" 
                           id="remember" 
                           name="remember" 
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="text-sm text-secondary">Recordar sesión</span>
                </label>
                
                <button type="button" 
                        onclick="showForgotPassword()"
                        class="text-sm text-primary-blue hover:text-primary-dark transition-colors">
                    ¿Olvidaste tu contraseña?
                </button>
            </div>

            <!-- Error Message -->
            <div id="error-message" class="hidden error-message bg-red-50 border border-red-200 rounded-lg p-3">
                <div class="flex items-center space-x-2">
                    <span class="text-red-500">⚠️</span>
                    <span class="text-red-700 text-sm" id="error-text">Error de autenticación</span>
                </div>
            </div>

            <!-- Login Button -->
            <button type="submit" 
                    id="login-btn"
                    class="login-btn w-full py-3 px-4 text-white font-semibold rounded-lg shadow-lg">
                <span id="login-btn-text" class="flex items-center justify-center space-x-2">
                    <span>🚀</span>
                    <span>Acceder al Panel</span>
                </span>
                <span id="login-btn-loading" class="hidden flex items-center justify-center space-x-2">
                    <span class="spinner">⏳</span>
                    <span>Verificando...</span>
                </span>
            </button>
        </form>

        <!-- Additional Options -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <div class="text-center space-y-3">
                <button onclick="requestAccess()" 
                        class="text-sm text-primary-blue hover:text-primary-dark transition-colors">
                    📧 Solicitar acceso administrativo
                </button>
                
                <div class="text-xs text-muted">
                    <p>Sistema protegido con autenticación de dos factores</p>
                    <p class="mt-1">Todas las acciones son registradas y monitoreadas</p>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center justify-between text-sm">
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-secondary">Sistema operativo</span>
                </div>
                <span class="text-muted" id="system-time">{{ date('H:i:s') }}</span>
            </div>
            
            <div class="flex items-center justify-between text-xs text-muted mt-2">
                <span>Versión: 1.0.0</span>
                <span>Última actualización: {{ date('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="login-footer fixed bottom-0 left-0 right-0 p-4">
        <div class="text-center text-white text-sm">
            <p>&copy; {{ date('Y') }} Imprimeindo. Todos los derechos reservados.</p>
            <p class="text-xs mt-1 opacity-75">
                <button onclick="showPrivacyPolicy()" class="hover:underline">Política de Privacidad</button>
                •
                <button onclick="showTerms()" class="hover:underline">Términos de Uso</button>
                •
                <button onclick="showSupport()" class="hover:underline">Soporte Técnico</button>
            </p>
        </div>
    </div>
</div>

<!-- Forgot Password Modal -->
<div id="forgot-password-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="text-2xl">🔐</span>
            </div>
            <h2 class="text-xl font-bold text-primary mb-2">Recuperar Contraseña</h2>
            <p class="text-secondary text-sm">
                Ingresa tu usuario para recibir instrucciones de recuperación
            </p>
        </div>
        
        <form id="forgot-password-form" onsubmit="handleForgotPassword(event)" class="space-y-4">
            <div>
                <label for="forgot-username" class="block text-sm font-medium text-primary mb-2">
                    Usuario Administrativo
                </label>
                <input type="text" 
                       id="forgot-username" 
                       name="username" 
                       required
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none">
            </div>
            
            <div class="flex space-x-3">
                <button type="button" 
                        onclick="closeForgotPassword()"
                        class="flex-1 py-3 px-4 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button type="submit" 
                        class="flex-1 py-3 px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Enviar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Success Modal -->
<div id="success-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 text-center">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-2xl">✅</span>
        </div>
        
        <h2 class="text-xl font-bold text-primary mb-2">¡Acceso Autorizado!</h2>
        <p class="text-secondary mb-6">
            Redirigiendo al panel administrativo...
        </p>
        
        <div class="flex items-center justify-center space-x-2 text-blue-600">
            <span class="spinner">⏳</span>
            <span>Cargando panel...</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Variables globales
    let isLoggingIn = false;
    let systemTimeInterval = null;

    // Inicializar página
    document.addEventListener('DOMContentLoaded', function() {
        initSystemTime();
        focusFirstInput();
        checkAutoLogin();
        
        // Configurar eventos de teclado
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeForgotPassword();
            }
        });
    });

    // Inicializar reloj del sistema
    function initSystemTime() {
        updateSystemTime();
        systemTimeInterval = setInterval(updateSystemTime, 1000);
    }

    // Actualizar hora del sistema
    function updateSystemTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        
        const timeElement = document.getElementById('system-time');
        if (timeElement) {
            timeElement.textContent = timeString;
        }
    }

    // Enfocar primer input
    function focusFirstInput() {
        const usernameInput = document.getElementById('username');
        if (usernameInput) {
            usernameInput.focus();
        }
    }

    // Verificar auto-login
    function checkAutoLogin() {
        const rememberedUser = localStorage.getItem('admin_remember');
        if (rememberedUser) {
            const userData = JSON.parse(rememberedUser);
            document.getElementById('username').value = userData.username;
            document.getElementById('remember').checked = true;
        }
    }

    // Manejar login
    async function handleLogin(event) {
        event.preventDefault();
        
        if (isLoggingIn) return;
        
        const formData = new FormData(event.target);
        const username = formData.get('username');
        const password = formData.get('password');
        const remember = formData.get('remember');
        
        // Validaciones básicas
        if (!username || !password) {
            showError('Por favor completa todos los campos');
            return;
        }
        
        if (username.length < 3) {
            showError('El usuario debe tener al menos 3 caracteres');
            return;
        }
        
        if (password.length < 6) {
            showError('La contraseña debe tener al menos 6 caracteres');
            return;
        }
        
        isLoggingIn = true;
        setLoadingState(true);
        hideError();
        
        try {
            // Simular delay de autenticación
            await new Promise(resolve => setTimeout(resolve, 1500));
            
            const response = await fetch('/api/admin/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({
                    username: username,
                    password: password,
                    remember: remember ? true : false
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Guardar sesión si se seleccionó recordar
                if (remember) {
                    localStorage.setItem('admin_remember', JSON.stringify({
                        username: username,
                        timestamp: Date.now()
                    }));
                } else {
                    localStorage.removeItem('admin_remember');
                }
                
                // Mostrar modal de éxito
                document.getElementById('success-modal').classList.remove('hidden');
                
                // Redirigir después de un delay
                setTimeout(() => {
                    window.location.href = data.redirect_url || '/admin/dashboard';
                }, 2000);
                
            } else {
                throw new Error(data.message || 'Credenciales inválidas');
            }
            
        } catch (error) {
            console.error('Error de login:', error);
            showError(error.message || 'Error de conexión. Intenta nuevamente.');
            
            // Limpiar contraseña en caso de error
            document.getElementById('password').value = '';
            document.getElementById('password').focus();
            
        } finally {
            isLoggingIn = false;
            setLoadingState(false);
        }
    }

    // Establecer estado de carga
    function setLoadingState(loading) {
        const loginBtn = document.getElementById('login-btn');
        const btnText = document.getElementById('login-btn-text');
        const btnLoading = document.getElementById('login-btn-loading');
        
        if (loading) {
            loginBtn.classList.add('loading');
            loginBtn.disabled = true;
            btnText.classList.add('hidden');
            btnLoading.classList.remove('hidden');
        } else {
            loginBtn.classList.remove('loading');
            loginBtn.disabled = false;
            btnText.classList.remove('hidden');
            btnLoading.classList.add('hidden');
        }
    }

    // Mostrar error
    function showError(message) {
        const errorDiv = document.getElementById('error-message');
        const errorText = document.getElementById('error-text');
        
        errorText.textContent = message;
        errorDiv.classList.remove('hidden');
        
        // Auto-ocultar después de 5 segundos
        setTimeout(hideError, 5000);
    }

    // Ocultar error
    function hideError() {
        const errorDiv = document.getElementById('error-message');
        errorDiv.classList.add('hidden');
    }

    // Toggle password visibility
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('password-toggle');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.textContent = '🙈';
        } else {
            passwordInput.type = 'password';
            toggleIcon.textContent = '👁️';
        }
    }

    // Mostrar modal de contraseña olvidada
    function showForgotPassword() {
        document.getElementById('forgot-password-modal').classList.remove('hidden');
        document.getElementById('forgot-username').focus();
    }

    // Cerrar modal de contraseña olvidada
    function closeForgotPassword() {
        document.getElementById('forgot-password-modal').classList.add('hidden');
    }

    // Manejar recuperación de contraseña
    async function handleForgotPassword(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const username = formData.get('username');
        
        if (!username) {
            showNotification('Por favor ingresa tu usuario', 'error');
            return;
        }
        
        try {
            const response = await fetch('/api/admin/forgot-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({ username: username })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showNotification('Instrucciones enviadas al administrador del sistema', 'success');
                closeForgotPassword();
            } else {
                throw new Error(data.message || 'Error al procesar solicitud');
            }
            
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al procesar solicitud: ' + error.message, 'error');
        }
    }

    // Solicitar acceso
    function requestAccess() {
        showNotification('Contacta al administrador del sistema para solicitar acceso', 'info');
    }

    // Mostrar política de privacidad
    function showPrivacyPolicy() {
        showNotification('Política de privacidad en desarrollo', 'info');
    }

    // Mostrar términos
    function showTerms() {
        showNotification('Términos de uso en desarrollo', 'info');
    }

    // Mostrar soporte
    function showSupport() {
        showNotification('Soporte técnico: admin@imprimeindo.com', 'info');
    }

    // Limpiar intervalos al salir
    window.addEventListener('beforeunload', function() {
        if (systemTimeInterval) {
            clearInterval(systemTimeInterval);
        }
    });

    // Configurar eventos de modal
    document.addEventListener('DOMContentLoaded', function() {
        // Cerrar modal al hacer clic fuera
        document.getElementById('forgot-password-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeForgotPassword();
            }
        });
        
        document.getElementById('success-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                // No permitir cerrar el modal de éxito
            }
        });
    });
</script>
@endpush