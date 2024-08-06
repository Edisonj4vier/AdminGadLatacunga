<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class ApiHelper
{
    public static function request($method, $endpoint, $data = [])
    {
        $token = Session::get('access_token');
        $baseUrl = Config::get('services.api.url');
        $url = $baseUrl . $endpoint;

        $headers = [
            'Accept' => 'application/json',
        ];

        return Http::withToken($token)
            ->withHeaders($headers)
            ->$method($url, $data);
    }
}
