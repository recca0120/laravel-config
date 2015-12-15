<?php

namespace Recca0120\Config;

use Illuminate\Config\Repository as BaseRepository;
use Illuminate\Contracts\Config\Repository as RepositoryContract;

// use Illuminate\Database\QueryException;

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
        $cacheKey = $this->getCacheKey();
        $cache = json_decode(app('cache')->rememberForever($cacheKey, function () use ($items) {
            $changed = [];
            foreach (Config::all() as $model) {
                $value = $model->value;
                switch ($value) {
                    case 'true':
                        $value = true;
                        break;
                    case 'false':
                        $value = false;
                        break;
                }
                array_set($changed, $model->key, $value);
                array_set($items, $model->key, $value);
            }

            return json_encode([
                'changed' => $changed,
                'items' => $items,
            ]);
        }), true);

        $this->changed = $cache['changed'];
        $this->items = $cache['items'];
    }

    public function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $innerKey => $innerValue) {
                $this->set($innerKey, $innerValue);
            }
        } else {
            $original = $this->get($key);
            $value = $this->stringValue($value, $key);
            if ($value !== $original) {
                $this->isDirty = true;
                array_set($this->changed, $key, $value);
                array_set($this->items, $key, $value);
            }
        }
    }

    protected function stringValue($value, $key = '')
    {
        if ($value === true || trim(strtolower($value)) === 'true') {
            $value = 'true';
        } elseif ($value === false || trim(strtolower($value)) === 'false') {
            $vaue = 'false';
        } elseif ($value === '') {
            if ($this->config !== null && $this->config->get($key) === null) {
                $value = null;
            }
        }

        return $value;
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
