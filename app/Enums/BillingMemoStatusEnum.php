<?php

namespace App\Enums;

/**
 * 出荷ステータス
 */
enum BillingMemoStatusEnum: string
{
    case HAVE = "1";

    public function label(): string
    {
        return match($this) {
            self::HAVE => '有',
        };
    }
}
