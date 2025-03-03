<?php

namespace App\Enums;

/**
 * 後払い.com出荷ステータス
 */
enum CbDeliStatusEnum: int
{
    case UNPROCESSED = 0;
    case PENDING_SHIPMENT = 10;
    case SHIPMENT_COMPLETED = 11;
    case SHIPMENT_NG = 19;
    
    public function label(): string
    {
        return match($this) {
            self::UNPROCESSED => '未処理',
            self::PENDING_SHIPMENT => '出荷連携待ち',
            self::SHIPMENT_COMPLETED => '出荷連携完了',
            self::SHIPMENT_NG => '出荷連携NG',
        };
    }
}
