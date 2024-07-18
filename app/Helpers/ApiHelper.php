<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class ApiHelper
{
    public static function request($method, $url, $data = [])
    {
        $token = Session::get('access_token');

        return Http::withToken($token)->$method(config('services.api.url') . $url, $data);
    }
}
