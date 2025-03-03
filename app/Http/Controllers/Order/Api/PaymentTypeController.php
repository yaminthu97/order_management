<?php

namespace App\Http\Controllers\Order\Api;

use App\Modules\Master\Base\SearchPaymentTypesInterface;
use Illuminate\Http\Request;

class PaymentTypeController
{
    public function detail(
        Request $request,
        SearchPaymentTypesInterface $searchPaymentTypes,
    ) {
        // 支払方法別手数料を返却
        $paymentTypeId = $request->route('payment_type_id');

        // 支払方法マスタを取得
        $result = $searchPaymentTypes->execute(['m_payment_types_id' => $paymentTypeId])->first();

        // 支払方法マスタが存在しない場合はエラー
        if (!$result) {
            return response()->json([
                'error' => 'm_payment_types_id is not found',
            ], 404);
        }

        // 支払方法マスタから必要な項目のみ抽出する
        $result = collect($result)->only([
            "m_payment_types_id",
            "m_payment_types_name",
            "m_payment_types_code",
            "payment_type",
            "delivery_condition",
            "payment_fee",
        ]);

        return response()->json($result->toArray());
    }
}
