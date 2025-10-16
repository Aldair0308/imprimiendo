<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Imprimeindo') }} - @yield('title', 'Sistema de Impresiones Inteligente')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Variables CSS Centralizadas -->
    <link href="{{ asset('css/variables.css') }}" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Meta Tags Adicionales -->
    <meta name="description" content="@yield('description', 'Sistema de impresiones inteligente multi-impresora con gestión automatizada y balanceador de carga')">
    <meta name="keywords" content="impresión, sistema, multi-impresora, Laravel, automatizado">
    <meta name="author" content="Imprimeindo">

    <!-- Open Graph -->
    <meta property="og:title" content="{{ config('app.name', 'Imprimeindo') }} - @yield('title', 'Sistema de Impresiones')">
    <meta property="og:description" content="@yield('description', 'Sistema de impresiones inteligente multi-impresora')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">

    <!-- Estilos Adicionales -->
    @stack('styles')
</head>
<body class="font-sans antialiased bg-secondary text-primary" style="font-family: var(--font-family-primary);">
    <div class="min-h-screen flex flex-col">
        <!-- Header Navigation -->
        @unless(request()->routeIs('admin.*'))
        <header class="bg-primary shadow-sm border-b border-light sticky top-0 z-fixed">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <a href="{{ route('home') }}" class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-primary-blue rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-lg">🖨️</span>
                            </div>
                            <span class="text-xl font-semibold text-primary">Imprimeindo</span>
                        </a>
                    </div>

                    <!-- Navigation Links -->
                    <nav class="hidden md:flex space-x-8">
                        <a href="{{ route('home') }}" 
                           class="text-secondary hover:text-primary transition-colors duration-150 font-medium
                                  {{ request()->routeIs('home') ? 'text-primary border-b-2 border-primary-blue' : '' }}">
                            Inicio
                        </a>
                        <a href="#" 
                           class="text-secondary hover:text-primary transition-colors duration-150 font-medium">
                            Ayuda
                        </a>
                    </nav>

                    <!-- Status Indicator -->
                    <div class="flex items-center space-x-4">
                        <div class="hidden sm:flex items-center space-x-2">
                            <div class="status-indicator status-online"></div>
                            <span class="text-sm text-secondary">Sistema Activo</span>
                        </div>
                        
                        <!-- Mobile Menu Button -->
                        <button type="button" 
                                class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-secondary hover:text-primary hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-blue"
                                id="mobile-menu-button">
                            <span class="sr-only">Abrir menú principal</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile Menu -->
                <div class="md:hidden hidden" id="mobile-menu">
                    <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 border-t border-light">
                        <a href="{{ route('home') }}" 
                           class="block px-3 py-2 rounded-md text-base font-medium text-secondary hover:text-primary hover:bg-secondary transition-colors duration-150
                                  {{ request()->routeIs('home') ? 'text-primary bg-tertiary' : '' }}">
                            Inicio
                        </a>
                        <a href="#" 
                           class="block px-3 py-2 rounded-md text-base font-medium text-secondary hover:text-primary hover:bg-secondary transition-colors duration-150">
                            Ayuda
                        </a>
                    </div>
                </div>
            </div>
        </header>
        @endunless

        <!-- Admin Header -->
        @if(request()->routeIs('admin.*'))
        <header class="bg-dark text-white shadow-lg sticky top-0 z-fixed">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Admin Logo -->
                    <div class="flex items-center">
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-primary-blue rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-lg">⚙️</span>
                            </div>
                            <span class="text-xl font-semibold">Panel Administrativo</span>
                        </a>
                    </div>

                    <!-- Admin Navigation -->
                    <nav class="hidden md:flex space-x-6">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="text-gray-300 hover:text-white transition-colors duration-150 font-medium
                                  {{ request()->routeIs('admin.dashboard') ? 'text-white border-b-2 border-primary-blue' : '' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('admin.printers.index') }}" 
                           class="text-gray-300 hover:text-white transition-colors duration-150 font-medium
                                  {{ request()->routeIs('admin.printers.*') ? 'text-white border-b-2 border-primary-blue' : '' }}">
                            Impresoras
                        </a>
                        <a href="{{ route('admin.sessions') }}" 
                           class="text-gray-300 hover:text-white transition-colors duration-150 font-medium
                                  {{ request()->routeIs('admin.sessions') ? 'text-white border-b-2 border-primary-blue' : '' }}">
                            Sesiones
                        </a>
                        <a href="{{ route('admin.reports') }}" 
                           class="text-gray-300 hover:text-white transition-colors duration-150 font-medium
                                  {{ request()->routeIs('admin.reports') ? 'text-white border-b-2 border-primary-blue' : '' }}">
                            Reportes
                        </a>
                    </nav>

                    <!-- Admin User Menu -->
                    <div class="flex items-center space-x-4">
                        <span class="hidden sm:block text-sm text-gray-300">
                            Administrador
                        </span>
                        <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="text-gray-300 hover:text-white transition-colors duration-150 text-sm font-medium">
                                Cerrar Sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>
        @endif

        <!-- Main Content -->
        <main class="flex-1">
            <!-- Flash Messages -->
            @if(session('success'))
            <div class="bg-success-light border border-success-green text-success-dark px-4 py-3 rounded-md mx-4 mt-4 fade-in" role="alert">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="bg-error-light border border-alert-red text-error-dark px-4 py-3 rounded-md mx-4 mt-4 fade-in" role="alert">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
            @endif

            @if(session('warning'))
            <div class="bg-warning-light border border-warning-yellow text-yellow-800 px-4 py-3 rounded-md mx-4 mt-4 fade-in" role="alert">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <span>{{ session('warning') }}</span>
                </div>
            </div>
            @endif

            <!-- Page Content -->
            @yield('content')
        </main>

        <!-- Footer -->
        @unless(request()->routeIs('admin.*'))
        <footer class="bg-primary border-t border-light mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Company Info -->
                    <div>
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-8 h-8 bg-primary-blue rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-lg">🖨️</span>
                            </div>
                            <span class="text-xl font-semibold text-primary">Imprimeindo</span>
                        </div>
                        <p class="text-secondary text-sm">
                            Sistema de impresiones inteligente multi-impresora con gestión automatizada y balanceador de carga.
                        </p>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h3 class="text-primary font-semibold mb-4">Enlaces Rápidos</h3>
                        <ul class="space-y-2">
                            <li><a href="{{ route('home') }}" class="text-secondary hover:text-primary transition-colors duration-150 text-sm">Inicio</a></li>
                            <li><a href="#" class="text-secondary hover:text-primary transition-colors duration-150 text-sm">Ayuda</a></li>
                            <li><a href="#" class="text-secondary hover:text-primary transition-colors duration-150 text-sm">Términos de Uso</a></li>
                            <li><a href="#" class="text-secondary hover:text-primary transition-colors duration-150 text-sm">Privacidad</a></li>
                        </ul>
                    </div>

                    <!-- System Status -->
                    <div>
                        <h3 class="text-primary font-semibold mb-4">Estado del Sistema</h3>
                        <div class="space-y-2">
                            <div class="flex items-center space-x-2">
                                <div class="status-indicator status-online"></div>
                                <span class="text-sm text-secondary">Impresoras Activas: <span id="active-printers-count">-</span></span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="status-indicator status-busy"></div>
                                <span class="text-sm text-secondary">Trabajos en Cola: <span id="queue-count">-</span></span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="status-indicator status-online"></div>
                                <span class="text-sm text-secondary">Sesiones Activas: <span id="active-sessions-count">-</span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-light mt-8 pt-8 text-center">
                    <p class="text-secondary text-sm">
                        &copy; {{ date('Y') }} Imprimeindo. Todos los derechos reservados. 
                        <span class="text-muted">v1.0.0</span>
                    </p>
                </div>
            </div>
        </footer>
        @endunless
    </div>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-modal hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-primary rounded-lg p-6 shadow-xl">
                <div class="flex items-center space-x-3">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-blue"></div>
                    <span class="text-primary font-medium">Procesando...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts Adicionales -->
    @stack('scripts')

    <!-- JavaScript Global -->
    <script>
        // Variables CSS disponibles en JavaScript
        const CSS_VARS = {
            primaryBlue: 'var(--primary-blue)',
            successGreen: 'var(--success-green)',
            alertRed: 'var(--alert-red)',
            printerOnline: 'var(--printer-online)',
            printerOffline: 'var(--printer-offline)',
            printerError: 'var(--printer-error)',
            transitionFast: 'var(--transition-fast)',
            transitionNormal: 'var(--transition-normal)'
        };

        // Funciones globales
        window.showLoading = function() {
            document.getElementById('loading-overlay').classList.remove('hidden');
        };

        window.hideLoading = function() {
            document.getElementById('loading-overlay').classList.add('hidden');
        };

        window.showNotification = function(message, type = 'info') {
            // Crear elemento de notificación
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transition-all duration-300 transform translate-x-full`;
            
            // Estilos según el tipo
            const typeClasses = {
                'success': 'bg-green-500 text-white',
                'error': 'bg-red-500 text-white',
                'warning': 'bg-yellow-500 text-white',
                'info': 'bg-blue-500 text-white'
            };
            
            notification.className += ' ' + (typeClasses[type] || typeClasses['info']);
            notification.innerHTML = `
                <div class="flex items-center justify-between">
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Animar entrada
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            // Auto-remover después de 5 segundos
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }, 5000);
        };

        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }

            // Auto-hide flash messages
            setTimeout(function() {
                const alerts = document.querySelectorAll('[role="alert"]');
                alerts.forEach(function(alert) {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(function() {
                        alert.remove();
                    }, 300);
                });
            }, 5000);

            // Update system status (si está en la página principal)
            if (typeof updateSystemStatus === 'function') {
                updateSystemStatus();
                setInterval(updateSystemStatus, 30000); // Actualizar cada 30 segundos
            }
        });

        // CSRF Token para AJAX
        window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Configurar AJAX headers
        if (typeof axios !== 'undefined') {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = window.csrfToken;
            axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        }
    </script>
</body>
</html>