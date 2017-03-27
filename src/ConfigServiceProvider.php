<?php

namespace Recca0120\Config;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Recca0120\Config\Contracts\Repository;
use Recca0120\Config\Middleware\SetConfigRepository;
use Recca0120\Config\Repositories\DatabaseRepository;

class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(Kernel $kernel)
    {
        $kernel->pushMiddleware(SetConfigRepository::class);

        if ($this->app->runningInConsole() === true) {
            $this->handlePublishes();
        }
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->singleton(Repository::class, function ($app) {
            $config = [
                'protected' => [
                    'auth.defaults.guard',
                ],
                'path' => $app->storagePath().'/app/',
            ];

            return new DatabaseRepository($app['config'], $app->make(Config::class), $app['files'], $config);
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

    /**
     * handle publishes.
     */
    protected function handlePublishes()
    {
        $this->publishes([
            __DIR__.'/../database/migrations/' => $this->app->databasePath().'/migrations',
        ], 'migrations');
    }
}
