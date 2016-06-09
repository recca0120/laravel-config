<?php

use Illuminate\Config\Repository;
use Mockery as m;
use Recca0120\Config\Config;
use Recca0120\Config\DatabaseRepository;

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

    public function test_has()
    {
        $config = m::mock(Repository::class)
            ->shouldReceive('has')->andReturn(true)
            ->mock();

        $model = m::mock(Config::class);

        $databaseRepository = new DatabaseRepository($config, $model);

        $this->assertSame($databaseRepository->has('test'), $config->has('test'));
    }

    public function test_get()
    {
        $config = m::mock(Repository::class)
            ->shouldReceive('get')->andReturn(['all'])
            ->mock();

        $model = m::mock(Config::class);

        $databaseRepository = new DatabaseRepository($config, $model);

        $this->assertSame($databaseRepository->get('test'), $config->get('test'));
    }

    public function test_all()
    {
        $config = m::mock(Repository::class)
            ->shouldReceive('all')->andReturn(['all'])
            ->mock();

        $model = m::mock(Config::class);

        $databaseRepository = new DatabaseRepository($config, $model);

        $this->assertSame($databaseRepository->all(), $config->all());
    }

    public function test_set()
    {
        $config = m::mock(Repository::class)
            ->shouldReceive('set')->andReturn(['all'])
            ->mock();

        $model = m::mock(Config::class);

        $databaseRepository = new DatabaseRepository($config, $model);

        $this->assertSame($databaseRepository->set('test'), $config->set('test'));
    }
}
