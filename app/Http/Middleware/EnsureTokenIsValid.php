<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class EnsureTokenIsValid
{
    public function handle(Request $request, Closure $next)
    {
        if (!Session::has('access_token')) {
            return redirect('login');
        }

        return $next($request);
    }
}
