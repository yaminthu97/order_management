<?php
namespace App\Modules\Order\Base;

use App\Exceptions\DataNotFoundException;
use App\Models\Cc\Gfh1207\CustModel;
use App\Models\Order\Base\OrderHdrModel;
use App\Models\Order\Base\OrderDestinationModel;
use App\Models\Order\Base\OrderDtlModel;
use App\Models\Ami\Base\AmiPageSkuModel;
use App\Models\Ami\Base\AmiEcPageSkuModel;
use App\Models\Ami\Base\AmiAttachmentItemModel;
use App\Models\Order\Base\OrderDtlSkuModel;
use App\Models\Order\Base\OrderDtlNoshiModel;
use App\Models\Master\Base\NoshiModel;
use App\Models\Order\Base\OrderDtlAttachmentItemModel;
use App\Models\Order\Base\OrderMemoModel;
use App\Models\Master\Base\PostalCodeModel;
use App\Models\Warehouse\Base\WarehouseModel;

use App\Models\Master\Base\PrefecturalModel;
use App\Models\Order\Base\DestinationModel;
use App\Services\EsmSessionManager;

use App\Enums\ProgressTypeEnum;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Prompts\Progress;

class UpdateOrder implements UpdateOrderInterface
{
    /**
     * ESMセッション管理クラス
     */
    protected $esmSessionManager;
    
    protected $nowTimestamp;

    public function __construct(
        EsmSessionManager $esmSessionManager
    ) {
        $this->esmSessionManager = $esmSessionManager;
    }

