<?php

namespace App\Http\Middleware;

use Closure;

class Logout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 認証情報を破棄
        session()->forget('OperatorInfo');
        session()->forget('AuthResponse');
        session()->forget('LoginFormValidationErrors');

        return $next($request);
    }
}
