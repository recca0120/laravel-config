<?php

namespace Recca0120\Config;

use Cache;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Database\QueryException;

class Repository extends ConfigRepository
{
    protected $config;

    protected $backup = [];

    protected $changed = [];

    protected $isDirty = false;

    public function __construct(ConfigRepository $config = null)
    {
        if ($config !== null) {
            $this->changed = Cache::rememberForever($this->getCacheKey(), function () {
                $result = [];
                try {
                    return Config::all()->pluck('value', 'key')->toArray();
                } catch (QueryException $e) {
                }

                return $result;
            });
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

    public function getDirty()
    {
        if ($this->isDirty === false) {
            return false;
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