    /**
     * 受注情報更新
     *
     * @param ?int $orderId 受注基本ID
     * @param array $params 更新情報
     */
    public function execute(?int $orderId, array $params)
    {
        try{
            $orderHdr = OrderHdrModel::findOrNew($orderId);
            $this->nowTimestamp = Carbon::now();

            // トランザクション開始
            $orderHdr = DB::transaction(function () use ($orderHdr, $params) {
                // 受注基本
                $orderHdr->fill($params);

                // m_warehouse_priority が一番小さい倉庫1件を取得
                $warehouse = WarehouseModel::orderBy('m_warehouse_priority')->first();

                // 初期値を設定
                $orderHdr->progress_type = ProgressTypeEnum::PendingConfirmation->value;

                // fill外の値設定
                $orderHdr->standard_discount = $params['discount_price10'] ?? 0;
                $orderHdr->reduce_discount = $params['discount_price08'] ?? 0;
                $orderHdr->standard_total_price = ($params['taeget_price10'] ?? 0) - ($params['discount_price10'] ?? 0);
                $orderHdr->reduce_total_price = ($params['taeget_price08'] ?? 0) - ($params['discount_price08'] ?? 0);
                $orderHdr->standard_tax_price = $params['tax_price10'] ?? 0;
                $orderHdr->reduce_tax_price = $params['tax_price08'] ?? 0;
                $orderHdr->tax_price = $orderHdr->standard_tax_price + $orderHdr->reduce_tax_price;
                $orderHdr->m_payment_types_id = $params['m_pay_type_id'] ?? 0;

                $orderHdr->m_account_id = $this->esmSessionManager->getAccountId();
                $orderHdr->update_operator_id = $this->esmSessionManager->getAccountId();

                // 新規の場合
                if(!$orderHdr->t_order_hdr_id){
                    $orderHdr->entry_operator_id = $this->esmSessionManager->getOperatorId();
                }

                $cust = CustModel::find($orderHdr->m_cust_id);
                
                // 請求先IDが未設定ならば顧客新規作成+請求先IDの設定
                if (!$orderHdr->m_cust_id_billing) {
                    $billing_cust = CustModel::firstOrNew([
                        'm_account_id' => $orderHdr->m_account_id,
                        'name_kanji' => $orderHdr->billing_name,
                        'tel1' => $orderHdr->billing_tel1,
                        'postal' => $orderHdr->billing_postal,
                        'address1' => $orderHdr->billing_address1,
                        'address2' => $orderHdr->billing_address2,
                        'address3' => $orderHdr->billing_address3,
                        'address4' => $orderHdr->billing_address4,
                    ]);
                    // 受注先と同じ顧客情報を設定
                    $billing_cust->customer_type = $cust->customer_type;

                    // 請求先顧客情報から取得
                    $billing_cust->tel1 = $orderHdr->billing_tel1;
                    $billing_cust->tel2 = $orderHdr->billing_tel2;
                    $billing_cust->fax = $orderHdr->billing_fax;
                    $billing_cust->name_kana = $orderHdr->billing_name_kana;
                    $billing_cust->name_kanji = $orderHdr->billing_name;
                    $billing_cust->email1 = $orderHdr->billing_email1;
                    $billing_cust->email2 = $orderHdr->billing_email2;
                    $billing_cust->m_cust_runk_id = $params['billing_cust_runk_id'] ?? $cust->m_cust_runk_id;
                    $billing_cust->alert_cust_type = $params['billing_alert_cust_type'] ?? $cust->alert_cust_type;

                    $billing_cust->postal = $orderHdr->billing_postal;
                    $billing_cust->address1 = $orderHdr->billing_address1;
                    $billing_cust->address2 = $orderHdr->billing_address2;
                    $billing_cust->address3 = $orderHdr->billing_address3;
                    $billing_cust->address4 = $orderHdr->billing_address4;
                    $billing_cust->corporate_kanji = $orderHdr->billing_corporate_name;
                    $billing_cust->division_name = $orderHdr->billing_division_name;
                    $billing_cust->corporate_tel = $params['billing_corporate_tel'] ?? null;
                    $billing_cust->note = $params['billing_cust_note'] ?? null;

                    $billing_cust->m_account_id = $this->esmSessionManager->getAccountId();
                    $billing_cust->update_operator_id = $this->esmSessionManager->getOperatorId();
                    $billing_cust->entry_operator_id = $this->esmSessionManager->getOperatorId();
                    $billing_cust->save();
                    $orderHdr->m_cust_id_billing = $billing_cust->m_cust_id;
                }

                // キャンセルの場合
                if(isset($params['cancel_flg']) && $params['cancel_flg']){
                    $orderHdr->cancel_operator_id = $this->esmSessionManager->getOperatorId();
                    $orderHdr->cancel_timestamp = $this->nowTimestamp;
                    //受注明細と受注明細SKUの削除処理 
                }
                $orderHdr->save();

                // 受注メモの更新or新規作成
                $memoParams = [
                    't_order_hdr_id' => $orderHdr->t_order_hdr_id,
                    'm_account_id' => $this->esmSessionManager->getAccountId(),
                    'update_operator_id' => $this->esmSessionManager->getOperatorId(),
                    'operator_comment' => $params['operator_comment'] ?? null,
                    'billing_comment' => $params['billing_comment'] ?? null,
                ];
                // データの作成または更新
                $orderMemo = OrderMemoModel::updateOrCreate(['t_order_hdr_id' => $orderHdr->t_order_hdr_id], $memoParams);
                // 新規作成の場合
                if ($orderMemo->wasRecentlyCreated) {
                    $orderMemo->entry_operator_id = $this->esmSessionManager->getOperatorId();  // 新規の場合、entry_operator_idにIDを設定
                }
                // 変更を保存
                $orderMemo->save();


                // キャンセル以外の場合
                if(!isset($params['cancel_flg']) || !$params['cancel_flg']){
                    // 受注配送先
                    foreach ($params['register_destination'] ?? [] as $dKey => $orderDestination) {
                        // 郵便番号よりチェック時にAPIで取得した住所コード取得(area_cd)
                        $postalCode = PostalCodeModel::where('postal_code', $orderDestination['destination_postal'])->first();
                        $orderDestination['area_cd'] = $postalCode->postal_jis_code ?? null;
                        // 国外扱いの都道府県の場合、郵便番号が未入力の場合は空欄にする（更新対策）
                        
                        // 受注配送先登録
                        $orderDestinationModel = OrderDestinationModel::findOrNew($orderDestination['t_order_destination_id'] ?? null);
                        $orderDestinationModel->t_order_hdr_id = $orderHdr->t_order_hdr_id;
                        $orderDestinationModel->m_account_id = $orderHdr->m_account_id;
                        $orderDestinationModel->update_operator_id = $orderHdr->update_operator_id;
                        $orderDestinationModel->campaign_flg = $orderDestination['campaign_flg'] ?? 0;

                        $orderDestinationModel->fill($orderDestination);
                        if (!$orderDestinationModel->t_order_destination_id) {
                            $orderDestinationModel->entry_operator_id = $this->esmSessionManager->getOperatorId();
                            $orderDestinationModel->entry_timestamp = $this->nowTimestamp;
                        }

                        // 配送先名寄せ＋保存
                        // cust_id, destination_name, destination_tel が一致する DestinationModel を取得
                        $destination = DestinationModel::firstOrNew([
                            'cust_id' => $orderHdr->m_cust_id,
                            'destination_name' => $orderDestination['destination_name'],
                            'destination_tel' => $orderDestination['destination_tel']
                        ]);
                        $destination->fill($orderDestination);
                        $destination->m_account_id = $orderHdr->m_account_id;
                        $destination->update_operator_id = $orderHdr->update_operator_id;
                        if (!$destination->entry_operator_id) {
                            $destination->entry_operator_id = $orderHdr->update_operator_id;
                        }
                        $destination->save();

                        $orderDestinationModel->destination_id = $destination->m_destination_id;
                        $orderDestinationModel->save();

                        // 受注明細
                        $order_dtl_seq = 0;
                        foreach ($orderDestination['register_detail'] ?? [] as $dtlKey => $orderDtl) {
                            $order_dtl_seq++;
                            // 商品ページマスタなどから値を取得
                            // 更新+明細取り消し時は取消担当者を設定し、SKUの削除設定
                        
                            // 受注明細登録
                            $orderDtlModel = OrderDtlModel::findOrNew($orderDtl['t_order_dtl_id'] ?? null);
                            $orderDtlModel->t_order_hdr_id = $orderHdr->t_order_hdr_id;
                            $orderDtlModel->m_account_id = $orderHdr->m_account_id;
                            $orderDtlModel->ecs_id = $orderHdr->m_ecs_id;
                            $orderDtlModel->update_operator_id = $orderHdr->update_operator_id;
                            $orderDtlModel->t_order_destination_id = $orderDestinationModel->t_order_destination_id;
                            $orderDtlModel->order_destination_seq = $orderDestinationModel->order_destination_seq;
                            $orderDtlModel->order_dtl_seq = $order_dtl_seq;
                            $orderDtlModel->attachment_item_group_id = $orderDtl['attachment_item_group_id'];
                            $orderDtlModel->fill($orderDtl);
                            if (!$orderDtlModel->t_order_dtl_id) {
                                $orderDtlModel->entry_operator_id = $this->esmSessionManager->getOperatorId();
                                $orderDtlModel->entry_timestamp = $this->nowTimestamp;
                            }

                            // 受注明細取り消し
                            if (($orderDtl['cancel_flg'] ?? 0 == 1)) {
                                $orderDtlModel->cancel_operator_id = $this->esmSessionManager->getOperatorId();
                                $orderDtlModel->cancel_timestamp = $this->nowTimestamp;
                            }
                            // order_bundle_type
                            // direct_delivery_type
                            // m_supplier_id
                            // temperature_type
                            $orderDtlModel->save();

                            // 既存の付属品を削除
                            $oldAttachments = OrderDtlAttachmentItemModel::where('t_order_dtl_id', $orderDtl['t_order_dtl_id'])->get();
                            foreach ($oldAttachments as $oldAttachment) {
                                $oldAttachment->delete();
                            }
                            // 受注明細付属品登録
                            foreach ($orderDtl['order_dtl_attachment_item'] ?? [] as $attachmentItem) {
                                // m_ami_attachment_item_id から AmiAttachmentItemModel を取得
                                $amiAttachmentItem = AmiAttachmentItemModel::find($attachmentItem['m_ami_attachment_item_id']);
                                if ($amiAttachmentItem === null) {
                                    // attachment_item_cd と attachment_item_name から取得
                                    $amiAttachmentItem = AmiAttachmentItemModel::where('attachment_item_cd', $attachmentItem['attachment_item_cd'])
                                        ->where('attachment_item_name', $attachmentItem['attachment_item_name'])
                                        ->first();
                                }
                                $orderDtlAttachmentItem = new OrderDtlAttachmentItemModel();
                                $orderDtlAttachmentItem->fill($attachmentItem);
                                $orderDtlAttachmentItem->t_order_hdr_id = $orderHdr->t_order_hdr_id;
                                $orderDtlAttachmentItem->t_order_destination_id = $orderDestinationModel->t_order_destination_id;
                                $orderDtlAttachmentItem->t_order_dtl_id = $orderDtlModel->t_order_dtl_id;
                                $orderDtlAttachmentItem->group_id = $orderDtl['attachment_item_group_id'];
                                $orderDtlAttachmentItem->category_id = $amiAttachmentItem->category_id;

                                // 受注明細取り消し
                                if (($orderDtl['cancel_flg'] ?? 0 == 1)) {
                                    $orderDtlAttachmentItem->cancel_operator_id = $this->esmSessionManager->getOperatorId();
                                    $orderDtlAttachmentItem->cancel_timestamp = $this->nowTimestamp;
                                }
                                $orderDtlAttachmentItem->save();
                            }

                            // 受注明細熨斗
                            if (isset($orderDtl['order_dtl_noshi']['noshi_id']) && $orderDtl['order_dtl_noshi']['noshi_id'] > 0 ||
                                isset($orderDtl['order_dtl_noshi']['t_order_dtl_noshi_id']) && $orderDtl['order_dtl_noshi']['t_order_dtl_noshi_id'] > 0) {
                                
                                $orderDtlNoshi = OrderDtlNoshiModel::firstOrNew([
                                    't_order_dtl_id' => $orderDtlModel->t_order_dtl_id
                                ]);
                                // noshi_id から NoshiModel を取得
                                $noshi = NoshiModel::find($orderDtl['order_dtl_noshi']['noshi_id'] ?? $orderDtlNoshi->noshi_id);
                                $orderDtlNoshi->m_account_id = $orderHdr->m_account_id;
                                $orderDtlNoshi->t_order_hdr_id = $orderHdr->t_order_hdr_id;
                                $orderDtlNoshi->t_order_destination_id = $orderDestinationModel->t_order_destination_id;
                                $orderDtlNoshi->order_destination_seq = $orderDestinationModel->order_destination_seq;
                                $orderDtlNoshi->t_order_dtl_id = $orderDtlModel->t_order_dtl_id;
                                $orderDtlNoshi->order_dtl_seq = $orderDtlModel->order_dtl_seq;
                                $orderDtlNoshi->ecs_id = $orderDtlModel->ecs_id;
                                $orderDtlNoshi->sell_cd = $orderDtl['sell_cd'];
                                $orderDtlNoshi->count = $orderDtl['order_sell_vol'];
                                $orderDtlNoshi->attachment_item_group_id = $orderDtl['attachment_item_group_id'];
                                $orderDtlNoshi->noshi_type = $noshi->noshi_type;
                                $orderDtlNoshi->entry_operator_id = $this->esmSessionManager->getOperatorId();
                                $orderDtlNoshi->update_operator_id = $this->esmSessionManager->getOperatorId();
                                if ($orderDtl['order_dtl_noshi']['noshi_id'] == null) {
                                    unset($orderDtl['order_dtl_noshi']['noshi_id']);
                                }
                                if ($orderDtl['order_dtl_noshi']['noshi_detail_id'] == null) {
                                    unset($orderDtl['order_dtl_noshi']['noshi_detail_id']);
                                }
                                $orderDtlNoshi->fill($orderDtl['order_dtl_noshi']);
                                
                                // search_string の作成
                                $orderDtlNoshi->search_string = '';
                                foreach (['company_name', 'section_name', 'title', 'firstname', 'name', 'ruby'] as $name) {
                                    for ($i = 1; $i <= 5; $i++) {
                                        $orderDtlNoshi->search_string .= $orderDtlNoshi->{$name . $i} ?? '';
                                    }
                                }
                                
                                // 受注明細取り消し
                                if (($orderDtl['cancel_flg'] ?? 0 == 1)) {
                                    $orderDtlNoshi->cancel_operator_id = $this->esmSessionManager->getOperatorId();
                                    $orderDtlNoshi->cancel_timestamp = $this->nowTimestamp;
                                }

                                $orderDtlNoshi->save();
                            }

                            // AmiEcPageSkuModel から SKU ID を取得し、AmiPageSkuModel で一括取得
                            $amiPageSkus = AmiPageSkuModel::with(['sku'])
                                ->whereIn('m_ami_sku_id', AmiEcPageSkuModel::where('m_ami_ec_page_id', $orderDtl['sell_id'])->pluck('m_ami_sku_id'))
                                ->get();

                            foreach ($amiPageSkus as $skuKey => $orderDtlSku) {
                                // OrderDtlSkuModel から t_order_dtl_id と item_idが一致するものを取得、なければ新規作成
                                //$orderDtlSkuModel = OrderDtlSkuModel::findOrNew($orderDtlSku['t_order_dtl_sku_id'] ?? null);
                                $orderDtlSkuModel = OrderDtlSkuModel::firstOrNew([
                                    't_order_dtl_id' => $orderDtlModel->t_order_dtl_id,
                                    'item_id' => $orderDtlSku['m_ami_sku_id']
                                ]);
                                $orderDtlSkuModel->fill($orderDtlModel->toArray());
                                $orderDtlSkuModel->item_id = $orderDtlSku['m_ami_sku_id'];
                                $orderDtlSkuModel->item_cd = $orderDtlSku['sku']['sku_cd'];
                                $orderDtlSkuModel->item_vol = $orderDtlSku['sku_vol'];
                                //SKUマスタなどから値を取得
                                $orderDtlSkuModel->m_supplier_id = $orderDtlSku['sku']['m_suppliers_id'];
                                $orderDtlSkuModel->direct_delivery_type = $orderDtlSku['sku']['direct_delivery_flg'];
                                $orderDtlSkuModel->gift_type = $orderDtlSku['sku']['gift_flg'];
                                $orderDtlSkuModel->item_cost = $orderDtlSku['sku']['item_cost'];
                                $orderDtlSkuModel->m_warehouse_id = $warehouse->m_warehouses_id;
                                $orderDtlSkuModel->entry_operator_id = $this->esmSessionManager->getOperatorId();
                                $orderDtlSkuModel->update_operator_id = $this->esmSessionManager->getOperatorId();
                                
                                // 受注明細取り消し
                                if (($orderDtl['cancel_flg'] ?? 0 == 1)) {
                                    $orderDtlSkuModel->cancel_operator_id = $this->esmSessionManager->getOperatorId();
                                    $orderDtlSkuModel->cancel_timestamp = $this->nowTimestamp;
                                }
                                $orderDtlSkuModel->save();
                            }
                        }
                    }
                }
                
                // 受注明細の紐づかなくなった配送先を更新する
                return $orderHdr;
            });
        }catch(ModelNotFoundException $e){
            throw new DataNotFoundException('受注情報が見つかりませんでした。');
        }

        return $orderHdr;
    }
}
