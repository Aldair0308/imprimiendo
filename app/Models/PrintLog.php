<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Carbon\Carbon;

class PrintLog extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'print_logs';

    protected $fillable = [
        'session_id',
        'file_id',
        'file_name',
        'session_code',
        'print_settings',
        'reason',
        'attempted_at',
        'metadata',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'attempted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'print_settings' => 'array',
        'metadata' => 'array'
    ];

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
     * Scope para logs recientes
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('attempted_at', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Scope por sesión
     */
    public function scopeBySession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope por razón
     */
    public function scopeByReason($query, $reason)
    {
        return $query->where('reason', $reason);
    }

    /**
     * Obtener configuración formateada
     */
    public function getFormattedSettings()
    {
        $settings = $this->print_settings ?? [];
        
        return [
            'copies' => $settings['copies'] ?? 1,
            'color_mode' => $this->getColorModeLabel($settings['color_mode'] ?? 'color'),
            'paper_size' => $settings['paper_size'] ?? 'A4',
            'orientation' => $this->getOrientationLabel($settings['orientation'] ?? 'portrait')
        ];
    }

    /**
     * Obtener etiqueta del modo de color
     */
    private function getColorModeLabel($colorMode)
    {
        $labels = [
            'color' => 'A Color',
            'grayscale' => 'Escala de Grises',
            'bw' => 'Blanco y Negro'
        ];

        return $labels[$colorMode] ?? $colorMode;
    }

    /**
     * Obtener etiqueta de orientación
     */
    private function getOrientationLabel($orientation)
    {
        $labels = [
            'portrait' => 'Vertical',
            'landscape' => 'Horizontal'
        ];

        return $labels[$orientation] ?? $orientation;
    }

    /**
     * Crear log de intento de impresión
     */
    public static function createPrintAttempt($session, $file, $printSettings, $metadata = [])
    {
        return self::create([
            'session_id' => $session->id,
            'file_id' => $file->id,
            'file_name' => $metadata['file_name'] ?? $file->original_name,
            'session_code' => $metadata['session_code'] ?? $session->session_code,
            'print_settings' => $printSettings,
            'reason' => $metadata['reason'] ?? 'Print attempt logged',
            'attempted_at' => $metadata['attempted_at'] ?? Carbon::now(),
            'metadata' => $metadata
        ]);
    }
}