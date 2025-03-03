<?php

namespace App\Modules\Claim\Base;

use App\Exceptions\DataNotFoundException;
use App\Models\Order\Gfh1207\OrderHdrModel;
use App\Models\Order\Base\OrderDrlSkuModel;
use App\Models\Claim\Gfh1207\BillingHdrModel;
use App\Services\EsmSessionManager;

use App\Enums\AvailableFlg;
use App\Enums\ProgressTypeEnum;
use App\Enums\BillingDetailTypeEnum;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Prompts\Progress;

use App\Modules\Claim\Base\UpdateBillingHdrInterface;

class UpdateBillingHdr implements UpdateBillingHdrInterface
{
    /**
     * ESMセッション管理クラス
     */
    protected $esmSessionManager;

    public function __construct(
        EsmSessionManager $esmSessionManager
    ) {
        $this->esmSessionManager = $esmSessionManager;
    }

    /**
     * 受注情報更新
     *
     * @param ?int $orderId 受注基本ID
     */
    public function execute(int $orderId)
    {
        try{
            $orderHdr = OrderHdrModel::find($orderId);
            // トランザクション開始
            $billingHdr = DB::transaction(function () use ($orderHdr) {
                {
                    // 同一 t_order_hdr_id の請求明細を取得
                    $oldBillingHdr = BillingHdrModel::where('t_order_hdr_id', '=', $orderHdr->t_order_hdr_id)
                        ->where('is_available', '=', AvailableFlg::Available->value)
                        ->orderBy('history_no', 'desc')->get();
                    // history_no の最大値を取得
                    $params['history_no'] = 1;
                    if (count($oldBillingHdr) > 0) {
                        $params['history_no'] = $oldBillingHdr[0]->history_no + 1;
                    }
                    // $oldBillingHdr の is_available を無効にする
                    foreach ($oldBillingHdr as $oldRow) {
                        $oldRow->is_available = AvailableFlg::NotAvailable->value;
                        $oldRow->save();
                    }

                    // 請求明細新規作成
                    $billingHdr = new BillingHdrModel();
                    $billingHdr = $this->fillBillingHdrModel($billingHdr, $orderHdr, $params);
                    $billingHdr->save();
    
                    // 受注基本テーブルの請求基本IDを更新
                    $orderHdr->t_billing_hdr_id = $billingHdr->t_billing_hdr_id;
                    $orderHdr->save();

                    return $billingHdr;
                }
            });
            return $billingHdr;
        } catch (ModelNotFoundException $e){
            throw new DataNotFoundException('受注情報が見つかりませんでした。');
        }
    }
    
    protected function fillBillingHdrModel($billingHdr, $orderHdr, $params) {
        $orderHdr->load(
            'orderDestination',
            'orderDestination.orderDtl',
            'orderDestination.orderDtl.orderDtlAttachmentItem',
            'orderDestination.orderDtl.orderDtlNoshi'
        );

        $editParams['m_account_id'] = $orderHdr->m_account_id;
        $editParams['t_order_hdr_id'] = $orderHdr->t_order_hdr_id;
        $editParams['history_no'] = $params['history_no'] ?? 1;
        $editParams['is_available'] = AvailableFlg::Available->value;
        $editParams['invoiced_customer_id'] = $orderHdr->m_cust_id_billing ?? 0;
        $editParams['invoiced_customer_name_kanji'] = $orderHdr->billing_name ?? '';
        $editParams['postal'] = $orderHdr->billing_postal ?? '';
        $editParams['address1'] = $orderHdr->billing_address1 ?? '';
        $editParams['address2'] = $orderHdr->billing_address2 ?? '';
        $editParams['address3'] = $orderHdr->billing_address3 ?? '';
        $editParams['address4'] = $orderHdr->billing_address4 ?? '';
        $editParams['corporate_kanji'] = $orderHdr->billing_corporate_name ?? '';
        $editParams['corporate_kana'] = '';
        $editParams['division_name'] = $orderHdr->billing_division_name ?? '';
        $editParams['corporate_tel'] = '';
        $editParams['note'] = $orderHdr->order_comment ?? '';

        $editParams['billing_amount'] = $orderHdr->order_total_price;
        $editParams['tax_excluded_price'] = $orderHdr->sell_total_price;
        $editParams['tax_price'] = $orderHdr->tax_price;
        $editParams['standard_tax_price'] = $orderHdr->standard_tax_price;
        $editParams['reduce_tax_price'] = $orderHdr->reduce_tax_price;
        $editParams['standard_tax_excluded_total_price'] = $orderHdr->standard_total_price;
        $editParams['reduce_tax_excluded_total_price'] = $orderHdr->reduce_total_price;
        $editParams['discount_amount'] = $orderHdr->discount;
        $editParams['standard_discount'] = $orderHdr->standard_discount;
        $editParams['reduce_discount'] = $orderHdr->reduce_discount;
        $editParams['tax_excluded_fee'] = $orderHdr->payment_fee;
        $editParams['tax_excluded_shipping_fee'] = $orderHdr->shipping_fee;
        
        $editParams['m_payment_types'] = $orderHdr->m_payment_types_id;
        $editParams['output_count'] = 0;
        $editParams['remind_count'] = 0;

        // $detail_info
        $editParams['detail_info'] = json_encode($this->createBillingJsonData($params, $orderHdr));

        $billingHdr->fill($editParams);
        return $billingHdr;
    }

