<?php
namespace App\Modules\Order\Base;

interface UpdateOrderInterface
{
    /**
     * 受注情報更新
     *
     * @param ?int $orderId 受注基本ID
     * @param array $params 更新情報
     */
    public function execute(?int $orderId, array $params);
}
