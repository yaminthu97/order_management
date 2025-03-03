<?php

namespace App\Modules\Customer\Gfh1207\Enums;

enum AuthorityCode: int
{
    case ADMIN = 1;
    case OPERATOR = 0;

    public function label(): string
    {
        return match($this) {
            self::ADMIN => '管理者',
            self::OPERATOR => 'オペレータ',
        };
    }
}
