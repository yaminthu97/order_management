<?php
namespace App\Modules\Order\Base;

use App\Models\Ami\Base\AmiEcPageModel;
use App\Models\Master\Gfh1207\CampaignModel;
use App\Models\Order\Gfh1207\OrderHdrModel;
use App\Models\Order\Gfh1207\OrderDestinationModel;
use App\Models\Order\Gfh1207\OrderDtlModel;
use App\Models\Order\Gfh1207\OrderDetailSkuModel;

use Illuminate\Database\Eloquent\Builder;
use App\Modules\Order\Base\UpdateCampaignItemInterface;

class UpdateCampaignItem implements UpdateCampaignItemInterface
{

    public function execute(int $orderHdrId, int $orderDestinationId): int
    {
        // OrderHdrModel から受注基本情報を取得
        $orderHdr = OrderHdrModel::find($orderHdrId);
        if (!$orderHdr) {
            return 101;
        }
        // OrderDestinationModel から配送先情報を取得
        $orderDestination = OrderDestinationModel::find($orderDestinationId);
        if (!$orderDestination) {
            return 102;
        }

        // 配送先の受注基本ID が一致しない場合は終了
        if ($orderDestination->t_order_hdr_id != $orderHdrId) {
            return 103;
        }

        /*
        前提条件
            キャンペーン期間内である。
            キャンペーン対象フラグが設定されている。
            キャンペーン対象チェックがされている送付先が存在する。
            商品合計金額がキャンペーン金額を上回っている。

        上記の前提条件を満たす場合
            キャンペーン商品をキャンペーン対象となる送付先に金額に応じた個数を追加する。
            ただし、既にキャンペーン商品が追加されている場合は削除し再追加する。
        */
        // 現在有効なキャンペーンを取得
        $campaign = CampaignModel::where('from_date', '<=', date('Y-m-d H:i:s'))
            ->where('to_date', '>=', date('Y-m-d H:i:s'))
            ->where('delete_flg', 0)
            ->first();
        if (!$campaign) {
            // キャンペーンが取得できない場合は終了
            return 104;
        }

        // $orderHdr->ecs_id と $campaign->giving_page_cd から AmiEcPageModel を取得
        $amiEcPage = AmiEcPageModel::where('m_ecs_id', $orderHdr->m_ecs_id)
            ->where('ec_page_cd', $campaign->giving_page_cd)
            ->first();
        if (!$amiEcPage) {
            // キャンペーン対象商品が取得できない場合は終了
            return 105;
        }

        // 商品購入金額合計が付与条件金額の何倍かを計算する
        $multiple = $orderHdr->sell_total_price / $campaign->giving_condition_amount;
        // 付与条件毎フラグが0の場合で$multipleが1以上の場合は上限を1とする
        if ($campaign->giving_condition_every == 0 && $multiple > 1) {
            $multiple = 1;
        }

        // $orderDestination->orderDtls にキャンペーンと同一の sell_cd を持つ商品があるか確認
        // あれば該当の orderDtl モデルを取得
        $currentOrderDtl = OrderDtlModel::where('t_order_destination_id', $orderDestinationId)
            ->where('sell_cd', $amiEcPage->ec_page_cd)
            ->first();

        // あるならば該当の orderDtl と orderDtlSku を取消
        if ($currentOrderDtl) {
            // $multiple が1以上ならキャンペーン商品数のみ変更
            if ($multiple >= 1) {
                // order_sell_vol を更新
                $currentOrderDtl->order_sell_vol = $multiple;
                $currentOrderDtl->save();
                $currentOrderDtl->orderDtlSkus()->update([
                    'sku_vol' => $multiple,
                ]);
            } else {
                // $multiple が0ならキャンペーン商品を削除
                // cancel_timestamp と cancel_user_id を設定
                $currentOrderDtl->cancel_timestamp = date('Y-m-d H:i:s');
                $currentOrderDtl->cancel_operator_id = $orderDestination->update_operator_id;
                $currentOrderDtl->save();
                $currentOrderDtl->orderDtlSkus()->update([
                    'cancel_timestamp' => date('Y-m-d H:i:s'),
                    'cancel_operator_id' => $orderDestination->update_operator_id,
                ]);
            }
        } else {
            // $multiple が1以上ならキャンペーン商品を追加し、orderDtl と orderDtlSku を登録
            if ($multiple >= 1) {
                // order_dtl_seq を計算
                $orderDtlSeq = $orderDestination->orderDtls()->max('order_dtl_seq') + 1;
    
                $orderDtl = new OrderDtlModel();
                $orderDtl->m_account_id = $orderDestination->m_account_id;
                $orderDtl->t_order_hdr_id = $orderHdrId;
                $orderDtl->t_order_destination_id = $orderDestinationId;
                $orderDtl->order_destination_seq = $orderDestination->order_destination_seq;
                $orderDtl->order_dtl_seq = $orderDtlSeq;
                $orderDtl->ecs_id = $orderHdr->m_ecs_id;
                $orderDtl->sell_id = $amiEcPage->m_ami_ec_page_id;
                $orderDtl->sell_cd = $amiEcPage->ec_page_cd;
                $orderDtl->sell_name = $amiEcPage->ec_page_title;
                $orderDtl->order_sell_price = $amiEcPage->sales_price;
                $orderDtl->order_time_sell_vol = $multiple;
                $orderDtl->order_sell_vol = $multiple;
                $orderDtl->tax_rate = $amiEcPage->tax_rate;
                $orderDtl->tax_price = 0;
                $orderDtl->entry_operator_id = $orderDestination->update_operator_id;
                $orderDtl->update_operator_id = $orderDestination->update_operator_id;
                $orderDtl->save();
    
                $amiPageSku = $amiEcPage->page->pageSku;
                foreach($amiPageSku as $pageSku) {
                    $orderDtlSku = new OrderDetailSkuModel();
                    $orderDtlSku->m_account_id = $orderDestination->m_account_id;
                    $orderDtlSku->t_order_hdr_id = $orderHdrId;
                    $orderDtlSku->t_order_destination_id = $orderDestinationId;
                    $orderDtlSku->order_destination_seq = $orderDestination->order_destination_seq;
                    $orderDtlSku->t_order_dtl_id = $orderDtl->t_order_dtl_id;
                    $orderDtlSku->order_dtl_seq = $orderDtlSeq;
                    $orderDtlSku->ecs_id = $orderHdr->m_ecs_id;
                    $orderDtlSku->sell_cd = $amiEcPage->ec_page_cd;
                    $orderDtlSku->order_sell_vol = $multiple;
                    $orderDtlSku->item_id = $pageSku->sku->m_ami_sku_id;
                    $orderDtlSku->item_cd = $pageSku->sku->sku_cd;
                    $orderDtlSku->item_vol = $pageSku->sku_vol;

                    $orderDtlSku->temperature_type = $pageSku->sku->three_temperature_zone_type;
                    $orderDtlSku->order_bundle_type = $pageSku->sku->including_package_flg;
                    $orderDtlSku->direct_delivery_type = $pageSku->sku->direct_delivery_flg;
                    $orderDtlSku->gift_type = $pageSku->sku->gift_flg;
                    $orderDtlSku->item_cost = $pageSku->sku->item_cost;

                    $orderDtlSku->entry_operator_id = $orderDestination->update_operator_id;
                    $orderDtlSku->update_operator_id = $orderDestination->update_operator_id;
                    $orderDtlSku->save();
                }
            }
        }

        // 追加した商品数を返却
        return $multiple;
    }
}
