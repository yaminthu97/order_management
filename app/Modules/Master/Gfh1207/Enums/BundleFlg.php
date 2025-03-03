<?php

namespace App\Modules\Master\Gfh1207\Enums;

enum BundleFlg: int
{
    case Enabled = 1;
    case Disabled = 0;

    public function label(): string
    {
        return match($this) {
            self::Enabled => 'まとめる',
            self::Disabled => 'まとめない',
        };
    }
}
