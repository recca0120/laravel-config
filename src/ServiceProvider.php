<?php

namespace Recca0120\Config;

use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Recca0120\Config\Middleware\StoreHandle;
use Recca0120\Config\Observers\CacheHandle;

class ServiceProvider extends BaseServiceProvider
{
    protected $kernel;

    public function boot(Kernel $kernel)
    {
        Config::observe(new CacheHandle);
        $kernel->pushMiddleware(StoreHandle::class);
        $this->publishAsses();

        $config = new Repository(config());
        $this->app['config'] = $this->app->share(function ($app) use ($config) {
            return $config;
        });
        date_default_timezone_set(config('app.timezone'));
    }

    public function publishAsses()
    {
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'migrations');
    }

    public function register()
    {
        $this->app->singleton(
            ConfigRepository::class,
            Repository::class
        );
    }

    public function provides()
    {
        return ['config'];
    }
}
