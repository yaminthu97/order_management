<?php

namespace App\Console\Commands\Goto;

use App\Enums\BatchExecuteStatusEnum;
use App\Events\ModuleFailed;
use App\Mail\OrderByDateAndProductOutMail;
use App\Models\Master\Base\ShopGfhModel;
use App\Models\Order\Gfh1207\OrderHdrModel;
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

class OrderByDateAndProductOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:OrderByDateAndProductOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '日別の商品別の受注数量と金額を抽出し、CSVファイルに保存する。 CSVファイルを所定のメールアドレスへ送信する。';

    // バッチ名
    protected $batchName = '日別商品別受注バッチ';

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
    private const PRIVATE_THROW_ERR_CODE = -1;
    private const STARTUP_TIMING_LIST = [1, 2];
    private const SEARCH_RANGE_DAYS = 60;
    private const EMPTY_DATA_ROW_COUNT = 0;  // specify empty value
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
                'm_account_id' => $searchInfo['m_account_id'], // 企業アカウントID
                'execute_batch_type' => BatchListEnum::EXPCSV_ORDER_BY_DATE_AND_PRODUCT->value, // BatchListEnum から取得
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
            $orderDtlOutput = $this->getOrderDtlData();

            // check orderDtlOutput array exist or not
            if (count($orderDtlOutput) === self::EMPTY_DATA_ROW_COUNT) {
                // [出力対象のデータがありませんでした。] message save to 'execute_result'
                $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                    'execute_result' =>  __('messages.error.data_not_found2', ['datatype' => '出力']),
                    'execute_status' => BatchExecuteStatusEnum::SUCCESS->value
                ]);

                DB::commit();
                return ;
            }

            // 日別商品別受注展開データを作成する。受注基本と受注明細SKUからデータを抽出する。
            $orderDtlSkuOutput = $this->getOrderDtlSkuData();

            // check orderDtlSkuOutput array exist or not
            if (count($orderDtlSkuOutput) === self::EMPTY_DATA_ROW_COUNT) {
                // [出力対象のデータがありませんでした。] message save to 'execute_result'
                $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                    'execute_result' =>  __('messages.error.data_not_found2', ['datatype' => '出力']),
                    'execute_status' => BatchExecuteStatusEnum::SUCCESS->value
                ]);

                DB::commit();
                return ;
            }

            // 日別商品別受注データと日別商品別受注展開データの抽出
            $orderDataArray = $this->generateDailyOrderAndSkuReport($times, $batchExecute, $orderDtlOutput, $orderDtlSkuOutput);

            /**
             * [共通処理] 終了処理
             * バッチ実行指示テーブルの該当バッチに以下の情報を書き込む
             * - バッチ終了時刻
             * - (実行状態)
             * - (実行結果)
             * - (格納ファイルパス)
             */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => __('messages.info.notice_output_count', ['count' => "日別商品別受注が{$orderDataArray['outputCount']['orderDtlOutput']}"]) . (__('messages.info.notice_output_count', ['count' => "日別商品別受注展開が{$orderDataArray['outputCount']['orderDtlSkuOutput']}"])), // [日別商品別受注が〇〇件出力しました。日別商品別受注展開が〇〇件出力しました。] message save to 'execute_result'
                'file_path' => $orderDataArray['fileSavePath'],
                'execute_status' => BatchExecuteStatusEnum::SUCCESS->value,
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
     * 日別商品別受注データを作成する。受注基本と受注明細からデータを抽出する。
     * Fetches and processes order details data for a specific date range.
     *
     * This method retrieve data based on 受注日:当日～60日前（61日間）
     * The  受注日 format should be 'Y-m-d'.
     *
     * This function retrieves order header and related detail records, groups them by order date and sell code, and calculates aggregates such as the number of unique orders, total quantity, and total sales amount.
     *
     * グルーピング項目 : 受注基本.受注日, 受注明細.販売コード
     *
     * retrieve data -
     * 集計項目 : 受注件数, 受注数, 金額
     *
     * 受注件数:count(受注ID）重複除く
     * 受注数:sum(受注数量）
     * 金額:sum(受注数量×販売単価）
     *
     *
     * @return \Illuminate\Support\Collection $dataCalculating The grouped and calculated data to create csv file
     */
    private function getOrderDtlData()
    {
        // Define date range for the query (60 days back from today)
        $startDate = Carbon::now()->subDays(self::SEARCH_RANGE_DAYS)->format('Y-m-d'); // previous 60 day' s date
        $endDate = Carbon::now()->format('Y-m-d'); // current date

        // Fetch t_order_hdr along with their related t_order_dtl
        $orderHdrs = OrderHdrModel::with([
            'orderDtl' => function ($query) {
                // Specify fields to fetch from the t_order_dtl table
                $query->select('t_order_dtl_id', 't_order_hdr_id', 'sell_cd', 'order_sell_vol', 'order_sell_price');
            },
        ])
        ->select(['t_order_hdr_id', 'order_datetime']) // Specify fields to fetch from the t_order_hdr table
        ->whereDate('t_order_hdr.order_datetime', '>=', $startDate) // Filter by start date
        ->whereDate('t_order_hdr.order_datetime', '<=', $endDate) // Filter by end date
        ->get();

        // Flatten the t_order_hdr and related t_order_dtl into a single dataset
        $dataGrouping = $orderHdrs->flatMap(function ($orderHdr) {
            // Map each t_order_hdr to its related t_order_dtl records
            return $orderHdr->orderDtl->map(function ($orderDtl) use ($orderHdr) {

                // Combine relevant fields from both t_order_hdr and t_order_dtl records
                return [
                    'order_datetime' => $orderHdr->order_datetime, // 受注日
                    'sell_cd' => $orderDtl->sell_cd, // 販売コード
                    't_order_hdr_id' => $orderDtl->t_order_hdr_id, // 受注ID
                    'order_sell_vol' => $orderDtl->order_sell_vol, // 受注数量
                    'order_sell_price' => $orderDtl->order_sell_price // 販売単価
                ];
            });
        })->groupBy(function ($item) {
            // Group the data by a unique key combining order_datetime and sell_cd
            return Carbon::parse($item['order_datetime'])->format('Y-m-d') . '-' . $item['sell_cd'];
        });

        // Calculate aggregates (order_no, order_quantity, amt_of_money) for each group
        $dataCalculating = $dataGrouping->map(function ($group) {
            return [
                'order_datetime' => Carbon::parse($group->pluck('order_datetime')->first())->format('Ymd'), // 受注日付
                'sell_cd' => $group->pluck('sell_cd')->first(), // 商品コード
                'order_no' => $group->pluck('t_order_hdr_id')->unique()->count(), // count(受注ID）　重複除く
                'order_quantity' => $group->sum(function ($item) {
                    return $item['order_sell_vol']; // sum(受注数量）
                }),
                'amt_of_money' => $group->sum(function ($item) {
                    return $item['order_sell_vol'] * $item['order_sell_price']; // sum(受注数量×販売単価）
                })
            ];
        })->values(); // This will reindex the array numerically starting from 0

        // Return the calculated data
        return $dataCalculating;
    }

    /**
     * 日別商品別受注展開データを作成する。受注基本と受注明細SKUからデータを抽出する。
     *
     * This method retrieve data based on 受注日:当日～60日前（61日間）
     * The  受注日 format should be 'Y-m-d'.
     * グルーピング項目 : 受注基本.受注日, 受注明細.SKUコード
     *
     * retrieve data -
     * 集計項目 : 受注件数, 受注数, 金額
     *
     * 受注件数 : count(受注ID）重複除く
     * 受注数 : sum(受注数量）
     * 金額 : sum(受注数量×基本販売単価）
     *
     *
     * @return \Illuminate\Support\Collection $dataCalculating The grouped and calculated data to create csv file
     */
    private function getOrderDtlSkuData()
    {
        // Define date range for the query (60 days back from today)
        $startDate = Carbon::now()->subDays(self::SEARCH_RANGE_DAYS)->format('Y-m-d'); // previous 60 day' s date
        $endDate = Carbon::now()->format('Y-m-d'); // current date

        // Fetch t_order_hdr along with their related t_order_dtl' s related a_ami_sku
        $orderHdrs = OrderHdrModel::with([
            'orderDtlSku' => function ($query) {
                // Specify fields to fetch from the t_order_dtl_sku table
                $query->select('t_order_hdr_id', 't_order_dtl_sku_id', 'item_id', 'order_sell_vol', 'item_cd');
            },
            'orderDtlSku.amiSku' => function ($query) {
                // Specify fields to fetch from the m_ami_sku table
                $query->select('m_ami_sku_id', 'sku_name', 'sales_price');
            },
        ])
        ->select(['t_order_hdr_id', 'order_datetime']) // Specify fields to fetch from the t_order_hdr table
        ->whereDate('t_order_hdr.order_datetime', '>=', $startDate) // Filter by start date
        ->whereDate('t_order_hdr.order_datetime', '<=', $endDate) // Filter by end date
        ->get();

        // Flatten the t_order_hdr and related t_order_dtl and m_ami_sku into a single dataset
        $dataGrouping = $orderHdrs->flatMap(function ($orderHdr) {
            // Map each t_order_hdr to its related t_order_dtl and m_ami_sku records
            return $orderHdr->orderDtlSku->map(function ($orderDtlSku) use ($orderHdr) {

                // Combine data from t_order_hdr, t_order_dtl and m_ami_sku into a single structure
                return [
                    'order_datetime' => $orderHdr->order_datetime, // 受注日
                    'item_cd' => $orderDtlSku->item_cd, // 販売コード
                    't_order_hdr_id' => $orderDtlSku->t_order_hdr_id, // 受注ID
                    'order_sell_vol' => $orderDtlSku->order_sell_vol, // 受注数量
                    'sales_price' => $orderDtlSku->amiSku->sales_price // 基本販売単価
                ];
            });
        })->groupBy(function ($item) {
            // Group the data by a unique key combining order date and sell code
            return Carbon::parse($item['order_datetime'])->format('Y-m-d') . '-' . $item['item_cd'];
        });

        // Calculate aggregates (order_no, order_quantity, amt_of_money) for each group
        $dataCalculating = $dataGrouping->map(function ($group) {
            return [
                'order_datetime' => Carbon::parse($group->pluck('order_datetime')->first())->format('Ymd'), // 受注日付
                'sell_cd' => $group->pluck('item_cd')->first(), // 商品コード
                'order_no' => $group->pluck('t_order_hdr_id')->unique()->count(), // count(受注ID）　重複除く
                'order_quantity' => $group->sum(function ($item) {
                    return $item['order_sell_vol']; // sum(受注数量）
                }),
                'amt_of_money' => $group->sum(function ($item) {
                    return $item['order_sell_vol'] * $item['sales_price']; // sum(受注数量×基本販売単価）
                })
            ];
        })->values(); // This will reindex the array numerically starting from 0

        return $dataCalculating;
    }

    /**
     * generate daily order and sku data
     *
     * This method will do csv file, create zip file, zip file save on s3, download zip file from s3 and mail sending process
     * This method will be used by 日別商品別受注データの抽出 and 日別商品別受注展開データの抽出.
     *
     * @param int $times   from parameter
     *        Laravel Eloquent Model Instance $batchExecute     from batchExecute
     *        array $orderDtlOutput  from query array
     *        array $orderDtlSkuOutput  from query array
     *
     * @return array   The count of two output and s3 file path
     */
    private function generateDailyOrderAndSkuReport($times, $batchExecute, $orderDtlOutput, $orderDtlSkuOutput)
    {
        // Arrayからcsv形式に変換する
        // Temporarily save on local
        $dtlCsvFilePath = $this->convertToCsvAndSaveTmp("日別商品別受注{$times}.csv", $orderDtlOutput);
        $dtlSkuCsvFilePath = $this->convertToCsvAndSaveTmp("日別商品別受注展開{$times}.csv", $orderDtlSkuOutput);

        // Create the zip file
        $tempZipPath = $this->createZipFile($batchExecute->t_execute_batch_instruction_id, $dtlCsvFilePath, $dtlSkuCsvFilePath);

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
                'orderDtlOutput' => count($orderDtlOutput), // 日別商品別受注データ csv file array count
                'orderDtlSkuOutput' => count($orderDtlSkuOutput) // 日別商品別受注展開データ csv file array count
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
        $csvContent = "受注日付,商品コード,受注件数,受注数,金額\n";

        foreach ($data as $row) {
            // prepare array to create csv file
            $csvRow = [
                $row['order_datetime'],
                $row['sell_cd'],
                $row['order_no'],
                $row['order_quantity'],
                $row['amt_of_money'],
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
     *         string   $dtlCsvFilePath  csv file path
     *         string   $dtlSkuCsvFilePath  csv file path
     *
     * @return string   $tempZipPath temporary file saving path
     */
    private function createZipFile($batchId, $dtlCsvFilePath, $dtlSkuCsvFilePath)
    {
        // Open the CSV files from local storage
        $dtlCsvContent = file_get_contents($dtlCsvFilePath);
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
        $zip->addFromString(basename($dtlCsvFilePath), $dtlCsvContent); // Add the dtlCsvContent CSV file
        $zip->addFromString(basename($dtlSkuCsvFilePath), $dtlSkuCsvContent); // Add the dtlSkuCsvContent CSV file

        // Close the ZIP archive
        $zip->close();

        // Clear the file path
        unlink($dtlCsvFilePath);
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
            // [日別商品別受注ファイルが見つかりません。（:filePath] message save to 'execute_result'
            throw new Exception(__('messages.error.file_not_found', ['file' => '日別商品別受注', 'path' => $filePath]), self::PRIVATE_THROW_ERR_CODE);
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

        // Return temporarily save file path
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
                $emailStr = $getMailData['to_mail']; // get recipient email addresses
                $fromEmail = $getMailData['from_mail']; // get sender email address
                // Validate email are valid or not and transform string to array
                $toEmail = explode(",", $emailStr);

                // check toEmail address
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

                $mailSubject = "【通販　商品別受注データ】"; // for mail subject
                $mailContent = ""; // for mail content

                // テンプレートファイルを使用してメールを送信する
                Mail::to($toEmail)->send(new OrderByDateAndProductOutMail($fromEmail, $mailSubject, $mailContent, $filePathArr));
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
