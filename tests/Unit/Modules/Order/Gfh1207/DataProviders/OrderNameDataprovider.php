<?php
namespace Tests\Unit\Modules\Order\Gfh1207\DataProviders;

class OrderNameDataprovider
{
    public static function provider()
    {
        return [
            'order_name_search_flgが1の場合' => [
                'values' => [
                    'order_name_search_flg' => '1',
                    'order_name' => 'test',
                ],
                'expected' => "(`gen_search_order_name` like '%test%' or `gen_search_order_name_kana` like '%test%')"
            ],
            'order_name_search_flgが0の場合' => [
                'values' => [
                    'order_name_search_flg' => '0',
                    'order_name' => 'test',
                ],
                'expected' => "(`gen_search_order_name` like 'test%' or `gen_search_order_name_kana` like 'test%')"
            ],
            'order_name_search_flgが設定されていない場合' => [
                'values' => [
                    'order_name' => 'test',
                ],
                'expected' => "(`gen_search_order_name` like 'test%' or `gen_search_order_name_kana` like 'test%')"
            ]
        ];
    }

}
