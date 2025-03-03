<?php

namespace App\Http\Controllers\Order\Api;

use App\Modules\Order\Base\SearchDeliveryTypesInterface;
use Illuminate\Http\Request;

class GetTempzoneFeeController
{
    public function search(
        Request $request,
        SearchDeliveryTypesInterface $search,
    ) {
        $req = $request->all();

        // 配送方法マスタID m_delivery_types_id は必須です
        if (!isset($req['m_delivery_types_id'])) {
            return response()->json([
                'error' => 'm_delivery_types_id is required',
            ], 400);
        }

        // 温度帯 temperature_zone_type は必須です
        if (!isset($req['temperature_zone_type'])) {
            return response()->json([
                'error' => 'temperature_zone_type is required',
            ], 400);
        }
        
        // 温度帯 temperature_zone_type が0, 1, 2以外ならばエラー
        if (!in_array($req['temperature_zone_type'], [0, 1, 2])) {
            return response()->json([
                'error' => 'temperature_zone_type is invalid',
            ], 400);
        }

        $conditions = [
            'm_delivery_types_id' => $req['m_delivery_types_id'] ?? null,
        ];

        // モジュール
        $results = $search->execute($conditions);

        // $destination が 1件の配列ならば、最初の要素を返す、それ以外はエラー
        if (count($results) === 1) {
            $result = $results[0];
        } else {
            return response()->json([
                'error' => 'm_ami_page is not found',
            ], 404);
        }

        // 温度帯別の手数料を返却する
        $fee = null;
        switch ($req['temperature_zone_type']) {
            case 0:
                $fee = $result->standard_fee;
                break;
            case 1:
                $fee = $result->frozen_fee;
                break;
            case 2:
                $fee = $result->chilled_fee;
                break;
        }

        return response()->json([
            'fee' => $fee,
        ]);
    }
}
