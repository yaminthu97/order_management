<?php

namespace App\Enums;

/**
 * CVS店舗コード
 */
enum CvsStoreCodeEnum: string
{
    case SEVEN_ELEVEN = 'C001';
    case LAWSON = '000007';
    case FAMILY_MART = '068442';
    case MINI_STOP = '000597';
    case DAILY_YAMAZAKI = '000003';
    case SEICO_MART = '000011';
    case POPLAR = '000010';
    case SHINKIN = 'MMK001';
    case NTT_DATA = '058020';
    case LINE_PAY = '980008';
    case PAY_PAY = '980002';
    case PAY_B = '980001';
    case AU_PAY = '980003';
    case D_PAY = '980004';
    case RAKUTEN_PAY = '980005';
    
    public function label(): string
    {
        return match($this){
            self::SEVEN_ELEVEN => '（株）セブン－イレブン・ジャパン',
            self::LAWSON => '（株）ローソン',
            self::FAMILY_MART => '（株）ファミリーマート',
            self::MINI_STOP => 'ミニストップ（株）',
            self::DAILY_YAMAZAKI => '山崎製パン（株）',
            self::SEICO_MART => '（株）セイコーマート',
            self::POPLAR => '（株）ポプラ',
            self::SHINKIN => '（株）しんきん情報サービス',
            self::NTT_DATA => 'エヌ・ティ・ティ・データ（株）',
            self::LINE_PAY => 'LINE Pay（株）',
            self::PAY_PAY => 'PayPay（株）',
            self::PAY_B => 'ビリングシステム（株）',
            self::AU_PAY => 'KDDI（株）',
            self::D_PAY => '（株）NTTドコモ',
            self::RAKUTEN_PAY => '楽天ペイメント（株）',
        };
    }
}