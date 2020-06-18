<?php

namespace DigiFactory\PartialDown\Commands;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class PartialParts extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'partial-parts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all registered routes';

    /**
     * The router instance.
     *
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    /**
     * The table headers for the command.
     *
     * @var array
     */
    protected $headers = ['Domain', 'Method', 'URI', 'Name', 'Action', 'Middleware'];

    /**
     * The columns to display when using the "compact" flag.
     *
     * @var array
     */
    protected $compactColumns = ['method', 'uri', 'action'];

    /**
     * Create a new route command instance.
     *
     * @param \Illuminate\Routing\Router $router
     * @return void
     */
    public function __construct(Router $router)
    {
        parent::__construct();

        $this->router = $router;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $parts = collect($this->router->getRoutes())->map(function ($route) {
            return [$this->getPartialPartsMiddleware($route)];
        })->filter()->unique();

        if ($parts->count() === 0) {
            $this->error('No parts found!');
        } else {
            $headers = ['Parts in use'];
            $this->table($headers, $parts->toArray());
        }
    }

    /**
     * Get the route information for a given route.
     *
     * @param \Illuminate\Routing\Route $route
     * @return array
     */
    protected function getPartialPartsMiddleware(Route $route)
    {
        return collect($route->gatherMiddleware())->map(function ($middleware) {
            return $middleware instanceof Closure ? 'Closure' : $middleware;
        })->filter(function($middleware) {
            return Str::contains($middleware, 'partialDown');
        })->map(function($middleware) {
            return Str::replaceFirst('partialDown:', '', $middleware);
        })->first();
    }
}
