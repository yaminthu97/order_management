<?php
namespace Tests\Unit\Modules\Order\Gfh1207\DataProviders;

class TelFaxDataprovider
{
    public static function provider()
    {
        return [
            'tel_fax_search_flgが1の場合' => [
                'values' => [
                    'tel_fax_search_flg' => '1',
                    'tel_fax' => '1',
                ],
                'expected' => "`order_tel1` like '1%' or `order_tel2` like '1%' or `order_fax` like '1%')"
            ],
            'tel_fax_search_flgが0の場合' => [
                'values' => [
                    'tel_fax_search_flg' => '0',
                    'tel_fax' => '1',
                ],
                'expected' => "`order_tel1` = '1' or `order_tel2` = '1' or `order_fax` = '1')"
            ],
            'tel_fax_search_flgが設定されていない場合' => [
                'values' => [
                    'tel_fax' => '1',
                ],
                'expected' => "`order_tel1` = '1' or `order_tel2` = '1' or `order_fax` = '1')"
            ],
        ];
    }

}
