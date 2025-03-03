<?php

namespace App\Modules\Order\Base;

interface RetrieveDeliveryInfoInterface
{
    /**
     * 出荷情報検索処理
     */
    public function execute($key);
}
