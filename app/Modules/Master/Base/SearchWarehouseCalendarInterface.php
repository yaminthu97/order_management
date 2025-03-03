<?php

namespace App\Modules\Master\Base;

/**
 * 倉庫カレンダーマスタ機能の検索処理インターフェース
 */
interface SearchWarehouseCalendarInterface
{
    public function execute($where);
}
