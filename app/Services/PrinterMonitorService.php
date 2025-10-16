<?php

namespace App\Services;

use App\Models\Printer;
use App\Models\PrinterStatus;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PrinterMonitorService
{
    private $multiPrinterService;
    
    public function __construct(MultiPrinterService $multiPrinterService = null)
    {
        $this->multiPrinterService = $multiPrinterService;
    }

    /**
     * Monitorear el estado de todas las impresoras
     */
    public function monitorAllPrinters()
    {
        $printers = Printer::where('is_active', true)->get();
        
        foreach ($printers as $printer) {
            $this->checkPrinterStatus($printer);
        }
        
        return $this->getSystemStatus();
    }

    /**
     * Verificar el estado de una impresora específica
     */
    public function checkPrinterStatus(Printer $printer)
    {
        try {
            // Simular verificación de estado de impresora
            $isOnline = $this->pingPrinter($printer->ip_address);
            $paperLevel = $this->checkPaperLevel($printer);
            $inkLevel = $this->checkInkLevel($printer);
            $temperature = $this->checkTemperature($printer);
            
            // Actualizar estado en la base de datos
            $status = PrinterStatus::updateOrCreate(
                ['printer_id' => $printer->_id],
                [
                    'is_online' => $isOnline,
                    'paper_level' => $paperLevel,
                    'ink_level' => $inkLevel,
                    'temperature' => $temperature,
                    'last_check' => Carbon::now(),
                    'error_count' => $isOnline ? 0 : ($printer->status->error_count ?? 0) + 1,
                    'status_message' => $this->generateStatusMessage($isOnline, $paperLevel, $inkLevel)
                ]
            );

            // Actualizar disponibilidad de la impresora
            $printer->update([
                'is_available' => $isOnline && $paperLevel > 10 && $inkLevel > 15,
                'last_maintenance' => $isOnline ? Carbon::now() : $printer->last_maintenance
            ]);

            Log::info("Printer {$printer->name} status updated", [
                'printer_id' => $printer->_id,
                'is_online' => $isOnline,
                'is_available' => $printer->is_available
            ]);

        } catch (\Exception $e) {
            Log::error("Error checking printer status: " . $e->getMessage(), [
                'printer_id' => $printer->_id
            ]);
        }
    }

    /**
     * Verificar conectividad con la impresora
     */
    private function pingPrinter($ipAddress)
    {
        // En un entorno real, esto haría ping real a la IP
        // Por ahora simulamos basado en la IP
        $lastOctet = (int) substr($ipAddress, strrpos($ipAddress, '.') + 1);
        return $lastOctet % 10 !== 0; // 90% de disponibilidad simulada
    }

    /**
     * Verificar nivel de papel
     */
    private function checkPaperLevel(Printer $printer)
    {
        // Simulación del nivel de papel (0-100%)
        return rand(5, 95);
    }

    /**
     * Verificar nivel de tinta
     */
    private function checkInkLevel(Printer $printer)
    {
        // Simulación del nivel de tinta (0-100%)
        return rand(10, 90);
    }

    /**
     * Verificar temperatura de la impresora
     */
    private function checkTemperature(Printer $printer)
    {
        // Simulación de temperatura (20-60°C)
        return rand(20, 60);
    }

    /**
     * Generar mensaje de estado
     */
    private function generateStatusMessage($isOnline, $paperLevel, $inkLevel)
    {
        if (!$isOnline) {
            return 'Impresora desconectada';
        }
        
        if ($paperLevel <= 10) {
            return 'Papel bajo - Requiere recarga';
        }
        
        if ($inkLevel <= 15) {
            return 'Tinta baja - Requiere recarga';
        }
        
        return 'Funcionando correctamente';
    }

    /**
     * Obtener estado general del sistema
     */
    public function getSystemStatus()
    {
        $totalPrinters = Printer::where('is_active', true)->count();
        $onlinePrinters = Printer::where('is_active', true)
                                ->where('is_available', true)
                                ->count();
        
        $offlinePrinters = $totalPrinters - $onlinePrinters;
        $systemHealth = $totalPrinters > 0 ? ($onlinePrinters / $totalPrinters) * 100 : 0;
        
        return [
            'total_printers' => $totalPrinters,
            'online_printers' => $onlinePrinters,
            'offline_printers' => $offlinePrinters,
            'system_health' => round($systemHealth, 2),
            'status' => $systemHealth >= 70 ? 'healthy' : ($systemHealth >= 40 ? 'warning' : 'critical'),
            'last_update' => Carbon::now()
        ];
    }

    /**
     * Obtener impresoras con problemas
     */
    public function getProblematicPrinters()
    {
        return Printer::with('status')
                     ->where('is_active', true)
                     ->where(function($query) {
                         $query->where('is_available', false)
                               ->orWhereHas('status', function($q) {
                                   $q->where('error_count', '>', 3)
                                     ->orWhere('paper_level', '<=', 10)
                                     ->orWhere('ink_level', '<=', 15);
                               });
                     })
                     ->get();
    }

    /**
     * Reiniciar impresora remotamente
     */
    public function restartPrinter(Printer $printer)
    {
        try {
            // En un entorno real, esto enviaría comando de reinicio
            Log::info("Restarting printer {$printer->name}");
            
            // Simular reinicio
            sleep(2);
            
            // Verificar estado después del reinicio
            $this->checkPrinterStatus($printer);
            
            return [
                'success' => true,
                'message' => 'Impresora reiniciada correctamente'
            ];
            
        } catch (\Exception $e) {
            Log::error("Error restarting printer: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al reiniciar la impresora'
            ];
        }
    }

    /**
     * Programar mantenimiento automático
     */
    public function scheduleMaintenanceCheck()
    {
        $printers = Printer::where('is_active', true)
                          ->where('last_maintenance', '<', Carbon::now()->subDays(7))
                          ->get();
        
        foreach ($printers as $printer) {
            // Programar mantenimiento
            Log::info("Maintenance scheduled for printer {$printer->name}");
            
            // En un entorno real, esto crearía una tarea programada
            $printer->update([
                'maintenance_required' => true,
                'maintenance_scheduled' => Carbon::now()->addHours(2)
            ]);
        }
        
        return $printers->count();
    }

    /**
     * Obtener estadísticas de rendimiento
     */
    public function getPerformanceStats($printerId = null, $days = 7)
    {
        $query = PrinterStatus::where('created_at', '>=', Carbon::now()->subDays($days));
        
        if ($printerId) {
            $query->where('printer_id', $printerId);
        }
        
        $stats = $query->get();
        
        return [
            'uptime_percentage' => $stats->where('is_online', true)->count() / max($stats->count(), 1) * 100,
            'average_paper_level' => $stats->avg('paper_level'),
            'average_ink_level' => $stats->avg('ink_level'),
            'average_temperature' => $stats->avg('temperature'),
            'total_errors' => $stats->sum('error_count'),
            'period_days' => $days
        ];
    }
}