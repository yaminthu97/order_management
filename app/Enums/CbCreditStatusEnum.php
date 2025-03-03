<?php

namespace App\Enums;

/**
 * 後払い.com決済ステータス
 */
enum CbCreditStatusEnum: int
{
    case UNPROCESSED = 0;
    case PENDING_CREDIT = 10;
    case IN_CREDIT_PROCESS = 11;
    case CREDIT_COMPLETED = 12;
    case CREDIT_NG = 19;
    case CANCELLATION_PENDING = 90;
    case CANCELLATION_COMPLETED = 91;
    case CANCELLATION_NG = 99;
    
    public function label(): string
    {
        return match($this) {
            self::UNPROCESSED => '未処理',
            self::PENDING_CREDIT => '与信待ち',
            self::IN_CREDIT_PROCESS => '与信中',
            self::CREDIT_COMPLETED => '与信完了',
            self::CREDIT_NG => '与信NG',
            self::CANCELLATION_PENDING => '与信取消待ち',
            self::CANCELLATION_COMPLETED => 'キャンセル完了',
            self::CANCELLATION_NG => 'キャンセルNG',
        };
    }
}
