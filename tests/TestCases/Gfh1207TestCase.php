<?php
namespace Tests\TestCases;

use App\Models\Master\Base\AccountModel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use Tests\TestCases\WithDatabaseTransactionOnLocalDbTestCase;

class Gfh1207TestCase extends WithDatabaseTransactionOnLocalDbTestCase
{
    protected $account_cd = 'gfh_1207';

    protected $mAccount;
    public function setUp(): void
    {
        parent::setUp();

        // m_accountにデータを登録
        $this->mAccount = AccountModel::factory()->state([
            'account_cd' => $this->account_cd,
            'account_name' => '株式会社スクロール360'
        ])->create();

    }


    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * ログイン中の担当者情報を返却
     */
    protected function operatorInfo()
    {
        return json_decode('{
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
        }', true);
    }
}
