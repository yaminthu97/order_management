<?php

namespace App\Enums;

enum MailSendStatusEnum: int
{
    case NOT_SEND = 0;
    case SUCCESS = 1;
    case ERROR = 9;
    
    public function label(): string
    {
        return match($this){
            self::NOT_SEND => '未送信',
            self::SUCCESS => '送信成功',
            self::ERROR => '送信エラー',
        };
    }
}