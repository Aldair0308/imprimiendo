<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Deshabilitar servicios costosos temporalmente
        $this->app->bind('App\Services\PrinterMonitorService', function () {
            return new class extends \App\Services\PrinterMonitorService {
                public function __construct() {
                    // No llamar al constructor padre para evitar dependencias
                }
                
                public function checkPrinterStatus($printer) {
                    return ['status' => 'online', 'queue_count' => 0];
                }
                
                public function monitorAllPrinters() {
                    return [
                        'total_printers' => 0,
                        'online_printers' => 0,
                        'offline_printers' => 0,
                        'system_load' => 0
                    ];
                }
                
                public function getSystemStatus() {
                    return [
                        'total_printers' => 0,
                        'online_printers' => 0,
                        'offline_printers' => 0,
                        'system_load' => 0,
                        'last_check' => now()
                    ];
                }
            };
        });
        
        $this->app->bind('App\Services\MultiPrinterService', function () {
            return new class extends \App\Services\MultiPrinterService {
                public function __construct() {
                    // No llamar al constructor padre para evitar dependencias
                }
                
                public function getAvailablePrinters() {
                    return [];
                }
                
                public function getSystemStats() {
                    return [
                        'total_printers' => 0,
                        'active_printers' => 0,
                        'total_jobs' => 0,
                        'pending_jobs' => 0,
                        'completed_jobs' => 0,
                        'failed_jobs' => 0
                    ];
                }
                
                public function processJobQueue() {
                    return true;
                }
                
                public function getLoadDistribution() {
                    return [];
                }
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Aumentar límite de memoria si está configurado en .env
        if (env('PHP_MEMORY_LIMIT')) {
            ini_set('memory_limit', env('PHP_MEMORY_LIMIT'));
        }
        
        // Deshabilitar eager loading para reducir memoria
        \Illuminate\Database\Eloquent\Model::preventLazyLoading(false);
    }
}
