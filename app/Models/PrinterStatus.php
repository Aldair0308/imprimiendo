<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Carbon\Carbon;

class PrinterStatus extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'printer_statuses';

    protected $fillable = [
        'printer_id',
        'status',
        'message',
        'timestamp',
        'response_time',
        'error_code',
        'additional_data'
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'response_time' => 'integer',
        'additional_data' => 'array'
    ];

    // Estados posibles
    const STATUS_ONLINE = 'online';
    const STATUS_OFFLINE = 'offline';
    const STATUS_BUSY = 'busy';
    const STATUS_ERROR = 'error';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_PAPER_JAM = 'paper_jam';
    const STATUS_OUT_OF_PAPER = 'out_of_paper';
    const STATUS_OUT_OF_INK = 'out_of_ink';
    const STATUS_DOOR_OPEN = 'door_open';

    /**
     * Relación con la impresora
     */
    public function printer()
    {
        return $this->belongsTo(Printer::class, 'printer_id');
    }

    /**
     * Verificar si es un estado de error
     */
    public function isErrorStatus()
    {
        return in_array($this->status, [
            self::STATUS_ERROR,
            self::STATUS_PAPER_JAM,
            self::STATUS_OUT_OF_PAPER,
            self::STATUS_OUT_OF_INK,
            self::STATUS_DOOR_OPEN
        ]);
    }

    /**
     * Verificar si es un estado crítico
     */
    public function isCriticalStatus()
    {
        return in_array($this->status, [
            self::STATUS_OFFLINE,
            self::STATUS_ERROR,
            self::STATUS_PAPER_JAM
        ]);
    }

    /**
     * Obtener descripción del estado
     */
    public function getStatusDescription()
    {
        $descriptions = [
            self::STATUS_ONLINE => 'En línea y disponible',
            self::STATUS_OFFLINE => 'Fuera de línea',
            self::STATUS_BUSY => 'Ocupada imprimiendo',
            self::STATUS_ERROR => 'Error en la impresora',
            self::STATUS_MAINTENANCE => 'En mantenimiento',
            self::STATUS_PAPER_JAM => 'Atasco de papel',
            self::STATUS_OUT_OF_PAPER => 'Sin papel',
            self::STATUS_OUT_OF_INK => 'Sin tinta/tóner',
            self::STATUS_DOOR_OPEN => 'Puerta abierta'
        ];

        return $descriptions[$this->status] ?? 'Estado desconocido';
    }

    /**
     * Obtener color del estado para UI
     */
    public function getStatusColor()
    {
        $colors = [
            self::STATUS_ONLINE => 'green',
            self::STATUS_OFFLINE => 'red',
            self::STATUS_BUSY => 'yellow',
            self::STATUS_ERROR => 'red',
            self::STATUS_MAINTENANCE => 'blue',
            self::STATUS_PAPER_JAM => 'orange',
            self::STATUS_OUT_OF_PAPER => 'orange',
            self::STATUS_OUT_OF_INK => 'orange',
            self::STATUS_DOOR_OPEN => 'orange'
        ];

        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Obtener icono del estado
     */
    public function getStatusIcon()
    {
        $icons = [
            self::STATUS_ONLINE => 'check-circle',
            self::STATUS_OFFLINE => 'x-circle',
            self::STATUS_BUSY => 'clock',
            self::STATUS_ERROR => 'alert-circle',
            self::STATUS_MAINTENANCE => 'tool',
            self::STATUS_PAPER_JAM => 'alert-triangle',
            self::STATUS_OUT_OF_PAPER => 'file-x',
            self::STATUS_OUT_OF_INK => 'droplet',
            self::STATUS_DOOR_OPEN => 'door-open'
        ];

        return $icons[$this->status] ?? 'help-circle';
    }

    /**
     * Crear registro de estado
     */
    public static function createStatus($printerId, $status, $message = null, $responseTime = null, $additionalData = [])
    {
        return self::create([
            'printer_id' => $printerId,
            'status' => $status,
            'message' => $message,
            'timestamp' => Carbon::now(),
            'response_time' => $responseTime,
            'additional_data' => $additionalData
        ]);
    }

    /**
     * Obtener último estado de una impresora
     */
    public static function getLastStatus($printerId)
    {
        return self::where('printer_id', $printerId)
                  ->orderBy('timestamp', 'desc')
                  ->first();
    }

    /**
     * Obtener historial de estados de una impresora
     */
    public static function getHistory($printerId, $hours = 24)
    {
        $startTime = Carbon::now()->subHours($hours);
        
        return self::where('printer_id', $printerId)
                  ->where('timestamp', '>=', $startTime)
                  ->orderBy('timestamp', 'desc')
                  ->get();
    }

    /**
     * Obtener estadísticas de disponibilidad
     */
    public static function getAvailabilityStats($printerId, $days = 7)
    {
        $startDate = Carbon::now()->subDays($days);
        
        $totalRecords = self::where('printer_id', $printerId)
                           ->where('timestamp', '>=', $startDate)
                           ->count();
        
        if ($totalRecords == 0) {
            return [
                'uptime_percentage' => 0,
                'downtime_percentage' => 100,
                'total_checks' => 0,
                'online_checks' => 0,
                'offline_checks' => 0
            ];
        }
        
        $onlineRecords = self::where('printer_id', $printerId)
                            ->where('timestamp', '>=', $startDate)
                            ->where('status', self::STATUS_ONLINE)
                            ->count();
        
        $offlineRecords = self::where('printer_id', $printerId)
                             ->where('timestamp', '>=', $startDate)
                             ->where('status', self::STATUS_OFFLINE)
                             ->count();
        
        $uptimePercentage = ($onlineRecords / $totalRecords) * 100;
        $downtimePercentage = 100 - $uptimePercentage;
        
        return [
            'uptime_percentage' => round($uptimePercentage, 2),
            'downtime_percentage' => round($downtimePercentage, 2),
            'total_checks' => $totalRecords,
            'online_checks' => $onlineRecords,
            'offline_checks' => $offlineRecords
        ];
    }

    /**
     * Obtener tiempo promedio de respuesta
     */
    public static function getAverageResponseTime($printerId, $hours = 24)
    {
        $startTime = Carbon::now()->subHours($hours);
        
        return self::where('printer_id', $printerId)
                  ->where('timestamp', '>=', $startTime)
                  ->whereNotNull('response_time')
                  ->avg('response_time');
    }

    /**
     * Detectar cambios de estado
     */
    public static function detectStatusChange($printerId, $newStatus)
    {
        $lastStatus = self::getLastStatus($printerId);
        
        if (!$lastStatus || $lastStatus->status !== $newStatus) {
            return true; // Hay cambio de estado
        }
        
        return false; // No hay cambio
    }

    /**
     * Limpiar registros antiguos
     */
    public static function cleanOldRecords($days = 30)
    {
        $cutoffDate = Carbon::now()->subDays($days);
        
        return self::where('timestamp', '<', $cutoffDate)->delete();
    }

    /**
     * Scope para estados de error
     */
    public function scopeErrors($query)
    {
        return $query->whereIn('status', [
            self::STATUS_ERROR,
            self::STATUS_PAPER_JAM,
            self::STATUS_OUT_OF_PAPER,
            self::STATUS_OUT_OF_INK,
            self::STATUS_DOOR_OPEN
        ]);
    }

    /**
     * Scope para estados críticos
     */
    public function scopeCritical($query)
    {
        return $query->whereIn('status', [
            self::STATUS_OFFLINE,
            self::STATUS_ERROR,
            self::STATUS_PAPER_JAM
        ]);
    }

    /**
     * Scope por rango de tiempo
     */
    public function scopeInTimeRange($query, $startTime, $endTime = null)
    {
        $endTime = $endTime ?: Carbon::now();
        
        return $query->whereBetween('timestamp', [$startTime, $endTime]);
    }

    /**
     * Scope por impresora
     */
    public function scopeForPrinter($query, $printerId)
    {
        return $query->where('printer_id', $printerId);
    }
}