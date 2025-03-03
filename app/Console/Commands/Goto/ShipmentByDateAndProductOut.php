<?php

namespace App\Console\Commands\Goto;

use App\Enums\BatchExecuteStatusEnum;
use App\Enums\ProgressTypeEnum;
use App\Events\ModuleFailed;
use App\Mail\ShipmentByDateAndProductOutMail;
use App\Models\Master\Base\ShopGfhModel;
use App\Models\Order\Gfh1207\OrderDestinationModel;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Common\Gfh1207\CheckBatchParameter;
use App\Modules\Master\Gfh1207\Enums\BatchListEnum;
use App\Modules\Order\Gfh1207\GetZipExportFilePath;
use App\Services\TenantDatabaseManager;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ShipmentByDateAndProductOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ShipmentByDateAndProductOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '日別の商品別の出荷、未出荷の数量と金額を抽出し、CSVファイルに保存する。 CSVファイルを所定のメールアドレスへ送信する。';

    // バッチ名
    protected $batchName = '日別商品別出荷未出荷バッチ';

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    // parameter check
    protected $checkBatchParameter;

    // export file path
    protected $getZipExportFilePath;

    // disk name
    protected $disk;

    // const variable
    private const PRIVATE_THROW_ERR_CODE = -1; // specify customize error code
    private const EMPTY_DATA_ROW_COUNT = 0;  // specify empty value
    private const STARTUP_TIMING_LIST = [1, 2]; // specify startup timing list
    private const SEARCH_RANGE_DAYS = 60; // specify day range

    /**
     * Create a new command instance.
     */
    public function __construct(
        StartBatchExecuteInstructionInterface $startBatchExecute,
        EndBatchExecuteInstructionInterface $endBatchExecute,
        CheckBatchParameter $checkBatchParameter,
        GetZipExportFilePath $getZipExportFilePath,
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->checkBatchParameter = $checkBatchParameter;
        $this->getZipExportFilePath = $getZipExportFilePath;

        // get disk name s3
        $this->disk = config('filesystems.default', 'local');

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

            // get json from parameter
            $searchInfo = json_decode($this->argument('json'), true);

            // required parameter
            $paramKey = ['m_account_id', 'times'];

            // json decode error
            if (json_last_error() !== JSON_ERROR_NONE) {
                // [パラメータが不正です。] message write to 'laravel.log'
                throw new \Exception(__('messages.error.invalid_parameter'));
            }

            // to check batch json parameter
            if (!empty(array_diff($paramKey, array_keys($searchInfo)))) {
                // [パラメータが不正です。] message write to 'laravel.log'
                throw new \Exception(__('messages.error.invalid_parameter'));
            }

            /**
             * [共通処理] 開始処理
             * バッチ実行指示テーブル（t_execute_batch_instruction ）から該当バッチの取得と開始処理
             * t_execute_batch_instruction_id より該当バッチを取得し以下の情報を書き込む
             * - バッチ開始時刻
             */
            $batchExecute = $this->startBatchExecute->execute(null, [
                'm_account_id' => 1, // 企業アカウントID
                'execute_batch_type' => BatchListEnum::EXPCSV_SHIPMENT_BY_DATE_AND_PRODUCT->value, // BatchListEnum から取得
                'execute_conditions' => $searchInfo
            ]);

            // account code from バッチ実行指示テーブル
            $accountCode = $batchExecute->account_cd;

            // 環境チェック
            if (app()->environment('testing')) {
                // テスト環境の場合
                TenantDatabaseManager::setTenantConnection($accountCode . '_db_testing');
            } else {
                // 本番環境の場合
                TenantDatabaseManager::setTenantConnection($accountCode . '_db');
            }
        } catch (Exception $e) {
            // Write the error message log in laravel.log file
            ModuleFailed::dispatch(__CLASS__, [$searchInfo], $e);
            return;
        }

        DB::beginTransaction();
        try {

            // 起動タイミングパラメータ 1 or 2
            $times = $searchInfo['times'];

            if ($times == null || !in_array($times, self::STARTUP_TIMING_LIST)) {
                // 'パラメータが不正です。'
                throw new \Exception(__('messages.error.invalid_parameter'), self::PRIVATE_THROW_ERR_CODE);
            }

            // 日別商品別受注データを作成する。受注基本と受注明細からデータを抽出する。
            $orderDestOutput = $this->getOrderDestiData();

            // check orderDestOutput array exist or not
            if (count($orderDestOutput) === self::EMPTY_DATA_ROW_COUNT) {
                // [出力対象のデータがありませんでした。] message save to 'execute_result'
                $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                    'execute_result' =>  __('messages.error.data_not_found2', ['datatype' => '出力']),
                    'execute_status' => BatchExecuteStatusEnum::SUCCESS->value
                ]);

                DB::commit();
                return ;
            }

            // 日別商品別出荷未出荷展開データを作成する。受注基本と受注明細SKUからデータを抽出する。
            $orderDtlSkuOutput = $this->getOrderDtlSkuData();

            // check orderDestOutput array exist or not
            if (count($orderDtlSkuOutput) === self::EMPTY_DATA_ROW_COUNT) {
                // [出力対象のデータがありませんでした。] message save to 'execute_result'
                $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                    'execute_result' =>  __('messages.error.data_not_found2', ['datatype' => '出力']),
                    'execute_status' => BatchExecuteStatusEnum::SUCCESS->value
                ]);

                DB::commit();
                return ;
            }

            // 日別商品別出荷未出荷データと日別商品別出荷未出荷展開データの抽出
            $orderDataArray = $this->generateDailyOrderAndSkuReport($times, $batchExecute, $orderDestOutput, $orderDtlSkuOutput);

            /**
             * [共通処理] 終了処理
             * バッチ実行指示テーブルの該当バッチに以下の情報を書き込む
             * - バッチ終了時刻
             * - (実行状態)
             * - (実行結果)
             * - (格納ファイルパス)
             */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => __('messages.info.notice_output_count', ['count' => "日別商品別出荷未出荷が{$orderDataArray['outputCount']['orderDestOutput']}"]) . (__('messages.info.notice_output_count', ['count' => "日別商品別出荷未出荷展開が{$orderDataArray['outputCount']['orderDtlSkuOutput']}"])), // [日別商品別出荷未出荷が〇〇件出力しました。日別商品別出荷未出荷展開が〇〇件出力しました。] message save to 'execute_result'
                'execute_status' => BatchExecuteStatusEnum::SUCCESS->value,
                'file_path' => $orderDataArray['fileSavePath'],
            ]);

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();

            // write the log error in laravel.log for error message
            Log::error('error_message : ' . $e->getMessage());

            /**
             * [共通処理] エラー時の処理
             * バッチ実行指示テーブルの該当バッチに以下の情報を書き込む
             * - バッチ終了時刻
             * - (実行状態)
             * - (実行結果)
             */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => ($e->getCode() == self::PRIVATE_THROW_ERR_CODE) ? $e->getMessage() : BatchExecuteStatusEnum::FAILURE->label(),
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value,
            ]);
        }
    }

    /**
     * generate daily order destination and sku data
     *
     * This method will do csv file, csv file save on s3, and mail sending process
     * This method will be used by 日別商品別出荷未出荷データの抽出 and 日別商品別出荷未出荷展開データの抽出.
     *
     * @param int $times   from parameter
     *        Laravel Eloquent Model Instance $batchExecute     from batchExecute
     *        array $orderDestOutput  from query array
     *        array $orderDtlSkuOutput  from query array
     *
     * @return array   $query  to create csv file
     */
    private function generateDailyOrderAndSkuReport($times, $batchExecute, $orderDestOutput, $orderDtlSkuOutput)
    {
        // Arrayからcsv形式に変換する
        // Temporarily save on local
        $desCsvFilePath = $this->convertToCsvAndSaveTmp("日別商品別出荷未出荷{$times}.csv", $orderDestOutput);
        $dtlSkuCsvFilePath = $this->convertToCsvAndSaveTmp("日別商品別出荷未出荷展開{$times}.csv", $orderDtlSkuOutput);

        // Create the zip file
        $tempZipPath = $this->createZipFile($batchExecute->t_execute_batch_instruction_id, $desCsvFilePath, $dtlSkuCsvFilePath);

        // Save on S3
        $fileSavePath = $this->saveZipOnS3($batchExecute->account_cd, $batchExecute->execute_batch_type, $batchExecute->t_execute_batch_instruction_id, $tempZipPath);

        // Download file from S3 to local
        $tempLocalPath = $this->downloadFileFromS3($fileSavePath);

        // Check the temp file exist or not
        if (!file_exists($tempLocalPath)) {
            // if not exist file
            throw new \Exception("Failed to save local file.");
        }

        // ZipArchive instance
        $zip = new ZipArchive();

        // Zip file open
        $zip->open($tempLocalPath);

        $attachFileArr = [];
        // Loop the files inside the zip
        for ($i = 0; $i < $zip->numFiles; $i++) {
            // Temp file path to extract the zip file
            $extractPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $batchExecute->t_execute_batch_instruction_id;

            // Extract files
            $zip->extractTo($extractPath);

            $attachFileArr[] = $extractPath . DIRECTORY_SEPARATOR . $zip->getNameIndex($i);
        }

        // 各CSVファイルにつき、メールを送信する
        $this->sendMail($attachFileArr);

        // Zip file close
        $zip->close();

        // Delete folder and its contents
        File::deleteDirectory($extractPath);

        // Delete the local file
        unlink($tempLocalPath);

        // Return the count of two output and s3 file path
        return [
            'outputCount' => [
                'orderDestOutput' => count($orderDestOutput), // 日別商品別出荷未出荷データ csv file array count
                'orderDtlSkuOutput' => count($orderDtlSkuOutput) // 日別商品別出荷未出荷展開データ csv file array count
            ],
            'fileSavePath' => $fileSavePath, // s3 file path
        ];
    }

    /**
     * Create the csv file
     *
     * This method create file based on data array that from getOrderDtlData func: and getOrderDtlSkuData func:
     * The given file path and data array.
     *
     * @param  string   $orderCsvFileName  file name
     *         array   $data  to create file with comma
     *
     * @return string   $csvFilePath  csv format file
     */
    protected function convertToCsvAndSaveTmp($orderCsvFileName, $data)
    {
        // create temp csv file
        $csvFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $orderCsvFileName;

        // header
        $csvContent = "出荷予定日,商品コード,商品名,受注数量合計,受注金額合計,未出荷数量合計,未出荷金額合計,出荷済数量合計,出荷済金額合計\n";
        foreach ($data as $row) {
            // prepare array to create csv file
            $csvRow = [
                $row['deli_plan_date'],
                $row['sell_cd'],
                $row['sell_name'],
                $row['total_order_sell_vol'],
                (int) $row['total_order_sell_price'],
                (int) $row['total_unshipped_order_vol'],
                (int) $row['unshipped_amount'],
                (int) $row['shipped_amount'],
                (int) $row['total_shipped_amount'],
            ];
            // to create the csv file
            $csvContent .= implode(",", $csvRow) . "\n";
        }

        // Save content to the temporary file
        file_put_contents($csvFilePath, $csvContent);

        // Now the file is saved in the temp directory with the specified name
        return $csvFilePath;
    }

    /**
    * Create the zip file on local
    *
    * This method create zip file path
    *
    * @param  int   $batchId  batch execution Id
    *         string   $desCsvFilePath  csv file path
    *         string   $dtlSkuCsvFilePath  csv file path
    *
    * @return string   $tempZipPath temporary file saving path
    */
    private function createZipFile($batchId, $desCsvFilePath, $dtlSkuCsvFilePath)
    {
        // Open the CSV files from local storage
        $desCsvContent = file_get_contents($desCsvFilePath);
        $dtlSkuCsvContent = file_get_contents($dtlSkuCsvFilePath);

        // ZipArchive instance
        $zip = new ZipArchive();

        // Create tempory zip file path
        $tempZipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $batchId . '.zip'; // create temp zip file

        // Check zip file can open or not
        if ($zip->open($tempZipPath, ZipArchive::CREATE) !== true) {
            throw new Exception("Failed to open the ZIP file.");
        }

        // Add CSV files to the ZIP archive
        $zip->addFromString(basename($desCsvFilePath), $desCsvContent); // Add the desCsvContent CSV file
        $zip->addFromString(basename($dtlSkuCsvFilePath), $dtlSkuCsvContent); // Add the dtlSkuCsvContent CSV file

        // Close the ZIP archive
        $zip->close();

        // Clear the file path
        unlink($desCsvFilePath);
        unlink($dtlSkuCsvFilePath);

        // Return temporary saving path
        return $tempZipPath;
    }

    /**
     * Save the csv file on s3 server
     *
     * This method create file path and save the file on s3 server
     *
     * @param  string   $accountCode  company account code
     *         string   $batchType  batchType
     *         string   $batchId  batchId
     *         string   $tempZipPath  temporary file path
     *
     * @return string   $fileSavePath   s3 file path
     */
    private function saveZipOnS3($accountCode, $batchType, $batchId, $tempZipPath)
    {
        // Read zip file path
        $zipFileContent = file_get_contents($tempZipPath);

        // Get file path to save on s3 server
        $fileSavePath =  $this->getZipExportFilePath->execute($accountCode, $batchType, $batchId);

        // Save on S3
        $zipFileSaveOnS3 = Storage::disk($this->disk)->put($fileSavePath, $zipFileContent);

        // Check the file saving process is success or fail
        if (!$zipFileSaveOnS3) {
            // if the file fails to be saved, it will also terminate abnormally (the return value of put() should be false).
            throw new Exception(__('messages.error.upload_s3_failed'), self::PRIVATE_THROW_ERR_CODE);
        }

        // Clear temp file path
        unlink($tempZipPath);

        // Return s3 file path
        return $fileSavePath;
    }

    /**
     * 日別商品別受注データを作成する。受注基本と受注明細からデータを抽出する。
     *
     * This method retrieve data based on 受注配送先.出荷予定日:当日±60日前（121日間）
     * The  出荷予定日 format should be 'Y-m-d'.
     *
     * グルーピング項目 : 受注配送先.出荷予定日, 受注明細.出荷予定日, 受注明細.販売商品名
     *
     * retrieve data -
     * 受注数量合計         sum(受注明細.受注数量)
     * 受注金額合計         sum(受注明細.販売単価×受注明細.受注数量）
     * 未出荷数量合計       出荷明細.検品日がNULLもしくは、受注基本.進捗区分 < 出荷中  sum(受注明細.受注数量)
     * 未出荷金額合計       出荷明細.検品日がNULLもしくは、受注基本.進捗区分 < 出荷中  sum(受注明細.販売単価×受注明細.受注数量）
     * 出荷済数量合計       出荷明細.検品日がNULLでない   sum(受注明細.受注数量)
     * 出荷済金額合計       出荷明細.検品日がNULLでない   sum(受注明細.販売単価×受注明細.受注数量）
     *
     *
     * @return array   $calculatedData  to create csv file
     */
    private function getOrderDestiData()
    {
        // Define the date range for filtering orders (60 days in the past and 60 days in the future)
        $startDate = Carbon::now()->subDays(self::SEARCH_RANGE_DAYS)->format('Y-m-d'); // previous 60 days ago
        $endDate = Carbon::now()->addDays(self::SEARCH_RANGE_DAYS)->format('Y-m-d'); // 60 days ahead

        // Fetch OrderDestinationModel with related data from orderDtls, deliveryDtl, and orderHdr
        $orderDestinations = OrderDestinationModel::with([
            'orderDtl' => function ($query) {
                // Fetch only the relevant fields from the orderDtls table
                $query->select('t_order_destination_id', 't_order_dtl_id', 'sell_cd', 'sell_name', 'order_sell_vol', 'order_sell_price');
            },
            'deliveryDtl' => function ($query) {
                // Fetch only the relevant fields from the deliveryDtl table
                $query->select('t_delivery_dtl_id', 't_order_dtl_id', 't_order_destination_id', 'deli_decision_date');
            },
             'orderHdr' => function ($query) {
                 // Fetch the progress type for orderHdr to determine order status
                 $query->select('t_order_hdr_id', 'progress_type');
             }
        ])
        ->select(['t_order_destination_id', 'deli_plan_date', 't_order_hdr_id']) // Fetch fields from the main table
        ->whereDate('t_order_destination.deli_plan_date', '>=', $startDate) // Filter by startDate
        ->whereDate('t_order_destination.deli_plan_date', '<=', $endDate) // Filter by endDate
        ->get();

        // Group the data by deli_plan_date, sell_cd, and sell_name
        // Flatten the orderDestinations collection and process each orderDestination
        $groupedData = $orderDestinations->flatMap(function ($orderDestination) {
            // Create an orderDestination array including delivery plan date and progress type
            $orderDestinationArr = [
                'orderDestination' => $orderDestination, // orderDestinations collection
                'deli_plan_date' => $orderDestination->deli_plan_date, // 受注配送先.出荷予定日
                'progress_type' => optional($orderDestination->orderHdr)->progress_type, // 受注基本.進捗区分
            ];

            // Map each order destination to its related orderDtl records
            return $orderDestinationArr['orderDestination']->orderDtl->map(function ($orderDtl) use ($orderDestinationArr) {
                // Create an array for each orderDtl, adding the relevant orderDestination
                $orderDtlArr = [
                    'deli_plan_date' => $orderDestinationArr['deli_plan_date'], // Assign delivery plan date from orderDestination array
                    'progress_type' => $orderDestinationArr['progress_type'], // Assign progress_type date from orderDestination array
                    'sell_cd' => $orderDtl->sell_cd, // 受注明細.販売コード
                    'sell_name' => $orderDtl->sell_name, // 受注明細.販売商品名
                    'order_sell_vol' => $orderDtl->order_sell_vol, // 受注明細.受注数量
                    'order_sell_price' => $orderDtl->order_sell_price, // 受注明細.受注数量
                ];

                // Map each orderDestination to its related deliveryDtl records
                return $orderDestinationArr['orderDestination']->deliveryDtl->map(function ($deliveryDtl) use ($orderDtlArr) {
                    // Combine the data from orderDestination, orderDtl, and deliveryDtl into a single structure
                    return [
                        'deli_plan_date' => $orderDtlArr['deli_plan_date'], // 受注配送先.出荷予定日
                        'sell_cd' => $orderDtlArr['sell_cd'], // 受注明細.販売コード
                        'sell_name' => $orderDtlArr['sell_name'], // 受注明細.販売商品名
                        'order_sell_vol' => $orderDtlArr['order_sell_vol'], // 受注明細.受注数量
                        'order_sell_price' => $orderDtlArr['order_sell_price'], // 受注明細.受注数量
                        'deli_decision_date' => $deliveryDtl->deli_decision_date, // 出荷明細.検品日
                        'progress_type' => $orderDtlArr['progress_type'], // 受注基本.進捗区分
                    ];
                });
            });
        })->flatMap(function ($collection) {
            // Flatten the collection after mapping and create a single-level collection
            return $collection;
        })->groupBy(function ($item) {
            // Group by combination of deli_plan_date, sell_cd, and sell_name
            return $item['deli_plan_date'] . '-' . $item['sell_cd'] . '-' . $item['sell_name'];
        });

        // Calculate totals for each group
        $calculatedData = $groupedData->map(function ($group) {
            // Calculate 受注数量合計 for this group
            $totalOrderSellVol = $group->sum('order_sell_vol');

            // Calculate 受注金額合計 for this group
            $totalOrderSellPrice = $group->sum(function ($item) {
                return $item['order_sell_price'] * $item['order_sell_vol'];
            });

            // Calculate 未出荷数量合計 for this group
            $totalUnshippedOrderVol = $group->filter(function ($item) {
                return is_null($item['deli_decision_date']) || $item['progress_type'] < ProgressTypeEnum::Shipping->value;
            })->sum('order_sell_vol');

            // Calculate 未出荷金額合計 for this group
            $unshippedAmount = $group->filter(function ($item) {
                return is_null($item['deli_decision_date']) || $item['progress_type'] < ProgressTypeEnum::Shipping->value;
            })->sum(function ($item) {
                return $item['order_sell_price'] * $item['order_sell_vol'];
            });

            // Calculate 出荷済数量合計 for this group
            $shippedAmount = $group->filter(function ($item) {
                return !is_null($item['deli_decision_date']);
            })->sum('order_sell_vol');

            // Calculate 出荷済金額合計 for this group
            $totalShippedAmount = $group->filter(function ($item) {
                return !is_null($item['deli_decision_date']);
            })->sum(function ($item) {
                return $item['order_sell_price'] * $item['order_sell_vol'];
            });

            // Return the calculated values for this group
            return [
                'deli_plan_date' => Carbon::parse($group->pluck('deli_plan_date')->first())->format('Ymd'), // 出荷予定日
                'sell_cd' => $group->pluck('sell_cd')->first(), // 商品コード
                'sell_name' => $group->pluck('sell_name')->first(), // 商品名
                'total_order_sell_vol' => $totalOrderSellVol, // 受注数量合計
                'total_order_sell_price' => $totalOrderSellPrice, // 受注金額合計
                'total_unshipped_order_vol' => $totalUnshippedOrderVol, // 未出荷数量合計
                'unshipped_amount' => $unshippedAmount, // 未出荷金額合計
                'shipped_amount' => $shippedAmount, // 出荷済数量合計
                'total_shipped_amount' => $totalShippedAmount, // 出荷済金額合計
            ];
        })->values(); // Reindex the array numerically starting from 0

        // Return the calculated data
        return $calculatedData;
    }

    /**
     * 日別商品別受注データを作成する。受注基本と受注明細からデータを抽出する。
     *
     * This method retrieve data based on 受注配送先.出荷予定日:当日±60日前（121日間）
     * The  出荷予定日 format should be 'Y-m-d'.
     *
     * グルーピング項目 : 受注配送先.出荷予定日, 受注明細SKU.商品コード, SKUマスタ.SKU名
     *
     * retrieve data -
     * 受注数量合計         sum(受注明細SKU.受注数量)
     * 受注金額合計         sum(受注明細SKU.受注数量×基本販売単価）
     * 未出荷数量合計       出荷明細.検品日がNULLもしくは、受注基本.進捗区分 < 出荷中  sum(受注明細SKU.受注数量)
     * 未出荷金額合計       出荷明細.検品日がNULLもしくは、受注基本.進捗区分 < 出荷中  sum(受注明細SKU.受注数量×基本販売単価)
     * 出荷済数量合計       出荷明細.検品日がNULLでない   sum(受注明細SKU.受注数量)
     * 出荷済金額合計       出荷明細.検品日がNULLでない   sum(受注明細SKU.受注数量×基本販売単価)
     *
     *
     * @return array   $calculatedData  to create csv file
     */
    private function getOrderDtlSkuData()
    {
        // Define the date range for filtering orders (60 days in the past and 60 days in the future)
        $startDate = Carbon::now()->subDays(self::SEARCH_RANGE_DAYS)->format('Y-m-d'); // previous 60 days ago
        $endDate = Carbon::now()->addDays(self::SEARCH_RANGE_DAYS)->format('Y-m-d'); // 60 days ahead

        // Fetch OrderDestinationModel with related data from orderDtlSku, deliveryDtl, orderHdr and orderDtlSku.amiSku
        $orderDestinations = OrderDestinationModel::with([
            'orderDtlSku' => function ($query) {
                // Fetch only the relevant fields from the orderDtls table
                $query->select('t_order_destination_id', 't_order_dtl_sku_id', 'item_id', 'order_sell_vol', 'item_cd');
            },
            'deliveryDtl' => function ($query) {
                // Fetch only the relevant fields from the deliveryDtl table
                $query->select('t_delivery_dtl_id', 't_order_destination_id', 'deli_decision_date');
            },
            'orderHdr' => function ($query) {
                // Fetch the progress type for orderHdr to determine order status
                $query->select('t_order_hdr_id', 'progress_type');
            },
            'orderDtlSku.amiSku' => function ($query) {
                // Fetch only the relevant fields from the m_ami_sku table
                $query->select('m_ami_sku_id', 'sku_name', 'sales_price');
            },
        ])
        ->select(['t_order_destination_id', 'deli_plan_date', 't_order_hdr_id']) // Fetch fields from the main table
        ->whereDate('t_order_destination.deli_plan_date', '>=', $startDate) // Filter by startDate
        ->whereDate('t_order_destination.deli_plan_date', '<=', $endDate) // Filter by endDate
        ->get();

        // Group the data by deli_plan_date, item_id, and sku_name
        $groupedData = $orderDestinations->flatMap(function ($orderDestination) {
            // Create an orderDestination array including delivery plan date and progress type
            $orderDestinationArr = [
                'orderDestination' => $orderDestination, // orderDestination collection
                'deli_plan_date' => $orderDestination->deli_plan_date, // 受注配送先.出荷予定日
                'progress_type' => optional($orderDestination->orderHdr)->progress_type, // 受注基本.進捗区分
            ];
            // Map each order destination to its related orderDtlSku records
            return $orderDestination->orderDtlSku->map(function ($orderDtlSku) use ($orderDestinationArr) {
                // Create an array for each orderDtl, adding the relevant orderDestination
                $orderDtlSkuArr = [
                    'deli_plan_date' => $orderDestinationArr['deli_plan_date'], // 受注配送先.出荷予定日
                    'progress_type' => $orderDestinationArr['progress_type'], // 受注基本.進捗区分
                    'item_id' => $orderDtlSku->item_id, // 受注明細SKU.SKU ID
                    'item_cd' => $orderDtlSku->item_cd, // 受注明細SKU.SKU CD
                    'sku_name' => $orderDtlSku->amiSku->sku_name, // SKUマスタ.SKU名
                    'order_sell_vol' => $orderDtlSku->order_sell_vol, // 受注明細SKU.受注数量
                    'sales_price' => $orderDtlSku->amiSku->sales_price, // SKUマスタ.基本販売単価
                ];

                // Map each orderDestination to its related deliveryDtl records
                return $orderDestinationArr['orderDestination']->deliveryDtl->map(function ($deliveryDtl) use ($orderDtlSkuArr) {
                    // Combine data from orderDestination and orderDtls into a single structure
                    return [
                        'deli_plan_date' => $orderDtlSkuArr['deli_plan_date'], // 受注配送先.出荷予定日
                        'item_id' => $orderDtlSkuArr['item_id'], // 受注明細SKU.SKU ID
                        'item_cd' => $orderDtlSkuArr['item_cd'], // 受注明細SKU.SKU CD
                        'sku_name' => $orderDtlSkuArr['sku_name'], // SKUマスタ.SKU名
                        'order_sell_vol' => $orderDtlSkuArr['order_sell_vol'], // 受注明細SKU.受注数量
                        'sales_price' => $orderDtlSkuArr['sales_price'], // SKUマスタ.基本販売単価
                        'deli_decision_date' => $deliveryDtl->deli_decision_date, // 出荷明細.検品日
                        'progress_type' => $orderDtlSkuArr['progress_type'], // 受注基本.進捗区分
                    ];
                });
            });
        })->flatMap(function ($collection) {
            // Flatten the collection after mapping and create a single-level collection
            return $collection;
        })
        ->groupBy(function ($item) {
            // Group by combination of deli_plan_date, item_cd, and sku_name
            return $item['deli_plan_date'] . '-' . $item['item_cd'] . '-' . $item['sku_name'];
        });
        // Calculate totals for each group
        $calculatedData = $groupedData->map(function ($group) {
            // Calculate 受注数量合計 for this group
            $totalOrderSellVol = $group->sum('order_sell_vol');

            // Calculate 受注金額合計 for this group
            $totalOrderSellPrice = $group->sum(function ($item) {
                return $item['order_sell_vol'] * $item['sales_price'];
            });

            // Calculate 未出荷数量合計 for this group
            $totalUnshippedOrderVol = $group->filter(function ($item) {
                return is_null($item['deli_decision_date']) || $item['progress_type'] < ProgressTypeEnum::Shipping->value;
            })->sum('order_sell_vol');

            // Calculate 未出荷金額合計 for this group
            $unshippedAmount = $group->filter(function ($item) {
                return is_null($item['deli_decision_date']) || $item['progress_type'] < ProgressTypeEnum::Shipping->value;
            })->sum(function ($item) {
                return $item['order_sell_vol'] * $item['sales_price'];
            });

            // Calculate 出荷済数量合計 for this group
            $shippedAmount = $group->filter(function ($item) {
                return !is_null($item['deli_decision_date']);
            })->sum('order_sell_vol');

            // Calculate 出荷済金額合計 for this group
            $totalShippedAmount = $group->filter(function ($item) {
                return !is_null($item['deli_decision_date']);
            })->sum(function ($item) {
                return $item['order_sell_vol'] * $item['sales_price'];
            });

            // Return the calculated values for this group
            return [
                'deli_plan_date' => Carbon::parse($group->pluck('deli_plan_date')->first())->format('Ymd'), // 出荷予定日
                'sell_cd' => $group->pluck('item_cd')->first(), // 商品コード
                'sell_name' => $group->pluck('sku_name')->first(), // 商品名
                'total_order_sell_vol' => $totalOrderSellVol, // 受注数量合計
                'total_order_sell_price' => $totalOrderSellPrice, // 受注金額合計
                'total_unshipped_order_vol' => $totalUnshippedOrderVol, // 未出荷数量合計
                'unshipped_amount' => $unshippedAmount, // 未出荷金額合計
                'shipped_amount' => $shippedAmount, // 出荷済数量合計
                'total_shipped_amount' => $totalShippedAmount, // 出荷済金額合計
            ];
        })->values(); // Reindex the array numerically starting from 0

        // Return the calculated data
        return $calculatedData;

    }

    /**
     * Save the csv file on s3 server
     *
     * This method create file path and save the file on s3 server
     *
     * @param  string   $accountCode  company account code
     *         string   $batchType  batchType
     *         string   $fileName  batchId_fileName
     *         string   $csvContent  data content with csv format
     *
     * @return string   $fileSavePath file saving path
     */
    private function saveCsvOnS3($accountCode, $batchType, $fileName, $csvContent)
    {
        // Get file path to save on s3 server
        $fileSavePath =  $this->getZipExportFilePath->execute($accountCode, $batchType, $fileName);

        // Save on S3
        $fileSaveOnS3 = Storage::disk($this->disk)->put($fileSavePath, $csvContent);

        // Check the file saving process is success or fail
        if (!$fileSaveOnS3) {
            // if the file fails to be saved, it will also terminate abnormally (the return value of put() should be false).
            throw new Exception(__('messages.error.upload_s3_failed'), self::PRIVATE_THROW_ERR_CODE);
        }
        return $fileSavePath;
    }

    /**
     * downlaod the csv file from s3 server
     *
     * This method downlaod the file from s3 and then save into the created local path
     *
     *
     * @param  string   $filePath  s3 server file path
     *
     *
     * @return string   $localPath file saving path
     */
    public function downloadFileFromS3($filePath)
    {
        // Open a stream to read the file from the S3 storage disk
        $stream = Storage::disk($this->disk)->readStream($filePath);

        // check file have or not
        if (!$stream) {
            // [日別商品別出荷未出荷ファイルが見つかりません。（:filePath] message save to 'execute_result'
            throw new Exception(__('messages.error.file_not_found', ['file' => '日別商品別出荷未出荷', 'path' => $filePath]), self::PRIVATE_THROW_ERR_CODE);
        }

        // Define the local path to temporarily save the file in the system's temporary directory
        $localPath = sys_get_temp_dir() . '/' . basename($filePath);

        // Open or create a local file at the specified path in write mode ('w+')
        // If the file doesn't exist, it will be created; if it does exist, it will be truncated
        $localFile = fopen($localPath, 'w+');

        // Check if the file handle was successfully created
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

    /**
     * 作成したCSVをそれぞれメール送信する。
     * 原田設定テーブルより送信先のメールアドレスを取得する。
     * 各CSVファイルにつき、メールを送信する。
     *
     * This method sends an email when a file is extracted.
     * The given process string.
     *
     * @param  string   $process  file name
     *
     * Used try/catch
     * If mail sending is something wrong, goto the main function(handle) catch and save to execute_result
     * after sending mail, remove temporary file
     */
    private function sendMail($filePathArr)
    {

        try {
            // 原田設定テーブルより送信先のメールアドレスを取得する。
            $getMailData = ShopGfhModel::query()
                ->select('mail_address_sales_dept as to_mail', 'mail_address_from as from_mail')
                ->orderBy('m_shop_gfh_id', 'desc')
                ->first();

            if ($getMailData) {
                $emailStr = $getMailData['to_mail']; // get receiver email address
                $fromEmail = $getMailData['from_mail']; // get sender email address
                // Validate email are valid or not and transform string to array
                $toEmail = explode(",", $emailStr);

                // check toEmail addresses
                foreach ($toEmail as $email) {
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception(__('messages.error.process_failed', ['process' => 'メール送信処理']));
                    }
                }

                // check fromEmail address
                if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception(__('messages.error.process_failed', ['process' => 'メール送信処理']));
                }

                // check mail server configuration
                $mailerConfig = config('mail.mailers.smtp');
                if (empty($mailerConfig['host']) || empty($mailerConfig['port']) || empty($mailerConfig['username']) || empty($mailerConfig['password'])) {
                    throw new Exception(__('messages.error.process_failed', ['process' => 'メール送信処理']));
                }

                $mailSubject = "【通販　出荷未出荷データ】"; // for mail subject
                $mailContent = ""; // for mail content

                // テンプレートファイルを使用してメールを送信する
                Mail::to($toEmail)->send(new ShipmentByDateAndProductOutMail($fromEmail, $mailSubject, $mailContent, $filePathArr));
            } else {
                Log::error(__('messages.error.batch_error.data_not_found3', ['data' => 'メールアドレス']));
            }
        } catch (Exception $e) {
            // write the error in laravel.log file
            log::Error($e->getMessage());
            throw new Exception($e->getMessage(), self::PRIVATE_THROW_ERR_CODE);
        }
    }
}
