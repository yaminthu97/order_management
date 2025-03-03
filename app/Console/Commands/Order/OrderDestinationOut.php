<?php

namespace App\Console\Commands\Order;

use App\Enums\AddressCheckTypeEnum;
use App\Enums\AlertCustCheckTypeEnum;
use App\Enums\BatchExecuteStatusEnum;
use App\Enums\CommentCheckTypeEnum;
use App\Enums\CreditTypeEnum;
use App\Enums\DeliDecisionTypeEnum;
use App\Enums\DeliHopeDateCheckTypeEnum;
use App\Enums\DeliInstructTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Enums\ProgressTypeEnum;
use App\Enums\ReservationTypeEnum;
use App\Enums\SalesStatusTypeEnum;
use App\Enums\SettlementSalesTypeEnum;
use App\Models\Order\Gfh1207\OrderHdrModel;
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

class OrderDestinationOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:OrderDestinationOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '受注検索画面で検索した条件で受注送付先一覧表を作成し、バッチ実行確認へと出力する';

    // テンプレートファイルパス
    protected $templateFilePath;

    // テンプレートファイル名
    protected $templateFileName;

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    protected $batchName = '受注送付先一覧表';

    // for report name
    protected $reportName = TemplateFileNameEnum::EXPXLSX_ORDER_DESTINATION->value;

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

    // for cancellation reason
    private const CANCELLATION_REASON = 2;

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
            $requiredFields = [ 't_order_hdr_id'];
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
                throw new \Exception(__('messages.error.file_not_found2', ['file' => 'テンプレート']), self::PRIVATE_THROW_ERR_CODE);
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

            $continuousValues = $this->getContinuousValues($dataList);   // for excel table part

            // write data to excel
            $erm = new ExcelReportManager($this->templateFilePath);
            $erm->setValues(null, $continuousValues);

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
        // Join the tables
        $query = OrderHdrModel::with([
            'orderDestination',
            'orderDestination.updateOperator',
            'orderDestination.deliveryType',
            'orderDestination.deliHdr',
            'orderDestination.deliHdr.shippingLabels',
            'paymentTypes',
            'ecs',
            'orderType',
            'orderMemo',
            'cancelType'
        ])
        ->when(!empty($param['t_order_hdr_id']), function ($query) use ($param) {
            $query->whereIn('t_order_hdr.t_order_hdr_id', $param['t_order_hdr_id']);
        })
        ->whereHas('cancelType', function ($query) {
            $query->where('m_itemname_types.m_itemname_type_code', self::CANCELLATION_REASON);
        })
        ->orderBy('t_order_hdr.t_order_hdr_id')
        ->get();
        $finalData = $query
        ->map(function ($hdr) {
            $hdr->orderDestination = $hdr->orderDestination->sortBy(fn ($destination) => $destination->order_destination_seq); // Sort by order_destination_seq
            return $hdr;
        })
        ->values()
        ->toArray();

        $resultItems = [];
        foreach ($finalData as $hdr) {
            foreach ($hdr['orderDestination'] as $des) {
                foreach ($des['deliHdr'] as $deli) {
                    $resultItems[] = [
                        '受注ID' =>  $hdr['t_order_hdr_id'] ?? null,
                        '進捗区分名' => $hdr['progress_type'] ? ProgressTypeEnum::from($hdr['progress_type'])->label() : null ,
                        '受注日時' => $hdr['order_datetime'] ?? null,
                        'ECサイト名' => $hdr['ecs']['m_ec_name'] ?? null ,
                        'ECサイト注文ID' =>  $hdr['ec_order_num'] ?? null,
                        '受注方法名' => $hdr['order_type']['m_itemname_type_name'] ?? null,
                        '備考' =>  $hdr['order_comment'] ?? null,
                        '社内メモ' => $hdr['order_memo']['operator_comment'] ?? null,
                        'キャンセル理由名' => $hdr['cancel_type']['m_itemname_type_name'] ?? null,
                        'キャンセル備考' => $hdr['cancel_note'] ?? null,
                        'コメント確認区分' => $hdr['comment_check_type'] ? CommentCheckTypeEnum::from($hdr['comment_check_type'])->label() : null,
                        '要注意顧客区分' => $hdr['alert_cust_check_type'] ? AlertCustCheckTypeEnum::from($hdr['alert_cust_check_type'])->label() : null,
                        '住所確認区分' => $hdr['address_check_type'] ? AddressCheckTypeEnum::from($hdr['address_check_type'])->label() : null,
                        '配達指定日確認区分' => $hdr['deli_hope_date_check_type'] ? DeliHopeDateCheckTypeEnum::from($hdr['deli_hope_date_check_type'])->label() : null,
                        '与信区分' => $hdr['credit_type'] ? CreditTypeEnum::from($hdr['credit_type'])->label() : null,
                        '入金区分' => $hdr['payment_type'] ? PaymentTypeEnum::from($hdr['payment_type'])->label() : null,
                        '在庫引当区分' => $hdr['reservation_type'] ? ReservationTypeEnum::from($hdr['reservation_type'])->label() : null,
                        '出荷指示区分' => $hdr['deli_instruct_type'] ? DeliInstructTypeEnum::from($hdr['deli_instruct_type'])->label() : null,
                        '出荷確定区分' => $hdr['deli_decision_type'] ? DeliDecisionTypeEnum::from($hdr['deli_decision_type'])->label() : null,
                        '決済売上計上区分' => $hdr['settlement_sales_type'] ? SettlementSalesTypeEnum::from($hdr['settlement_sales_type'])->label() : null,
                        '売上ステータス反映区分' => $hdr['sales_status_type'] ? SalesStatusTypeEnum::from($hdr['sales_status_type'])->label() : null,
                        '支払い方法名' => $hdr['payment_types']['m_payment_types_name'] ?? null,
                        '入金日' => $hdr['payment_date'] ?? null,
                        '入金済額' => $hdr['payment_price'] ?? null,
                        '顧客ID' => $hdr['m_cust_id'] ?? null,
                        '注文者氏名' => $hdr['order_name'] ?? null,
                        '注文者カナ' => $hdr['order_name_kana'] ?? null,
                        '注文者法人名・団体名' => $hdr['order_corporate_name'] ?? null,
                        '注文者電話番号' => $hdr['order_tel1'] ?? null,
                        '注文者電話番号２' => $hdr['order_tel2'] ?? null,
                        '注文者メールアドレス' => $hdr['order_email1'] ?? null,
                        '注文者メールアドレス２' => $hdr['order_email2'] ?? null,
                        '注文者郵便番号' => $hdr['order_postal'] ?? null,
                        '注文者都道府県' => $hdr['order_address1'] ?? null,
                        '注文者市区町村' => $hdr['order_address2'] ?? null,
                        '注文者番地' => $hdr['order_address3'] ?? null,
                        '注文者建物名' => $hdr['order_address4'] ?? null,
                        '請求先顧客ID' => $hdr['m_cust_id_billing'] ?? null,
                        '請求先氏名' => $hdr['billing_name'] ?? null,
                        '請求先カナ' => $hdr['billing_name_kana'] ?? null,
                        '請求先法人名・団体名' => $hdr['billing_corporate_name'] ?? null,
                        '請求先電話番号' => $hdr['billing_tel1'] ?? null,
                        '請求先者電話番号２' => $hdr['billing_tel2'] ?? null,
                        '請求先メールアドレス' => $hdr['billing_email1'] ?? null,
                        '請求先メールアドレス２' => $hdr['billing_email2'] ?? null,
                        '請求先郵便番号' => $hdr['billing_postal'] ?? null,
                        '請求先都道府県' => $hdr['billing_address1'] ?? null,
                        '請求先市区町村' => $hdr['billing_address2'] ?? null,
                        '請求先番地' => $hdr['billing_address3'] ?? null,
                        '請求先建物名' => $hdr['billing_address4'] ?? null,
                        '送付先番号' => $des['order_destination_seq'] ?? null,
                        '送付先氏名' => $des['destination_name'] ?? null,
                        '送付先カナ' => $des['destination_name_kana'] ?? null,
                        '送付先法人名・団体名' => $des['destination_company_name'] ?? null,
                        '送付先郵便番号' => $des['destination_postal'] ?? null,
                        '送付先都道府県' => $des['destination_address1'] ?? null,
                        '送付先市区町村' => $des['destination_address2'] ?? null,
                        '送付先番地' => $des['destination_address3'] ?? null,
                        '送付先建物名' => $des['destination_address4'] ?? null,
                        '送付先電話番号' => $des['destination_tel'] ?? null,
                        '配送方法' => $des['deliveryType']['m_delivery_type_name'] ?? null,
                        '配送希望日' => $des['deli_hope_date'] ?? null,
                        '配送希望時間' => $des['deli_hope_time_name'] ?? null,
                        '配送日' => $deli['deli_decision_date'] ?? null,
                        '送り状番号' =>  $deli['shippingLabels']->pluck('shipping_label_number')->implode('/'),
                        '受注商品金額' => $hdr['sell_total_price'] ?? null,
                        '受注送料' => $hdr['shipping_fee'] ?? null,
                        '受注手数料' => $hdr['payment_fee'] ?? null,
                        '受注包装料' => $hdr['package_fee'] ?? null,
                        '受注消費税額' => $hdr['tax_price'] ?? null,
                        '受注合計金額' => ($hdr['standard_total_price'] ?? 0) + ($hdr['reduce_total_price'] ?? 0),
                        '割引金額' => $hdr['discount'] ?? null,
                        'ストアクーポン利用額' => $hdr['use_coupon_store'] ?? null,
                        'モールクーポン利用額' => $hdr['use_coupon_mall'] ?? null,
                        'クーポン利用額計' => $hdr['total_use_coupon'] ?? null,
                        '利用ポイント' => $hdr['use_point'] ?? null,
                        '請求金額' => $hdr['order_total_price'] ?? null,
                        '標準税率合計金額' => $hdr['standard_total_price'] ?? null,
                        '軽減税率合計金額' => $hdr['reduce_total_price'] ?? null,
                        '標準税率消費税' => $hdr['standard_tax_price'] ?? null,
                        '軽減税率消費税' => $hdr['reduce_tax_price'] ?? null,
                        '最終更新ユーザ名' => $des['updateOperator']['m_operator_name'] ?? null,
                        '最終更新タイムスタンプ' => isset($des['update_timestamp']) ? $des['update_timestamp']->toDateTimeString() : null,
                    ];
                }
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
    * Get continuous values for excel table
    *
    * @param  array $dataList  Excel table data from database
    * @return array ( excel table data )
    */
    private function getContinuousValues($dataList)
    {
        $keys = [
                        '受注ID','進捗区分名','受注日時','ECサイト名',	'ECサイト注文ID','受注方法名','備考','社内メモ','キャンセル理由名','キャンセル備考','コメント確認区分','要注意顧客区分','住所確認区分','配達指定日確認区分',
                        '与信区分','入金区分','在庫引当区分','出荷指示区分','出荷確定区分','決済売上計上区分','売上ステータス反映区分','支払い方法名','入金日','入金済額','顧客ID','注文者氏名','注文者カナ','注文者法人名・団体名',
                        '注文者電話番号','注文者電話番号２','注文者メールアドレス','注文者メールアドレス２','注文者郵便番号','注文者都道府県','注文者市区町村','注文者番地','注文者建物名','請求先顧客ID','請求先氏名','請求先カナ',
                        '請求先法人名・団体名','請求先電話番号','請求先者電話番号２','請求先メールアドレス','請求先メールアドレス２','請求先郵便番号','請求先都道府県','請求先市区町村','請求先番地','請求先建物名','送付先番号',
                        '送付先氏名','送付先カナ','送付先法人名・団体名','送付先郵便番号','送付先都道府県','送付先市区町村','送付先番地','送付先建物名','送付先電話番号','配送方法','配送希望日','配送希望時間','配送日','送り状番号',
                        '受注商品金額','受注送料','受注手数料','受注包装料','受注消費税額','受注合計金額','割引金額','ストアクーポン利用額','モールクーポン利用額','クーポン利用額計','利用ポイント','請求金額','標準税率合計金額',
                        '軽減税率合計金額','標準税率消費税','軽減税率消費税','最終更新ユーザ名','最終更新タイムスタンプ'
                     ];
        $data = [];
        foreach ($dataList as $item) {
            $row = [];
            foreach ($keys as $key) {
                $row[] = $item[$key] ?? null;
            }
            $data[] = $row;
        }

        $continuousValues = [
            'items' => $keys,
            'data' => $data,
        ];
        return $continuousValues;
    }

}
