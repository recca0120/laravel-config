<?php

namespace Recca0120\Config\Tests\Middleware;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\Config\Middleware\SwapConfigRepository;

class SwapConfigRepositoryTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function test_middleware()
    {
        $middleware = new SwapConfigRepository(
            $app = m::mock('Illuminate\Contracts\Foundation\Application'),
            $config = m::mock('Recca0120\Config\Contracts\Repository')
        );

        $config->shouldReceive('get')->with('app.timezone')->once()->andReturn('Asia/Taipei');
        $app->shouldReceive('instance')->with('config', $config)->once();

        $request = m::mock('Illuminate\Http\Request');
        $response = m::mock('stdClass');
        $next = function ($request) use ($response) {
            return $response;
        };

        $this->assertSame($response, $middleware->handle($request, $next));
    }
}
