<?php

use Illuminate\Database\Capsule\Manager as Connection;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Events\Dispatcher;
use Mockery as m;
use Recca0120\Config\Config;
use Recca0120\Config\Repository;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Eloquent::unguard();
        $connection = new Connection();
        $connection->addConnection([
            'driver'   => 'sqlite',
            'database' => ':memory:',
        ]);
        $connection->bootEloquent();
        $connection->setAsGlobal();

        $this->schema()->create('configs', function ($table) {
            $table->increments('id');
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function tearDown()
    {
        m::close();
        $this->schema()->drop('configs');
    }

    public function testConfigChanged()
    {
        $cacheFactory = new CacheFactory();
        $dispatcher = new Dispatcher();
        $config = new Repository([], null, $cacheFactory, $dispatcher);
        $data = [
            'a' => 'd',
            'b' => 'e',
            'c' => 'f',
        ];
        $config->set($data);
        $this->assertEquals(
            $dispatcher->fire('kernel.handled', ['', ''], true),
            Config::all()->pluck('value', 'key')->toArray()
        );
    }

    /**
     * Schema Helpers.
     */
    protected function schema()
    {
        return $this->connection()->getSchemaBuilder();
    }

    protected function connection()
    {
        return Eloquent::getConnectionResolver()->connection();
    }
}

class CacheFactory implements \Illuminate\Contracts\Cache\Factory
{
    protected $key;

    public function store($name = null)
    {
        return $this;
    }

    public function driver()
    {
        return $this;
    }

    public function rememberForever($key, \Closure $handle)
    {
        $this->key = $key;

        return $handle();
    }

    public function forget($key)
    {
        return $key = $key;
    }
}
