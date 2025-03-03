<?php
namespace App\Modules\Order\Base;

use App\Models\Order\Base\DeliveryModel;

class FindOrderDelivery implements FindOrderDeliveryInterface
{
    /**
     * 出荷情報取得
     *
     * @param int $deliveryId 出荷ID
     */
    public function execute(int $deliveryId)
    {
        $query = DeliveryModel::query();
        $query->with([
            'order',    // 受注基本
            'orderDestination', // 受注送付先
            'deliveryDetails', // 出荷詳細
            'deliveryDetails.amiPage', // 商品ページマスタ
            'deliveryType', // 配送方法マスタ
        ]);

        return $query->find($deliveryId);
    }
}
