<?php

namespace App\Modules\Customer\Gfh1207\Enums;

/**
 * DM配送方法メール
 */
enum DmSendMailWishFlgEnum: int
{
    case NO_WISH = 0;
    case WISH = 1;

    public function label(): string
    {
        return match($this) {
            self::NO_WISH => '希望しない',
            self::WISH => '希望する',
        };
    }
}
