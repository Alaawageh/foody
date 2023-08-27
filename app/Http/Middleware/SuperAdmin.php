<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->user()->type == "Super Admin") {
            return $next($request);
        } 
        else {
            return response()->json(['error' => 'FORBIDDEN'],Response::HTTP_FORBIDDEN) ;
        }
    }
}
