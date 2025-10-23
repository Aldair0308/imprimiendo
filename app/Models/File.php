<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'files';

    protected $fillable = [
        'original_name',
        'stored_name',
        'file_path',
        'file_size',
        'file_type',
        'mime_type',
        'pages',
        'session_id',
        'uploaded_at',
        'expires_at',
        'status',
        'checksum'
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'expires_at' => 'datetime',
        'file_size' => 'integer',
        'pages' => 'integer'
    ];

    // Estados posibles del archivo
    const STATUS_UPLOADED = 'uploaded';
    const STATUS_PROCESSING = 'processing';
    const STATUS_READY = 'ready';
    const STATUS_PRINTED = 'printed';
    const STATUS_EXPIRED = 'expired';
    const STATUS_ERROR = 'error';

    /**
     * Relación con la sesión
     */
    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id');
    }

    /**
     * Verificar si el archivo está listo para imprimir
     */
    public function isReady()
    {
        return $this->status === self::STATUS_READY && 
               $this->expires_at > Carbon::now();
    }

    /**
     * Verificar si el archivo ha expirado
     */
    public function isExpired()
    {
        return $this->expires_at <= Carbon::now() || 
               $this->status === self::STATUS_EXPIRED;
    }

    /**
     * Marcar archivo como procesado y listo
     */
    public function markAsReady()
    {
        $this->update(['status' => self::STATUS_READY]);
    }

    /**
     * Marcar archivo como impreso
     */
    public function markAsPrinted()
    {
        $this->update(['status' => self::STATUS_PRINTED]);
    }

    /**
     * Marcar archivo como expirado
     */
    public function markAsExpired()
    {
        $this->update(['status' => self::STATUS_EXPIRED]);
    }

    /**
     * Obtener la URL completa del archivo
     */
    public function getFullPath()
    {
        return storage_path('app/' . $this->file_path);
    }

    /**
     * Obtener el tamaño del archivo formateado
     */
    public function getFormattedSize()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Verificar si el archivo existe físicamente
     */
    public function fileExists()
    {
        return Storage::exists($this->file_path);
    }

    /**
     * Eliminar archivo físico y registro
     */
    public function deleteFile()
    {
        if ($this->fileExists()) {
            Storage::delete($this->file_path);
        }
        $this->delete();
    }

    /**
     * Generar checksum del archivo
     */
    public function generateChecksum()
    {
        if ($this->fileExists()) {
            $fullPath = $this->getFullPath();
            $checksum = md5_file($fullPath);
            $this->update(['checksum' => $checksum]);
            return $checksum;
        }
        return null;
    }

    /**
     * Verificar integridad del archivo
     */
    public function verifyIntegrity()
    {
        if (!$this->checksum || !$this->fileExists()) {
            return false;
        }
        
        $currentChecksum = md5_file($this->getFullPath());
        return $currentChecksum === $this->checksum;
    }

    /**
     * Detectar número de páginas según el tipo de archivo
     */
    public function detectPages()
    {
        if (!$this->fileExists()) {
            return 1;
        }

        $pages = 1;
        
        try {
            switch (strtolower($this->file_type)) {
                case 'pdf':
                    $pages = $this->countPdfPages();
                    break;
                case 'doc':
                case 'docx':
                    $pages = $this->estimateDocPages();
                    break;
                default:
                    $pages = 1; // Imágenes y otros archivos
            }
        } catch (\Exception $e) {
            \Log::error('Error detecting pages for file ' . $this->id . ': ' . $e->getMessage());
            $pages = 1;
        }

        $this->update(['pages' => $pages]);
        return $pages;
    }

    /**
     * Contar páginas de un PDF
     */
    private function countPdfPages()
    {
        $fullPath = $this->getFullPath();
        
        // Método simple para contar páginas PDF
        $content = file_get_contents($fullPath);
        $pageCount = preg_match_all('/\/Page\W/', $content);
        
        return max(1, $pageCount);
    }

    /**
     * Estimar páginas de documentos Word
     */
    private function estimateDocPages()
    {
        // Estimación basada en el tamaño del archivo
        // Aproximadamente 50KB por página para documentos de texto
        $estimatedPages = ceil($this->file_size / 51200);
        return max(1, $estimatedPages);
    }

    /**
     * Scope para archivos expirados
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', Carbon::now())
                    ->orWhere('status', self::STATUS_EXPIRED);
    }

    /**
     * Scope para archivos listos para imprimir
     */
    public function scopeReady($query)
    {
        return $query->where('status', self::STATUS_READY)
                    ->where('expires_at', '>', Carbon::now());
    }
}