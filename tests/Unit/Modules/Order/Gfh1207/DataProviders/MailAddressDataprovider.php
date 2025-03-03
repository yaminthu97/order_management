<?php
namespace Tests\Unit\Modules\Order\Gfh1207\DataProviders;

class MailAddressDataprovider
{
    public static function provider()
    {
        return [
            'order_email_search_flgが1の場合' => [
                'values' => [
                    'order_email_search_flg' => '1',
                    'order_email' => 'test',
                ],
                'expected' => "(`order_email1` like 'test%' or `order_email2` like 'test%')"
            ],
            'order_email_search_flgが0の場合' => [
                'values' => [
                    'order_email_search_flg' => '0',
                    'order_email' => 'test',
                ],
                'expected' => "(`order_email1` = 'test' or `order_email2` = 'test')"
            ],
            'order_email_search_flgが設定されていない場合' => [
                'values' => [
                    'order_email' => 'test',
                ],
                'expected' => "(`order_email1` = 'test' or `order_email2` = 'test')"
            ]
        ];
    }

}
