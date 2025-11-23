<?php

namespace PicoBaz\Sentinel\Commands;

use Illuminate\Console\Command;

class SentinelInstallCommand extends Command
{
    protected $signature = 'sentinel:install';
    protected $description = 'Install Laravel Sentinel package';

    public function handle()
    {
        $this->info('Installing Laravel Sentinel...');

        $this->call('vendor:publish', [
            '--tag' => 'sentinel-config',
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'sentinel-views',
        ]);

        $this->call('migrate');

        $this->info('Laravel Sentinel installed successfully!');
        $this->line('');
        $this->line('Next steps:');
        $this->line('1. Configure your notification channels in config/sentinel.php');
        $this->line('2. Visit /sentinel to view the dashboard');
        $this->line('3. Add middleware to routes: Route::middleware(\'sentinel\')');
    }
}
