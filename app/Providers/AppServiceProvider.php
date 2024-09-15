<?php

namespace App\Providers;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Client::class, function ($app) {
            return new Client([
                'User-Agent' => config('app.user_agent'),
                'timeout' => 5.0,
            ]);
        });
    }

    public function boot(): void
    {
        //
    }
}
