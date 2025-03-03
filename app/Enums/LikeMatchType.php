<?php

namespace App\Enums;

/**
 * (検索用)前方後方検索区分
 */
enum LikeMatchType: int
{
    case FORWARD = 1;
    case BACKWARD = 2; // Esm2.0では使っていない
    case PARTIAL = 3; // Esm2.0では使っていない



    public function label(): string
    {
        return match($this) {
            self::FORWARD => '前方一致',
            self::BACKWARD => '後方一致',
            self::PARTIAL => '部分一致',
        };
    }
}
