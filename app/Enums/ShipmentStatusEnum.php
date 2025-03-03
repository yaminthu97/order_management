<?php

namespace App\Enums;

/**
 * 出荷ステータス
 */
enum ShipmentStatusEnum: int
{
    // 出荷未連携 = NULL;
    case LINKED = 1;
    case INSTRUCTED = 2;
    case SLIP_ISSUED = 3;
    case INSPECTED = 4;
    case SHIPPED = 5;
    
    public function label(): string
    {
        return match($this) {
            self::LINKED => '出荷連携済',
            self::INSTRUCTED => '出荷指示済',
            self::SLIP_ISSUED => '伝票発行済',
            self::INSPECTED => '検品済',
            self::SHIPPED => '発送済',
        };
    }
}