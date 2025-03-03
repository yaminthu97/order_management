<?php

namespace App\Enums;

/**
 * 項目コード（入金科目）
 */
enum PaymentSubjectItemCodeEnum: string
{
    case BANK = '100';
    case COLLECT = '200';
    case CREDIT = '300';
    case CONVENIENCE = '600';
    case POST_OFFICE = '610';
    case CASH = '700';
    case RETURN = '900';

    public function label(): string
    {
        return match($this){
            self::BANK => '銀行振込',
            self::COLLECT => 'コレクト',
            self::CREDIT => 'クレジットカード',
            self::CONVENIENCE => 'コンビニ支払',
            self::POST_OFFICE => 'ゆうちょ銀行',
            self::CASH => '現金',
            self::RETURN => '返金',
        };
    }
}