<?php

namespace App\Modules\Master\Base\Enums;

enum AttentionType: int implements AttentionTypeInterface
{
    case Normal = 0; // 通常
    case NeedsConfirmation = 1; // 要確認
    case OrderNotAllowed = 2; // 受注不可

    public function label(): string
    {
        return match($this) {
            self::Normal => '通常',
            self::NeedsConfirmation => '要確認',
            self::OrderNotAllowed => '受注不可',
        };
    }
}
