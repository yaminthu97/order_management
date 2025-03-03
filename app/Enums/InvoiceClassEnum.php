<?php

namespace App\Enums;

/**
 * 請求書区分
 */
enum InvoiceClassEnum: int
{
    case INVOICE_INCLUDED = 1;
    case DELIVERY_SLIP = 2;
    case GIFT_DETAILS = 3;

    public function label(): string
    {
        return match($this) {
            self::INVOICE_INCLUDED => '請求書同梱',
            self::DELIVERY_SLIP => '納品書',
            self::GIFT_DETAILS => 'ギフト明細書',
        };
    }
}