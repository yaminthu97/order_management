<?php
namespace Tests\Unit\Modules\Order\Gfh1207\DataProviders;

class DeliveryNameDataprovider
{
    public static function provider()
    {
        return [
            'destination_search_flagが1の場合' => [
                'values' => [
                    'destination_search_flag' => '1',
                    'destination_name' => 'test',
                ],
                'expected' => "exists (select * from `t_order_destination` where `t_order_hdr`.`t_order_hdr_id` = `t_order_destination`.`t_order_hdr_id` and (`destination_name` like '%test%' or `destination_name_kana` like '%test%'))"
            ],
            'destination_search_flagが0の場合' => [
                'values' => [
                    'destination_search_flag' => '0',
                    'destination_name' => 'test',
                ],
                'expected' => "exists (select * from `t_order_destination` where `t_order_hdr`.`t_order_hdr_id` = `t_order_destination`.`t_order_hdr_id` and (`destination_name` like 'test%' or `destination_name_kana` like 'test%'))"
            ],
            'destination_search_flagが設定されていない場合' => [
                'values' => [
                    'destination_name' => 'test',
                ],
                'expected' => "exists (select * from `t_order_destination` where `t_order_hdr`.`t_order_hdr_id` = `t_order_destination`.`t_order_hdr_id` and (`destination_name` like 'test%' or `destination_name_kana` like 'test%'))"
            ]
        ];
    }

}
