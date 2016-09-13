<?php

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Mockery as m;
use Recca0120\Config\Contracts\Repository;
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

        $app = m::mock(Application::class);
        $config = m::mock(Repository::class);
        $request = m::mock(Request::class);
        $response = m::mock(stdClass::class);
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
