<?php

namespace App\Modules\Master\Base;
/**
 * 倉庫カレンダーマスタ機能の登録処理インターフェース
 */
interface SaveWarehouseCalendarInterface
{
    public function execute($request, $mainPrimaryKeyValue, $update_operator_id);
}
