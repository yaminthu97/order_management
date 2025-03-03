<?php

namespace App\Modules\Order\Gfh1207\Enums;

use App\Modules\Order\Base\Enums\ShippingInstructTypeEnumInterface;

enum ShippingInstructTypeEnum: int implements ShippingInstructTypeEnumInterface
{
    case INSTRUCTED = 1;
    case NOT_INSTRUCTED = 2;

    public function label(): string
    {
        return match($this) {
            self::INSTRUCTED => 'あり',
            self::NOT_INSTRUCTED => 'なし',
        };
    }
}
