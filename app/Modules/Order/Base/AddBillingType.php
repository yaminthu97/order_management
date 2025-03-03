<?php
namespace App\Modules\Order\Base;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Master\Base\PaymentTypeModel;
use App\Modules\Order\Base\AddBillingTypeInterface;

class AddBillingType implements AddBillingTypeInterface
{

    public function execute(array $editRow)
    {
        //請求書：届け先が請求先と同一　＆　最終出荷日　＆　後払いコンビニ、もしくは銀振であるもの

        // 請求書同梱フラグは初期値1（別送）を選択
        $editRow['cb_billed_type'] = 1;

        // 支払方法マスタを取得
        $payment_type = PaymentTypeModel::find($editRow['m_pay_type_id']);
        // 後払いかどうかをチェック（支払方法名に「後払」を含む場合）
        if (strpos($payment_type->m_payment_types_name, '後払') !== false) {
            // 請求先住所(address1+2+3+4)
            $billing_address = $editRow['billing_address1'] .
                               $editRow['billing_address2'] .
                               $editRow['billing_address3'] .
                               $editRow['billing_address4'];

            // $editRow['register_destination'] の中から出荷予定日(register_destination)が最も遅いもの（複数）を取得
            $latest_shipment_date = null;
            $latest_shipments = [];
            foreach ($editRow['register_destination'] as $destination_id => $destination) {
                // 請求書区分は一旦リセット
                $editRow['register_destination'][$destination_id]['billing_type'] = null;
                if ($destination['deli_plan_date'] >= $latest_shipment_date) {
                    $latest_shipment_date = $destination['deli_plan_date'];
                    $latest_shipments[] = $destination_id;
                }
            }

            // latest_shipments 一覧の中から住所が請求先と同じものを取得
            $invoice_destination = [];
            foreach ($latest_shipments as $latest_id) {
                $address = $editRow['register_destination'][$latest_id]['destination_address1'] .
                           $editRow['register_destination'][$latest_id]['destination_address2'] .
                           $editRow['register_destination'][$latest_id]['destination_address3'] .
                           $editRow['register_destination'][$latest_id]['destination_address4'];
                if ($address == $billing_address) {
                    $invoice_destination[] = $latest_id;
                }
            }
            
            // $invoice_destination が存在する場合、請求書区分 billing_type = 1 を設定し請求書同梱フラグを同梱に設定
            if (count($invoice_destination) >= 1) {
                $editRow['register_destination'][$invoice_destination[0]]['billing_type'] = 1;
                $editRow['cb_billed_type'] = 0;
            }
        }

        // 変更後の $req を返却
        return $editRow;
    }
}
