<?php

namespace App\Http\Controllers\Order\Api;

use App\Modules\Warehouse\Base\SearchWarehousesInterface;
use App\Modules\Order\Base\SearchDeliveryFeesInterface;
use App\Modules\Master\Base\SearchDeliveryTypesInterface;
use Illuminate\Http\Request;

class DeliveryTypeController
{

    public function list(
        Request $request,
        SearchDeliveryTypesInterface $searchDeliveryTypes,
    ) {
        $results = $searchDeliveryTypes->execute();

        // $results の配列から必要な項目のみ抽出する
        $results = collect($results)->map(function($result) {
            return collect($result)->only([
                "m_delivery_types_id",
                "m_delivery_type_name",
                "delivery_type",
                "m_delivery_sort",
                "standard_fee",
                "chilled_fee",
                "frozen_fee",
            ]);
        });
        return response()->json($results->toArray());
    }
    
    public function detail(
        Request $request,
        SearchDeliveryTypesInterface $searchDeliveryTypes,
        SearchWarehousesInterface $searchWarehouses,
        SearchDeliveryFeesInterface $searchDeliveryFees,
    ) {
        // 温度帯別送料、都道府県別送料を含め、配送方法詳細を返却
        $deliTypeId = $request->route('deli_type_id');
        $req = $request->all();

        // 倉庫ID m_warehouse_id  が未設定ならば倉庫引当順 m_warehouse_priority が一番小さい倉庫を取得する
        if (!isset($req['m_warehouse_id'])) {
            $req['m_warehouse_id'] = $searchWarehouses->execute([], [
                'sorts' => 'm_warehouse_priority',
            ])->first()->m_warehouses_id;
        }

        // 配送方法マスタを取得
        $result = $searchDeliveryTypes->execute(['m_delivery_types_id' => $deliTypeId])->first();

        // 配送方法マスタが存在しない場合はエラー
        if (!$result) {
            return response()->json([
                'error' => 'm_delivery_types is not found',
            ], 404);
        }

        // 配送方法マスタから必要な項目のみ抽出する
        $result = collect($result)->only([
            "m_delivery_types_id",
            "m_delivery_type_name",
            "delivery_type",
            "m_delivery_sort",
            "standard_fee",
            "chilled_fee",
            "frozen_fee",
        ]);
        
        $conditions = [
            'm_warehouse_id' => $req['m_warehouse_id'] ?? null,
            'm_delivery_types_id' => $deliTypeId ?? null,
        ];

        // 都道府県別送料取得
        $resultFees = $searchDeliveryFees->execute($conditions);

        
        // 都道府県別送料から必要な項目のみ抽出する
        $resultFees = collect($resultFees)->map(function($resultFee) {
            return collect($resultFee)->only([
                "m_delivery_fee_id",
                "m_delivery_types_id",
                "m_warehouses_id",
                "m_prefecture_id",
                "delivery_fee",
            ]);
        });
        $result['delivery_fees'] = $resultFees;

        return response()->json($result->toArray());
    }
}
