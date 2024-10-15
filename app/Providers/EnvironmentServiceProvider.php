<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class EnvironmentServiceProvider extends ServiceProvider {
    /**
     * Register services.
     */
    public function register(): void {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot() {
        $environment  = app()->environment();
        $database     = config('database.default');
        $databaseName = config("database.connections.$database.database");
        view()->share([
            'env' => $environment,
            'db'  => $databaseName,
        ]);
    }
}
