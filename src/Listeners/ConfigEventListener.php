<?php

namespace Recca0120\Config\Listeners;

use Cache;
use Illuminate\Events\Dispatcher;
use Recca0120\Config\Config;
use Recca0120\Config\Repository;

class ConfigEventListener
{
    protected static $clearCache = false;

    public function clearCache()
    {
        if (static::$clearCache === false) {
            static::$clearCache = true;
            Cache::forget(Repository::getCacheKey());
        }
    }

    public function subscribe(Dispatcher $events)
    {
        // $event = "eloquent.{$event}: ".get_class($this);
        $events->listen(
            'eloquent.saved: '.Config::class,
            static::class.'@clearCache'
        );

        $events->listen(
            'eloquent.deleted: '.Config::class,
            static::class.'@clearCache'
        );
    }
}
