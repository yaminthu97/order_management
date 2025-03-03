<?php

namespace App\Enums;

/**
 * 出力帳票の一覧
 */
enum OrderOutputPdfNamesEnum: string
{
    case EXPPDF_TOTAL_PICKING = 'exppdf_total_picking';
    case EXPPDF_DETAIL_PICKING = 'exppdf_detail_picking';
    case EXPPDF_SUBMISSION = 'exppdf_submission';
    case EXPPDF_DIRECT_DELIVERY_ORDER_PLACEMENT = 'exppdf_direct_delivery_order_placement';
    case EXPPDF_RECEIPT = 'exppdf_receipt';
    // case EXPPDF_SAGAWA_YUUMAIL = 'exppdf_sagawa_yuumail';

    public function label(): string
    {
        return match($this) {
            self::EXPPDF_TOTAL_PICKING => 'トータルピッキングリスト',
            self::EXPPDF_DETAIL_PICKING => '個別ピッキングリスト',
            self::EXPPDF_SUBMISSION => '納品書',
            self::EXPPDF_DIRECT_DELIVERY_ORDER_PLACEMENT => '直送発注書',
            self::EXPPDF_RECEIPT => '領収書',
            // self::EXPPDF_SAGAWA_YUUMAIL => '飛脚ゆうメール便ラベル',
        };
    }

}