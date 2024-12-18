<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function imprimir(Request $request)
    {
        // Validar el archivo PDF
        $request->validate([
            'archivo_pdf' => 'required|file|mimes:pdf|max:10240',
        ]);

        // Obtener el archivo PDF temporal
        $pdf = $request->file('archivo_pdf');
        $filePath = $pdf->getRealPath(); // Ruta temporal del archivo
        $printerName = "NombreImpresora"; // Configura el nombre de tu impresora

        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Comando para Windows
                exec("print /d:\\\\{$printerName} {$filePath}", $output, $returnVar);
            } else {
                // Comando para Linux/macOS
                exec("lp -d {$printerName} {$filePath}", $output, $returnVar);
            }

            if ($returnVar === 0) {
                return back()->with('success', 'El PDF se ha enviado a la impresora correctamente.');
            } else {
                return back()->with('error', 'Error al imprimir el PDF.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error al imprimir el PDF: ' . $e->getMessage());
        }
    }
}
