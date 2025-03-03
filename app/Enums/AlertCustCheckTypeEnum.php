<?php

namespace App\Enums;

/**
 * 要注意顧客区分
 * 顧客用のApp\Enums\AlertCustTypeEnum とは異なるため、混用しないこと
 */
enum AlertCustCheckTypeEnum: int
{
    /**
     * 未確認
     */
    case UNCONFIRMED = 0;
    /**
     * 確認済
     */
    case CONFIRMED = 2;
    /**
     * 対象外
     */
    case EXCLUDED = 9;

    public function label(): string
    {
        return match($this) {
            self::UNCONFIRMED => '未確認',
            self::CONFIRMED => '確認済',
            self::EXCLUDED => '対象外',
        };
    }
}
