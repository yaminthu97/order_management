<?php
namespace App\Console\Commands\Payment;

use App\Enums\BatchExecuteStatusEnum;
use App\Modules\Payment\Base\CreateReceiptDataInterface;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Services\TenantDatabaseManager;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReceiptOut extends Command
{
    private const PRIVATE_EXCEPTION = -1;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ReceiptOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '領収書を出力する';

    protected $batchName = '領収書出力';

    // 開始時処理
    protected $startBatchExecute;
    
    // 終了時処理
    protected $endBatchExecute;

    // 領収書データ作成処理
    protected $createReceiptData;

    /**
     * Create a new command instance.
     */
    public function __construct(
        StartBatchExecuteInstructionInterface $startBatchExecute,
        EndBatchExecuteInstructionInterface $endBatchExecute,
        CreateReceiptDataInterface $createReceiptData
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->createReceiptData = $createReceiptData;
        parent::__construct();
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $executeBatchInstructionId = $this->argument('t_execute_batch_instruction_id');
        $batchExecute = $this->startBatchExecute->execute($executeBatchInstructionId);

        $accountCd = $batchExecute->account_cd;

        if (app()->environment('testing')) {
            // テスト環境の場合
            TenantDatabaseManager::setTenantConnection($accountCd.'_db_testing');
        } else {
            TenantDatabaseManager::setTenantConnection($accountCd.'_db');
        }
        DB::beginTransaction();
        try
        {
            // パラメータの取得
            $json = json_decode($this->argument('json'),true);
            if(empty($json['t_receipt_output_id'])){
                throw new Exception(__('messages.error.invalid_parameter'),self::PRIVATE_EXCEPTION);
            }
            $rv = $this->createReceiptData->execute($json['t_receipt_output_id'],$batchExecute);
            $excelExportPath = $rv['filepath'];
            $excelFilename = $rv['filename'];

            $result = $rv['manager']->save($excelExportPath.$excelFilename);
            if($result == false){
                throw new Exception(__('messages.error.write_file_error',['format'=>'Excel']),self::PRIVATE_EXCEPTION);
            }
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => __('messages.info.excel_output'),
                'execute_status' => BatchExecuteStatusEnum::SUCCESS->value, // BatchExecuteStatusEnum の「正常」
                'file_path' => $excelExportPath.$excelFilename
            ]);
            DB::commit();
        }
        catch (Exception $e)
        {
            Log::error($e->getMessage());
            DB::rollBack();
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => $e->getMessage(), 
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value,
            ]);
        }
    }
}