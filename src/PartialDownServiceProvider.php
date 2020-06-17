<?php

namespace DigiFactory\PartialDown;

use DigiFactory\PartialDown\Commands\PartialDown;
use DigiFactory\PartialDown\Commands\PartialUp;
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
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        // $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'partial-down');
    }
}
