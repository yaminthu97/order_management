<?php

namespace App\Enums;

/**
 * 受注進捗区分の一覧と名称
 */
enum ProgressTypeEnum: int
{
    case PendingConfirmation = 0;
    case PendingCredit = 10;
    case PendingPrepayment = 20;
    case PendingAllocation = 30;
    case PendingShipment = 40;
    case Shipping = 50;
    case Shipped = 60;
    case PendingPostPayment = 70;
    case Completed = 80;
    case Cancelled = 90;
    case Returned = 100;

    public function label(): string
    {
        return match($this) {
            self::PendingConfirmation => '確認待',
            self::PendingCredit => '与信待',
            self::PendingPrepayment => '前払入金待',
            self::PendingAllocation => '引当待',
            self::PendingShipment => '出荷待',
            self::Shipping => '出荷中',
            self::Shipped => '出荷済',
            self::PendingPostPayment => '後払入金待',
            self::Completed => '完了',
            self::Cancelled => 'キャンセル',
            self::Returned => '返品',
        };
    }
}