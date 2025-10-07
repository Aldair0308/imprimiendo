@extends('layouts.app')

@section('title', 'Ayuda - Sistema de Impresiones')
@section('description', 'Centro de ayuda y soporte para el sistema de impresiones inteligente.')

@push('styles')
<style>
    .help-container {
        background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
    }
    
    .help-card {
        transition: all var(--transition-normal);
    }
    
    .help-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-xl);
    }
    
    .faq-item {
        transition: all var(--transition-normal);
    }
    
    .faq-item.active .faq-answer {
        max-height: 200px;
        opacity: 1;
    }
    
    .faq-answer {
        max-height: 0;
        opacity: 0;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .step-number {
        background: linear-gradient(135deg, var(--primary-blue) 0%, #3b82f6 100%);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-secondary">
    <!-- Hero Section -->
    <section class="help-container py-16 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <div class="mb-8">
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-4">
                    💡 Centro de Ayuda
                </h1>
                <p class="text-xl md:text-2xl text-blue-100 mb-2">
                    Todo lo que necesitas saber sobre Imprimeindo
                </p>
                <p class="text-lg text-blue-200">
                    Guías, tutoriales y soporte técnico
                </p>
            </div>
        </div>
    </section>

    <!-- Quick Help Cards -->
    <section class="py-12 px-4">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-3xl font-bold text-primary text-center mb-12">
                Ayuda Rápida
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Cómo Imprimir -->
                <div class="help-card bg-primary rounded-2xl p-6 shadow-lg">
                    <div class="w-16 h-16 bg-primary-blue rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white text-2xl">🖨️</span>
                    </div>
                    <h3 class="text-xl font-semibold text-primary mb-3 text-center">
                        Cómo Imprimir
                    </h3>
                    <p class="text-secondary text-center mb-4">
                        Aprende a usar el sistema paso a paso
                    </p>
                    <button onclick="scrollToSection('how-to-print')" 
                            class="w-full bg-primary-blue hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                        Ver Tutorial
                    </button>
                </div>

                <!-- Problemas Comunes -->
                <div class="help-card bg-primary rounded-2xl p-6 shadow-lg">
                    <div class="w-16 h-16 bg-warning-yellow rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white text-2xl">🔧</span>
                    </div>
                    <h3 class="text-xl font-semibold text-primary mb-3 text-center">
                        Problemas Comunes
                    </h3>
                    <p class="text-secondary text-center mb-4">
                        Soluciones a los errores más frecuentes
                    </p>
                    <button onclick="scrollToSection('troubleshooting')" 
                            class="w-full bg-warning-yellow hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                        Ver Soluciones
                    </button>
                </div>

                <!-- Contacto -->
                <div class="help-card bg-primary rounded-2xl p-6 shadow-lg">
                    <div class="w-16 h-16 bg-success-green rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white text-2xl">💬</span>
                    </div>
                    <h3 class="text-xl font-semibold text-primary mb-3 text-center">
                        Contactar Soporte
                    </h3>
                    <p class="text-secondary text-center mb-4">
                        ¿Necesitas ayuda personalizada?
                    </p>
                    <button onclick="scrollToSection('contact')" 
                            class="w-full bg-success-green hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                        Contactar
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Cómo Usar el Sistema -->
    <section id="how-to-print" class="py-12 px-4 bg-light">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl font-bold text-primary text-center mb-12">
                📖 Cómo Usar Imprimeindo
            </h2>
            
            <div class="space-y-8">
                <!-- Paso 1 -->
                <div class="flex items-start space-x-6">
                    <div class="step-number w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                        1
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-semibold text-primary mb-2">
                            Escanea el Código QR
                        </h3>
                        <p class="text-secondary mb-4">
                            Usa la cámara de tu teléfono para escanear el código QR que aparece en la pantalla principal. 
                            Esto te llevará a la página de configuración de impresión.
                        </p>
                        <div class="bg-primary rounded-lg p-4">
                            <p class="text-sm text-secondary">
                                💡 <strong>Tip:</strong> Asegúrate de que tu teléfono esté conectado a internet para acceder al sistema.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Paso 2 -->
                <div class="flex items-start space-x-6">
                    <div class="step-number w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                        2
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-semibold text-primary mb-2">
                            Selecciona tu Impresora
                        </h3>
                        <p class="text-secondary mb-4">
                            Elige la impresora que deseas usar de la lista de impresoras disponibles. 
                            Verás el estado de cada impresora (disponible, ocupada, sin papel, etc.).
                        </p>
                        <div class="bg-primary rounded-lg p-4">
                            <p class="text-sm text-secondary">
                                💡 <strong>Tip:</strong> Las impresoras con estado "Disponible" están listas para imprimir inmediatamente.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Paso 3 -->
                <div class="flex items-start space-x-6">
                    <div class="step-number w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                        3
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-semibold text-primary mb-2">
                            Sube tus Documentos
                        </h3>
                        <p class="text-secondary mb-4">
                            Selecciona los archivos que deseas imprimir desde tu teléfono. 
                            Puedes subir múltiples documentos PDF, imágenes (JPG, PNG) y documentos de texto.
                        </p>
                        <div class="bg-primary rounded-lg p-4">
                            <p class="text-sm text-secondary">
                                💡 <strong>Formatos soportados:</strong> PDF, JPG, PNG, DOC, DOCX, TXT
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Paso 4 -->
                <div class="flex items-start space-x-6">
                    <div class="step-number w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                        4
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-semibold text-primary mb-2">
                            Configura la Impresión
                        </h3>
                        <p class="text-secondary mb-4">
                            Ajusta las opciones de impresión: número de copias, orientación, calidad, 
                            impresión a doble cara, y selección de páginas específicas.
                        </p>
                        <div class="bg-primary rounded-lg p-4">
                            <p class="text-sm text-secondary">
                                💡 <strong>Tip:</strong> Revisa la vista previa antes de confirmar para evitar desperdicios.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Paso 5 -->
                <div class="flex items-start space-x-6">
                    <div class="step-number w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                        5
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-semibold text-primary mb-2">
                            Confirma e Imprime
                        </h3>
                        <p class="text-secondary mb-4">
                            Revisa todos los detalles y confirma tu trabajo de impresión. 
                            El sistema procesará tu solicitud y comenzará a imprimir automáticamente.
                        </p>
                        <div class="bg-success-green bg-opacity-10 border border-success-green border-opacity-20 rounded-lg p-4">
                            <p class="text-sm text-success-green">
                                ✅ <strong>¡Listo!</strong> Recoge tus documentos de la impresora seleccionada.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Preguntas Frecuentes -->
    <section id="troubleshooting" class="py-12 px-4">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl font-bold text-primary text-center mb-12">
                ❓ Preguntas Frecuentes
            </h2>
            
            <div class="space-y-4">
                <!-- FAQ 1 -->
                <div class="faq-item bg-primary rounded-lg shadow-md">
                    <button class="faq-question w-full text-left p-6 focus:outline-none" onclick="toggleFAQ(this)">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-primary">
                                ¿Qué hago si el código QR no funciona?
                            </h3>
                            <span class="faq-icon text-2xl text-primary-blue">+</span>
                        </div>
                    </button>
                    <div class="faq-answer px-6 pb-6">
                        <p class="text-secondary">
                            Si el código QR no se escanea correctamente, intenta lo siguiente:
                        </p>
                        <ul class="list-disc list-inside mt-2 text-secondary space-y-1">
                            <li>Asegúrate de que tu cámara tenga buena iluminación</li>
                            <li>Mantén el teléfono estable y a una distancia apropiada</li>
                            <li>Verifica que tu teléfono esté conectado a internet</li>
                            <li>Intenta recargar la página para generar un nuevo código QR</li>
                        </ul>
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div class="faq-item bg-primary rounded-lg shadow-md">
                    <button class="faq-question w-full text-left p-6 focus:outline-none" onclick="toggleFAQ(this)">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-primary">
                                ¿Por qué mi documento no se imprime?
                            </h3>
                            <span class="faq-icon text-2xl text-primary-blue">+</span>
                        </div>
                    </button>
                    <div class="faq-answer px-6 pb-6">
                        <p class="text-secondary">
                            Verifica los siguientes puntos:
                        </p>
                        <ul class="list-disc list-inside mt-2 text-secondary space-y-1">
                            <li>La impresora seleccionada esté disponible y con papel</li>
                            <li>El formato del archivo sea compatible (PDF, JPG, PNG, DOC, DOCX, TXT)</li>
                            <li>El tamaño del archivo no exceda los límites permitidos</li>
                            <li>No haya errores en la configuración de impresión</li>
                        </ul>
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="faq-item bg-primary rounded-lg shadow-md">
                    <button class="faq-question w-full text-left p-6 focus:outline-none" onclick="toggleFAQ(this)">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-primary">
                                ¿Cuánto tiempo tarda en imprimir?
                            </h3>
                            <span class="faq-icon text-2xl text-primary-blue">+</span>
                        </div>
                    </button>
                    <div class="faq-answer px-6 pb-6">
                        <p class="text-secondary">
                            El tiempo de impresión depende de varios factores:
                        </p>
                        <ul class="list-disc list-inside mt-2 text-secondary space-y-1">
                            <li>Número de páginas y complejidad del documento</li>
                            <li>Calidad de impresión seleccionada</li>
                            <li>Cola de trabajos pendientes en la impresora</li>
                            <li>Generalmente, documentos simples tardan 1-3 minutos</li>
                        </ul>
                    </div>
                </div>

                <!-- FAQ 4 -->
                <div class="faq-item bg-primary rounded-lg shadow-md">
                    <button class="faq-question w-full text-left p-6 focus:outline-none" onclick="toggleFAQ(this)">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-primary">
                                ¿Qué formatos de archivo puedo imprimir?
                            </h3>
                            <span class="faq-icon text-2xl text-primary-blue">+</span>
                        </div>
                    </button>
                    <div class="faq-answer px-6 pb-6">
                        <p class="text-secondary">
                            El sistema soporta los siguientes formatos:
                        </p>
                        <ul class="list-disc list-inside mt-2 text-secondary space-y-1">
                            <li><strong>Documentos:</strong> PDF, DOC, DOCX, TXT</li>
                            <li><strong>Imágenes:</strong> JPG, JPEG, PNG, GIF</li>
                            <li><strong>Tamaño máximo:</strong> 50 MB por archivo</li>
                            <li><strong>Páginas máximas:</strong> 100 páginas por trabajo</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contacto -->
    <section id="contact" class="py-12 px-4 bg-light">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-3xl font-bold text-primary mb-8">
                📞 ¿Necesitas Más Ayuda?
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Soporte Técnico -->
                <div class="bg-primary rounded-xl p-8 shadow-lg">
                    <div class="w-16 h-16 bg-primary-blue rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white text-2xl">🛠️</span>
                    </div>
                    <h3 class="text-xl font-semibold text-primary mb-4">
                        Soporte Técnico
                    </h3>
                    <p class="text-secondary mb-6">
                        Para problemas técnicos, errores del sistema o configuración de impresoras.
                    </p>
                    <div class="space-y-3">
                        <p class="text-sm text-secondary">
                            📧 <strong>Email:</strong> soporte@imprimeindo.com
                        </p>
                        <p class="text-sm text-secondary">
                            📱 <strong>WhatsApp:</strong> +1 (555) 123-4567
                        </p>
                        <p class="text-sm text-secondary">
                            🕒 <strong>Horario:</strong> Lun-Vie 8:00-18:00
                        </p>
                    </div>
                </div>

                <!-- Ayuda General -->
                <div class="bg-primary rounded-xl p-8 shadow-lg">
                    <div class="w-16 h-16 bg-success-green rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white text-2xl">💬</span>
                    </div>
                    <h3 class="text-xl font-semibold text-primary mb-4">
                        Ayuda General
                    </h3>
                    <p class="text-secondary mb-6">
                        Para consultas generales, sugerencias o información sobre el servicio.
                    </p>
                    <div class="space-y-3">
                        <p class="text-sm text-secondary">
                            📧 <strong>Email:</strong> info@imprimeindo.com
                        </p>
                        <p class="text-sm text-secondary">
                            📞 <strong>Teléfono:</strong> +1 (555) 987-6543
                        </p>
                        <p class="text-sm text-secondary">
                            🕒 <strong>Horario:</strong> Lun-Sab 9:00-17:00
                        </p>
                    </div>
                </div>
            </div>

            <!-- Enlaces Adicionales -->
            <div class="mt-12 pt-8 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-primary mb-4">
                    Enlaces Útiles
                </h3>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="{{ route('home') }}" 
                       class="text-primary-blue hover:text-blue-700 font-medium">
                        🏠 Inicio
                    </a>
                    <a href="{{ route('home.terms') }}" 
                       class="text-primary-blue hover:text-blue-700 font-medium">
                        📋 Términos de Servicio
                    </a>
                    <a href="{{ route('home.privacy') }}" 
                       class="text-primary-blue hover:text-blue-700 font-medium">
                        🔒 Política de Privacidad
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    // Función para hacer scroll suave a una sección
    function scrollToSection(sectionId) {
        const element = document.getElementById(sectionId);
        if (element) {
            element.scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        }
    }
    
    // Función para toggle FAQ
    function toggleFAQ(button) {
        const faqItem = button.closest('.faq-item');
        const answer = faqItem.querySelector('.faq-answer');
        const icon = faqItem.querySelector('.faq-icon');
        
        // Cerrar otros FAQs abiertos
        document.querySelectorAll('.faq-item.active').forEach(item => {
            if (item !== faqItem) {
                item.classList.remove('active');
                item.querySelector('.faq-icon').textContent = '+';
            }
        });
        
        // Toggle el FAQ actual
        faqItem.classList.toggle('active');
        icon.textContent = faqItem.classList.contains('active') ? '−' : '+';
    }
    
    // Inicialización
    document.addEventListener('DOMContentLoaded', function() {
        // Animación de entrada para las cards
        const cards = document.querySelectorAll('.help-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
</script>
@endpush