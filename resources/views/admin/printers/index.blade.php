@extends('layouts.app')

@section('title', 'Gestión de Impresoras - Imprimeindo Admin')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-gray-700 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Gestión de Impresoras</h1>
                        <p class="text-sm text-gray-600">Administra y monitorea todas las impresoras del sistema</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <button id="refreshPrinters" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span>Actualizar</span>
                    </button>
                    <button id="addPrinter" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span>Nueva Impresora</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Estadísticas Rápidas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Impresoras</p>
                        <p class="text-2xl font-bold text-gray-900" id="totalPrinters">0</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Activas</p>
                        <p class="text-2xl font-bold text-green-600" id="activePrinters">0</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">En Cola</p>
                        <p class="text-2xl font-bold text-yellow-600" id="queuedJobs">0</p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Con Errores</p>
                        <p class="text-2xl font-bold text-red-600" id="errorPrinters">0</p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Impresoras -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Impresoras Registradas</h2>
                    <div class="flex items-center space-x-3">
                        <div class="relative">
                            <input type="text" id="searchPrinters" placeholder="Buscar impresoras..." 
                                   class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <select id="filterStatus" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Todos los estados</option>
                            <option value="online">En línea</option>
                            <option value="offline">Fuera de línea</option>
                            <option value="busy">Ocupada</option>
                            <option value="error">Con error</option>
                            <option value="maintenance">Mantenimiento</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Impresora</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ubicación</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cola</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última Actividad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="printersTableBody" class="bg-white divide-y divide-gray-200">
                        <!-- Las impresoras se cargarán dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Agregar/Editar Impresora -->
