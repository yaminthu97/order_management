<?php
namespace Tests\TestCases;

use App\Models\Master\Base\AccountModel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCases\WithDatabaseTransactionOnLocalDbTestCase;

/**
 * 各企業用のサンプルテストケース
 */
class XXXTestCase extends WithDatabaseTransactionOnLocalDbTestCase
{
    /**
     * @todo アカウントコードが確定したら調整
     */
    protected $account_cd = 'xxx';

    public function setUp(): void
    {
        parent::setUp();

        // m_accountにデータを登録
        // 必要に応じて各項目を埋めてください。
        // ただし、rakuten_app_cd,yahoo_app_cd,yahoo_auth_cd,amazon_app_cd,amazon_auth_cd などの外部サービスの認証などに係るセキュリティ情報は、
        // !!絶対に!!コードベースで管理しないこと
        AccountModel::factory()->state([
            'account_cd' => $this->account_cd,
            'account_name' => 'xxx'
        ])->create();

    }


    public function tearDown(): void
    {
        parent::tearDown();
    }
}
