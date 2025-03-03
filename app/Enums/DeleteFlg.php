<?php

namespace App\Enums;

enum DeleteFlg: int
{
    case Use = 0;
    case Notuse = 1;
    
    public function label(): string
    {
        return match($this){
            self::Use => '使用中',
            self::Notuse => '使用停止',
        };
    }
}