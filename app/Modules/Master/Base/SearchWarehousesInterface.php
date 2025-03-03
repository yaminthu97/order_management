<?php

namespace App\Modules\Master\Base;

interface SearchWarehousesInterface
{
    /**
     * 拡張データ取得処理
     */
    public function execute(array $conditions = [], array $options = []);
}
