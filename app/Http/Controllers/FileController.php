<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FileController extends Controller
{
    public function index()
    {
        return view('files.index');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:20480', // Asegura que el archivo sea PDF y no exceda los 20 MB
            'status' => 'required|string|in:active,inactive,archived',
            'color' => 'required|string|in:color,black-and-white',
            'copies' => 'required|integer|min:1',
            'session' => 'required|integer',
            'price' => 'required|numeric|min:0',
        ]);

        // Procesa el archivo
        $file = $request->file('file');
        $fileBase64 = base64_encode(file_get_contents($file->getPathname())); // Convierte el archivo a Base64

        // Construye los datos a enviar
        $fileData = [
            'id_v' => random_int(1, 1000), // Genera un ID único para este archivo
            'status' => $request->status,
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'data' => $fileBase64, // Contenido del archivo en Base64
            'pages' => [1, 2, 3, 4], // Esto puede ajustarse según tu lógica
            'color' => $request->color,
            'copies' => $request->copies,
            'price' => $request->price,
            'session' => $request->session,
        ];

        // Envía los datos a tu API
        $response = Http::post('http://192.168.100.169:3000/files', $fileData);

        // Verifica la respuesta de la API
        if ($response->successful()) {
            $responseData = $response->json();
            return back()->with('success', 'Archivo registrado: ' . $responseData['name']);
        } else {
            return back()->withErrors(['error' => 'Error al registrar el archivo.']);
        }
    }
}
