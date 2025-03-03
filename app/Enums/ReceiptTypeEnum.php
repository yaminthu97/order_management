<?php

namespace App\Enums;

enum ReceiptTypeEnum: int
{
    case Unneeded = 0;
    case Batch = 1;
    case Split = 2;
   
    public function label(): string
    {
        return match($this){
            self::Unneeded => '不要',
            self::Batch => '一括',
            self::Split => '分割',
        };
    }
}