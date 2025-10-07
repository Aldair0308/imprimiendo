<?php

namespace App\Http\Controllers;

use App\Models\Printer;
use App\Models\PrintJob;
use App\Services\PrinterMonitorService;
use App\Services\MultiPrinterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PrinterController extends Controller
{
    private $printerMonitorService;
    private $multiPrinterService;
    
    public function __construct(
        PrinterMonitorService $printerMonitorService,
        MultiPrinterService $multiPrinterService
    ) {
        $this->printerMonitorService = $printerMonitorService;
        $this->multiPrinterService = $multiPrinterService;
    }

    /**
     * Obtener lista de impresoras disponibles - optimizado
     */
    public function getAvailablePrinters()
    {
        try {
            // Limitar consultas y evitar servicios costosos
            $printers = Printer::select(['_id', 'name', 'model', 'location', 'status', 'supports_color', 'supports_duplex', 'max_paper_size', 'priority'])
                              ->where('is_active', true)
                              ->limit(5)
                              ->get();
            
            $printersData = [];
            foreach ($printers as $printer) {
                // Evitar llamadas costosas al servicio de monitoreo
                $queueCount = PrintJob::where('printer_id', $printer->_id)
                                    ->whereIn('status', ['pending', 'processing'])
                                    ->count();
                
                $printersData[] = [
                    'id' => $printer->_id,
                    'name' => $printer->name,
                    'model' => $printer->model,
                    'location' => $printer->location,
                    'status' => $printer->status, // Usar status directo del modelo
                    'supports_color' => $printer->supports_color,
                    'supports_duplex' => $printer->supports_duplex,
                    'max_paper_size' => $printer->max_paper_size,
                    'queue_count' => $queueCount,
                    'estimated_wait_time' => $queueCount * 2, // Estimación simple: 2 min por trabajo
                    'priority' => $printer->priority,
                    'is_recommended' => $queueCount < 3 && $printer->status === 'online'
                ];
            }
            
            // Ordenar por recomendación y prioridad
            usort($printersData, function($a, $b) {
                if ($a['is_recommended'] !== $b['is_recommended']) {
                    return $b['is_recommended'] - $a['is_recommended'];
                }
                if ($a['queue_count'] !== $b['queue_count']) {
                    return $a['queue_count'] - $b['queue_count'];
                }
                return $a['priority'] - $b['priority'];
            });
            
            return response()->json([
                'success' => true,
                'printers' => $printersData
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting available printers: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener impresoras disponibles'
            ], 400);
        }
    }

    /**
     * Obtener estado detallado de una impresora
     */
    public function getPrinterStatus($printerId)
    {
        try {
            $printer = Printer::findOrFail($printerId);
            $status = $this->printerMonitorService->checkPrinterStatus($printer);
            
            // Trabajos en cola
            $queueJobs = PrintJob::where('printer_id', $printerId)
                               ->whereIn('status', ['pending', 'processing'])
                               ->orderBy('created_at', 'asc')
                               ->get();
            
            // Estadísticas de la impresora
            $stats = [
                'jobs_today' => PrintJob::where('printer_id', $printerId)
                                      ->whereDate('created_at', today())
                                      ->count(),
                'jobs_completed_today' => PrintJob::where('printer_id', $printerId)
                                                ->whereDate('created_at', today())
                                                ->where('status', 'completed')
                                                ->count(),
                'jobs_failed_today' => PrintJob::where('printer_id', $printerId)
                                             ->whereDate('created_at', today())
                                             ->where('status', 'failed')
                                             ->count(),
                'avg_completion_time' => PrintJob::where('printer_id', $printerId)
                                               ->where('status', 'completed')
                                               ->whereNotNull('completed_at')
                                               ->get()
                                               ->avg(function($job) {
                                                   return $job->completed_at->diffInMinutes($job->created_at);
                                               })
            ];
            
            return response()->json([
                'success' => true,
                'printer' => [
                    'id' => $printer->_id,
                    'name' => $printer->name,
                    'model' => $printer->model,
                    'location' => $printer->location,
                    'ip_address' => $printer->ip_address,
                    'port' => $printer->port,
                    'status' => $status['status'],
                    'status_details' => $status,
                    'supports_color' => $printer->supports_color,
                    'supports_duplex' => $printer->supports_duplex,
                    'max_paper_size' => $printer->max_paper_size,
                    'priority' => $printer->priority,
                    'is_active' => $printer->is_active,
                    'created_at' => $printer->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $printer->updated_at->format('Y-m-d H:i:s')
                ],
                'queue' => $queueJobs->map(function($job) {
                    return [
                        'id' => $job->_id,
                        'file_name' => $job->file_name,
                        'status' => $job->status,
                        'progress' => $job->progress ?? 0,
                        'created_at' => $job->created_at->format('Y-m-d H:i:s'),
                        'estimated_completion' => $job->estimated_completion_time
                    ];
                }),
                'statistics' => $stats
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estado de la impresora'
            ], 400);
        }
    }

    /**
     * Probar conectividad con una impresora
     */
    public function testConnection($printerId)
    {
        try {
            $printer = Printer::findOrFail($printerId);
            $connectionTest = $this->printerMonitorService->testPrinterConnection($printer);
            
            return response()->json([
                'success' => $connectionTest['connected'],
                'message' => $connectionTest['message'],
                'details' => $connectionTest
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al probar conexión'
            ], 400);
        }
    }

    /**
     * Pausar/reanudar impresora
     */
    public function togglePrinter($printerId)
    {
        try {
            $printer = Printer::findOrFail($printerId);
            
            $printer->is_active = !$printer->is_active;
            $printer->save();
            
            $action = $printer->is_active ? 'activada' : 'pausada';
            
            return response()->json([
                'success' => true,
                'message' => "Impresora {$action} correctamente",
                'is_active' => $printer->is_active
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado de la impresora'
            ], 400);
        }
    }

    /**
     * Limpiar cola de trabajos de una impresora
     */
    public function clearQueue($printerId)
    {
        try {
            $printer = Printer::findOrFail($printerId);
            
            // Cancelar trabajos pendientes
            $pendingJobs = PrintJob::where('printer_id', $printerId)
                                 ->whereIn('status', ['pending'])
                                 ->get();
            
            $cancelledCount = 0;
            foreach ($pendingJobs as $job) {
                if ($this->multiPrinterService->cancelPrintJob($job)) {
                    $cancelledCount++;
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Se cancelaron {$cancelledCount} trabajos de la cola",
                'cancelled_jobs' => $cancelledCount
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar cola de trabajos'
            ], 400);
        }
    }

    /**
     * Obtener trabajos de una impresora específica
     */
    public function getPrinterJobs($printerId, Request $request)
    {
        try {
            $printer = Printer::findOrFail($printerId);
            
            $query = PrintJob::where('printer_id', $printerId);
            
            // Filtros
            if ($request->status) {
                $query->where('status', $request->status);
            }
            
            if ($request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            $jobs = $query->orderBy('created_at', 'desc')
                         ->limit($request->limit ?? 50)
                         ->get();
            
            return response()->json([
                'success' => true,
                'printer_name' => $printer->name,
                'jobs' => $jobs->map(function($job) {
                    return [
                        'id' => $job->_id,
                        'file_name' => $job->file_name,
                        'status' => $job->status,
                        'progress' => $job->progress ?? 0,
                        'pages' => $job->pages,
                        'copies' => $job->copies,
                        'color_mode' => $job->color_mode,
                        'created_at' => $job->created_at->format('Y-m-d H:i:s'),
                        'completed_at' => $job->completed_at ? $job->completed_at->format('Y-m-d H:i:s') : null,
                        'session_code' => $job->session->session_code ?? null
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener trabajos de la impresora'
            ], 400);
        }
    }

    /**
     * Reiniciar impresora (reiniciar estado y limpiar errores)
     */
    public function restartPrinter($printerId)
    {
        try {
            $printer = Printer::findOrFail($printerId);
            
            // Reiniciar estado
            $printer->status = 'offline';
            $printer->last_error = null;
            $printer->save();
            
            // Verificar estado actual
            $status = $this->printerMonitorService->checkPrinterStatus($printer);
            
            return response()->json([
                'success' => true,
                'message' => 'Impresora reiniciada correctamente',
                'new_status' => $status['status']
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reiniciar impresora'
            ], 400);
        }
    }

    /**
     * Obtener estadísticas de rendimiento de impresoras
     */
    public function getPerformanceStats(Request $request)
    {
        try {
            $dateFrom = $request->date_from ?? now()->subDays(7)->format('Y-m-d');
            $dateTo = $request->date_to ?? now()->format('Y-m-d');
            
            $printers = Printer::all();
            $performanceStats = [];
            
            foreach ($printers as $printer) {
                $jobs = PrintJob::where('printer_id', $printer->_id)
                              ->whereBetween('created_at', [$dateFrom, $dateTo])
                              ->get();
                
                $completedJobs = $jobs->where('status', 'completed');
                $failedJobs = $jobs->where('status', 'failed');
                
                $avgCompletionTime = $completedJobs->avg(function($job) {
                    return $job->completed_at ? $job->completed_at->diffInMinutes($job->created_at) : 0;
                });
                
                $performanceStats[] = [
                    'printer_id' => $printer->_id,
                    'printer_name' => $printer->name,
                    'total_jobs' => $jobs->count(),
                    'completed_jobs' => $completedJobs->count(),
                    'failed_jobs' => $failedJobs->count(),
                    'success_rate' => $jobs->count() > 0 ? ($completedJobs->count() / $jobs->count()) * 100 : 0,
                    'avg_completion_time_minutes' => round($avgCompletionTime, 2),
                    'total_pages_printed' => $completedJobs->sum('pages'),
                    'uptime_percentage' => $this->calculateUptimePercentage($printer, $dateFrom, $dateTo)
                ];
            }
            
            return response()->json([
                'success' => true,
                'date_range' => ['from' => $dateFrom, 'to' => $dateTo],
                'performance_stats' => $performanceStats
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas de rendimiento'
            ], 400);
        }
    }

    /**
     * Calcular porcentaje de tiempo activo de una impresora
     */
    private function calculateUptimePercentage($printer, $dateFrom, $dateTo)
    {
        // Implementación simplificada
        // En un sistema real, esto requeriría un log de estados de impresora
        $totalHours = now()->parse($dateTo)->diffInHours(now()->parse($dateFrom));
        
        // Estimación basada en trabajos completados vs fallidos
        $completedJobs = PrintJob::where('printer_id', $printer->_id)
                               ->whereBetween('created_at', [$dateFrom, $dateTo])
                               ->where('status', 'completed')
                               ->count();
        
        $totalJobs = PrintJob::where('printer_id', $printer->_id)
                           ->whereBetween('created_at', [$dateFrom, $dateTo])
                           ->count();
        
        if ($totalJobs === 0) {
            return $printer->status === 'online' ? 100 : 0;
        }
        
        return round(($completedJobs / $totalJobs) * 100, 2);
    }

    /**
     * Obtener configuración de una impresora
     */
    public function getPrinterConfig($printerId)
    {
        try {
            $printer = Printer::findOrFail($printerId);
            
            return response()->json([
                'success' => true,
                'config' => [
                    'id' => $printer->_id,
                    'name' => $printer->name,
                    'model' => $printer->model,
                    'ip_address' => $printer->ip_address,
                    'port' => $printer->port,
                    'location' => $printer->location,
                    'supports_color' => $printer->supports_color,
                    'supports_duplex' => $printer->supports_duplex,
                    'max_paper_size' => $printer->max_paper_size,
                    'priority' => $printer->priority,
                    'is_active' => $printer->is_active,
                    'max_queue_size' => $printer->max_queue_size ?? 10,
                    'maintenance_mode' => $printer->maintenance_mode ?? false
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener configuración de la impresora'
            ], 400);
        }
    }
}