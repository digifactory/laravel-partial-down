<?php

namespace DigiFactory\PartialDown;

use DigiFactory\PartialDown\Commands\PartialDown;
use DigiFactory\PartialDown\Commands\PartialUp;
use DigiFactory\PartialDown\Middleware\CheckForPartialMaintenanceMode;
use Illuminate\Support\ServiceProvider;

class PartialDownServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                PartialDown::class,
                PartialUp::class,
            ]);
        }

        $this->app->get('router')->aliasMiddleware('partialDown', CheckForPartialMaintenanceMode::class);
    }
}
