<?php

use Mockery as m;
use Recca0120\Config\Repositories\DatabaseRepository;

class DatabaseRepositoryTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_repository()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $originalRepository = m::mock('Illuminate\Contracts\Config\Repository');
        $model = m::mock('Recca0120\Config\Config');
        $filesystem = m::mock('Illuminate\Filesystem\Filesystem');
        $data = [
            'a' => 'b',
            'a1' => ['b'],
        ];

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $originalRepository
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
            });

        $model
            ->shouldReceive('firstOrCreate')->andReturnSelf()
            ->shouldReceive('getAttribute')->with('value')->andReturn(['c' => 'd'])
            ->shouldReceive('setAttribute')->andReturn([])
            ->shouldReceive('fill')->andReturnSelf()
            ->shouldReceive('save')->andReturn(true);

        $filesystem
            ->shouldReceive('exists')->with('config.json')->andReturn(true)
            ->shouldReceive('get')->with('config.json')->andReturn('[]')
            ->shouldReceive('put')->with('config.json', m::type('string'));

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $repository = new DatabaseRepository($originalRepository, $model, $filesystem);
        $repository->set('c', 'd');
        $repository->offsetSet('c', 'd');
        $this->assertTrue($repository->has('a'));
        $this->assertTrue($repository->offsetExists('a'));
        $this->assertSame($repository->offsetGet('a'), 'b');
        $this->assertSame($repository->get('a'), 'b');
        $repository->prepend('e', 'f');
        $repository->push('h', 'i');
        $repository->set('a', ['b']);
        $repository->set('a1', ['d']);
        $repository->offsetUnset('a1');

        $this->assertSame($repository->all(), $data);

        $repository
            ->needUpdate(false)
            ->offsetUnset('g');

        $repository2 = new DatabaseRepository($originalRepository, $model, $filesystem);
    }
}
