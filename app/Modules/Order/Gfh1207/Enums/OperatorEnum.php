<?php

namespace App\Modules\Order\Gfh1207\Enums;

use App\Modules\Order\Base\Enums\OperatorEnumInterface;

enum OperatorEnum: string implements OperatorEnumInterface
{
    case EQUAL = "=";
    case NOT_EQUAL = "≠";
    case LESS_THAN = "＜";
    case GREATER_THAN = "＞";
    case LESS_THAN_OR_EQUAL = "≦";
    case GREATER_THAN_OR_EQUAL = "≧";
    case CONTAINS =  "含む";
    case DOES_NOT_CONTAIN = "含まない";
    case IS_NULL = "NULL";
    case IS_NOT_NULL = "NOT NULL";
    case IN = "IN";

    public function label(): string
    {
        return match ($this) {
            self::EQUAL => '=',
            self::NOT_EQUAL => '≠',
            self::LESS_THAN => '＜',
            self::GREATER_THAN => '＞',
            self::LESS_THAN_OR_EQUAL => '≦',
            self::GREATER_THAN_OR_EQUAL => '≧',
            self::CONTAINS => '含む',
            self::DOES_NOT_CONTAIN => '含まない',
            self::IS_NULL => 'NULL',
            self::IS_NOT_NULL => 'NOT NULL',
            self::IN => 'IN',
        };
    }
}
