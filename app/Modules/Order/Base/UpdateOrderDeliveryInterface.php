<?php
namespace App\Modules\Order\Base;

interface UpdateOrderDeliveryInterface
{
    /**
     * 出荷情報更新
     *
     * @param int $deliveryId 出荷ID
     * @param array $params 更新情報
     */
    public function execute(int $deliveryId, array $params);
}
