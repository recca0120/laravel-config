<?php

use Mockery as m;
use Recca0120\Config\ConfigServiceProvider;

class ConfigServiceProviderTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_register()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $app = m::spy('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $config = m::spy('Illuminate\Contracts\Config\Repository');
        $filesystem = m::spy('Illuminate\Filesystem\Filesystem');
        $model = m::spy('Recca0120\Config\Config');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $model
            ->shouldReceive('firstOrCreate')->andReturnSelf();

        $app
            ->shouldReceive('offsetGet')->with('config')->andReturn($config)
            ->shouldReceive('offsetGet')->with('files')->andReturn($filesystem)
            ->shouldReceive('make')->with('Recca0120\Config\Config')->andReturn($model)
            ->shouldReceive('singleton')->with('Recca0120\Config\Contracts\Repository', m::type('Closure'))->andReturnUsing(function ($className, $closure) use ($app) {
                return $closure($app);
            });

        $serviceProvider = new ConfigServiceProvider($app);
        $serviceProvider->register();

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $app->shouldHaveReceived('offsetGet')->with('config')->once();
        $app->shouldHaveReceived('offsetGet')->with('files')->once();
        $app->shouldHaveReceived('make')->with('Recca0120\Config\Config')->once();
        $app->shouldHaveReceived('singleton')->with('Recca0120\Config\Contracts\Repository', m::type('Closure'))->once();
    }

    public function test_boot()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $app = m::spy('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $kernel = m::spy('Illuminate\Contracts\Http\Kernel');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $app
            ->shouldReceive('runningInConsole')->andReturn(true);

        $serviceProvider = new ConfigServiceProvider($app);
        $serviceProvider->boot($kernel);
        $serviceProvider->provides();

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $kernel->shouldHaveReceived('pushMiddleware')->with('Recca0120\Config\Middleware\SetConfigRepository')->once();
    }
}
