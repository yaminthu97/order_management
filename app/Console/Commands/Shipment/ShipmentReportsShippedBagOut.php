<?php

namespace App\Console\Commands\Shipment;

use App\Enums\BatchExecuteStatusEnum;
use App\Enums\ItemNameType;
use App\Models\Order\Gfh1207\OrderDestinationModel;
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

class ShipmentReportsShippedBagOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ShipmentReportsShippedBagOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '出荷系帳票出力画面で指定した条件にて手提げ出荷未出荷一覧を作成し、バッチ実行確認へと出力する';

    // テンプレートファイルパス
    protected $templateFilePath;

    // テンプレートファイル名
    protected $templateFileName;

    protected $batchName = '手提げ出荷未出荷一覧出力';

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    // for report name
    protected $reportName = TemplateFileNameEnum::EXPXLSX_SHIPMENT_REPORTS_SHIPPED_BAG->value;

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

    // for handbag value
    private const HAND_BAG = 1;

    /**
     * Create a new command instance.
     */
    public function __construct(
        StartBatchExecuteInstructionInterface $startBatchExecute,
        EndBatchExecuteInstructionInterface $endBatchExecute,
        GetTemplateFileName $getTemplateFileName,
        CheckBatchParameter $checkBatchParameter,
        GetTemplateFilePath $getTemplateFilePath,
        GetExcelExportFilePath $getExcelExportFilePath,
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->getTemplateFileName = $getTemplateFileName;
        $this->getTemplateFilePath = $getTemplateFilePath;
        $this->checkBatchParameter = $checkBatchParameter;
        $this->getExcelExportFilePath = $getExcelExportFilePath;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $batchExecutionId  = $this->argument('t_execute_batch_instruction_id');  // for batch execute id
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
            $requiredFields = [ 'deli_plan_date_from','deli_plan_date_to','cust_runk_id'];
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
                // [テンプレートファイルが見つかりません。] message save to 'execute_result'
                throw new Exception(__('messages.error.file_not_found2', ['file' => 'テンプレート']), self::PRIVATE_THROW_ERR_CODE);
            }

            $dataList = $this->getData($searchCondition);   // to get for excel data from database

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
                'execute_result' => __('messages.info.notice_output_count', ['count' => count($dataList)]),  // 〇〇件出力しました。
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
                'execute_result' => ($e->getCode() == self::PRIVATE_THROW_ERR_CODE) ? $e->getMessage() : BatchExecuteStatusEnum::FAILURE->label(),
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value
            ]);
        }
    }

    /**
    * To get data the related to search parameter
    *
    * @param array $param Search parameter
    * @return array ( search data )
    */
    private function getData($param)
    {
        // Join the tables
        $query = OrderDestinationModel::with([
           'customer',
           'deliHdr',
           'orderDtls.attachmentItems' => function ($query) {
               $query->whereHas('category', function ($query) {
                   $query->where('m_itemname_types.m_itemname_type_code', self::HAND_BAG)
                       ->where('m_itemname_types.m_itemname_type', ItemNameType::AttachmentCategory->value);
               });
           },
           'orderDtls.attachmentItems.amiAttachmentItem' ,
        ])
        // Conditions
        ->when(!empty($param['deli_plan_date_from']), function ($query) use ($param) {
            $query->where('t_order_destination.deli_plan_date', '>=', $param['deli_plan_date_from']);
        })
        ->when(!empty($param['deli_plan_date_to']), function ($query) use ($param) {
            $query->where('t_order_destination.deli_plan_date', '<=', $param['deli_plan_date_to']);
        })
        ->when(!empty($param['cust_runk_id']), function ($query) use ($param) {
            $query->whereHas('customer', function ($q) use ($param) {
                $q->where('m_cust_runk_id', '=', $param['cust_runk_id']);
            });
        })
        ->get();

        $finalData = $query
        ->groupBy(fn ($destination) => $destination->deli_plan_date) // Group by deli_plan_date
        ->map(function ($groupByDate) {
            return $groupByDate
                ->map(function ($destination) {
                    // Flatten and get unique attachment_item_cd
                    $destination->unique_attachment_item_codes = $destination->orderDtls
                        ->flatMap(fn ($orderDtl) => $orderDtl->attachmentItems->pluck('attachment_item_cd'))
                        ->unique()
                        ->values()
                        ->all();
                    return $destination;
                })
                ->groupBy(fn ($destination) => $destination->unique_attachment_item_codes) // Secondary group by attachment_item_cd
                ->map(function ($groupByAttachmentItemCd) {
                    return $groupByAttachmentItemCd->sortBy('deli_plan_date'); // Sort each group
                });
        })
        ->values()
        ->toArray();

        $resultItems = [];
        foreach ($finalData as $group) {
            foreach ($group as $group1) {
                $shippedCount = 0; // 出荷済数
                $unshippedCount = 0; // 未出荷数
                foreach ($group1 as $destination) {
                    $shipmentDate = $destination['deli_plan_date'] ?? null;
                    foreach ($destination['order_dtls'] as $orderDtl) {
                        foreach ($orderDtl['attachment_items'] as $attachmentItem) {
                            $itemCode = $attachmentItem['ami_attachment_item']['attachment_item_cd'] ?? null;
                            $festaCode = $attachmentItem['ami_attachment_item']['reserve1'] ?? null;

                            if (!is_null($attachmentItem['deli_decision_date'])) {
                                $shippedCount += $attachmentItem['order_sell_vol'] ?? 0;  // 出荷済数
                            } else {
                                $unshippedCount += $attachmentItem['order_sell_vol'] ?? 0;  // 未出荷数
                            }
                        }
                    }
                }
                // Add the result to the resultItems array
                $resultItems[] = [
                    '出荷予定日' => $shipmentDate,
                    '通販コード' => $itemCode,
                    'FESTAコード' => $festaCode,
                    '出荷済数' => $shippedCount,
                    '未出荷数' => $unshippedCount,
                ];
            }
        }


        try {
            $result = $resultItems;
        } catch (\Throwable $th) {
            $result = [];
            Log::error('Error occurred: ' . $th->getMessage());
        }

        return $result;

    }

    /**
    * get  values for excel common head part
    *
    * @param  array $searchCondition  Search parameter
    * @return array ( search parameter data )
    */
    private function getValues($searchCondition)
    {
        $values = [
            'items' => ['出荷予定日from','出荷予定日to','顧客ランク'],
            'data' => [$searchCondition['deli_plan_date_from'], $searchCondition['deli_plan_date_to'], $searchCondition['cust_runk_id'] == null ? '全て' : $searchCondition['cust_runk_id']],
        ];
        return $values;
    }

    /**
    * get continuous values for excel table
    *
    * @param  array $dataList  Excel table data from database
    * @return array ( excel table data )
    */
    private function getContinuousValues($dataList)
    {
        foreach ($dataList as $item) {
            $data[] = [
                $item['出荷予定日'],
                $item['通販コード'],
                $item['FESTAコード'],
                $item['未出荷数'],
                $item['出荷済数'],
            ];
        }

        $continuousValues = [
            'items' => ['出荷予定日', '通販コード', 'FESTAコード','未出荷数','出荷済数' ],
            'data' => $data,
        ];
        return $continuousValues;
    }

}
