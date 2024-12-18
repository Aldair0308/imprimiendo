<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function imprimir(Request $request)
    {
        // Validar el archivo PDF y opciones
        $request->validate([
            'archivo_pdf' => 'required|file|mimes:pdf|max:10240',
            'modo' => 'required|string|in:color,bn',
            'paginas' => 'required|string|in:todas,pares,impares,rango',
            'rango_paginas' => 'nullable|string|regex:/^\d+-\d+$/',
        ]);

        $pdf = $request->file('archivo_pdf');
        $filePath = $pdf->getRealPath();
        $modo = $request->input('modo');
        $paginas = $request->input('paginas');
        $rango = $request->input('rango_paginas');

        try {
            $printerCommand = "";

            // Configurar el comando base para impresión
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $printerCommand = "print {$filePath}";
            } else {
                $printerCommand = "lp {$filePath}";
            }

            // Opciones de impresión
            if ($modo === 'bn') {
                $printerCommand .= " -o grayscale";
            }
            if ($paginas === 'pares') {
                $printerCommand .= " -o page-ranges=even";
            } elseif ($paginas === 'impares') {
                $printerCommand .= " -o page-ranges=odd";
            } elseif ($paginas === 'rango' && $rango) {
                $printerCommand .= " -o page-ranges={$rango}";
            }

            // Ejecutar el comando
            exec($printerCommand, $output, $returnVar);

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
