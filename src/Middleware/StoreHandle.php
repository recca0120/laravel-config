<?php

namespace Recca0120\Config\Middleware;

use Closure;
use DB;
use Recca0120\Config\Config;
use Recca0120\Config\Repository;

class StoreHandle
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $changed = Repository::getChanged();
        if (empty($changed) === false) {
            Config::truncate();
            DB::transaction(function () use ($changed) {
                array_walk($changed, function (&$value, $key) {
                    Config::create([
                        'key' => $key,
                        'value' => $value,
                    ]);
                });
            });
        }

        return $response;
    }
}
