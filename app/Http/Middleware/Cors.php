<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Config;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class Cors
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('Access-Control-Allow-Origin', implode(', ', Config::get('cors.allowed_origins', ['*'])));
        $response->headers->set('Access-Control-Allow-Methods', implode(', ', Config::get('cors.allowed_methods', ['*'])));
        $response->headers->set('Access-Control-Allow-Headers', implode(', ', Config::get('cors.allowed_headers', ['*'])));

        if (Config::get('cors.supports_credentials')) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');

        }
        $response->headers->set('Referrer-Policy', Config::get('cors.referrer_policy', 'strict-origin-when-cross-origin'));
        // Manejo de solicitudes preflight OPTIONS
        if ($request->getMethod() == "OPTIONS") {
            $response->setStatusCode(200);
            $response->headers->set('Access-Control-Max-Age', '86400');
            $response->headers->set('Content-Length', '0');
            $response->headers->set('Content-Type', 'text/plain');
        }

        return $response;
    }
}
