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
        // Bind local catalogue service for use in Livewire components
        $this->app->singleton('localCatalogue', function () {
            return new \App\Services\LocalCatalogueService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enable error logging to a dedicated file for debugging layout/content issues
        if (!is_writable(__DIR__)) {
            // avoid failing boot if permission issue
        }
        ini_set('display_errors', '1');
        ini_set('log_errors', '1');
        $logPath = __DIR__ . '/../../log/php_errors.log';
        $logDir = dirname($logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        ini_set('error_log', $logPath);
        error_log('[DEBUG] AppServiceProvider boot: PHP error logging configured to ' . $logPath);
    }
}
