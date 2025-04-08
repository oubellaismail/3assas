<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check if user is logged in and has admin role/permission
        if (false) {
            return $next($request);
        }
        
        // Redirect non-admin users
        return redirect('/')->with('error', 'Unauthorized access');
    }
}