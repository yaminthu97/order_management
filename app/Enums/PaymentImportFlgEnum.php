<?php

namespace App\Enums;

/**
 * 入金データ種別
 * 入金取込用
 */
enum PaymentImportFlgEnum: int
{
    case MISMATCH = 0;
    case MATCH = 1;
    
    public function label(): string
    {
        return match($this) {
            self::MISMATCH => '不一致',
            self::MATCH => '一致',
        };
    }
}
