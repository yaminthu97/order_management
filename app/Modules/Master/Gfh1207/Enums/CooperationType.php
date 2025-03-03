<?php

namespace App\Modules\Master\Gfh1207\Enums;

/**
 * 後払い.com連携区分
 */
enum CooperationType: int
{
    case COOPERATE = 0;
    case NO_COOPERATION = 1;

    public function label(): string
    {
        return match($this) {
            self::COOPERATE => '連携する',
            self::NO_COOPERATION => '連携しない',
        };
    }
}
