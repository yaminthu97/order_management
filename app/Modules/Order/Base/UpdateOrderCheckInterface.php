<?php
namespace App\Modules\Order\Base;

interface UpdateOrderCheckInterface
{
    /**
     * 受注情報更新チェック
     *
     * @param ?int $orderId 受注基本ID
     * @param array $params 更新情報
     */
    public function execute(?int $orderId, array $params);
}
