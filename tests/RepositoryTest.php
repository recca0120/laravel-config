<?php

use Illuminate\Contracts\Config\Repository as RepositoryContract;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Mockery as m;
use Recca0120\Config\Config;
use Recca0120\Config\Repositories\DatabaseRepository;

class RepositoryTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testRepository()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $configRepository = m::mock(RepositoryContract::class);
        $app = m::mock(ApplicationContract::class);
        $model = m::mock(Config::class);
        $data = [
            'a'  => 'b',
            'a1' => ['b'],
        ];

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $configRepository
            ->shouldReceive('all')->andReturnUsing(function () use (&$data) {
                return $data;
            })
            ->shouldReceive('set')->with(m::any(), m::any())->andReturnUsing(function ($key, $value) use (&$data) {
                $data[$key] = $value;
            })
            ->shouldReceive('has')->andReturnUsing(function ($key) use (&$data) {
                return isset($data[$key]);
            })
            ->shouldReceive('get')->andReturnUsing(function ($key) use (&$data) {
                return $data[$key];
            })
            ->shouldReceive('prepend')->andReturnUsing(function ($key, $value) use (&$data) {
                return $data = array_merge([$key => $value], $data);
            })
            ->shouldReceive('push')->andReturnUsing(function ($key, $value) use (&$data) {
                return $data = array_merge($data, [$key => $value]);
            })
            ->mock();

        $app
            ->shouldReceive('storagePath')->andReturn(__DIR__)
            ->mock();

        $model
            ->shouldReceive('firstOrCreate')->andReturnSelf()
            ->shouldReceive('getAttribute')->with('value')->andReturn(['c' => 'd'])
            ->shouldReceive('setAttribute')->andReturn([])
            ->shouldReceive('fill')->andReturnSelf()
            ->shouldReceive('save')->andReturn(true)
            ->mock();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $config = new DatabaseRepository($configRepository, $model, $app);
        $config->set('c', 'd');
        $config->offsetSet('c', 'd');
        $this->assertTrue($config->has('a'));
        $this->assertTrue($config->offsetExists('a'));
        $this->assertSame($config->offsetGet('a'), 'b');
        $this->assertSame($config->get('a'), 'b');
        $config->prepend('e', 'f');
        $config->push('h', 'i');
        $config->set('a', ['b']);
        $config->set('a1', ['d']);
        $config->offsetUnset('a1');

        $this->assertSame($config->all(), $data);

        $config
            ->needUpdate(false)
            ->offsetUnset('g');

        $config2 = new DatabaseRepository($configRepository, $model, $app);

        @unlink(__DIR__.'/config.json');
    }
}
