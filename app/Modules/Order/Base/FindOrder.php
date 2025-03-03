<?php
namespace App\Modules\Order\Base;

use App\Models\Order\Base\OrderHdrModel;

class FindOrder implements FindOrderInterface
{
    /**
     * 受注情報取得
     *
     * @param int $orderId 受注ID
     */
    public function execute(?int $orderId)
    {
        $query = OrderHdrModel::query();
        $query->with([
            'orderDestination', // 受注送付先
            'orderDestination.orderDtl', // 受注明細
            //
        ]);

        // $orderIdがnullの場合
        if ($orderId === null) {
            $order = new OrderModel();
            // register_destination リレーションをロード
            $order['register_destination'] = [];
            return $order;
        }

        // $orderIdがnullでない場合
        return $query->find($orderId) ?? new OrderModel();
    }
}
