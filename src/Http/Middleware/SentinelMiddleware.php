<?php

namespace PicoBaz\Sentinel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SentinelMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $response = $next($request);

        $executionTime = (microtime(true) - $startTime) * 1000;
        $memoryUsed = (memory_get_usage() - $startMemory) / 1024 / 1024;

        $response->headers->set('X-Sentinel-Time', round($executionTime, 2) . 'ms');
        $response->headers->set('X-Sentinel-Memory', round($memoryUsed, 2) . 'MB');

        return $response;
    }
}
