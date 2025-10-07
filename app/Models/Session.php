<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Carbon\Carbon;

class Session extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'sessions';

    protected $fillable = [
        'qr_code',
        'status',
        'created_at',
        'expires_at',
        'files',
        'total_cost',
        'print_settings',
        'user_ip',
        'user_agent'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
        'files' => 'array',
        'print_settings' => 'array',
        'total_cost' => 'decimal:2'
    ];

    // Estados posibles de la sesión
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Generar código QR único para la sesión
     */
    public static function generateUniqueQRCode()
    {
        do {
            $qrCode = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        } while (self::where('qr_code', $qrCode)->exists());

        return $qrCode;
    }

    /**
     * Verificar si la sesión está activa
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE && 
               $this->expires_at > Carbon::now();
    }

    /**
     * Verificar si la sesión ha expirado
     */
    public function isExpired()
    {
        return $this->expires_at <= Carbon::now() || 
               $this->status === self::STATUS_EXPIRED;
    }

    /**
     * Marcar sesión como expirada
     */
    public function markAsExpired()
    {
        $this->update(['status' => self::STATUS_EXPIRED]);
    }

    /**
     * Marcar sesión como completada
     */
    public function markAsCompleted()
    {
        $this->update(['status' => self::STATUS_COMPLETED]);
    }

    /**
     * Calcular costo total basado en archivos y configuración
     */
    public function calculateTotalCost()
    {
        $totalCost = 0;
        $pricePerPageBW = config('app.price_per_page_bw', 2.50);
        $pricePerPageColor = config('app.price_per_page_color', 5.00);

        foreach ($this->files as $file) {
            $pages = $file['pages'] ?? 1;
            $copies = $this->print_settings['copies'] ?? 1;
            $isColor = $this->print_settings['color'] ?? false;

            $pricePerPage = $isColor ? $pricePerPageColor : $pricePerPageBW;
            $totalCost += $pages * $copies * $pricePerPage;
        }

        $this->update(['total_cost' => $totalCost]);
        return $totalCost;
    }

    /**
     * Obtener archivos de la sesión
     */
    public function getFiles()
    {
        return File::whereIn('_id', $this->files ?? [])->get();
    }

    /**
     * Agregar archivo a la sesión
     */
    public function addFile($fileId)
    {
        $files = $this->files ?? [];
        if (!in_array($fileId, $files)) {
            $files[] = $fileId;
            $this->update(['files' => $files]);
        }
    }

    /**
     * Remover archivo de la sesión
     */
    public function removeFile($fileId)
    {
        $files = $this->files ?? [];
        $files = array_filter($files, function($id) use ($fileId) {
            return $id !== $fileId;
        });
        $this->update(['files' => array_values($files)]);
    }
}