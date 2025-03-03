<?php
namespace App\Console\Commands\Payment;

use App\Enums\BatchExecuteStatusEnum;
use App\Services\TenantDatabaseManager;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Payment\Base\CreateBillingDataInterface;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BillingOut extends Command
{
    private const PRIVATE_EXCEPTION = -1;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:BillingOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '請求書を出力する';

    protected $batchName = '請求書出力';

    // 開始時処理
    protected $startBatchExecute;
    
    // 終了時処理
    protected $endBatchExecute;

    // 請求書作成
    protected $createBillingData;


    /**
     * Create a new command instance.
     */
    public function __construct(
        StartBatchExecuteInstructionInterface $startBatchExecute,
        EndBatchExecuteInstructionInterface $endBatchExecute,
        CreateBillingDataInterface $createBillingData
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->createBillingData = $createBillingData;
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
        $accountId = $batchExecute->m_account_id;

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
            if(empty($json['t_billing_outputs_id'])){
                throw new Exception(__('messages.error.invalid_parameter'),self::PRIVATE_EXCEPTION);
            }
            $tempPdfFilePath = tempnam(sys_get_temp_dir(), 'pdf');
            $rv = $this->createBillingData->execute($json['t_billing_outputs_id'],$batchExecute,$tempPdfFilePath);
            $pdfExportPath = $rv['filepath'];
            $pdfFilename = $rv['filename'];
            $status = Storage::disk(config('filesystems.default', 'local'))->put($pdfExportPath.$pdfFilename, file_get_contents($tempPdfFilePath));
            unlink($tempPdfFilePath);
            if(!$status){
                throw new Exception(__('messages.error.upload_s3_file_failed',['file'=>$pdfExportPath.$pdfFilename]),self::PRIVATE_EXCEPTION);
            }
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => __('messages.info.pdf_output'),
                'execute_status' => BatchExecuteStatusEnum::SUCCESS->value, // BatchExecuteStatusEnum の「正常」
                'file_path' => $pdfExportPath.$pdfFilename
            ]);
            DB::commit();
        }
        catch (Exception $e)
        {
            Log::error($e->getMessage());
            DB::rollBack();
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => $e->getCode() == self::PRIVATE_EXCEPTION?$e->getMessage():BatchExecuteStatusEnum::FAILURE->label().$e->getMessage() , // エラーとなった原因を記載（実際は messages.php などから取得する）
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value, // BatchExecuteStatusEnum の「異常」
            ]);
        }
    }
    
}