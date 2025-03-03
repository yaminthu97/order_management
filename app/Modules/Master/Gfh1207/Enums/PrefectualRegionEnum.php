<?php

namespace App\Modules\Master\Gfh1207\Enums;

enum PrefectualRegionEnum: int
{
    case HOKKAIDO = 1;
    case TOHOKU = 2;
    case KANTO = 3;
    case CHUBU = 4;
    case KINKI = 5;
    case CHUGOKU = 6;
    case SHIKOKU = 7;
    case KYUSHU = 8;
    case OTHER = 99;


    public function label(): string
    {
        return match($this) {
            self::HOKKAIDO => '北海道地方',
            self::TOHOKU => '東北地方',
            self::KANTO => '関東地方',
            self::CHUBU => '中部地方',
            self::KINKI => '近畿地方',
            self::CHUGOKU => '中国地方',
            self::SHIKOKU => '四国地方',
            self::KYUSHU => '九州地方',
            self::OTHER => 'その他',
        };
    }
}
