<?php

namespace App\Modules\Master\Gfh1207\Enums;

enum PriorityFlg: int
{
    case Enabled = 1;
    case Disabled = 0;

    public function label(): string
    {
        return match($this) {
            self::Enabled => '引当有効',
            self::Disabled => '引当無効',
        };
    }
}
