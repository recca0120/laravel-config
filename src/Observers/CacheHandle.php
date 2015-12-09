<?php

namespace Recca0120\Config\Observers;

use Cache;

class CacheHandle
{
    protected static $clearCache = false;

    public function clearCache()
    {
        if (static::$clearCache === false) {
            static::$clearCache = true;
            Cache::forget(config()->getCacheKey());
        }
    }

    public function saved($model)
    {
        $this->clearCache();
    }

    public function deleted($model)
    {
        $this->clearCache();
    }
}
