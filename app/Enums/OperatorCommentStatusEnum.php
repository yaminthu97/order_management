<?php

namespace App\Enums;

/**
 * (検索用)顧客備考の有無区分
 *
 */
enum OperatorCommentStatusEnum: string
{
    case EXIST = "1";
    case NONE = "2";


    public function label(): string
    {
        return match($this) {
            self::EXIST => '有',
            self::NONE => '無',
        };
    }
}
