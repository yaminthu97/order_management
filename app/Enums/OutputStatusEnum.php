<?php

namespace App\Enums;

/**
 * 出荷ステータス
 */
enum OutputStatusEnum: string
{
    case UNPUBLISH = "0";
    case PUBLISH = "1";

    public function label(): string
    {
        return match($this) {
            self::UNPUBLISH => '未発行',
            self::PUBLISH => '発行済',
        };
    }
}
