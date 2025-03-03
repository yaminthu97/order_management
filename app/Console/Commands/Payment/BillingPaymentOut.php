<?php

namespace App\Console\Commands\Payment;

use App\Enums\BatchExecuteStatusEnum;
use App\Enums\DeliDecisionTypeEnum;
use App\Enums\DeliInstructTypeEnum;
use App\Enums\ItemNameType;
use App\Enums\ProgressTypeEnum;
use App\Mail\BillingPaymentOutMail;
use App\Models\Master\Base\ShopGfhModel;
use App\Models\Order\Base\DeliHdrModel;
use App\Models\Order\Gfh1207\OrderHdrModel;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Common\Gfh1207\CheckBatchParameter;
use App\Modules\Master\Gfh1207\Enums\BatchListEnum;
use App\Modules\Payment\Base\GetCsvZipExportFilePathInterface;
use App\Services\TenantDatabaseManager;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BillingPaymentOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:BillingPaymentOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '請求入金一覧をCSV形式で出力し、zipで格納する。';

    // バッチName
    protected $batchName = '請求入金一覧出力';

    // バッチType
    protected $batchType = BatchListEnum::EXPXLSX_BILLING_PAYMENT->value;

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    // for check batch parameter
    protected $checkBatchParameter;

    // for error code
    private const PRIVATE_THROW_ERR_CODE = -1;
    private $batchExecutionId;  // for batch Execution Id
    private $csvFileContent;    // for csv file content data
    private $currentDate;       // for current date
    private $lastThreeYearDate; // for last three year date
    private $orderDateFrom;     // for order date start
    private $orderDateTo;       // for order date end
    private const EMPTY_DATA_ROW_COUNT = 0;  // empty value
    private const DEFAULT_CURRENT_PAGE = 1; // for default current page

    //get file path to save on S3 server
    protected $getCsvZipExportFilePath;  // 手動起動

    /**
     * Create a new command instance.
     */
    public function __construct(
        StartBatchExecuteInstructionInterface $startBatchExecute,
        EndBatchExecuteInstructionInterface $endBatchExecute,
        GetCsvZipExportFilePathInterface $getCsvZipExportFilePath,
        CheckBatchParameter $checkBatchParameter,
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->getCsvZipExportFilePath = $getCsvZipExportFilePath;
        $this->checkBatchParameter = $checkBatchParameter;
        $this->currentDate = Carbon::now();
        $this->lastThreeYearDate = $this->currentDate->copy()->subYears(3);
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->batchExecutionId = $this->argument('t_execute_batch_instruction_id');

            // 定期実行チェック
            if ($this->isRegularFlg()) {
                $orderDateFrom = $this->lastThreeYearDate->format('Y/m/d');
                $orderDateTo = $this->currentDate->format('Y/m/d');
                $mAccountId = json_decode($this->argument('json'), true)['m_account_id'];  // for all search parameters

                // 定期実行の場合
                $options = [
                    'm_account_id' => $mAccountId, // 企業アカウントID
                    'execute_batch_type' => $this->batchType, // batch type
                    'execute_conditions' => [
                        'm_account_id'    => $mAccountId,
                        'order_date_from' => $orderDateFrom,  // 処理実行日から過去三年間の日付
                        'order_date_to'   => $orderDateTo     // 処理実行日
                    ]
                ];
                $batchExecute = $this->startBatchExecute->execute(null, $options);

                $executeCondition = json_decode($batchExecute->execute_conditions);
                $this->orderDateFrom = $executeCondition->order_date_from;
                $this->orderDateTo = $executeCondition->order_date_to;

            } else {
                // 手動起動の場合
                $batchExecute = $this->startBatchExecute->execute($this->batchExecutionId);
            }

            /**
             * [共通処理] 開始処理
             * バッチ実行指示テーブル（t_execute_batch_instruction ）から該当バッチの取得と開始処理
             * t_execute_batch_instruction_id より該当バッチを取得し以下の情報を書き込む
             * - バッチ開始時刻
             */
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

            // 手動起動の場合のみバッチJSONパラメータチェックを行う
            if (!$this->isRegularFlg()) {
                // required parameter list from search_info
                $paramKey = [
                    'deposit_account',
                    'shipping_instruction_category',
                    'shipping_confirmation_category',
                    'progress_classification',
                    'payment_classification',
                    'payment_method',
                    'order_method',
                    'ec_site',
                    'payment_registration_date_from',
                    'payment_registration_date_to',
                    'customer_payment_date_from',
                    'customer_payment_date_to',
                    'account_deposit_date_from',
                    'account_deposit_date_to',
                    'estimated_shipping_date_from',
                    'scheduled_ship_date_to',
                    'shipment_confirmation_date_from',
                    'shipment_confirmation_date_to',
                    'internal_memo',
                    'm_cust_id_billing'
                ];

                $checkResult = $this->checkBatchParameter->execute($this->argument('json'), $paramKey);  // バッチJSONパラメータをチェックする

                if (!$checkResult) {
                    // [パラメータが不正です。] メッセージを'execute_result'にセットする
                    throw new Exception(__('messages.error.invalid_parameter'), self::PRIVATE_THROW_ERR_CODE);
                }
            }

            // 定期実行チェック
            if ($this->isRegularFlg()) {
                $searchResultCount = $this->getRegularData();
            } else {
                $searchCondition = (json_decode($this->argument('json'), true))['search_info'];  // for all search parameters
                $searchResultCount = $this->getData($searchCondition);
            }

            // data is empty check
            if ($searchResultCount === self::EMPTY_DATA_ROW_COUNT) {
                // [出力対象のデータがありませんでした。] message save to 'execute_result'
                $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                    'execute_result' => __('messages.error.data_not_found2', ['datatype' => '出力']),
                    'execute_status' => BatchExecuteStatusEnum::SUCCESS->value
                ]);
                DB::commit();
                return;
            }

            // create zip file from csv file
            $zipFileContent = $this->createZipFile($this->csvFileContent);

            // 定期実行チェック
            if ($this->isRegularFlg()) {
                // 定期実行の場合、s3 file path create
                $savePath =  $this->getRegularZipFilePath($accountCode, $this->currentDate->format('Ymd_His'));
            } else {
                // 手動起動の場合、s3 file path create
                $savePath = $this->getCsvZipExportFilePath->execute($accountCode, $batchType, $this->batchExecutionId);
            }

            //save file on s3
            $fileuploaded = Storage::disk(config('filesystems.default', 'local'))->put($savePath, $zipFileContent);

            //ファイルがAWS S3にアップロードされていない場合
            if (!$fileuploaded) {
                //AWS S3へのファイルのアップロードに失敗しました。
                throw new Exception(__('messages.error.upload_s3_failed'), self::PRIVATE_THROW_ERR_CODE);
            }

            // [ 〇〇件出力しました。] message save to 'execute_result'
            $successMessage = __('messages.info.notice_output_count', ['count' => $searchResultCount]);

            // 定期実行チェック
            if ($this->isRegularFlg()) {
                // get mail address
                $mailData = $this->getFromToMailAddress();

                if ($mailData) {
                    $emailStr = $mailData['to_mail']; // get recipient email addresses
                    $fromEmail = $mailData['from_mail']; // get sender email address
                    // Validate email are valid or not and transform string to array
                    $toEmail = explode(",", $emailStr);

                    try {
                        // check toEmail addresses
                        foreach ($toEmail as $email) {
                            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                throw new Exception(__('messages.error.process_failed', ['process' => 'メール送信処理']));
                            }
                        }
                        // check fromEmail addresses
                        if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
                            throw new Exception(__('messages.error.process_failed', ['process' => 'メール送信処理']));
                        }

                        // check mail server configuration
                        $mailerConfig = config('mail.mailers.smtp');
                        if (empty($mailerConfig['host']) || empty($mailerConfig['port']) || empty($mailerConfig['username']) || empty($mailerConfig['password'])) {
                            throw new Exception();
                        }

                        // prepare for sending email
                        $mailSubject = __('messages.mail_subject.expxlsx_billing_payment'); // for mail subject
                        $currentYearForMail = Carbon::createFromFormat('Y/m/d', $this->orderDateTo)->format('Y年m月d日'); // current date time
                        $lastThreeYearForMail = Carbon::createFromFormat('Y/m/d', $this->orderDateFrom)->format('Y年m月d日');  // last 3 years date time
                        $mailContent = $lastThreeYearForMail . " ～ " . $currentYearForMail;    // for mail content

                        // sending mail with template file
                        Mail::to($toEmail)->send(new BillingPaymentOutMail($fromEmail, $mailSubject, $mailContent));

                    } catch (Exception $e) {
                        Log::error(__('messages.error.process_failed', ['process' => 'メール送信処理']));

                        // [ 〇〇件出力しました。メール送信でエラーが発生しました。] message save to 'execute_result'
                        $successMessage = $successMessage . __('messages.error.mail_sent_process_wrong');

                    }
                } else {
                    Log::error(__('messages.error.batch_error.data_not_found3', ['data' => 'メールアドレス']));

                    // [ 〇〇件出力しました。メール送信でエラーが発生しました。] message save to 'execute_result'
                    $successMessage = __('messages.info.notice_output_count', ['count' => $searchResultCount]) . __('messages.error.mail_sent_process_wrong');

                }
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
                'execute_result' => $successMessage,
                'execute_status' => BatchExecuteStatusEnum::SUCCESS->value,
                'file_path' => $savePath,
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
     * Get the database data mail address information
     * @return object
     */
    private function getFromToMailAddress()
    {
        $query = ShopGfhModel::query()
                ->select('mail_address_accounting_dept as to_mail', 'mail_address_from as from_mail')
                ->orderBy('m_shop_gfh_id', 'desc')
                ->first();

        return $query;
    }

    /**
     * Check the execution is regular
     * @return boolean value
     */
    private function isRegularFlg()
    {
        $tExecuteBatchInstructionId = $this->batchExecutionId;

        if ($tExecuteBatchInstructionId == "null") {
            return true;
        }

        return empty($tExecuteBatchInstructionId);
    }

    /**
     * Get the database data (join all table) with search condition
     * @return collection data
     */
    private function getCommonData($filters)
    {
        // get deposit value from ItemNameType Enum
        $depositType = ItemNameType::Deposit->value;

        // get receiptType value from ItemNameType Enum
        $receiptType = ItemNameType::ReceiptType->value;

        // get data for latest data time
        $deliverySubQuery = $this->getLastestDeliveryData();

        $query = OrderHdrModel::query()
                ->with([
                    'deliHdr' => function ($query) {
                        $query->select('m_cust_id', 't_order_hdr_id', 'deli_decision_date', 'deli_plan_date');
                    },
                    'billingHdr' => function ($query) {
                        $query->select('billing_amount', 'standard_tax_price', 'reduce_tax_price', 't_billing_hdr_id');
                    },
                    'payment' => function ($query) {
                        $query->select('t_payment_id', 'payment_entry_date', 'cust_payment_date', 'account_payment_date', 'payment_subject', 'payment_price', 't_order_hdr_id');
                    },
                    'payment.itemnameType' => function ($query) use ($depositType) {
                        $query->select('m_itemname_types_id', 'm_itemname_type', 'm_itemname_type_name')
                            ->where('m_itemname_type', '=', $depositType);
                    },
                    'orderType' => function ($query) use ($receiptType) {
                        $query->select('m_itemname_types_id', 'm_itemname_type', 'm_itemname_type_name')
                            ->where('m_itemname_type', '=', $receiptType);
                    },
                    'paymentTypes' => function ($query) {
                        $query->select('payment_type', 'm_payment_types_name', 'm_payment_types_id');
                    },
                    'orderMemo' => function ($query) {
                        $query->select('operator_comment', 't_order_hdr_id');
                    },
                ])
                ->joinSub($deliverySubQuery, 'latest_delivery', function ($join) {
                    $join->on('t_order_hdr.t_order_hdr_id', '=', 'latest_delivery.tOrderHdrId');
                })
                ->select(
                    'm_payment_types_id',
                    't_order_hdr.t_order_hdr_id',
                    't_order_hdr.t_billing_hdr_id',
                    't_order_hdr.progress_type',
                    't_order_hdr.deli_instruct_type',
                    't_order_hdr.deli_decision_type',
                    't_order_hdr.order_name',
                    't_order_hdr.m_cust_id_billing',
                    't_order_hdr.order_type',
                    't_order_hdr.m_ecs_id',
                    't_order_hdr.order_datetime',
                    'latest_delivery.deli_plan_date',
                    'latest_delivery.deli_decision_date',
                )
                ->orderBy('t_order_hdr_id', 'asc');

        // **** (start) for regular batch running
        if (!empty($filters['order_datetime_from'])) {
            $query->where('t_order_hdr.order_datetime', '>=', $filters['order_datetime_from']);    // 受注基本．受注日時
        }
        if (!empty($filters['order_datetime_to'])) {
            $query->where('t_order_hdr.order_datetime', '<=', $filters['order_datetime_to']);    // 受注基本．受注日時
        }
        // **** (end) for regular batch running

        // **** (start) for manual batch running
        // 受注基本.進捗区分
        if (!empty($filters['progress_classification'])) {
            $query->where('t_order_hdr.progress_type', '=', $filters['progress_classification']);
        }

        // 出荷基本.出荷予定日の最遅日
        if (!empty($filters['estimated_shipping_date_from'])) {
            $query->where('latest_delivery.deli_plan_date', '>=', $filters['estimated_shipping_date_from']);
        }
        if (!empty($filters['scheduled_ship_date_to'])) {
            $query->where('latest_delivery.deli_plan_date', '<=', $filters['scheduled_ship_date_to']);
        }

        // 出荷基本.出荷完了日
        if (!empty($filters['shipment_confirmation_date_from'])) {
            $query->where('latest_delivery.deli_decision_date', '>=', $filters['shipment_confirmation_date_from']);
        }
        if (!empty($filters['shipment_confirmation_date_to'])) {
            $query->where('latest_delivery.deli_decision_date', '<=', $filters['shipment_confirmation_date_to']);
        }

        // 受注基本.顧客ID
        if (!empty($filters['m_cust_id_billing'])) {
            $query->where('t_order_hdr.m_cust_id_billing', '=', $filters['m_cust_id_billing']);
        }

        // 入金.顧客入金日
        if (!empty($filters['customer_payment_date_from'])) {
            $query->whereHas('payment', function ($query) use ($filters) {
                $query->where('cust_payment_date', '>=', $filters['customer_payment_date_from']);
            });
        }
        if (!empty($filters['customer_payment_date_to'])) {
            $query->whereHas('payment', function ($query) use ($filters) {
                $query->where('cust_payment_date', '<=', $filters['customer_payment_date_to']);
            });
        }

        // 入金.口座入金日
        if (!empty($filters['account_deposit_date_from'])) {
            $query->whereHas('payment', function ($query) use ($filters) {
                $query->where('account_payment_date', '>=', $filters['account_deposit_date_from']);
            });
        }
        if (!empty($filters['account_deposit_date_to'])) {
            $query->whereHas('payment', function ($query) use ($filters) {
                $query->where('account_payment_date', '<=', $filters['account_deposit_date_to']);
            });
        }

        // 入金.入金登録日
        if (!empty($filters['payment_registration_date_from'])) {
            $query->whereHas('payment', function ($query) use ($filters) {
                $query->where('payment_entry_date', '>=', $filters['payment_registration_date_from']);
            });
        }
        if (!empty($filters['payment_registration_date_to'])) {
            $query->whereHas('payment', function ($query) use ($filters) {
                $query->where('payment_entry_date', '<=', $filters['payment_registration_date_to']);
            });
        }

        // 受注基本.支払い方法マスタID
        if (!empty($filters['payment_method'])) {
            $query->where('t_order_hdr.m_payment_types_id', '=', $filters['payment_method']);
        }

        // 受注基本.受注方法ID
        if (!empty($filters['order_method'])) {
            $query->where('t_order_hdr.order_type', '=', $filters['order_method']);
        }

        // 受注基本.入金区分
        if (!empty($filters['payment_classification'])) {
            $query->where('t_order_hdr.payment_type', '=', $filters['payment_classification']);
        }

        // 受注基本.ECサイトID
        if (!empty($filters['ec_site'])) {
            $query->where('t_order_hdr.m_ecs_id', '=', $filters['ec_site']);
        }

        // 受注社内メモ.社内メモ
        if (!empty($filters['internal_memo'])) {
            $query->whereHas('orderMemo', function ($query) use ($filters) {
                $query->where('operator_comment', 'LIKE', "%{$filters['internal_memo']}%");
            });
        }

        // 入金.入金科目
        if (!empty($filters['deposit_account'])) {
            $query->whereHas('payment', function ($query) use ($filters) {
                $query->where('payment_subject', '=', $filters['deposit_account']);
            });
        }

        // 受注基本.出荷指示区分
        if (!empty($filters['shipping_instruction_category'])) {
            $query->where('t_order_hdr.deli_instruct_type', '=', $filters['shipping_instruction_category']);
        }

        // 受注基本.出荷確定区分
        if (!empty($filters['shipping_confirmation_category'])) {
            $query->where('t_order_hdr.deli_decision_type', '=', $filters['shipping_confirmation_category']);
        }
        // **** (end) for manual batch running

        return $query;
    }

    /**
     * Get the database data with search condition
     * @param $searchInfo
     * @return collection data
     */
    private function getData($searchInfo)
    {
        // $searchInfo parameter collect to array
        $filters = [
            'payment_registration_date_from' => $searchInfo['payment_registration_date_from'] ?? '', // 入金.入金登録日
            'payment_registration_date_to' => $searchInfo['payment_registration_date_to'] ?? '',     // 入金.入金登録日
            'customer_payment_date_from' => $searchInfo['customer_payment_date_from'] ?? '',         // 入金.顧客入金日
            'customer_payment_date_to' => $searchInfo['customer_payment_date_to'] ?? '',             // 入金.顧客入金日
            'account_deposit_date_from' => $searchInfo['account_deposit_date_from'] ?? '',           // 入金.口座入金日
            'account_deposit_date_to' => $searchInfo['account_deposit_date_to'] ?? '',               // 入金.口座入金日
            'deposit_account' => $searchInfo['deposit_account'] ?? '',         // 入金.入金科目
            'internal_memo' => $searchInfo['internal_memo'] ?? '',              // 受注社内メモ.社内メモ
            'estimated_shipping_date_from' => $searchInfo['estimated_shipping_date_from'] ?? '',  // 出荷基本.出荷予定日の最遅日
            'scheduled_ship_date_to' => $searchInfo['scheduled_ship_date_to'] ?? '',              // 出荷基本.出荷予定日の最遅日
            'shipment_confirmation_date_from' => $searchInfo['shipment_confirmation_date_from'] ?? '',  // 出荷基本.出荷完了日
            'shipment_confirmation_date_to' => $searchInfo['shipment_confirmation_date_to'] ?? '',      // 出荷基本.出荷完了日
            'progress_classification' => $searchInfo['progress_classification'] ?? '',    // 受注基本.進捗区分
            'm_cust_id_billing' => $searchInfo['m_cust_id_billing'] ?? '',                // 受注基本.顧客ID
            'payment_method' => $searchInfo['payment_method'] ?? '',              // 受注基本.支払い方法マスタID
            'order_method' => $searchInfo['order_method'] ?? '',                  // 受注基本.受注方法ID
            'payment_classification' => $searchInfo['payment_classification'] ?? '',      // 受注基本.入金区分
            'ec_site' => $searchInfo['ec_site'] ?? '',                            // 受注基本.ECサイトID
            'shipping_instruction_category' => $searchInfo['shipping_instruction_category'] ?? '',      // 受注基本.出荷指示区分
            'shipping_confirmation_category' => $searchInfo['shipping_confirmation_category'] ?? '',    // 受注基本.出荷確定区分
        ];

        // call database query function with filters
        $query = $this->getCommonData($filters)->get();

        $queryCount = count($query);
        $csvFilePath = tempnam(sys_get_temp_dir(), 'csv') . '.csv'; // create temp csv file
        $csvFile = fopen($csvFilePath, 'w');  // open and write data

        // write header row to the csv file
        $header = [
            '受注ID', '進捗区分', '出荷指示区分', '出荷確定区分', '受注方法',
            '支払方法', '顧客氏名', '顧客ID', '出荷予定日', '出荷確定日',
            '請求金額', '消費税額(8%)', '消費税額(10%)', '入金No', '入金額',
            '入金科目', '顧客入金日', '口座入金日', '入金登録日'
        ];
        fputcsv($csvFile, $header);

        foreach ($query as $row) {
            $csvRow = $this->dataRow($row);
            fputcsv($csvFile, $csvRow); // insert each row data
        }

        fclose($csvFile);   // close csv file after all data is written
        $csvFileContent = file_get_contents($csvFilePath); // read csv content
        $this->csvFileContent = $csvFileContent;    // get csv file content to change zip file
        unlink($csvFilePath);   // delete temporary csv file

        return $queryCount;
    }

    /**
     * Get the database data from three years ago
     * @return collection data
     */
    private function getRegularData()
    {
        $fromLastThreeYear = $this->lastThreeYearDate->format('Y-m-d'); // last 3 years date time
        $toCurrentYear = $this->currentDate->format('Y-m-d');               // current date time

        $filters = [
            'order_datetime_from' => $fromLastThreeYear,
            'order_datetime_to' => $toCurrentYear,
        ];

        // call database query function with filters
        $query = $this->getCommonData($filters);

        $currentPage = self::DEFAULT_CURRENT_PAGE;   // for current page for pagination database data
        $dataLimit = config('env.csv_data_limit');   // data limit for database data

        $queryCount = $query->count();  // get the result query data count

        $csvFilePath = tempnam(sys_get_temp_dir(), 'csv') . '.csv'; // create temp csv file
        $csvFile = fopen($csvFilePath, 'w');  // open and write data

        // write header row to the csv file
        $header = [
            '受注ID', '進捗区分', '出荷指示区分', '出荷確定区分', '受注方法',
            '支払方法', '顧客氏名', '顧客ID', '出荷予定日', '出荷確定日',
            '請求金額', '消費税額(8%)', '消費税額(10%)', '入金No', '入金額',
            '入金科目', '顧客入金日', '口座入金日', '入金登録日'
        ];
        fputcsv($csvFile, $header);

        $query->chunkById($dataLimit, function ($rows) use (&$dataWithPage, $csvFile) {
            foreach ($rows as $row) {
                $csvRow = $this->dataRow($row);
                fputcsv($csvFile, $csvRow);
            }
        }, 't_order_hdr_id');

        fclose($csvFile);   // close csv file after all data is written
        $csvFileContent = file_get_contents($csvFilePath); // read csv content
        $this->csvFileContent = $csvFileContent;    // get csv file content to change zip file
        unlink($csvFilePath);   // delete temporary csv file

        return $queryCount;
    }

    /**
     * Create the database data as CSV file
     *
     * @param array $data
     * @return string
     */
    private function dataRow($row)
    {
        $billing_amount = (int) $row['billingHdr']['billing_amount'] ?? '';
        $standard_tax_price = (int) $row['billingHdr']['standard_tax_price'] ?? '';
        $reduce_tax_price = (int) $row['billingHdr']['reduce_tax_price'] ?? '';
        $payment_price = (int) $row['payment'][0]['payment_price'] ?? '';

        $csvRow = [
            ($row['t_order_hdr_id'] ?? ''),
            $this->getEnumLabel(ProgressTypeEnum::cases(), $row['progress_type']),
            $this->getEnumLabel(DeliInstructTypeEnum::cases(), $row['deli_instruct_type']),
            $this->getEnumLabel(DeliDecisionTypeEnum::cases(), $row['deli_decision_type']),
            ($row['orderType']['m_itemname_type_name'] ?? ''),
            ($row['paymentTypes']['m_payment_types_name'] ?? ''),
            ($row['order_name'] ?? ''),
            ($row['m_cust_id_billing'] ?? ''),
            $this->convertDateFormat($row['deliHdr'][0]['deli_plan_date']),
            $this->convertDateFormat($row['deliHdr'][0]['deli_decision_date']),
            $billing_amount,
            $standard_tax_price,
            $reduce_tax_price,
            ($row['payment'][0]['t_payment_id'] ?? ''),
            $payment_price,
            ($row['payment'][0]['itemnameType']['m_itemname_type_name'] ?? ''),
            $this->convertDateFormat($row['payment'][0]['cust_payment_date']),
            $this->convertDateFormat($row['payment'][0]['account_payment_date']),
            $this->convertDateFormat($row['payment'][0]['payment_entry_date']),
        ];

        return $csvRow;
    }

    /**
     * Get the database data the latest date time with group by customer
     * @return collection data
     */
    private function getLastestDeliveryData()
    {
        // get data the latest data time
        $subQuery = DeliHdrModel::select('m_cust_id', 't_order_hdr_id as tOrderHdrId', 'deli_decision_date', 'deli_plan_date')
                        ->whereIn('deli_plan_date', function ($query) {
                            $query->select(DB::raw('MAX(deli_plan_date)'))
                                ->from('t_deli_hdr')
                                ->groupBy('t_order_hdr_id');
                        })
                        ->orWhereIn('deli_decision_date', function ($query) {
                            $query->select(DB::raw('MAX(deli_decision_date)'))
                                ->from('t_deli_hdr')
                                ->groupBy('t_order_hdr_id');
                        });

        return $subQuery;
    }

    /**
     * Create Zip file from CSV file
     *
     * @param array $data
     * @return string
     */
    private function createZipFile($csvFileContent)
    {
        // 一時保存
        $zip = new ZipArchive();
        //create tempory zip file path
        $tempZipPath = tempnam(sys_get_temp_dir(), 'zip') . '.zip';

        if ($zip->open($tempZipPath, ZipArchive::CREATE) !== true) {
            throw new Exception("Unable to open the ZIP file.", self::PRIVATE_THROW_ERR_CODE);
        }

        // 定期実行チェック
        if ($this->isRegularFlg()) {
            // 定期実行の場合
            $csvFileName = $this->currentDate->format('Ymd_His') . '.csv';
        } else {
            // 手動起動の場合
            $csvFileName = $this->batchExecutionId . '.csv';
        }

        // CSVファイルをZIPに追加
        $zip->addFromString($csvFileName, $csvFileContent);
        $zip->close();

        //read zip file path
        $zipFileContent = file_get_contents($tempZipPath);
        unlink($tempZipPath); // clear temp file path

        return $zipFileContent;
    }

    /**
    * convert date format
    *
    * @param $value
    * @return array
    */
    private function convertDateFormat($value)
    {
        if (empty($value)) {
            return "";
        }

        // get date time with 'Y/m/d' format
        return Carbon::parse($value)->format('Y/m/d');
    }

    /**
     * Create Zip file from CSV file
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

    /**
     * 定期実行
     *
     * get file path to save on S3 server
     * @return string (regular csv zip file path)
     */
    private function getRegularZipFilePath($accountCode, $currentDate)
    {
        return $accountCode . "/output/" . $currentDate . '.zip';
    }

}
