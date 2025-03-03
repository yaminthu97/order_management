<?php

namespace App\Enums;

/**
 * CVS店舗コード（ゆうちょ）
 */
enum CvsPostOfficeCodeEnum: string
{
    case POST_OFFICE_COUNTER = '9900';
    case POST_OFFICE_ATM = '9901';
    
    public function label(): string
    {
        return match($this){
            self::POST_OFFICE_COUNTER => 'ゆうちょ銀行または郵便局（窓口）',
            self::POST_OFFICE_ATM => 'ゆうちょ銀行または郵便局（ATM）',
        };
    }
}