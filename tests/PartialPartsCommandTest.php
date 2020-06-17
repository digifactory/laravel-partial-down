<?php

namespace DigiFactory\PartialDown\Tests;

use DigiFactory\PartialDown\Commands\PartialParts;
use DigiFactory\PartialDown\Middleware\CheckForPartialMaintenanceMode;
use DigiFactory\PartialDown\PartialDownServiceProvider;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Http\Exceptions\MaintenanceModeException;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Mockery as m;
use Orchestra\Testbench\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

class PartialPartsCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockConsoleOutput = false;
    }

    public function testPartialPartCommandsReturnsNoParts()
    {
        $this->artisan('partial-parts')
            ->expectsOutput('No parts found!')
            ->assertExitCode(0);
    }

    public function testPartialPartCommandsReturnsParts()
    {
        $this->setUpRoutes();

        $kernel = $this->app->make(Kernel::class);

        $kernel->handle(
            $input = new ArrayInput(['command' => 'partial-parts']),
            $outputBuffer = new BufferedOutput()
        );

        $output = $outputBuffer->fetch();

        $this->assertStringContainsString('backend', $output);
        $this->assertStringContainsString('frontend', $output);
        $this->assertStringNotContainsString('No parts found!', $output);
    }

    protected function setUpRoutes()
    {
        /** @var Router $router */
        $router = $this->app->get('router');

        $router->get('test-1')->middleware('partialDown:backend');
        $router->get('test-2')->middleware(['partialDown:frontend']);
    }

    protected function getPackageProviders($app)
    {
        return [
            PartialDownServiceProvider::class,
        ];
    }
}
