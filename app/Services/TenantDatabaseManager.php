<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantDatabaseManager
{
    public static function setTenantConnection($userCode)
    {
        // 現在の接続を取得
        $defaultConnection = config('database.connections.mysql');

        // 新しい接続設定を作成
        $newConnection = array_merge($defaultConnection, [
            'database' => $userCode,
            ]);

        // 新しい接続を設定
        config(['database.connections.mysql' => $newConnection]);

        // 接続を再設定
        DB::purge('mysql');
        DB::reconnect('mysql');

    }
}
