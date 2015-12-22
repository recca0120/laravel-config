<?php

namespace Recca0120\Config;

use Cache;
use Illuminate\Config\Repository as BaseRepository;
use Illuminate\Contracts\Config\Repository as RepositoryContract;

class Repository extends BaseRepository
{
    protected $config = null;

    protected $backup = [];

    protected $changed = [];

    protected $isDirty = false;

    public function __construct(array $items = [], RepositoryContract $config = null)
    {
        parent::__construct($items);

        if ($config == null) {
            return;
        }

        $this->config = $config;

        $changed = Cache::driver('file')->rememberForever(Config::cacheKey(), function () {
            return Config::all()->pluck('value', 'key')->toArray();
        });

        foreach ($changed as $key => $value) {
            array_set($this->changed, $key, $value);
            array_set($this->items, $key, $value);
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
            $value = $this->checkValue($value, $key);
            if ($value !== $original) {
                $this->isDirty = true;
                array_set($this->changed, $key, $value);
                array_set($this->items, $key, $value);
            }
        }
    }

    protected function checkValue($value, $key = '')
    {
        if (is_array($value) === true) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->checkValue($v, $key.'.'.$k);
            }
        } elseif ($value === '') {
            if ($this->config !== null && $this->config->get($key) === null) {
                $value = null;
            }
        }

        return $value;
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
