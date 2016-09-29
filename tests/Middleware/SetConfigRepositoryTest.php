<?php

use Mockery as m;
use Recca0120\Config\Middleware\SetConfigRepository;

class SetConfigRepositoryTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_middleware()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $app = m::mock('Illuminate\Contracts\Foundation\Application');
        $config = m::mock('Recca0120\Config\Contracts\Repository');
        $request = m::mock('Illuminate\Http\Request');
        $response = m::mock('stdClass');
        $next = function ($request) use ($response) {
            return $response;
        };
        $middleware = new SetConfigRepository($app, $config);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $config->shouldReceive('get')->with('app.timezone')->once()->andReturn('Asia/Taipei');

        $app->shouldReceive('instance')->with('config', $config)->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($response, $middleware->handle($request, $next));
    }
}
