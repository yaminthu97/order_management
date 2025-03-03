<?php
namespace Tests\Feature\Http\Controller\Order\OrderListController\Gfh1207\DataProviders;


class SearchDataProvider
{
    public static function provider()
    {
        return [
            '検索条件無しでの受注検索' => [
                'search' => [],
                'expected' => [
                    'html'
                ]
            ],
            'ページを指定しての受注検索' => [
                'search' => [
                    'hidden_next_page_no' => 2
                ],
                'expected' => [
                    'html'
                ]
            ],
            '表示件数を変更しての受注検索' => [
                'search' => [
                    'page_list_count' => 50
                ],
                'expected' => [
                    'html'
                ]
            ],
            '「注文者氏名・カナ氏名」での受注検索（既存の検索条件）' => [
                'search' => [
                    'order_name' => 'テスト'
                ],
                'expected' => [
                    'html'
                ]
            ],
            '検索結果が存在しない場合の受注検索' => [
                'search' => [
                    'order_name' => '存在しない名前'
                ],
                'expected' => [
                    'html'
                ]
            ],
        ];
    }

}
