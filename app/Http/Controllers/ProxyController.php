<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class ProxyController extends Controller
{
    public function proxy(Request $request, $uri)
    {
        if (RateLimiter::tooManyAttempts('proxy:' . $request->ip(), $perMinute = 60)) {
            return response('Too many requests', 429);
        }

        RateLimiter::hit('proxy:' . $request->ip());

        $response = Http::get($uri);
        return response($response->body(), $response->status())->withHeaders($response->headers());
    }
}
