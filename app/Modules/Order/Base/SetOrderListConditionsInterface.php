<?php

namespace App\Modules\Order\Base;

interface SetOrderListConditionsInterface
{
    /**
     * 取得処理
     */
    public function execute(array $conditions);
}
