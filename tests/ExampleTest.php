<?php

namespace DigiFactory\PartialDown\Tests;

use Orchestra\Testbench\TestCase;
use DigiFactory\PartialDown\PartialDownServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [PartialDownServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
