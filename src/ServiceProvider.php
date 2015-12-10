<?php

namespace Recca0120\Config;

use Illuminate\Contracts\Config\Repository as RepositoryContract;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Recca0120\Config\Middleware\StoreHandle;

class ServiceProvider extends BaseServiceProvider
{
    protected $kernel;

    public function boot(Kernel $kernel, RepositoryContract $config)
    {
        $this->handlePublishes();
        $kernel->pushMiddleware(StoreHandle::class);
        $this->app->instance('config', $config);
    }

    public function handlePublishes()
    {
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'migrations');
    }

    public function register()
    {
        $config = $this->app->make(RepositoryContract::class);
        $this->app->singleton(RepositoryContract::class, function ($app) use ($config) {
            $config = new Repository($config->all(), $config);
            date_default_timezone_set($config->get('app.timezone'));

            return $config;
        });
    }

    public function provides()
    {
        return ['config'];
    }
}
