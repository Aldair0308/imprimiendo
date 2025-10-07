<?php

namespace App\Services;

use App\Models\File;
use App\Models\Session;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FileService
{
    private $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt'];
    private $maxFileSize = 10 * 1024 * 1024; // 10MB
    private $uploadPath = 'uploads/sessions';

    /**
     * Subir archivo a una sesión
     */
    public function uploadFile(UploadedFile $uploadedFile, Session $session)
    {
        try {
            // Validar archivo
            $this->validateFile($uploadedFile);
            
            // Generar nombre único
            $fileName = $this->generateUniqueFileName($uploadedFile);
            
            // Guardar archivo
            $filePath = $uploadedFile->storeAs(
                $this->uploadPath . '/' . $session->session_code,
                $fileName,
                'public'
            );
            
            // Crear registro en base de datos
            $file = File::create([
                'session_id' => $session->_id,
                'original_name' => $uploadedFile->getClientOriginalName(),
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_size' => $uploadedFile->getSize(),
                'mime_type' => $uploadedFile->getMimeType(),
                'extension' => $uploadedFile->getClientOriginalExtension(),
                'upload_status' => 'completed',
                'uploaded_at' => Carbon::now()
            ]);
            
            // Analizar archivo para obtener información adicional
            $this->analyzeFile($file);
            
            Log::info('File uploaded successfully', [
                'file_id' => $file->_id,
                'session_id' => $session->_id,
                'original_name' => $uploadedFile->getClientOriginalName(),
                'file_size' => $uploadedFile->getSize()
            ]);
            
            return $file;
            
        } catch (\Exception $e) {
            Log::error('Error uploading file: ' . $e->getMessage(), [
                'session_id' => $session->_id,
                'original_name' => $uploadedFile->getClientOriginalName()
            ]);
            
            throw new \Exception('Error al subir el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Validar archivo subido
     */
    private function validateFile(UploadedFile $file)
    {
        // Verificar tamaño
        if ($file->getSize() > $this->maxFileSize) {
            throw new \Exception('El archivo es demasiado grande. Máximo permitido: 10MB');
        }
        
        // Verificar extensión
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $this->allowedExtensions)) {
            throw new \Exception('Tipo de archivo no permitido. Extensiones permitidas: ' . implode(', ', $this->allowedExtensions));
        }
        
        // Verificar que el archivo no esté corrupto
        if (!$file->isValid()) {
            throw new \Exception('El archivo está corrupto o no se subió correctamente');
        }
    }

    /**
     * Generar nombre único para el archivo
     */
    private function generateUniqueFileName(UploadedFile $file)
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = Carbon::now()->format('YmdHis');
        $random = Str::random(8);
        
        return "{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Analizar archivo para obtener información adicional
     */
    private function analyzeFile(File $file)
    {
        try {
            $filePath = Storage::disk('public')->path($file->file_path);
            $analysis = [];
            
            switch ($file->extension) {
                case 'pdf':
                    $analysis = $this->analyzePDF($filePath);
                    break;
                case 'jpg':
                case 'jpeg':
                case 'png':
                    $analysis = $this->analyzeImage($filePath);
                    break;
                case 'doc':
                case 'docx':
                    $analysis = $this->analyzeDocument($filePath);
                    break;
                case 'txt':
                    $analysis = $this->analyzeText($filePath);
                    break;
            }
            
            // Actualizar archivo con información del análisis
            $file->update([
                'page_count' => $analysis['page_count'] ?? 1,
                'print_settings' => $analysis['print_settings'] ?? [],
                'analysis_completed' => true,
                'analyzed_at' => Carbon::now()
            ]);
            
        } catch (\Exception $e) {
            Log::warning('Error analyzing file: ' . $e->getMessage(), [
                'file_id' => $file->_id
            ]);
        }
    }

    /**
     * Analizar archivo PDF
     */
    private function analyzePDF($filePath)
    {
        try {
            // Usar una librería como smalot/pdfparser para análisis real
            // Por ahora simulamos el análisis
            $pageCount = rand(1, 20);
            
            return [
                'page_count' => $pageCount,
                'print_settings' => [
                    'recommended_paper_size' => 'A4',
                    'color_detected' => rand(0, 1) ? true : false,
                    'estimated_print_time' => $pageCount * 30 // segundos
                ]
            ];
        } catch (\Exception $e) {
            return ['page_count' => 1];
        }
    }

    /**
     * Analizar imagen
     */
    private function analyzeImage($filePath)
    {
        try {
            $imageInfo = getimagesize($filePath);
            
            return [
                'page_count' => 1,
                'print_settings' => [
                    'width' => $imageInfo[0] ?? 0,
                    'height' => $imageInfo[1] ?? 0,
                    'recommended_paper_size' => $this->recommendPaperSize($imageInfo[0] ?? 0, $imageInfo[1] ?? 0),
                    'color_detected' => true,
                    'estimated_print_time' => 45 // segundos
                ]
            ];
        } catch (\Exception $e) {
            return ['page_count' => 1];
        }
    }

    /**
     * Analizar documento Word
     */
    private function analyzeDocument($filePath)
    {
        // Simulación - en producción usaría PhpOffice/PhpWord
        $pageCount = rand(1, 15);
        
        return [
            'page_count' => $pageCount,
            'print_settings' => [
                'recommended_paper_size' => 'A4',
                'color_detected' => rand(0, 1) ? true : false,
                'estimated_print_time' => $pageCount * 25
            ]
        ];
    }

    /**
     * Analizar archivo de texto
     */
    private function analyzeText($filePath)
    {
        try {
            $content = file_get_contents($filePath);
            $lines = substr_count($content, "\n") + 1;
            $pageCount = ceil($lines / 50); // Aproximadamente 50 líneas por página
            
            return [
                'page_count' => $pageCount,
                'print_settings' => [
                    'recommended_paper_size' => 'A4',
                    'color_detected' => false,
                    'estimated_print_time' => $pageCount * 20
                ]
            ];
        } catch (\Exception $e) {
            return ['page_count' => 1];
        }
    }

    /**
     * Recomendar tamaño de papel basado en dimensiones de imagen
     */
    private function recommendPaperSize($width, $height)
    {
        $ratio = $width / $height;
        
        if ($ratio > 1.3) {
            return 'A4_landscape';
        } elseif ($ratio < 0.8) {
            return 'A4_portrait';
        } else {
            return 'A4';
        }
    }

    /**
     * Eliminar archivo
     */
    public function deleteFile(File $file)
    {
        try {
            // Eliminar archivo físico
            if (Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }
            
            // Eliminar registro de base de datos
            $file->delete();
            
            Log::info('File deleted successfully', [
                'file_id' => $file->_id,
                'file_path' => $file->file_path
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Error deleting file: ' . $e->getMessage(), [
                'file_id' => $file->_id
            ]);
            
            return false;
        }
    }

    /**
     * Eliminar archivos de una sesión
     */
    public function deleteSessionFiles(Session $session)
    {
        $files = File::where('session_id', $session->_id)->get();
        $deletedCount = 0;
        
        foreach ($files as $file) {
            if ($this->deleteFile($file)) {
                $deletedCount++;
            }
        }
        
        // Eliminar directorio de la sesión si está vacío
        $sessionPath = $this->uploadPath . '/' . $session->session_code;
        if (Storage::disk('public')->exists($sessionPath)) {
            $remainingFiles = Storage::disk('public')->files($sessionPath);
            if (empty($remainingFiles)) {
                Storage::disk('public')->deleteDirectory($sessionPath);
            }
        }
        
        return $deletedCount;
    }

    /**
     * Limpiar archivos antiguos automáticamente
     */
    public function cleanupOldFiles($hoursOld = 24)
    {
        $cutoffTime = Carbon::now()->subHours($hoursOld);
        
        $oldFiles = File::where('uploaded_at', '<', $cutoffTime)
                       ->whereHas('session', function($query) use ($cutoffTime) {
                           $query->where('expires_at', '<', $cutoffTime)
                                 ->orWhere('status', 'expired');
                       })
                       ->get();
        
        $deletedCount = 0;
        
        foreach ($oldFiles as $file) {
            if ($this->deleteFile($file)) {
                $deletedCount++;
            }
        }
        
        Log::info('Old files cleanup completed', [
            'deleted_files' => $deletedCount,
            'cutoff_time' => $cutoffTime
        ]);
        
        return $deletedCount;
    }

    /**
     * Obtener información de archivo para impresión
     */
    public function getFileForPrint(File $file)
    {
        if (!Storage::disk('public')->exists($file->file_path)) {
            throw new \Exception('Archivo no encontrado');
        }
        
        return [
            'file' => $file,
            'full_path' => Storage::disk('public')->path($file->file_path),
            'url' => Storage::disk('public')->url($file->file_path),
            'print_ready' => $file->analysis_completed,
            'estimated_cost' => $this->calculatePrintCost($file)
        ];
    }

    /**
     * Calcular costo estimado de impresión
     */
    private function calculatePrintCost(File $file)
    {
        $baseCostPerPage = 2.00; // Pesos mexicanos
        $colorMultiplier = 1.5;
        
        $pageCount = $file->page_count ?? 1;
        $printSettings = $file->print_settings ?? [];
        
        $cost = $pageCount * $baseCostPerPage;
        
        // Aplicar multiplicador por color
        if (isset($printSettings['color_detected']) && $printSettings['color_detected']) {
            $cost *= $colorMultiplier;
        }
        
        return round($cost, 2);
    }

    /**
     * Obtener estadísticas de archivos
     */
    public function getFileStats($days = 7)
    {
        $startDate = Carbon::now()->subDays($days);
        
        $stats = [
            'total_files' => File::where('uploaded_at', '>=', $startDate)->count(),
            'total_size' => File::where('uploaded_at', '>=', $startDate)->sum('file_size'),
            'by_extension' => File::where('uploaded_at', '>=', $startDate)
                                 ->groupBy('extension')
                                 ->selectRaw('extension, count(*) as count')
                                 ->pluck('count', 'extension'),
            'average_file_size' => File::where('uploaded_at', '>=', $startDate)->avg('file_size'),
            'period_days' => $days
        ];
        
        return $stats;
    }
}