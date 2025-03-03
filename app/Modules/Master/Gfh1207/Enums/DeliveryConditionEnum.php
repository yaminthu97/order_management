<?php

namespace App\Modules\Master\Gfh1207\Enums;

/**
 * 配送条件
 */
enum DeliveryConditionEnum: int
{
    case NONE = 0;
    case PAID = 1;
    case BILLED = 2;

    public function label(): string
    {
        return match($this) {
            self::NONE => 'なし',
            self::PAID => '入金済',
            self::BILLED => '請求済'
        };
    }
}
