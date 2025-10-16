<?php

namespace App\Http\Controllers;

use App\Services\SessionService;
use App\Services\PrinterMonitorService;
use App\Models\Printer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    private $sessionService;
    private $printerMonitorService;
    
    public function __construct(
        SessionService $sessionService,
        PrinterMonitorService $printerMonitorService
    ) {
        $this->sessionService = $sessionService;
        $this->printerMonitorService = $printerMonitorService;
    }

    /**
     * Mostrar página principal con QR y estado de impresoras
     */
    public function index(Request $request)
    {
        try {
            // Crear nueva sesión
            $session = $this->sessionService->createSession($request->ip());
            
            // Obtener estado de impresoras
            $systemStatus = $this->printerMonitorService->getSystemStatus();
            $availablePrinters = Printer::where('is_active', true)
                                       ->where('is_available', true)
                                       ->with('status')
                                       ->get();
            
            // Verificar que el QR se haya generado correctamente
            $qrCodeUrl = null;
            if ($session->qr_code_path) {
                $fullPath = storage_path('app/public/' . $session->qr_code_path);
                Log::info('Verificando QR en HomeController', [
                    'session_code' => $session->session_code,
                    'qr_path' => $session->qr_code_path,
                    'full_path' => $fullPath,
                    'file_exists' => file_exists($fullPath)
                ]);
                
                if (file_exists($fullPath)) {
                    $qrCodeUrl = asset('storage/' . $session->qr_code_path);
                } else {
                    Log::warning('Archivo QR no encontrado', ['path' => $fullPath]);
                }
            }
            
            return view('home.index', [
                'session' => $session,
                'systemStatus' => $systemStatus,
                'availablePrinters' => $availablePrinters,
                'qrCodeUrl' => $qrCodeUrl
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading home page: ' . $e->getMessage());
            
            return view('home.error', [
                'message' => 'Error al cargar la página principal'
            ]);
        }
    }

    /**
     * API para obtener estado actualizado del sistema
     */
    public function getSystemStatus()
    {
        try {
            $systemStatus = $this->printerMonitorService->getSystemStatus();
            $availablePrinters = Printer::where('is_active', true)
                                       ->with('status')
                                       ->get()
                                       ->map(function($printer) {
                                           return [
                                               'id' => $printer->_id,
                                               'name' => $printer->name,
                                               'location' => $printer->location,
                                               'is_available' => $printer->is_available,
                                               'status' => $printer->status ? [
                                                   'is_online' => $printer->status->is_online,
                                                   'paper_level' => $printer->status->paper_level,
                                                   'ink_level' => $printer->status->ink_level,
                                                   'status_message' => $printer->status->status_message
                                               ] : null
                                           ];
                                       });
            
            return response()->json([
                'success' => true,
                'system_status' => $systemStatus,
                'printers' => $availablePrinters
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estado del sistema'
            ], 500);
        }
    }

    /**
     * Generar nueva sesión (para renovar QR)
     */
    public function newSession(Request $request)
    {
        try {
            $session = $this->sessionService->createSession($request->ip());
            
            return response()->json([
                'success' => true,
                'session_code' => $session->session_code,
                'qr_code_url' => asset('storage/' . $session->qr_code_path),
                'expires_at' => $session->expires_at->format('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear nueva sesión'
            ], 500);
        }
    }

    /**
     * Información del sistema para la página principal
     */
    public function getSystemInfo()
    {
        try {
            $systemStatus = $this->printerMonitorService->getSystemStatus();
            
            // Información adicional del sistema
            $info = [
                'system_name' => 'Imprimeindo',
                'version' => '1.0.0',
                'status' => $systemStatus,
                'features' => [
                    'multi_printer' => true,
                    'color_printing' => true,
                    'supported_formats' => ['PDF', 'DOC', 'DOCX', 'JPG', 'PNG', 'TXT'],
                    'max_file_size' => '10MB',
                    'session_duration' => '2 horas'
                ],
                'pricing' => [
                    'black_white_per_page' => 2.00,
                    'color_per_page' => 3.00,
                    'currency' => 'MXN'
                ]
            ];
            
            return response()->json([
                'success' => true,
                'info' => $info
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información del sistema'
            ], 500);
        }
    }

    /**
     * Página de ayuda
     */
    public function help()
    {
        return view('home.help');
    }

    /**
     * Términos y condiciones
     */
    public function terms()
    {
        return view('home.terms');
    }

    /**
     * Política de privacidad
     */
    public function privacy()
    {
        return view('home.privacy');
    }
}