<?php

namespace App\Enums;

/**
 * 帳票出力単位
 */
enum ExcelReportOutputUnitEnum: int
{
    case ORDER = 1;
    case BRANCH = 2;
    
    public function label(): string
    {
        return match($this) {
            self::ORDER => '受注単位',
            self::BRANCH => '枝番単位',
        };
    }
}
