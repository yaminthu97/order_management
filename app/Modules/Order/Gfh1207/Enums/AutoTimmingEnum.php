<?php

namespace App\Modules\Order\Gfh1207\Enums;

use App\Modules\Order\Base\Enums\AutoTimmingEnumInterface;

enum AutoTimmingEnum: int implements AutoTimmingEnumInterface
{
    case NONE = 0;        // なし
    case REGISTER = 1;    // 登録時
    case UPDATE = 2;      // 登録更新時

    public function label(): string
    {
        return match ($this) {
            self::NONE => 'なし',
            self::REGISTER => '登録時',
            self::UPDATE => '登録更新時',
        };
    }
}
