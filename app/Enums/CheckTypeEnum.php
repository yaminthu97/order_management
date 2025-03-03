<?php

namespace App\Enums;

/**
 * 確認区分
 */
enum CheckTypeEnum: int
{
    case UNCONFIRMED = 0;
    case CONFIRMED = 2;
    case EXCLUDED = 9;
    
    public function label(): string
    {
        return match($this) {
            self::UNCONFIRMED => '未確認',
            self::CONFIRMED => '確認済み',
            self::EXCLUDED => '対象外',
        };
    }
}