<?php

namespace App\Enums;

enum AttachmentItemCodeEnum: string
{
    case SILVER_SEAL = '03';  // 銀シール

    public function label(): string
    {
        return match($this) {
            self::SILVER_SEAL => '銀シール',
        };
    }
}
