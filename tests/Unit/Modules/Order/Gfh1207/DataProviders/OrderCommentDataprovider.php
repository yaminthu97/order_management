<?php
namespace Tests\Unit\Modules\Order\Gfh1207\DataProviders;

class OrderCommentDataprovider
{
    public static function orderCommentFlgProvider()
    {
        return [
            "1の場合は備考があること" => ["1", "(`order_comment` <> '' and `order_comment` is not null)"],
            "0の場合は備考がないこと" => ["0", "(`order_comment` = '' or `order_comment` is null)"],
        ];
    }

    public static function orderCommentProvider()
    {
        return [
            "order_comment_search_flgが1の場合は前方後方一致" => [
                'values' => [
                    "order_comment_search_flg" => "1",
                    "order_comment" => "test"
                ],
                'expected' => "`order_comment` like '%test%'"
            ],
            "order_comment_search_flgが0の場合は後方一致" => [
                'values' => [
                    "order_comment_search_flg" => "0",
                    "order_comment" => "test"
                ],
                'expected' => "`order_comment` like 'test%'"
            ],
            "order_comment_search_flgが存在しない場合は後方一致" => [
                'values' => [
                    "order_comment" => "test"
                ],
                'expected' => "`order_comment` like 'test%'"
            ]
        ];
    }
}
