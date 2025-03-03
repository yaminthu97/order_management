<?php

namespace App\Modules\Order\Base;

interface RegisterDeliveryStatusInterface
{
    /**
     * 出荷済登録処理
     */
    public function execute(array $editRow);
}
