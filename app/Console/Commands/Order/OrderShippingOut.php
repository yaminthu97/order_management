<?php

namespace App\Console\Commands\Order;

use App\Enums\BatchExecuteStatusEnum;
use App\Enums\ProgressTypeEnum;
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
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class OrderShippingOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:OrderShippingOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '受注検索画面で検索した条件で受注明細一覧表を作成し、バッチ実行確認へと出力する';

    // バッチ名
    protected $batchName = '商品別受注数・出荷数';

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    // for check batch parameter
    protected $checkBatchParameter;

    // for template file name
    protected $getTemplateFileName;

    // for template file path
    protected $getTemplateFilePath;

    // for excel export file path
    protected $getExcelExportFilePath;

    //throw error code constants
    private const PRIVATE_THROW_ERR_CODE = -1;

    //キャンセル
    private const CANCELLED = ProgressTypeEnum::Cancelled->value;

    //返品
    private const RETURNED = ProgressTypeEnum::Returned->value;

    /**
     * Create a new command instance.
     */
    public function __construct(
        StartBatchExecuteInstructionInterface $startBatchExecute,
        EndBatchExecuteInstructionInterface $endBatchExecute,
        CheckBatchParameter $checkBatchParameter,
        GetTemplateFileName $getTemplateFileName,
        GetTemplateFilePath $getTemplateFilePath,
        GetExcelExportFilePath $getExcelExportFilePath,
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->checkBatchParameter = $checkBatchParameter;
        $this->getTemplateFileName = $getTemplateFileName;
        $this->getTemplateFilePath = $getTemplateFilePath;
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
            //バッチ実行ID
            $batchExecutionId = $this->argument('t_execute_batch_instruction_id');

            /**
             * [共通処理] 開始処理
             * バッチ実行指示テーブル（t_execute_batch_instruction ）から該当バッチの取得と開始処理
             * t_execute_batch_instruction_id より該当バッチを取得し以下の情報を書き込む
             * - バッチ開始時刻
             */
            $batchExecute = $this->startBatchExecute->execute($batchExecutionId);

            //バッチタイプ
            $batchType = $batchExecute->execute_batch_type;

            //アカウントコード
            $accountCode = $batchExecute->account_cd;

            //アカウントID
            $accountId = $batchExecute->m_account_id;

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

            $param = $this->argument('json');

            // to required parameter
            $paramKey = ['date'];

            // to check batch json parameter
            $checkResult = $this->checkBatchParameter->execute($param, $paramKey);

            if (!$checkResult) {
                //エラーメッセージはパラメータが不正です。
                throw new Exception(__('messages.error.invalid_parameter'), self::PRIVATE_THROW_ERR_CODE);
            }

            $searchData = (json_decode($param, true))['search_info'];

            //パラメータから受注一覧を取得する。
            $orderList = $searchData['order_list'] ?? null;

            //パラメータから対象年月を取得する。
            $date = $searchData['date'];

            //dateがnullまたは空の文字列の場合
            if ($date == null || $date == "") {
                //エラーメッセージは出力処理日は必須です。
                throw new Exception(__('messages.error.missing_process_date2', ['datatype' => '出力']), self::PRIVATE_THROW_ERR_CODE);
            }

            //dateがYmフォーマットでない場合
            if (!Carbon::hasFormat($date, 'Ym')) {
                //エラーメッセージは:date日付フォーマットが無効です。
                throw new Exception(__('messages.error.invalid_date_format', ['date' => $date]), self::PRIVATE_THROW_ERR_CODE);
            }

            //パラメータ.受注一覧の指定がある場合
            if ($orderList) {
                $ordersData = $this->orderDataWithParameters($orderList);
            } else {
                $ordersData = $this->orderDataWithoutParameters($date);
            }

            // to check excel data have or not condition
            if (collect($ordersData)->isEmpty()) {
                // [出力対象のデータがありませんでした。] message save to 'execute_result'
                $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                    'execute_result' =>  __('messages.error.data_not_found2', ['datatype' => '出力']),
                    'execute_status' => BatchExecuteStatusEnum::SUCCESS->value
                ]);

                DB::commit();
                return;
            }

            $reportName = TemplateFileNameEnum::EXPXLSX_ORDER_SHIPPING->value;  // レポート名
            $templateFileName = $this->getTemplateFileName->execute($reportName, $accountId);  // template file name from database
            $templateFilePath = $this->getTemplateFilePath->execute($accountCode, $templateFileName);   // to get template file path

            // テンプレートファイルパスが存在するかどうかをチェック
            if (empty($templateFilePath)) {
                // [テンプレートファイルが見つかりません。] メッセージを'execute_result'にセットする
                throw new Exception(__('messages.error.file_not_found2', ['file' => 'テンプレート']), self::PRIVATE_THROW_ERR_CODE);
            }

            $continuousValues = $this->orderDataPrepare($ordersData, $date, $templateFilePath);

            // Excelにデータを書き込む
            $erm = new ExcelReportManager($templateFilePath);
            $erm->setValues(null, $continuousValues);

            // to get base file path
            $savePath = $this->getExcelExportFilePath->execute($accountCode, $batchType, $batchExecutionId);

            $result = $erm->save($savePath);

            // check to upload permission allow or not allow
            if (!$result) {
                // [AWS S3へのファイルのアップロードに失敗しました。] message save to 'execute_result'
                throw new Exception(__('messages.error.upload_s3_failed'), self::PRIVATE_THROW_ERR_CODE);
            }

            // data row counts
            $totalRowCnt = count($ordersData);

            /**
             * [共通処理] 終了処理
             * バッチ実行指示テーブルの該当バッチに以下の情報を書き込む
             * - バッチ終了時刻
             * - (実行状態)
             * - (実行結果)
             * - (格納ファイルパス)
             */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => __('messages.info.process_completed'), // 処理が完了しました。
                'execute_status' => BatchExecuteStatusEnum::SUCCESS->value,
                'file_path' => $savePath,
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            // write the log error in laravel.log for error message
            Log::error($e->getMessage());
            /**
             * [共通処理] 終了処理
             * バッチ実行指示テーブルの該当バッチに以下の情報を書き込む
             * - バッチ終了時刻
             * - (実行状態)
             * - (実行結果)
             * - (エラーファイルパス)
             */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => ($e->getCode() == self::PRIVATE_THROW_ERR_CODE) ? $e->getMessage() : BatchExecuteStatusEnum::FAILURE->label(),
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value,
            ]);
        }
    }

    /**
     * order query data
     *
     * @param array (order list form param)
     *
     * @return array
     */
    private function orderDataWithParameters($orderList)
    {
        $orderIds = array_column($orderList, 't_order_hdr_id');

        $ordersData = OrderDestinationModel::select('t_order_destination_id', 't_order_hdr_id', 'deli_plan_date')
            ->whereHas('orderHdr', function ($query) use ($orderIds) {
                $query->whereIn('t_order_hdr_id', $orderIds) //受注基本.受注ID IN パラメータ.受注一覧
                    ->whereNotIn('progress_type', [self::CANCELLED, self::RETURNED]) //受注基本.進捗区分 が[90:キャンセル]、[100:返品]を除く
                    ->where('estimate_flg', 0); //受注基本.見積フラグ＝0
            })
            ->with([
                'orderHdr:t_order_hdr_id,progress_type',
                'orderDtl:t_order_dtl_id,t_order_destination_id,sell_cd,order_sell_vol',
                'deliHdrOne:t_deli_hdr_id,t_order_destination_id,deli_plan_date'
            ])
            ->get();

        return $ordersData;
    }

    /**
     * order query data
     *
     * @param string  (dare from parameter)
     *
     * @return array
     */
    private function orderDataWithoutParameters($date)
    {
        $date = Carbon::createFromFormat('Ym', $date);

        // Extract the year and month
        $year = $date->year;
        $month = $date->month;

        $ordersData = OrderDestinationModel::select('t_order_destination_id', 't_order_hdr_id', 'deli_plan_date')
            ->where(function ($query) use ($year, $month) {
                $query->where(function ($subQuery) use ($year, $month) {
                    // Condition for deli_plan_date if it exists
                    $subQuery->whereYear('deli_plan_date', $year)
                        ->whereMonth('deli_plan_date', $month);
                })
                    ->orWhereHas('deliHdrOne', function ($subQuery) use ($year, $month) {
                        // Condition for deliHdrOne.deli_decision_date
                        $subQuery->whereYear('deli_decision_date', $year)
                            ->whereMonth('deli_decision_date', $month);
                    });
            })
            ->whereHas('orderHdr', function ($query) {
                $query->whereNotIn('progress_type', [self::CANCELLED, self::RETURNED]) //受注基本.進捗区分 が[90:キャンセル]、[100:返品]を除く
                    ->where('estimate_flg', 0); //受注基本.見積フラグ＝0
            })
            ->with([
                'orderHdr:t_order_hdr_id,progress_type',
                'orderDtl:t_order_dtl_id,t_order_destination_id,sell_cd,order_sell_vol',
                'deliHdrOne:t_deli_hdr_id,t_order_destination_id,deli_decision_date'
            ])->get();

        return $ordersData;
    }

    /**
     * Order Data Prepare for excel
     *
     * @param array (order data)
     * @param string (date from parameter)
     * @param string (template file path)
     *
     * @return array
     */
    private function orderDataPrepare($ordersData, $date, $templateFilePath)
    {
        $date = Carbon::createFromFormat('Ym', $date);

        $daysArray = $this->allDayInMonth($date);

        $excelItemsColumn =  $this->excelColumnsItems($templateFilePath);

        $updatedData = [];

        foreach ($daysArray as $index => $dayData) {

            // Filter orders where deli_plan_date or deli_decision_date are same
            $filteredOrdersArray = array_filter(
                $ordersData->toArray(),
                fn ($orderData) =>
                // 出荷基本.出荷確定日が設定されていない受注配送先の出荷予定日が該当の日付と一致する。
                Carbon::parse($orderData['deli_hdr_one']['deli_decision_date'] ?? $orderData['deli_plan_date'])
                    ->format('Y/m/d') === $dayData['date']
            );

            // orders array  restructure
            $ordersArrayrRestructured = array_map(fn ($item) => [
                array_map(
                    fn ($orderDtl) => array_merge(
                        $orderDtl,
                        ['order_destination' => ["deli_plan_date" => $item['deli_plan_date']]], // add order destination into each order_dtl
                        ['deli_hdr' => $item['deli_hdr_one']] // add deli hdr into each order_dtl
                    ),
                    $item['order_dtl']
                )
            ], $filteredOrdersArray);

            // Initialize order_data
            $dayData['order_data'] = $dayData['order_data'] ?? [];

            // Merge order order_data into one lvl arry
            $dayData['order_data'] = array_merge(...array_map('array_merge', array_merge($dayData['order_data'], ...$ordersArrayrRestructured)));

            $groupedData = collect($dayData['order_data'])->groupBy('sell_cd')->mapWithKeys(function ($group, $sellCd) {

                //それ以外の場合は受注配送先の出荷予定日 (deli_plan_date) を「受注」として集計
                $orderVol = count(array_filter($group->toArray(), function ($order) {
                    return empty($order['deli_hdr']['deli_decision_date'])
                        && !empty($order['order_destination']['deli_plan_date']);
                }));

                //出荷基本の出荷確定日 (deli_decion_date) がある場合は「出荷」として集計
                $deliVol = count(array_filter($group->toArray(), function ($order) {
                    return !empty($order['deli_hdr']['deli_decision_date']);
                }));

                return [
                    "受注数_{$sellCd}" => $orderVol == 0 ? '' : $orderVol, //受注数_{商品コード}
                    "出荷数_{$sellCd}" => $deliVol == 0 ? '' : $deliVol, //出荷数_{商品コード}
                ];
            })->toArray();

            $dayData = array_merge($dayData, $groupedData);
            unset($dayData['order_data']); // Remove the 'order_data' array
            $daysArray[$index] = $dayData;

            foreach ($excelItemsColumn as $col) {
                // Skip the excelItemsColumn columns
                if ($col == '日付') {
                    $phpToExcelDate = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($daysArray[$index]['date']);
                    $updatedData['日付'] = $phpToExcelDate;
                    $updatedData['日付の曜日'] = $phpToExcelDate;
                } else {
                    $updatedData[$col] = $daysArray[$index][$col] ?? '';
                }
            }
            $daysArray[$index] = $updatedData;
        }

        return [
            'items' => $excelItemsColumn,
            'data' => array_map('array_values', $daysArray), //remove keys
        ];
    }

    /**
     * fetch all day in month
     *
     * @param date
     *
     * @return array
     */
    private function allDayInMonth($date)
    {
        $startOfMonth = Carbon::parse($date->firstOfMonth());
        $endOfMonth = Carbon::parse($date->endOfMonth());
        $daysArray = [];

        // Create a DatePeriod with daily intervals
        $period = CarbonPeriod::create($startOfMonth, $endOfMonth);
        foreach ($period as $day) {
            $daysArray[] = [
                'date' => $day->format('Y/m/d'), //日付
            ];
        }

        return $daysArray;
    }

    /**
     * get all columns items from excel
     *
     * @param string (Template File Path)
     *
     * @return array
     */
    private function excelColumnsItems($templateFilePath)
    {
        $fileContents = Storage::disk(config('filesystems.default', 'local'))->get($templateFilePath);

        // Create a temporary file and write the contents of the MinIO file
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');
        file_put_contents($tempFile, $fileContents);

        // Load the spreadsheet
        $spreadsheet = IOFactory::load($tempFile);

        // Get the first sheet
        $sheet = $spreadsheet->getActiveSheet();

        // Convert sheet data to array and fetch  items column
        $data  = $sheet->toArray()[4];

        // remove ${{ }}
        foreach ($data  as &$value) {
            $value = preg_replace('/\$\{\{(.+?)\}\}/', '$1', $value);
            $value = trim($value, "&");
        }

        return $data;
    }
}
