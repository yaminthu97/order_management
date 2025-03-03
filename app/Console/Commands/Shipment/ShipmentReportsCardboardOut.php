<?php

namespace App\Console\Commands\Shipment;

use App\Enums\BatchExecuteStatusEnum;
use App\Models\Order\Base\CardboardLogModel;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Common\Gfh1207\CheckBatchParameter;
use App\Modules\Master\Gfh1207\Enums\TemplateFileNameEnum;
use App\Modules\Order\Gfh1207\GetExcelExportFilePath;
use App\Modules\Order\Gfh1207\GetTemplateFileName;
use App\Modules\Order\Gfh1207\GetTemplateFilePath;
use App\Services\ExcelReportManager;
use App\Services\TenantDatabaseManager;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShipmentReportsCardboardOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ShipmentReportsCardboardOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '出荷未出荷一覧画面で検索した条件で出荷未出荷一覧を作成し、バッチ実行確認へと出力する';

    // テンプレートファイルパス
    protected $templateFilePath;

    // テンプレートファイル名
    protected $templateFileName;

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    // for report name
    protected $reportName = TemplateFileNameEnum::EXPXLSX_SHIPMENT_REPORTS_CARDBOARD_WORK->value;

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
        GetExcelExportFilePath $getExcelExportFilePath,
        CheckBatchParameter $checkBatchParameter,
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->getTemplateFileName = $getTemplateFileName;
        $this->getTemplateFilePath = $getTemplateFilePath;
        $this->getExcelExportFilePath = $getExcelExportFilePath;
        $this->checkBatchParameter = $checkBatchParameter;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $batchExecutionId  = $this->argument('t_execute_batch_instruction_id');  // for batch execute id
        try {
            /**
            * [共通処理] 開始処理
            * バッチ実行指示テーブル（t_execute_batch_instruction ）から該当バッチの取得と開始処理
            * t_execute_batch_instruction_id より該当バッチを取得し以下の情報を書き込む
            * - バッチ開始時刻
            */
            $batchExecute = $this->startBatchExecute->execute($batchExecutionId);

            $accountCode = $batchExecute->account_cd;     // for account cd
            $accountId = $batchExecute->m_account_id;   // for m account id
            $batchType = $batchExecute->execute_batch_type;  // for batch type

            if (app()->environment('testing')) {
                // テスト環境の場合
                TenantDatabaseManager::setTenantConnection($accountCode . '_db_testing');
            } else {
                TenantDatabaseManager::setTenantConnection($accountCode . '_db');
            }
        } catch (Exception $e) {
            // write the log error in laravel.log for error message
            Log::error('error_message : ' . $e->getMessage());
            return;
        }

        DB::beginTransaction();
        try {

            // to required parameter
            $requiredFields = [ 'deli_inspection_date_from','deli_inspection_date_to'];
            $checkResult = $this->checkBatchParameter->execute($this->argument('json'), $requiredFields);  // to check batch json parameter

            if (!$checkResult) {
                // [パラメータが不正です。] message save to 'execute_result'
                throw new Exception(__('messages.error.invalid_parameter'), self::PRIVATE_THROW_ERR_CODE);
            }

            $searchCondition = (json_decode($this->argument('json'), true))['search_info'];  // for all search parameters

            $this->templateFileName = $this->getTemplateFileName->execute($this->reportName, $accountId);    // template file name from database
            $this->templateFilePath = $this->getTemplateFilePath->execute($accountCode, $this->templateFileName);   // to get template file path

            // to check exist template file path or not condition
            if (empty($this->templateFilePath)) {
                // [テンプレートファイルが見つかりません。（:templateFilePath）] message save to 'execute_result'
                throw new \Exception(__('messages.error.file_not_found2', ['file' => 'テンプレート']), self::PRIVATE_THROW_ERR_CODE);
            }

            $searchResult = $this->getData($searchCondition);   // to get for excel data from database
            $dataList = json_decode($searchResult, true);

            // to check excel data have or not condition
            if (count($dataList) === 0) {
                // [出力対象のデータがありませんでした。] message save to 'execute_result'
                $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                    'execute_result' =>  __('messages.error.data_not_found2', ['datatype' => '出力']),
                    'execute_status' => BatchExecuteStatusEnum::SUCCESS->value
                ]);

                DB::commit();
                return ;
            }

            $values = $this->getValues($searchCondition);    // for excel header body part
            $continuousValues = $this->getContinuousValues($dataList);   // for excel table part

            // write data to excel
            $erm = new ExcelReportManager($this->templateFilePath);
            $erm->setValues($values, $continuousValues);

            $savePath = $this->getExcelExportFilePath->execute($accountCode, $batchType, $batchExecutionId);  // to get base file path
            $result = $erm->save($savePath);

            // check to upload permission allow or not
            if (!$result) {
                // [AWS S3へのファイルのアップロードに失敗しました。] message save to 'execute_result'
                throw new \Exception(__('messages.error.upload_s3_failed'), self::PRIVATE_THROW_ERR_CODE);
            }

            /**
             * [共通処理] 終了処理
             * バッチ実行指示テーブルの該当バッチに以下の情報を書き込む
             * - バッチ終了時刻
             * - (実行状態)
             * - (実行結果)
             * - (格納ファイルパス)
             */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' =>  __('messages.info.notice_output_count', ['count' => count($dataList)]),  // 〇〇件出力しました。
                'execute_status' => BatchExecuteStatusEnum::SUCCESS->value,
                'file_path' => $savePath
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            // write the log error in laravel.log for error message
            Log::error('error_message : ' . $e->getMessage());

            /**
             * [共通処理] 終了処理
             * バッチ実行指示テーブルの該当バッチに以下の情報を書き込む
             * - バッチ終了時刻
             * - (実行状態)
             * - (実行結果)
             */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' =>  ($e->getCode() == self::PRIVATE_THROW_ERR_CODE) ? $e->getMessage() : BatchExecuteStatusEnum::FAILURE->label(),
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value
            ]);
        }
    }


    /**
    * To get data the related to search parameter
    *
    * @param  array $param  Search parameter
    * @return array ( search data )
    */
    private function getData($param)
    {
        $query = CardboardLogModel::query()
                    ->select(
                        'deli_inspection_date AS 検品日',  // Selecting 検品日
                        'cardboard_type AS 段ボール名',  // Selecting 段ボール名
                        DB::raw('SUM(use_vol) AS 使用個数')  // Summing up for 使用個数 ( I want to use SUM, So I use DB:raw )
                    )
                    // Group by the fields that are not aggregate functions
                    ->groupBy('deli_inspection_date', 'cardboard_type')

                    // Order by fields
                    ->orderBy('deli_inspection_date')
                    ->orderBy('cardboard_type');

        // Apply the conditions based on the input
        if (!empty($param['deli_inspection_date_from'])) {
            $query->where('deli_inspection_date', '>=', $param['deli_inspection_date_from']);
        }

        if (!empty($param['deli_inspection_date_to'])) {
            $query->where('deli_inspection_date', '<=', $param['deli_inspection_date_to']);
        }

        try {
            $result = $query->get();
        } catch (\Throwable $th) {
            $result = [];
            Log::error('Error occurred: ' . $th->getMessage());
        }

        return $result;

    }

    /**
    * Get  values for excel common head part
    *
    * @param  array $searchCondition  Search parameter
    * @return array ( search parameter data )
    */
    private function getValues($searchCondition)
    {
        $values = [
            'items' => ['検品日from','検品日to'],
            'data' => [$searchCondition['deli_inspection_date_from'], $searchCondition['deli_inspection_date_to']],
        ];
        return $values;
    }

    /**
    * Get continuous values for excel table
    *
    * @param  array $dataList  Excel table data from database
    * @return array ( excel table data )
    */
    private function getContinuousValues($dataList)
    {
        foreach ($dataList as $item) {
            $data[] = [
                $item['検品日'],
                $item['段ボール名'],
                $item['使用個数']
            ];
        }
        $continuousValues = [
            'items' => ['検品日', '段ボール名', '使用個数'],
            'data' => $data,
        ];
        return $continuousValues;
    }

}
