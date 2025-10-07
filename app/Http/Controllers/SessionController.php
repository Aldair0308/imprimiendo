<?php

namespace App\Http\Controllers;

use App\Services\SessionService;
use App\Services\FileService;
use App\Models\Printer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SessionController extends Controller
{
    private $sessionService;
    private $fileService;
    
    public function __construct(
        SessionService $sessionService,
        FileService $fileService
    ) {
        $this->sessionService = $sessionService;
        $this->fileService = $fileService;
    }

    /**
     * Mostrar página de sesión con selector de impresoras
     */
    public function show($sessionCode)
    {
        try {
            $session = $this->sessionService->getSessionByCode($sessionCode);
            
            // Obtener impresoras disponibles
            $availablePrinters = Printer::where('is_active', true)
                                       ->where('is_available', true)
                                       ->with('status')
                                       ->get();
            
            // Obtener archivos de la sesión
            $files = $this->sessionService->getSessionFiles($session);
            
            // Verificar límites de la sesión
            $limits = $this->sessionService->checkSessionLimits($session);
            
            return view('session.show', [
                'session' => $session,
                'availablePrinters' => $availablePrinters,
                'files' => $files,
                'limits' => $limits
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading session: ' . $e->getMessage(), [
                'session_code' => $sessionCode
            ]);
            
            return view('session.error', [
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * API para obtener información de la sesión
     */
    public function getSessionInfo($sessionCode)
    {
        try {
            $session = $this->sessionService->getSessionByCode($sessionCode);
            $files = $this->sessionService->getSessionFiles($session);
            $limits = $this->sessionService->checkSessionLimits($session);
            $cost = $this->sessionService->calculateSessionCost($session);
            
            return response()->json([
                'success' => true,
                'session' => [
                    'code' => $session->session_code,
                    'status' => $session->status,
                    'expires_at' => $session->expires_at->format('Y-m-d H:i:s'),
                    'time_remaining' => $session->expires_at->diffInMinutes(now()),
                ],
                'files' => $files->map(function($file) {
                    return [
                        'id' => $file->_id,
                        'name' => $file->original_name,
                        'size' => $file->file_size,
                        'pages' => $file->page_count ?? 1,
                        'type' => $file->extension,
                        'uploaded_at' => $file->uploaded_at->format('Y-m-d H:i:s')
                    ];
                }),
                'limits' => $limits,
                'cost' => $cost
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Extender tiempo de sesión
     */
    public function extend($sessionCode, Request $request)
    {
        try {
            $session = $this->sessionService->getSessionByCode($sessionCode);
            $hours = $request->input('hours', 1);
            
            if ($hours < 1 || $hours > 4) {
                throw new \Exception('Solo se puede extender entre 1 y 4 horas');
            }
            
            $extendedSession = $this->sessionService->extendSession($session, $hours);
            
            return response()->json([
                'success' => true,
                'message' => "Sesión extendida por {$hours} hora(s)",
                'new_expiration' => $extendedSession->expires_at->format('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Actualizar estado de sesión
     */
    public function updateStatus($sessionCode, Request $request)
    {
        $request->validate([
            'status' => 'required|in:active,uploading,configuring,printing,completed,cancelled'
        ]);
        
        try {
            $session = $this->sessionService->getSessionByCode($sessionCode);
            $updatedSession = $this->sessionService->updateSessionStatus(
                $session, 
                $request->status,
                $request->only(['printer_id', 'notes'])
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Estado de sesión actualizado',
                'session' => [
                    'code' => $updatedSession->session_code,
                    'status' => $updatedSession->status,
                    'updated_at' => $updatedSession->status_updated_at->format('Y-m-d H:i:s')
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Cancelar sesión
     */
    public function cancel($sessionCode, Request $request)
    {
        try {
            $session = $this->sessionService->getSessionByCode($sessionCode);
            $reason = $request->input('reason', 'Cancelado por el usuario');
            
            $cancelledSession = $this->sessionService->cancelSession($session, $reason);
            
            return response()->json([
                'success' => true,
                'message' => 'Sesión cancelada correctamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Completar sesión
     */
    public function complete($sessionCode)
    {
        try {
            $session = $this->sessionService->getSessionByCode($sessionCode);
            
            if ($session->status !== 'printing') {
                throw new \Exception('Solo se pueden completar sesiones en estado de impresión');
            }
            
            $completedSession = $this->sessionService->completeSession($session);
            
            return response()->json([
                'success' => true,
                'message' => 'Sesión completada correctamente',
                'session' => [
                    'code' => $completedSession->session_code,
                    'status' => $completedSession->status,
                    'completed_at' => $completedSession->completed_at->format('Y-m-d H:i:s')
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Regenerar código QR de la sesión
     */
    public function regenerateQR($sessionCode)
    {
        try {
            $session = $this->sessionService->getSessionByCode($sessionCode);
            $updatedSession = $this->sessionService->regenerateQRCode($session);
            
            return response()->json([
                'success' => true,
                'message' => 'Código QR regenerado',
                'qr_code_url' => asset('storage/' . $updatedSession->qr_code_path)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Obtener estadísticas de la sesión
     */
    public function getStats($sessionCode)
    {
        try {
            $session = $this->sessionService->getSessionByCode($sessionCode);
            $files = $this->sessionService->getSessionFiles($session);
            $cost = $this->sessionService->calculateSessionCost($session);
            
            $stats = [
                'session_duration' => $session->created_at->diffInMinutes($session->status_updated_at ?? now()),
                'total_files' => $files->count(),
                'total_size' => $files->sum('file_size'),
                'total_pages' => $files->sum('page_count'),
                'file_types' => $files->groupBy('extension')->map->count(),
                'estimated_cost' => $cost['total_cost'],
                'status_history' => [
                    'created' => $session->created_at->format('Y-m-d H:i:s'),
                    'last_update' => ($session->status_updated_at ?? $session->created_at)->format('Y-m-d H:i:s'),
                    'expires' => $session->expires_at->format('Y-m-d H:i:s')
                ]
            ];
            
            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Página de configuración de sesión
     */
    public function configure($sessionCode)
    {
        try {
            $session = $this->sessionService->getSessionByCode($sessionCode);
            $files = $this->sessionService->getSessionFiles($session);
            
            if ($files->isEmpty()) {
                return redirect()->route('session.show', $sessionCode)
                               ->with('error', 'Debe subir al menos un archivo antes de configurar la impresión');
            }
            
            $availablePrinters = Printer::where('is_active', true)
                                       ->where('is_available', true)
                                       ->with('status')
                                       ->get();
            
            $cost = $this->sessionService->calculateSessionCost($session);
            
            return view('session.configure', [
                'session' => $session,
                'files' => $files,
                'availablePrinters' => $availablePrinters,
                'cost' => $cost
            ]);
            
        } catch (\Exception $e) {
            return view('session.error', [
                'message' => $e->getMessage()
            ]);
        }
    }
}