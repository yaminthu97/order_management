<?php
namespace App\Enums;

/**
 * DM送付区分(メール)
 */
enum DmSendMailFlgEnum: int
{
    case NO_SEND = 0;
    case SEND = 1;

    public function label(): string
    {
        return match($this) {
            self::NO_SEND => '不要',
            self::SEND => '要',
        };
    }
}
