<?php

namespace App\Modules\Master\Gfh1207\Enums;

/**
 * ユーザ種類
 */
enum UserTypeEnum: int
{
    case GENERAL_USER = 1;
    case STORE_MANAGER = 2;
    case SYSTEM_ADMIN = 99;

    public function label(): string
    {
        return match ($this) {
            self::GENERAL_USER => '一般ユーザ',
            self::STORE_MANAGER => '店舗管理者',
            self::SYSTEM_ADMIN => 'システム管理者'
        };
    }
}
