<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\AdminController;

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

// Página principal
Route::get('/', [HomeController::class, 'index'])->name('home');

// Rutas de páginas informativas
Route::get('/help', [HomeController::class, 'help'])->name('home.help');
Route::get('/terms', [HomeController::class, 'terms'])->name('home.terms');
Route::get('/privacy', [HomeController::class, 'privacy'])->name('home.privacy');

// Rutas de sesiones
Route::prefix('session')->group(function () {
    Route::get('/{sessionCode}', [SessionController::class, 'show'])->name('session.show');
    Route::get('/{sessionCode}/files', [SessionController::class, 'showFiles'])->name('session.files');
    Route::post('/upload', [SessionController::class, 'upload'])->name('session.upload');
    Route::post('/remove-file', [SessionController::class, 'removeFile'])->name('session.remove-file');
    Route::post('/print', [SessionController::class, 'print'])->name('session.print');
});

// Rutas de impresión
Route::prefix('print')->group(function () {
    Route::get('/{sessionCode}/configure', [PrintController::class, 'configure'])->name('print.configure');
    Route::get('/{sessionCode}/confirm', [PrintController::class, 'confirm'])->name('print.confirm');
    Route::get('/{sessionCode}/status', [PrintController::class, 'status'])->name('print.status');
});

// Rutas administrativas
Route::prefix('admin')->group(function () {
    // Login administrativo
    Route::get('/login', [AdminController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'login'])->name('admin.login.post');
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
    
    // Panel administrativo (requiere autenticación)
    Route::middleware('admin.auth')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/printers', [AdminController::class, 'printers'])->name('admin.printers');
        Route::get('/jobs', [AdminController::class, 'jobs'])->name('admin.jobs');
        Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');
        Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
    });
});
