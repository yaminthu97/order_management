<?php
namespace Tests\Unit\Modules\Order\Gfh1207\DataProviders;

class OperatorCommentDataprovider
{
    public static function operatorCommentFlgProvider()
    {
        return [
            "1の場合は備考があること" => ["1", "`operator_comment` <> ''"],
            "0の場合は備考がないこと" => ["0", "`operator_comment` = ''"],
        ];
    }

    public static function operatorCommentProvider()
    {
        return [
            "operator_comment_search_flgが1の場合は前方後方一致" => [
                'values' => [
                    "operator_comment_search_flg" => "1",
                    "operator_comment" => "test"
                ],
                'expected' => "`operator_comment` like '%test%'"
            ],
            "operator_comment_search_flgが0の場合は後方一致" => [
                'values' => [
                    "operator_comment_search_flg" => "0",
                    "operator_comment" => "test"
                ],
                'expected' => "`operator_comment` like 'test%'"
            ],
            "operator_comment_search_flgが存在しない場合は後方一致" => [
                'values' => [
                    "operator_comment" => "test"
                ],
                'expected' => "`operator_comment` like 'test%'"
            ]
        ];
    }
}
