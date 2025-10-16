<?php

namespace App\Services;

use App\Models\Printer;
use App\Models\PrintJob;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LoadBalancerService
{
    private $multiPrinterService;
    private $printerMonitorService;
    
    public function __construct(
        MultiPrinterService $multiPrinterService = null,
        PrinterMonitorService $printerMonitorService = null
    ) {
        $this->multiPrinterService = $multiPrinterService;
        $this->printerMonitorService = $printerMonitorService;
    }

    /**
     * Distribuir trabajo de impresión usando algoritmo inteligente
     */
    public function distributeJob(PrintJob $job)
    {
        $availablePrinters = $this->getAvailablePrinters($job);
        
        if ($availablePrinters->isEmpty()) {
            throw new \Exception('No hay impresoras disponibles para procesar el trabajo');
        }

        $selectedPrinter = $this->selectOptimalPrinter($availablePrinters, $job);
        
        return $this->assignJobToPrinter($job, $selectedPrinter);
    }

    /**
     * Obtener impresoras disponibles para un trabajo específico
     */
    private function getAvailablePrinters(PrintJob $job)
    {
        return Printer::with(['status', 'printJobs'])
                     ->where('is_active', true)
                     ->where('is_available', true)
                     ->where(function($query) use ($job) {
                         // Filtrar por tipo de impresión si es necesario
                         if ($job->print_type === 'color') {
                             $query->where('supports_color', true);
                         }
                         
                         // Filtrar por tamaño de papel
                         $query->whereJsonContains('supported_paper_sizes', $job->paper_size);
                     })
                     ->whereHas('status', function($query) {
                         $query->where('is_online', true)
                               ->where('paper_level', '>', 10)
                               ->where('ink_level', '>', 15);
                     })
                     ->get();
    }

    /**
     * Seleccionar la impresora óptima usando algoritmo de balanceo
     */
    private function selectOptimalPrinter($printers, PrintJob $job)
    {
        $scoredPrinters = $printers->map(function($printer) use ($job) {
            return [
                'printer' => $printer,
                'score' => $this->calculatePrinterScore($printer, $job)
            ];
        });

        // Ordenar por puntuación (mayor es mejor)
        $bestPrinter = $scoredPrinters->sortByDesc('score')->first();
        
        Log::info('Printer selected by load balancer', [
            'job_id' => $job->_id,
            'printer_id' => $bestPrinter['printer']->_id,
            'printer_name' => $bestPrinter['printer']->name,
            'score' => $bestPrinter['score']
        ]);

        return $bestPrinter['printer'];
    }

    /**
     * Calcular puntuación de impresora para un trabajo específico
     */
    private function calculatePrinterScore(Printer $printer, PrintJob $job)
    {
        $score = 0;
        
        // Factor 1: Carga actual de trabajo (peso: 30%)
        $currentJobs = $printer->printJobs()
                              ->whereIn('status', ['pending', 'processing'])
                              ->count();
        $loadScore = max(0, 100 - ($currentJobs * 20)); // Penalizar por cada trabajo pendiente
        $score += $loadScore * 0.3;

        // Factor 2: Estado de recursos (peso: 25%)
        $status = $printer->status;
        $resourceScore = 0;
        if ($status) {
            $resourceScore = ($status->paper_level + $status->ink_level) / 2;
        }
        $score += $resourceScore * 0.25;

        // Factor 3: Velocidad de impresión (peso: 20%)
        $speedScore = $printer->print_speed ?? 50; // páginas por minuto
        $score += min($speedScore, 100) * 0.2;

        // Factor 4: Historial de confiabilidad (peso: 15%)
        $reliabilityScore = $this->calculateReliabilityScore($printer);
        $score += $reliabilityScore * 0.15;

        // Factor 5: Proximidad/Prioridad (peso: 10%)
        $priorityScore = $printer->priority_level ?? 50;
        $score += $priorityScore * 0.1;

        // Bonus por compatibilidad perfecta
        if ($this->isPerfectMatch($printer, $job)) {
            $score += 10;
        }

        return round($score, 2);
    }

    /**
     * Calcular puntuación de confiabilidad basada en historial
     */
    private function calculateReliabilityScore(Printer $printer)
    {
        $recentJobs = PrintJob::where('printer_id', $printer->_id)
                             ->where('created_at', '>=', Carbon::now()->subDays(7))
                             ->get();

        if ($recentJobs->isEmpty()) {
            return 75; // Puntuación neutral para impresoras sin historial reciente
        }

        $successfulJobs = $recentJobs->where('status', 'completed')->count();
        $totalJobs = $recentJobs->count();
        
        return ($successfulJobs / $totalJobs) * 100;
    }

    /**
     * Verificar si la impresora es una coincidencia perfecta para el trabajo
     */
    private function isPerfectMatch(Printer $printer, PrintJob $job)
    {
        // Verificar tipo de impresión
        if ($job->print_type === 'color' && !$printer->supports_color) {
            return false;
        }

        // Verificar tamaño de papel preferido
        $supportedSizes = $printer->supported_paper_sizes ?? [];
        if (!in_array($job->paper_size, $supportedSizes)) {
            return false;
        }

        // Verificar calidad de impresión
        if ($job->print_quality === 'high' && $printer->max_quality < 600) {
            return false;
        }

        return true;
    }

    /**
     * Asignar trabajo a impresora seleccionada
     */
    private function assignJobToPrinter(PrintJob $job, Printer $printer)
    {
        try {
            // Actualizar el trabajo con la impresora asignada
            $job->update([
                'printer_id' => $printer->_id,
                'assigned_at' => Carbon::now(),
                'status' => 'assigned'
            ]);

            // Actualizar estadísticas de la impresora
            $printer->increment('total_jobs_assigned');
            
            Log::info('Job assigned to printer', [
                'job_id' => $job->_id,
                'printer_id' => $printer->_id,
                'printer_name' => $printer->name
            ]);

            return [
                'success' => true,
                'printer' => $printer,
                'estimated_completion' => $this->calculateEstimatedCompletion($printer, $job)
            ];

        } catch (\Exception $e) {
            Log::error('Error assigning job to printer: ' . $e->getMessage(), [
                'job_id' => $job->_id,
                'printer_id' => $printer->_id
            ]);

            throw new \Exception('Error al asignar trabajo a la impresora');
        }
    }

    /**
     * Calcular tiempo estimado de finalización
     */
    private function calculateEstimatedCompletion(Printer $printer, PrintJob $job)
    {
        // Obtener trabajos pendientes en la impresora
        $pendingJobs = PrintJob::where('printer_id', $printer->_id)
                              ->whereIn('status', ['pending', 'processing', 'assigned'])
                              ->where('_id', '!=', $job->_id)
                              ->orderBy('created_at')
                              ->get();

        // Calcular tiempo total estimado
        $totalMinutes = 0;
        
        foreach ($pendingJobs as $pendingJob) {
            $totalMinutes += $this->estimateJobDuration($pendingJob, $printer);
        }
        
        // Agregar tiempo del trabajo actual
        $totalMinutes += $this->estimateJobDuration($job, $printer);

        return Carbon::now()->addMinutes($totalMinutes);
    }

    /**
     * Estimar duración de un trabajo de impresión
     */
    private function estimateJobDuration(PrintJob $job, Printer $printer)
    {
        $baseTimePerPage = 60 / ($printer->print_speed ?? 10); // segundos por página
        
        // Ajustar por tipo de impresión
        if ($job->print_type === 'color') {
            $baseTimePerPage *= 1.5;
        }
        
        // Ajustar por calidad
        switch ($job->print_quality) {
            case 'high':
                $baseTimePerPage *= 1.3;
                break;
            case 'medium':
                $baseTimePerPage *= 1.1;
                break;
        }

        $totalSeconds = $baseTimePerPage * ($job->total_pages ?? 1);
        
        return ceil($totalSeconds / 60); // convertir a minutos
    }

    /**
     * Rebalancear trabajos en caso de falla de impresora
     */
    public function rebalanceJobs(Printer $failedPrinter)
    {
        $affectedJobs = PrintJob::where('printer_id', $failedPrinter->_id)
                               ->whereIn('status', ['pending', 'assigned'])
                               ->get();

        $rebalancedCount = 0;
        
        foreach ($affectedJobs as $job) {
            try {
                // Resetear asignación
                $job->update([
                    'printer_id' => null,
                    'assigned_at' => null,
                    'status' => 'pending'
                ]);

                // Redistribuir
                $this->distributeJob($job);
                $rebalancedCount++;
                
            } catch (\Exception $e) {
                Log::error('Error rebalancing job: ' . $e->getMessage(), [
                    'job_id' => $job->_id
                ]);
            }
        }

        Log::info('Jobs rebalanced after printer failure', [
            'failed_printer_id' => $failedPrinter->_id,
            'rebalanced_jobs' => $rebalancedCount
        ]);

        return $rebalancedCount;
    }

    /**
     * Obtener estadísticas de balanceo de carga
     */
    public function getLoadBalancingStats()
    {
        $printers = Printer::with(['printJobs', 'status'])
                          ->where('is_active', true)
                          ->get();

        $stats = [];
        
        foreach ($printers as $printer) {
            $pendingJobs = $printer->printJobs()
                                  ->whereIn('status', ['pending', 'processing', 'assigned'])
                                  ->count();
            
            $completedToday = $printer->printJobs()
                                    ->where('status', 'completed')
                                    ->where('completed_at', '>=', Carbon::today())
                                    ->count();

            $stats[] = [
                'printer_id' => $printer->_id,
                'printer_name' => $printer->name,
                'pending_jobs' => $pendingJobs,
                'completed_today' => $completedToday,
                'load_percentage' => min(($pendingJobs / 10) * 100, 100), // Máximo 10 trabajos = 100%
                'is_available' => $printer->is_available,
                'status' => $printer->status ? 'online' : 'offline'
            ];
        }

        return [
            'printers' => $stats,
            'total_pending_jobs' => collect($stats)->sum('pending_jobs'),
            'average_load' => collect($stats)->avg('load_percentage'),
            'last_update' => Carbon::now()
        ];
    }
}