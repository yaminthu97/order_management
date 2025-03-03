<?php
namespace App\Enums;

enum ThreeTemperatureZoneTypeEnum: int
{
    case NORMAL = 0;
    case COOL = 1;
    case FROZEN = 2;


    public function label(): string
    {
        return match ($this) {
            self::NORMAL => '常温',
            self::COOL => '冷蔵',
            self::FROZEN => '冷凍',
            default => '',
        };
    }
}
