<?php

namespace App\Modules\Order\Gfh1207\Enums;

use App\Modules\Order\Base\Enums\ShipNyukinRunTypeEnumInterface;

enum ShipNyukinRunTypeEnum: int implements ShipNyukinRunTypeEnumInterface
{
    case EXECTUE_ALL = 1;
    case CREATE = 2;
    case SEND = 3;

    public function label(): string
    {
        return match($this) {
            self::EXECTUE_ALL => '全処理実行',
            self::CREATE => '作成のみ',
            self::SEND => '送信のみ',
        };
    }
}
