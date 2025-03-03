<?php

namespace App\Modules\Order\Gfh1207\Enums;

use App\Modules\Order\Base\Enums\OrderCustomerImportTypeEnumInterface;

enum OrderCustomerImportTypeEnum: int implements OrderCustomerImportTypeEnumInterface
{
    case IMPORT_ORDER_DATA = 1;
    case IMPORT_CUSTOMER_DATA = 2;

    public function label(): string
    {
        return match($this) {
            self::IMPORT_ORDER_DATA => '受注データ取込',
            self::IMPORT_CUSTOMER_DATA => '顧客データ取込'
        };
    }
}
