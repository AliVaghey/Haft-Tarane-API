<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CORSMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request)
            ->header('Access-Control-Allow-Origin', config('cors.allowed_origins', '*'))
            ->header('Access-Control-Allow-Methods', config('cors.allowed_methods', 'DELETE, GET, PUT, PATCH, POST, OPTIONS'))
            ->header('Access-Control-Allow-Credentials', config('cors.supports_credentials', '*'))
            ->header('Access-Control-Allow-Headers', config('cors.allowed_headers', '*'))
            ->header('Accept', 'application/json');
    }
}
