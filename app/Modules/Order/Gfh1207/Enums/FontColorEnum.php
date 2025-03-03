<?php

namespace App\Modules\Order\Gfh1207\Enums;

use App\Modules\Order\Base\Enums\FontColorEnumInterface;

enum FontColorEnum: string implements FontColorEnumInterface
{
    case BLACK = '000000';    // 黒
    case WHITE = 'FFFFFF';    // 白

    public function label(): string
    {
        return match ($this) {
            self::BLACK => '黒',
            self::WHITE => '白',
        };
    }
}
