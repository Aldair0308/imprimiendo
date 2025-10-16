@extends('layouts.app')

@section('title', 'Error en la Sesión')
@section('description', 'Ha ocurrido un error al acceder a la sesión de impresión.')

@push('styles')
<style>
    .error-container {
        animation: fadeIn 0.5s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .error-icon {
        animation: bounce 1s ease-in-out;
    }
    
    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
        40% { transform: translateY(-10px); }
        60% { transform: translateY(-5px); }
    }
    
    .action-button {
        transition: all var(--transition-normal);
    }
    
    .action-button:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-secondary flex items-center justify-center px-4">
    <div class="error-container max-w-md w-full bg-white rounded-xl shadow-xl p-8 text-center">
        <!-- Error Icon -->
        <div class="error-icon mb-6">
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto">
                <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
        </div>

        <!-- Error Title -->
        <h1 class="text-2xl font-bold text-gray-900 mb-4">
            Error en la Sesión
        </h1>

        <!-- Error Message -->
        <div class="mb-6">
            <p class="text-gray-600 mb-4">
                Ha ocurrido un problema al acceder a la sesión de impresión.
            </p>
            
            @if(isset($message) && $message)
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                <p class="text-red-700 text-sm">
                    <strong>Detalles del error:</strong><br>
                    {{ $message }}
                </p>
            </div>
            @endif
            
            <p class="text-gray-500 text-sm">
                Esto puede ocurrir si la sesión ha expirado, el código QR es inválido, o hay un problema temporal con el sistema.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-3">
            <!-- Retry Button -->
            <button onclick="window.location.reload()" 
                    class="action-button w-full bg-primary-blue text-white py-3 px-6 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Intentar de Nuevo
            </button>

            <!-- Home Button -->
            <a href="{{ route('home') }}" 
               class="action-button block w-full bg-gray-100 text-gray-700 py-3 px-6 rounded-lg font-medium hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Volver al Inicio
            </a>

            <!-- Generate New QR Button -->
            <a href="{{ route('home') }}#new-qr" 
               class="action-button block w-full border-2 border-primary-blue text-primary-blue py-3 px-6 rounded-lg font-medium hover:bg-primary-blue hover:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Generar Nuevo Código QR
            </a>
        </div>

        <!-- Help Text -->
        <div class="mt-6 pt-6 border-t border-gray-200">
            <p class="text-xs text-gray-400">
                Si el problema persiste, contacta al administrador del sistema.
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-refresh después de 30 segundos si el usuario no hace nada
    setTimeout(function() {
        if (confirm('¿Deseas intentar recargar la página automáticamente?')) {
            window.location.reload();
        }
    }, 30000);
    
    // Detectar si viene de un QR y mostrar mensaje específico
    if (document.referrer === '' || document.referrer.includes('qr')) {
        console.log('Acceso desde QR detectado');
    }
</script>
@endpush
@endsection