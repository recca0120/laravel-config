<?php

namespace Recca0120\Config;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Recca0120\Config\Middleware\ConfigMiddleware;
use Recca0120\Config\Observers\ConfigObserver;

class ServiceProvider extends BaseServiceProvider
{
    protected $kernel;

    public function boot(Kernel $kernel)
    {
        Config::observe(new ConfigObserver);
        $kernel->pushMiddleware(ConfigMiddleware::class);
        $this->publishAsses();

        $config = new Repository(config()->all());
        date_default_timezone_set($config['app.timezone']);

        $this->app['config'] = $this->app->share(function ($app) use ($config) {
            return $config;
        });
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
            'Illuminate\Contracts\Config\Repository',
            Repository::class
        );
    }

    public function provides()
    {
        return ['config'];
    }
}
