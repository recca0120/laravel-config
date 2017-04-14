<?php

namespace Recca0120\Config\Tests;

use stdClass;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\Config\ConfigServiceProvider;
use Recca0120\Config\Repositories\DatabaseRepository;

class ConfigServiceProviderTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testRegister()
    {
        $serviceProvider = new ConfigServiceProvider(
            $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess')
        );

        $app->shouldReceive('singleton')->once()->with('Recca0120\Config\Contracts\Repository', m::on(function($closure) use ($app) {
            $app->shouldReceive('storagePath')->once()->andReturn(__DIR__);
            $app->shouldReceive('offsetGet')->once()->with('config')->andReturn(
                $config = m::mock('Illuminate\Contracts\Config\Repository')
            );
            $app->shouldReceive('make')->once()->with('Recca0120\Config\Config')->andReturn(
                $model = m::mock('Recca0120\Config\Config')
            );
            $app->shouldReceive('offsetGet')->once()->with('files')->andReturn(
                $files = m::mock('Illuminate\Filesystem\Filesystem')
            );

            $object = new stdClass;
            $object->value = [];
            $config->shouldReceive('all')->once()->andReturn([]);
            $files->shouldReceive('exists')->once()->andReturn(false);
            $model->shouldReceive('firstOrCreate')->andReturn($object);
            $files->shouldReceive('put')->once();

            return $closure($app) instanceof DatabaseRepository;
        }));

        $serviceProvider->register();
        $this->assertSame(['config'], $serviceProvider->provides());
    }

    public function testBoot()
    {
        $serviceProvider = new ConfigServiceProvider(
            $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess')
        );

        $app->shouldReceive('runningInConsole')->once()->andReturn(true);
        $app->shouldReceive('databasePath')->once()->andReturn(__DIR__);

        $kernel = m::mock('\Illuminate\Contracts\Http\Kernel');
        $kernel->shouldReceive('pushMiddleware')->once()->with('Recca0120\Config\Middleware\SwapConfigRepository');

        $serviceProvider->boot($kernel);
    }
}
