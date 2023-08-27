<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class admin
{
    public function handle(Request $request, Closure $next)
    { 
            if (auth()->user()->type == "admin" || auth()->user()->type == 'Super Admin') {
                // dd("yes i am admin");
                return $next($request);
            } else {
                return response()->json(['error' => 'FORBIDDEN'],Response::HTTP_FORBIDDEN) ;
            }
        
    }
}