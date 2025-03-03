<?php
namespace App\Enums;

/**
 * キャンペーンフラグ
 */
enum CampaignFlgEnum: int
{
    case SUBJECT = 1;
    case EXCLUDED = 0;

    public function label(): string
    {
        return match($this) {
            self::SUBJECT => '対象',
            self::EXCLUDED => '対象外',
        };
    }
}
