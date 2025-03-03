<?php

namespace App\Enums;

/**
 * 見積フラグ
 */
enum EstimateFlgEnum: int
{
    case WITH = 1;
    case WITHOUT = 0;
    
    public function label(): string
    {
        return match($this) {
            self::WITH => 'あり',
            self::WITHOUT => 'なし',
        };
    }
}
