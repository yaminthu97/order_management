<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequestContent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info(__('messages.info.request_logger', ['uri'=>$request->url()]), [
            'method' => $request->method(),
            'query' => $request->query->all(),
            'body' => $request->all(),
        ]);
        return $next($request);
    }
}
