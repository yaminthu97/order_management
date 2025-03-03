<?php

namespace App\Modules\Master\Gfh1207\Enums;

/**
 * 多要素認証
 */
enum MultiFactorAuthentication: int
{
    case Use = 1;
    case Notuse = 0;

    public function label(): string
    {
        return match ($this) {
            self::Use => '利用する',
            self::Notuse => '利用しない',
        };
    }
}
