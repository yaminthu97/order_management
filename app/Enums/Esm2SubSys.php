<?php

namespace App\Enums;

enum Esm2SubSys: string
{
    case GLOBAL = 'global';
    case SYSCOM = 'syscom';
    case MASTER = 'master';
    case WAREHOUSE = 'warehouse';
    case COMMON = 'common';
    case STOCK = 'stock';
    case ORDER = 'order';
    case ORDER_API = 'order/api';
    case CC = 'cc';
    case CLAIM = 'claim';
    case AMI = 'ami';
    case GOTO = 'goto';

    public function getSubSysForUrl(): string
    {
        if($this === self::GLOBAL) {
            return 'gcommon';
        }

        return $this->value;
    }
}
