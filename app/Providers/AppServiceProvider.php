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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //Configuracion para despliegue en Render.com (peristencia de db)
        if (app()->environment('production')) {
            $sqlitePath = database_path('database.sqlite');
            $tempPath = '/tmp/database.sqlite';
    
            if (!file_exists($tempPath) && file_exists($sqlitePath)) {
                copy($sqlitePath, $tempPath);
            }
    
            config(['database.connections.sqlite.database' => $tempPath]);
        }
    }
}
