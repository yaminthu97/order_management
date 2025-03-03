<?php

namespace App\Console\Commands\Goto;

use Exception;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

use App\Enums\ItemNameType;
use App\Enums\ProgressTypeEnum;
use App\Enums\BatchExecuteStatusEnum;
use App\Modules\Master\Gfh1207\Enums\BatchListEnum;
use App\Modules\Master\Gfh1207\Enums\TemplateFileNameEnum;
use App\Modules\Master\Gfh1207\Enums\StoreAggregationGroupEnum;

use App\Modules\Order\Base\SearchInterface;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;

use App\Modules\Order\Gfh1207\GetTemplateFileName;
use App\Modules\Order\Gfh1207\GetTemplateFilePath;
use App\Modules\Order\Gfh1207\GetExcelExportFilePath;

use App\Services\ExcelReportManager;
use App\Services\TenantDatabaseManager;


class ExternalSalesFileOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ExternalSalesFileOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    private const PRODUCT_MODIFIER = '×';
    private const PRODUCT_DELIMITER = ',';

    private const PRIVATE_THROW_ERR_CODE = -1;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '外販申し送りファイルを作成する';
    // テンプレートファイルパス
    protected $templateFilePath;
    // テンプレートファイル名
    protected $templateFileName;
    // 開始時処理
    protected $startBatchExecute;
    // 終了時処理
    protected $endBatchExecute;
    // 受注検索モジュール
    protected $search;

    /**
     * Create a new command instance.
     */
    public function __construct(
        StartBatchExecuteInstructionInterface $startBatchExecute,
        EndBatchExecuteInstructionInterface $endBatchExecute,
        SearchInterface $search,
        GetTemplateFileName $getTemplateFileName,
        GetTemplateFilePath $getTemplateFilePath,
        GetExcelExportFilePath $getExcelExportFilePath,
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->search = $search;
        $this->getTemplateFileName = $getTemplateFileName;
        $this->getTemplateFilePath = $getTemplateFilePath;
        $this->getExcelExportFilePath = $getExcelExportFilePath;
        parent::__construct();
    }

    private function setTenantConnection($accountCode)
    {
        $TenantConnectionValue = $accountCode . '_db';

        if (app()->environment('testing')) {
            // テスト環境の場合
            $TenantConnectionValue = $accountCode . '_db_testing';
        }

        TenantDatabaseManager::setTenantConnection($TenantConnectionValue);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $searchInfo = json_decode($this->argument('json'), true);

        try {
            if (!isset($searchInfo['m_account_id'])) {
                $parameterErrorStr = __('messages.error.invalid_parameter');
                Log::info($parameterErrorStr);
                // [パラメータが不正です。] message save to 'execute_result'
                throw new \Exception($parameterErrorStr, self::PRIVATE_THROW_ERR_CODE);
            }

            /**
             * [共通処理] 開始処理
             * バッチ実行指示テーブル（t_execute_batch_instruction ）に新規作成と開始処理
             * - バッチ開始時刻
             */
            $batchExecute = $this->startBatchExecute->execute(null, [
                'm_account_id' => $searchInfo['m_account_id'],
                'execute_batch_type' => BatchListEnum::EXPXLSX_EXTERNAL_SALES_FILE->value,
                'execute_conditions' => $searchInfo,
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return;
        }

        $accountCode = $batchExecute->account_cd;
        $accountId = $batchExecute->m_account_id;
        $batchType = $batchExecute->execute_batch_type;

        $dt = Carbon::now();
        $orderDt = $dt->copy()->subDay();

        if (isset($searchInfo['date'])) {
            $orderDt = Carbon::create($searchInfo['date']);
        }

        $this->setTenantConnection($accountCode);

        DB::beginTransaction();

        try {
            // 指定日の受注を取得([受注配送先],[受注明細],[項目名称マスタ]も合わせ)
            $orders = $this->search->execute([
                'account_id' => $accountId,
                'order_date' => $orderDt
            ], [
                'with' => [
                    'cust',
                    'cust.custRunk',
                    'orderDestination',
                    'salesStoreItemnameTypes',
                    'orderDestination.orderDtl',
                ],
            ]);

            // to check excel data have or not condition
            if (count($orders) < 1) {
                DB::rollBack();

                $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                    'execute_result' => __('messages.info.display.no_data', ['data' => '検索結果']),
                    'execute_status' => BatchExecuteStatusEnum::SUCCESS->value
                ]);

                return;
            }

            $reportName = TemplateFileNameEnum::EXPXLSX_EXTERNAL_SALES->value;
            $this->templateFileName = $this->getTemplateFileName->execute($reportName, $accountId);

            if (is_null($this->templateFileName)) {
                throw new \Exception(__('messages.error.data_not_found', ['data' => $reportName . 'のテンプレートデータ', 'id' => '']), self::PRIVATE_THROW_ERR_CODE);
            }

            $this->templateFilePath = $this->getTemplateFilePath->execute($accountCode, $this->templateFileName);

            // to check exist template file path or not condition
            if (empty($this->templateFilePath)) {
                // [テンプレートファイルが見つかりません。（:templateFilePath）] message save to 'execute_result'
                throw new \Exception(__('messages.error.file_not_found', ['file' => 'テンプレート', 'path' => $this->templateFilePath]), self::PRIVATE_THROW_ERR_CODE);
            }

            $values = $this->getValues($dt);
            $continuousValues = $this->getContinuousValues($orders);

            if (count($continuousValues) < 1) {
                DB::rollBack();

                $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                    'execute_result' => __('messages.info.display.no_data', ['data' => 'order']),
                    'execute_status' => BatchExecuteStatusEnum::SUCCESS->value
                ]);

                return;
            }

            // write data to excel
            $erm = new ExcelReportManager($this->templateFilePath);
            $erm->setValues($values, $continuousValues);
            $savePath = $this->getExcelExportFilePath->execute($accountCode, $batchType, $batchExecute->t_execute_batch_instruction_id);  // to get base file path

            if ($erm->save($savePath) !== true) {
                throw new \Exception(__('messages.error.upload_s3_file_failed', ['file' => $savePath]), self::PRIVATE_THROW_ERR_CODE);
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
                'execute_result' => __('messages.info.notice_output_count', ['count' => count($continuousValues['data'])]),
                'execute_status' => BatchExecuteStatusEnum::SUCCESS->value,
                'file_path' => $savePath
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            // default fail message
            $errorMessage = BatchExecuteStatusEnum::FAILURE->label();

            // If there is an Exception error message, write it to the log
            if ($e->getCode() == self::PRIVATE_THROW_ERR_CODE) {
                $errorMessage = $e->getMessage();
            }

            /**
             * [共通処理] 終了処理
             * バッチ実行指示テーブルの該当バッチに以下の情報を書き込む
             * - バッチ終了時刻
             * - (実行状態)
             * - (実行結果)
             */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => $errorMessage,
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value
            ]);
        }
    }

    /**
     * excel 用の単項目データを生成
     *
     * @return array ( excel table data )
     */
    private function getValues($dt)
    {
        return [
            'items' => ['出力日'],
            'data' => [$dt->format('Y/m/d')],
        ];
    }

    /**
     * orderHdrModel から excel 用の連続項目データを生成
     *
     * @return array ( excel table data )
     */
    private function getContinuousValues($orders)
    {
        if (!isset($orders[0]) || !isset($orders[0]->orderDestination[0]) || !isset($orders[0]->orderDestination[0]->orderDtl[0])) {
            return [];
        }

        $continuousValues = [
            'items' => [
                '受注日',
                '出荷日',
                'お届け日',
                '受注ID',
                '配送先番号',
                '販売窓口',
                '顧客コード',
                '顧客名',
                '割引率',
                '主な商品',
                '商品計',
                '割引計'
            ]
        ];

        $data = [];

        foreach ($orders as $item) {
            $progressType = $item->progress_type;

            if ($progressType == ProgressTypeEnum::Cancelled->value || $progressType == ProgressTypeEnum::Returned->value) {
                continue;
            }

            $cust = $item->cust;

            if (!isset($cust->custRunk) || $cust->custRunk->m_itemname_type != ItemNameType::CustomerRank->value || $cust->custRunk->m_itemname_type_code == StoreAggregationGroupEnum::NORMAL->value) {
                continue;
            }

            $tbl = [];
            $discount = $item->discount; //配送の最初の行に割引合計を入れる。

            foreach ($item->orderDestination as $val) {
                foreach ($val->orderDtl as $dtl) {
                    $orderSellPrice = $dtl->order_sell_price;
                    $cancelTimestamp = $dtl->cancel_timestamp;

                    if (!isset($dtl->t_order_destination_id) || is_null($dtl->t_order_destination_id) ||
                        !isset($dtl->sell_cd) || strlen($dtl->sell_cd) < 1 ||
                        $cancelTimestamp != '0000-00-00 00:00:00.000000' ||
                        $orderSellPrice < 1 //金額が無いものが感謝品
                    ) {
                        continue;
                    }

                    $orderDestinationId = $dtl->t_order_destination_id;

                    if (!isset($tbl[$orderDestinationId])) {
                        $tbl[$orderDestinationId] = [];
                    }

                    $sellCd = $dtl->sell_cd;

                    if (!isset($tbl[$orderDestinationId][$sellCd])) {
                        $tbl[$orderDestinationId][$sellCd] = [
                            'order_destination' => $val,
                            'order_dtl' => $dtl,
                            'main_products' => [],
                            'total' => 0,
                            'discount_total' => $discount
                        ];
                    }

                    $orderSellVol = $dtl->order_sell_vol;
                    $tbl[$orderDestinationId][$sellCd]['main_products'][] = $sellCd . self::PRODUCT_MODIFIER . $orderSellVol;
                    $tbl[$orderDestinationId][$sellCd]['total'] += $orderSellPrice * $orderSellVol;

                    $discount = 0; //配送の最初の行に割引合計を入れたのでそれ以降を 0 にする。
                }
            }

            $salesStoreItemnameTypes = $item->salesStoreItemnameTypes;

            foreach ($tbl as $orderDestinationId => $row) {
                foreach ($row as $sellCd => $val) {
                    $orderDestination = $val['order_destination'];

                    $data[] = [
                        Carbon::parse($item->order_datetime)->format('Y/m/d'), //受注日
                        Carbon::parse($orderDestination->deli_plan_date)->format('Y/m/d'), //出荷予定日
                        Carbon::parse($orderDestination->deli_hope_date)->format('Y/m/d'), //配達希望日
                        $item->t_order_hdr_id, //受注ID
                        $orderDestination->order_destination_seq, //受注配送先ID
                        $salesStoreItemnameTypes->m_itemname_type_name, //販売窓口
                        $item->m_cust_id, //顧客ID
                        $cust->name_kanji, //顧客名
                        $cust->discount_rate, //割引率
                        implode(self::PRODUCT_DELIMITER, $val['main_products']), //主な商品
                        $val['total'],  //合計金額
                        $val['discount_total'] //割引金額計
                    ];
                }
            }
        }

        if (count($data) < 1) {
            return [];
        }

        $continuousValues['data'] = $data;
        return $continuousValues;
    }
}
