<?php

namespace Recca0120\Config\Middleware;

use Closure;
use DB;
use Recca0120\Config\Config;

class StoreHandle
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $config = config();
        $changed = $config->getDirty();

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
