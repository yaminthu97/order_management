<?php

namespace App\Modules\Order\Gfh1207\Enums;

use App\Modules\Order\Base\Enums\OrderCustomerRunTypeEnumInterface;

enum OrderCustomerRunTypeEnum: int implements OrderCustomerRunTypeEnumInterface
{
    case EXECTUE_ALL = 1;
    case RECEIVE = 2;
    case IMPORT = 3;

    public function label(): string
    {
        return match($this) {
            self::EXECTUE_ALL => '全処理実行',
            self::RECEIVE => '受信のみ',
            self::IMPORT => '取込のみ'
        };
    }
}
