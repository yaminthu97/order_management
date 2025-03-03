<?php

namespace App\Enums;

/**
 * 項目名称区分
 */
enum ItemNameType: int
{
    case ReceiptType = 1;
    case CancelReason = 2;
    case CustomerRank = 3;
    case CustomerSupportStatus = 4;
    case Deposit = 5;
    case CustomerContact = 6;
    case CustomerSupportType = 7;
    case StockModifyReason = 8;
    case SalesContact = 9;
    case ContactType = 10;
    case SupportResult = 11;
    case AttachmentCategory = 12;
    case AttachmentGroup = 13;
    case NoshiSize = 14;
    case CustomerType = 15;

    public function label(): string
    {
        return match($this){
            self::ReceiptType => '受注方法',
            self::CancelReason => 'キャンセル理由',
            self::CustomerRank => '顧客ランク',
            self::CustomerSupportStatus => '顧客対応ステータス',
            self::Deposit => '入金科目',
            self::CustomerContact => '顧客対応連絡方法',
            self::CustomerSupportType => '顧客対応分類',
            self::StockModifyReason => '在庫訂正理由',
            self::SalesContact => '販売窓口',
            self::ContactType => '問い合わせ内容種別',
            self::SupportResult => '対応結果',
            self::AttachmentCategory => '付属品カテゴリ',
            self::AttachmentGroup => '付属品グループ',
            self::NoshiSize => '熨斗サイズ',
            self::CustomerType => '顧客区分',
        };
    }
}