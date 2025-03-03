<?php

namespace App\Modules\Master\Base;

interface SearchDeliveryTypesInterface
{
    /**
     * 拡張データ取得処理
     */
    public function execute(array $conditions = [], array $options = []);
}
