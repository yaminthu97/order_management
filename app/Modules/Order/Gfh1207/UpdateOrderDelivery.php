<?php
namespace App\Modules\Order\Gfh1207;

use App\Exceptions\DataNotFoundException;
use App\Models\Order\Gfh1207\DeliveryModel;
use App\Models\Order\Gfh1207\ShippingLabelModel;
use App\Modules\Order\Base\UpdateOrderDeliveryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        try{
            $delivery = DeliveryModel::findOrFail($deliveryId);
            // トランザクション開始
            $delivery = DB::transaction(function () use ($delivery, $params) {
                // 出荷情報更新
                $delivery->fill($params);
                $delivery->save();

                //送り状実績更新
                try{
                    $shippingLabelNumbers = array_keys($params['shipping_label_numbers']);
                    $shippingLabels = ShippingLabelModel::find($shippingLabelNumbers);

                    $foundIds = $shippingLabels->pluck('t_shipping_label_id')->toArray();
                    // 見つからなかった送り状情報のIDを取得
                    $missingIds = array_diff($shippingLabelNumbers, $foundIds);

                    if (!empty($missingIds)) {
                        $missingIdsImploded = implode(',', $missingIds);
                        throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => '送り状情報', 'id' => $missingIdsImploded]));
                    }
                }catch(ModelNotFoundException $e){
                    // DataNotFoundExceptionは外側でcatchされないため、そのまま返っていく
                    $keysImploded = implode(',', array_keys($params['shipping_label_numbers']));
                    throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => '送り状情報', 'id' => $keysImploded]), 0, $e);
                }
                foreach ($shippingLabels as $shippingLabel) {
                    $shippingLabel->shipping_label_number = $params['shipping_label_numbers'][$shippingLabel->t_shipping_label_id];
                    $shippingLabel->three_temperature_zone_type = $params['three_temperature_zone_types'][$shippingLabel->t_shipping_label_id];
                    $shippingLabel->save();
                }

                return $delivery->refresh(['shippingLabels']);
            });
        }catch(ModelNotFoundException $e){
            throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => '出荷情報', 'id' => $deliveryId]), 0, $e);
        }

        return $delivery;
    }
}
