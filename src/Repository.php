<?php

namespace Recca0120\Config;

use Cache;
use Illuminate\Config\Repository as BaseRepository;

class Repository extends BaseRepository
{
    public static $original = [];

    public static $changed = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
        try {
            $config = Cache::rememberForever(static::getCacheKey(), function () {
                return Config::all()->pluck('value', 'key')->toArray();
            });
            foreach ($config as $key => $value) {
                array_set(static::$original, $key, $value);
            }
            $this->items = array_merge($this->items, static::$original);
        } catch (QueryException $e) {
        }
    }

    public function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $innerKey => $innerValue) {
                $this->set($innerKey, $innerValue);
            }
        } else {
            array_set(static::$changed, $key, $value);
            array_set($this->items, $key, $value);
        }
    }

    public static function getCacheKey()
    {
        return $cacheKey = md5(static::class);
    }

    public static function getChanged()
    {
        if (empty(static::$changed) === true) {
            return [];
        }

        $config = array_merge(static::$original, static::$changed);

        return array_dot($config);
    }
}
