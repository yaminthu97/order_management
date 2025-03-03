<?php

namespace App\Modules\Master\Gfh1207\Enums;

enum DeliveryFlg: int
{
    case Enabled = 1;
    case Disabled = 0;

    public function label(): string
    {
        return match($this) {
            self::Enabled => '可能',
            self::Disabled => '不可',
        };
    }
}
