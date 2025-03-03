<?php

namespace App\Enums;

/**
 * 後払い.com請求書送付ステータス
 */
enum CbBilledStatusEnum: int
{
    case UNPROCESSED = 0;
    case PRINT_QUEUE_TRANSFERRED = 11;
    case PRINT_QUEUE_TRANSFER_NG = 12;
    case PRINT_INFO_RETRIEVED = 21;
    case PRINT_INFO_RETRIEVAL_NG = 22;
    case ISSUANCE_REPORTED = 31;
    case ISSUANCE_REPORT_NG = 32;
    case INVOICE_SENT_SEPARATELY = 40;
    
    public function label(): string
    {
        return match($this) {
            self::UNPROCESSED => '未処理',
            self::PRINT_QUEUE_TRANSFERRED => '印刷キュー転送完了',
            self::PRINT_QUEUE_TRANSFER_NG => '印刷キュー転送NG',
            self::PRINT_INFO_RETRIEVED => '印字情報取得完了',
            self::PRINT_INFO_RETRIEVAL_NG => '印字情報取得NG',
            self::ISSUANCE_REPORTED => '発行報告完了',
            self::ISSUANCE_REPORT_NG => '発行報告NG',
            self::INVOICE_SENT_SEPARATELY => '請求書別送',
        };
    }
}
