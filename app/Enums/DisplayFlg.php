<?php

namespace App\Enums;

enum DisplayFlg: int
{
    case HIDDEN = 0;
    case VISIBLE = 1;
    
    public function label(): string
    {
        return match($this){
            self::HIDDEN => '非表示',
            self::VISIBLE => '表示',
        };
    }
}