<?php

use Mockery as m;
use Recca0120\Config\Config;
use Recca0120\Config\Repository;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    use Laravel;

    public function setUp()
    {
        $this->migrate('up');
    }

    public function tearDown()
    {
        m::close();
        $this->migrate('down');
        $this->destroyApplication();
    }

    public function test_config_changed()
    {
        $app = $this->createApplication();
        $app['events'] = $app['events']
            ->shouldReceive('listen')
            ->mock();

        $data = [
            'a' => 'a',
            'b' => 'b',
            'c' => 'c',
            'd' => 'd',
        ];

        $config = m::mock('\Illuminate\Contracts\Config\Repository');
        $cacheFactory = m::mock('\Illuminate\Contracts\Cache\Factory')
            ->shouldReceive('driver')->with('file')->andReturnSelf()
            ->shouldReceive('rememberForever')->andReturn([])
            ->mock();

        $config = new Repository([], null, $cacheFactory, $app['events']);

        $config->set($data);
        $config->onKernelHandled();

        $this->assertEquals(
            Config::all()->pluck('value', 'key')->toArray(),
            $data
        );
    }
}
