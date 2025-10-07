@extends('layouts.app')

@section('title', 'Error - Sistema de Impresiones')
@section('description', 'Ha ocurrido un error en el sistema de impresiones.')

@push('styles')
<style>
    .error-container {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }
    
    .error-card {
        animation: fadeInUp 0.6s ease-out;
    }
    
    @keyframes fadeInUp {
        from { 
            opacity: 0; 
            transform: translateY(30px); 
        }
        to { 
            opacity: 1; 
            transform: translateY(0); 
        }
    }
    
    .pulse-error {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-secondary">
    <!-- Error Section -->
    <section class="error-container py-16 px-4">
        <div class="max-w-2xl mx-auto text-center">
            <div class="mb-8">
                <div class="w-24 h-24 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-6 pulse-error">
                    <span class="text-4xl">⚠️</span>
                </div>
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">
                    ¡Oops! Algo salió mal
                </h1>
                <p class="text-xl text-red-100">
                    Ha ocurrido un error inesperado
                </p>
            </div>
        </div>
    </section>

    <!-- Error Details -->
    <section class="py-12 px-4">
        <div class="max-w-2xl mx-auto">
            <div class="error-card bg-primary rounded-2xl shadow-2xl p-8 text-center">
                <div class="mb-6">
                    <h2 class="text-2xl font-semibold text-primary mb-4">
                        Detalles del Error
                    </h2>
                    
                    @if(isset($message) && $message)
                        <div class="bg-error-red bg-opacity-10 border border-error-red border-opacity-20 rounded-lg p-4 mb-6">
                            <p class="text-error-red font-medium">
                                {{ $message }}
                            </p>
                        </div>
                    @else
                        <div class="bg-gray-100 rounded-lg p-4 mb-6">
                            <p class="text-secondary">
                                Se ha producido un error interno del sistema. Por favor, inténtalo de nuevo más tarde.
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="space-y-4">
                    <a href="{{ route('home') }}" 
                       class="inline-block w-full bg-primary-blue hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200">
                        🏠 Volver al Inicio
                    </a>
                    
                    <button onclick="window.location.reload()" 
                            class="inline-block w-full bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200">
                        🔄 Intentar de Nuevo
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Help Section -->
    <section class="py-12 px-4 bg-light">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-3xl font-bold text-primary mb-8">
                ¿Necesitas ayuda?
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Reintentar -->
                <div class="bg-primary rounded-xl p-6 shadow-lg">
                    <div class="w-12 h-12 bg-primary-blue rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white text-xl">🔄</span>
                    </div>
                    <h3 class="font-semibold text-primary mb-2">Reintentar</h3>
                    <p class="text-sm text-secondary mb-4">
                        A veces los errores son temporales. Intenta recargar la página.
                    </p>
                    <button onclick="window.location.reload()" 
                            class="text-primary-blue hover:text-blue-700 font-medium text-sm">
                        Recargar página →
                    </button>
                </div>

                <!-- Nuevo QR -->
                <div class="bg-primary rounded-xl p-6 shadow-lg">
                    <div class="w-12 h-12 bg-success-green rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white text-xl">📱</span>
                    </div>
                    <h3 class="font-semibold text-primary mb-2">Nuevo Código QR</h3>
                    <p class="text-sm text-secondary mb-4">
                        Genera un nuevo código QR para comenzar una sesión fresca.
                    </p>
                    <a href="{{ route('home') }}" 
                       class="text-success-green hover:text-green-700 font-medium text-sm">
                        Generar nuevo QR →
                    </a>
                </div>

                <!-- Soporte -->
                <div class="bg-primary rounded-xl p-6 shadow-lg">
                    <div class="w-12 h-12 bg-warning-yellow rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white text-xl">💬</span>
                    </div>
                    <h3 class="font-semibold text-primary mb-2">Contactar Soporte</h3>
                    <p class="text-sm text-secondary mb-4">
                        Si el problema persiste, contacta a nuestro equipo de soporte.
                    </p>
                    <a href="{{ route('home.help') }}" 
                       class="text-warning-yellow hover:text-yellow-600 font-medium text-sm">
                        Ver ayuda →
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Error Code Section -->
    @if(config('app.debug'))
    <section class="py-8 px-4">
        <div class="max-w-2xl mx-auto">
            <div class="bg-gray-100 rounded-lg p-4">
                <h3 class="font-semibold text-gray-700 mb-2">Información de Debug</h3>
                <div class="text-sm text-gray-600 space-y-1">
                    <p><strong>Timestamp:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
                    <p><strong>URL:</strong> {{ request()->fullUrl() }}</p>
                    <p><strong>User Agent:</strong> {{ request()->userAgent() }}</p>
                    @if(isset($exception))
                    <p><strong>Exception:</strong> {{ get_class($exception) }}</p>
                    <p><strong>File:</strong> {{ $exception->getFile() }}:{{ $exception->getLine() }}</p>
                    @endif
                </div>
            </div>
        </div>
    </section>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Auto-refresh después de 30 segundos si no hay interacción
    let autoRefreshTimer;
    
    function resetAutoRefresh() {
        if (autoRefreshTimer) {
            clearTimeout(autoRefreshTimer);
        }
        
        autoRefreshTimer = setTimeout(() => {
            if (confirm('¿Quieres intentar recargar la página automáticamente?')) {
                window.location.reload();
            }
        }, 30000);
    }
    
    // Inicializar auto-refresh
    document.addEventListener('DOMContentLoaded', function() {
        resetAutoRefresh();
        
        // Reset timer en cualquier interacción del usuario
        document.addEventListener('click', resetAutoRefresh);
        document.addEventListener('keypress', resetAutoRefresh);
    });
    
    // Función para reportar error (opcional)
    function reportError() {
        const errorData = {
            url: window.location.href,
            userAgent: navigator.userAgent,
            timestamp: new Date().toISOString(),
            message: '{{ $message ?? "Error desconocido" }}'
        };
        
        // Aquí podrías enviar el error a un servicio de logging
        console.log('Error reported:', errorData);
        
        alert('Error reportado. Gracias por ayudarnos a mejorar el sistema.');
    }
</script>
@endpush