<div id="printerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 id="modalTitle" class="text-lg font-semibold text-gray-900">Nueva Impresora</h3>
                <button id="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <form id="printerForm" class="p-6 space-y-6">
            <input type="hidden" id="printerId" name="printer_id">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="printerName" class="block text-sm font-medium text-gray-700 mb-2">Nombre de la Impresora</label>
                    <input type="text" id="printerName" name="name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Ej: Impresora Principal">
                </div>

                <div>
                    <label for="printerModel" class="block text-sm font-medium text-gray-700 mb-2">Modelo</label>
                    <select id="printerModel" name="model" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Seleccionar modelo</option>
                        <option value="EPSON L325">EPSON L325</option>
                        <option value="EPSON L355">EPSON L355</option>
                        <option value="EPSON L365">EPSON L365</option>
                        <option value="EPSON L375">EPSON L375</option>
                        <option value="EPSON L395">EPSON L395</option>
                        <option value="HP DeskJet">HP DeskJet</option>
                        <option value="Canon PIXMA">Canon PIXMA</option>
                    </select>
                </div>

                <div>
                    <label for="printerIp" class="block text-sm font-medium text-gray-700 mb-2">Dirección IP</label>
                    <input type="text" id="printerIp" name="ip_address" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="192.168.1.100">
                </div>

                <div>
                    <label for="printerPort" class="block text-sm font-medium text-gray-700 mb-2">Puerto</label>
                    <input type="number" id="printerPort" name="port" value="9100"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label for="printerLocation" class="block text-sm font-medium text-gray-700 mb-2">Ubicación</label>
                    <input type="text" id="printerLocation" name="location"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Ej: Planta Baja - Área de Servicio">
                </div>

                <div>
                    <label for="printerPriority" class="block text-sm font-medium text-gray-700 mb-2">Prioridad</label>
                    <select id="printerPriority" name="priority"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="1">Alta (1)</option>
                        <option value="2">Media (2)</option>
                        <option value="3">Baja (3)</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="printerDescription" class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                <textarea id="printerDescription" name="description" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          placeholder="Descripción opcional de la impresora"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="flex items-center">
                    <input type="checkbox" id="printerActive" name="is_active" checked
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="printerActive" class="ml-2 block text-sm text-gray-900">Activa</label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="printerColorSupport" name="supports_color"
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="printerColorSupport" class="ml-2 block text-sm text-gray-900">Soporte Color</label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="printerDuplexSupport" name="supports_duplex"
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="printerDuplexSupport" class="ml-2 block text-sm text-gray-900">Impresión Doble Cara</label>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <button type="button" id="cancelModal" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Cancelar
                </button>
                <button type="button" id="testConnection" class="px-4 py-2 text-blue-700 bg-blue-100 hover:bg-blue-200 rounded-lg transition-colors">
                    Probar Conexión
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    Guardar Impresora
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Detalles de Impresora -->
<div id="printerDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Detalles de la Impresora</h3>
                <button id="closeDetailsModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div id="printerDetailsContent" class="p-6">
            <!-- El contenido se cargará dinámicamente -->
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let printers = [];
    let currentPrinterId = null;
    let refreshInterval = null;

    // Elementos del DOM
    const printersTableBody = document.getElementById('printersTableBody');
    const printerModal = document.getElementById('printerModal');
    const printerDetailsModal = document.getElementById('printerDetailsModal');
    const printerForm = document.getElementById('printerForm');
    const searchInput = document.getElementById('searchPrinters');
    const filterStatus = document.getElementById('filterStatus');

    // Inicializar
    loadPrinters();
    startAutoRefresh();

    // Event Listeners
    document.getElementById('addPrinter').addEventListener('click', () => openPrinterModal());
    document.getElementById('refreshPrinters').addEventListener('click', loadPrinters);
    document.getElementById('closeModal').addEventListener('click', closePrinterModal);
    document.getElementById('cancelModal').addEventListener('click', closePrinterModal);
    document.getElementById('closeDetailsModal').addEventListener('click', closeDetailsModal);
    document.getElementById('testConnection').addEventListener('click', testPrinterConnection);
    
    printerForm.addEventListener('submit', savePrinter);
    searchInput.addEventListener('input', filterPrinters);
    filterStatus.addEventListener('change', filterPrinters);

    // Funciones principales
    async function loadPrinters() {
        try {
            showLoading();
            const response = await fetch('/api/printers');
            const data = await response.json();
            
            if (data.success) {
                printers = data.data;
                renderPrinters(printers);
                updateStatistics();
            } else {
                showNotification('Error al cargar impresoras', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error de conexión', 'error');
        } finally {
            hideLoading();
        }
    }

    function renderPrinters(printersToRender) {
        printersTableBody.innerHTML = '';
        
        if (printersToRender.length === 0) {
            printersTableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        <p class="text-lg font-medium">No hay impresoras registradas</p>
                        <p class="text-sm">Agrega tu primera impresora para comenzar</p>
                    </td>
                </tr>
            `;
            return;
        }

        printersToRender.forEach(printer => {
            const row = createPrinterRow(printer);
            printersTableBody.appendChild(row);
        });
    }

    function createPrinterRow(printer) {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50 transition-colors';
        
        const statusInfo = getStatusInfo(printer.status);
        const queueCount = printer.queue_count || 0;
        const lastActivity = formatDate(printer.last_activity);

        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">${printer.name}</div>
                        <div class="text-sm text-gray-500">${printer.model} • ${printer.ip_address}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusInfo.class}">
                    <span class="w-2 h-2 rounded-full ${statusInfo.dotClass} mr-1.5"></span>
                    ${statusInfo.text}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${printer.location || 'No especificada'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <div class="flex items-center">
                    <span class="text-lg font-semibold ${queueCount > 0 ? 'text-yellow-600' : 'text-gray-400'}">${queueCount}</span>
                    <span class="text-xs text-gray-500 ml-1">trabajos</span>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${lastActivity}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center space-x-2">
                    <button onclick="viewPrinterDetails('${printer._id}')" class="text-blue-600 hover:text-blue-900 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                    <button onclick="editPrinter('${printer._id}')" class="text-indigo-600 hover:text-indigo-900 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <button onclick="togglePrinter('${printer._id}', ${printer.is_active})" class="text-${printer.is_active ? 'red' : 'green'}-600 hover:text-${printer.is_active ? 'red' : 'green'}-900 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            ${printer.is_active ? 
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>' :
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
                            }
                        </svg>
                    </button>
                    <button onclick="deletePrinter('${printer._id}')" class="text-red-600 hover:text-red-900 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </td>
        `;

        return row;
    }

    function getStatusInfo(status) {
        const statusMap = {
            'online': { text: 'En línea', class: 'bg-green-100 text-green-800', dotClass: 'bg-green-400' },
            'offline': { text: 'Fuera de línea', class: 'bg-gray-100 text-gray-800', dotClass: 'bg-gray-400' },
            'busy': { text: 'Ocupada', class: 'bg-yellow-100 text-yellow-800', dotClass: 'bg-yellow-400' },
            'error': { text: 'Error', class: 'bg-red-100 text-red-800', dotClass: 'bg-red-400' },
            'maintenance': { text: 'Mantenimiento', class: 'bg-blue-100 text-blue-800', dotClass: 'bg-blue-400' }
        };
        return statusMap[status] || statusMap['offline'];
    }

    function updateStatistics() {
        const total = printers.length;
        const active = printers.filter(p => p.status === 'online').length;
        const queued = printers.reduce((sum, p) => sum + (p.queue_count || 0), 0);
        const errors = printers.filter(p => p.status === 'error').length;

        document.getElementById('totalPrinters').textContent = total;
        document.getElementById('activePrinters').textContent = active;
        document.getElementById('queuedJobs').textContent = queued;
        document.getElementById('errorPrinters').textContent = errors;
    }

    function filterPrinters() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusFilter = filterStatus.value;

        const filtered = printers.filter(printer => {
            const matchesSearch = printer.name.toLowerCase().includes(searchTerm) ||
                                printer.model.toLowerCase().includes(searchTerm) ||
                                (printer.location && printer.location.toLowerCase().includes(searchTerm));
            
            const matchesStatus = !statusFilter || printer.status === statusFilter;

            return matchesSearch && matchesStatus;
        });

        renderPrinters(filtered);
    }

    function openPrinterModal(printerId = null) {
        currentPrinterId = printerId;
        const modal = document.getElementById('printerModal');
        const title = document.getElementById('modalTitle');
        
        if (printerId) {
            title.textContent = 'Editar Impresora';
            loadPrinterData(printerId);
        } else {
            title.textContent = 'Nueva Impresora';
            printerForm.reset();
            document.getElementById('printerActive').checked = true;
        }
        
        modal.classList.remove('hidden');
    }

    function closePrinterModal() {
        document.getElementById('printerModal').classList.add('hidden');
        currentPrinterId = null;
        printerForm.reset();
    }

    function closeDetailsModal() {
        document.getElementById('printerDetailsModal').classList.add('hidden');
    }

    async function loadPrinterData(printerId) {
        try {
            const response = await fetch(`/api/printers/${printerId}`);
            const data = await response.json();
            
            if (data.success) {
                const printer = data.data;
                document.getElementById('printerId').value = printer._id;
                document.getElementById('printerName').value = printer.name;
                document.getElementById('printerModel').value = printer.model;
                document.getElementById('printerIp').value = printer.ip_address;
                document.getElementById('printerPort').value = printer.port;
                document.getElementById('printerLocation').value = printer.location || '';
                document.getElementById('printerPriority').value = printer.priority;
                document.getElementById('printerDescription').value = printer.description || '';
                document.getElementById('printerActive').checked = printer.is_active;
                document.getElementById('printerColorSupport').checked = printer.supports_color;
                document.getElementById('printerDuplexSupport').checked = printer.supports_duplex;
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al cargar datos de la impresora', 'error');
        }
    }

    async function savePrinter(e) {
        e.preventDefault();
        
        const formData = new FormData(printerForm);
        const printerData = {
            name: formData.get('name'),
            model: formData.get('model'),
            ip_address: formData.get('ip_address'),
            port: parseInt(formData.get('port')),
            location: formData.get('location'),
            priority: parseInt(formData.get('priority')),
            description: formData.get('description'),
            is_active: document.getElementById('printerActive').checked,
            supports_color: document.getElementById('printerColorSupport').checked,
            supports_duplex: document.getElementById('printerDuplexSupport').checked
        };

        try {
            const url = currentPrinterId ? `/api/printers/${currentPrinterId}` : '/api/printers';
            const method = currentPrinterId ? 'PUT' : 'POST';
            
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(printerData)
            });

            const data = await response.json();
            
            if (data.success) {
                showNotification(currentPrinterId ? 'Impresora actualizada correctamente' : 'Impresora agregada correctamente', 'success');
                closePrinterModal();
                loadPrinters();
            } else {
                showNotification(data.message || 'Error al guardar la impresora', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error de conexión', 'error');
        }
    }

    async function testPrinterConnection() {
        const ip = document.getElementById('printerIp').value;
        const port = document.getElementById('printerPort').value;
        
        if (!ip || !port) {
            showNotification('Ingresa la IP y puerto de la impresora', 'warning');
            return;
        }

        try {
            const response = await fetch('/api/printers/test-connection', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ ip_address: ip, port: parseInt(port) })
            });

            const data = await response.json();
            
            if (data.success) {
                showNotification('Conexión exitosa con la impresora', 'success');
            } else {
                showNotification('No se pudo conectar con la impresora', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al probar la conexión', 'error');
        }
    }

    // Funciones globales para los botones de la tabla
    window.editPrinter = function(printerId) {
        openPrinterModal(printerId);
    };

    window.viewPrinterDetails = async function(printerId) {
        try {
            const response = await fetch(`/api/printers/${printerId}/details`);
            const data = await response.json();
            
            if (data.success) {
                showPrinterDetails(data.data);
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al cargar detalles', 'error');
        }
    };

    window.togglePrinter = async function(printerId, currentStatus) {
        try {
            const response = await fetch(`/api/printers/${printerId}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();
            
            if (data.success) {
                showNotification(`Impresora ${currentStatus ? 'desactivada' : 'activada'} correctamente`, 'success');
                loadPrinters();
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al cambiar estado', 'error');
        }
    };

    window.deletePrinter = async function(printerId) {
        if (!confirm('¿Estás seguro de que deseas eliminar esta impresora?')) {
            return;
        }

        try {
            const response = await fetch(`/api/printers/${printerId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();
            
            if (data.success) {
                showNotification('Impresora eliminada correctamente', 'success');
                loadPrinters();
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al eliminar impresora', 'error');
        }
    };

    function showPrinterDetails(printer) {
        const content = document.getElementById('printerDetailsContent');
        const statusInfo = getStatusInfo(printer.status);
        
        content.innerHTML = `
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-6">
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Información General</h4>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Nombre:</dt>
                                <dd class="text-sm text-gray-900">${printer.name}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Modelo:</dt>
                                <dd class="text-sm text-gray-900">${printer.model}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Estado:</dt>
                                <dd><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusInfo.class}">
                                    <span class="w-2 h-2 rounded-full ${statusInfo.dotClass} mr-1.5"></span>
                                    ${statusInfo.text}
                                </span></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">IP:</dt>
                                <dd class="text-sm text-gray-900">${printer.ip_address}:${printer.port}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Ubicación:</dt>
                                <dd class="text-sm text-gray-900">${printer.location || 'No especificada'}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Capacidades</h4>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 ${printer.supports_color ? 'text-green-500' : 'text-gray-400'} mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-sm text-gray-900">Impresión a color</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 ${printer.supports_duplex ? 'text-green-500' : 'text-gray-400'} mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-sm text-gray-900">Impresión doble cara</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Estadísticas</h4>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Trabajos en cola:</dt>
                                <dd class="text-sm font-semibold text-gray-900">${printer.queue_count || 0}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Trabajos completados:</dt>
                                <dd class="text-sm font-semibold text-gray-900">${printer.completed_jobs || 0}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Prioridad:</dt>
                                <dd class="text-sm text-gray-900">${printer.priority}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Última actividad:</dt>
                                <dd class="text-sm text-gray-900">${formatDate(printer.last_activity)}</dd>
                            </div>
                        </dl>
                    </div>

                    ${printer.description ? `
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Descripción</h4>
                        <p class="text-sm text-gray-700">${printer.description}</p>
                    </div>
                    ` : ''}
                </div>
            </div>
        `;
        
        document.getElementById('printerDetailsModal').classList.remove('hidden');
    }

    function startAutoRefresh() {
        refreshInterval = setInterval(loadPrinters, 30000); // Actualizar cada 30 segundos
    }

    function formatDate(dateString) {
        if (!dateString) return 'Nunca';
        const date = new Date(dateString);
        return date.toLocaleString('es-MX');
    }

    function showLoading() {
        // Implementar indicador de carga
    }

    function hideLoading() {
        // Ocultar indicador de carga
    }

    function showNotification(message, type = 'info') {
        // Implementar sistema de notificaciones
        console.log(`${type.toUpperCase()}: ${message}`);
    }

    // Limpiar interval al salir de la página
    window.addEventListener('beforeunload', () => {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    });
});
</script>
@endsection