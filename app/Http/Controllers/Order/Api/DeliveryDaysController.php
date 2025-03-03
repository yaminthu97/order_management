<?php

namespace App\Http\Controllers\Order\Api;

use App\Modules\Warehouse\Base\SearchWarehousesInterface;
use App\Modules\Master\Base\GetYmstTimeInterface;
use Illuminate\Http\Request;

class DeliveryDaysController
{
    public function search(
        Request $request,
        SearchWarehousesInterface $searchWarehouses,
        GetYmstTimeInterface $getYmstTime,
    ) {
        $zipCode = $request->route('zip_code');
        $req = $request->all();
        // 倉庫ID m_warehouse_id が未設定ならば倉庫引当順 m_warehouse_priority が一番小さい倉庫を取得する
        if (!isset($req['m_warehouse_id'])) {
            $req['m_warehouse_id'] = $searchWarehouses->execute([], [
                'sorts' => 'm_warehouse_priority',
            ])->first()->m_warehouses_id;
        }

        // $zipCodeは必須
        if (!isset($zipCode)) {
            return response()->json([
                'error' => 'zip_code is required',
            ], 400);
        }

        // getYmstTime に m_warehouses_id と zip_code を渡して、結果を取得する
        $results = $getYmstTime->execute($req['m_warehouse_id'], $zipCode);

        // delivery_days, delivery_time を返却する
        // delivery_days 配送日数
        // delivery_time 配達可能時間帯;24時間の時がゼロ詰めで入る。99は時間指定不可
        return response()->json([
            'delivery_days' => $results->delivery_days,
            'delivery_time' => $results->delivery_time,
        ]);
    }
}
