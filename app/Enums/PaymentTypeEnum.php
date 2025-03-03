<?php

namespace App\Enums;

/**
 * 入金区分
 */
enum PaymentTypeEnum: int
{
    case NOT_PAID = 0;
    case PARTIALLY_PAID = 1;
    case PAID = 2;
    case OVER_PAID = 3;
    case EXCLUDED = 9;
    
    public function label(): string
    {
        return match($this) {
            self::NOT_PAID => '未入金',
            self::PARTIALLY_PAID => '一部入金',
            self::PAID => '入金済み',
            self::OVER_PAID => '過入金',
            self::EXCLUDED => '対象外',
        };
    }
}
