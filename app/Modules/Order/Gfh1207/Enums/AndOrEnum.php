<?php

namespace App\Modules\Order\Gfh1207\Enums;

use App\Modules\Order\Base\Enums\AndOrEnumInterface;

enum AndOrEnum: int implements AndOrEnumInterface
{
    case AND = 0;    // 黒
    case OR = 1;    // 白

    public function label(): string
    {
        return match ($this) {
            self::AND => 'AND',
            self::OR => 'OR',
        };
    }
}
