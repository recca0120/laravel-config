<?php

namespace Recca0120\Config\Observers;

class CacheHandle
{
    protected static $clearCache = false;

    public function clearCache()
    {
        if (static::$clearCache === false) {
            static::$clearCache = true;
            app('cache')->driver('file')->forget(config()->getCacheKey());
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
