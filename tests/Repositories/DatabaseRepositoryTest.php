<?php

namespace Recca0120\Config\Tests\Repositories;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\Config\Repositories\DatabaseRepository;

class DatabaseRepositoryTest extends TestCase
{
    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function testRepository()
    {
        $originalRepository = m::mock('Illuminate\Contracts\Config\Repository');
        $model = m::mock('Recca0120\Config\Config');
        $files = m::mock('Illuminate\Filesystem\Filesystem');
        $store = [];
        $data = [
            'a' => 'b',
            'a1' => ['b'],
        ];

        $originalRepository->shouldReceive('all')->andReturnUsing(function () use (&$data) {
            return $data;
        });

        $files->shouldReceive('exists')->with('config.json')->andReturn(true);
        $files->shouldReceive('get')->with('config.json')->andReturn('[]');

        $originalRepository->shouldReceive('set')->with(m::any(), m::any())->andReturnUsing(function ($key, $value) use (&$data) {
            $data[$key] = $value;
        });

        $model->shouldReceive('firstOrCreate')->andReturnSelf();

        $repository = new DatabaseRepository(
            $originalRepository,
            $model,
            $files,
            $config = [
            'protected' => [
                'auth.defaults.guard',
            ],
            'cache' => 'config.json',
        ]);

        $model->shouldReceive('fill')->andReturnSelf();
        $model->shouldReceive('save')->andReturn(true);
        $files->shouldReceive('put')->with('config.json', m::type('string'))->andReturnUsing(function ($filename, $data) use (&$store) {
            $store = json_decode($data, true);
        });

        $repository->set('c', 'd');
        $repository->offsetSet('c', 'd');

        $originalRepository->shouldReceive('has')->andReturnUsing(function ($key) use (&$data) {
            return isset($data[$key]);
        });
        $this->assertTrue($repository->has('a'));
        $this->assertTrue($repository->offsetExists('a'));

        $originalRepository->shouldReceive('get')->andReturnUsing(function ($key) use (&$data) {
            return $data[$key];
        });
        $this->assertSame($repository->offsetGet('a'), 'b');
        $this->assertSame($repository->get('a'), 'b');

        $originalRepository->shouldReceive('prepend')->andReturnUsing(function ($key, $value) use (&$data) {
            return $data = array_merge([$key => $value], $data);
        });
        $repository->prepend('e', 'f');

        $originalRepository->shouldReceive('push')->andReturnUsing(function ($key, $value) use (&$data) {
            return $data = array_merge($data, [$key => $value]);
        });
        $repository->push('h', 'i');

        $repository->set('a', ['b']);
        $repository->set('a1', ['d']);
        $repository->offsetUnset('a1');

        $repository->set('auth.defaults.guard', true);
        $this->assertSame($repository->all(), $data);
        $this->assertArrayNotHasKey('auth.defaults.guard', $store);
    }
}
