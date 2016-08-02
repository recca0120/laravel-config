<?php

namespace Recca0120\Config;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Recca0120\Config\Repositories\DatabaseRepository;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->handlePublishes();
        if ($this->app->runningInConsole() === true) {
            return;
        }

        $this->app->booted(function () {
            $config = $this->app->make(DatabaseRepository::class);
            $this->app->instance('config', $config);
            date_default_timezone_set($config->get('app.timezone'));
        });
    }

    /**
     * handle publishes.
     *
     * @return void
     */
    public function handlePublishes()
    {
        $this->publishes([
            __DIR__.'/../database/migrations/' => $this->app->databasePath().'/migrations',
        ], 'migrations');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['config'];
    }
}
