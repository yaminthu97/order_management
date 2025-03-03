<?php

namespace App\Console\Commands\Shipment;

use App\Enums\BatchExecuteStatusEnum;
use App\Models\Order\Gfh1207\OrderDestinationModel;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Common\Gfh1207\CheckBatchParameter;
use App\Modules\Master\Gfh1207\Enums\TemplateFileNameEnum;
use App\Modules\Order\Gfh1207\Enums\InspectionStatusEnum;
use App\Modules\Order\Gfh1207\GetExcelExportFilePath;
use App\Modules\Order\Gfh1207\GetTemplateFileName;
use App\Modules\Order\Gfh1207\GetTemplateFilePath;
use App\Services\ExcelReportManager;
use App\Services\TenantDatabaseManager;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShipmentReportsChecklistOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ShipmentReportsChecklistOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json :  JSON化した引数}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '出荷系帳票出力画面で指定した条件にて出荷検品チェックリストを作成し、バッチ実行確認へと出力する';

    // バッチ名
    protected $batchName = '出荷検品チェックリスト出力';

    // テンプレートファイルパス
    protected $templateFilePath;

    // テンプレートファイル名
    protected $templateFileName;

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    // レポート名
    protected $reportName = TemplateFileNameEnum::EXPXLSX_SHIPMENT_REPORTS_CHECKLIST->value;

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
    private const EMPTY_DATA_ROW_CNT = 0;

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
            $paramKey = [
                'type',
                'deli_plan_date_from',
                'deli_plan_date_to',
                'order_id_from',
                'order_id_to'
            ];

            $checkResult = $this->checkBatchParameter->execute($this->argument('json'), $paramKey);  // バッチJSONパラメータをチェックする

            if (!$checkResult) {
                // [パラメータが不正です。] メッセージを'execute_result'にセットする
                throw new \Exception(__('messages.error.invalid_parameter'), self::PRIVATE_THROW_ERR_CODE);
            }

            $searchCondition = (json_decode($this->argument('json'), true))['search_info'];

            $this->templateFileName = $this->getTemplateFileName->execute($this->reportName, $accountId);    // template file name from database

            $this->templateFilePath = $this->getTemplateFilePath->execute($accountCode, $this->templateFileName);   // to get template file path

            // テンプレートファイルパスが存在するかどうかをチェック
            if (empty($this->templateFilePath)) {
                // [テンプレートファイルが見つかりません。] メッセージを'execute_result'にセットする
                throw new Exception(__('messages.error.file_not_found2', ['file' => 'テンプレート']), self::PRIVATE_THROW_ERR_CODE);
            }

            $searchResult = $this->getData($searchCondition);  //データベースから出力データを取得
            $dataList = $searchResult;

            $recordCount = count($dataList);

            // 抽出結果がない場合、[出力対象のデータがありませんでした。]メッセージを'execute_result'にセットする
            if ($recordCount === self::EMPTY_DATA_ROW_CNT) {
                $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                    'execute_result' =>  __('messages.error.data_not_found2', ['datatype' => '出力']),
                    'execute_status' => BatchExecuteStatusEnum::SUCCESS->value
                ]);

                DB::commit();
                return;
            }

            $continuousValues = $this->getContinuousValues($dataList);

            $resultCount = count($continuousValues['data']);

            $values = $this->getValues($searchCondition, $resultCount);

            // Excelにデータを書き込む
            $erm = new ExcelReportManager($this->templateFilePath);
            $erm->setValues($values, $continuousValues);

            $savePath = $this->getExcelExportFilePath->execute($accountCode, $batchType, $batchExecutionId);  // to get base file path
            $result = $erm->save($savePath);

            // check to upload permission allow or not allow
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
                // 〇〇件出力しました。
                'execute_result' => __('messages.info.notice_output_count', ['count' => $resultCount]),
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
     * Excel の共通ヘッド部分の値を取得
     *
     * @param array $searchCondition 検索条件に関するデータの配列
     * @param int $resultCount 検索結果のレコード数
     *
     * @return array 検索パラメータデータを含む配列
     */
    private function getValues($searchCondition, $resultCount)
    {
        $typeLabel = '';
        switch ($searchCondition['type']) {
            case InspectionStatusEnum::UNINSPECTED->value:
                $typeLabel = InspectionStatusEnum::UNINSPECTED->label();
                break;
            case InspectionStatusEnum::INSPECTED->value:
                $typeLabel = InspectionStatusEnum::INSPECTED->label();
                break;
            default:
                $typeLabel = InspectionStatusEnum::ALL->label();
                break;
        }

        $values = [
            'items' => ['発行区分', '出荷予定日from', '出荷予定日to', '受注ID', '出力合計'],
            'data' => [
                $typeLabel,
                $searchCondition['deli_plan_date_from'],
                $searchCondition['deli_plan_date_to'],
                ($searchCondition['order_id_from'] || $searchCondition['order_id_to'])
                    ? ($searchCondition['order_id_from'] ?? '') . '～' . ($searchCondition['order_id_to'] ?? '')
                    : '全て',
                $resultCount
            ],
        ];
        return $values;
    }

    /**
     * Excel テーブルの連続値を取得
     *
     * @param array $dataList Excel テーブルに追加するデータの配列
     *
     * @return array (Excel テーブルデータ)
     *               - items: テーブルのヘッダー項目の配列
     *               - data: 各行のデータを含む配列
     */
    private function getContinuousValues($dataList)
    {
        $data = [];
        foreach ($dataList as $item) {
            if (!empty($item['delivery_hdr'])) {
                foreach ($item['delivery_hdr'] as $delivery) {
                    $data[] = [
                        Carbon::parse($item['deli_plan_date'])->format('Y/m/d') ?? '',
                        $item['t_order_hdr_id'] ?? '',
                        $item['order_destination_seq'] ?? '',
                        !empty($item['shipping_labels']) && isset($item['shipping_labels'][0]['shipping_label_numbers'])
                            ? $item['shipping_labels'][0]['shipping_label_numbers']
                            : '',
                        !empty($delivery['deli_inspection_date'])
                            ? Carbon::parse($delivery['deli_inspection_date'])->format('Y/m/d')
                            : '',
                        $item['destination_id'] ?? '',
                        $item['destination_name'] ?? ''
                    ];
                }
            } else {
                $data[] = [
                    Carbon::parse($item['deli_plan_date'])->format('Y/m/d') ?? '',
                    $item['t_order_hdr_id'] ?? '',
                    $item['order_destination_seq'] ?? '',
                    !empty($item['shipping_labels']) && isset($item['shipping_labels'][0]['shipping_label_numbers'])
                        ? $item['shipping_labels'][0]['shipping_label_numbers']
                        : '',
                    '',
                    $item['destination_id'] ?? '',
                    $item['destination_name'] ?? ''
                ];
            }
        }

        $continuousValues = [
            'items' => ['出荷予定日', '受注No', '受注枝番', '伝票番号', '検品日', '届先コード', '届先名'],
            'data' => $data,
        ];

        return $continuousValues;
    }

    /**
     * 検索パラメータに関連するデータを取得
     *
     *
     * @param array $param 検索条件を含むパラメータの配列
     *                     - deli_plan_date_from: 受注配送先の出荷予定日の開始日
     *                     - deli_plan_date_to: 受注配送先の出荷予定日の終了日
     *                     - order_id_from: 受注IDの開始値
     *                     - order_id_to: 受注IDの終了値
     *                     - type: 検品ステータス（未検品または検品済み）
     *
     * @return array 検索結果のデータを含む配列
     *               - 検索条件に一致する受注先のデータ
     */
    private function getData($param)
    {
        $deliPlanDateFrom = $param['deli_plan_date_from'] ?? null;
        $deliPlanDateTo = $param['deli_plan_date_to'] ?? null;
        $orderIdFrom = $param['order_id_from'] ?? null;
        $orderIdTo = $param['order_id_to'] ?? null;
        $type = $param['type'] ?? null;

        $query = OrderDestinationModel::select(
            't_order_destination_id',
            'deli_plan_date',
            't_order_hdr_id',
            'order_destination_seq',
            'destination_id',
            'destination_name'
        )
            ->with([
                'deliHdr' => function ($query) use ($type) {
                    $query->select('t_deli_hdr_id', 'deli_inspection_date', 't_order_destination_id')
                        ->when($type == InspectionStatusEnum::UNINSPECTED->value, fn ($q) => $q->whereNull('deli_inspection_date'))
                        ->when($type == InspectionStatusEnum::INSPECTED->value, fn ($q) => $q->whereNotNull('deli_inspection_date'));
                },
                'shippingLabels' => function ($query) {
                    $query->selectRaw('t_order_destination_id, GROUP_CONCAT(shipping_label_number SEPARATOR "/") as shipping_label_numbers')
                        ->groupBy('t_order_destination_id');
                }
            ])
            ->when($deliPlanDateFrom, fn ($query) => $query->where('deli_plan_date', '>=', $deliPlanDateFrom))
            ->when($deliPlanDateTo, fn ($query) => $query->where('deli_plan_date', '<=', $deliPlanDateTo))
            ->when($orderIdFrom, fn ($query) => $query->where('t_order_hdr_id', '>=', $orderIdFrom))
            ->when($orderIdTo, fn ($query) => $query->where('t_order_hdr_id', '<=', $orderIdTo))
            ->when(
                $type == InspectionStatusEnum::UNINSPECTED->value,
                function ($query) {
                    $query->where(function ($query) {
                        $query->whereDoesntHave('deliHdr') // No deliHdr records
                            ->orWhereHas('deliHdr', function ($q) {
                                $q->whereNull('deli_inspection_date');
                            });
                    });
                }
            )
            ->when(
                $type == InspectionStatusEnum::INSPECTED->value,
                fn ($query) =>
                $query->whereHas('deliHdr', fn ($q) => $q->whereNotNull('deli_inspection_date'))
            )
            ->orderBy('deli_plan_date', 'asc')
            ->get();


        try {
            $result = $query->toArray();
        } catch (\Throwable $th) {
            $result = [];
            Log::error('Error occurred: ' . $th->getMessage());
        }

        return $result;
    }
}
