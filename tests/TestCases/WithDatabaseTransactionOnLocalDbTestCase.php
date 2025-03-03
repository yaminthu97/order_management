<?php
namespace Tests\TestCases;

use App\Models\Master\Base\AccountModel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\DatabaseTransactionsManager;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Log;



class WithDatabaseTransactionOnLocalDbTestCase extends TestCase
{
    use DatabaseTransactions;

    protected $account_cd = 'local';

    /**
     * DatabaseTransactionsを使用する際に複数のコネクションを対象にする方法
     * https://github.com/laravel/framework/issues/10873
     * ただし、本アプリケーションでは、ローカルスキーマを動的に解決する必要があるため、beginDatabaseTransactionをオーバーライドする。
     */
    protected $connectionsToTransact = ['global'];

    public function setUp(): void
    {
        parent::setUp();
    }


    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * DatabaseTransactionsを使用する際に複数のコネクションを対象にする方法
     * https://github.com/laravel/framework/issues/10873
     * ただし、本アプリケーションでは、ローカルスキーマを動的に解決する必要があるため、beginDatabaseTransactionをオーバーライドする。
     *
     * Handle database transactions on the specified connections.
     * @return void
     */
    public function beginDatabaseTransaction()
    {
        $this->setLocalDbConfig();

        /**
         * 以下、Illuminate\Foundation\Testing\DatabaseTransactions::beginDatabaseTransaction()の処理そのまま
         */
        $database = $this->app->make('db');
        $this->app->instance('db.transactions', $transactionsManager = new DatabaseTransactionsManager);

        foreach ($this->connectionsToTransact() as $name) {
            $connection = $database->connection($name);
            $connection->setTransactionManager($transactionsManager);
            $dispatcher = $connection->getEventDispatcher();

            $connection->unsetEventDispatcher();
            $connection->beginTransaction();
            $connection->setEventDispatcher($dispatcher);
        }

        $this->beforeApplicationDestroyed(function () use ($database) {
            foreach ($this->connectionsToTransact() as $name) {
                $connection = $database->connection($name);
                $dispatcher = $connection->getEventDispatcher();

                $connection->unsetEventDispatcher();
                $connection->rollBack();
                $connection->setEventDispatcher($dispatcher);
                $connection->disconnect();
            }
        });
    }

    /**
     * 接続先のローカルスキーマを設定する
     */
    private function setLocalDbConfig()
    {
        // 現在の接続を取得
        $defaultConnection = config('database.connections.mysql');

        // 新しい接続設定を作成
        $newConnection = array_merge($defaultConnection, [
            'database' => $this->account_cd.'_db_testing',
            ]);

        // 新しい接続を設定
        config(['database.connections.mysql' => $newConnection]);

        $this->connectionsToTransact = array_merge($this->connectionsToTransact, ['mysql']);
    }
}
