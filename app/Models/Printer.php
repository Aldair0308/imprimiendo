<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Carbon\Carbon;

class Printer extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'printers';

    protected $fillable = [
        'name',
        'model',
        'ip_address',
        'port',
        'status',
        'is_active',
        'is_available',
        'capabilities',
        'location',
        'description',
        'max_queue_size',
        'current_queue_size',
        'total_jobs_printed',
        'last_health_check',
        'last_job_completed',
        'error_count',
        'maintenance_mode',
        'last_maintenance',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_available' => 'boolean',
        'maintenance_mode' => 'boolean',
        'capabilities' => 'array',
        'max_queue_size' => 'integer',
        'current_queue_size' => 'integer',
        'total_jobs_printed' => 'integer',
        'error_count' => 'integer',
        'port' => 'integer',
        'last_health_check' => 'datetime',
        'last_job_completed' => 'datetime',
        'last_maintenance' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Estados de la impresora
    const STATUS_ONLINE = 'online';
    const STATUS_OFFLINE = 'offline';
    const STATUS_BUSY = 'busy';
    const STATUS_ERROR = 'error';
    const STATUS_MAINTENANCE = 'maintenance';

    // Capacidades de la impresora
    const CAPABILITY_COLOR = 'color';
    const CAPABILITY_DUPLEX = 'duplex';
    const CAPABILITY_A4 = 'a4';
    const CAPABILITY_LETTER = 'letter';
    const CAPABILITY_LEGAL = 'legal';

    /**
     * Relación con trabajos de impresión
     */
    public function printJobs()
    {
        return $this->hasMany(PrintJob::class, 'printer_id')->limit(100);
    }

    /**
     * Relación con estados de impresora
     */
    public function statuses()
    {
        return $this->hasMany(PrinterStatus::class, 'printer_id')->limit(50)->latest();
    }

    /**
     * Verificar si la impresora está disponible
     */
    public function isAvailable()
    {
        return $this->is_active && 
               $this->status === self::STATUS_ONLINE && 
               !$this->maintenance_mode &&
               $this->current_queue_size < $this->max_queue_size;
    }

    /**
     * Verificar si la impresora está ocupada
     */
    public function isBusy()
    {
        return $this->status === self::STATUS_BUSY;
    }

    /**
     * Verificar si la impresora está en línea
     */
    public function isOnline()
    {
        return $this->status === self::STATUS_ONLINE;
    }

    /**
     * Verificar si la impresora está fuera de línea
     */
    public function isOffline()
    {
        return $this->status === self::STATUS_OFFLINE;
    }

    /**
     * Verificar si la impresora tiene errores
     */
    public function hasError()
    {
        return $this->status === self::STATUS_ERROR;
    }

    /**
     * Marcar impresora como en línea
     */
    public function markAsOnline()
    {
        $this->update([
            'status' => self::STATUS_ONLINE,
            'last_health_check' => Carbon::now()
        ]);
    }

    /**
     * Marcar impresora como fuera de línea
     */
    public function markAsOffline()
    {
        $this->update([
            'status' => self::STATUS_OFFLINE,
            'last_health_check' => Carbon::now()
        ]);
    }

    /**
     * Marcar impresora como ocupada
     */
    public function markAsBusy()
    {
        $this->update(['status' => self::STATUS_BUSY]);
    }

    /**
     * Marcar impresora con error
     */
    public function markAsError($errorMessage = null)
    {
        $this->update([
            'status' => self::STATUS_ERROR,
            'error_count' => $this->error_count + 1,
            'last_health_check' => Carbon::now()
        ]);

        // Registrar el error en el historial de estados
        $this->recordStatus(self::STATUS_ERROR, $errorMessage);
    }

    /**
     * Activar modo de mantenimiento
     */
    public function enableMaintenanceMode()
    {
        $this->update([
            'maintenance_mode' => true,
            'status' => self::STATUS_MAINTENANCE
        ]);
    }

    /**
     * Desactivar modo de mantenimiento
     */
    public function disableMaintenanceMode()
    {
        $this->update([
            'maintenance_mode' => false,
            'status' => self::STATUS_ONLINE
        ]);
    }

    /**
     * Incrementar cola de trabajos
     */
    public function incrementQueue()
    {
        $this->increment('current_queue_size');
    }

    /**
     * Decrementar cola de trabajos
     */
    public function decrementQueue()
    {
        if ($this->current_queue_size > 0) {
            $this->decrement('current_queue_size');
        }
    }

    /**
     * Registrar trabajo completado
     */
    public function recordJobCompleted()
    {
        $this->update([
            'total_jobs_printed' => $this->total_jobs_printed + 1,
            'last_job_completed' => Carbon::now()
        ]);
        $this->decrementQueue();
    }

    /**
     * Obtener carga de trabajo (porcentaje)
     */
    public function getWorkloadPercentage()
    {
        if ($this->max_queue_size == 0) {
            return 0;
        }
        return ($this->current_queue_size / $this->max_queue_size) * 100;
    }

    /**
     * Verificar capacidad específica
     */
    public function hasCapability($capability)
    {
        return in_array($capability, $this->capabilities ?? []);
    }

    /**
     * Verificar si soporta color
     */
    public function supportsColor()
    {
        return $this->hasCapability(self::CAPABILITY_COLOR);
    }

    /**
     * Verificar si soporta dúplex
     */
    public function supportsDuplex()
    {
        return $this->hasCapability(self::CAPABILITY_DUPLEX);
    }

    /**
     * Verificar si soporta tamaño de papel
     */
    public function supportsPaperSize($size)
    {
        $sizeCapability = 'capability_' . strtolower($size);
        return $this->hasCapability($sizeCapability);
    }

    /**
     * Obtener trabajos pendientes
     */
    public function getPendingJobs()
    {
        return $this->printJobs()
                   ->whereIn('status', [PrintJob::STATUS_PENDING, PrintJob::STATUS_QUEUED])
                   ->orderBy('priority', 'desc')
                   ->orderBy('created_at', 'asc')
                   ->get();
    }

    /**
     * Obtener próximo trabajo en cola
     */
    public function getNextJob()
    {
        return $this->printJobs()
                   ->where('status', PrintJob::STATUS_QUEUED)
                   ->orderBy('priority', 'desc')
                   ->orderBy('created_at', 'asc')
                   ->first();
    }

    /**
     * Registrar estado en historial
     */
    public function recordStatus($status, $message = null)
    {
        PrinterStatus::create([
            'printer_id' => $this->_id,
            'status' => $status,
            'message' => $message,
            'timestamp' => Carbon::now()
        ]);
    }

    /**
     * Obtener último estado registrado
     */
    public function getLastStatus()
    {
        return $this->statuses()
                   ->orderBy('timestamp', 'desc')
                   ->first();
    }

    /**
     * Obtener estadísticas de la impresora
     */
    public function getStats($days = 7)
    {
        $startDate = Carbon::now()->subDays($days);
        
        return [
            'total_jobs' => $this->printJobs()->count(),
            'jobs_last_days' => $this->printJobs()
                                    ->where('created_at', '>=', $startDate)
                                    ->count(),
            'completed_jobs' => $this->printJobs()
                                    ->where('status', PrintJob::STATUS_COMPLETED)
                                    ->count(),
            'failed_jobs' => $this->printJobs()
                                 ->where('status', PrintJob::STATUS_FAILED)
                                 ->count(),
            'current_queue' => $this->current_queue_size,
            'workload_percentage' => $this->getWorkloadPercentage(),
            'uptime_percentage' => $this->calculateUptimePercentage($days),
            'error_count' => $this->error_count
        ];
    }

    /**
     * Calcular porcentaje de tiempo activo
     */
    private function calculateUptimePercentage($days)
    {
        $startDate = Carbon::now()->subDays($days);
        $totalMinutes = $days * 24 * 60;
        
        $offlineMinutes = $this->statuses()
                              ->where('status', self::STATUS_OFFLINE)
                              ->where('timestamp', '>=', $startDate)
                              ->count() * 5; // Asumiendo checks cada 5 minutos
        
        $uptimeMinutes = $totalMinutes - $offlineMinutes;
        return ($uptimeMinutes / $totalMinutes) * 100;
    }

    /**
     * Scope para impresoras activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para impresoras disponibles
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
                    ->where('status', self::STATUS_ONLINE)
                    ->where('maintenance_mode', false)
                    ->whereRaw('current_queue_size < max_queue_size');
    }

    /**
     * Scope para impresoras en línea
     */
    public function scopeOnline($query)
    {
        return $query->where('status', self::STATUS_ONLINE);
    }

    /**
     * Scope por capacidad
     */
    public function scopeWithCapability($query, $capability)
    {
        return $query->where('capabilities', 'like', '%' . $capability . '%');
    }
}