<?php

namespace App\Enums;

/**
 * 入金データ種別
 * 入金取込用
 */
enum PaymentImportDataTypeEnum: int
{
    case FLASH = 1;
    case FIX = 2;
    case CANCEL = 3;
    
    public function label(): string
    {
        return match($this) {
            self::FLASH => '速報',
            self::FIX => '確報',
            self::CANCEL => '取消',
        };
    }
}
