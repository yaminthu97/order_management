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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class ReceiptSheetsOut extends Command
{
    private const PRIVATE_EXCEPTION = -1;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ReceiptSheetsOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '領収書を一括出力する';

    protected $batchName = '領収書一括出力';

    // 開始時処理
    protected $startBatchExecute;
    
    // 終了時処理
    protected $endBatchExecute;

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
        $accountId = $batchExecute->m_account_id;

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
                if(empty($e['t_receipt_output_id'])){
                    throw new Exception(__('messages.error.invalid_parameter'),self::PRIVATE_EXCEPTION);
                }
                $rv = $this->createReceiptData->execute($e['t_receipt_output_id'],$batchExecute);
                $excelExportPath = sys_get_temp_dir().'/'.$rv['filename'];
                $rv['manager']->saveLocalFile($excelExportPath);

                // アーカイブに追加
                $zip->addFile($excelExportPath,pathinfo($excelExportPath, PATHINFO_BASENAME));       
                
                $removeFlies[] = $excelExportPath;
            }
            $zip->close();
            $isClose = true;

            // storageに出力
            $zipExportPath = sprintf("/%s/excel/export/%s/",$accountCd,$executeBatchInstructionId);
            $zipFilename = sprintf("receipt_%s.zip",date('YmdHis'));
            $status = Storage::disk(config('filesystems.default', 'local'))->put($zipExportPath.$zipFilename, file_get_contents($tempZipFilePath));
            if(!$status){
                throw new Exception(__('messages.error.write_file_error',['format'=>'Zip']),self::PRIVATE_EXCEPTION);
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
                'execute_result' => $e->getMessage(), 
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value,
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