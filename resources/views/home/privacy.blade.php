@extends('layouts.app')

@section('title', 'Política de Privacidad - Sistema de Impresiones')
@section('description', 'Política de privacidad y protección de datos del sistema de impresiones Imprimeindo.')

@push('styles')
<style>
    .privacy-container {
        background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
    }
    
    .privacy-section {
        scroll-margin-top: 100px;
    }
    
    .privacy-nav {
        position: sticky;
        top: 80px;
        background: var(--bg-primary);
        border-radius: 12px;
        box-shadow: var(--shadow-lg);
    }
    
    .privacy-nav a {
        transition: all var(--transition-normal);
    }
    
    .privacy-nav a:hover,
    .privacy-nav a.active {
        background: var(--success-green);
        color: white;
    }
    
    .security-box {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border-left: 4px solid var(--success-green);
    }
    
    .data-box {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border-left: 4px solid var(--primary-blue);
    }
    
    .warning-box {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-left: 4px solid var(--warning-yellow);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-secondary">
    <!-- Hero Section -->
    <section class="privacy-container py-16 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <div class="mb-8">
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-4">
                    🔒 Política de Privacidad
                </h1>
                <p class="text-xl md:text-2xl text-blue-100 mb-2">
                    Protección de Datos en Imprimeindo
                </p>
                <p class="text-lg text-blue-200">
                    Última actualización: {{ now()->format('d/m/Y') }}
                </p>
            </div>
        </div>
    </section>

    <!-- Content Section -->
    <section class="py-12 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Navigation Sidebar -->
                <div class="lg:col-span-1">
                    <nav class="privacy-nav p-6">
                        <h3 class="font-semibold text-primary mb-4">Índice</h3>
                        <ul class="space-y-2">
                            <li>
                                <a href="#introduction" class="block px-3 py-2 rounded-lg text-sm text-secondary hover:bg-success-green hover:text-white">
                                    1. Introducción
                                </a>
                            </li>
                            <li>
                                <a href="#data-collection" class="block px-3 py-2 rounded-lg text-sm text-secondary hover:bg-success-green hover:text-white">
                                    2. Datos Recopilados
                                </a>
                            </li>
                            <li>
                                <a href="#data-usage" class="block px-3 py-2 rounded-lg text-sm text-secondary hover:bg-success-green hover:text-white">
                                    3. Uso de Datos
                                </a>
                            </li>
                            <li>
                                <a href="#data-sharing" class="block px-3 py-2 rounded-lg text-sm text-secondary hover:bg-success-green hover:text-white">
                                    4. Compartir Datos
                                </a>
                            </li>
                            <li>
                                <a href="#data-security" class="block px-3 py-2 rounded-lg text-sm text-secondary hover:bg-success-green hover:text-white">
                                    5. Seguridad
                                </a>
                            </li>
                            <li>
                                <a href="#data-retention" class="block px-3 py-2 rounded-lg text-sm text-secondary hover:bg-success-green hover:text-white">
                                    6. Retención de Datos
                                </a>
                            </li>
                            <li>
                                <a href="#user-rights" class="block px-3 py-2 rounded-lg text-sm text-secondary hover:bg-success-green hover:text-white">
                                    7. Derechos del Usuario
                                </a>
                            </li>
                            <li>
                                <a href="#cookies" class="block px-3 py-2 rounded-lg text-sm text-secondary hover:bg-success-green hover:text-white">
                                    8. Cookies
                                </a>
                            </li>
                            <li>
                                <a href="#contact-privacy" class="block px-3 py-2 rounded-lg text-sm text-secondary hover:bg-success-green hover:text-white">
                                    9. Contacto
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>

                <!-- Main Content -->
                <div class="lg:col-span-3">
                    <div class="bg-primary rounded-2xl shadow-lg p-8 space-y-12">
                        
                        <!-- Introducción -->
                        <section id="introduction" class="privacy-section">
                            <div class="security-box p-6 rounded-lg">
                                <h2 class="text-2xl font-bold text-primary mb-4">
                                    🛡️ Compromiso con su Privacidad
                                </h2>
                                <p class="text-secondary">
                                    En Imprimeindo, respetamos y protegemos la privacidad de nuestros usuarios. 
                                    Esta política explica cómo recopilamos, utilizamos y protegemos su información personal 
                                    cuando utiliza nuestro sistema de impresiones inteligente.
                                </p>
                            </div>
                        </section>

                        <!-- 1. Datos Recopilados -->
                        <section id="data-collection" class="privacy-section">
                            <h2 class="text-2xl font-bold text-primary mb-4">
                                1. Información que Recopilamos
                            </h2>
                            <div class="space-y-6 text-secondary">
                                
                                <div>
                                    <h3 class="text-lg font-semibold text-primary mb-3">
                                        📄 Documentos y Archivos
                                    </h3>
                                    <ul class="list-disc list-inside space-y-2 ml-4">
                                        <li>Archivos que usted sube para imprimir (PDF, imágenes, documentos)</li>
                                        <li>Metadatos de archivos (nombre, tamaño, tipo, fecha de creación)</li>
                                        <li>Configuraciones de impresión seleccionadas</li>
                                    </ul>
                                </div>

                                <div>
                                    <h3 class="text-lg font-semibold text-primary mb-3">
                                        📱 Información Técnica
                                    </h3>
                                    <ul class="list-disc list-inside space-y-2 ml-4">
                                        <li>Dirección IP y ubicación aproximada</li>
                                        <li>Tipo de dispositivo y navegador utilizado</li>
                                        <li>Fecha y hora de acceso al servicio</li>
                                        <li>Páginas visitadas y acciones realizadas</li>
                                    </ul>
                                </div>

                                <div>
                                    <h3 class="text-lg font-semibold text-primary mb-3">
                                        💳 Información de Pago
                                    </h3>
                                    <ul class="list-disc list-inside space-y-2 ml-4">
                                        <li>Información de facturación (procesada por terceros seguros)</li>
                                        <li>Historial de transacciones</li>
                                        <li>Métodos de pago utilizados</li>
                                    </ul>
                                </div>

                                <div class="data-box p-4 rounded-lg">
                                    <p class="text-primary-blue font-medium">
                                        💡 <strong>Importante:</strong> No almacenamos información completa de tarjetas de crédito en nuestros servidores.
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- 2. Uso de Datos -->
                        <section id="data-usage" class="privacy-section">
                            <h2 class="text-2xl font-bold text-primary mb-4">
                                2. Cómo Utilizamos su Información
                            </h2>
                            <div class="space-y-4 text-secondary">
                                <p>Utilizamos la información recopilada para:</p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-3">
                                        <h4 class="font-semibold text-primary">🖨️ Servicios de Impresión</h4>
                                        <ul class="list-disc list-inside space-y-1 text-sm">
                                            <li>Procesar y ejecutar trabajos de impresión</li>
                                            <li>Gestionar colas de impresión</li>
                                            <li>Optimizar calidad y configuraciones</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="space-y-3">
                                        <h4 class="font-semibold text-primary">📊 Mejora del Servicio</h4>
                                        <ul class="list-disc list-inside space-y-1 text-sm">
                                            <li>Analizar patrones de uso</li>
                                            <li>Identificar y solucionar problemas</li>
                                            <li>Desarrollar nuevas funcionalidades</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="space-y-3">
                                        <h4 class="font-semibold text-primary">💰 Facturación y Pagos</h4>
                                        <ul class="list-disc list-inside space-y-1 text-sm">
                                            <li>Procesar pagos de servicios</li>
                                            <li>Generar facturas y recibos</li>
                                            <li>Gestionar reembolsos si aplica</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="space-y-3">
                                        <h4 class="font-semibold text-primary">🛡️ Seguridad y Soporte</h4>
                                        <ul class="list-disc list-inside space-y-1 text-sm">
                                            <li>Prevenir fraudes y abusos</li>
                                            <li>Proporcionar soporte técnico</li>
                                            <li>Cumplir con obligaciones legales</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- 3. Compartir Datos -->
                        <section id="data-sharing" class="privacy-section">
                            <h2 class="text-2xl font-bold text-primary mb-4">
                                3. Compartir Información
                            </h2>
                            <div class="space-y-4 text-secondary">
                                <p>
                                    <strong>No vendemos ni alquilamos</strong> su información personal a terceros. 
                                    Podemos compartir información limitada en las siguientes circunstancias:
                                </p>
                                
                                <div class="space-y-4">
                                    <div>
                                        <h4 class="font-semibold text-primary mb-2">🏢 Proveedores de Servicios</h4>
                                        <p class="text-sm">
                                            Compartimos datos mínimos necesarios con proveedores que nos ayudan a operar el servicio 
                                            (procesamiento de pagos, hosting, soporte técnico).
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <h4 class="font-semibold text-primary mb-2">⚖️ Requerimientos Legales</h4>
                                        <p class="text-sm">
                                            Podemos divulgar información cuando sea requerido por ley, orden judicial, 
                                            o para proteger nuestros derechos legales.
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <h4 class="font-semibold text-primary mb-2">🔄 Transferencias Comerciales</h4>
                                        <p class="text-sm">
                                            En caso de fusión, adquisición o venta de activos, 
                                            la información puede transferirse como parte de la transacción.
                                        </p>
                                    </div>
                                </div>

                                <div class="warning-box p-4 rounded-lg">
                                    <p class="text-warning-yellow font-medium">
                                        ⚠️ <strong>Garantía:</strong> Todos los terceros están obligados contractualmente a proteger su información.
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- 4. Seguridad de Datos -->
                        <section id="data-security" class="privacy-section">
                            <h2 class="text-2xl font-bold text-primary mb-4">
                                4. Seguridad de la Información
                            </h2>
                            <div class="space-y-4 text-secondary">
                                <p>Implementamos múltiples capas de seguridad para proteger su información:</p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="security-box p-4 rounded-lg">
                                        <h4 class="font-semibold text-success-green mb-2">🔐 Encriptación</h4>
                                        <ul class="list-disc list-inside space-y-1 text-sm">
                                            <li>SSL/TLS para transmisión de datos</li>
                                            <li>Encriptación AES-256 para almacenamiento</li>
                                            <li>Certificados de seguridad actualizados</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="security-box p-4 rounded-lg">
                                        <h4 class="font-semibold text-success-green mb-2">🛡️ Protección de Servidores</h4>
                                        <ul class="list-disc list-inside space-y-1 text-sm">
                                            <li>Firewalls y sistemas de detección</li>
                                            <li>Monitoreo 24/7 de seguridad</li>
                                            <li>Actualizaciones regulares de seguridad</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="security-box p-4 rounded-lg">
                                        <h4 class="font-semibold text-success-green mb-2">👥 Control de Acceso</h4>
                                        <ul class="list-disc list-inside space-y-1 text-sm">
                                            <li>Acceso limitado por roles</li>
                                            <li>Autenticación de dos factores</li>
                                            <li>Auditorías regulares de acceso</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="security-box p-4 rounded-lg">
                                        <h4 class="font-semibold text-success-green mb-2">🗂️ Gestión de Documentos</h4>
                                        <ul class="list-disc list-inside space-y-1 text-sm">
                                            <li>Eliminación automática después de 24h</li>
                                            <li>Borrado seguro de archivos</li>
                                            <li>Sin acceso al contenido de documentos</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- 5. Retención de Datos -->
                        <section id="data-retention" class="privacy-section">
                            <h2 class="text-2xl font-bold text-primary mb-4">
                                5. Retención y Eliminación de Datos
                            </h2>
                            <div class="space-y-4 text-secondary">
                                <p>Mantenemos diferentes tipos de información por períodos específicos:</p>
                                
                                <div class="space-y-4">
                                    <div class="data-box p-4 rounded-lg">
                                        <h4 class="font-semibold text-primary-blue mb-2">📄 Documentos de Impresión</h4>
                                        <p class="text-sm">
                                            <strong>Eliminación automática:</strong> 24 horas después de la impresión o cancelación del trabajo.
                                        </p>
                                    </div>
                                    
                                    <div class="data-box p-4 rounded-lg">
                                        <h4 class="font-semibold text-primary-blue mb-2">📊 Datos de Uso</h4>
                                        <p class="text-sm">
                                            <strong>Retención:</strong> 12 meses para análisis y mejora del servicio, luego se anonimizan.
                                        </p>
                                    </div>
                                    
                                    <div class="data-box p-4 rounded-lg">
                                        <h4 class="font-semibold text-primary-blue mb-2">💳 Información de Facturación</h4>
                                        <p class="text-sm">
                                            <strong>Retención:</strong> 7 años según requerimientos fiscales y contables.
                                        </p>
                                    </div>
                                    
                                    <div class="data-box p-4 rounded-lg">
                                        <h4 class="font-semibold text-primary-blue mb-2">🔒 Logs de Seguridad</h4>
                                        <p class="text-sm">
                                            <strong>Retención:</strong> 90 días para investigación de incidentes de seguridad.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- 6. Derechos del Usuario -->
                        <section id="user-rights" class="privacy-section">
                            <h2 class="text-2xl font-bold text-primary mb-4">
                                6. Sus Derechos de Privacidad
                            </h2>
                            <div class="space-y-4 text-secondary">
                                <p>Usted tiene los siguientes derechos respecto a su información personal:</p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <h4 class="font-semibold text-primary mb-2">👁️ Derecho de Acceso</h4>
                                        <p class="text-sm">Solicitar información sobre qué datos personales tenemos sobre usted.</p>
                                    </div>
                                    
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <h4 class="font-semibold text-primary mb-2">✏️ Derecho de Rectificación</h4>
                                        <p class="text-sm">Corregir información personal inexacta o incompleta.</p>
                                    </div>
                                    
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <h4 class="font-semibold text-primary mb-2">🗑️ Derecho de Eliminación</h4>
                                        <p class="text-sm">Solicitar la eliminación de sus datos personales en ciertas circunstancias.</p>
                                    </div>
                                    
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <h4 class="font-semibold text-primary mb-2">📤 Derecho de Portabilidad</h4>
                                        <p class="text-sm">Recibir sus datos en un formato estructurado y legible por máquina.</p>
                                    </div>
                                    
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <h4 class="font-semibold text-primary mb-2">⛔ Derecho de Oposición</h4>
                                        <p class="text-sm">Oponerse al procesamiento de sus datos para ciertos propósitos.</p>
                                    </div>
                                    
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <h4 class="font-semibold text-primary mb-2">⏸️ Derecho de Limitación</h4>
                                        <p class="text-sm">Solicitar la limitación del procesamiento de sus datos.</p>
                                    </div>
                                </div>
                                
                                <div class="security-box p-4 rounded-lg">
                                    <p class="text-success-green font-medium">
                                        📧 <strong>Para ejercer sus derechos:</strong> Contacte a privacy@imprimeindo.com con su solicitud específica.
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- 7. Cookies -->
                        <section id="cookies" class="privacy-section">
                            <h2 class="text-2xl font-bold text-primary mb-4">
                                7. Uso de Cookies y Tecnologías Similares
                            </h2>
                            <div class="space-y-4 text-secondary">
                                <p>Utilizamos cookies y tecnologías similares para mejorar su experiencia:</p>
                                
                                <div class="space-y-4">
                                    <div>
                                        <h4 class="font-semibold text-primary mb-2">🍪 Cookies Esenciales</h4>
                                        <p class="text-sm">
                                            Necesarias para el funcionamiento básico del sitio (sesiones, seguridad, preferencias).
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <h4 class="font-semibold text-primary mb-2">📈 Cookies Analíticas</h4>
                                        <p class="text-sm">
                                            Nos ayudan a entender cómo los usuarios interactúan con nuestro servicio (Google Analytics).
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <h4 class="font-semibold text-primary mb-2">⚙️ Cookies Funcionales</h4>
                                        <p class="text-sm">
                                            Recuerdan sus preferencias y configuraciones para mejorar la experiencia de usuario.
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="data-box p-4 rounded-lg">
                                    <p class="text-primary-blue font-medium">
                                        🔧 <strong>Control de Cookies:</strong> Puede gestionar las cookies a través de la configuración de su navegador.
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- 8. Contacto -->
                        <section id="contact-privacy" class="privacy-section">
                            <h2 class="text-2xl font-bold text-primary mb-4">
                                8. Contacto sobre Privacidad
                            </h2>
                            <div class="space-y-4 text-secondary">
                                <p>
                                    Para preguntas, solicitudes o inquietudes sobre esta política de privacidad 
                                    o el manejo de sus datos personales:
                                </p>
                                
                                <div class="bg-light rounded-lg p-6 space-y-3">
                                    <h4 class="font-semibold text-primary">📧 Oficial de Protección de Datos</h4>
                                    <div class="space-y-2 text-sm">
                                        <p><strong>Email:</strong> privacy@imprimeindo.com</p>
                                        <p><strong>Teléfono:</strong> +1 (555) 123-4567 ext. 101</p>
                                        <p><strong>Dirección:</strong> 123 Tech Street, Innovation City, IC 12345</p>
                                        <p><strong>Horario:</strong> Lunes a Viernes, 9:00 AM - 6:00 PM</p>
                                    </div>
                                </div>
                                
                                <div class="security-box p-4 rounded-lg">
                                    <p class="text-success-green font-medium">
                                        ⏱️ <strong>Tiempo de respuesta:</strong> Responderemos a su solicitud dentro de 30 días hábiles.
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- Cambios en la Política -->
                        <section class="privacy-section">
                            <div class="data-box p-6 rounded-lg">
                                <h3 class="text-xl font-bold text-primary mb-3">
                                    📝 Cambios en esta Política
                                </h3>
                                <p class="text-secondary mb-4">
                                    Podemos actualizar esta política de privacidad ocasionalmente para reflejar cambios 
                                    en nuestras prácticas o por razones legales. Le notificaremos sobre cambios significativos 
                                    a través del servicio o por email.
                                </p>
                                <p class="text-sm text-secondary">
                                    <strong>Versión actual:</strong> 2.1 | 
                                    <strong>Última actualización:</strong> {{ now()->format('d \d\e F \d\e Y') }}
                                </p>
                            </div>
                        </section>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Navigation Footer -->
    <section class="py-8 px-4 bg-light">
        <div class="max-w-4xl mx-auto text-center">
            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('home') }}" 
                   class="bg-primary-blue hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors">
                    🏠 Volver al Inicio
                </a>
                <a href="{{ route('home.terms') }}" 
                   class="bg-warning-yellow hover:bg-yellow-600 text-white font-semibold py-2 px-6 rounded-lg transition-colors">
                    📋 Términos de Servicio
                </a>
                <a href="{{ route('home.help') }}" 
                   class="bg-success-green hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors">
                    💡 Centro de Ayuda
                </a>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Smooth scrolling para los enlaces de navegación
        const navLinks = document.querySelectorAll('.privacy-nav a[href^="#"]');
        
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    
                    // Actualizar navegación activa
                    navLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                }
            });
        });
        
        // Highlight de sección activa al hacer scroll
        const sections = document.querySelectorAll('.privacy-section');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const id = entry.target.id;
                    navLinks.forEach(link => {
                        link.classList.remove('active');
                        if (link.getAttribute('href') === `#${id}`) {
                            link.classList.add('active');
                        }
                    });
                }
            });
        }, {
            rootMargin: '-20% 0px -70% 0px'
        });
        
        sections.forEach(section => {
            if (section.id) {
                observer.observe(section);
            }
        });
        
        // Animación de entrada para las secciones
        const boxes = document.querySelectorAll('.security-box, .data-box, .warning-box');
        boxes.forEach((box, index) => {
            box.style.opacity = '0';
            box.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                box.style.transition = 'all 0.6s ease';
                box.style.opacity = '1';
                box.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
</script>
@endpush