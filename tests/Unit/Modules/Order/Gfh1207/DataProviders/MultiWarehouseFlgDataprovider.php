<?php
namespace Tests\Unit\Modules\Order\Gfh1207\DataProviders;

class MultiWarehouseFlgDataprovider
{
    public static function provider()
    {
        return [
            'multi_warehouse_flgが0を含む(単数)' => [
                'values' => [
                    'multi_warehouse_flg' => '0',
                ],
                'expected' => "(`multi_warehouse_flg` is null or `multi_warehouse_flg` in ('0'))"
            ],
            'multi_warehouse_flgが0を含む(複数)' => [
                'values' => [
                    'multi_warehouse_flg' => '0,1',
                ],
                'expected' => "(`multi_warehouse_flg` is null or `multi_warehouse_flg` in ('0', '1'))"
            ],
            'multi_warehouse_flgが0を含まない' => [
                'values' => [
                    'multi_warehouse_flg' => '1',
                ],
                'expected' => "(`multi_warehouse_flg` in ('1'))"
            ],
        ];
    }

}
