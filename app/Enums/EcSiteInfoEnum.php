<?php

namespace App\Enums;

/**
 * ECサイト情報
 */
enum EcSiteInfoEnum: int
{
    case YAHOO = 1;
    case RAKUTEN = 3;
    case AMAZON = 4;
    case WOWMA = 5;
    case SHOP = 6;
    case FUTURESHOP = 7;
    
    // ec_type_name
    public function label(): string
    {
        return match($this) {
            self::YAHOO => 'Yahoo!ショッピング',
            self::RAKUTEN => '楽天市場',
            self::AMAZON => 'Amazon',
            self::WOWMA => 'Wowma',
            self::SHOP => '店舗',
            self::FUTURESHOP => 'futureshop',
        };
    }

    // ec_type_uri
    public function uri(): string
    {
        return match($this) {
            self::YAHOO => 'yahoo',
            self::RAKUTEN => 'rakuten',
            self::AMAZON => 'amazon',
            self::WOWMA => 'wowma',
            self::SHOP => 'shop',
            self::FUTURESHOP => 'futureshop',
        };
    }
}
