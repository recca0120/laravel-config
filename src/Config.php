<?php

namespace Recca0120\Config;

use Cache;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $guarded = ['id'];

    private static $clearCache = false;

    protected static function boot()
    {
        parent::boot();
        static::saved(function () {
            static::clearCache();
        });

        static::deleted(function () {
            static::clearCache();
        });
    }

    public static function cacheKey()
    {
        return md5(static::class);
    }

    private static function clearCache()
    {
        if (static::$clearCache === false) {
            static::$clearCache = true;
            Cache::driver('file')->forget(static::cacheKey());
        }
    }

    public function setValueAttribute($value)
    {
        if (is_bool($value) === true) {
            $value = ($value === true) ? 'true' : 'false';
        }

        $this->attributes['value'] = $value;
    }

    public function getValueAttribute($value)
    {
        switch ($value) {
            case 'true':
                $value = true;
                break;
            case 'false':
                $value = false;
                break;
        }

        return $value;
    }
}
