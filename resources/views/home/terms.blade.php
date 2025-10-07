@extends('layouts.app')

@section('title', 'Términos de Servicio - Sistema de Impresiones')
@section('description', 'Términos y condiciones de uso del sistema de impresiones Imprimeindo.')

@push('styles')
<style>
    .terms-container {
        background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
    }
    
    .terms-section {
        scroll-margin-top: 100px;
    }
    
    .terms-nav {
        position: sticky;
        top: 80px;
        background: var(--bg-primary);
        border-radius: 12px;
        box-shadow: var(--shadow-lg);
    }
    
    .terms-nav a {
        transition: all var(--transition-normal);
    }
    
    .terms-nav a:hover,
    .terms-nav a.active {
        background: var(--primary-blue);
        color: white;
    }
    
    .highlight-box {
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
    <section class="terms-container py-16 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <div class="mb-8">
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-4">
                    📋 Términos de Servicio
                </h1>
                <p class="text-xl md:text-2xl text-blue-100 mb-2">
                    Condiciones de Uso de Imprimeindo
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
                    <nav class="terms-nav p-6">
                        <h3 class="font-semibold text-primary mb-4">Índice</h3>
                        <ul class="space-y-2">
                            <li>
                                <a href="#acceptance" class="block px-3 py-2 rounded-lg text-sm text-secondary hover:bg-primary-blue hover:text-white">
                                    1. Aceptación
                                </a>
                            </li>
                            <li>
                                <a href="#service-description" class="block px-3 py-2 rounded-lg text-sm text-secondary hover:bg-primary-blue hover:text-white">
                                    2. Descripción del Servicio
                                </a>
                            </li>
                            <li>
                                <a href="#user-responsibilities" class="block px-3 py-2 rounded-lg text-sm text-secondary hover:bg-primary-blue hover:text-white">
                                    3. Responsabilidades del Usuario
                                </a>
                            </li>
                            <li>
                                <a href="#content-policy" class="block px-3 py-2 rounded-lg text-sm text-secondary hover:bg-primary-blue hover:text-white">
                                    4. Política de Contenido
                                </a>
                            </li>
                            <li>
                                <a href="#payment-terms" class="block px-3 py-2 rounded-lg text-sm text-secondary hover:bg-primary-blue hover:text-white">
                                    5. Términos de Pago
                                </a>
                            </li>
                            <li>
                                <a href="#privacy-data" class="block px-3 py-2 rounded-lg text-sm text-secondary hover:bg-primary-blue hover:text-white">
                                    6. Privacidad y Datos
                                </a>
                            </li>
                            <li>
                                <a href="#limitations" class="block px-3 py-2 rounded-lg text-sm text-secondary hover:bg-primary-blue hover:text-white">
                                    7. Limitaciones
                                </a>
                            </li>
                            <li>
                                <a href="#termination" class="block px-3 py-2 rounded-lg text-sm text-secondary hover:bg-primary-blue hover:text-white">
                                    8. Terminación
                                </a>
                            </li>
                            <li>
                                <a href="#contact-terms" class="block px-3 py-2 rounded-lg text-sm text-secondary hover:bg-primary-blue hover:text-white">
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
                        <div class="highlight-box p-6 rounded-lg">
                            <h2 class="text-2xl font-bold text-primary mb-4">
                                Bienvenido a Imprimeindo
                            </h2>
                            <p class="text-secondary">
                                Estos términos de servicio rigen el uso de nuestro sistema de impresiones inteligente. 
                                Al utilizar nuestro servicio, usted acepta cumplir con estos términos y condiciones.
                            </p>
                        </div>

                        <!-- 1. Aceptación de Términos -->
                        <section id="acceptance" class="terms-section">
                            <h2 class="text-2xl font-bold text-primary mb-4">
                                1. Aceptación de Términos
                            </h2>
                            <div class="space-y-4 text-secondary">
                                <p>
                                    Al acceder y utilizar el servicio de Imprimeindo, usted acepta estar sujeto a estos 
                                    términos de servicio y todas las leyes y regulaciones aplicables.
                                </p>
                                <p>
                                    Si no está de acuerdo con alguno de estos términos, no debe utilizar nuestro servicio.
                                </p>
                                <div class="warning-box p-4 rounded-lg">
                                    <p class="text-warning-yellow font-medium">
                                        ⚠️ <strong>Importante:</strong> El uso del servicio implica la aceptación automática de estos términos.
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- 2. Descripción del Servicio -->
                        <section id="service-description" class="terms-section">
                            <h2 class="text-2xl font-bold text-primary mb-4">
                                2. Descripción del Servicio
                            </h2>
                            <div class="space-y-4 text-secondary">
                                <p>
                                    Imprimeindo es un sistema de impresiones inteligente que permite a los usuarios:
                                </p>
                                <ul class="list-disc list-inside space-y-2 ml-4">
                                    <li>Imprimir documentos mediante códigos QR</li>
                                    <li>Seleccionar entre múltiples impresoras disponibles</li>
                                    <li>Configurar opciones de impresión personalizadas</li>
                                    <li>Monitorear el estado de sus trabajos de impresión</li>
                                </ul>
                                <p>
                                    El servicio está disponible 24/7, sujeto a mantenimiento programado y disponibilidad de equipos.
                                </p>
                            </div>
                        </section>

                        <!-- 3. Responsabilidades del Usuario -->
                        <section id="user-responsibilities" class="terms-section">
                            <h2 class="text-2xl font-bold text-primary mb-4">
                                3. Responsabilidades del Usuario
                            </h2>
                            <div class="space-y-4 text-secondary">
                                <p>Como usuario del servicio, usted se compromete a:</p>
                                <ul class="list-disc list-inside space-y-2 ml-4">
                                    <li>Utilizar el servicio de manera responsable y legal</li>
                                    <li>No imprimir contenido ilegal, ofensivo o que viole derechos de autor</li>
                                    <li>Pagar por todos los servicios de impresión utilizados</li>
                                    <li>Recoger sus documentos impresos en tiempo oportuno</li>
                                    <li>Reportar cualquier problema técnico o mal funcionamiento</li>
                                    <li>No intentar acceder a sistemas o datos no autorizados</li>
                                </ul>
                                <div class="highlight-box p-4 rounded-lg">
                                    <p class="text-primary-blue font-medium">
                                        💡 <strong>Recordatorio:</strong> Usted es responsable de todo el contenido que envíe para imprimir.
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- 4. Política de Contenido -->
                        <section id="content-policy" class="terms-section">
                            <h2 class="text-2xl font-bold text-primary mb-4">
                                4. Política de Contenido
                            </h2>
                            <div class="space-y-4 text-secondary">
                                <p>Está prohibido imprimir contenido que:</p>
                                <ul class="list-disc list-inside space-y-2 ml-4">
                                    <li>Sea ilegal o viole las leyes locales, nacionales o internacionales</li>
                                    <li>Infrinja derechos de autor, marcas registradas u otros derechos de propiedad intelectual</li>
                                    <li>Contenga material pornográfico, violento o discriminatorio</li>
                                    <li>Promueva actividades ilegales o dañinas</li>
                                    <li>Contenga virus, malware o código malicioso</li>
                                </ul>
                                <div class="warning-box p-4 rounded-lg">
                                    <p class="text-warning-yellow font-medium">
                                        ⚠️ <strong>Advertencia:</strong> Nos reservamos el derecho de rechazar cualquier trabajo de impresión que viole esta política.
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- 5. Términos de Pago -->
                        <section id="payment-terms" class="terms-section">
                            <h2 class="text-2xl font-bold text-primary mb-4">
                                5. Términos de Pago
                            </h2>
                            <div class="space-y-4 text-secondary">
                                <p>Los precios y términos de pago incluyen:</p>
                                <ul class="list-disc list-inside space-y-2 ml-4">
                                    <li>Tarifas basadas en número de páginas, calidad y tipo de papel</li>
                                    <li>Pago requerido antes del procesamiento del trabajo</li>
                                    <li>Métodos de pago aceptados: tarjetas de crédito/débito, transferencias</li>
                                    <li>No hay reembolsos por trabajos completados correctamente</li>
                                    <li>Precios sujetos a cambio con notificación previa</li>
                                </ul>
                                <div class="highlight-box p-4 rounded-lg">
                                    <p class="text-primary-blue font-medium">
                                        💳 <strong>Facturación:</strong> Todos los precios incluyen impuestos aplicables.
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- 6. Privacidad y Datos -->
                        <section id="privacy-data" class="terms-section">
                            <h2 class="text-2xl font-bold text-primary mb-4">
                                6. Privacidad y Protección de Datos
                            </h2>
                            <div class="space-y-4 text-secondary">
                                <p>Respecto a sus datos personales y documentos:</p>
                                <ul class="list-disc list-inside space-y-2 ml-4">
                                    <li>Los documentos se eliminan automáticamente después de 24 horas</li>
                                    <li>No accedemos al contenido de sus documentos</li>
                                    <li>Utilizamos encriptación para proteger la transmisión de datos</li>
                                    <li>Cumplimos con las regulaciones de protección de datos aplicables</li>
                                    <li>No compartimos información personal con terceros sin consentimiento</li>
                                </ul>
                                <p>
                                    Para más detalles, consulte nuestra 
                                    <a href="{{ route('home.privacy') }}" class="text-primary-blue hover:underline">
                                        Política de Privacidad
                                    </a>.
                                </p>
                            </div>
                        </section>

                        <!-- 7. Limitaciones y Exenciones -->
                        <section id="limitations" class="terms-section">
                            <h2 class="text-2xl font-bold text-primary mb-4">
                                7. Limitaciones de Responsabilidad
                            </h2>
                            <div class="space-y-4 text-secondary">
                                <p>Nuestras limitaciones de responsabilidad incluyen:</p>
                                <ul class="list-disc list-inside space-y-2 ml-4">
                                    <li>No garantizamos disponibilidad del servicio 100% del tiempo</li>
                                    <li>No somos responsables por pérdida de datos debido a fallas técnicas</li>
                                    <li>La calidad de impresión puede variar según el equipo y materiales</li>
                                    <li>No somos responsables por documentos no recogidos después de 7 días</li>
                                    <li>Nuestra responsabilidad máxima se limita al costo del servicio de impresión</li>
                                </ul>
                                <div class="warning-box p-4 rounded-lg">
                                    <p class="text-warning-yellow font-medium">
                                        ⚠️ <strong>Importante:</strong> El servicio se proporciona "tal como está" sin garantías expresas o implícitas.
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- 8. Terminación del Servicio -->
                        <section id="termination" class="terms-section">
                            <h2 class="text-2xl font-bold text-primary mb-4">
                                8. Terminación del Servicio
                            </h2>
                            <div class="space-y-4 text-secondary">
                                <p>Podemos terminar o suspender el acceso al servicio:</p>
                                <ul class="list-disc list-inside space-y-2 ml-4">
                                    <li>Por violación de estos términos de servicio</li>
                                    <li>Por uso fraudulento o abusivo del sistema</li>
                                    <li>Por falta de pago de servicios utilizados</li>
                                    <li>Por razones técnicas o de mantenimiento</li>
                                    <li>Con o sin previo aviso, según las circunstancias</li>
                                </ul>
                                <p>
                                    Los usuarios pueden discontinuar el uso del servicio en cualquier momento.
                                </p>
                            </div>
                        </section>

                        <!-- 9. Contacto -->
                        <section id="contact-terms" class="terms-section">
                            <h2 class="text-2xl font-bold text-primary mb-4">
                                9. Información de Contacto
                            </h2>
                            <div class="space-y-4 text-secondary">
                                <p>
                                    Para preguntas sobre estos términos de servicio o el uso del sistema, 
                                    puede contactarnos a través de:
                                </p>
                                <div class="bg-light rounded-lg p-4 space-y-2">
                                    <p><strong>Email:</strong> legal@imprimeindo.com</p>
                                    <p><strong>Teléfono:</strong> +1 (555) 123-4567</p>
                                    <p><strong>Dirección:</strong> 123 Tech Street, Innovation City, IC 12345</p>
                                    <p><strong>Horario de atención:</strong> Lunes a Viernes, 9:00 AM - 6:00 PM</p>
                                </div>
                            </div>
                        </section>

                        <!-- Modificaciones -->
                        <section class="terms-section">
                            <div class="highlight-box p-6 rounded-lg">
                                <h3 class="text-xl font-bold text-primary mb-3">
                                    Modificaciones a los Términos
                                </h3>
                                <p class="text-secondary">
                                    Nos reservamos el derecho de modificar estos términos en cualquier momento. 
                                    Los cambios entrarán en vigor inmediatamente después de su publicación en esta página. 
                                    El uso continuado del servicio después de los cambios constituye la aceptación de los nuevos términos.
                                </p>
                                <p class="text-sm text-secondary mt-4">
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
                <a href="{{ route('home.privacy') }}" 
                   class="bg-success-green hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors">
                    🔒 Política de Privacidad
                </a>
                <a href="{{ route('home.help') }}" 
                   class="bg-warning-yellow hover:bg-yellow-600 text-white font-semibold py-2 px-6 rounded-lg transition-colors">
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
        const navLinks = document.querySelectorAll('.terms-nav a[href^="#"]');
        
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
        const sections = document.querySelectorAll('.terms-section');
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
    });
</script>
@endpush