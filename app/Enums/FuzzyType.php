<?php

namespace App\Enums;

/**
 * (検索用)あいまい検索フラグ
 */
enum FuzzyType: int
{
    case OFF = 0;
    case ON = 1;



    public function label(): string
    {
        return match($this) {
            self::OFF => '',
            self::ON => 'あいまい検索',
        };
    }
}
