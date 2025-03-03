<?php

namespace App\Enums;

/**
 * 出荷指示区分
 */
enum DeliInstructTypeEnum: int
{
    case NOT_INSTRUCTED = 0;
    case PARTIALLY_INSTRUCTED = 1;
    case INSTRUCTED = 2;
    case EXCLUDED = 9;
    
    public function label(): string
    {
        return match($this) {
            self::NOT_INSTRUCTED => '未指示',
            self::PARTIALLY_INSTRUCTED => '一部指示',
            self::INSTRUCTED => '指示済み',
            self::EXCLUDED => '対象外',
        };
    }
}
