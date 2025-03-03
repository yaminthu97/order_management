<?php

namespace App\Modules\Order\Base;

interface SearchDeliveryTypesInterface
{
    /**
     * 検索処理
     */
    public function execute(array $condtions=[], array $options=[]);
}
