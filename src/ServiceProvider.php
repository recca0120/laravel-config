<?php

namespace Recca0120\Config;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Config\Repository as RepositoryContract;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    protected $kernel;

    protected $config;

    public function boot(RepositoryContract $config, CacheFactory $cacheFactory, Dispatcher $dispatcher)
    {
        $this->handlePublishes();

        $config = new Repository($config->all(), $config, $cacheFactory, $dispatcher);
        $this->app->singleton(RepositoryContract::class, function ($app) use ($config) {
            return $config;
        });
        $this->app->booted(function ($app) use ($config) {
            date_default_timezone_set($config->get('app.timezone'));
            $app->instance('config', $config);
        });
    }

    public function handlePublishes()
    {
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'migrations');
    }

    public function register()
    {
    }

    public function provides()
    {
        return ['config'];
    }
}
