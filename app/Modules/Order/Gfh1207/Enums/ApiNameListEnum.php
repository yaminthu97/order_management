<?php

namespace App\Modules\Order\Gfh1207\Enums;

use App\Modules\Order\Base\Enums\ApiNameListEnumInterface;

enum ApiNameListEnum: string implements ApiNameListEnumInterface
{
    case EXP_CUSTOMER = 'exp_customer.aspx';
    case DL_CUSTOMER = 'dl_customer.aspx';
    case EXP_SALES = 'exp_sales.aspx';
    case DL_SALES = 'dl_sales.aspx';
    case IMP_SHIP = 'imp_ship.aspx';
    case UPDATE_SHIP = 'update_ship.aspx';
    case IMP_NYUKIN = 'imp_nyukin.aspx';
    case UPDATE_NYUKIN = 'update_nyukin.aspx';

    public function label(): string
    {
        return match($this) {
            self::EXP_CUSTOMER => '顧客データ作成',
            self::DL_CUSTOMER => '顧客データダウンロード',
            self::EXP_SALES => '注文データ作成',
            self::DL_SALES => '注文データダウンロード',
            self::IMP_SHIP => '出荷確定データ取込',
            self::UPDATE_SHIP => '出荷確定データ更新',
            self::IMP_NYUKIN => '入金・受注変更データ取込',
            self::UPDATE_NYUKIN => '入金・受注変更データ更新',
        };
    }
}
