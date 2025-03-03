<?php

namespace App\Enums;

/**
 * 与信区分
 */
enum CreditTypeEnum: int
{
    case UNPROCESSED = 0;
    case CREDIT_NG = 1;
    case CREDIT_OK = 2;
    case EXCLUDED = 9;
    
    public function label(): string
    {
        return match($this) {
            self::UNPROCESSED => '未処理',
            self::CREDIT_NG => '与信NG',
            self::CREDIT_OK => '与信OK',
            self::EXCLUDED => '対象外',
        };
    }
}
