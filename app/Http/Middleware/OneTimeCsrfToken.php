<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class OneTimeCsrfToken
{
    public function handle($request, Closure $next)
    {
        // トークンをリフレッシュ
        $request->session()->regenerateToken();

        $key = $request->session()->getId().'_cache_token';
        $input_key = $request->session()->getId().'_input_token';

        if ($request->method() !== 'POST') {
            Cache::forget($key);
            // 画面に表示されるトークンを保持
            $input_token = $request->session()->token();
            Cache::put($input_key, $input_token, 1);
        } else {
            // POST時の初回のみキャッシュにリフレッシュしたトークンを載せる
            $cache_token = Cache::get($key);
            if (is_null($cache_token)) {
                $cache_token = $request->session()->token();
                Cache::put($key, $cache_token, 1);
            }
        }

        return $next($request);
    }
}
