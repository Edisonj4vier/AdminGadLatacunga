<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class SetReferrerPolicy
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $policy = Config::get('app.referrer_policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Referrer-Policy', $policy);

        return $response;
    }
}