    protected function createBillingJsonData($params, $orderHdr) {
        $result = [];
        $destinations = [];
        foreach ($orderHdr->orderDestination as $destination) {
            $billing_details = [];
            foreach ($destination->orderDtl as $orderDtl) {
                if ($orderDtl->tax_rate == 0.08) {
                    $display_name = '※ ' . $orderDtl->sell_name;
                } else {
                    $display_name = $orderDtl->sell_name;
                }
                // 熨斗データ
                if (isset($orderDtl->orderDtlNoshi)) {
                    $noshi_omotegaki = $orderDtl->orderDtlNoshi->omotegaki ?? '';
                    $noshi_names = [];
                    if ($orderDtl->orderDtlNoshi->noshi_name1) {
                        $noshi_names[] = $orderDtl->orderDtlNoshi->noshi_name1;
                    }
                    if ($orderDtl->orderDtlNoshi->noshi_name2) {
                        $noshi_names[] = $orderDtl->orderDtlNoshi->noshi_name2;
                    }
                    if ($orderDtl->orderDtlNoshi->noshi_name3) {
                        $noshi_names[] = $orderDtl->orderDtlNoshi->noshi_name3;
                    }
                    if ($orderDtl->orderDtlNoshi->noshi_name4) {
                        $noshi_names[] = $orderDtl->orderDtlNoshi->noshi_name4;
                    }
                    if ($orderDtl->orderDtlNoshi->noshi_name5) {
                        $noshi_names[] = $orderDtl->orderDtlNoshi->noshi_name5;
                    }
                    $noshi_addressee = implode('/', $noshi_names);
                } else {
                    $noshi_omotegaki = '';
                    $noshi_addressee = '';
                }
                // 商品明細
                $billing_details[] = [
                    'detail_type' => BillingDetailTypeEnum::PRODUCT_DTL->value, // 商品明細
                    'display_code' => $orderDtl->sell_cd,
                    'display_name' => $display_name,
                    'unit_price' => $orderDtl->order_sell_price,
                    'quantity' => $orderDtl->order_sell_vol,
                    'amount' => $orderDtl->order_sell_vol * $orderDtl->order_sell_price,
                    'tax_rate' => $orderDtl->tax_rate,
                    'display_flag' => true,
                    'order_id' => $orderDtl->t_order_hdr_id,
                    'destination_id' => $destination->t_order_destination_id,
                    'order_dtl_id' => $orderDtl->t_order_dtl_id,
                    'order_attachment_item_id' => null,
                    'noshi_omotegaki' => $noshi_omotegaki,
                    'noshi_addressee' => $noshi_addressee,
                ];
                // 付属品
                if (isset($orderDtl->orderDtlAttachmentItem)) {
                    foreach ($orderDtl->orderDtlAttachmentItem as $attachmentItem) {
                        $billing_details[] = [
                            'detail_type' => BillingDetailTypeEnum::ATTACHMENT_ITEM->value, // 付属品
                            'display_code' => $attachmentItem->attachment_item_cd,
                            'display_name' => $attachmentItem->attachment_item_name,
                            'unit_price' => 0,
                            'quantity' => $attachmentItem->attachment_vol,
                            'amount' => 0,
                            'tax_rate' => 0,
                            'display_flag' => $attachmentItem->display_flg,
                            'order_id' => $orderDtl->t_order_hdr_id,
                            'destination_id' => $destination->t_order_destination_id,
                            'order_dtl_id' => $orderDtl->t_order_dtl_id,
                            'order_attachment_item_id' => $attachmentItem->t_order_dtl_attachment_item_id,
                            'noshi_omotegaki' => null,
                            'noshi_addressee' => null,
                        ];
                    }
                }
            }
            // 送料
            if ($destination['shipping_fee'] > 0) {
                $billing_details[] = [
                    'detail_type' => BillingDetailTypeEnum::SHIPPING_FEE->value, // 送料
                    'display_code' => '',
                    'display_name' => '送料',
                    'unit_price' => $destination->shipping_fee,
                    'quantity' => 1,
                    'amount' => $destination->shipping_fee,
                    'tax_rate' => 0.1,
                    'display_flag' => true,
                    'order_id' => $destination->t_order_hdr_id,
                    'destination_id' => $destination->t_order_destination_id,
                    'order_dtl_id' => null,
                    'order_attachment_item_id' => null,
                    'noshi_omotegaki' => null,
                    'noshi_addressee' => null,
                ];
            }
            // 手数料
            // 温度帯から手数料名を取得
            $temperature_fee = $destination->payment_fee;
            if ($temperature_fee > 0) {
                if ($destination->total_temperature_zone_type == 0) {
                    $temperature_fee_text = '手数料';
                } else if ($destination->total_temperature_zone_type == 1) {
                    $temperature_fee_text = 'クール手数料';
                } else if ($destination->total_temperature_zone_type == 2) {
                    $temperature_fee_text = 'チルド手数料';
                }
                $billing_details[] = [
                    'detail_type' => BillingDetailTypeEnum::PAYMENT_FEE->value, // 手数料
                    'display_code' => '',
                    'display_name' => $temperature_fee_text,
                    'unit_price' => $temperature_fee,
                    'quantity' => 1,
                    'amount' => $temperature_fee,
                    'tax_rate' => 0.1,
                    'display_flag' => true,
                    'order_id' => $destination->t_order_hdr_id,
                    'destination_id' => $destination->t_order_destination_id,
                    'order_dtl_id' => null,
                    'order_attachment_item_id' => null,
                    'noshi_omotegaki' => null,
                    'noshi_addressee' => null,
                ];
            }
        
            // 配送先情報
            $destination_address = implode(' ', array_filter([
                $destination->address1,
                $destination->address2,
                $destination->address3,
                $destination->address4,
            ]));
            $destinations[] = [
                'destination_id' => $destination->t_order_destination_id,
                'destination_name' => $destination->destination_name,
                'destination_kana' => $destination->destination_kana,
                'destination_postal_code' => $destination->destination_postal_code,
                'destination_address' => $destination_address,
                'destination_tel' => $destination->destination_tel,
                //'destination_fax' => $destination->destination_fax',
                'destination_company_name' => $destination->destination_company_name,
                'destination_division_name' => $destination->destination_division_name,
                //'destination_person_tel' => $destination->destination_person_tel,
                'sender_name' => $destination->sender_name ?? $orderHdr->order_name,
                //'deli_decision_date' => $destination->deli_decision_date,
                //'shipping_fee' => $destination->shipping_fee,
                'billing_details' => $billing_details,
            ];
        }
        $result['destinations'] = $destinations;
        return $result;
    }
}
