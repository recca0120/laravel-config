<?php

namespace Recca0120\Config;

use Illuminate\Config\Repository as BaseRepository;
use Illuminate\Contracts\Config\Repository as RepositoryContract;
use Illuminate\Database\QueryException;

class Repository extends BaseRepository
{
    protected $config;

    protected $backup = [];

    protected $changed = [];

    protected $isDirty = false;

    public function __construct(RepositoryContract $config = null)
    {
        if ($config !== null) {
            $cacheKey = $this->getCacheKey();
            try {
                $this->changed = app('cache')->rememberForever($cacheKey, function () {
                    return Config::all()->pluck('value', 'key')->toArray();
                });
            } catch (QueryException $e) {
                // if (app('cache')->has($cacheKey) === true) {
                //     app('cache')->forget($cacheKey);
                // }
            }
            $config->set($this->changed);
            $this->items = $config->all();
            $this->config = $config;
        }
    }

    public function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $innerKey => $innerValue) {
                $this->set($innerKey, $innerValue);
            }
        } else {
            $original = $this->get($key);
            if ($value !== $original) {
                $this->isDirty = true;
                array_set($this->changed, $key, $value);
                array_set($this->items, $key, $value);
            }
        }
    }

    public function getCacheKey()
    {
        return md5(static::class);
    }

    public function getChanged()
    {
        if ($this->isDirty === false) {
            return [];
        }

        return array_dot($this->changed);
    }

    public function backup()
    {
        $this->backup = [
            'items' => $this->items,
            'changed' => $this->changed,
            'isDirty' => $this->isDirty,
        ];

        return $this;
    }

    public function restore()
    {
        $this->items = $this->backup['items'];
        $this->changed = $this->backup['changed'];
        $this->isDirty = $this->backup['isDirty'];
        $this->backup = [];

        return $this;
    }
}
