<?php

namespace App\Console\Commands\Payment;

use App\Enums\AvailableFlg;
use App\Enums\BatchExecuteStatusEnum;
use App\Enums\OperatorCommentStatusEnum;
use App\Enums\OutputStatusEnum;
use App\Enums\ProgressTypeEnum;
use App\Enums\ReceiptTypeEnum;
use App\Models\Claim\Gfh1207\ReceiptHdrModel;
use App\Models\Master\Base\PaymentTypeModel;
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
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReceiptListOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ReceiptListOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '領収書一覧を出力する。';

    // バッチID
    protected $batchID;

    // バッチName
    protected $batchName = '領収一覧表出力';

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    // for report name
    protected $reportName = TemplateFileNameEnum::EXPXLSX_RECEIPT_LIST->value;

    // テンプレートファイルパス
    protected $templateFilePath;

    // テンプレートファイル名
    protected $templateFileName;

    // for check batch parameter
    protected $checkBatchParameter;

    // for template file name
    protected $getTemplateFileName;

    // for template file path
    protected $getTemplateFilePath;

    // for excel export file path
    protected $getExcelExportFilePath;

    private const PRIVATE_THROW_ERR_CODE = -1;  // for error code

    /**
     * Create a new command instance.
     */
    public function __construct(
        StartBatchExecuteInstructionInterface $startBatchExecute,
        EndBatchExecuteInstructionInterface $endBatchExecute,
        GetExcelExportFilePath $getExcelExportFilePath,
        GetTemplateFileName $getTemplateFileName,
        GetTemplateFilePath $getTemplateFilePath,
        CheckBatchParameter $checkBatchParameter,
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->getExcelExportFilePath = $getExcelExportFilePath;
        $this->getTemplateFileName = $getTemplateFileName;
        $this->getTemplateFilePath = $getTemplateFilePath;
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
            $batchExecutionId = $this->argument('t_execute_batch_instruction_id');  // for batch execute id
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
                TenantDatabaseManager::setTenantConnection($accountCode.'_db_testing');
            } else {
                TenantDatabaseManager::setTenantConnection($accountCode.'_db');
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
                'order_ids[]',
                'progress_type',
                'm_payment_types_id',
                'billing_name',
                'payment_date_from',
                'payment_date_to',
                't_order_hdr_id',
                'order_date_from',
                'order_date_to',
                'receipt_type',
                'receipt_output',
                'operator_comment'
            ];

            // バッチJSONパラメータをチェックする
            $checkResult = $this->checkBatchParameter->execute($this->argument('json'), $paramKey);

            if (!$checkResult) {
                // [パラメータが不正です。] メッセージを'execute_result'にセットする
                throw new Exception(__('messages.error.invalid_parameter'), self::PRIVATE_THROW_ERR_CODE);
            }

            $searchCondition = (json_decode($this->argument('json'), true))['search_info'];  // for all search parameters

            // get orderIds[] value
            $orderIds = Arr::get($searchCondition, 'order_ids[]', null);
            // check orderIds[] value
            if (empty($orderIds)) {
                // [パラメータが不正です。] メッセージを'execute_result'にセットする
                throw new Exception(__('messages.error.invalid_parameter'), self::PRIVATE_THROW_ERR_CODE);
            }

            $this->templateFileName = $this->getTemplateFileName->execute($this->reportName, $accountId);    // template file name from database

            $this->templateFilePath = $this->getTemplateFilePath->execute($accountCode, $this->templateFileName);   // to get template file path

            // テンプレートファイルパスが存在するかどうかをチェック
            if (empty($this->templateFilePath)) {
                // [テンプレートファイルが見つかりません。] message save to 'execute_result'
                throw new Exception(__('messages.error.file_not_found2', ['file' => 'テンプレート']), self::PRIVATE_THROW_ERR_CODE);
            }

            $dataList = $this->getData($searchCondition);   // to get for excel data from database

            // 抽出結果がない場合、[出力対象のデータがありませんでした。]メッセージを'execute_result'にセットする
            if (empty($dataList)) {
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

            // s3 file path create
            $savePath = $this->getExcelExportFilePath->execute($accountCode, $batchType, $batchExecutionId);  // to get base file path

            // Excelにデータを書き込む
            $erm = new ExcelReportManager($this->templateFilePath);
            $erm->setValues($values, $continuousValues);  // write data to excel
            $result = $erm->save($savePath);

            // check to upload permission allow or not
            if (!$result) {
                // [AWS S3へのファイルのアップロードに失敗しました。] message save to 'execute_result'
                throw new Exception(__('messages.error.upload_s3_failed'), self::PRIVATE_THROW_ERR_CODE);
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
                'file_path'      => $savePath,
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
    * @param  array $param  Search parameter
    * @return array ( search data )
    */
    private function getData($searchInfo)
    {
        // order_ids from searchInfo data
        $orderIds = Arr::get($searchInfo, 'order_ids[]', null);
        // get is_available enum value
        $availableValue = AvailableFlg::Available->value;

        // get ReceiptOutput data with searchInfo data
        $query = ReceiptHdrModel::query()
                    ->with([
                        'orderHdr:payment_date,t_order_hdr_id,order_datetime,m_cust_id,billing_name,m_payment_types_id,billing_address1,billing_address2,billing_address3,billing_address4,receipt_direction,receipt_proviso,order_total_price,sell_total_price,receipt_type',
                        'paymentType:m_payment_types_id,m_payment_types_name',
                        'orderHdr.orderMemo:t_order_hdr_id,operator_comment',
                        'receiptOuput:t_receipt_hdr_id,t_receipt_output_id',
                    ])
                    ->whereIn('t_order_hdr_id', $orderIds)
                    ->where('is_available', $availableValue)
                    ->select('t_receipt_hdr_id', 't_order_hdr_id', 'm_payment_types_id')
                    ->get();

        if (!empty($orderIds)) {
            $availableIds = $query->pluck('t_order_hdr_id')->toArray(); // Get IDs that are available
            $unavailableIds = array_diff($orderIds, $availableIds); // Get IDs that are not available

            // IDの配列と件数が一致しない
            if (!empty($unavailableIds)) {
                Log::info('出力できなかった受注があります。', $unavailableIds);  // 存在しない受注IDはログに出力
            }
        }

        try {
            $result = $query->toArray();
        } catch (Throwable $th) {
            $result = [];
            Log::error('Error occurred: ' . $th->getMessage());
        }

        return $result;
    }

    /**
     * Excel の共通ヘッド部分の値を取得
     *
     * @param array $searchCondition 検索条件に関するデータの配列
     * @param int $recordCount 検索結果のレコード数
     *
     * @return array 検索パラメータデータを含む配列
     */
    private function getValues($searchCondition)
    {
        // 進捗区分
        $progressTypeLabel = $this->getEnumLabel(ProgressTypeEnum::cases(), $searchCondition['progress_type']);

        // 支払方法
        $paymentTypesId = $searchCondition['m_payment_types_id'];
        $paymentTypesName = $paymentTypesId !== null
            ? PaymentTypeModel::query()
                ->where('m_payment_types_id', $paymentTypesId)
                ->select('m_payment_types_name')
                ->value('m_payment_types_name')
            : '';

        // 領収書発行要否
        $receiptTypeLabel = $this->getEnumLabel(ReceiptTypeEnum::cases(), $searchCondition['receipt_type']);
        // 領収書発行未済
        $receiptOutputLabel = $this->getEnumLabel(OutputStatusEnum::cases(), $searchCondition['receipt_output']);
        // 社内メモ
        $operatorCommentLabel = $this->getEnumLabel(OperatorCommentStatusEnum::cases(), $searchCondition['operator_comment']);

        $values = [
            'items' => ['進捗区分', '支払方法', '請求先顧客氏名', '顧客入金日from', '顧客入金日to', '受注番号', '受注日from', '受注日to', '領収書発行要否', '領収書発行未済', '社内メモ'],
            'data' => [
                $progressTypeLabel ?? '',          // 進捗区分
                $paymentTypesName ?? '',           // 支払方法
                $searchCondition['billing_name'] ?? '',         // 請求先顧客氏名
                $searchCondition['payment_date_from'] ?? '',    // 顧客入金日from
                $searchCondition['payment_date_to'] ?? '',      // 顧客入金日to
                $searchCondition['t_order_hdr_id'] ?? '',       // 受注番号
                $searchCondition['order_date_from'] ?? '',      // 受注日from
                $searchCondition['order_date_to'] ?? '',        // 受注日to
                $receiptTypeLabel ?? '',           // 領収書発行要否
                $receiptOutputLabel ?? '',         // 領収書発行未済
                $operatorCommentLabel ?? '',       // 社内メモ
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

            //請求先顧客住所
            $address = $item['order_hdr']['billing_address1'] . $item['order_hdr']['billing_address2'] . $item['order_hdr']['billing_address3'] . $item['order_hdr']['billing_address4'] ?? '';

            // get enum value
            $receiptType = $item['order_hdr']['receipt_type'];
            $receiptTypeLabel = $this->getEnumLabel(ReceiptTypeEnum::cases(), $receiptType);
            // get is_available enum value
            $splitTypeValue = ReceiptTypeEnum::Split->value;

            // change date format
            $paymentDate = $item['order_hdr']['payment_date']
                            ? Carbon::createFromFormat('Y-m-d', $item['order_hdr']['payment_date'])->format('Y/m/d')
                            : '';
            $orderDate = $item['order_hdr']['order_datetime']
                            ? Carbon::createFromFormat('Y-m-d H:i:s', $item['order_hdr']['order_datetime'])->format('Y/m/d')
                            : '';
            // 領収書発行未済
            $isOutputLabel = $this->getEnumLabel(OutputStatusEnum::cases(), $item['receipt_ouput'] ? "1" : "0");

            $data[] = [
                $paymentDate ?? '',                             // 顧客入金日
                $item['order_hdr']['t_order_hdr_id'] ?? '',     // 受注ID
                $orderDate ?? '',                               // 受注日
                $item['order_hdr']['m_cust_id'] ?? '',          // 請求先顧客ID
                $item['order_hdr']['billing_name'] ?? '',       // 請求先顧客名
                $item['payment_type']['m_payment_types_name'] ?? '',    // 支払方法
                $address ?? '',                                 // 請求先顧客住所
                $receiptType === $splitTypeValue ? '' : ($item['order_hdr']['receipt_direction'] ?? ''), //分割の場合、宛名は未設定
                $receiptType === $splitTypeValue ? '' : ($item['order_hdr']['receipt_proviso'] ?? ''),   //分割の場合、但し書きは未設定
                $item['order_hdr']['order_total_price'] ?? '',  // 請求金額
                $item['order_hdr']['sell_total_price'] ?? '',   // 入金額
                $receiptTypeLabel,                              // 領収書発行要否
                $isOutputLabel,                                 // 領収書発行未済
                $item['order_hdr']['order_memo']['operator_comment'] ?? '', // 社内メモ
            ];
        }

        $continuousValues = [
            'items' => ['顧客入金日', '受注ID', '受注日', '請求先顧客ID', '請求先顧客氏名', '支払方法', '請求先顧客住所', '宛名', '但し書き', '請求金額', '入金額', '領収書発行要否', '領収書発行未済', '社内メモ'],
            'data' => $data,
        ];

        return $continuousValues;
    }

    /**
     * Get Enum Label
     *
     * @param array $data
     * @return string
     */
    private function getEnumLabel($enumCases, $key = null)
    {
        // initial declare to get enum label and value
        $enumLabel = [];

        // loop all case of enum
        foreach ($enumCases as $case) {
            $enumLabel[$case->value] = $case->label();
        }

        // enum value is contain, show enum label
        if ($key !== null) {
            return $enumLabel[$key] ?? "";
        }
        return;
    }

}
