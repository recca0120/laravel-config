<?php

require __DIR__.'/../vendor/autoload.php';

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Hashing\BcryptHasher;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Str;

class Application extends Container
{
    public $aliases = [
        \Illuminate\Support\Facades\Facade::class  => 'Facade',
        \Illuminate\Support\Facades\App::class     => 'App',
        \Illuminate\Support\Facades\Schema::class  => 'Schema',
    ];

    public function __construct()
    {
        date_default_timezone_set('UTC');
        Carbon\Carbon::setTestNow(Carbon\Carbon::now());

        $this['app'] = $this;
        $this->setupAliases();
        $this->setupDispatcher();
        $this->setupConnection();
        Facade::setFacadeApplication($this);
        Container::setInstance($this);
    }

    public function setupDispatcher()
    {
        $this['events'] = new Dispatcher($this);
    }

    public function setupAliases()
    {
        foreach ($this->aliases as $className => $alias) {
            class_alias($className, $alias);
        }
    }

    public function setupConnection()
    {
        $connection = new Illuminate\Database\Capsule\Manager();
        $connection->addConnection([
            'driver'   => 'sqlite',
            'database' => ':memory:',
        ]);
        $connection->setEventDispatcher($this['events']);
        $connection->bootEloquent();
        $connection->setAsGlobal();

        $this['db'] = $connection;
    }

    public function migrate($method)
    {
        foreach (glob(__DIR__.'/../database/migrations/*.php') as $file) {
            include_once $file;
            if (preg_match('/\d+_\d+_\d+_\d+_(.*)\.php/', $file, $m)) {
                $className = Str::studly($m[1]);
                $migration = new $className();
                call_user_func_array([$migration, $method], []);
            }
        }
    }

    public function environment()
    {
        return 'testing';
    }
}

if (!function_exists('bcrypt')) {
    /**
     * Hash the given value.
     *
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    function bcrypt($value, $options = [])
    {
        return (new BcryptHasher())->make($value, $options);
    }
}

if (!function_exists('app')) {
    function app()
    {
        return App::getInstance();
    }
}

if (Application::getInstance() == null) {
    new Application();
}
