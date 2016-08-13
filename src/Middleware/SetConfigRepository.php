<?php

namespace Recca0120\Config\Middleware;

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Recca0120\Config\Contracts\Repository as RepositoryContract;

class SetConfigRepository
{
    /**
     * $app.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * $config.
     *
     * @var \Recca0120\Config\Contracts\Repository
     */
    protected $config;

    /**
     * __construct.
     *
     * @method __construct
     *
     * @param \Illuminate\Contracts\Foundation\Application      $app
     * @param \Recca0120\Config\Contracts\Repository $config
     */
    public function __construct(ApplicationContract $app, RepositoryContract $config)
    {
        $this->app = $app;
        $this->config = $config;
    }

    /**
     * handle.
     *
     * @method handle
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle($request, $next)
    {
        $this->app->instance('config', $this->config);
        date_default_timezone_set($this->config->get('app.timezone'));

        return $next($request);
    }
}
