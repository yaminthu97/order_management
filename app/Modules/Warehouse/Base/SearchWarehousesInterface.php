<?php

namespace App\Modules\Warehouse\Base;

interface SearchWarehousesInterface
{
    /**
     * 検索処理
     */
    public function execute(array $condtions=[], array $options=[]);
}
