<?php

namespace App\Modules\Master\Gfh1207\Enums;

use App\Modules\Master\Base\Enums\DeliveryCompanyEnumInterface;

/**
 * 配送方法タイプ
 */
enum DeliveryCompanyEnum: int implements DeliveryCompanyEnumInterface
{
    case YAMATO = 100;
    case SAGAWA_5H = 200;
    case SAGAWA_6H = 201;
    case NIHON_YUSEI = 400;
    case SEINO = 500;
    case FUKUYAMA = 600;
    case EMS = 700;
    case OTHERS = 999;

    public function label(): string
    {
        return match($this) {
            self::YAMATO => 'ヤマト運輸',
            self::SAGAWA_5H => '佐川急便（５時間帯）',
            self::SAGAWA_6H => '佐川急便（６時間帯）',
            self::NIHON_YUSEI => '日本郵政',
            self::SEINO => '西濃運輸',
            self::FUKUYAMA => '福山通運',
            self::EMS => 'ＥＭＳ',
            self::OTHERS => 'その他',
        };
    }
}
