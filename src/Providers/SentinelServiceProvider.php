<?php

namespace PicoBaz\Sentinel\Providers;

use Illuminate\Support\ServiceProvider;
use PicoBaz\Sentinel\Commands\SentinelInstallCommand;
use PicoBaz\Sentinel\Commands\SentinelStatusCommand;
use PicoBaz\Sentinel\Services\SentinelService;
use PicoBaz\Sentinel\Http\Middleware\SentinelMiddleware;

class SentinelServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/sentinel.php', 'sentinel');
        
        $this->app->singleton('sentinel', function ($app) {
            return new SentinelService($app);
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../Config/sentinel.php' => config_path('sentinel.php'),
        ], 'sentinel-config');

        $this->publishes([
            __DIR__ . '/../Views' => resource_path('views/vendor/sentinel'),
        ], 'sentinel-views');

        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Views', 'sentinel');
        $this->loadRoutesFrom(__DIR__ . '/../routes.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SentinelInstallCommand::class,
                SentinelStatusCommand::class,
            ]);
        }

        $this->registerMiddleware();
        $this->bootModules();
    }

    protected function registerMiddleware()
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('sentinel', SentinelMiddleware::class);
    }

    protected function bootModules()
    {
        if (!config('sentinel.enabled', true)) {
            return;
        }

        foreach (config('sentinel.modules', []) as $module => $enabled) {
            if ($enabled) {
                $moduleClass = "PicoBaz\\Sentinel\\Modules\\" . ucfirst($module) . "\\" . ucfirst($module) . "Module";
                if (class_exists($moduleClass)) {
                    $this->app->make($moduleClass)->boot();
                }
            }
        }
    }
}
