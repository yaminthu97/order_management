<?php
namespace Tests\TestCases;

use App\Models\Master\Base\AccountModel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use Tests\TestCases\WithDatabaseTransactionOnLocalDbTestCase;

class HaradaTestCase extends WithDatabaseTransactionOnLocalDbTestCase
{
    /**
     * @todo アカウントコードが確定したら調整
     */
    protected $account_cd = 'gfh_1207';

    public function setUp(): void
    {
        parent::setUp();

        // m_accountにデータを登録
        AccountModel::factory()->state([
            'account_cd' => $this->account_cd,
            'account_name' => 'ガトーフェスタハラダ'
        ])->create();

    }


    public function tearDown(): void
    {
        parent::tearDown();
    }
}
