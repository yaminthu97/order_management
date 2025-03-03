<?php

namespace App\Console\Commands\Payment;

use App\Enums\AvailableFlg;
use App\Enums\BatchExecuteStatusEnum;
use App\Enums\BillingMemoStatusEnum;
use App\Enums\ItemNameType;
use App\Enums\OutputStatusEnum;
use App\Enums\PaymentTypeEnum;
use App\Enums\ProgressTypeEnum;
use App\Models\Claim\Gfh1207\BillingHdrModel;
use App\Models\Master\Base\ItemnameTypeModel;
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
use Throwable;

class BillingListOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:BillingListOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '請求一覧を出力する。';

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    // for report name
    protected $reportName = TemplateFileNameEnum::EXPXLSX_BILLING_LIST->value;

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
                'progress_type', 'order_cust_runk_id', 'order_cust_id', 'order_cust_name_kanji', 'billing_cust_runk_id',
                'billing_cust_id', 'billing_cust_name_kanji', 'm_payment_types_id', 'order_type', 'order_hdr_id' ,
                'first_billing_date_from', 'first_billing_date_to', 'payment_due_date_from', 'payment_due_date_to',
                'remind_count_from', 'remind_count_to', 'payment_type', 'is_output', 'has_billing_memo'
            ];

            // バッチJSONパラメータをチェックする
            $checkResult = $this->checkBatchParameter->execute($this->argument('json'), $paramKey);   // to check batch json parameter

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

        // get BillingHdr data with searchInfo data
        $query = BillingHdrModel::query()
                ->with([
                    'orderHdr' => function ($query) {
                        $query->select(
                            'progress_type',
                            't_order_hdr_id',
                            'order_datetime',
                            'm_cust_id',
                            'order_name',
                            'm_cust_id_billing',
                            'billing_name',
                            'billing_tel1',
                            'billing_tel2',
                            'billing_fax',
                            'billing_address1',
                            'billing_address2',
                            'billing_address3',
                            'billing_address4',
                            'order_total_price',
                            'payment_price'
                        );
                    },
                    'paymentType'  => function ($query) {
                        $query->select('m_payment_types_id', 'm_payment_types_name');
                    },
                    'orderHdr.orderMemo' => function ($query) {
                        $query->select('t_order_hdr_id', 'billing_comment');
                    }
                ])
                ->whereIn('t_order_hdr_id', $orderIds)
                ->where('is_available', $availableValue)
                ->select('t_billing_hdr_id', 't_order_hdr_id', 'm_payment_types_id', 'remind_count', 'billing_amount')
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

        // 項目名称区分
        $customerRank = ItemNameType::CustomerRank->value;

        // 注文主顧客ランク
        $orderCustRankId = $searchCondition['order_cust_runk_id'];
        $orderCustRankName = $this->getItemName($customerRank, $orderCustRankId);

        // 請求先顧客ランク
        $billingCustRankId = $searchCondition['billing_cust_runk_id'];
        $billingCustRankName = $this->getItemName($customerRank, $billingCustRankId);

        // 支払方法
        $paymentTypesId = $searchCondition['m_payment_types_id'];
        $paymentTypesName = $paymentTypesId !== null
            ? PaymentTypeModel::query()
                ->where('m_payment_types_id', $paymentTypesId)
                ->select('m_payment_types_name')
                ->value('m_payment_types_name')
            : '';

        // 項目名称区分
        $receiptType = ItemNameType::ReceiptType->value;

        // 受注方法
        $orderType = $searchCondition['order_type'];
        $orderTypeName = $this->getItemName($receiptType, $orderType);

        // 入金区分
        $paymentTypeLabel = $this->getEnumLabel(PaymentTypeEnum::cases(), $searchCondition['payment_type']);
        // 請求書発行未済
        $isOutputLabel = $this->getEnumLabel(OutputStatusEnum::cases(), $searchCondition['is_output']);
        // 請求メモ有
        $billingMemoLabel = $this->getEnumLabel(BillingMemoStatusEnum::cases(), $searchCondition['has_billing_memo']);

        $values = [
            'items' => ['進捗区分', '注文主顧客ランク', '注文主顧客ID', '注文主顧客氏名', '請求先顧客ランク', '請求先顧客ID', '請求先顧客氏名', '支払方法', '受注方法', '受注番号', '初回請求日from', '初回請求日to', '支払期限日from', '支払期限日to', '督促回数from', '督促回数to', '入金区分', '請求書発行未済', '請求メモ有'],
            'data' => [
                $progressTypeLabel ?? '',           // 進捗区分
                $orderCustRankName ?? '',           // 注文主顧客ランク
                $searchCondition['order_cust_id'] ?? '',            // 注文主顧客ID
                $searchCondition['order_cust_name_kanji'] ?? '',    // 注文主顧客氏名
                $billingCustRankName ?? '',         // 請求先顧客ランク
                $searchCondition['billing_cust_id'] ?? '',          // 請求先顧客ID
                $searchCondition['billing_cust_name_kanji'] ?? '',  // 請求先顧客氏名
                $paymentTypesName ?? '',                            // 支払方法
                $orderTypeName ?? '',                               // 受注方法
                $searchCondition['order_hdr_id'] ?? '',             // 受注番号
                $searchCondition['first_billing_date_from'] ?? '',  // 初回請求日from
                $searchCondition['first_billing_date_to'] ?? '',    // 初回請求日to
                $searchCondition['payment_due_date_from'] ?? '',    // 支払期限日from
                $searchCondition['payment_due_date_to'] ?? '',      // 支払期限日to
                $searchCondition['remind_count_from'] ?? '',        // 督促回数from
                $searchCondition['remind_count_to'] ?? '',          // 督促回数to
                $paymentTypeLabel ?? '',                            // 入金区分
                $isOutputLabel ?? '',                               // 請求書発行未済
                $billingMemoLabel ?? '',                            // 請求メモ有
            ]
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

            // 請求残高計算
            $orderTotalPrice = $item['order_hdr']['order_total_price'];
            $paymentPrice = $item['order_hdr']['payment_price'];

            if (!is_null($orderTotalPrice) && !is_null($paymentPrice) && $orderTotalPrice > $paymentPrice) {
                $billingBalance = $orderTotalPrice - $paymentPrice;
            } else {
                $billingBalance = null; // if $paymentPrice is greater $orderTotalPrice
            }

            //get enum label
            $progressType = $this->getEnumLabel(ProgressTypeEnum::cases(), $item['order_hdr']['progress_type']);

            // change date format as (2024/01/01)
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $item['order_hdr']['order_datetime'])->format('Y/m/d');

            $data[] = [
                $item['payment_type']['m_payment_types_name'] ?? '',
                $progressType ?? '',
                $item['t_order_hdr_id'] ?? '',
                $date ?? '',
                $item['order_hdr']['m_cust_id'] ?? '',
                $item['order_hdr']['order_name'] ?? '',
                $item['order_hdr']['m_cust_id_billing'] ?? '',
                $item['order_hdr']['billing_name'] ?? '',
                $item['order_hdr']['billing_tel1'] ?? '',
                $item['order_hdr']['billing_tel2'] ?? '',
                $item['order_hdr']['billing_fax'] ?? '',
                $address ?? '',
                $item['remind_count'] ?? '',
                $item['billing_amount'] ?? '',
                $item['order_hdr']['payment_price'] ?? '',
                $billingBalance ?? '',
                $item['order_hdr']['order_memo']['billing_comment'] ?? ''
            ];
        }

        $continuousValues = [
            'items' => ['支払方法', '進捗区分', '受注ID', '受注日', '注文主顧客ID', '注文主顧客氏名', '請求先顧客ID', '請求先顧客氏名', '請求先顧客電話番号1', '請求先顧客電話番号2', '請求先顧客FAX番号', '請求先顧客住所', '督促回数', '請求金額', '入金金額', '請求残高', '請求メモ'],
            'data' => $data,
        ];

        return $continuousValues;
    }

    /**
     * Get 項目名称マスタ data
     *
     * @param array $data
     * @return string
     */
    public function getItemName($type, $id)
    {
        return $id !== null
            ? ItemnameTypeModel::query()
                ->where('m_itemname_type', $type)
                ->where('m_itemname_types_id', $id)
                ->select('m_itemname_type_name')
                ->value('m_itemname_type_name')
            : '';
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
