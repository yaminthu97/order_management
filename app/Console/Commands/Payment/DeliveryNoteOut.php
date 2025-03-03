<?php

namespace App\Console\Commands\Payment;

use App\Enums\BatchExecuteStatusEnum;
use App\Models\Claim\Gfh1207\BillingHdrModel;
use App\Models\Claim\Gfh1207\BillingOutputModel;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Common\Gfh1207\CheckBatchParameter;
use App\Modules\Master\Gfh1207\Enums\TemplateFileNameEnum;
use App\Modules\Order\Gfh1207\GetExcelExportFilePath;
use App\Modules\Order\Gfh1207\GetTemplateFileName;
use App\Modules\Order\Gfh1207\GetTemplateFilePath;
use App\Modules\Payment\Gfh1207\ExportBillingData;
use App\Services\TenantDatabaseManager;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DeliveryNoteOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:DeliveryNoteOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '納品書を出力する。';

    // テンプレートファイルパス
    protected $templateFilePath;

    // テンプレートファイル名
    protected $templateFileName;

    // バッチ名
    protected $batchName = '納品書単体出力';

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    // for report name
    protected $reportName = TemplateFileNameEnum::EXPXLSX_DELIVERY_NOTE->value;

    // for template file name
    protected $getTemplateFileName;

    // for template file path
    protected $getTemplateFilePath;

    // for excel export file path
    protected $getExcelExportFilePath;

    // for check batch parameter
    protected $checkBatchParameter;

    // for error code
    private const PRIVATE_THROW_ERR_CODE = -1;

    /**
     * Create a new command instance.
    */
    public function __construct(
        StartBatchExecuteInstructionInterface $startBatchExecute,
        EndBatchExecuteInstructionInterface $endBatchExecute,
        GetTemplateFileName $getTemplateFileName,
        GetTemplateFilePath $getTemplateFilePath,
        CheckBatchParameter $checkBatchParameter,
        GetExcelExportFilePath $getExcelExportFilePath,
        ExportBillingData $exportBillingData,
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->getTemplateFileName = $getTemplateFileName;
        $this->getTemplateFilePath = $getTemplateFilePath;
        $this->checkBatchParameter = $checkBatchParameter;
        $this->getExcelExportFilePath = $getExcelExportFilePath;
        $this->exportBillingData = $exportBillingData;
        // get disk name s3
        $this->disk = config('filesystems.default', 'local');

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
    */
    public function handle()
    {
        try{
            //バッチ実行ID
            $batchExecutionId  = $this->argument('t_execute_batch_instruction_id');
             /**
             * [共通処理] 開始処理
             * バッチ実行指示テーブル（t_execute_batch_instruction ）から該当バッチの取得と開始処理
             * t_execute_batch_instruction_id より該当バッチを取得し以下の情報を書き込む
             * - バッチ開始時刻
             */
            $batchExecute = $this->startBatchExecute->execute($batchExecutionId);
            $accountCode = $batchExecute->account_cd;
            $accountId   = $batchExecute->m_account_id;
            $batchType   = $batchExecute->execute_batch_type;
            if (app()->environment('testing')) {
                // テスト環境の場合
                TenantDatabaseManager::setTenantConnection($accountCode . '_db_testing');
            } else {
                TenantDatabaseManager::setTenantConnection($accountCode . '_db');
            }
        }catch(Exception $e){
            Log::error('error_message : ' . $e->getMessage());
            return;
        }

        DB::beginTransaction();

        try{
             // to required parameter
             $requiredFields = [ 't_billing_outputs_id'];
             $checkResult = $this->checkBatchParameter->execute($this->argument('json'), $requiredFields);  // to check batch json parameter
             if (!$checkResult) {
                // [パラメータが不正です。] message save to 'execute_result'
                throw new Exception(__('messages.error.invalid_parameter'), self::PRIVATE_THROW_ERR_CODE);
            }
            $searchCondition = (json_decode($this->argument('json'), true))['search_info'];
            $templateFileName = $this->getTemplateFileName->execute($this->reportName, $accountId);

            $templateFilePath = $this->getTemplateFilePath->execute($accountCode, $templateFileName);
             if (empty($templateFilePath)) {
                // [テンプレートファイルが見つかりません。] message save to 'execute_result'
                throw new Exception(__('messages.error.file_not_found', ['file' => 'テンプレート']), self::PRIVATE_THROW_ERR_CODE);
            }
            $savePath = $this->getExcelExportFilePath->execute($accountCode, $batchType, $batchExecutionId);
            $finalResult = $this->exportBillingData->execute($searchCondition,$templateFilePath,$savePath,$accountId);

            /**
             * [共通処理] 終了処理
             * バッチ実行指示テーブルの該当バッチに以下の情報を書き込む
             * - バッチ終了時刻
             * - (実行状態)
             * - (実行結果)
             * - (格納ファイルパス)
            */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => __('messages.info.notice_output_count', ['count' => $finalResult]),  // 〇〇件出力しました。
                'execute_status' => BatchExecuteStatusEnum::SUCCESS->value,
                'file_path'      => $savePath,
            ]);
            DB::commit();
        } catch(Exception $e){
            DB::rollBack();

            Log::error('error_message : ' . $e->getMessage());

            /**
             * [共通処理] 終了処理
             * バッチ実行指示テーブルの該当バッチに以下の情報を書き込む
             * - バッチ終了時刻
             * - (実行状態)
             * - (実行結果)
            */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => ($e->getCode() == self::PRIVATE_THROW_ERR_CODE) ? $e->getMessage() : BatchExecuteStatusEnum::FAILURE->label(),
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value
            ]);
        }
    }
}
