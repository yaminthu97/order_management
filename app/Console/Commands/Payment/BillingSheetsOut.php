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
use ZipArchive;

class BillingSheetsOut extends Command
{
    private const PRIVATE_EXCEPTION = -1;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:BillingSheetsOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '請求書を一括出力する';

    protected $batchName = '請求書一括出力';

    // 開始時処理
    protected $startBatchExecute;
    
    // 終了時処理
    protected $endBatchExecute;

    // 請求書履歴データ取得
    protected $findBillingOutput;


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

        if (app()->environment('testing')) {
            // テスト環境の場合
            TenantDatabaseManager::setTenantConnection($accountCd.'_db_testing');
        } else {
            TenantDatabaseManager::setTenantConnection($accountCd.'_db');
        }
        $removeFlies = [];
        DB::beginTransaction();
        $zip = null;
        $isClose = false;
        try
        {
            // パラメータの取得
            $json = json_decode($this->argument('json'),true);
            if(!is_array($json)){
                throw new Exception(__('messages.error.invalid_parameter'),self::PRIVATE_EXCEPTION);
            }
            // zipファイル作成
            $zip = new ZipArchive();
            $tempZipFilePath = tempnam(sys_get_temp_dir(), 'zip');
            $zip->open($tempZipFilePath);
            $removeFlies[] = $tempZipFilePath;
            foreach($json as $e){
                if(empty($e['t_billing_outputs_id'])){
                    throw new Exception(__('messages.error.invalid_parameter'),self::PRIVATE_EXCEPTION);
                }
                $tempPdfFilePath = tempnam(sys_get_temp_dir(), 'pdf');
                // 例外が起きたときのためにファイル名を追加しておく
                $removeFlies[] = $tempPdfFilePath;
                $rv = $this->createBillingData->execute($e['t_billing_outputs_id'],$batchExecute,$tempPdfFilePath);
                rename($tempPdfFilePath,sys_get_temp_dir().'/'.$rv['filename']);
                $tempPdfFilePath = sys_get_temp_dir().'/'.$rv['filename'];

                // アーカイブに追加
                $zip->addFile($tempPdfFilePath,pathinfo($tempPdfFilePath, PATHINFO_BASENAME));

                $removeFlies[] = $tempPdfFilePath;
            }
            $zip->close();
            $isClose = true;

            // storageに出力
            $zipExportPath = sprintf("/%s/pdf/export/%s/",$accountCd,$executeBatchInstructionId);
            $zipFilename = sprintf("billing_%s.zip",date('YmdHis'));
            $status = Storage::disk(config('filesystems.default', 'local'))->put($zipExportPath.$zipFilename, file_get_contents($tempZipFilePath));
            if(!$status){
                throw new Exception(__('messages.error.upload_s3_file_failed',['file'=>$zipExportPath.$zipFilename]),self::PRIVATE_EXCEPTION);
            }
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => __('messages.info.zip_output'),
                'execute_status' => BatchExecuteStatusEnum::SUCCESS->value, // BatchExecuteStatusEnum の「正常」
                'file_path' => $zipExportPath.$zipFilename
            ]);
            DB::commit();
        } catch (Exception $e) {
            Log::error($e->getMessage());
            DB::rollBack();
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => $e->getCode() == self::PRIVATE_EXCEPTION?$e->getMessage():BatchExecuteStatusEnum::FAILURE->label().$e->getMessage() , // エラーとなった原因を記載（実際は messages.php などから取得する）
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value, // BatchExecuteStatusEnum の「異常」
            ]);
        } finally {
            if($zip && $isClose == false){
                $zip->close();
            }
            // 作成したファイルの削除処理
            foreach($removeFlies as $e){
                if(file_exists($e)){
                    unlink($e);
                }
            }
        }
    }    
}