<?php
namespace Tests\Feature\Http\Controller\Order\OrderListController\Gfh1207\DataProviders;


class LoginOperatorInfoDataProvider
{
    public static function provider()
    {
        return  [
            '汎用ユーザー' => [
                'operatorInfo' => json_decode('{
                    "m_account_id": 1,
                    "account_cd": "gfh_1207",
                    "account_name": "株式会社スクロール360",
                    "syscom_use_version": "v1_0",
                    "master_use_version": "v1_0",
                    "warehouse_use_version": "v1_0",
                    "common_use_version": "v1_0",
                    "stock_use_version": "v1_0",
                    "order_use_version": "v1_0",
                    "cc_use_version": "v1_0",
                    "claim_use_version": "v1_0",
                    "ami_use_version": "v1_0",
                    "goto_use_version": "v1_0",
                    "m_operators_id": 1,
                    "m_operator_name": "システム管理者",
                    "user_type": "99",
                    "password_update_timestamp": "2023-04-01 00:00:00.000000",
                    "operation_authority_detail": [
                        {
                            "menu_type": "10",
                            "available_flg": "1"
                        },
                        {
                            "menu_type": "20",
                            "available_flg": "1"
                        },
                        {
                            "menu_type": "30",
                            "available_flg": "1"
                        },
                        {
                            "menu_type": "40",
                            "available_flg": "1"
                        },
                        {
                            "menu_type": "50",
                            "available_flg": "1"
                        },
                        {
                            "menu_type": "60",
                            "available_flg": "1"
                        }
                    ],
                    "m_operation_authority_id": 1,
                    "m_operation_authority_name": "全権限",
                    "CommonHeader": {
                        "NoticeInfo": [],
                        "AlertInfo": []
                    }
                }', true),
                'expected' => [
                    'status' => 200,
                    ]
            ],
            '未ログイン' => [
                'operatorInfo' => null,
                'expected' => [
                    'status' => 302,
                ]
            ],
        ];
    }

    public static function searchProvider()
    {
        return [
            '検索条件無しでの受注検索' => [],
            'ページを指定しての受注検索' => [],
            '表示件数を変更しての受注検索' => [],
            '「注文者氏名・カナ氏名」での受注検索（既存の検索条件）' => [],
            '検索結果が存在しない場合の受注検索' => [],
        ];
    }

}
