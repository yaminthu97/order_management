<?php

namespace App\Enums;

enum MenuTypeEnum: int
{
    case CONTACT_CENTER = 10;
    case ORDER = 20;
    case CLAIM = 30;
    case SHIPPING = 40;
    case PURCHASE = 50;
    case STOCK = 70;
    case SALES = 80;
    case CONTENTS = 90;

    public function label(): string
    {
        return match($this){
            self::CONTACT_CENTER => 'コンタクトセンター',
            self::ORDER => '受注',
            self::CLAIM => '債権',
            self::SHIPPING => '出荷',
            self::PURCHASE => '仕入',
            self::STOCK => '在庫',
            self::SALES => '商品・販売',
            self::CONTENTS => 'コンテンツ',
        };
    }
}