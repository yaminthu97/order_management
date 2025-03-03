<?php

namespace App\Enums;

/**
 * 出荷確定区分
 */
enum DeliDecisionTypeEnum: int
{
    case NOT_DECIDED = 0;
    case PARTIALLY_DECIDED = 1;
    case DECIDED = 2;
    case EXCLUDED = 9;
    
    public function label(): string
    {
        return match($this) {
            self::NOT_DECIDED => '未確定',
            self::PARTIALLY_DECIDED => '一部確定',
            self::DECIDED => '確定済み',
            self::EXCLUDED => '対象外',
        };
    }
}
