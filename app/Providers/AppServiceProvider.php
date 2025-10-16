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
        // Registrar servicios sin dependencias circulares
        $this->app->singleton('App\Services\PrinterMonitorService', function ($app) {
            return new \App\Services\PrinterMonitorService();
        });
        
        $this->app->singleton('App\Services\LoadBalancerService', function ($app) {
            return new \App\Services\LoadBalancerService();
        });
        
        $this->app->singleton('App\Services\MultiPrinterService', function ($app) {
            return new \App\Services\MultiPrinterService(
                $app->make('App\Services\LoadBalancerService'),
                $app->make('App\Services\PrinterMonitorService')
            );
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
