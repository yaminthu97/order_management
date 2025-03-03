<?php

namespace App\Http\Middleware;

use App\Services\TenantDatabaseManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SetTenantMiddlware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $account = session('OperatorInfo');
        $accountCode = $account['account_cd'];
        if (app()->environment('testing')) {
            // テスト環境の場合
            TenantDatabaseManager::setTenantConnection($accountCode.'_db_testing');
        } {
            TenantDatabaseManager::setTenantConnection($accountCode.'_db');
        }
        return $next($request);
    }
}
