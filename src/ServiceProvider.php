<?php

namespace Recca0120\Config;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Recca0120\Config\Contracts\Repository;
use Recca0120\Config\Middleware\SetConfigRepository;
use Recca0120\Config\Repositories\DatabaseRepository;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(Kernel $kernel)
    {
        $this->handlePublishes();
        if ($this->app->runningInConsole() === true) {
            return;
        }

        $kernel->pushMiddleware(SetConfigRepository::class);
    }

    /**
     * handle publishes.
     */
    public function handlePublishes()
    {
        $this->publishes([
            __DIR__.'/../database/migrations/' => $this->app->databasePath().'/migrations',
        ], 'migrations');
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->singleton(Repository::class, function ($app) {
            $config = [
                'path' => $app->storagePath().'/app/',
            ];

            return $app->make(DatabaseRepository::class, [
                'config' => $config,
            ]);
        });
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
