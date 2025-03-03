<?php

namespace App\Modules\Order\Base;

interface SearchShippingOrderInterface
{
    /**
     * 検索処理
     */
    public function execute(array $condtions=[], array $options=[]);
}
