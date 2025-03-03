<?php

namespace App\Modules\Order\Gfh1207\Enums;

use App\Modules\Order\Base\Enums\ShippedDataReportTypeEnumInterface;

enum ShippedDataReportTypeEnum: int implements ShippedDataReportTypeEnumInterface
{
    case BY_SHIPMENT_DATE = 1;
    case BY_PRODUCT = 2;
    case BY_SKU = 3;

    public function label(): string
    {
        return match($this) {
            self::BY_SHIPMENT_DATE => '出荷未出荷一覧_出荷予定日別',
            self::BY_PRODUCT => '出荷未出荷一覧_セット商品別',
            self::BY_SKU => '出荷未出荷一覧_SKU別',
        };
    }
}
