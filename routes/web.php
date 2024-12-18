<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::post('/imprimir-pdf', [PdfController::class, 'imprimir'])->name('pdf.imprimir');


Route::get('/', function () {
    return view('welcome');
});

Route::post('/generar-qr', function (\Illuminate\Http\Request $request) {
    $texto = $request->input('texto');

    // Generar QR usando Node.js directamente desde PHP
    $qrCode = shell_exec("node -e \"const QRCode = require('qrcode'); QRCode.toDataURL('$texto', { width: 1000,  }).then(url => console.log(url)).catch(err => console.error(err));\"");

    return view('welcome', ['qrCode' => trim($qrCode), 'iconPath' => asset('images/icon.webp')]);
})->name('generar.qr');
