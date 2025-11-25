<?php

namespace PicoBaz\Sentinel\Modules\SecurityMonitor;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use PicoBaz\Sentinel\Facades\Sentinel;

class SecurityMonitorModule
{
    protected $suspiciousPatterns = [
        'sql_injection' => [
            '/(\bUNION\b.*\bSELECT\b)/i',
            '/(\bSELECT\b.*\bFROM\b.*\bWHERE\b)/i',
            '/(DROP|DELETE|UPDATE|INSERT)\s+(TABLE|DATABASE)/i',
            '/(\bOR\b\s+\d+\s*=\s*\d+)/i',
        ],
        'xss' => [
            '/<script[^>]*>.*?<\/script>/i',
            '/javascript:/i',
            '/onerror\s*=/i',
            '/onload\s*=/i',
        ],
        'path_traversal' => [
            '/\.\.[\/\\\\]/',
            '/etc\/passwd/i',
            '/proc\/self/i',
        ],
        'command_injection' => [
            '/;\s*(ls|cat|wget|curl|nc|bash|sh)\s/i',
            '/\|\s*(ls|cat|wget|curl)/i',
        ],
    ];

    public function boot()
    {
        if (!config('sentinel.modules.securityMonitor')) {
            return;
        }

        $this->monitorFailedLogins();
        $this->monitorSuspiciousRequests();
        $this->monitorRateLimiting();
        $this->monitorUnauthorizedAccess();
    }

    protected function monitorFailedLogins()
    {
        Event::listen('Illuminate\Auth\Events\Failed', function ($event) {
            Sentinel::log('security', [
                'type' => 'failed_login',
                'email' => $event->credentials['email'] ?? 'N/A',
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toDateTimeString(),
            ]);
        });

        Event::listen('Illuminate\Auth\Events\Lockout', function ($event) {
            Sentinel::log('security', [
                'type' => 'account_lockout',
                'email' => $event->request->input('email'),
                'ip' => request()->ip(),
                'attempts' => 5,
                'timestamp' => now()->toDateTimeString(),
            ]);
        });
    }

    protected function monitorSuspiciousRequests()
    {
        app()->middleware(function ($request, $next) {
            $this->checkForSuspiciousPatterns($request);
            return $next($request);
        });
    }

    protected function checkForSuspiciousPatterns($request)
    {
        $input = json_encode($request->all());
        $url = $request->fullUrl();

        foreach ($this->suspiciousPatterns as $type => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $input) || preg_match($pattern, $url)) {
                    Sentinel::log('security', [
                        'type' => 'suspicious_request',
                        'attack_type' => $type,
                        'pattern' => $pattern,
                        'url' => $url,
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'payload' => substr($input, 0, 500),
                        'timestamp' => now()->toDateTimeString(),
                    ]);
                    break 2;
                }
            }
        }
    }

    protected function monitorRateLimiting()
    {
        RateLimiter::for('sentinel-monitor', function ($request) {
            $key = 'sentinel:' . $request->ip();

            if (RateLimiter::tooManyAttempts($key, 100)) {
                Sentinel::log('security', [
                    'type' => 'rate_limit_exceeded',
                    'ip' => $request->ip(),
                    'limit' => 100,
                    'window' => '1 minute',
                    'url' => $request->fullUrl(),
                    'timestamp' => now()->toDateTimeString(),
                ]);
            }

            return RateLimiter::hit($key, 60);
        });
    }

    protected function monitorUnauthorizedAccess()
    {
        Event::listen('Illuminate\Auth\Events\Attempting', function ($event) {
            $user = $event->user ?? null;

            if ($user && !$user->hasPermissionTo($event->guard ?? 'web')) {
                Sentinel::log('security', [
                    'type' => 'unauthorized_access_attempt',
                    'user_id' => $user->id ?? null,
                    'email' => $user->email ?? 'N/A',
                    'guard' => $event->guard,
                    'ip' => request()->ip(),
                    'url' => request()->fullUrl(),
                    'timestamp' => now()->toDateTimeString(),
                ]);
            }
        });
    }

    public function checkFileIntegrity(array $files)
    {
        foreach ($files as $file) {
            if (!file_exists($file)) {
                continue;
            }

            $hash = md5_file($file);
            $stored = cache()->get("sentinel:file:$file");

            if ($stored && $stored !== $hash) {
                Sentinel::log('security', [
                    'type' => 'file_modification',
                    'file' => $file,
                    'old_hash' => $stored,
                    'new_hash' => $hash,
                    'timestamp' => now()->toDateTimeString(),
                ]);
            }

            cache()->forever("sentinel:file:$file", $hash);
        }
    }
}