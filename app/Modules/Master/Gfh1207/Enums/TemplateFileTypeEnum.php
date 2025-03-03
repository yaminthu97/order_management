<?php

namespace App\Modules\Master\Gfh1207\Enums;

/**
 * レポート テンプレート ファイル タイプ リスト
 */
enum TemplateFileTypeEnum: string
{
    case ORDER_PROCESSING = '100';
    case SHIPPING_OPERATION = '200';
    case BILLING_AND_RECEIPT = '300';
    case EXTERNAL_SALE = '400';

    public function label(): string
    {
        return match($this) {
            self::ORDER_PROCESSING => '受注業務',
            self::SHIPPING_OPERATION => '出荷業務',
            self::BILLING_AND_RECEIPT => '請求・領収',
            self::EXTERNAL_SALE => '外販・客相・販促',
        };
    }

}
