<?php

use Mockery as m;
use Recca0120\Config\ConfigServiceProvider;

class ConfigServiceProviderTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_boot()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $app = m::mock('Illuminate\Contracts\Foundation\Application');
        $config = m::mock('Recca0120\Config\Repositories\DatabaseRepository');
        $kernel = m::mock('Illuminate\Contracts\Http\Kernel');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $kernel
            ->shouldReceive('pushMiddleware')->with('Recca0120\Config\Middleware\SetConfigRepository')->once();

        $app
            ->shouldReceive('singleton')->with('Recca0120\Config\Contracts\Repository', m::type('Closure'))->once()->andReturnUsing(function ($className, $closure) use ($app) {
                $closure($app);
            })
            ->shouldReceive('make')->with('Recca0120\Config\Repositories\DatabaseRepository', m::any())->once()
            ->shouldReceive('databasePath')->once()
            ->shouldReceive('storagePath')->once()->andReturn(__DIR__)
            ->shouldReceive('runningInConsole')->once()->andReturn(true);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $serviceProvider = new ConfigServiceProvider($app);
        $serviceProvider->register();
        $serviceProvider->provides();
        $serviceProvider->boot($kernel);
    }
}