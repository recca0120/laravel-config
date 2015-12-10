<?php

namespace Recca0120\Config;

use Illuminate\Contracts\Config\Repository as RepositoryContract;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Recca0120\Config\Middleware\StoreHandle;
use Recca0120\Config\Observers\CacheHandle;

class ServiceProvider extends BaseServiceProvider
{
    protected $kernel;

    public function boot(Kernel $kernel)
    {
        $this->publishAsses();
        $kernel->pushMiddleware(StoreHandle::class);

        $this->app['config'] = $this->app->make(RepositoryContract::class);
    }

    public function publishAsses()
    {
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'migrations');
    }

    public function register()
    {
        // $config = $this->app->make(RepositoryContract::class);
        $this->app->singleton(RepositoryContract::class, function ($app) {
            Config::observe(new CacheHandle);
            $config = $app['config'];
            $config = new Repository($config);
            date_default_timezone_set($config->get('app.timezone'));

            return $config;
        });
    }

    public function provides()
    {
        return ['config'];
    }
}
