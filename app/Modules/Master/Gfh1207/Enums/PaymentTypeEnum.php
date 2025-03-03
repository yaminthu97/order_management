<?php

namespace App\Modules\Master\Gfh1207\Enums;

/**
 * 支払方法タイプ
 */
enum PaymentTypeEnum: string
{
    case BANK = 'P001';
    case PREPAYMENT_BANK = 'P002';
    case CASH_ON_DELIVERY = 'P003';
    case CREDIT_CARD = 'P004';
    case CONVENIENCE_POSTAL = 'P005';
    case PREPAYMENT_CONVENIENCE_POSTAL = 'P006';
    case CASH = 'P007';

    public function label(): string
    {
        return match($this) {
            self::BANK => '後払い 銀行振込',
            self::PREPAYMENT_BANK => '前払い 銀行振込',
            self::CASH_ON_DELIVERY => 'コレクト',
            self::CREDIT_CARD => 'クレジットカード',
            self::CONVENIENCE_POSTAL => '後払い コンビニ・郵便振込',
            self::PREPAYMENT_CONVENIENCE_POSTAL => '前払い コンビニ・郵便振込',
            self::CASH => '現金',
        };
    }
    public function billingout_label(): string
    {
        return match($this) {
            self::BANK => '銀行振込',
            self::CASH_ON_DELIVERY => 'コレクト',
            self::CREDIT_CARD => 'クレジットカード',
            self::PREPAYMENT_CONVENIENCE_POSTAL => 'コンビニ・郵便振込',
            self::PREPAYMENT_BANK => '銀行振込',
            self::CONVENIENCE_POSTAL => 'コンビニ・郵便振込',
            self::CASH => '現金',
        };
    }
}
