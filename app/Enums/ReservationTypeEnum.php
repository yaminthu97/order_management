<?php

namespace App\Enums;

/**
 * 在庫引当区分
 */
enum ReservationTypeEnum: int
{
    case NOT_RESERVED = 0;
    case PARTIALLY_RESERVED = 1;
    case RESERVED = 2;
    case EXCLUDED = 9;
    
    public function label(): string
    {
        return match($this) {
            self::NOT_RESERVED => '未引当',
            self::PARTIALLY_RESERVED => '一部引当',
            self::RESERVED => '引当済み',
            self::EXCLUDED => '対象外',
        };
    }
}
