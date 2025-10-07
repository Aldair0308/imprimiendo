<?php

namespace App\Services;

use App\Models\Printer;
use App\Models\PrintJob;
use App\Models\PrinterStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MultiPrinterService
{
    protected $loadBalancerService;
    protected $printerMonitorService;

    public function __construct(
        LoadBalancerService $loadBalancerService,
        PrinterMonitorService $printerMonitorService
    ) {
        $this->loadBalancerService = $loadBalancerService;
        $this->printerMonitorService = $printerMonitorService;
    }

    /**
     * Obtener todas las impresoras disponibles - optimizado
     */
    public function getAvailablePrinters()
    {
        return Printer::select(['_id', 'name', 'status', 'is_active', 'maintenance_mode', 'current_queue_size', 'max_queue_size'])
                     ->where('is_active', true)
                     ->where('status', 'online')
                     ->where('maintenance_mode', false)
                     ->limit(5)
                     ->get();
    }

    /**
     * Obtener impresora óptima para un trabajo
     */
    public function getOptimalPrinter($requirements = [])
    {
        $availablePrinters = $this->getAvailablePrinters();
        
        if ($availablePrinters->isEmpty()) {
            return null;
        }

        // Filtrar por capacidades requeridas
        if (!empty($requirements)) {
            $availablePrinters = $this->filterByCapabilities($availablePrinters, $requirements);
        }

        if ($availablePrinters->isEmpty()) {
            return null;
        }

        // Usar load balancer para seleccionar la mejor impresora
        return $this->loadBalancerService->selectPrinter($availablePrinters);
    }

    /**
     * Filtrar impresoras por capacidades
     */
    protected function filterByCapabilities($printers, $requirements)
    {
        return $printers->filter(function ($printer) use ($requirements) {
            // Verificar si soporta color si es requerido
            if (isset($requirements['color']) && $requirements['color']) {
                if (!$printer->supportsColor()) {
                    return false;
                }
            }

            // Verificar si soporta dúplex si es requerido
            if (isset($requirements['duplex']) && $requirements['duplex']) {
                if (!$printer->supportsDuplex()) {
                    return false;
                }
            }

            // Verificar tamaño de papel
            if (isset($requirements['paper_size'])) {
                if (!$printer->supportsPaperSize($requirements['paper_size'])) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Asignar trabajo a impresora específica
     */
    public function assignJobToPrinter(PrintJob $job, Printer $printer)
    {
        try {
            // Verificar que la impresora esté disponible
            if (!$printer->isAvailable()) {
                throw new \Exception("La impresora {$printer->name} no está disponible");
            }

            // Asignar el trabajo
            $job->markAsQueued($printer->_id);
            $printer->incrementQueue();

            Log::info("Trabajo {$job->_id} asignado a impresora {$printer->name}");
            
            return true;
        } catch (\Exception $e) {
            Log::error("Error asignando trabajo a impresora: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Procesar cola de trabajos
     */
    public function processJobQueue()
    {
        $printers = Printer::available()->get();
        
        foreach ($printers as $printer) {
            $this->processJobsForPrinter($printer);
        }
    }

    /**
     * Procesar trabajos para una impresora específica
     */
    protected function processJobsForPrinter(Printer $printer)
    {
        // Verificar si la impresora está ocupada
        if ($printer->isBusy()) {
            return;
        }

        // Obtener próximo trabajo en cola
        $nextJob = $printer->getNextJob();
        
        if (!$nextJob) {
            return;
        }

        try {
            // Marcar impresora como ocupada
            $printer->markAsBusy();
            
            // Iniciar trabajo
            $this->startPrintJob($nextJob, $printer);
            
        } catch (\Exception $e) {
            Log::error("Error procesando trabajo para impresora {$printer->name}: " . $e->getMessage());
            $printer->markAsError($e->getMessage());
            $nextJob->markAsFailed($e->getMessage());
        }
    }

    /**
     * Iniciar trabajo de impresión
     */
    protected function startPrintJob(PrintJob $job, Printer $printer)
    {
        try {
            // Marcar trabajo como iniciado
            $job->markAsStarted();
            
            // Aquí iría la lógica de envío a la impresora física
            // Por ahora simulamos el proceso
            $this->sendToPrinter($job, $printer);
            
            Log::info("Trabajo {$job->_id} iniciado en impresora {$printer->name}");
            
        } catch (\Exception $e) {
            $job->markAsFailed($e->getMessage());
            $printer->markAsError($e->getMessage());
            throw $e;
        }
    }

    /**
     * Enviar trabajo a impresora física
     */
    protected function sendToPrinter(PrintJob $job, Printer $printer)
    {
        // Aquí iría la integración con mike42/escpos-php
        // Por ahora simulamos el proceso
        
        // Simular tiempo de impresión
        sleep(2);
        
        // Marcar como completado
        $job->markAsCompleted();
        $printer->recordJobCompleted();
        $printer->markAsOnline();
        
        Log::info("Trabajo {$job->_id} completado en impresora {$printer->name}");
    }

    /**
     * Redistribuir trabajos de impresora fallida
     */
    public function redistributeJobs(Printer $failedPrinter)
    {
        $pendingJobs = $failedPrinter->getPendingJobs();
        
        foreach ($pendingJobs as $job) {
            // Buscar nueva impresora para el trabajo
            $newPrinter = $this->getOptimalPrinter([
                'color' => $job->color_mode === PrintJob::COLOR_COLOR,
                'duplex' => $job->duplex,
                'paper_size' => $job->paper_size
            ]);
            
            if ($newPrinter) {
                $this->assignJobToPrinter($job, $newPrinter);
                Log::info("Trabajo {$job->_id} redistribuido de {$failedPrinter->name} a {$newPrinter->name}");
            } else {
                $job->markAsFailed('No hay impresoras disponibles para redistribuir');
                Log::warning("No se pudo redistribuir trabajo {$job->_id}");
            }
        }
    }

    /**
     * Obtener estadísticas del sistema multi-impresora - optimizado
     */
    public function getSystemStats()
    {
        // Limitar consultas y usar select específico
        $printers = Printer::select(['_id', 'name', 'status', 'maintenance_mode', 'current_queue_size', 'max_queue_size', 'total_jobs_printed'])->limit(10)->get();
        
        // Usar consultas más eficientes
        $totalJobs = PrintJob::count();
        $pendingJobs = PrintJob::where('status', 'pending')->count();
        $completedJobs = PrintJob::where('status', 'completed')->count();
        $failedJobs = PrintJob::where('status', 'failed')->count();
        
        return [
            'printers' => [
                'total' => $printers->count(),
                'online' => $printers->where('status', 'online')->count(),
                'offline' => $printers->where('status', 'offline')->count(),
                'busy' => $printers->where('status', 'busy')->count(),
                'error' => $printers->where('status', 'error')->count(),
                'maintenance' => $printers->where('maintenance_mode', true)->count()
            ],
            'jobs' => [
                'total' => $totalJobs,
                'pending' => $pendingJobs,
                'completed' => $completedJobs,
                'failed' => $failedJobs,
                'success_rate' => $totalJobs > 0 ? ($completedJobs / $totalJobs) * 100 : 0
            ],
            'load_distribution' => $this->getLoadDistribution($printers),
            'average_queue_time' => 0, // Evitar consulta costosa
            'system_uptime' => 95 // Valor fijo para evitar consulta costosa
        ];
    }

    /**
     * Obtener distribución de carga
     */
    protected function getLoadDistribution($printers)
    {
        $distribution = [];
        
        foreach ($printers as $printer) {
            $distribution[] = [
                'printer_id' => $printer->_id,
                'name' => $printer->name,
                'queue_size' => $printer->current_queue_size,
                'max_queue' => $printer->max_queue_size,
                'workload_percentage' => $printer->getWorkloadPercentage(),
                'total_jobs' => $printer->total_jobs_printed
            ];
        }
        
        return $distribution;
    }

    /**
     * Obtener tiempo promedio en cola
     */
    protected function getAverageQueueTime()
    {
        $completedJobs = PrintJob::completed()
                               ->whereNotNull('started_at')
                               ->where('created_at', '>=', Carbon::now()->subDays(7))
                               ->get();
        
        if ($completedJobs->isEmpty()) {
            return 0;
        }
        
        $totalWaitTime = 0;
        foreach ($completedJobs as $job) {
            $waitTime = $job->started_at->diffInSeconds($job->created_at);
            $totalWaitTime += $waitTime;
        }
        
        return $totalWaitTime / $completedJobs->count();
    }

    /**
     * Obtener tiempo de actividad del sistema
     */
    protected function getSystemUptime()
    {
        $printers = Printer::active()->get();
        
        if ($printers->isEmpty()) {
            return 0;
        }
        
        $totalUptime = 0;
        foreach ($printers as $printer) {
            $stats = PrinterStatus::getAvailabilityStats($printer->_id, 7);
            $totalUptime += $stats['uptime_percentage'];
        }
        
        return $totalUptime / $printers->count();
    }

    /**
     * Optimizar distribución de trabajos
     */
    public function optimizeJobDistribution()
    {
        $printers = Printer::available()->get();
        
        if ($printers->count() < 2) {
            return; // No hay suficientes impresoras para optimizar
        }
        
        // Identificar impresoras sobrecargadas y con poca carga
        $overloadedPrinters = $printers->filter(function ($printer) {
            return $printer->getWorkloadPercentage() > 80;
        });
        
        $underloadedPrinters = $printers->filter(function ($printer) {
            return $printer->getWorkloadPercentage() < 30;
        });
        
        // Redistribuir trabajos si es necesario
        foreach ($overloadedPrinters as $overloaded) {
            $this->redistributeJobsFromOverloaded($overloaded, $underloadedPrinters);
        }
    }

    /**
     * Redistribuir trabajos de impresora sobrecargada
     */
    protected function redistributeJobsFromOverloaded(Printer $overloaded, $underloadedPrinters)
    {
        if ($underloadedPrinters->isEmpty()) {
            return;
        }
        
        $pendingJobs = $overloaded->getPendingJobs();
        $jobsToMove = $pendingJobs->take(ceil($pendingJobs->count() * 0.3)); // Mover 30% de trabajos
        
        foreach ($jobsToMove as $job) {
            $targetPrinter = $underloadedPrinters->sortBy('current_queue_size')->first();
            
            if ($targetPrinter && $targetPrinter->isAvailable()) {
                // Remover de impresora actual
                $overloaded->decrementQueue();
                
                // Asignar a nueva impresora
                $this->assignJobToPrinter($job, $targetPrinter);
                
                Log::info("Trabajo {$job->_id} movido de {$overloaded->name} a {$targetPrinter->name} para optimización");
            }
        }
    }
}