<?php

namespace Recca0120\Config\Observers;

use Cache;
use Recca0120\Config\Repository;

class ModelCache
{
    protected static $clearCache = false;

    public function clearCache()
    {
        if (static::$clearCache === false) {
            static::$clearCache = true;
            Cache::forget(Repository::getCacheKey());
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
