<?php

namespace Recca0120\Config;

use Illuminate\Contracts\Config\Repository as ConfigRepositoryContract;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

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
        $this->app->booted(function () {
            $config = $this->app->make(Repository::class);
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
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'migrations');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // $this->app->extend(ConfigRepositoryContract::class, function ($config, $app) {
        //     $config = new Repository([], $config, $app['cache'], $app['events'], $app);
        //
        //     return $config;
        // });
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
