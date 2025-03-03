<?php

namespace App\Enums;

/**
 * 後払い.com請求書送付種別
 */
enum CbBilledTypeEnum: int
{
    case INCLUDED = 0;
    case SEPARATE = 1;
    
    public function label(): string
    {
        return match($this) {
            self::INCLUDED => '同梱',
            self::SEPARATE => '別送',
        };
    }
}
