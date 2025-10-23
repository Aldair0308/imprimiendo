<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Carbon\Carbon;

class PrintJob extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'print_jobs';

    protected $fillable = [
        'session_id',
        'file_id',
        'printer_id',
        'status',
        'priority',
        'copies',
        'color_mode',
        'paper_size',
        'duplex',
        'quality',
        'pages_range',
        'total_pages',
        'cost',
        'created_at',
        'started_at',
        'completed_at',
        'error_message',
        'retry_count',
        'print_settings'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'copies' => 'integer',
        'total_pages' => 'integer',
        'cost' => 'decimal:2',
        'retry_count' => 'integer',
        'priority' => 'integer',
        'duplex' => 'boolean',
        'print_settings' => 'array'
    ];

    // Estados del trabajo de impresión
    const STATUS_PENDING = 'pending';
    const STATUS_QUEUED = 'queued';
    const STATUS_PRINTING = 'printing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    // Prioridades
    const PRIORITY_LOW = 1;
    const PRIORITY_NORMAL = 2;
    const PRIORITY_HIGH = 3;
    const PRIORITY_URGENT = 4;

    // Modos de color
    const COLOR_BW = 'bw';
    const COLOR_COLOR = 'color';

    // Tamaños de papel
    const PAPER_A4 = 'A4';
    const PAPER_LETTER = 'Letter';
    const PAPER_LEGAL = 'Legal';

    // Calidades de impresión
    const QUALITY_DRAFT = 'draft';
    const QUALITY_NORMAL = 'normal';
    const QUALITY_HIGH = 'high';

    /**
     * Relación con la sesión
     */
    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id');
    }

    /**
     * Relación con el archivo
     */
    public function file()
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    /**
     * Relación con la impresora
     */
    public function printer()
    {
        return $this->belongsTo(Printer::class, 'printer_id');
    }

    /**
     * Verificar si el trabajo está pendiente
     */
    public function isPending()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_QUEUED]);
    }

    /**
     * Verificar si el trabajo está en progreso
     */
    public function isInProgress()
    {
        return $this->status === self::STATUS_PRINTING;
    }

    /**
     * Verificar si el trabajo está completado
     */
    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Verificar si el trabajo ha fallado
     */
    public function isFailed()
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Marcar trabajo como en cola
     */
    public function markAsQueued($printerId = null)
    {
        $updateData = ['status' => self::STATUS_QUEUED];
        if ($printerId) {
            $updateData['printer_id'] = $printerId;
        }
        $this->update($updateData);
    }

    /**
     * Marcar trabajo como iniciado
     */
    public function markAsStarted()
    {
        $this->update([
            'status' => self::STATUS_PRINTING,
            'started_at' => Carbon::now()
        ]);
    }

    /**
     * Marcar trabajo como completado
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => Carbon::now()
        ]);
    }

    /**
     * Marcar trabajo como fallido
     */
    public function markAsFailed($errorMessage = null)
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1
        ]);
    }

    /**
     * Marcar trabajo como cancelado
     */
    public function markAsCancelled()
    {
        $this->update(['status' => self::STATUS_CANCELLED]);
    }

    /**
     * Calcular costo del trabajo
     */
    public function calculateCost()
    {
        $pricePerPageBW = config('app.price_per_page_bw', 2.50);
        $pricePerPageColor = config('app.price_per_page_color', 5.00);
        
        $pricePerPage = $this->color_mode === self::COLOR_COLOR ? 
                       $pricePerPageColor : $pricePerPageBW;
        
        $totalPages = $this->total_pages * $this->copies;
        $cost = $totalPages * $pricePerPage;
        
        $this->update(['cost' => $cost]);
        return $cost;
    }

    /**
     * Verificar si se puede reintentar
     */
    public function canRetry()
    {
        $maxRetries = config('app.max_print_retries', 3);
        return $this->isFailed() && $this->retry_count < $maxRetries;
    }

    /**
     * Reintentar trabajo
     */
    public function retry()
    {
        if ($this->canRetry()) {
            $this->update([
                'status' => self::STATUS_PENDING,
                'error_message' => null,
                'started_at' => null,
                'completed_at' => null
            ]);
            return true;
        }
        return false;
    }

    /**
     * Obtener tiempo de ejecución
     */
    public function getExecutionTime()
    {
        if ($this->started_at && $this->completed_at) {
            return $this->completed_at->diffInSeconds($this->started_at);
        }
        return null;
    }

    /**
     * Obtener configuración de impresión formateada
     */
    public function getFormattedSettings()
    {
        return [
            'copies' => $this->copies,
            'color' => $this->color_mode === self::COLOR_COLOR ? 'Color' : 'Blanco y Negro',
            'paper' => $this->paper_size,
            'quality' => ucfirst($this->quality),
            'duplex' => $this->duplex ? 'Doble cara' : 'Una cara',
            'pages' => $this->pages_range ?: 'Todas las páginas'
        ];
    }

    /**
     * Scope para trabajos pendientes
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_QUEUED]);
    }

    /**
     * Scope para trabajos en progreso
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_PRINTING);
    }

    /**
     * Scope para trabajos completados
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope para trabajos fallidos
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope por prioridad
     */
    public function scopeByPriority($query, $priority = null)
    {
        if ($priority) {
            return $query->where('priority', $priority);
        }
        return $query->orderBy('priority', 'desc');
    }

    /**
     * Scope por impresora
     */
    public function scopeByPrinter($query, $printerId)
    {
        return $query->where('printer_id', $printerId);
    }
}