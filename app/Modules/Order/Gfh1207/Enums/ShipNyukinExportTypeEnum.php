<?php

namespace App\Modules\Order\Gfh1207\Enums;

use App\Modules\Order\Base\Enums\ShipNyukinExportTypeEnumInterface;

enum ShipNyukinExportTypeEnum: int implements ShipNyukinExportTypeEnumInterface
{
    case SHIP_EXPORT = 1;
    case NYUKIN_EXPORT = 2;

    public function label(): string
    {
        return match($this) {
            self::SHIP_EXPORT => '出荷確定データ出力',
            self::NYUKIN_EXPORT => '入金・受注修正データ出力',
        };
    }
}
