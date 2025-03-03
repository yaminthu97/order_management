<?php
namespace App\Modules\Order\Base;

use App\Models\Order\DeliveryModel;
use Illuminate\Support\Facades\DB;

class UpdateOrderDelivery implements UpdateOrderDeliveryInterface
{
    /**
     * 出荷情報更新
     *
     * @param int $deliveryId 出荷ID
     * @param array $params 更新情報
     */
    public function execute(int $deliveryId, array $params)
    {
        throw new \Exception('未実装');
    }
}
