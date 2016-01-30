<?php

use Illuminate\Cache\CacheManager as BaseCacheManager;
use Mockery as m;
use Recca0120\Config\Config;
use Recca0120\Config\Repository;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $app = App::getInstance();
        $app->migrate('up');
    }

    public function tearDown()
    {
        m::close();
        $app = App::getInstance();
        $app->migrate('down');
    }

    public function test_config_changed()
    {
        $app = App::getInstance();
        $cacheFactory = new CacheManager($app);
        $config = new Repository([], null, $cacheFactory, $app['events']);
        $data = [
            'a' => 'd',
            'b' => 'e',
            'c' => 'f',
        ];
        $config->set($data);
        $this->assertEquals(
            $app['events']->fire('kernel.handled', ['', ''], true),
            Config::all()->pluck('value', 'key')->toArray()
        );
    }
}

class CacheManager extends BaseCacheManager
{
    public function driver($driver = null)
    {
        return $this->createArrayDriver();
    }
}
