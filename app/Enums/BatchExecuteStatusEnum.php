<?php

namespace App\Enums;

/**
 * バッチ実行状態
 */
enum BatchExecuteStatusEnum: int
{
    case SUCCESS = 0;
    case FAILURE = 1;
    case CANCELED = 9;
    case STARTED = 97;
    case PROCESSING = 98;
    case NOT_YET = 99;
    
    public function label(): string
    {
        return match($this) {
            self::SUCCESS => '正常',
            self::FAILURE => '異常',
            self::CANCELED => '取消済',
            self::STARTED => '起動中',
            self::PROCESSING => '処理中',
            self::NOT_YET => '未実施',
        };
    }
}
