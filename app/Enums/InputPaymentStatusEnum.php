<?php

namespace App\Enums;

/**
 * 入金取込用
 * 入金ステータス
 */
enum InputPaymentStatusEnum: int
{
    case NO_MATCH = 0;
    case FULL_MATCH = 1;
    case MULTI_MATCH = 2;
    case PART_MATCH = 3;
    
    public function label(): string
    {
        return match($this) {
            self::NO_MATCH => '該当なし',
            self::FULL_MATCH => '完全一致',
            self::MULTI_MATCH => '複数一致',
            self::PART_MATCH => '部分一致',
        };
    }
}
