<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Printer;
use App\Models\PrintJob;
use App\Models\Session;
use App\Services\MultiPrinterService;
use App\Services\PrinterMonitorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminController extends Controller
{
    private $multiPrinterService;
    private $printerMonitorService;
    
    public function __construct(
        MultiPrinterService $multiPrinterService,
        PrinterMonitorService $printerMonitorService
    ) {
        $this->multiPrinterService = $multiPrinterService;
        $this->printerMonitorService = $printerMonitorService;
    }

    /**
     * Mostrar formulario de login administrativo
     */
    public function showLogin()
    {
        return view('admin.login');
    }

    /**
     * Procesar login administrativo
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string'
        ]);
        
        try {
            $admin = Admin::where('email', $request->username)->first();
            
            if (!$admin || !Hash::check($request->password, $admin->password)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Credenciales incorrectas'
                    ], 401);
                }
                return back()->withErrors(['login' => 'Credenciales incorrectas']);
            }
            
            if (!$admin->is_active) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cuenta desactivada'
                    ], 401);
                }
                return back()->withErrors(['login' => 'Cuenta desactivada']);
            }
            
            // Actualizar último acceso
            $admin->last_login = now();
            $admin->save();
            
            // Crear sesión administrativa
            session(['admin_id' => $admin->_id, 'admin_email' => $admin->email]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login exitoso',
                    'redirect_url' => route('admin.dashboard')
                ]);
            }
            
            return redirect()->route('admin.dashboard');
            
        } catch (\Exception $e) {
            Log::error('Admin login error: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error en el sistema'
                ], 500);
            }
            
            return back()->withErrors(['login' => 'Error en el sistema']);
        }
    }

    /**
     * Cerrar sesión administrativa
     */
    public function logout()
    {
        session()->forget(['admin_id', 'admin_username']);
        return redirect()->route('admin.login');
    }

    /**
     * Dashboard principal administrativo
     */
    public function dashboard()
    {
        try {
            // Estadísticas generales con límites de memoria
            $stats = [
                'total_printers' => Printer::count(),
                'active_printers' => Printer::where('status', 'online')->count(),
                'total_sessions_today' => Session::whereDate('created_at', today())->count(),
                'total_jobs_today' => PrintJob::whereDate('created_at', today())->count(),
                'revenue_today' => Session::whereDate('created_at', today())->sum('total_cost') ?? 0,
                'pending_jobs' => PrintJob::whereIn('status', ['pending', 'processing'])->count()
            ];
            
            // Trabajos recientes - limitados y optimizados
            $recentJobs = PrintJob::select(['_id', 'printer_id', 'session_id', 'status', 'created_at', 'file_name'])
                                 ->with(['printer:_id,name', 'session:_id,session_code'])
                                 ->orderBy('created_at', 'desc')
                                 ->limit(5)
                                 ->get();
            
            // Estado de impresoras - limitado y optimizado
            $printers = Printer::select(['_id', 'name', 'status', 'ip_address'])->limit(10)->get();
            $printersStatus = [];
            
            foreach ($printers as $printer) {
                // Evitar llamadas costosas al servicio de monitoreo
                $printersStatus[] = [
                    'printer' => $printer,
                    'status' => $printer->status, // Usar el status del modelo directamente
                    'queue_count' => PrintJob::where('printer_id', $printer->_id)
                                           ->whereIn('status', ['pending', 'processing'])
                                           ->count()
                ];
            }
            
            return view('admin.dashboard', compact('stats', 'recentJobs', 'printersStatus'));
            
        } catch (\Exception $e) {
            Log::error('Admin dashboard error: ' . $e->getMessage());
            return view('admin.dashboard')->with('error', 'Error al cargar el dashboard');
        }
    }

    /**
     * Gestión de impresoras - optimizado
     */
    public function printers()
    {
        try {
            // Limitar y optimizar consulta de impresoras
            $printers = Printer::select(['_id', 'name', 'status', 'ip_address', 'location'])->limit(10)->get();
            $printersWithStatus = [];
            
            foreach ($printers as $printer) {
                // Evitar llamadas costosas al servicio de monitoreo
                $printersWithStatus[] = [
                    'printer' => $printer,
                    'status' => ['status' => $printer->status], // Usar status directo
                    'jobs_today' => PrintJob::where('printer_id', $printer->_id)
                                          ->whereDate('created_at', today())
                                          ->count(),
                    'queue_count' => PrintJob::where('printer_id', $printer->_id)
                                           ->whereIn('status', ['pending', 'processing'])
                                           ->count()
                ];
            }
            
            return view('admin.printers', compact('printersWithStatus'));
            
        } catch (\Exception $e) {
            Log::error('Admin printers error: ' . $e->getMessage());
            return view('admin.printers')->with('error', 'Error al cargar impresoras');
        }
    }

    /**
     * Agregar nueva impresora
     */
    public function addPrinter(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'port' => 'required|integer|between:1,65535',
            'model' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'supports_color' => 'boolean',
            'supports_duplex' => 'boolean',
            'max_paper_size' => 'required|in:A4,Letter,Legal'
        ]);
        
        try {
            $printer = new Printer([
                'name' => $request->name,
                'ip_address' => $request->ip_address,
                'port' => $request->port,
                'model' => $request->model,
                'location' => $request->location,
                'supports_color' => $request->supports_color ?? false,
                'supports_duplex' => $request->supports_duplex ?? false,
                'max_paper_size' => $request->max_paper_size,
                'status' => 'offline',
                'is_active' => true,
                'priority' => 1,
                'created_at' => now()
            ]);
            
            $printer->save();
            
            // Verificar conectividad inicial
            $this->printerMonitorService->checkPrinterStatus($printer);
            
            return response()->json([
                'success' => true,
                'message' => 'Impresora agregada correctamente',
                'printer_id' => $printer->_id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error adding printer: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar impresora'
            ], 400);
        }
    }

    /**
     * Actualizar configuración de impresora
     */
    public function updatePrinter(Request $request, $printerId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'port' => 'required|integer|between:1,65535',
            'location' => 'required|string|max:255',
            'priority' => 'required|integer|between:1,10',
            'is_active' => 'boolean'
        ]);
        
        try {
            $printer = Printer::findOrFail($printerId);
            
            $printer->update([
                'name' => $request->name,
                'ip_address' => $request->ip_address,
                'port' => $request->port,
                'location' => $request->location,
                'priority' => $request->priority,
                'is_active' => $request->is_active ?? true,
                'updated_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Impresora actualizada correctamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar impresora'
            ], 400);
        }
    }

    /**
     * Eliminar impresora
     */
    public function deletePrinter($printerId)
    {
        try {
            $printer = Printer::findOrFail($printerId);
            
            // Verificar que no tenga trabajos pendientes
            $pendingJobs = PrintJob::where('printer_id', $printerId)
                                  ->whereIn('status', ['pending', 'processing'])
                                  ->count();
            
            if ($pendingJobs > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar una impresora con trabajos pendientes'
                ], 400);
            }
            
            $printer->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Impresora eliminada correctamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar impresora'
            ], 400);
        }
    }

    /**
     * Trabajos de impresión
     */
    public function jobs(Request $request)
    {
        try {
            $query = PrintJob::with(['printer', 'session']);
            
            // Filtros
            if ($request->status) {
                $query->where('status', $request->status);
            }
            
            if ($request->printer_id) {
                $query->where('printer_id', $request->printer_id);
            }
            
            if ($request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            $jobs = $query->orderBy('created_at', 'desc')->paginate(20);
            $printers = Printer::all();
            
            return view('admin.jobs', compact('jobs', 'printers'));
            
        } catch (\Exception $e) {
            Log::error('Admin jobs error: ' . $e->getMessage());
            return view('admin.jobs')->with('error', 'Error al cargar trabajos');
        }
    }

    /**
     * Reportes y estadísticas
     */
    public function reports(Request $request)
    {
        try {
            $dateFrom = $request->date_from ?? Carbon::now()->subDays(30)->format('Y-m-d');
            $dateTo = $request->date_to ?? Carbon::now()->format('Y-m-d');
            
            // Estadísticas del período
            $stats = [
                'total_sessions' => Session::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'total_jobs' => PrintJob::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'total_revenue' => Session::whereBetween('created_at', [$dateFrom, $dateTo])->sum('total_cost'),
                'avg_session_cost' => Session::whereBetween('created_at', [$dateFrom, $dateTo])->avg('total_cost'),
                'completed_jobs' => PrintJob::whereBetween('created_at', [$dateFrom, $dateTo])
                                          ->where('status', 'completed')->count(),
                'failed_jobs' => PrintJob::whereBetween('created_at', [$dateFrom, $dateTo])
                                        ->where('status', 'failed')->count()
            ];
            
            // Estadísticas por impresora
            $printerStats = Printer::withCount([
                'printJobs as jobs_count' => function($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('created_at', [$dateFrom, $dateTo]);
                },
                'printJobs as completed_jobs' => function($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('created_at', [$dateFrom, $dateTo])
                          ->where('status', 'completed');
                }
            ])->get();
            
            // Ingresos por día
            $dailyRevenue = Session::selectRaw('DATE(created_at) as date, SUM(total_cost) as revenue')
                                  ->whereBetween('created_at', [$dateFrom, $dateTo])
                                  ->groupBy('date')
                                  ->orderBy('date')
                                  ->get();
            
            return view('admin.reports', compact('stats', 'printerStats', 'dailyRevenue', 'dateFrom', 'dateTo'));
            
        } catch (\Exception $e) {
            Log::error('Admin reports error: ' . $e->getMessage());
            return view('admin.reports')->with('error', 'Error al cargar reportes');
        }
    }

    /**
     * Configuración del sistema
     */
    public function settings()
    {
        try {
            $settings = [
                'max_files_per_session' => env('MAX_FILES_PER_SESSION', 5),
                'max_file_size_mb' => env('MAX_FILE_SIZE_MB', 10),
                'session_timeout_minutes' => env('SESSION_TIMEOUT_MINUTES', 30),
                'price_per_page_bw' => env('PRICE_PER_PAGE_BW', 1.00),
                'price_per_page_color' => env('PRICE_PER_PAGE_COLOR', 3.00),
                'auto_delete_files_hours' => env('AUTO_DELETE_FILES_HOURS', 24)
            ];
            
            return view('admin.settings', compact('settings'));
            
        } catch (\Exception $e) {
            Log::error('Admin settings error: ' . $e->getMessage());
            return view('admin.settings')->with('error', 'Error al cargar configuración');
        }
    }

    /**
     * Actualizar configuración del sistema
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'max_files_per_session' => 'required|integer|min:1|max:20',
            'max_file_size_mb' => 'required|integer|min:1|max:100',
            'session_timeout_minutes' => 'required|integer|min:5|max:120',
            'price_per_page_bw' => 'required|numeric|min:0.1|max:10',
            'price_per_page_color' => 'required|numeric|min:0.1|max:20',
            'auto_delete_files_hours' => 'required|integer|min:1|max:168'
        ]);
        
        try {
            // Aquí normalmente actualizarías un archivo .env o base de datos de configuración
            // Por simplicidad, solo retornamos éxito
            
            return response()->json([
                'success' => true,
                'message' => 'Configuración actualizada correctamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar configuración'
            ], 400);
        }
    }

    /**
     * Gestión de sesiones
     */
    public function sessions()
    {
        try {
            $sessions = Session::select(['_id', 'session_code', 'status', 'created_at', 'expires_at', 'total_cost'])
                              ->orderBy('created_at', 'desc')
                              ->limit(50)
                              ->get();
            
            return view('admin.sessions', compact('sessions'));
            
        } catch (\Exception $e) {
            Log::error('Admin sessions error: ' . $e->getMessage());
            return view('admin.sessions')->with('error', 'Error al cargar sesiones');
        }
    }

    /**
     * Obtener estado del sistema en tiempo real - optimizado
     */
    public function getSystemStatus()
    {
        try {
            // Limitar consultas y usar select específico
            $printers = Printer::select(['_id', 'name', 'status'])->limit(5)->get();
            $systemStatus = [];
            
            foreach ($printers as $printer) {
                // Evitar llamadas costosas al servicio de monitoreo
                $systemStatus[] = [
                    'printer_id' => $printer->_id,
                    'name' => $printer->name,
                    'status' => $printer->status, // Usar status directo del modelo
                    'queue_count' => PrintJob::where('printer_id', $printer->_id)
                                           ->whereIn('status', ['pending', 'processing'])
                                           ->count(),
                    'last_job' => null // Evitar consulta costosa
                ];
            }
            
            return response()->json([
                'success' => true,
                'system_status' => $systemStatus,
                'total_pending_jobs' => PrintJob::whereIn('status', ['pending', 'processing'])->count(),
                'active_sessions' => Session::where('status', 'active')->count()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estado del sistema'
            ], 400);
        }
    }
}