<?php
namespace App\Modules\Claim\Base;

interface UpdateBillingHdrInterface
{
    /**
     * 請求基本情報更新
     *
     * @param int $orderId 受注基本ID
     */
    public function execute(int $orderId);
}
