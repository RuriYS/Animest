<?php

namespace App\Http\Controllers;

use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProxyController extends Controller
{
    protected $http;

    public function __construct(Factory $http)
    {
        $handler = new CurlHandler();
        $stack = HandlerStack::create($handler);

        $this->http = $http->withHeaders([
            'Origin' => config('app.urls.proxy_origin'),
            'Referer' => config('app.urls.proxy_origin') . '/'
        ])
            ->withOptions([
                'version' => 2.0,
                'curl' => [
                    CURLOPT_TCP_KEEPALIVE => 1,
                    CURLOPT_TCP_KEEPIDLE => 60,
                    CURLOPT_TCP_KEEPINTVL => 30,
                ],
                'handler' => $stack,
                'connect_timeout' => 5,
            ]);
    }

    public function proxy(Request $request, $uri)
    {
        $key = "proxy:{md5($uri)}";

        return Cache::remember($key, now()->addMinutes(60), function () use ($uri) {
            $response = $this->http->get($uri);
            return response($response->body(), $response->status())->withHeaders($response->headers());
        });
    }
}
