<?php

namespace App\Console\Commands\Shipment;

use App\Enums\BatchExecuteStatusEnum;
use App\Enums\ProgressTypeEnum;
use App\Enums\ShipmentStatusEnum;
use App\Enums\ThreeTemperatureZoneTypeEnum;
use App\Models\Order\Gfh1207\OrderDestinationModel;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Common\Gfh1207\CheckBatchParameter;
use App\Modules\Master\Gfh1207\Enums\DeliveryTypeEnum;
use App\Modules\Master\Gfh1207\Enums\PaymentTypeEnum;
use App\Modules\Master\Gfh1207\Enums\SlipTypeEnum;
use App\Modules\Master\Gfh1207\Enums\TemplateFileNameEnum;
use App\Modules\Order\Gfh1207\Enums\ShippingInstructTypeEnum;
use App\Modules\Order\Gfh1207\GetExcelExportFilePath;
use App\Modules\Order\Gfh1207\GetTemplateFileName;
use App\Modules\Order\Gfh1207\GetTemplateFilePath;
use App\Services\ExcelReportManager;
use App\Services\TenantDatabaseManager;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class ShipmentReportsStatusPgOut extends Command
{
    /**
     *
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ShipmentReportsStatusPgOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json :  JSON化した引数}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '出荷系帳票出力画面で指定した条件にて出荷ステータスPGを作成し、バッチ実行確認へと出力する';

    // バッチ名
    protected $batchName = '出荷ステータスPG';

    // テンプレートファイルパス
    protected $templateFilePath;

    // テンプレートファイル名
    protected $templateFileName;

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    // レポート名
    protected $reportName = TemplateFileNameEnum::EXPXLSX_SHIPMENT_REPORTS_STATUS_PG->value;

    // テンプレートファイル名
    protected $getTemplateFileName;

    // テンプレートファイルパス
    protected $getTemplateFilePath;

    // Excelエクスポートファイルパス
    protected $getExcelExportFilePath;

    // バッチパラメータのチェック
    protected $checkBatchParameter;

    // エラーコード
    private const PRIVATE_THROW_ERR_CODE = -1;
    private const EMPTY_DATA_ROW_CNT = 0;

    // 請求金額
    private const CHARGE_AMOUNT = 40000;

    // 熨斗枚数
    private const NOSHI_COUNT_MAX = 10;   // 10枚
    private const NOSHI_COUNT_SINGLE = 1; // 1枚
    private const NOSHI_COUNT_NONE = 0;   // 0枚

    // 大口小口種別
    private const BULK_WITH_NOSHI = '大口（のし有）';   // 大口（のし有）
    private const BULK = '大口';   // 大口
    private const SMALL_LOT_WITH_NOSHI = '小口（のし有）'; // 小口（のし有）
    private const SMALL_LOT = '小口'; // 小口
    private const SINGLE_ITEM_WITH_NOSHI = '一品一葉（のし有）'; // 一品一葉（のし有）
    private const SINGLE_ITEM = '一品一葉';  // 一品一葉

    // 出荷指示の値
    private const DELI_INSTRUCT_LIST = [1, 2];

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
            $batchExecutionId  = $this->argument('t_execute_batch_instruction_id');  // for batch execute id
            $batchExecute = $this->startBatchExecute->execute($batchExecutionId);

            $accountCode = $batchExecute->account_cd;     // アカウントCD
            $accountId = $batchExecute->m_account_id;   // m account id
            $batchType = $batchExecute->execute_batch_type;  // バッチタイプ

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
            // 必須パラメータ
            $paramKey = [
                'deli_plan_date_from',
                'deli_plan_date_to',
                'order_type',
                'payment_type',
                'm_delivery_id',
                'deli_instruct'
            ];

            $checkResult = $this->checkBatchParameter->execute($this->argument('json'), $paramKey);  // バッチJSONパラメータをチェックする

            if (!$checkResult) {
                // [パラメータが不正です。] メッセージを'execute_result'にセットする
                throw new \Exception(__('messages.error.invalid_parameter'), self::PRIVATE_THROW_ERR_CODE);
            }

            // すべての検索パラメータ
            $searchCondition = (json_decode($this->argument('json'), true))['search_info'];

            $deliPlanDateFrom = $searchCondition['deli_plan_date_from'];
            $deliPlanDateTo = $searchCondition['deli_plan_date_to'];
            $deliInstruct = $searchCondition['deli_instruct'];

            // パラメータチェック

            // 出荷予定日fromと出荷予定日to必須チェック
            if ($deliPlanDateFrom == null || $deliPlanDateTo == null) {
                $missingParam = $deliPlanDateFrom === null ? '出荷予定日from' : '出荷予定日to';
                throw new Exception(
                    __('messages.error.required_parameter', ['param' => $missingParam]),
                    self::PRIVATE_THROW_ERR_CODE
                );
            }

            // 「出荷予定日」が Y/m/d 形式でない場合のチェック
            $deliPlanDateFromObj = DateTime::createFromFormat('Y/m/d', $deliPlanDateFrom);
            $deliPlanDateToObj = DateTime::createFromFormat('Y/m/d', $deliPlanDateTo);

            // 日付フォーマットが無効の場合
            if (!$deliPlanDateFromObj || !$deliPlanDateToObj) {
                $invalidParam = !$deliPlanDateFromObj ? '出荷予定日from' : '出荷予定日to';
                throw new Exception(
                    __('messages.error.invalid_date_format', ['date' => $invalidParam]),
                    self::PRIVATE_THROW_ERR_CODE
                );
            }

            // 出荷予定日From > 出荷予定日Toのチェック
            if ($deliPlanDateFromObj > $deliPlanDateToObj) {
                throw new Exception(
                    __('messages.error.invalid_date_range', ['from' => '出荷予定日from', 'to' => '出荷予定日to']),
                    self::PRIVATE_THROW_ERR_CODE
                );
            }

            // 無効な '出荷指示' をチェックする
            if ($deliInstruct != null && !in_array($deliInstruct, self::DELI_INSTRUCT_LIST)) {
                // 'パラメータが不正です。'
                throw new Exception(__('messages.error.invalid_parameter'), self::PRIVATE_THROW_ERR_CODE);
            }


            $this->templateFileName = $this->getTemplateFileName->execute($this->reportName, $accountId);    // template file name from database

            $this->templateFilePath = $this->getTemplateFilePath->execute($accountCode, $this->templateFileName);   // to get template file path

            // テンプレートファイルパスが存在するかどうかをチェック
            if (empty($this->templateFilePath)) {
                // [テンプレートファイルが見つかりません。] メッセージを'execute_result'にセットする
                throw new Exception(__('messages.error.file_not_found2', ['file' => 'テンプレート']), self::PRIVATE_THROW_ERR_CODE);
            }

            $searchResult = $this->getData($searchCondition);  // データベースから出力データを取得

            $recordCount = count($searchResult);

            // 抽出結果がない場合、[出力対象のデータがありませんでした。]メッセージを'execute_result'にセットする
            if ($recordCount === self::EMPTY_DATA_ROW_CNT) {
                $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                    'execute_result' =>  __('messages.error.data_not_found2', ['datatype' => '出力']),
                    'execute_status' => BatchExecuteStatusEnum::SUCCESS->value
                ]);

                DB::commit();
                return;
            }

            $values = $this->getValues($searchCondition);

            $blockValues = $this->getBlockValues($searchResult);

            // Excelにデータを書き込む
            $erm = new ExcelReportManager($this->templateFilePath);
            $erm->setValues($values, [], $blockValues);

            // ベースファイルパスを取得する
            $savePath = $this->getExcelExportFilePath->execute($accountCode, $batchType, $batchExecutionId);
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
                'execute_result' => __('messages.info.notice_output_count', ['count' => $recordCount]),   // 〇〇件出力しました。
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
     *
     * @return array 検索パラメータデータを含む配列
     */
    private function getValues($searchCondition)
    {
        $values = [
            'items' => ['出荷予定日from', '出荷予定日to', '受注方法', '支払方法', '配送', '出荷指示'],
            'data' => [
                $searchCondition['deli_plan_date_from'],
                $searchCondition['deli_plan_date_to'],
                $searchCondition['order_type'] ?? "なし",
                $searchCondition['payment_type'] ?? "なし",
                $searchCondition['m_delivery_id'] ?? "なし",
                ($searchCondition['deli_instruct'] == ShippingInstructTypeEnum::INSTRUCTED->value
                ? ShippingInstructTypeEnum::INSTRUCTED->label()
                : ShippingInstructTypeEnum::NOT_INSTRUCTED->label())
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
     *               - listItems: テーブルのヘッダー項目の配列
     *               - data: 各行のデータを含む配列
     */
    private function getBlockValues($dataList)
    {
        // リストアイテムのキーを定義する
        $keys = [
            '出荷予定日',
            '伝票種別_確認待',
            '大口個口種別_確認待',
            '件数_確認待',
            '金額_確認待',
            '伝票種別_出荷待',
            '大口個口種別_出荷待',
            '件数_出荷待',
            '金額_出荷待',
            '伝票種別_出荷連携済',
            '大口個口種別_出荷連携済',
            '件数_出荷連携済',
            '金額_出荷連携済',
            '伝票種別_出荷指示済',
            '大口個口種別_出荷指示済',
            '件数_出荷指示済',
            '金額_出荷指示済',
            '伝票種別_検品済',
            '大口個口種別_検品済',
            '件数_検品済',
            '金額_検品済'
        ];

        // グループ化されたデータを準備する
        $groupedData = [];

        foreach ($dataList as $item) {
            $currentDate = $item['出荷予定日'] ?? '';

            // 現在の日付のグループを初期化します
            if (!isset($groupedData[$currentDate])) {
                $groupedData[$currentDate] = [];
            }

            // 現在のアイテムを適切な日付グループに追加
            $row = [];
            foreach ($keys as $key) {
                $row[] = $item[$key] ?? null;
            }
            $groupedData[$currentDate][] = $row;
        }

        // ブロックデータをフォーマット
        $data = [];
        foreach ($groupedData as $date => $listItems) {
            $data[] = [
                'listData' => $listItems
            ];
        }

        $blockData = [
            'singleItems' => [],
            'listItems' => $keys,
            'data' => $data,
        ];

        return $blockData;
    }

    /**
     * 検索パラメータに関連するデータを取得
     *
     *
     * @param array $param 検索条件を含むパラメータの配列
     *
     *
     * @return array 検索結果のデータを含む配列
     *               - 検索条件に一致する受注先のデータ
     */
    private function getData($param)
    {
        // 日付のフォーマットを確認と変換
        try {
            $deliPlanDateFrom = isset($param['deli_plan_date_from']) && !empty($param['deli_plan_date_from'])
                ? Carbon::createFromFormat('Y/m/d', $param['deli_plan_date_from'])->format('Y-m-d')
                : null;
            $deliPlanDateTo = isset($param['deli_plan_date_to']) && !empty($param['deli_plan_date_to'])
                ? Carbon::createFromFormat('Y/m/d', $param['deli_plan_date_to'])->format('Y-m-d')
                : null;
        } catch (Exception $e) {
            throw new InvalidArgumentException("Invalid date format: {$e->getMessage()}");
        }
        $orderType = $param['order_type'] ?? null;
        $paymentType = $param['payment_type'] ?? null;
        $deliveryId = $param['m_delivery_id'] ?? null;
        $deliInstruct = $param['deli_instruct'] ?? null;

        $query = OrderDestinationModel::select(
            't_order_destination_id',
            'deli_plan_date',
            't_order_hdr_id',
            'm_delivery_type_id',
            'gp1_type',
            'total_temperature_zone_type'
        )
            ->with([
                'orderHdr' => function ($query) {
                    $query->select('t_order_hdr_id', 'order_type', 'm_payment_types_id', 'progress_type', 'order_total_price');
                },
                'orderHdr.paymentTypes' => function ($query) {
                    $query->select('m_payment_types_id', 'm_payment_types_code');
                },
                'orderDtl' => function ($query) {
                    $query->select('t_order_dtl_id', 't_order_destination_id');
                },
                'orderDtl.orderDtlNoshi' => function ($query) {
                    $query->select('t_order_dtl_noshi_id', 't_order_dtl_id', 'count');
                },
                'deliHdr' => function ($query) {
                    $query->select('t_deli_hdr_id', 't_order_destination_id', 'gp2_type', 'cancel_operator_id');
                },
                'deliveryType' => function ($query) {
                    $query->select('m_delivery_types_id', 'm_delivery_type_code');
                }
            ])
            ->where(function ($query) {
                $query->whereDoesntHave('deliHdr') // 関連する deliHdr のないレコードを含める
                    ->orWhereHas('deliHdr', function ($query) {
                        $query->whereNull('cancel_operator_id'); // 関連する deliHdr と cancel_operator_id が null であるレコードを含める
                    });
            })
            ->withCount('orderDtl') // 受注明細count
            ->when($deliPlanDateFrom, fn ($query) => $query->where('deli_plan_date', '>=', $deliPlanDateFrom))
            ->when($deliPlanDateTo, fn ($query) => $query->where('deli_plan_date', '<=', $deliPlanDateTo))
            ->when($deliveryId, fn ($query) => $query->where('m_delivery_type_id', $deliveryId))
            ->when(
                $orderType || $paymentType,
                fn ($query) =>
                $query->whereHas(
                    'orderHdr',
                    fn ($q) =>
                    $q->when($orderType, fn ($q) => $q->where('order_type', $orderType))
                        ->when($paymentType, fn ($q) => $q->where('m_payment_types_id', $paymentType))
                )
            )
            ->when(
                $deliInstruct,
                fn ($query) =>
                // 出荷指示が1で、汎用区分2がNULLの場合
                $query->when(
                    $deliInstruct == ShippingInstructTypeEnum::INSTRUCTED->value,
                    fn ($query) =>
                    $query->whereHas(
                        'deliHdr',
                        fn ($q) => $q->whereNull('gp2_type')
                    )
                )
                    // 出荷指示が2で、汎用区分2がNOT NULLの場合
                    ->when(
                        $deliInstruct == ShippingInstructTypeEnum::NOT_INSTRUCTED->value,
                        fn ($query) =>
                        $query->whereHas(
                            'deliHdr',
                            fn ($q) => $q->whereNotNull('gp2_type')
                        )
                    )
            )
            ->groupBy('t_order_destination_id') // 受注配送先グルーピング
            ->get()
            ->filter(function ($item) { // ステータスPG出力対象外として除外する。
                return !($item->deliHdr && $item->deliHdr->contains(function ($deliHdrItem) {
                    return $deliHdrItem->gp2_type == ShipmentStatusEnum::SHIPPED->value; // gp2_type 5
                }) || in_array($item->orderHdr?->progress_type, [
                    ProgressTypeEnum::Shipped->value,
                    ProgressTypeEnum::PendingPostPayment->value,
                    ProgressTypeEnum::Completed->value,
                    ProgressTypeEnum::Cancelled->value,
                    ProgressTypeEnum::Returned->value
                ]));
            })
            ->filter(function ($item) {
                // 「出荷なし」の場合ステータスPG出力対象外として除外
                return $item->deliveryType->m_delivery_type_code != DeliveryTypeEnum::NO_SHIPMENT->value;
            })
            ->map(fn ($item) => $this->getStatus($item)) // ステータスフィールド追加
            ->map(fn ($item) => $this->getItemSize($item)) // 大口小口種別フィールド追加
            ->map(fn ($item) => $this->getDeliveryType($item)) // 伝票種別フィールド追加
            ->groupBy([
                fn ($item) => $item->status, // ステータス
                fn ($item) => $item->deli_plan_date, // 出荷予定日
                fn ($item) => $item->delivery_type, // 伝票種別
                fn ($item) => $item->item_size, // 大口小口種別
            ])
            ->flatMap(fn ($group) => $group->flatten(3)->unique()->map(fn ($item) => [
                't_order_destination_id' => $item->t_order_destination_id ?? null,
                'status' => $item->status ?? null,
                'deli_plan_date' => $item->deli_plan_date ?? null,
                'delivery_type' => $item->delivery_type ?? null,
                'item_size' => $item->item_size ?? null,
                'order_total_price' =>  $item->orderHdr->order_total_price ?? null,
            ]))
            ->toArray();

        // 商品購入金額と受注配送先の件数を取得
        $groupedResults = [];
        foreach ($query as $item) {
            $key = "{$item['status']}|{$item['deli_plan_date']}|{$item['delivery_type']}|{$item['item_size']}";
            if (!isset($groupedResults[$key])) {
                $groupedResults[$key] = [
                    'status' => $item['status'],
                    'deli_plan_date' => $item['deli_plan_date'],
                    'delivery_type' => $item['delivery_type'],
                    'item_size' => $item['item_size'],
                    'order_total_price' => 0,
                    'count' => 0,
                ];
            }
            $groupedResults[$key]['order_total_price'] += $item['order_total_price']; // 商品購入金額

            $groupedResults[$key]['count']++; // 受注配送先の件数
        }

        $groupedResults = collect($groupedResults)->sortBy('deli_plan_date')->values()->all();

        // 定義済みのステータス
        $statuses = ["確認待", "出荷待", "出荷連携済", "出荷指示済", "検品済"];

        // 出荷予定日とステータスでグループ化
        $formattedResult = [];
        foreach ($groupedResults as $entry) {
            $deliPlanDate = $entry['deli_plan_date'] ?? null;
            $status = $entry['status'] ?? null;

            // 無効なエントリをスキップ
            if (!$deliPlanDate || !$status) {
                continue;
            }

            // 出荷予定日グループを初期化する
            if (!isset($formattedResult[$deliPlanDate])) {
                $formattedResult[$deliPlanDate] = [];
            }

            // 出荷予定内のステータス グループを初期化します
            if (!isset($formattedResult[$deliPlanDate][$status])) {
                $formattedResult[$deliPlanDate][$status] = [];
            }

            // 特定のステータスグループにエントリデータを追加する
            $formattedResult[$deliPlanDate][$status][] = [
                "伝票種別" => $entry['delivery_type'] ?? null,
                "大口個口種別" => $entry['item_size'] ?? null,
                "件数" => $entry['count'] ?? null,
                "金額" => $entry['order_total_price'] ?? null,
            ];
        }

        // 最終的なフォーマット済みデータを準備する
        $formattedData = [];
        foreach ($formattedResult as $deliPlanDate => $statusData) {
            // 特定の出荷予定日グループの最大行数を見つける
            $maxRows = max(array_map('count', $statusData));

            // 現在の出荷予定日の行を生成
            for ($i = 0; $i < $maxRows; $i++) {
                $entry = ["出荷予定日" => $deliPlanDate]; // 出荷予定日で行を初期化

                // すべてのステータス属性をnullに初期化します
                foreach ($statuses as $status) {
                    $entry["伝票種別_{$status}"] = null;
                    $entry["大口個口種別_{$status}"] = null;
                    $entry["件数_{$status}"] = null;
                    $entry["金額_{$status}"] = null;
                }

                // 既存のステータスの属性を入力
                foreach ($statusData as $status => $details) {
                    $statusRow = $details[$i] ?? null; //現在のステータスのi行目を取得
                    if ($statusRow) {
                        $entry["伝票種別_{$status}"] = $statusRow["伝票種別"] ?? null;
                        $entry["大口個口種別_{$status}"] = $statusRow["大口個口種別"] ?? null;
                        $entry["件数_{$status}"] = $statusRow["件数"] ?? null;
                        $entry["金額_{$status}"] = $statusRow["金額"] ?? null;
                    }
                }
                $formattedData[] = $entry;
            }
        }

        try {
            $result = $formattedData;
        } catch (\Throwable $th) {
            $result = [];
            Log::error('Error occurred: ' . $th->getMessage());
        }
        return $result;
    }

    /**
     * 条件に基づいてステータスを取得
     *
     * @param  object $item
     * @return object|null
     */
    private function getStatus($item)
    {
        // 確認待判定
        $pendingConfirmation = $item->deliHdr->isEmpty() || $item->deliHdr->contains(function ($deliHdrItem) use ($item) {
            return $deliHdrItem->gp2_type == null &&
                $item->orderHdr?->progress_type !== null && (
                    $item->orderHdr?->progress_type == ProgressTypeEnum::PendingConfirmation->value ||
                    $item->orderHdr?->progress_type == ProgressTypeEnum::PendingCredit->value ||
                    $item->orderHdr?->progress_type == ProgressTypeEnum::PendingPrepayment->value ||
                    $item->orderHdr?->progress_type == ProgressTypeEnum::PendingAllocation->value
                );
        });

        // 出荷待判定
        $pendingShipment = $item->deliHdr->isEmpty() || $item->deliHdr->contains(function ($deliHdrItem) use ($item) {
            return $deliHdrItem->gp2_type == null &&
                $item->orderHdr?->progress_type !== null && (
                    $item->orderHdr?->progress_type == ProgressTypeEnum::PendingShipment->value ||
                    $item->orderHdr?->progress_type == ProgressTypeEnum::Shipping->value
                );
        });

        // 出荷連携済判定
        $shipmentLinked = $item->deliHdr->contains(function ($deliHdrItem) use ($item) {
            return $deliHdrItem->gp2_type == ShipmentStatusEnum::LINKED->value &&
                $item->orderHdr?->progress_type !== null && (
                    $item->orderHdr?->progress_type == ProgressTypeEnum::PendingConfirmation->value ||
                    $item->orderHdr?->progress_type == ProgressTypeEnum::PendingCredit->value ||
                    $item->orderHdr?->progress_type == ProgressTypeEnum::PendingPrepayment->value ||
                    $item->orderHdr?->progress_type == ProgressTypeEnum::PendingAllocation->value ||
                    $item->orderHdr?->progress_type == ProgressTypeEnum::PendingShipment->value ||
                    $item->orderHdr?->progress_type == ProgressTypeEnum::Shipping->value
                );
        });

        // 出荷指示済判定
        $shipmentInstructed = $item->deliHdr->contains(function ($deliHdrItem) use ($item) {
            return ($deliHdrItem->gp2_type == ShipmentStatusEnum::INSTRUCTED->value ||
                $deliHdrItem->gp2_type == ShipmentStatusEnum::SLIP_ISSUED->value) &&
                $item->orderHdr?->progress_type !== null && (
                    $item->orderHdr?->progress_type == ProgressTypeEnum::PendingConfirmation->value ||
                    $item->orderHdr?->progress_type == ProgressTypeEnum::PendingCredit->value ||
                    $item->orderHdr?->progress_type == ProgressTypeEnum::PendingPrepayment->value ||
                    $item->orderHdr?->progress_type == ProgressTypeEnum::PendingAllocation->value ||
                    $item->orderHdr?->progress_type == ProgressTypeEnum::PendingShipment->value ||
                    $item->orderHdr?->progress_type == ProgressTypeEnum::Shipping->value
                );
        });

        // 検品済判定
        $shipmentInspected = $item->deliHdr->contains(function ($deliHdrItem) use ($item) {
            return $deliHdrItem->gp2_type == ShipmentStatusEnum::INSPECTED->value &&
                $item->orderHdr?->progress_type !== null && (
                    $item->orderHdr?->progress_type == ProgressTypeEnum::PendingConfirmation->value ||
                    $item->orderHdr?->progress_type == ProgressTypeEnum::PendingCredit->value ||
                    $item->orderHdr?->progress_type == ProgressTypeEnum::PendingPrepayment->value ||
                    $item->orderHdr?->progress_type == ProgressTypeEnum::PendingAllocation->value ||
                    $item->orderHdr?->progress_type == ProgressTypeEnum::PendingShipment->value ||
                    $item->orderHdr?->progress_type == ProgressTypeEnum::Shipping->value
                );
        });

        // 条件に基づいてステータスを割り当てる
        if ($pendingConfirmation) {
            $item->status = '確認待'; // 確認待ステータス
        } elseif ($pendingShipment) {
            $item->status = '出荷待'; // 出荷待ステータス
        } elseif ($shipmentLinked) {
            $item->status = '出荷連携済'; // 出荷連携済ステータス
        } elseif ($shipmentInstructed) {
            $item->status = '出荷指示済'; // 出荷指示済ステータス
        } elseif ($shipmentInspected) {
            $item->status = '検品済'; // 検品済ステータス
        }

        return $item;
    }

    /**
     * 条件に基づいて大口小口種別を取得
     *
     * @param  object $item
     * @return object|null
     */
    private function getItemSize($item)
    {
        $count = $item->orderDtl ? $item->orderDtl->sum(function ($detail) {
            return $detail->orderDtlNoshi->count ?? 0;
        }) : 0;
        $orderTotalPrice = $item->orderHdr->order_total_price;
        $gp1Type = $item->gp1_type ?? null;

        // 大口小口種別値を設定
        if ($gp1Type == null) {
            if ($orderTotalPrice >= self::CHARGE_AMOUNT && ($count >= self::NOSHI_COUNT_MAX || $count >= self::NOSHI_COUNT_SINGLE)) {
                $item->item_size = self::BULK_WITH_NOSHI;
            } elseif ($orderTotalPrice >= self::CHARGE_AMOUNT && $count >= self::NOSHI_COUNT_NONE) {
                $item->item_size = self::BULK;
            } elseif ($orderTotalPrice <= self::CHARGE_AMOUNT && $count >= self::NOSHI_COUNT_MAX) {
                $item->item_size = self::BULK_WITH_NOSHI;
            } elseif ($orderTotalPrice <= self::CHARGE_AMOUNT && $count >= self::NOSHI_COUNT_SINGLE) {
                $item->item_size = self::SMALL_LOT_WITH_NOSHI;
            } elseif ($orderTotalPrice <= self::CHARGE_AMOUNT && $count >= self::NOSHI_COUNT_NONE) {
                $item->item_size = self::SMALL_LOT;
            }
        } else {
            $item->item_size = $count >= self::NOSHI_COUNT_SINGLE ? self::SINGLE_ITEM_WITH_NOSHI : self::SINGLE_ITEM;
        }

        return $item;
    }

    /**
     * 条件に基づいて伝票種別を取得
     *
     * @param  object $item
     * @return object|null
     */
    private function getDeliveryType($item)
    {
        $deliveryTypeCode = $item->deliveryType->m_delivery_type_code ?? '';
        $paymentTypeCode = $item->orderHdr->paymentTypes->m_payment_types_code ?? '';
        $temperatureZone = $item->total_temperature_zone_type ?? '';

        // 伝票種別値を設定
        if ($deliveryTypeCode == DeliveryTypeEnum::YAMATO->value) {
            if ($paymentTypeCode == PaymentTypeEnum::CASH_ON_DELIVERY->value && $temperatureZone == ThreeTemperatureZoneTypeEnum::NORMAL->value) {
                $item->delivery_type = SlipTypeEnum::COLLECT->value;
            } elseif ($paymentTypeCode == PaymentTypeEnum::CASH_ON_DELIVERY->value && in_array($temperatureZone, [ThreeTemperatureZoneTypeEnum::COOL->value, ThreeTemperatureZoneTypeEnum::FROZEN->value])) {
                $item->delivery_type = SlipTypeEnum::COOL_COLLECT->value;
            } elseif ($paymentTypeCode != PaymentTypeEnum::CASH_ON_DELIVERY->value && $temperatureZone == ThreeTemperatureZoneTypeEnum::NORMAL->value) {
                $item->delivery_type = SlipTypeEnum::YAMATO_SHIPMENT_PAID->value;
            } elseif ($paymentTypeCode != PaymentTypeEnum::CASH_ON_DELIVERY->value && in_array($temperatureZone, [ThreeTemperatureZoneTypeEnum::COOL->value, ThreeTemperatureZoneTypeEnum::FROZEN->value])) {
                $item->delivery_type = SlipTypeEnum::COOL_SHIPMENT_PAID->value;
            }
        } elseif ($deliveryTypeCode == DeliveryTypeEnum::OWN_SHIPMENT->value && in_array($temperatureZone, [ThreeTemperatureZoneTypeEnum::NORMAL->value, ThreeTemperatureZoneTypeEnum::COOL->value, ThreeTemperatureZoneTypeEnum::FROZEN->value])) {
            $item->delivery_type = SlipTypeEnum::OWN_SHIPMENT->value;
        } elseif ($deliveryTypeCode == DeliveryTypeEnum::OTHER->value && in_array($temperatureZone, [ThreeTemperatureZoneTypeEnum::NORMAL->value, ThreeTemperatureZoneTypeEnum::COOL->value, ThreeTemperatureZoneTypeEnum::FROZEN->value])) {
            $item->delivery_type = SlipTypeEnum::OTHER->value;
        }
        return $item;
    }
}
