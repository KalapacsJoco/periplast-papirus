<?php

namespace App\Http\Middleware;

use App\Auth\TabAuthentication;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ScopeDashboardAuthenticationToTab
{
    public function handle(Request $request, Closure $next): Response
    {
        $usesTabAuthentication = false;

        if (
            $request->header('X-Papirus-Tab') !== null
            || $request->header('X-Livewire') === '1'
            || ($request->isMethod('GET') && $request->is('/'))
        ) {
            TabAuthentication::tabIdFromRequest($request);
            Auth::forgetGuards();
            Auth::shouldUse('papirus-tab');
            $usesTabAuthentication = true;
        }

        try {
            return $next($request);
        } finally {
            if ($usesTabAuthentication) {
                Auth::forgetGuards();
                Auth::shouldUse(config('auth.defaults.guard'));
            }
        }
    }
}
