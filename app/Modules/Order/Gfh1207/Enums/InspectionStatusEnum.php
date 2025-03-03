<?php

namespace App\Modules\Order\Gfh1207\Enums;

use App\Modules\Order\Base\Enums\InspectionStatusEnumInterface;

enum InspectionStatusEnum: int implements InspectionStatusEnumInterface
{
    case UNINSPECTED = 1;
    case INSPECTED = 2;
    case ALL = 3;

    public function label(): string
    {
        return match($this) {
            self::UNINSPECTED => '未検品',
            self::INSPECTED => '検品済み',
            self::ALL => 'すべて',
        };
    }
}
