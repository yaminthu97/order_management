<?php

namespace App\Modules\Order\Gfh1207\Enums;

use App\Modules\Order\Base\Enums\EcbeingExecuteTypeInterface;

enum EcbeingExecuteType: int implements EcbeingExecuteTypeInterface
{
    case IMPORT = 1;
    case EXPORT = 2;

    public function label(): string
    {
        return match($this) {
            self::IMPORT => '顧客・受注取込',
            self::EXPORT => '出荷確定データ出力、入金・受注修正データ出力',
        };
    }
}
