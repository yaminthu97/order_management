<?php

namespace App\Enums;


/**
 * 有効フラグ
 */enum AvailableFlg: int
{
    case NotAvailable = 0;
    case Available = 1;
    
    public function label(): string
    {
        return match($this){
            self::NotAvailable => '無効',
            self::Available => '有効',
        };
    }
}