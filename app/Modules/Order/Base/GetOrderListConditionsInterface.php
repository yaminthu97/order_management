<?php

namespace App\Modules\Order\Base;

interface GetOrderListConditionsInterface
{
    /**
     * 取得処理
     */
    public function execute(array $conditions=[], array $options=[]);
}
