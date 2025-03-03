<?php

namespace App\Enums;

enum AttachFlgEnum: int
{
    case ATTACH = 0;
    case INCLUDE = 1;
    
    public function label(): string
    {
        return match($this){
            self::ATTACH => '貼付',
            self::INCLUDE => '同梱',
        };
    }
}