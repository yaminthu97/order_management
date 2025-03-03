<?php

namespace App\Enums;

/**
 * (検索用)削除データを含むかフラグ
 */
enum DeleteIncludeType: int
{
    case OFF = 0;
    case ON = 1;



    public function label(): string
    {
        return match($this) {
            self::OFF => '含まない',
            self::ON => '含む',
        };
    }
}
