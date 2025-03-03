<?php

namespace App\Enums;

/**
 * 出荷連携状態
 */
enum ShipmentLinkageStatusEnum: int
{
    case LINKABLE = 0;
    case LINKED = 1;
    
    public function label(): string
    {
        return match($this) {
            self::LINKABLE => '連携可能',
            self::LINKED => '連携済',
        };
    }
}