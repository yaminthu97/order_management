<?php

namespace App\Http\Controllers\Order\Api;

use App\Modules\Order\Base\SearchDeliveryFees;
use App\Modules\Warehouse\Base\SearchWarehousesInterface;
use App\Modules\Order\Base\SearchDeliveryFeesInterface;
use Illuminate\Http\Request;

class GetShippingFeeController
{
    public function search(
        Request $request,
        SearchWarehousesInterface $searchWarehouses,
        SearchDeliveryFeesInterface $searchDeliveryFees,
    ) {
        $req = $request->all();
        // 倉庫ID m_warehouse_id  が未設定ならば倉庫引当順 m_warehouse_priority が一番小さい倉庫を取得する
        if (!isset($req['m_warehouse_id'])) {
            $req['m_warehouse_id'] = $searchWarehouses->execute([], [
                'sorts' => 'm_warehouse_priority',
            ])->first()->m_warehouses_id;
        }

        // m_warehouse_id, m_delivery_types_id, m_prefecture_id が必須です
        if (!isset($req['m_warehouse_id'])) {
            return response()->json([
                'error' => 'm_warehouse_id is required',
            ], 400);
        }
        if (!isset($req['m_delivery_types_id'])) {
            return response()->json([
                'error' => 'm_delivery_types_id is required',
            ], 400);
        }
        if (!isset($req['m_prefecture_id'])) {
            return response()->json([
                'error' => 'm_prefecture_id is required',
            ], 400);
        }

        // 倉庫ID m_warehouse_id と 配送方法マスタID m_delivery_types_id と都道府県ID m_prefecture_id から m_delivery_fees を取得する
        $conditions = [
            'm_warehouse_id' => $req['m_warehouse_id'] ?? null,
            'm_delivery_types_id' => $req['m_delivery_types_id'] ?? null,
            'm_prefecture_id' => $req['m_prefecture_id'] ?? null,
        ];

        // モジュール
        $results = $searchDeliveryFees->execute($conditions);

        // 送料(税込) delivery_fee を返却する
        $fee = null;
        if (count($results) === 1) {
            $fee = $results[0]->delivery_fee;
        } else {
            return response()->json([
                'error' => 'm_delivery_fees is not found',
            ], 404);
        }

        return response()->json([
            'delivery_fee' => $fee,
        ]);
    }
}
