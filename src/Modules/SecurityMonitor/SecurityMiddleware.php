<?php

namespace PicoBaz\Sentinel\Modules\SecurityMonitor;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        if (SecurityHelper::shouldBlockRequest($ip)) {
            abort(403, 'Access Denied: Security Policy Violation');
        }

        $response = $next($request);

        $response->headers->set('X-Security-Score', SecurityHelper::getSecurityScore($ip));
        $response->headers->set('X-Threat-Level', SecurityHelper::getThreatLevel(SecurityHelper::getSecurityScore($ip)));

        return $response;
    }
}