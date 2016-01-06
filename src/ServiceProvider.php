<?php

namespace Recca0120\Config;

use Illuminate\Contracts\Config\Repository as RepositoryContract;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    protected $kernel;

    public function boot()
    {
        $this->handlePublishes();
        $this->app->booted(function ($app) {
            $config = $app->make(RepositoryContract::class);
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
        $config = $this->app->make(RepositoryContract::class);
        $this->app->singleton(RepositoryContract::class, function ($app) use ($config) {
            $config = new Repository($config->all(), $config);
            date_default_timezone_set($config->get('app.timezone'));
            $app['events']->listen('kernel.handled', function ($request, $response) use ($config) {
                $config->onKernelHandled();
            });

            return $config;
        });
    }

    public function provides()
    {
        return ['config'];
    }
}
