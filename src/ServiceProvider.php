<?php

namespace Recca0120\Config;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    protected $kernel;

    protected $config;

    public function boot()
    {
        $this->handlePublishes();
        $config = $this->app->make(Repository::class, [[]]);
        $this->app->instance('config', $config);
        date_default_timezone_set($config->get('app.timezone'));
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
