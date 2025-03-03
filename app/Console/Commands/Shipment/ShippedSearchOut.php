<?php

namespace App\Console\Commands\Shipment;

use App\Enums\BatchExecuteStatusEnum;
use App\Enums\ItemNameType;
use App\Enums\ProgressTypeEnum;
use App\Models\Master\Base\ItemnameTypeModel;
use App\Models\Order\Base\OrderHdrModel;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Common\Gfh1207\CheckBatchParameter;
use App\Modules\Order\Gfh1207\Enums\ShippedDataReportTypeEnum;
use App\Modules\Order\Gfh1207\GetExcelExportFilePath;
use App\Modules\Order\Gfh1207\GetTemplateFileName;
use App\Modules\Order\Gfh1207\GetTemplateFilePath;
use App\Services\ExcelReportManager;
use App\Services\TenantDatabaseManager;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShippedSearchOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ShippedSearchOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

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
        try {
            /**
            * [共通処理] 開始処理
            * バッチ実行指示テーブル（t_execute_batch_instruction ）から該当バッチの取得と開始処理
            * t_execute_batch_instruction_id より該当バッチを取得し以下の情報を書き込む
            * - バッチ開始時刻
            */
            $batchExecute = $this->startBatchExecute->execute($this->argument('t_execute_batch_instruction_id'));

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
            $paramKey = [ 'process_type','order_date_from','order_date_to','deli_plan_date_from','deli_plan_date_to','inspection_date_from','inspection_date_to',
                                        'order_id_from','order_id_to','one_item_only','has_noshi','page_cd','store_group','order_type'];


            $checkResult = $this->checkBatchParameter->execute($this->argument('json'), $paramKey);  // to check batch json parameter

            if (!$checkResult) {
                // [パラメータが不正です。] message save to 'execute_result'
                throw new \Exception(__('messages.error.invalid_parameter'), self::PRIVATE_THROW_ERR_CODE);
            }

            $searchCondition = (json_decode($this->argument('json'), true))['search_info'];  // for all search parameters
            $processType = $searchCondition['process_type'];   // for order method
            $reportName = ShippedDataReportTypeEnum::tryFrom($processType)->label();

            $this->templateFileName = $this->getTemplateFileName->execute($reportName, $accountId);    // template file name from database
            $this->templateFilePath = $this->getTemplateFilePath->execute($accountCode, $this->templateFileName);   // to get template file path

            // to check exist template file path or not condition
            if (empty($this->templateFilePath)) {
                // [テンプレートファイルが見つかりません。]message save to 'execute_result'
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

            $orderTypeName = $searchCondition['order_type'] == null ? "" : $this->getOrderTypeName($searchCondition['order_type'], $accountId);  // to get order type name from database
            $values = $this->getValues($searchCondition, $orderTypeName);    // for excel header body part
            $continuousValues = $this->getContinuousValues($dataList, $processType);   // for excel table part

            // write data to excel
            $erm = new ExcelReportManager($this->templateFilePath);
            $erm->setValues($values, $continuousValues);

            $savePath = $this->getExcelExportFilePath->execute($accountCode, $batchType, $this->argument('t_execute_batch_instruction_id'));  // to get base file path
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
    * to get item type name from database
    *
    * @return string (item type name)
    */
    private function getOrderTypeName($orderType, $accountId)
    {
        try {
            $itemNameData = ItemnameTypeModel::query()
            ->where('m_itemname_types_id', $orderType)
            ->where('m_account_id', $accountId)
            ->select('m_itemname_type_name')
            ->get();
            $itemName = json_decode($itemNameData, true);
            return   count($itemName) > 0 ? $itemName[0]['m_itemname_type_name'] : "";
        } catch (QueryException $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return ['error' => 'Database connection error. Please try again later.'];
        }
    }

    /**
     * get  values for excel common head part
     *
     * @return array ( search parameter data )
    */
    private function getValues($searchCondition, $orderTypeName)
    {
        $values = [
            'items' => ['出荷予定日from', '出荷予定日to', '受注日from','受注日to','検品日from','検品日to','商品ページコード','店舗集計グループ','受注方法','受注IDfrom','受注IDto','一品一葉'],
            'data' => [$searchCondition['deli_plan_date_from'], $searchCondition['deli_plan_date_to'], $searchCondition['order_date_from'],$searchCondition['order_date_to'],$searchCondition['inspection_date_from'],
                            $searchCondition['inspection_date_to'],$searchCondition['page_cd'],($searchCondition['store_group'] == "" ? '全て' : $searchCondition['store_group']),($searchCondition['order_type'] == "" ? '全て' : $orderTypeName),
                            $searchCondition['order_id_from'],$searchCondition['order_id_to'],($searchCondition['one_item_only'] == 0 ? "全て" : "一品一葉")],
        ];
        return $values;
    }

    /**
    * get continuous values for excel table
    *
    * @return array ( excel table data )
    */
    private function getContinuousValues($dataList, $processType)
    {
        if ($processType == ShippedDataReportTypeEnum::BY_SHIPMENT_DATE->value) {
            $data = [];
            foreach ($dataList as $item) {
                $data[] = [
                    $item['出荷予定日'],
                    $item['受注件数'],
                    $item['受注数量合計'],
                    $item['受注金額合計'],
                    $item['受注送料'],
                    $item['未出荷件数'],
                    $item['未出荷数量合計'],
                    $item['未出荷金額合計'],
                    $item['未出荷送料'],
                    $item['出荷済件数'],
                    $item['出荷済数量合計'],
                    $item['出荷済金額合計'],
                    $item['出荷済送料'],
                ];
            }
            $continuousValues = [
                'items' => ['出荷予定日', '受注件数', '受注数量合計','受注金額合計','受注送料','未出荷件数','未出荷数量合計','未出荷金額合計','未出荷送料', '出荷済件数' ,
                                  '出荷済数量合計' , '出荷済金額合計' , '出荷済送料' ],
                'data' => $data,
            ];


        } elseif ($processType == ShippedDataReportTypeEnum::BY_PRODUCT->value) {
            $data = [];
            foreach ($dataList as $item) {
                $data[] = [
                    $item['出荷予定日'],
                    $item['商品ページコード'],
                    $item['商品名'],
                    $item['受注数量合計'],
                    $item['受注金額合計'],
                    $item['未出荷数量合計'],
                    $item['未出荷金額合計'],
                    $item['出荷済数量合計'],
                    $item['出荷済金額合計'],
                ];
            }

            $continuousValues = [
                'items' => ['出荷予定日', '商品ページコード', '商品名','受注数量合計','受注金額合計','未出荷数量合計','未出荷金額合計','出荷済数量合計','出荷済金額合計'],
                'data' => $data,
            ];
        } else {
            $data = [];
            foreach ($dataList as $item) {
                $data[] = [
                    $item['出荷予定日'],
                    $item['SKUコード'],
                    $item['受注明細SKU'],
                    $item['受注数量合計'],
                    $item['受注金額合計'],
                    $item['未出荷数量合計'],
                    $item['未出荷金額合計'],
                    $item['出荷済数量合計'],
                    $item['出荷済金額合計'],
                ];
            }

            $continuousValues = [
                'items' => ['出荷予定日', 'SKUコード', '受注明細SKU','受注数量合計','受注金額合計','未出荷数量合計','未出荷金額合計','出荷済数量合計','出荷済金額合計'],
                'data' => $data,
            ];
        }
        return $continuousValues;
    }


    /**
     * to get data the related to search parameter
     *
     * @return array ( search data )
    */
    private function getData($param)
    {
        $query = OrderHdrModel::query()
            ->join('t_order_destination', 't_order_hdr.t_order_hdr_id', '=', 't_order_destination.t_order_hdr_id')
            ->join('t_order_dtl', 't_order_destination.t_order_destination_id', '=', 't_order_dtl.t_order_destination_id')
            ->join('t_deli_hdr', 't_order_destination.t_order_destination_id', '=', 't_deli_hdr.t_order_destination_id');

        if (!empty($param['process_type']) && $param['process_type'] == ShippedDataReportTypeEnum::BY_SKU->value) {
            $query->join('t_order_dtl_sku', 't_order_dtl.t_order_dtl_id', '=', 't_order_dtl_sku.t_order_dtl_id')
                      ->leftJoin('m_ami_sku', 't_order_dtl_sku.item_id', '=', 'm_ami_sku.m_ami_sku_id');
        }

        if (isset($param['has_noshi'])) {
            $query->leftJoin('t_order_dtl_noshi', 't_order_dtl.t_order_dtl_id', '=', 't_order_dtl_noshi.t_order_dtl_id');
        }

        if (!empty($param['store_group'])) {
            $query->join('m_itemname_types as mit1', function ($join) {
                $join->on('t_order_hdr.sales_store', '=', 'mit1.m_itemname_types_id')
                     ->where('mit1.m_itemname_type', ItemNameType::SalesContact->value);
            })
            ->join('m_itemname_types as mit2', function ($join) use ($param) {
                $join->on('mit1.m_itemname_type_code', '=', 'mit2.m_itemname_types_id')
                     ->where('mit2.m_itemname_type', ItemNameType::CustomerRank->value)
                     ->where('mit2.m_itemname_type_code', $param['store_group']);
            });
        }


        // Conditions
        if (!empty($param['order_date_from'])) {
            $query->where('t_order_hdr.order_datetime', '>=', $param['order_date_from']);
        }
        if (!empty($param['order_date_to'])) {
            $query->where('t_order_hdr.order_datetime', '<=', $param['order_date_to']);
        }
        if (!empty($param['order_id_from'])) {
            $query->where('t_order_hdr.t_order_hdr_id', '>=', $param['order_id_from']);
        }
        if (!empty($param['order_id_to'])) {
            $query->where('t_order_hdr.t_order_hdr_id', '<=', $param['order_id_to']);
        }
        if (!empty($param['order_type'])) {
            $query->where('t_order_hdr.order_type', '=', $param['order_type']);
        }
        if (!empty($param['page_cd'])) {
            $query->where('t_order_dtl.sell_cd', '=', $param['page_cd']);
        }
        if (!empty($param['inspection_date_from'])) {
            $query->where('t_deli_hdr.deli_inspection_date', '>=', $param['inspection_date_from']);
        }
        if (!empty($param['inspection_date_to'])) {
            $query->where('t_deli_hdr.deli_inspection_date', '<=', $param['inspection_date_to']);
        }
        if (!empty($param['deli_plan_date_from'])) {
            $query->where('t_order_destination.deli_plan_date', '>=', $param['deli_plan_date_from']);
        }
        if (!empty($param['deli_plan_date_to'])) {
            $query->where('t_order_destination.deli_plan_date', '<=', $param['deli_plan_date_to']);
        }
        if (!empty($param['one_item_only']) && $param['one_item_only'] == 1) {
            $query->where('t_order_destination.gp1_type', '=', 1);
        }

        $query->whereNotIn('t_order_hdr.progress_type', [ProgressTypeEnum::Cancelled->value, ProgressTypeEnum::Returned->value]);

        if (isset($param['has_noshi'])) {
            if ($param['has_noshi'] == 0) {
                $query->whereNotNull('t_order_dtl_noshi.t_order_dtl_noshi_id');
            } elseif ($param['has_noshi'] == 1) {
                $query->whereNull('t_order_dtl_noshi.t_order_dtl_noshi_id');
            }
        }

        $commonSelects  = [
            't_order_destination.deli_plan_date AS 出荷予定日',
        ];

        // Grouping and selecting based on process_type
        if (!empty($param['process_type']) && $param['process_type'] == ShippedDataReportTypeEnum::BY_SHIPMENT_DATE->value) {
            $query->select(
                DB::raw('COUNT(t_order_hdr.t_order_hdr_id) AS 受注件数'),     //Because sum and count functions can be obtained by writing DB::raw
                DB::raw('SUM(t_order_dtl.order_sell_vol) AS 受注数量合計'),     //Because sum and count functions can be obtained by writing DB::raw
                DB::raw('SUM(t_order_dtl.order_sell_price * t_order_dtl.order_sell_vol) AS 受注金額合計'),     //Because sum and count functions can be obtained by writing DB::raw
                DB::raw('SUM(t_order_destination.shipping_fee) AS 受注送料'),     //Because sum and count functions can be obtained by writing DB::raw
                DB::raw('COUNT(CASE WHEN t_deli_hdr.deli_inspection_date IS NULL THEN t_order_destination.t_order_destination_id ELSE 0 END) AS 未出荷件数'),  //Because sum and count functions can be obtained by writing DB::raw
                DB::raw('SUM(CASE WHEN t_deli_hdr.deli_inspection_date IS NULL THEN t_order_dtl.order_sell_vol ELSE 0 END) AS 未出荷数量合計'),      //Because sum and count functions can be obtained by writing DB::raw
                DB::raw('SUM(CASE WHEN t_deli_hdr.deli_inspection_date IS NULL THEN t_order_dtl.order_sell_price * t_order_dtl.order_sell_vol ELSE 0 END) AS 未出荷金額合計'),     //Because sum and count functions can be obtained by writing DB::raw
                DB::raw('SUM(CASE WHEN t_deli_hdr.deli_inspection_date IS NULL THEN t_order_destination.shipping_fee ELSE 0 END) AS 未出荷送料'),     //Because sum and count functions can be obtained by writing DB::raw
                DB::raw('SUM(CASE WHEN t_deli_hdr.deli_inspection_date IS NOT NULL THEN t_order_dtl.order_sell_vol ELSE 0 END) AS 出荷済数量合計'),    //Because sum and count functions can be obtained by writing DB::raw
                DB::raw('SUM(CASE WHEN t_deli_hdr.deli_inspection_date IS NOT NULL THEN t_order_dtl.order_sell_price * t_order_dtl.order_sell_vol ELSE 0 END) AS 出荷済金額合計'),    //Because sum and count functions can be obtained by writing DB::raw
                DB::raw('COUNT(CASE WHEN t_deli_hdr.deli_inspection_date IS NOT NULL THEN t_order_destination.t_order_destination_id ELSE NULL END) AS 出荷済件数'),     //Because sum and count functions can be obtained by writing DB::raw
                DB::raw('SUM(CASE WHEN t_deli_hdr.deli_inspection_date IS NOT NULL THEN t_order_destination.shipping_fee ELSE 0 END) AS 出荷済送料'),     //Because sum and count functions can be obtained by writing DB::raw
            )
            ->addSelect($commonSelects)
            ->groupBy('t_order_destination.deli_plan_date')
            ->orderBy('t_order_destination.deli_plan_date', 'ASC');
        } elseif (!empty($param['process_type']) && $param['process_type'] == ShippedDataReportTypeEnum::BY_PRODUCT->value) {
            $query->select(
                't_order_dtl.sell_cd AS 商品ページコード',
                't_order_dtl.sell_name AS 商品名',
                DB::raw('SUM(t_order_dtl.order_sell_vol) AS 受注数量合計'),     //Because sum and count functions can be obtained by writing DB::raw
                DB::raw('SUM(t_order_dtl.order_sell_price * t_order_dtl.order_sell_vol) AS 受注金額合計'),     //Because sum and count functions can be obtained by writing DB::raw
                DB::raw('SUM(CASE WHEN t_deli_hdr.deli_inspection_date IS NULL THEN t_order_dtl.order_sell_vol ELSE 0 END) AS 未出荷数量合計'),      //Because sum and count functions can be obtained by writing DB::raw
                DB::raw('SUM(CASE WHEN t_deli_hdr.deli_inspection_date IS NULL THEN t_order_dtl.order_sell_price * t_order_dtl.order_sell_vol ELSE 0 END) AS 未出荷金額合計'),     //Because sum and count functions can be obtained by writing DB::raw
                DB::raw('SUM(CASE WHEN t_deli_hdr.deli_inspection_date IS NOT NULL THEN t_order_dtl.order_sell_vol ELSE 0 END) AS 出荷済数量合計'),    //Because sum and count functions can be obtained by writing DB::raw
                DB::raw('SUM(CASE WHEN t_deli_hdr.deli_inspection_date IS NOT NULL THEN t_order_dtl.order_sell_price * t_order_dtl.order_sell_vol ELSE 0 END) AS 出荷済金額合計')    //Because sum and count functions can be obtained by writing DB::raw
            )
            ->addSelect($commonSelects)
            ->groupBy('t_order_destination.deli_plan_date', 't_order_dtl.sell_cd', 't_order_dtl.sell_name')
            ->orderBy('t_order_destination.deli_plan_date', 'ASC')
            ->orderBy('t_order_dtl.sell_cd', 'ASC');
        } else {
            $query->select(
                't_order_dtl_sku.item_cd AS SKUコード',
                'm_ami_sku.sku_name AS 受注明細SKU',
                DB::raw('SUM(t_order_dtl_sku.order_sell_vol) AS 受注数量合計'),     //Because sum and count functions can be obtained by writing DB::raw
                DB::raw('SUM(m_ami_sku.sales_price * t_order_dtl_sku.order_sell_vol) AS 受注金額合計'),     //Because sum and count functions can be obtained by writing DB::raw
                DB::raw('SUM(CASE WHEN t_deli_hdr.deli_inspection_date IS NULL THEN t_order_dtl_sku.order_sell_vol ELSE 0 END) AS 未出荷数量合計'),      //Because sum and count functions can be obtained by writing DB::raw
                DB::raw('SUM(CASE WHEN t_deli_hdr.deli_inspection_date IS NULL THEN m_ami_sku.sales_price * t_order_dtl_sku.order_sell_vol ELSE 0 END) AS 未出荷金額合計'),     //Because sum and count functions can be obtained by writing DB::raw
                DB::raw('SUM(CASE WHEN t_deli_hdr.deli_inspection_date IS NOT NULL THEN t_order_dtl_sku.order_sell_vol ELSE 0 END) AS 出荷済数量合計'),    //Because sum and count functions can be obtained by writing DB::raw
                DB::raw('SUM(CASE WHEN t_deli_hdr.deli_inspection_date IS NOT NULL THEN m_ami_sku.sales_price * t_order_dtl_sku.order_sell_vol ELSE 0 END) AS 出荷済金額合計')    //Because sum and count functions can be obtained by writing DB::raw
            )
            ->addSelect($commonSelects)
            ->groupBy('t_order_destination.deli_plan_date', 't_order_dtl_sku.item_cd', 'm_ami_sku.sku_name')
            ->orderBy('t_order_destination.deli_plan_date', 'ASC')
            ->orderBy('t_order_dtl_sku.item_cd', 'ASC');
        }

        try {
            $result = $query->get();
        } catch (\Throwable $th) {
            $result = [];
            Log::error('Error occurred: ' . $th->getMessage());
        }

        return $result;
    }

}
