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
            $order = new OrderHdrModel();
            // 各リレーションを空のコレクションとして設定
            $order->setRelation('registerDestination', collect([]));
            return $order;
        }

        // $orderIdがnullでない場合
        $order = $query->find($orderId);

        if ($order === null) {
            // データが見つからない場合も同様に空のコレクションを設定
            $order = new OrderHdrModel();
        }

        // リレーションがnullの場合に空のコレクションを設定
        $order->setRelation('registerDestination', $order->registerDestination ?? collect([]));

        return $order;
    }
}
