<?php

namespace Recca0120\Config;

use Cache;
use Closure;
use DB;
use Illuminate\Config\Repository as BaseRepository;
use Illuminate\Contracts\Config\Repository as RepositoryContract;
use Illuminate\Support\Arr;

class Repository extends BaseRepository
{
    protected $model;

    protected $config;

    protected $isDirty = false;

    protected $changed = [];

    protected $cacheForget = false;

    protected static $booted = false;

    public function __construct(array $items = [], RepositoryContract $config = null)
    {
        $this->config = $config;
        parent::__construct($items);
        $changed = Cache::driver('file')->rememberForever($this->cacheKey(), function () {
            return Config::all()->pluck('value', 'key')->toArray();
        });
        foreach ($changed as $key => $value) {
            Arr::set($this->changed, $key, $value);
            Arr::set($this->items, $key, $value);
        }

        Config::saved(function () {
            $this->cacheForget();
        });

        Config::deleted(function () {
            $this->cacheForget();
        });
    }

    protected function cacheKey()
    {
        return md5(static::class);
    }

    protected function cacheForget()
    {
        if ($this->cacheForget === true) {
            return;
        }
        Cache::driver('file')->forget($this->cacheKey());
        $this->cacheForget = true;
    }

    public function onKernelHandled()
    {
        if ($this->isDirty === true && empty($this->changed) === false) {
            Config::truncate();
            DB::transaction(function () {
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
            });
            $this->isDirty = false;
        }

        return $this->changed;
    }

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
                $this->isDirty = true;
                Arr::set($this->changed, $key, $value);
            }
        }
    }

    protected function checkValue($value, $key = '')
    {
        if ($value === '') {
            if ($this->config !== null && $this->config->get($key) === null) {
                $value = null;
            }
        }

        return $value;
    }

    public function __call($method, $arguments)
    {
        return call_user_func($this->config, $arguments);
    }
}
