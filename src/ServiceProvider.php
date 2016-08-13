<?php

namespace Recca0120\Config;

use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Recca0120\Config\Contracts\Repository as RepositoryContract;
use Recca0120\Config\Middleware\SetConfigRepository;
use Recca0120\Config\Repositories\DatabaseRepository;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(HttpKernelContract $kernel)
    {
        $this->handlePublishes();
        if ($this->app->runningInConsole() === true) {
            return;
        }

        $kernel->pushMiddleware(SetConfigRepository::class);
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
        $this->app->singleton(RepositoryContract::class, DatabaseRepository::class);
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
