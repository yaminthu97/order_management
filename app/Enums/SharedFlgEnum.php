<?php

namespace App\Enums;

/**
 * 連携フラグ
 */
enum SharedFlgEnum: int
{
    case NOT_LINKED = 0;
    case LINKED = 1;
    
    public function label(): string
    {
        return match($this) {
            self::NOT_LINKED => '未連携',
            self::LINKED => '連携済'
        };
    }
}
