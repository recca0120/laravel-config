<?php

use Illuminate\Config\Repository as RepositoryContract;
use Mockery as m;
use Recca0120\Config\Config;
use Recca0120\Config\Repositories\DatabaseRepository;

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

    public function test_all()
    {
        // $data = [
        //     'a' => [
        //         'b' => 'c',
        //     ],
        // ];
        //
        // $config = m::mock(RepositoryContract::class)
        //     ->shouldReceive('all')->andReturn($data)
        //     ->mock();
        //
        // $model = new Config();
        //
        // $app = $this->createApplication()
        //     ->shouldReceive('storagePath')->andReturn(realpath(__DIR__).'/')
        //     ->mock();
        //
        // $app['events']->shouldReceive('listen')->mock();
        //
        // $databaseRepository = new DatabaseRepository($config, $config);
        // $this->assertSame($databaseRepository->all(), $data);
    }
}
