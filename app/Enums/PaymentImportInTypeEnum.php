<?php

namespace App\Enums;

/**
 * 銀行取込種類
 * 入金取込用
 */
enum PaymentImportInTypeEnum: int
{
    case STANDARD = 1;
    case JNB = 2;
    case ZENGIN = 3;
    case CREDIT = 4;
    case CVS = 5;
    case COLLECT = 6;
    
    public function label(): string
    {
        return match($this) {
            self::STANDARD => '銀行入金取込（標準）',
            self::JNB => '銀行入金取込（JNB）',
            self::ZENGIN => '銀行入金取込（全銀）',
            self::CREDIT => 'クレジット入金取込',
            self::CVS => 'コンビニ・郵便振込取込',
            self::COLLECT => 'コレクト入金取込',
        };
    }
}
