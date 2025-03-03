<?php

namespace App\Modules\Master\Gfh1207\Enums;

/**
 * 配送方法タイプ
 */
enum DeliveryTypeEnum: string
{
    case YAMATO = 'D001';
    case OWN_SHIPMENT = 'D002';
    case OTHER = 'D003';
    case NO_SHIPMENT = 'D004';

    public function label(): string
    {
        return match($this) {
            self::YAMATO => 'ヤマト',
            self::OWN_SHIPMENT => '自社便',
            self::OTHER => 'その他',
            self::NO_SHIPMENT => '出荷無し',
        };
    }
}
