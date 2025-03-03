<?php

namespace App\Enums;

enum InvoiceFlg: int
{
    case Describe = 1;
    case Notlisted = 0;
    
    public function label(): string
    {
        return match($this){
            self::Describe => '記載する',
            self::Notlisted => '記載しない',
        };
    }
}