<?php

namespace App\Modules\Customer\Gfh1207\Enums;

enum OpenFlg: int
{
    case PUBLISH = 1;
    case NOT_PUBLISH = 0;

    public function label(): string
    {
        return match($this) {
            self::PUBLISH => '公開する',
            self::NOT_PUBLISH => '公開しない',
        };
    }
}
