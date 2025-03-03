<?php
namespace App\Modules\Order\Gfh1207;

use App\Models\Ami\Base\AmiEcPageModel;
use App\Models\Master\Gfh1207\CampaignModel;
use Illuminate\Database\Eloquent\Builder;
use App\Modules\Order\Base\AddCampaignItemInterface;

class AddCampaignItem implements AddCampaignItemInterface
{

    public function execute(array $editRow)
    {
        /*
        前提条件
			キャンペーン期間内である。
			キャンペーン対象フラグが設定されている。
			キャンペーン対象チェックがされている送付先が存在する。
			商品合計金額がキャンペーン金額を上回っている。

		上記の前提条件を満たす場合
			キャンペーン商品をキャンペーン対象となる送付先に金額に応じた個数を追加する。
			ただし、既にキャンペーン商品が追加されている場合は個数を変更する。

		上記の前提条件を満たさない場合かつ、いずれかの配送先にキャンペーン商品が付与されている場合
			該当のキャンペーン商品を削除する。
        */
        if (!isset($editRow['campaign_flg']) || $editRow['campaign_flg'] != 1) {
            // キャンペーン対象外なら終了
            return $editRow;
        }
        // 現在有効なキャンペーンを取得
        $campaign = CampaignModel::where('from_date', '<=', date('Y-m-d H:i:s'))
            ->where('to_date', '>=', date('Y-m-d H:i:s'))
            ->where('delete_flg', 0)
            ->first();
        if (!$campaign) {
            // キャンペーンが取得できない場合は終了
            return $editRow;
        }

        // キャンペーン追加の配送先を取得
        $targetId = null;
        foreach ($editRow['register_destination'] as $destination_id => $destination) {
            if (isset($destination['campaign_target_flg']) && $destination['campaign_target_flg'] == 1) {
                $targetId = $destination_id;
            }
        }
        if ($targetId === null) {
            // キャンペーン対象配送先未設定の場合は終了
            return $editRow;
        }

        // $editRow['ecs_id'] と $campaign->giving_page_cd から AmiEcPageModel を取得
        $amiEcPage = AmiEcPageModel::where('m_ecs_id', $editRow['m_ecs_id'])
            ->where('ec_page_cd', $campaign->giving_page_cd)
            ->first();
        if (!$amiEcPage) {
            // キャンペーン対象商品が取得できない場合は終了
            return $editRow;
        }

        // 商品購入金額合計が付与条件金額の何倍かを計算する
        $multiple = floor($editRow['sell_total_price'] / $campaign->giving_condition_amount);
        // 付与条件毎フラグが0の場合で$multipleが1以上の場合は上限を1とする
        if ($campaign->giving_condition_every == 0 && $multiple > 1) {
            $multiple = 1;
        }

        // 付属品グループIDを取得
        $attachment_item_group_id = 0;
        foreach ($editRow['register_destination'][$targetId]['register_detail'] as $key => $item) {
            if ($item['attachment_item_group_id']) {
                $attachment_item_group_id = $item['attachment_item_group_id'];
                break;
            }
        }

        // 既にキャンペーン商品と同一商品がある場合は削除
        foreach ($editRow['register_destination'][$targetId]['register_detail'] as $key => $item) {
            if ($item['sell_cd'] == $amiEcPage->ec_page_cd) {
                unset($editRow['register_destination'][$targetId]['register_detail'][$key]);
            }
        }

        // $multiple が1以上ならキャンペーン商品を追加
        if ($multiple >= 1) {
            $register_detail_sku = [
                'item_cd' => $amiEcPage->item_cd,
                'item_vol' => (string)$multiple,
            ];
            $register_detail = [
                "t_order_dtl_sku_id" => null,
                't_order_dtl_id' => null,
                'order_dtl_seq' => null,
                'sell_id' => (string)$amiEcPage->m_ami_ec_page_id,
                'sell_checked' => null,
                'sku_data' => null,
                'tax_rate' => $amiEcPage->tax_rate,
                'sell_cd' => $amiEcPage->ec_page_cd,
                'sell_name' => $amiEcPage->ec_page_title,
                'order_sell_price' => '0',
                'order_sell_vol' => (string)$multiple,
                'btn_delete_visible' => '1',
                'register_detail_sku' => $register_detail_sku,
                'order_sell_amount' => '0',
                "cancel_flg" => null,
                'variation_values' => null,
                "drawing_status_name" => null,
                'order_dtl_coupon_id' => null,
                't_deli_hdr_id' => null,
                'order_dtl_coupon_price' => null,
                'attachment_item_group_id' => $attachment_item_group_id,
                'order_dtl_noshi' => [],
                'order_dtl_attachment_item' => [],
                "cancel_timestamp" => null,
                "reservation_date" => null,
                'three_temperature_zone_type' => "0",
                "image_path" => $amiEcPage->page->image_path,
                "page_desc" => $amiEcPage->page->page_desc,
            ];
            $editRow['register_destination'][$targetId]['register_detail'][] = $register_detail;
        }

        return $editRow;
    }
}
