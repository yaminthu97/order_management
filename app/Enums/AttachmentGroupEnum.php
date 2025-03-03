<?php

namespace App\Enums;

enum AttachmentGroupEnum: string
{
    case BUTSU = '02';       // 仏

    public function label(): string
    {
        return match($this) {
            self::BUTSU => '仏',
        };
    }
}
