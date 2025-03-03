<?php

namespace App\Modules\Claim\Gfh1207;


use Illuminate\Support\Facades\DB;

use App\Enums\AvailableFlg;
use App\Enums\BillingDetailTypeEnum;
use App\Models\Cc\Base\CustOrderSumModel;
use App\Models\Order\Gfh1207\DeliHdrModel;
use App\Models\Claim\Gfh1207\BillingOutputModel;
use App\Modules\Claim\Base\UndepositedAmountReductionShipmentInterface;

class UndepositedAmountReductionShipment implements UndepositedAmountReductionShipmentInterface
{
    public function execute(int $accountId, int $orderId, int $deliHdrId, int $value)
    {
        DB::beginTransaction();

        try{
            $billingOutputList = BillingOutputModel::where([
                'm_account_id' => $accountId,
                't_order_hdr_id' => $orderId,
                'is_available' => AvailableFlg::Available->value
            ])->get();

            if (0 < count($billingOutputList)) {
                return;
            }

            $deliHdr = DeliHdrModel::where(['deli_hdr_id' => $deliHdrId, 'billing_type' => BillingDetailTypeEnum::PRODUCT_DTL])->first();

            if (is_null($deliHdr)) {
                throw new \Exception('deli hdr id : ' . $deliHdrId . 'が不正なパラメータです。');
            }

            $custOrderSum = null;
            //$custOrderSum = CustOrderSumModel::where()->first(); //TODO 条件を実装して下さい。
            $custOrderSum->subTotalUnbilledAndAppendTotalUndeposited($value);

            DB::commit();
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            throw new DataNotFoundException('見つかりませんでした。');
        }
    }
}
