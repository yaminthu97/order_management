<?php

namespace App\Modules\Master\Base\Enums;

enum ItemnameType: int implements ItemnameTypeInterface
{
    case OrderMethod = 1; // 受注方法
    case CancelReason = 2; // キャンセル理由
    case CustomerRank = 3; // 顧客ランク
    case CustomerStatus = 4; // 顧客対応ステータス
    case PaymentSubject = 5; // 入金科目
    case ContactMethod = 6; // 顧客対応連絡方法
    case ContactCategory = 7; // 顧客対応分類
    case StockCorrectionReason = 8; // 在庫訂正理由
    case AttachmentGroup = 13; // 付属品グループ

    public function label(): string
    {
        return match($this) {
            self::OrderMethod => '受注方法',
            self::CancelReason => 'キャンセル理由',
            self::CustomerRank => '顧客ランク',
            self::CustomerStatus => '顧客対応ステータス',
            self::PaymentSubject => '入金科目',
            self::ContactMethod => '顧客対応連絡方法',
            self::ContactCategory => '顧客対応分類',
            self::StockCorrectionReason => '在庫訂正理由',
            self::AttachmentGroup => '付属品グループ',
        };
    }
}
