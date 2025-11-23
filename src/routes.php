<?php

use Illuminate\Support\Facades\Route;
use PicoBaz\Sentinel\Http\Controllers\SentinelDashboardController;

if (config('sentinel.dashboard.enabled')) {
    Route::prefix(config('sentinel.dashboard.route_prefix'))
        ->middleware(config('sentinel.dashboard.middleware'))
        ->group(function () {
            Route::get('/', [SentinelDashboardController::class, 'index'])->name('sentinel.dashboard');
            Route::get('/metrics/{type?}', [SentinelDashboardController::class, 'metrics'])->name('sentinel.metrics');
        });
}
