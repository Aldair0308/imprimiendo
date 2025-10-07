<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PrinterController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rutas públicas de sesiones
Route::prefix('sessions')->group(function () {
    Route::post('/', [SessionController::class, 'create']); // Crear nueva sesión
    Route::get('/{sessionCode}', [SessionController::class, 'getSession']); // Obtener información de sesión
    Route::post('/{sessionCode}/validate', [SessionController::class, 'validateSession']); // Validar sesión
    Route::delete('/{sessionCode}', [SessionController::class, 'deleteSession']); // Eliminar sesión
    Route::get('/{sessionCode}/status', [SessionController::class, 'getSessionStatus']); // Estado de sesión
});

// Rutas de gestión de archivos
Route::prefix('sessions/{sessionCode}/files')->group(function () {
    Route::post('/', [FileController::class, 'upload']); // Subir archivo individual
    Route::post('/multiple', [FileController::class, 'uploadMultiple']); // Subir múltiples archivos
    Route::get('/{fileId}', [FileController::class, 'getFileInfo']); // Información de archivo
    Route::delete('/{fileId}', [FileController::class, 'delete']); // Eliminar archivo
});

// Rutas de impresión
Route::prefix('print')->group(function () {
    Route::post('/calculate-cost', [PrintController::class, 'calculateCost']); // Calcular costo de impresión
    Route::post('/{sessionCode}/options', [PrintController::class, 'setPrintOptions']); // Configurar opciones de impresión
    Route::post('/{sessionCode}/process', [PrintController::class, 'processPrint']); // Procesar impresión y pago
    Route::get('/{sessionCode}/jobs', [PrintController::class, 'getJobsStatus']); // Estado de trabajos de impresión
    Route::delete('/{sessionCode}/jobs/{jobId}', [PrintController::class, 'cancelJob']); // Cancelar trabajo de impresión
});

// Rutas de impresoras (públicas para selección)
Route::prefix('printers')->group(function () {
    Route::get('/', [PrinterController::class, 'getAvailablePrinters']); // Lista de impresoras disponibles
    Route::get('/{printerId}/status', [PrinterController::class, 'getPrinterStatus']); // Estado de impresora específica
});

// Rutas administrativas (requieren autenticación)
Route::prefix('admin')->middleware('admin.auth')->group(function () {
    
    // Dashboard y estadísticas
    Route::get('/system-status', [AdminController::class, 'getSystemStatus']); // Estado del sistema en tiempo real
    
    // Gestión de impresoras
    Route::prefix('printers')->group(function () {
        Route::post('/', [AdminController::class, 'addPrinter']); // Agregar impresora
        Route::put('/{printerId}', [AdminController::class, 'updatePrinter']); // Actualizar impresora
        Route::delete('/{printerId}', [AdminController::class, 'deletePrinter']); // Eliminar impresora
        Route::post('/{printerId}/test', [PrinterController::class, 'testConnection']); // Probar conexión
        Route::post('/{printerId}/toggle', [PrinterController::class, 'togglePrinter']); // Pausar/reanudar
        Route::post('/{printerId}/clear-queue', [PrinterController::class, 'clearQueue']); // Limpiar cola
        Route::post('/{printerId}/restart', [PrinterController::class, 'restartPrinter']); // Reiniciar impresora
        Route::get('/{printerId}/jobs', [PrinterController::class, 'getPrinterJobs']); // Trabajos de impresora
        Route::get('/{printerId}/config', [PrinterController::class, 'getPrinterConfig']); // Configuración de impresora
    });
    
    // Estadísticas y reportes
    Route::get('/performance-stats', [PrinterController::class, 'getPerformanceStats']); // Estadísticas de rendimiento
    
    // Configuración del sistema
    Route::post('/settings', [AdminController::class, 'updateSettings']); // Actualizar configuración
});

// Rutas de autenticación administrativa
Route::prefix('admin/auth')->group(function () {
    Route::post('/login', [AdminController::class, 'login']); // Login administrativo
    Route::post('/logout', [AdminController::class, 'logout']); // Logout administrativo
});

// Ruta para obtener información del usuario autenticado (si se implementa autenticación de usuarios)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rutas de utilidad
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});

// Ruta para obtener configuración pública del sistema
Route::get('/config', function () {
    return response()->json([
        'max_files_per_session' => env('MAX_FILES_PER_SESSION', 5),
        'max_file_size_mb' => env('MAX_FILE_SIZE_MB', 10),
        'session_timeout_minutes' => env('SESSION_TIMEOUT_MINUTES', 30),
        'supported_file_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt'],
        'price_per_page_bw' => env('PRICE_PER_PAGE_BW', 1.00),
        'price_per_page_color' => env('PRICE_PER_PAGE_COLOR', 3.00),
        'currency' => 'MXN'
    ]);
});