<?php

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel;
use Mockery as m;
use Recca0120\Config\Contracts\Repository;
use Recca0120\Config\Middleware\SetConfigRepository;
use Recca0120\Config\Repositories\DatabaseRepository;
use Recca0120\Config\ServiceProvider;

class ServiceProviderTest extends PHPUnit_Framework_TestCase
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

        $app = m::mock(Application::class);
        $config = m::mock(DatabaseRepository::class);
        $kernel = m::mock(Kernel::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $kernel->shouldReceive('pushMiddleware')->with(SetConfigRepository::class)->once();

        $app
            ->shouldReceive('singleton')->with(Repository::class, DatabaseRepository::class)->once()
            ->shouldReceive('databasePath')->once()
            ->shouldReceive('runningInConsole')->once()->andReturn(false);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $serviceProvider = new ServiceProvider($app);
        $serviceProvider->register();
        $serviceProvider->provides();
        $serviceProvider->boot($kernel);
    }

    public function test_running_in_console()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $app = m::mock(Application::class);
        $config = m::mock(DatabaseRepository::class);
        $kernel = m::mock(Kernel::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $app
            ->shouldReceive('singleton')->with(Repository::class, DatabaseRepository::class)->once()
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
        $serviceProvider->boot($kernel);
    }
}
