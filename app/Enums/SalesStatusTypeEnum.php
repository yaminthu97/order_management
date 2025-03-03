<?php

namespace App\Enums;

/**
 * 売上ステータス反映区分
 */
enum SalesStatusTypeEnum: int
{
    case NOT_RECORDED = 0;
    case RECORDING_NG = 1;
    case RECORDED = 2;
    case EXCLUDED = 9;
    
    public function label(): string
    {
        return match($this) {
            self::NOT_RECORDED => '未計上',
            self::RECORDING_NG => '計上NG',
            self::RECORDED => '計上済み',
            self::EXCLUDED => '対象外',
        };
    }
}
