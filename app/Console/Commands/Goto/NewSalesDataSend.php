<?php

namespace App\Console\Commands\Goto;

use App\Enums\BatchExecuteStatusEnum;
use App\Enums\ProgressTypeEnum;
use App\Enums\ThreeTemperatureZoneTypeEnum;
use App\Mail\SaleDataMail;
use App\Models\Common\Base\ExecuteBatchInstructionModel;
use App\Models\Master\Base\ShopGfhModel;
use App\Models\Order\Gfh1207\DeliHdrModel;
use App\Models\Order\Gfh1207\DeliveryDetailModel;
use App\Models\Order\Gfh1207\OrderHdrModel;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Master\Gfh1207\Enums\BatchListEnum;

use App\Modules\Order\Gfh1207\GetCsvExportFilePath;
use App\Services\TenantDatabaseManager;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class NewSalesDataSend extends Command
{
    /**
    * The name and signature of the console command.
    *
    * @var string
    */
    protected $signature = 'command:NewSalesDataSend {t_execute_batch_instruction_id : バッチ実行指示ID} {json? :  JSON化した引数}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '新売上データCSVファイルを作成し、FESTAへメール送信する。';

    // バッチ名
    protected $batchName = '新売上データ作成＆送信';

    // バッチType
    protected $batchType = BatchListEnum::SEND_NEW_SALES_DATA->value;

    // テンプレートファイルパス
    protected $templateFilePath;

    // テンプレートファイル名
    protected $templateFileName;

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    // 検品データ取得
    protected $getCsvExportFilePath;

    // エラーコード
    private const PRIVATE_THROW_ERR_CODE = -1;

    /**
     * Create a new command instance.
     */
    public function __construct(
        StartBatchExecuteInstructionInterface $startBatchExecute,
        EndBatchExecuteInstructionInterface $endBatchExecute,
        GetCsvExportFilePath $getCsvExportFilePath,
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->getCsvExportFilePath = $getCsvExportFilePath;
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $searchInfo = json_decode($this->argument('json'), true);
        // $searchInfo に必要なパラメータが含まれているか確認する
        $paramKey = ['m_account_id','aggreration_date'];
        try {
            // json decode error
            if (json_last_error() !== JSON_ERROR_NONE) {
                // [パラメータが不正です。] message will display in log file.
                throw new \Exception(__('messages.error.invalid_parameter'));
            }
            if(!array_key_exists('aggreration_date', $searchInfo)){
                unset($paramKey[1]);
            }
            if (!empty(array_diff($paramKey, array_keys($searchInfo)))) {
                // [パラメータが不正です。] message will display in log file.
                throw new \Exception(__('messages.error.invalid_parameter'));
            }

            // deli_Decision_date validation
            $deliDecisionDate = $searchInfo['aggreration_date'] ?? Carbon::today()->format('Y/m/d');

            // Parameter reference or not;
            $isDateInclude = $searchInfo['aggreration_date'] ?? false;

            /**
                * [共通処理] 開始処理
                * バッチ実行指示テーブル（t_execute_batch_instruction ）から該当バッチの取得と開始処理
                * t_execute_batch_instruction_id より該当バッチを取得し以下の情報を書き込む
                * - バッチ開始時刻
            */
            $options = [
                'm_account_id' => $searchInfo['m_account_id'], // m_account_idが実行されます
                'execute_batch_type' => $this->batchType, // バッチタイプ
                'execute_conditions' => [
                    'm_account_id' => $searchInfo['m_account_id'],
                    'aggreration_date' => $deliDecisionDate,
                ], // パラメータに基づくバッチ実行条件
            ];

            $batchExecute = $this->startBatchExecute->execute(null, $options);

            $accountCode = $batchExecute->account_cd;     // for account cd
            $accountId = $batchExecute->m_account_id;   // for m account id
            $batchType = $batchExecute->execute_batch_type;  // for batch type

            if (app()->environment('testing')) {
                // テスト環境の場合
                TenantDatabaseManager::setTenantConnection($accountCode . '_db_testing');
            } else {
                TenantDatabaseManager::setTenantConnection($accountCode . '_db');
            }

        } catch (Exception  $e) {
            // write the log error in laravel.log for error message
            Log::error('error_message : ' . $e->getMessage());
            return;
        }

        DB::beginTransaction();
        try {
            // Get latest batch job end date from t_execute_batch_instruction table
            $jobEndDateTime = ExecuteBatchInstructionModel::where('execute_batch_type' , $this->batchType)
                            ->whereNotNull('batchjob_end_datetime')
                            ->select('batchjob_end_datetime')
                            ->get()
                            ->sortByDesc('batchjob_end_datetime')
                            ->first()
                            ?->batchjob_end_datetime;

            // parameter collection for data fetch 
            $filters = [
                'aggreration_date' => Carbon::parse($deliDecisionDate)->format('Y-m-d'), // parse any date format to YYYY-MM-DD format for database query column
                'isDateParamInclude' => ($isDateInclude) ? true : false, // verify dataParameter is include or not
                'jobEndDateTime' => $jobEndDateTime,
            ];

            // 荷基本テーブルと出荷明細テーブルから売上データを取得する
            $saleData = $this->getSaleData($filters);
            // 受注基本から送料データを取得する
            $shippedData = $this->getShippedData($filters);
            // 受注基本テーブルと受注明細テーブルから返品データを取得する
            $returnedData = $this->getReturnedData($filters);
            // 支払手数料データを取得する
            $paymentFees = $this->getPaymentFees($filters);
            // 冷蔵手数料データと冷凍手数料データを取得する
            $freezeData = $this->getFreezeData($filters);

            // check total rows are empty or not
            if (empty($saleData) 
                && empty($shippedData) 
                && empty($returnedData) 
                && empty($paymentFees) 
                && empty($freezeData) 
                && empty($coolData)) {
                // [出力対象のデータがありませんでした。] message save to 'execute_result'
                $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                    'execute_result' =>  __('messages.error.data_not_found2', ['datatype' => '出力']),
                    'execute_status' => BatchExecuteStatusEnum::SUCCESS->value
                ]);

                DB::commit();
                return ;
            }
            // collection of array data.
            $data = [
                'sale' => $saleData,
                'shipped' => $shippedData,
                'returned' => $returnedData,
                'paymentFees' => $paymentFees,
                'freeze' => $freezeData,
            ];

            $today = Carbon::now()->format('Ymd');

            // CSV file preparation
            $fileName = $today . 'ShohinUriage'; // naming 8桁の日付＋ShohinUriage
            $csvData = $this->createCsvData($data);
            $recordCount = $csvData['recordCount'];
            
            // Save CSV file to file system
            $filePathSave = $this->getCsvExportFilePath->execute($accountCode, $batchType, $fileName);
            $fileuploaded = Storage::disk(config('filesystems.default', 'local'))->put($filePathSave, $csvData['csvData']);

            // return fail if there is an error
            if($fileuploaded === false) {
                throw new Exception(__('messages.error.upload_s3_failed'), self::PRIVATE_THROW_ERR_CODE);
            }
            // get file path
            $filePath = $this->downloadFileFromS3($filePathSave);

            // メールアドレス取得
            $mailData = $this->getFromToMailAddress();
            // [ 〇〇件出力しました。] message save to 'execute_result'
            $successMessage = __('messages.info.notice_output_count', ['count' => $recordCount]);

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
            
                    // Check mail server configuration
                    $mailerConfig = config('mail.mailers.smtp');
                    if (empty($mailerConfig['host']) || empty($mailerConfig['port']) || empty($mailerConfig['username']) || empty($mailerConfig['password'])) {
                        throw new Exception(__('messages.error.process_failed', ['process' => 'メール送信処理']));
                    }
            
                    // メール送信
                    Mail::to($toEmail)->send(new SaleDataMail($filePath, $fileName, $fromEmail));
                } catch (Exception $e) {  
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    // [ 〇〇件出力しました。メール送信でエラーが発生しました。] message save to 'execute_result'
                    $successMessage = $successMessage . __('messages.error.mail_sent_process_wrong');
                }
            } else {
                Log::error(__('messages.error.batch_error.data_not_found3', ['data' => 'メールアドレス']));

                // [ 〇〇件出力しました。メール送信でエラーが発生しました。] message save to 'execute_result'
                $successMessage = $successMessage . __('messages.error.mail_sent_process_wrong');
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
                'file_path' => $filePathSave,
            ]);
            DB::commit();

        } catch (Exception  $e) {
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
     * Get from/to mail addresses
     *
     * @return array
     */
    private function getFromToMailAddress()
    {
        // メールアドレス取得
        $query = ShopGfhModel::query()
            ->select('mail_address_festa_sales as to_mail', 'mail_address_from as from_mail')
            ->orderBy('m_shop_gfh_id', 'desc')
            ->first();

        return $query;
    }

    /**
     * Get the Sale data from Deli Hdr Model
     * @param array $filters
     * @return collection data
     */
    private function getSaleData($filters)
    {
        try {
            $saleData = DeliveryDetailModel::where('deli_decision_date' , $filters['aggreration_date'])
                                            ->select('t_delivery_dtl_id', 't_delivery_hdr_id','t_order_hdr_id', 'sell_cd', 'order_sell_vol', 'order_sell_price','deli_decision_date')
                                            ->with(['deliHdr:t_deli_hdr_id,m_cust_id', 
                                                    'deliHdr.cust:m_cust_id,m_cust_runk_id',
                                                    'deliHdr.cust.custRunk:m_itemname_types_id,m_itemname_type_code'])                                            
                                            ->get()
                                            ->sortBy('t_order_hdr_id')
                                            ->groupBy([
                                                'sell_cd',
                                                'cust.m_cust_runk_id',
                                                'order_sell_price',
                                                'deli_decision_date',
                                            ])
                                            ->flatMap(function ($group) use ($filters) {
                                                // Flatten and remove duplicates by layers
                                                return $group->flatten(3)->map(function ($data) use ($filters) {
                                                    $deliHdr = $data->deliHdr->toArray();
                                                        return [
                                                            "product_code" => $data->sell_cd ?? null,
                                                            "count" => $data->order_sell_vol ?? null,
                                                            "unit_price" => $data->order_sell_price ?? null,
                                                            "amount" => round(($data->order_sell_vol ?? 0) * ($data->order_sell_price ?? 0)),
                                                            "cust_rank" => $deliHdr['cust']['cust_runk']['m_itemname_type_code'] ?? null,
                                                            "deli_date" => $filters['aggreration_date'] ?? null,
                                                            "external_sale_classify" => null,
                                                        ];
                                                    });
                                                    // Assuming one detail per header                                            
                                                })
                                            ->values()
                                            ->toArray();

            $result = [];
            // Error handling for empty data
            if(empty($saleData)){   
                return $result; 
            }  
            
            // Sum total data of the value
            foreach ($saleData as $item) {
                $code = $item['product_code'] . '_' . $item['cust_rank'] . '_' . $item["unit_price"] . '_' . $item['deli_date']; // for groupby data checkpoint
                if (!isset($result[$code])) {
                    $result[$code] = [
                        "product_code" => $item['product_code'],
                        "count" => 0,
                        "unit_price" => round($item["unit_price"]),
                        "amount" => 0,
                        "cust_rank" => $item["cust_rank"],
                        "deli_date" => Carbon::parse($item["deli_date"])->format('Ymd'),
                        "external_sale_classify" => $item["external_sale_classify"]
                    ];
                }
                $result[$code]["count"] += $item["count"];
                $result[$code]["amount"] += $item["amount"];
            }
            $result = array_values($result); // Reset keys for clean output
            return $result;
        } catch (Exception  $e) {
            throw $e;
        }
    }

    /**
     * Get the Shipped data from Order Hdr Model
     * @param array $filters
     * @return collection data
     */
    private function getShippedData($filters)
    {        
        try {
            $shippedProductCode = 'SORYO';
            $result = OrderHdrModel::with('cust.custRunk', 'orderDestination')
                                    ->where('progress_type', ProgressTypeEnum::Shipped->value)
                                    ->when($filters['jobEndDateTime'], function ($query) use($filters) {
                                        $query->where('progress_update_datetime', '>=' , $filters['jobEndDateTime']) // aggreration_date date is earlier than equal to progress_update_datetime date
                                              ->whereDate('progress_update_datetime', '<=' , $filters['aggreration_date']); 
                                    }, function ($query) use($filters) {
                                        $query->whereDate('progress_update_datetime', '<=' , $filters['aggreration_date']); // job_end date time is later than equal to progress_update_datetime date
                                    })
                                    ->get()
                                    ->groupBy([
                                        't_order_hdr_id',
                                        'cust.m_cust_runk_id',
                                        'sales_store'
                                    ])
                                    ->flatMap(function ($group) use ($filters, $shippedProductCode) {
                                        // Flatten and remove duplicates by layers
                                        return $group->flatten(2)->map(function ($item) use ($filters, $shippedProductCode) {
                                            return [
                                                "product_code" => $shippedProductCode ?? null,
                                                "count" => count($item->orderDestination) ?? 0,
                                                "unit_price" => 0,
                                                "amount" => round($item->shipping_fee) ?? 0,
                                                "cust_rank" => $item->cust->custRunk->m_itemname_type_code ?? null,
                                                "deli_date" => Carbon::parse($filters['aggreration_date'])->format('Ymd') ?? null,
                                                "external_sale_classify" => null,
                                            ];
                                        });
                                    })
                                    ->values()
                                    ->toArray();
            return $result;
        } catch (Exception  $e) {
            throw $e;
        }
    }

    /**
     * Get the Returned data from Order Hdr Model
     * @param array $filters
     * @return collection data
     */
    private function getReturnedData($filters)
    {
        try {
            $returnedData = OrderHdrModel::with('cust.custRunk', 'orderDtl')
                            ->where('progress_type', ProgressTypeEnum::Returned->value)
                            ->when($filters['jobEndDateTime'], function ($query) use($filters) {
                                $query->where('progress_update_datetime', '>=' , $filters['jobEndDateTime']) // aggreration_date date is earlier than equal to progress_update_datetime date
                                      ->whereDate('progress_update_datetime', '<=' , $filters['aggreration_date']); 
                            }, function ($query) use($filters) {
                                $query->whereDate('progress_update_datetime', '<=' , $filters['aggreration_date']); // job_end date time is later than equal to progress_update_datetime date
                            })
                            ->get()
                            ->groupBy([
                                fn ($item) => $item->orderDtl->pluck('sell_cd')->toArray(),
                                'cust.m_cust_runk_id',
                                'sales_store',
                                fn ($item) => $item->orderDtl->pluck('order_sell_price')->toArray(),
                            ])
                            ->flatMap(function ($group) use ($filters) {
                                // Flatten and remove duplicates by layers
                                return $group->flatten(3)->map(function ($item) use ($filters) {
                                    // Assuming one detail per header
                                    return $item->orderDtl->map(function ($data) use ($filters, $item){ 
                                        return [
                                            "product_code" => $data->sell_cd ?? null,
                                            "count" => ($data['order_sell_vol'] * -1) ?? 0,
                                            "unit_price" => $data['order_sell_price'] ?? 0,
                                            "amount" => (round($data['order_sell_price'] * $data['order_sell_vol'] * -1)) ?? 0,
                                            "cust_rank" => $item->cust->custRunk->m_itemname_type_code ?? null,
                                            "deli_date" => $filters['aggreration_date'] ?? null,
                                            "external_sale_classify" => null,
                                            'sales_store' => $item['sales_store'],
                                        ];
                                    });
                                });
                            })
                            ->values()
                            ->toArray();
            $result = [];
            // Error handling for empty data
            if(empty($returnedData)){
                return $result; 
            }  
            $modifiedReturnedeData = array_merge(...array_map('array_values', $returnedData));
            // Sum order_sell_vol
            foreach ($modifiedReturnedeData as $item) {
                $code = $item['product_code'] . '_' . $item['cust_rank'] . '_' . $item['sales_store'] . '_' . $item["unit_price"]; // for groupby data checkpoint
                if (!isset($result[$code])) {
                    $result[$code] = [
                        "product_code" => $item['product_code'],
                        "count" => 0,
                        "unit_price" => round($item["unit_price"]),
                        "amount" => 0,
                        "cust_rank" => $item["cust_rank"],
                        "deli_date" => Carbon::parse($item['deli_date'])->format('Ymd'),
                        "external_sale_classify" => $item["external_sale_classify"]
                    ];
                }
                $result[$code]["count"] += $item["count"];
                $result[$code]["amount"] += $item["amount"];
            }
            $result = array_values($result); // Reset keys for clean output
            return $result;
        } catch (Exception  $e) {
            throw $e;
        }
    }

    /**
     * Create the database data as CSV file
     *
     * @param array $filters
     * @return collection data
     */
    private function getPaymentFees($filters)
    {
        try {
            $paymentFees = OrderHdrModel::with(['deliHdr', 'cust.custRunk'])
                            ->when($filters['isDateParamInclude'], function ($query) {
                                $query->whereIn('progress_type',[
                                    ProgressTypeEnum::Shipping->value, 
                                    ProgressTypeEnum::Shipped->value,
                                    ProgressTypeEnum::PendingPostPayment->value,
                                    ProgressTypeEnum::Completed->value
                                ]);
                            }, function ($query) {
                                $query->whereIn('progress_type',[
                                    ProgressTypeEnum::Shipping->value, 
                                    ProgressTypeEnum::Shipped->value,
                                    ProgressTypeEnum::PendingPostPayment->value,
                                ]);
                            })
                            ->whereHas('deliHdr', function ($query) {
                                $query->whereNotNull('deli_decision_date');
                            })
                            ->get()
                            ->groupBy([
                                't_order_hdr_id',
                                'cust.m_cust_runk_id',
                                'transfer_fee'
                            ])
                            ->flatMap(function ($group) use ($filters)  {
                                return $group->flatten(3)->map(function ($item) use ($filters) {
                                    $deliHdr = $item->deliHdr->sortBy('deli_decision_date')->first();
                                    if($deliHdr['deli_decision_date'] === $filters['aggreration_date']){
                                        return [
                                            't_order_hdr_id' => $item->t_order_hdr_id ?? null,
                                            'count' => count($item->deliHdr) ?? 0,
                                            'amount' => round($item->transfer_fee) ?? 0, 
                                            'cust_rank' => $item->cust->custRunk->m_itemname_type_code ?? null,
                                            'deli_date' => $deliHdr['deli_decision_date'] ?? null,
                                            'external_sale_classify' => null,
                                        ];
                                    }
                                });  
                            })
                            ->values()
                            ->toArray();
            $result = [];
            // Error handling for empty data
            if(empty($paymentFees)){
                return $result; 
            }  
            // Sum payment fees
            foreach ($paymentFees as $item) {
                $paymentFeesProductCode = 'U1';
                // validate array is empty or not.
                if(!empty($item)){
                    $rank = $item["t_order_hdr_id"] . '_' . $item["cust_rank"] . '_' . $item["amount"]; // for groupby data checkpoint
                    if (!isset($result[$rank])) {
                        $result[$rank] = [
                            "product_code" => $paymentFeesProductCode,
                            "count" => 0,
                            "unit_price" => 0,
                            "amount" => 0,
                            "cust_rank" => $item["cust_rank"],
                            "deli_date" => Carbon::parse($item['deli_date'])->format('Ymd'),
                            "external_sale_classify" => $item["external_sale_classify"]
                        ];
                    }
                    $result[$rank]["count"] += $item["count"];
                    $result[$rank]["amount"] += $item["amount"];
                }
            }
            $result = array_values($result); // Reset keys for clean output
            return $result;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * Create the database data as CSV file
     *
     * @param array $filters
     * @return collection data
     */
    private function getFreezeData($filters)
    {
        try {
            $freezeData = DeliHdrModel::where('deli_decision_date', $filters['aggreration_date'])
                                    ->whereIn('temperature_zone', [
                                        ThreeTemperatureZoneTypeEnum::FROZEN->value, 
                                        ThreeTemperatureZoneTypeEnum::COOL->value
                                    ])
                                    ->select(['t_deli_hdr_id','m_cust_id','temperature_zone','payment_fee', 'deli_decision_date'])
                                    ->with([
                                        'cust' => function ($query) {
                                            $query->select(['m_cust_id', 'm_cust_runk_id'])
                                            ->with([
                                                'custRunk' => function ($query) {
                                                    $query->select([
                                                        'm_itemname_types_id','m_itemname_type_code'
                                                    ]);
                                                }
                                            ]);
                                        }
                                    ])
                                    ->get()
                                    ->groupBy([
                                        'cust.m_cust_runk_id',
                                    ])
                                    ->sortByDesc('temperature')
                                    ->toArray();
            $result = [];
            // sum all the result of collection data
            foreach ($freezeData as $item) {
                foreach ($item as $data) {
                    // productcode assign base on temperature
                    $productCode = ($data['temperature_zone'] == ThreeTemperatureZoneTypeEnum::FROZEN->value) ? 'U4' 
                                    : (($data['temperature_zone'] == ThreeTemperatureZoneTypeEnum::COOL->value) ? 'U6' 
                                    : null);
                    $code = $productCode . "_" . $data['cust']['m_cust_runk_id']; //groupby base on product code and rank
                    // calculation data
                    if (!isset($result[$code])) {
                        $result[$code] = [
                            "product_code" => $productCode,
                            "count" => count($item),
                            "unit_price" => 0,
                            'amount' => 0,
                            "cust_rank" => $data['cust']['cust_runk']['m_itemname_type_code'],
                            "deli_date" => Carbon::parse($data['deli_decision_date'])->format('Ymd'),
                            "external_sale_classify" => null,
                        ];
                    }
                    $result[$code]["amount"] += round($data["payment_fee"]);
                }
            }
            $result = array_values($result); // Reset keys for clean output
            sort($result); // sorting the array in ascending
            return $result;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * Create the database data as CSV file
     *
     * @param array $csvData
     * @return string
     */
    private function createCsvData($csvData)
    {
        try {
            // create tempory csv file path
            $csvFilePath = tempnam(sys_get_temp_dir(), 'csv') . '.csv';
            // open and write data
            $csvFile = fopen($csvFilePath, 'w');

            $header = [
                '商品コード','数量', '単価', '金額税抜','得意先CD','出荷年月日','外販内訳'
            ];
            // character encode to shift-jis
            $header = array_map(function ($value) {
                return mb_convert_encoding($value, 'SJIS', 'UTF-8');
            }, $header);
            fputcsv($csvFile, $header); // insert header data
            $countRecord = 0;
            foreach ($csvData as $key => $csvRow) {
                foreach ($csvRow as $key => $row) {
                    $outputRow = array_map(function ($value) {
                        return mb_convert_encoding($value, 'SJIS', 'UTF-8');
                    }, $row);
                    $countRecord++;
                    fputcsv($csvFile, $outputRow);
                }
            }

            // csv file close
            fclose($csvFile);
            // read csv file path
            $csvContent = file_get_contents($csvFilePath);
            unlink($csvFilePath);   // clear temp file path
            $result = [
                'csvData' => $csvContent,
                'recordCount' => $countRecord, 
            ];
            return $result;
        } catch (Exception  $e) {
            throw $e;
        }
    }

    /**
     * downloadFileFromS3
     *
     * @param  string $filePath
     * @return string
     */
    private function downloadFileFromS3(string $filePath): string
    {
        // Stream file from S3
        $stream = Storage::disk(config('filesystems.default', 'local'))->readStream($filePath);

        if (!$stream) {
            // [新売上データ作成＆送信ファイルが見つかりません。（:filePath] message save to 'execute_result'
            throw new Exception(__('messages.error.file_not_found', ['file' => '新売上データ作成＆送信', 'path' => $filePath]), self::PRIVATE_THROW_ERR_CODE);
        }

        $localPath = sys_get_temp_dir() . '/' . basename($filePath);

        // Create a local file and write the stream to it
        $localFile = fopen($localPath, 'w+');

        if ($localFile === false) {
            // if the file could not be created or opened
            throw new Exception(__('messages.error.file_create_failed'));
        }

        // Write the stream contents to the local file
        stream_copy_to_stream($stream, $localFile);

        // Close the streams
        fclose($localFile);
        fclose($stream);

        return $localPath;
    }
}
