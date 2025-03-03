<?php

namespace App\Modules\Master\Base;

interface SearchDeliveryTypeInterface
{
    /**
     * 検索処理
     */
    public function execute(array $conditions, array $options);
}
