<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class SetRequestId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // リクエストIDをUUIDで生成
        $requestId = Uuid::uuid4()->toString();

        // ログコンテクストにリクエストIDを設定
        Log::withContext(['request_id' => $requestId]);
        return $next($request);
    }
}
