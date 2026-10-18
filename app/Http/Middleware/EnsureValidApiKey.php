<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.api_key');
        $provided = (string) ($request->header('X-API-Key') ?: $request->bearerToken());

        // Without a configured key every API request is rejected.
        if ($expected === '' || ! hash_equals($expected, $provided)) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }
}
