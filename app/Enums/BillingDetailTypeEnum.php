<?php

namespace App\Enums;

/**
 * 請求書明細種別
 */
enum BillingDetailTypeEnum: int
{
    case PRODUCT_DTL = 1;
    case ATTACHMENT_ITEM = 2;
    case SHIPPING_FEE = 3;
    case PAYMENT_FEE = 4;
    public function label(): string
    {
        return match($this) {
            self::PRODUCT_DTL => '商品明細',
            self::ATTACHMENT_ITEM => '付属品',
            self::SHIPPING_FEE => '送料',
            self::PAYMENT_FEE => '手数料',
        };
    }
}
