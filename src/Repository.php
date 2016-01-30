<?php

namespace Recca0120\Config;

use Closure;
use Illuminate\Config\Repository as BaseRepository;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository as CacheRepositoryContract;
use Illuminate\Contracts\Config\Repository as RepositoryContract;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;

class Repository extends BaseRepository
{
    /**
     * origin \Illuminate\Contracts\Config\Repository.
     *
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * is data changed.
     *
     * @var bool
     */
    protected $dirty = false;

    /**
     * data changed.
     *
     * @var array
     */
    protected $changed = [];

    /**
     * cache is cleaned.
     *
     * @var [type]
     */
    protected $cacheCleaned = false;

    /**
     * construct.
     *
     * @param array                                   $items
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \Illuminate\Contracts\Cache\Factory     $cacheFactory
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     */
    public function __construct(
        array $items = [],
        RepositoryContract $config = null,
        CacheFactory $cacheFactory = null,
        Dispatcher $events = null
    ) {
        $this->config = $config;
        if (count($items) === 0) {
            $items = $config->all();
        }

        parent::__construct($items);

        if ($cacheFactory !== null) {
            $cacheKey = $this->cacheKey();
            $cacheRepository = $cacheFactory->driver('file');
            $changed = $cacheRepository->rememberForever($cacheKey, function () {
                $this->loadConfig();
            });

            Config::saved(function () use ($cacheRepository, $cacheKey) {
                $this->forgetCache($cacheRepository, $cacheKey);
            });

            Config::deleted(function () use ($cacheRepository, $cacheKey) {
                $this->forgetCache($cacheRepository, $cacheKey);
            });
        } else {
            $changed = Config::all()->pluck('value', 'key')->toArray();
        }

        foreach ($changed as $key => $value) {
            Arr::set($this->changed, $key, $value);
            Arr::set($this->items, $key, $value);
        }

        if ($events !== null) {
            $events->listen('kernel.handled', function ($request, $response) {
                return $this->onKernelHandled();
            });
        }
    }

    /**
     * load config.
     * @return mixed
     */
    protected function loadConfig()
    {
        return Config::all()->pluck('value', 'key')->toArray();
    }

    /**
     * get cache key.
     *
     * @return string
     */
    protected function cacheKey()
    {
        return md5(static::class);
    }

    /**
     * clear cache.
     *
     * @param \Illuminate\Contracts\Cache\Repository $cacheRepository [description]
     * @param string                                 $cacheKey
     *
     * @return void
     */
    protected function forgetCache(CacheRepositoryContract $cacheRepository, $cacheKey)
    {
        if ($this->cacheCleaned === true) {
            return;
        }
        $cacheRepository->forget($cacheKey);
        $this->cacheCleaned = true;
    }

    /**
     * trigger when event kernel.handled.
     *
     * @return void
     */
    public function onKernelHandled()
    {
        if ($this->dirty === true && empty($this->changed) === false) {
            $model = Config::truncate();
            $model->getConnection()->transaction(function () {
                $this->saveToDatabase();
            });
            $this->dirty = false;
        }

        return $this->changed;
    }

    /**
     * save to database.
     * @return void
     */
    public function saveToDatabase()
    {
        $changed = array_dot($this->changed);
        array_walk($changed, function (&$value, $key) {
            if ($value === null) {
                return;
            }
            Config::create([
                'key'   => $key,
                'value' => $value,
            ])->save();
        });
    }

    /**
     * Set a given configuration value.
     *
     * @param array|string $key
     * @param mixed        $value
     *
     * @return void
     */
    public function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $innerKey => $innerValue) {
                $this->set($innerKey, $innerValue);
            }
        } elseif (is_array($value) === true) {
            foreach ($value as $innerKey => $innerValue) {
                $this->set($key.'.'.$innerKey, $innerValue);
            }
        } else {
            $original = $this->get($key);
            $value = $this->checkValue($value, $key);
            if ($value !== $original) {
                Arr::set($this->items, $key, $value);
                if ($value instanceof Closure) {
                    return;
                }
                $this->dirty = true;
                Arr::set($this->changed, $key, $value);
            }
        }
    }

    /**
     * check value.
     *
     * @param mixed  $value
     * @param string $key
     *
     * @return mixed
     */
    protected function checkValue($value, $key = '')
    {
        if ($value === '') {
            if ($this->config !== null && $this->config->get($key) === null) {
                $value = null;
            }
        }

        return $value;
    }
}
