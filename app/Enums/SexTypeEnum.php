<?php

namespace App\Enums;

/**
 * 性別区分
 */
enum SexTypeEnum: int
{
    case UNKNOWN = 0;
    case MALE = 1;
    case FEMALE = 2;



    public function label(): string
    {
        return match($this) {
            self::UNKNOWN => '不明',
            self::MALE => '男性',
            self::FEMALE => '女性',
        };
    }
}
