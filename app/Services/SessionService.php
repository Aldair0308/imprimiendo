<?php

namespace App\Services;

use App\Models\Session;
use App\Models\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;

class SessionService
{
    private $fileService;
    
    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Crear nueva sesión de impresión
     */
    public function createSession($clientIp = null)
    {
        try {
            $sessionCode = $this->generateUniqueSessionCode();
            $expiresAt = Carbon::now()->addHours(2); // Sesión válida por 2 horas
            
            $session = Session::create([
                'session_code' => $sessionCode,
                'status' => 'active',
                'client_ip' => $clientIp,
                'created_at' => Carbon::now(),
                'expires_at' => $expiresAt,
                'max_files' => 10,
                'max_total_size' => 50 * 1024 * 1024, // 50MB
                'qr_generated' => false
            ]);
            
            // Generar código QR
            $this->generateQRCode($session);
            
            Log::info('New session created', [
                'session_id' => $session->_id,
                'session_code' => $sessionCode,
                'client_ip' => $clientIp
            ]);
            
            return $session;
            
        } catch (\Exception $e) {
            Log::error('Error creating session: ' . $e->getMessage());
            throw new \Exception('Error al crear la sesión');
        }
    }

    /**
     * Generar código único de sesión
     */
    private function generateUniqueSessionCode()
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Session::where('session_code', $code)->exists());
        
        return $code;
    }

    /**
     * Generar código QR para la sesión
     */
    private function generateQRCode(Session $session)
    {
        try {
            $qrUrl = url("/session/{$session->session_code}");
            
            // Crear instancia de QrCode con Endroid
            $qrCode = new QrCode($qrUrl);
            $qrCode->setSize(300);
            $qrCode->setMargin(10);
            
            // Crear writer SVG
            $writer = new SvgWriter();
            $result = $writer->write($qrCode);
            
            // Obtener el contenido SVG
            $svgContent = $result->getString();
            
            // Guardar QR en storage
            $qrPath = "qr-codes/{$session->session_code}.svg";
            \Storage::disk('public')->put($qrPath, $svgContent);
            
            // Actualizar sesión con información del QR
            $session->update([
                'qr_code_path' => $qrPath,
                'qr_code_url' => $qrUrl,
                'qr_generated' => true,
                'qr_generated_at' => Carbon::now()
            ]);
            
            return $qrPath;
            
        } catch (\Exception $e) {
            Log::error('Error generating QR code: ' . $e->getMessage(), [
                'session_id' => $session->_id
            ]);
        }
    }

    /**
     * Obtener sesión por código
     */
    public function getSessionByCode($sessionCode)
    {
        $session = Session::where('session_code', $sessionCode)->first();
        
        if (!$session) {
            throw new \Exception('Sesión no encontrada');
        }
        
        if ($this->isSessionExpired($session)) {
            throw new \Exception('La sesión ha expirado');
        }
        
        return $session;
    }

    /**
     * Verificar si la sesión ha expirado
     */
    public function isSessionExpired(Session $session)
    {
        return Carbon::now()->isAfter($session->expires_at);
    }

    /**
     * Extender tiempo de sesión
     */
    public function extendSession(Session $session, $additionalHours = 1)
    {
        if ($this->isSessionExpired($session)) {
            throw new \Exception('No se puede extender una sesión expirada');
        }
        
        $newExpirationTime = $session->expires_at->addHours($additionalHours);
        
        $session->update([
            'expires_at' => $newExpirationTime,
            'extended_at' => Carbon::now()
        ]);
        
        Log::info('Session extended', [
            'session_id' => $session->_id,
            'new_expiration' => $newExpirationTime
        ]);
        
        return $session;
    }

    /**
     * Actualizar estado de sesión
     */
    public function updateSessionStatus(Session $session, $status, $additionalData = [])
    {
        $validStatuses = ['active', 'uploading', 'configuring', 'printing', 'completed', 'expired', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            throw new \Exception('Estado de sesión inválido');
        }
        
        $updateData = array_merge([
            'status' => $status,
            'status_updated_at' => Carbon::now()
        ], $additionalData);
        
        $session->update($updateData);
        
        Log::info('Session status updated', [
            'session_id' => $session->_id,
            'old_status' => $session->status,
            'new_status' => $status
        ]);
        
        return $session;
    }

    /**
     * Obtener archivos de la sesión
     */
    public function getSessionFiles(Session $session)
    {
        return File::where('session_id', $session->_id)
                  ->orderBy('uploaded_at', 'desc')
                  ->get();
    }

    /**
     * Verificar límites de la sesión
     */
    public function checkSessionLimits(Session $session, $newFileSize = 0)
    {
        $currentFiles = File::where('session_id', $session->_id)->count();
        $currentTotalSize = File::where('session_id', $session->_id)->sum('file_size');
        
        $limits = [
            'can_add_file' => true,
            'can_add_size' => true,
            'current_files' => $currentFiles,
            'max_files' => $session->max_files,
            'current_size' => $currentTotalSize,
            'max_size' => $session->max_total_size,
            'remaining_files' => $session->max_files - $currentFiles,
            'remaining_size' => $session->max_total_size - $currentTotalSize
        ];
        
        if ($currentFiles >= $session->max_files) {
            $limits['can_add_file'] = false;
            $limits['file_limit_message'] = 'Se ha alcanzado el límite máximo de archivos';
        }
        
        if (($currentTotalSize + $newFileSize) > $session->max_total_size) {
            $limits['can_add_size'] = false;
            $limits['size_limit_message'] = 'Se ha alcanzado el límite máximo de tamaño';
        }
        
        return $limits;
    }

    /**
     * Calcular costo total de la sesión
     */
    public function calculateSessionCost(Session $session)
    {
        $files = $this->getSessionFiles($session);
        $totalCost = 0;
        $totalPages = 0;
        
        foreach ($files as $file) {
            $fileCost = $this->fileService->calculatePrintCost($file);
            $totalCost += $fileCost;
            $totalPages += $file->page_count ?? 1;
        }
        
        return [
            'total_cost' => round($totalCost, 2),
            'total_pages' => $totalPages,
            'total_files' => $files->count(),
            'cost_breakdown' => $files->map(function($file) {
                return [
                    'file_name' => $file->original_name,
                    'pages' => $file->page_count ?? 1,
                    'cost' => $this->fileService->calculatePrintCost($file)
                ];
            })
        ];
    }

    /**
     * Finalizar sesión
     */
    public function completeSession(Session $session)
    {
        $this->updateSessionStatus($session, 'completed', [
            'completed_at' => Carbon::now()
        ]);
        
        // Programar eliminación de archivos después de 1 hora
        $this->scheduleFileCleanup($session, 1);
        
        return $session;
    }

    /**
     * Cancelar sesión
     */
    public function cancelSession(Session $session, $reason = null)
    {
        $this->updateSessionStatus($session, 'cancelled', [
            'cancelled_at' => Carbon::now(),
            'cancellation_reason' => $reason
        ]);
        
        // Eliminar archivos inmediatamente
        $this->fileService->deleteSessionFiles($session);
        
        return $session;
    }

    /**
     * Programar limpieza de archivos
     */
    private function scheduleFileCleanup(Session $session, $hoursDelay = 1)
    {
        // En un entorno real, esto usaría un sistema de colas como Redis
        // Por ahora, solo registramos la programación
        Log::info('File cleanup scheduled', [
            'session_id' => $session->_id,
            'cleanup_at' => Carbon::now()->addHours($hoursDelay)
        ]);
    }

    /**
     * Limpiar sesiones expiradas
     */
    public function cleanupExpiredSessions()
    {
        $expiredSessions = Session::where('expires_at', '<', Carbon::now())
                                 ->whereNotIn('status', ['completed', 'cancelled'])
                                 ->get();
        
        $cleanedCount = 0;
        
        foreach ($expiredSessions as $session) {
            try {
                $this->updateSessionStatus($session, 'expired');
                $this->fileService->deleteSessionFiles($session);
                $cleanedCount++;
                
            } catch (\Exception $e) {
                Log::error('Error cleaning expired session: ' . $e->getMessage(), [
                    'session_id' => $session->_id
                ]);
            }
        }
        
        Log::info('Expired sessions cleanup completed', [
            'cleaned_sessions' => $cleanedCount
        ]);
        
        return $cleanedCount;
    }

    /**
     * Obtener estadísticas de sesiones
     */
    public function getSessionStats($days = 7)
    {
        $startDate = Carbon::now()->subDays($days);
        
        $stats = [
            'total_sessions' => Session::where('created_at', '>=', $startDate)->count(),
            'active_sessions' => Session::where('status', 'active')
                                       ->where('expires_at', '>', Carbon::now())
                                       ->count(),
            'completed_sessions' => Session::where('status', 'completed')
                                          ->where('created_at', '>=', $startDate)
                                          ->count(),
            'expired_sessions' => Session::where('status', 'expired')
                                        ->where('created_at', '>=', $startDate)
                                        ->count(),
            'cancelled_sessions' => Session::where('status', 'cancelled')
                                          ->where('created_at', '>=', $startDate)
                                          ->count(),
            'average_session_duration' => $this->calculateAverageSessionDuration($startDate),
            'period_days' => $days
        ];
        
        return $stats;
    }

    /**
     * Calcular duración promedio de sesiones
     */
    private function calculateAverageSessionDuration($startDate)
    {
        $completedSessions = Session::where('status', 'completed')
                                   ->where('created_at', '>=', $startDate)
                                   ->whereNotNull('completed_at')
                                   ->get();
        
        if ($completedSessions->isEmpty()) {
            return 0;
        }
        
        $totalMinutes = 0;
        
        foreach ($completedSessions as $session) {
            $duration = $session->created_at->diffInMinutes($session->completed_at);
            $totalMinutes += $duration;
        }
        
        return round($totalMinutes / $completedSessions->count(), 2);
    }

    /**
     * Regenerar código QR de sesión
     */
    public function regenerateQRCode(Session $session)
    {
        // Eliminar QR anterior si existe
        if ($session->qr_code_path && \Storage::disk('public')->exists($session->qr_code_path)) {
            \Storage::disk('public')->delete($session->qr_code_path);
        }
        
        // Generar nuevo QR
        $this->generateQRCode($session);
        
        Log::info('QR code regenerated', [
            'session_id' => $session->_id
        ]);
        
        return $session->fresh();
    }
}