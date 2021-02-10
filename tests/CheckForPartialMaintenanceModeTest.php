<?php

namespace DigiFactory\PartialDown\Tests;

use DigiFactory\PartialDown\Middleware\CheckForPartialMaintenanceMode;
use DigiFactory\PartialDown\PartialDownServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Http\Exceptions\MaintenanceModeException;
use Illuminate\Http\Request;
use Mockery as m;
use Orchestra\Testbench\TestCase;

class CheckForPartialMaintenanceModeTest extends TestCase
{
    /**
     * @var string
     */
    protected $part = 'backend';

    /**
     * @var string
     */
    protected $storagePath;

    /**
     * @var string
     */
    protected $downFilePath;

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    public function testApplicationIsRunningNormally()
    {
        $middleware = $this->app->get(CheckForPartialMaintenanceMode::class);

        $result = $middleware->handle(Request::create('/'), function ($request) {
            return 'Running normally.';
        }, $this->part);

        $this->assertSame('Running normally.', $result);
    }

    public function testApplicationAllowsSomeIPs()
    {
        $ips = ['127.0.0.1', '2001:0db8:85a3:0000:0000:8a2e:0370:7334'];

        $this->makeDownFile($ips);

        // Check IPv4.
        $middleware = $this->app->get(CheckForPartialMaintenanceMode::class);

        $request = m::mock(Request::class);
        $request->shouldReceive('ip')->once()->andReturn('127.0.0.1');

        $result = $middleware->handle($request, function ($request) {
            return 'Allowing [127.0.0.1]';
        }, $this->part);

        $this->assertSame('Allowing [127.0.0.1]', $result);

        // Check IPv6.
        $middleware = $this->app->get(CheckForPartialMaintenanceMode::class);

        $request = m::mock(Request::class);
        $request->shouldReceive('ip')->once()->andReturn('2001:0db8:85a3:0000:0000:8a2e:0370:7334');

        $result = $middleware->handle($request, function ($request) {
            return 'Allowing [2001:0db8:85a3:0000:0000:8a2e:0370:7334]';
        }, $this->part);

        $this->assertSame('Allowing [2001:0db8:85a3:0000:0000:8a2e:0370:7334]', $result);
    }

    /**
     * Make a down file with the given allowed ips.
     */
    protected function makeDownFile($ips = null)
    {
        $data = [
            'time' => time(),
            'retry' => 86400,
            'message' => 'This application is down for maintenance.',
        ];

        if ($ips !== null) {
            $data['allowed'] = $ips;
        }

        $this->files->put($this->downFilePath, json_encode($data, JSON_PRETTY_PRINT));

        return $data;
    }

    public function testApplicationDeniesSomeIPs()
    {
        $this->makeDownFile(null);

        $this->expectException(MaintenanceModeException::class);
        $this->expectExceptionMessage('This application is down for maintenance.');

        $middleware = $this->app->get(CheckForPartialMaintenanceMode::class);

        $middleware->handle(Request::create('/'), function ($request) {
            //
        }, $this->part);
    }

    public function testPartialDownCommands()
    {
        $this->artisan('partial-down '.$this->part.' --message="This application is down for maintenance."')
            ->expectsOutput('This part ['.$this->part.'] of the application is now in maintenance mode.')
            ->assertExitCode(0);

        $this->expectException(MaintenanceModeException::class);
        $this->expectExceptionMessage('This application is down for maintenance.');

        $middleware = $this->app->get(CheckForPartialMaintenanceMode::class);

        $middleware->handle(Request::create('/'), function ($request) {
            //
        }, $this->part);

        $this->artisan('partial-up '.$this->part)
            ->expectsOutput('This part ['.$this->part.'] of the application is now live.')
            ->assertExitCode(0);

        $middleware = $this->app->get(CheckForPartialMaintenanceMode::class);

        $result = $middleware->handle(Request::create('/'), function ($request) {
            return 'ok';
        }, $this->part);

        $this->assertEquals('ok', $result);
    }

    protected function getPackageProviders($app)
    {
        return [
            PartialDownServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['path.storage'] = $this->storagePath;
    }

    protected function setUp(): void
    {
        if (is_null($this->files)) {
            $this->files = new Filesystem;
        }

        $this->storagePath = __DIR__.'/tmp';
        $this->downFilePath = $this->storagePath.'/framework/partial-down-'.$this->part;

        $this->files->makeDirectory($this->storagePath.'/framework', 0755, true);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->files->deleteDirectory($this->storagePath);

        m::close();
    }
}
