<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CacheControlHeader
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        // Set the Cache-Control header for static assets
        if ($request->is('resources/css/*') || $request->is('resources/js/*')) {
            $response->headers->set('Cache-Control', 'public, max-age=86400');
        }

        return $response;
    }
}

