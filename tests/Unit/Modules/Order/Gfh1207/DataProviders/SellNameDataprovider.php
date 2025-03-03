<?php
namespace Tests\Unit\Modules\Order\Gfh1207\DataProviders;

class SellNameDataprovider
{
    public static function provider()
    {
        return [
            'sell_name_search_flagが1の場合' => [
                'values' => [
                    'sell_name_search_flag' => '1',
                    'sell_name' => 'test',
                ],
                'expected' => "(select * from `t_order_dtl` where `t_order_hdr`.`t_order_hdr_id` = `t_order_dtl`.`t_order_hdr_id` and `sell_name` like '%test%')"
            ],
            'sell_name_search_flagが0の場合' => [
                'values' => [
                    'sell_name_search_flag' => '0',
                    'sell_name' => 'test',
                ],
                'expected' => "(select * from `t_order_dtl` where `t_order_hdr`.`t_order_hdr_id` = `t_order_dtl`.`t_order_hdr_id` and `sell_name` like 'test%')"
            ],
            'sell_name_search_flagが設定されていない場合' => [
                'values' => [
                    'sell_name' => 'test',
                ],
                'expected' => "(select * from `t_order_dtl` where `t_order_hdr`.`t_order_hdr_id` = `t_order_dtl`.`t_order_hdr_id` and `sell_name` like 'test%')"
            ]
        ];
    }

}
