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
     * Subir archivo a la sesión
     */
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:51200|mimes:pdf,docx,doc,txt,jpg,jpeg,png',
                'session_code' => 'required|string'
            ]);

            $sessionCode = $request->input('session_code');
            $session = $this->sessionService->getSessionByCode($sessionCode);
            
            // Verificar límites de la sesión
            $limits = $this->sessionService->checkSessionLimits($session);
            $currentFiles = $this->sessionService->getSessionFiles($session);
            
            if ($currentFiles->count() >= $limits['max_files']) {
                return response()->json([
                    'success' => false,
                    'message' => "Máximo {$limits['max_files']} archivos permitidos"
                ], 400);
            }
            
            $file = $request->file('file');
            $uploadedFile = $this->fileService->uploadFile($file, $session);
            
            return response()->json([
                'success' => true,
                'message' => 'Archivo subido correctamente',
                'file' => [
                    'id' => $uploadedFile->id,
                    'original_name' => $uploadedFile->original_name,
                    'file_size' => $uploadedFile->file_size,
                    'extension' => $uploadedFile->extension
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error uploading file: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al subir el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar archivo de la sesión
     */
    public function removeFile(Request $request)
    {
        try {
            $request->validate([
                'file_id' => 'required|string',
                'session_code' => 'required|string'
            ]);

            $sessionCode = $request->input('session_code');
            $fileId = $request->input('file_id');
            
            $session = $this->sessionService->getSessionByCode($sessionCode);
            $result = $this->fileService->deleteFile($fileId, $session);
            
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Archivo eliminado correctamente'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo eliminar el archivo'
                ], 400);
            }
            
        } catch (\Exception $e) {
            Log::error('Error removing file: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Iniciar impresión de la sesión
     */
    public function print(Request $request)
    {
        try {
            $request->validate([
                'session_code' => 'required|string',
                'printer_id' => 'required|integer',
                'copies' => 'integer|min:1|max:10',
                'color_mode' => 'string|in:color,grayscale,bw',
                'paper_size' => 'string|in:A4,Letter,Legal',
                'orientation' => 'string|in:portrait,landscape'
            ]);

            $sessionCode = $request->input('session_code');
            $session = $this->sessionService->getSessionByCode($sessionCode);
            
            // Verificar que hay archivos
            $files = $this->sessionService->getSessionFiles($session);
            if ($files->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay archivos para imprimir'
                ], 400);
            }
            
            // Verificar que la impresora existe y está disponible
            $printer = Printer::where('id', $request->input('printer_id'))
                             ->where('is_active', true)
                             ->where('is_available', true)
                             ->first();
                             
            if (!$printer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impresora no disponible'
                ], 400);
            }
            
            // Configuración de impresión
            $printSettings = [
                'copies' => $request->input('copies', 1),
                'color_mode' => $request->input('color_mode', 'color'),
                'paper_size' => $request->input('paper_size', 'A4'),
                'orientation' => $request->input('orientation', 'portrait')
            ];
            
            // Crear trabajos de impresión
            $printJobs = [];
            foreach ($files as $file) {
                $printJob = $this->sessionService->createPrintJob($session, $file, $printer, $printSettings);
                $printJobs[] = $printJob;
            }
            
            // Actualizar estado de la sesión
            $this->sessionService->updateSessionStatus($session, 'printing');
            
            return response()->json([
                'success' => true,
                'message' => 'Impresión iniciada correctamente',
                'print_jobs' => count($printJobs),
                'estimated_time' => count($printJobs) * 2 // 2 minutos por trabajo estimado
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error starting print: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar la impresión: ' . $e->getMessage()
            ], 500);
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