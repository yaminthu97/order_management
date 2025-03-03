<?php

namespace App\Modules\Master\Base;

interface GetYmstTimeInterface
{
    /**
     * 拡張データ取得処理
     */
    public function execute($warehouseId, $deliveryZipCode);
}
