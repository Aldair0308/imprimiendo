<?php

namespace App\Http\Controllers;

use App\Services\FileService;
use App\Services\SessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    private $fileService;
    private $sessionService;
    
    public function __construct(
        FileService $fileService,
        SessionService $sessionService
    ) {
        $this->fileService = $fileService;
        $this->sessionService = $sessionService;
    }

    /**
     * Subir archivo a una sesión
     */
    public function upload(Request $request, $sessionCode)
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,txt'
        ]);
        
        try {
            $session = $this->sessionService->getSessionByCode($sessionCode);
            
            // Verificar límites de la sesión
            $limits = $this->sessionService->checkSessionLimits($session, $request->file('file')->getSize());
            
            if (!$limits['can_add_file']) {
                throw new \Exception($limits['file_limit_message']);
            }
            
            if (!$limits['can_add_size']) {
                throw new \Exception($limits['size_limit_message']);
            }
            
            // Subir archivo
            $file = $this->fileService->uploadFile($request->file('file'), $session);
            
            // Actualizar estado de sesión
            $this->sessionService->updateSessionStatus($session, 'uploading');
            
            return response()->json([
                'success' => true,
                'message' => 'Archivo subido correctamente',
                'file' => [
                    'id' => $file->_id,
                    'name' => $file->original_name,
                    'size' => $file->file_size,
                    'pages' => $file->page_count ?? 1,
                    'type' => $file->extension,
                    'uploaded_at' => $file->uploaded_at->format('Y-m-d H:i:s'),
                    'analysis_completed' => $file->analysis_completed
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error uploading file: ' . $e->getMessage(), [
                'session_code' => $sessionCode
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Subir múltiples archivos
     */
    public function uploadMultiple(Request $request, $sessionCode)
    {
        $request->validate([
            'files' => 'required|array|max:5',
            'files.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,txt'
        ]);
        
        try {
            $session = $this->sessionService->getSessionByCode($sessionCode);
            $uploadedFiles = [];
            $errors = [];
            
            foreach ($request->file('files') as $uploadedFile) {
                try {
                    // Verificar límites para cada archivo
                    $limits = $this->sessionService->checkSessionLimits($session, $uploadedFile->getSize());
                    
                    if (!$limits['can_add_file'] || !$limits['can_add_size']) {
                        $errors[] = "Archivo {$uploadedFile->getClientOriginalName()}: Límites excedidos";
                        continue;
                    }
                    
                    $file = $this->fileService->uploadFile($uploadedFile, $session);
                    $uploadedFiles[] = [
                        'id' => $file->_id,
                        'name' => $file->original_name,
                        'size' => $file->file_size,
                        'pages' => $file->page_count ?? 1,
                        'type' => $file->extension
                    ];
                    
                } catch (\Exception $e) {
                    $errors[] = "Archivo {$uploadedFile->getClientOriginalName()}: {$e->getMessage()}";
                }
            }
            
            // Actualizar estado de sesión si se subió al menos un archivo
            if (!empty($uploadedFiles)) {
                $this->sessionService->updateSessionStatus($session, 'uploading');
            }
            
            return response()->json([
                'success' => !empty($uploadedFiles),
                'message' => count($uploadedFiles) . ' archivo(s) subido(s) correctamente',
                'files' => $uploadedFiles,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Eliminar archivo de una sesión
     */
    public function delete($sessionCode, $fileId)
    {
        try {
            $session = $this->sessionService->getSessionByCode($sessionCode);
            $file = \App\Models\File::where('_id', $fileId)
                                   ->where('session_id', $session->_id)
                                   ->firstOrFail();
            
            $deleted = $this->fileService->deleteFile($file);
            
            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Archivo eliminado correctamente'
                ]);
            } else {
                throw new \Exception('No se pudo eliminar el archivo');
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Obtener información de un archivo
     */
    public function getFileInfo($sessionCode, $fileId)
    {
        try {
            $session = $this->sessionService->getSessionByCode($sessionCode);
            $file = \App\Models\File::where('_id', $fileId)
                                   ->where('session_id', $session->_id)
                                   ->firstOrFail();
            
            $fileInfo = $this->fileService->getFileForPrint($file);
            
            return response()->json([
                'success' => true,
                'file' => [
                    'id' => $file->_id,
                    'name' => $file->original_name,
                    'size' => $file->file_size,
                    'pages' => $file->page_count ?? 1,
                    'type' => $file->extension,
                    'mime_type' => $file->mime_type,
                    'uploaded_at' => $file->uploaded_at->format('Y-m-d H:i:s'),
                    'analysis_completed' => $file->analysis_completed,
                    'print_settings' => $file->print_settings ?? [],
                    'estimated_cost' => $fileInfo['estimated_cost'],
                    'print_ready' => $fileInfo['print_ready']
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Archivo no encontrado'
            ], 404);
        }
    }

    /**
     * Descargar archivo
     */
    public function download($sessionCode, $fileId)
    {
        try {
            $session = $this->sessionService->getSessionByCode($sessionCode);
            $file = \App\Models\File::where('_id', $fileId)
                                   ->where('session_id', $session->_id)
                                   ->firstOrFail();
            
            if (!Storage::disk('public')->exists($file->file_path)) {
                throw new \Exception('Archivo no encontrado en el servidor');
            }
            
            return Storage::disk('public')->download($file->file_path, $file->original_name);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Vista previa de archivo (para imágenes y PDFs)
     */
    public function preview($sessionCode, $fileId)
    {
        try {
            $session = $this->sessionService->getSessionByCode($sessionCode);
            $file = \App\Models\File::where('_id', $fileId)
                                   ->where('session_id', $session->_id)
                                   ->firstOrFail();
            
            if (!Storage::disk('public')->exists($file->file_path)) {
                throw new \Exception('Archivo no encontrado');
            }
            
            // Solo permitir vista previa para ciertos tipos de archivo
            $previewableTypes = ['jpg', 'jpeg', 'png', 'pdf'];
            if (!in_array(strtolower($file->extension), $previewableTypes)) {
                throw new \Exception('Vista previa no disponible para este tipo de archivo');
            }
            
            $fileUrl = Storage::disk('public')->url($file->file_path);
            
            return response()->json([
                'success' => true,
                'preview_url' => $fileUrl,
                'file_type' => $file->extension,
                'file_name' => $file->original_name
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Obtener todos los archivos de una sesión
     */
    public function getSessionFiles($sessionCode)
    {
        try {
            $session = $this->sessionService->getSessionByCode($sessionCode);
            $files = $this->sessionService->getSessionFiles($session);
            
            $filesData = $files->map(function($file) {
                return [
                    'id' => $file->_id,
                    'name' => $file->original_name,
                    'size' => $file->file_size,
                    'size_formatted' => $this->formatFileSize($file->file_size),
                    'pages' => $file->page_count ?? 1,
                    'type' => $file->extension,
                    'mime_type' => $file->mime_type,
                    'uploaded_at' => $file->uploaded_at->format('Y-m-d H:i:s'),
                    'analysis_completed' => $file->analysis_completed,
                    'print_settings' => $file->print_settings ?? []
                ];
            });
            
            return response()->json([
                'success' => true,
                'files' => $filesData,
                'total_files' => $files->count(),
                'total_size' => $files->sum('file_size'),
                'total_pages' => $files->sum('page_count')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Validar archivos antes de subir
     */
    public function validateFiles(Request $request, $sessionCode)
    {
        try {
            $session = $this->sessionService->getSessionByCode($sessionCode);
            $files = $request->input('files', []);
            
            $validationResults = [];
            
            foreach ($files as $fileInfo) {
                $result = [
                    'name' => $fileInfo['name'],
                    'size' => $fileInfo['size'],
                    'valid' => true,
                    'errors' => []
                ];
                
                // Verificar tamaño
                if ($fileInfo['size'] > 10 * 1024 * 1024) {
                    $result['valid'] = false;
                    $result['errors'][] = 'Archivo demasiado grande (máximo 10MB)';
                }
                
                // Verificar extensión
                $extension = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);
                $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt'];
                if (!in_array(strtolower($extension), $allowedExtensions)) {
                    $result['valid'] = false;
                    $result['errors'][] = 'Tipo de archivo no permitido';
                }
                
                // Verificar límites de sesión
                $limits = $this->sessionService->checkSessionLimits($session, $fileInfo['size']);
                if (!$limits['can_add_file']) {
                    $result['valid'] = false;
                    $result['errors'][] = 'Límite de archivos excedido';
                }
                if (!$limits['can_add_size']) {
                    $result['valid'] = false;
                    $result['errors'][] = 'Límite de tamaño excedido';
                }
                
                $validationResults[] = $result;
            }
            
            return response()->json([
                'success' => true,
                'validation_results' => $validationResults,
                'all_valid' => collect($validationResults)->every('valid')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Formatear tamaño de archivo
     */
    private function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}