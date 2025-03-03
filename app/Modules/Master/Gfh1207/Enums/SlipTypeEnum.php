<?php

namespace App\Modules\Master\Gfh1207\Enums;

/**
 * 伝票種別名一覧
 */
enum SlipTypeEnum: string
{
    case YAMATO_SHIPMENT_PAID = 'ヤマト発払';
    case COOL_SHIPMENT_PAID = 'クール発払';
    case COLLECT = 'コレクト';
    case COOL_COLLECT = 'クールコレクト';
    case OWN_SHIPMENT = '自社便';
    case OTHER = 'その他';
    case NO_SHIPMENT = '出荷無し';
}
