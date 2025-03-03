<?php

namespace App\Modules\Master\Gfh1207\Enums;

enum WarehouseTypesEnum: int
{
    case Regular = 1;
    case L_Spark = 3;

    public function label(): string
    {
        return match($this) {
            self::Regular => '通常倉庫',
            self::L_Spark => 'L-Spark倉庫',

        };
    }
}
