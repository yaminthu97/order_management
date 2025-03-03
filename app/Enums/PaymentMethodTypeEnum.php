<?php

namespace App\Enums;

/**
 * 支払方法種類
 */
enum PaymentMethodTypeEnum: int
{
    case OTHER = 0;
    case CASH_ON_DELIVERY = 10;
    case CREDIT_CARD = 20;
    case PAY_LATER = 30;
    case BANK = 40;

    public function label(): string
    {
        return match($this) {
            self::OTHER => 'その他',
            self::CASH_ON_DELIVERY => '代金引換',
            self::CREDIT_CARD => 'クレジットカード',
            self::PAY_LATER => '後払い.com',
            self::BANK => '銀行振込',
        };
    }
}
