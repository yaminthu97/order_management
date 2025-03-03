<?php
namespace App\Modules\Order\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Exceptions\DataNotFoundException;
use App\Models\Order\Gfh1207\DeliveryModel;
use App\Modules\Order\Base\FindOrderDeliveryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FindOrderDelivery implements FindOrderDeliveryInterface
{
    /**
     * 出荷情報取得
     *
     * @param int $deliveryId 出荷ID
     */
    public function execute(int $deliveryId)
    {
        ModuleStarted::dispatch(__CLASS__, compact('deliveryId'));
        try{
            $query = DeliveryModel::query();
            $query->with([
                'order',    // 受注基本
                'orderDestination', // 受注送付先
                'deliveryDetails', // 出荷詳細
                'deliveryDetails.amiPage', // 商品ページマスタ
                'shippingLabels', // 出荷ラベル
                'deliveryType', // 配送方法マスタ
            ]);
            $delivery =$query->findOrFail($deliveryId);
        }catch(ModelNotFoundException $e){
            ModuleFailed::dispatch(__CLASS__, compact('deliveryId'), $e);
            throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => '出荷情報', 'id' => $deliveryId]), 0, $e);
        }
        ModuleCompleted::dispatch(__CLASS__, [$delivery->toArray()]);
        return $delivery;
    }
}
