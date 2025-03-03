<?php

namespace App\Enums;

/**
 * 要注意顧客区分
 */
enum AlertCustTypeEnum: int
{
    case NO_ALERT = 0;
    case ATTENTION = 1;
    case BANNED = 2;



    public function label(): string
    {
        return match($this) {
            self::NO_ALERT => '通常',
            self::ATTENTION => '要確認',
            self::BANNED => '受注不可',
        };
    }
}
