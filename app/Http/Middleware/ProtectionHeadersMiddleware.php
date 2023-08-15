<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class ProtectionHeadersMiddleware
{
    public function handle($request, Closure $next)
   
      {
        $response = $next($request);

        if (!$response instanceof Response || $response->headers->has('Content-Disposition')) {
            return $response;
        }

        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('X-XSS-Protection', '1; mode=block');
        $response->header('X-Frame-Options', 'SAMEORIGIN');
        $response->header('Content-Security-Policy', "default-src 'self'");
        $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }
}
