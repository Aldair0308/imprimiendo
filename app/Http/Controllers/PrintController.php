<?php

namespace App\Http\Controllers;

use App\Services\MultiPrinterService;
use App\Services\LoadBalancerService;
use App\Services\FileService;
use App\Services\SessionService;
use App\Models\PrintJob;
use App\Models\Printer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PrintController extends Controller
{
    private $multiPrinterService;
    private $loadBalancerService;
    private $fileService;
    private $sessionService;
    
    public function __construct(
        MultiPrinterService $multiPrinterService,
        LoadBalancerService $loadBalancerService,
        FileService $fileService,
        SessionService $sessionService
    ) {
        $this->multiPrinterService = $multiPrinterService;
        $this->loadBalancerService = $loadBalancerService;
        $this->fileService = $fileService;
        $this->sessionService = $sessionService;
    }

    /**
     * Mostrar página de configuración de impresión
     */
    public function configure($sessionCode)
    {
        try {
            $session = $this->sessionService->getSessionByCode($sessionCode);
            $files = $session->files;
            $availablePrinters = $this->multiPrinterService->getAvailablePrinters();
            
            // Calcular costos estimados
            $totalCost = 0;
            $filesCosts = [];
            
            foreach ($files as $file) {
                $fileInfo = $this->fileService->getFileForPrint($file);
                $filesCosts[] = [
                    'file' => $file,
                    'cost' => $fileInfo['estimated_cost']
                ];
                $totalCost += $fileInfo['estimated_cost'];
            }
            
            return view('print.configure', compact(
                'session', 
                'files', 
                'availablePrinters', 
                'filesCosts', 
                'totalCost'
            ));
            
        } catch (\Exception $e) {
            return redirect()->route('home')->with('error', 'Sesión no válida');
        }
    }

    /**
     * Configurar opciones de impresión para archivos
     */
    public function setPrintOptions(Request $request, $sessionCode)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*.file_id' => 'required|string',
            'files.*.copies' => 'required|integer|min:1|max:10',
            'files.*.color' => 'required|in:color,bw',
            'files.*.paper_size' => 'required|in:A4,Letter',
            'files.*.orientation' => 'required|in:portrait,landscape',
            'files.*.quality' => 'required|in:draft,normal,high',
            'files.*.duplex' => 'boolean',
            'printer_id' => 'nullable|string'
        ]);
        
        try {
            $session = $this->sessionService->getSessionByCode($sessionCode);
            $totalCost = 0;
            
            foreach ($request->files as $fileConfig) {
                $file = \App\Models\File::where('id', $fileConfig['file_id'])
                ->where('session_id', $session->id)
                                       ->firstOrFail();
                
                // Configurar opciones de impresión
                $printSettings = [
                    'copies' => $fileConfig['copies'],
                    'color' => $fileConfig['color'],
                    'paper_size' => $fileConfig['paper_size'],
                    'orientation' => $fileConfig['orientation'],
                    'quality' => $fileConfig['quality'],
                    'duplex' => $fileConfig['duplex'] ?? false
                ];
                
                $file->print_settings = $printSettings;
                $file->save();
                
                // Calcular costo actualizado
                $fileInfo = $this->fileService->getFileForPrint($file);
                $totalCost += $fileInfo['estimated_cost'];
            }
            
            // Actualizar sesión con costo total
            $session->total_cost = $totalCost;
            $session->printer_preference = $request->printer_id;
            $session->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Configuración guardada correctamente',
                'total_cost' => $totalCost,
                'redirect_url' => route('print.confirm', $sessionCode)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error setting print options: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al configurar opciones de impresión'
            ], 400);
        }
    }

    /**
     * Mostrar página de confirmación y pago
     */
    public function confirm($sessionCode)
    {
        try {
            $session = $this->sessionService->getSessionByCode($sessionCode);
            $files = $session->files;
            
            // Verificar que todos los archivos tengan configuración
            foreach ($files as $file) {
                if (!$file->print_settings) {
                    return redirect()->route('print.configure', $sessionCode)
                                   ->with('error', 'Debe configurar todos los archivos antes de continuar');
                }
            }
            
            $selectedPrinter = null;
            if ($session->printer_preference) {
                $selectedPrinter = Printer::find($session->printer_preference);
            }
            
            return view('print.confirm', compact('session', 'files', 'selectedPrinter'));
            
        } catch (\Exception $e) {
            return redirect()->route('home')->with('error', 'Sesión no válida');
        }
    }

    /**
     * Procesar pago y enviar trabajos de impresión
     */
    public function processPrint(Request $request, $sessionCode)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,card',
            'payment_amount' => 'required|numeric|min:0'
        ]);
        
        try {
            $session = $this->sessionService->getSessionByCode($sessionCode);
            
            // Verificar pago
            if ($request->payment_amount < $session->total_cost) {
                return response()->json([
                    'success' => false,
                    'message' => 'Monto de pago insuficiente'
                ], 400);
            }
            
            // Seleccionar impresora óptima si no se especificó
            $printerId = $session->printer_preference;
            if (!$printerId) {
                $optimalPrinter = $this->loadBalancerService->selectOptimalPrinter();
                if (!$optimalPrinter) {
                    throw new \Exception('No hay impresoras disponibles');
                }
                $printerId = $optimalPrinter->_id;
            }
            
            // Crear trabajos de impresión
            $printJobs = [];
            foreach ($session->files as $file) {
                $printJob = $this->multiPrinterService->createPrintJob($file, $printerId);
                $printJobs[] = $printJob;
            }
            
            // Actualizar sesión
            $session->status = 'printing';
            $session->payment_method = $request->payment_method;
            $session->payment_amount = $request->payment_amount;
            $session->payment_change = $request->payment_amount - $session->total_cost;
            $session->printed_at = now();
            $session->save();
            
            // Procesar trabajos de impresión
            foreach ($printJobs as $printJob) {
                $this->multiPrinterService->processPrintJob($printJob);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Impresión iniciada correctamente',
                'print_jobs' => array_map(function($job) {
                    return [
                        'id' => $job->_id,
                        'file_name' => $job->file_name,
                        'status' => $job->status,
                        'estimated_time' => $job->estimated_completion_time
                    ];
                }, $printJobs),
                'change' => $session->payment_change,
                'redirect_url' => route('print.status', $sessionCode)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error processing print: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la impresión: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Mostrar estado de impresión
     */
    public function status($sessionCode)
    {
        try {
            $session = $this->sessionService->getSessionByCode($sessionCode);
            $printJobs = PrintJob::where('session_id', $session->id)->get();
            
            return view('print.status', compact('session', 'printJobs'));
            
        } catch (\Exception $e) {
            return redirect()->route('home')->with('error', 'Sesión no válida');
        }
    }

    /**
     * Obtener estado de trabajos de impresión (API)
     */
    public function getJobsStatus($sessionCode)
    {
        try {
            $session = $this->sessionService->getSessionByCode($sessionCode);
            $printJobs = PrintJob::where('session_id', $session->id)->get();
            
            $jobsStatus = [];
            foreach ($printJobs as $job) {
                $jobsStatus[] = [
                    'id' => $job->_id,
                    'file_name' => $job->file_name,
                    'status' => $job->status,
                    'progress' => $job->progress ?? 0,
                    'estimated_completion' => $job->estimated_completion_time,
                    'printer_name' => $job->printer->name ?? 'Desconocida',
                    'created_at' => $job->created_at->format('Y-m-d H:i:s'),
                    'completed_at' => $job->completed_at ? $job->completed_at->format('Y-m-d H:i:s') : null
                ];
            }
            
            return response()->json([
                'success' => true,
                'jobs' => $jobsStatus,
                'session_status' => $session->status
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Cancelar trabajo de impresión
     */
    public function cancelJob($sessionCode, $jobId)
    {
        try {
            $session = $this->sessionService->getSessionByCode($sessionCode);
            $printJob = PrintJob::where('id', $jobId)
                ->where('session_id', $session->id)
                               ->firstOrFail();
            
            if (in_array($printJob->status, ['completed', 'cancelled'])) {
                throw new \Exception('No se puede cancelar un trabajo completado o ya cancelado');
            }
            
            $cancelled = $this->multiPrinterService->cancelPrintJob($printJob);
            
            if ($cancelled) {
                return response()->json([
                    'success' => true,
                    'message' => 'Trabajo de impresión cancelado'
                ]);
            } else {
                throw new \Exception('No se pudo cancelar el trabajo');
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Calcular costo de impresión
     */
    public function calculateCost(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*.file_id' => 'required|string',
            'files.*.copies' => 'required|integer|min:1|max:10',
            'files.*.color' => 'required|in:color,bw',
            'files.*.duplex' => 'boolean'
        ]);
        
        try {
            $totalCost = 0;
            $filesCosts = [];
            
            foreach ($request->files as $fileConfig) {
                $file = \App\Models\File::findOrFail($fileConfig['file_id']);
                
                // Configuración temporal para cálculo
                $tempSettings = [
                    'copies' => $fileConfig['copies'],
                    'color' => $fileConfig['color'],
                    'duplex' => $fileConfig['duplex'] ?? false
                ];
                
                $file->print_settings = $tempSettings;
                $fileInfo = $this->fileService->getFileForPrint($file);
                
                $filesCosts[] = [
                    'file_id' => $file->_id,
                    'file_name' => $file->original_name,
                    'cost' => $fileInfo['estimated_cost']
                ];
                
                $totalCost += $fileInfo['estimated_cost'];
            }
            
            return response()->json([
                'success' => true,
                'total_cost' => $totalCost,
                'files_costs' => $filesCosts
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al calcular costo'
            ], 400);
        }
    }
}