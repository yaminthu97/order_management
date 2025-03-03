<?php
namespace App\Modules\Order\Base;

use App\Models\Order\DeliveryModel;

/**
 * 出荷情報取得インターフェース
 */
interface FindOrderDeliveryInterface
{
    /**
     * 出荷情報取得
     *
     * @param int $deliveryId 出荷ID
     */
    public function execute(int $deliveryId);
}
