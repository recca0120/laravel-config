<?php

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Mockery as m;
use Recca0120\Config\Repositories\DatabaseRepository;
use Recca0120\Config\ServiceProvider;

class ServiceProviderTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testBoot()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $app = m::mock(ApplicationContract::class);
        $config = m::mock(DatabaseRepository::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $app
            ->shouldReceive('databasePath')->once()
            ->shouldReceive('runningInConsole')->once()->andReturn(false)
            ->shouldReceive('booted')->with(m::type(Closure::class))->once()->andReturnUsing(function ($closure) {
                $closure();
            })
            ->shouldReceive('make')->with(DatabaseRepository::class)->andReturn($config)
            ->shouldReceive('instance')->with('config', $config);

        $config->shouldReceive('get')->with('app.timezone')->once()->andReturn('Asia/Taipei');

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $serviceProvider = new ServiceProvider($app);
        $serviceProvider->register();
        $serviceProvider->provides();
        $serviceProvider->boot();
    }

    public function testRunningInConsole()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $app = m::mock(ApplicationContract::class);
        $config = m::mock(DatabaseRepository::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $app
            ->shouldReceive('databasePath')->once()->andReturn(__DIR__)
            ->shouldReceive('runningInConsole')->once()->andReturn(true);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $serviceProvider = new ServiceProvider($app);
        $serviceProvider->register();
        $serviceProvider->provides();
        $serviceProvider->boot();
    }
}
