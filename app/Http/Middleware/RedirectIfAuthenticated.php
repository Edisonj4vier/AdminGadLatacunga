<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (Session::has('access_token')) {
            return redirect()->route('app-lector-ruta.index');
        }

        return $next($request);
    }
}
