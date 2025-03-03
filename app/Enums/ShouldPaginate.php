<?php

namespace App\Enums;

/**
 * (検索用)ページネーションフラグ
 */
enum ShouldPaginate: int
{
    case NO = 0;
    case YES = 1;

    public function label(): string
    {
        return match($this){
            self::NO => 'ページネーションなし',
            self::YES => 'ページネーションあり',
        };
    }
}
