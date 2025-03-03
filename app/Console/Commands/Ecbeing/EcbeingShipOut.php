<?php

namespace App\Console\Commands\Ecbeing;

use App\Enums\BatchExecuteStatusEnum;
use App\Models\Order\Gfh1207\ShippingLabelModel;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Common\Gfh1207\CheckBatchParameter;
use App\Modules\Order\Gfh1207\Enums\ShipNyukinExportTypeEnum;
use App\Modules\Order\Gfh1207\Enums\ShipNyukinRunTypeEnum;
use App\Modules\Order\Gfh1207\GetTsvExportFilePath;
use App\Modules\Order\Gfh1207\SendEcbeingShipData;
use App\Services\TenantDatabaseManager;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use TypeError;

class EcbeingShipOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:EcbeingShipOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'EC受注画面、出荷確定データ出力、入金・受注修正データ出力機能により 出荷確定データの出力を行う。パラメータによってはEcbeingAPIを利用し、出荷確定データの送信を行う。';

    // バッチ名
    protected $batchName = '出荷確定データ出力';

    // disk name
    protected $disk;

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    // 出荷確定データ取込と更新
    protected $sendEcbeingShipData;

    // export file path
    protected $getTsvExportFilePath;

    // parameter check
    protected $checkBatchParameter;

    // const variable
    private const PRIVATE_THROW_ERR_CODE = -1;
    private const TSV_START_LINE_NUM = 1;
    private const WEB_ORDER_NUM_MAX_BYTES = 30;
    private const DELI_NUM_MAX_DIGITS = 6;
    private const INVOICE_NUM_MAX_BYTES = 650;

    /**
     * Create a new command instance.
     */
    public function __construct(
        StartBatchExecuteInstructionInterface $startBatchExecute,
        EndBatchExecuteInstructionInterface $endBatchExecute,
        SendEcbeingShipData $sendEcbeingShipData,
        GetTsvExportFilePath $getTsvExportFilePath,
        CheckBatchParameter $checkBatchParameter,
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->sendEcbeingShipData = $sendEcbeingShipData;
        $this->getTsvExportFilePath = $getTsvExportFilePath;
        $this->checkBatchParameter = $checkBatchParameter;

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

            /**
             * [共通処理] 開始処理
             * バッチ実行指示テーブル（t_execute_batch_instruction ）から該当バッチの取得と開始処理
             * t_execute_batch_instruction_id より該当バッチを取得し以下の情報を書き込む
             * - バッチ開始時刻
             */
            $batchExecute = $this->startBatchExecute->execute($this->argument('t_execute_batch_instruction_id'));

            // account code
            $accountCode = $batchExecute->account_cd;

            if (app()->environment('testing')) {
                // テスト環境の場合
                TenantDatabaseManager::setTenantConnection($accountCode . '_db_testing');
            } else {
                TenantDatabaseManager::setTenantConnection($accountCode . '_db');
            }
        } catch (Exception $e) {
            // Write the log in laravel.log for error message
            Log::error('error_message : ' . $e->getMessage());
            return;
        }
        DB::beginTransaction();
        try {

            // required parameter
            $paramKey = ['type', 'export_type', 'inspection_date'];
            $checkResult = $this->checkBatchParameter->execute($this->argument('json'), $paramKey);  // to check batch json parameter

            if (!$checkResult) {
                // [パラメータが不正です。] message save to 'execute_result'
                throw new \Exception(__('messages.error.invalid_parameter'), self::PRIVATE_THROW_ERR_CODE);
            }

            // get json from parameter
            $searchInfo = json_decode($this->argument('json'), true)['search_info'];
            $type = $searchInfo['type']; // processing type
            $exportType = $searchInfo['export_type']; // export type
            $inspectionDate = $searchInfo['inspection_date']; // inspection date

            // 入力パラメータチェック
            $executeResult = '';
            if ($type == ShipNyukinRunTypeEnum::SEND->value) {
                // 「処理種別：送信のみ」は出荷確定データ出力バッチ実行対象外です。message save to 'execute_result'
                throw new Exception((__('messages.error.batch_error.not_match_param1', ['functype' => '処理種別：' . ShipNyukinRunTypeEnum::SEND->label(), 'batchname' => $this->batchName])), self::PRIVATE_THROW_ERR_CODE);

            } elseif ($exportType == null || !in_array(ShipNyukinExportTypeEnum::SHIP_EXPORT->value, $exportType)) {
                // 「出荷確定データ出力」がチェックされていない場合は、出荷確定データ出力バッチ実行対象外です。message save to 'execute_result'
                throw new Exception((__('messages.error.batch_error.not_match_param2', ['functype' => ShipNyukinExportTypeEnum::SHIP_EXPORT->label(), 'batchname' => $this->batchName])), self::PRIVATE_THROW_ERR_CODE);

            }

            // EC受注画面にて指定された条件を元に送り状実績からデータを抽出し作成する。
            $outputData = $this->retrieveData($inspectionDate);

            // Checking if there is data to create tsv file
            if (count($outputData) === 0) {
                // [出力対象のデータがありませんでした。] message save to 'execute_result'
                $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                    'execute_result' =>  __('messages.error.data_not_found2', ['datatype' => '出力']),
                    'execute_status' => BatchExecuteStatusEnum::SUCCESS->value
                ]);

                DB::commit();
                return ;
            }

            // 抽出したデータをtsvファイルに変換する
            $tsvContent = $this->tsvCreate($outputData);

            // Get file path to save on s3 server
            $fileSavePath =  $this->getTsvExportFilePath->execute($accountCode, $batchExecute->execute_batch_type, $this->argument('t_execute_batch_instruction_id'));

            // Save on S3
            $fileSaveOnS3 = Storage::disk($this->disk)->put($fileSavePath, $tsvContent);

            if (!$fileSaveOnS3) {
                // if the file fails to be saved, it will also terminate abnormally (the return value of put() should be false).
                throw new Exception(__('messages.error.upload_s3_failed'), self::PRIVATE_THROW_ERR_CODE);
            }


            // 入力パラメータチェック
            if ($type == ShipNyukinRunTypeEnum::EXECTUE_ALL->value) {
                /**
                 * [個別処理]出荷確定データ送信処理
                 * 下記のEcbeingAPIを利用して、出荷確定データの送信を行う。
                 * - 出荷確定データ取込API
                 * - 出荷確定データ更新API
                 */
                $this->sendEcbeingShipData->execute(['shipInputFile' => $fileSavePath, 'disk' => $this->disk]);
            }

            // [〇〇件の確認済み出荷データが処理されました。] message save to 'execute_result'
            $executeResult = (__('messages.info.notice_output_count', ['count' => count($outputData)]));
            /**
             * [共通処理] 終了処理
             * バッチ実行指示テーブルの該当バッチに以下の情報を書き込む
             * - バッチ終了時刻
             * - (実行状態)
             * - (実行結果)
             * - (格納ファイルパス)
             */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => $executeResult,
                'execute_status' => BatchExecuteStatusEnum::SUCCESS->value,
                'file_path' => $fileSavePath
            ]);

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();

            // Write the error message in laravel.log file
            Log::error('error_message : ' . $e->getMessage());

            // set fail result (異常)
            $executeResult = BatchExecuteStatusEnum::FAILURE->label();

            // Identify the error code for the error message
            if ($e->getCode() == self::PRIVATE_THROW_ERR_CODE) {
                $executeResult = $e->getMessage();
            }

            /**
             * [共通処理] エラー時の処理
             * バッチ実行指示テーブルの該当バッチに以下の情報を書き込む
             * - バッチ終了時刻
             * - (実行状態)
             * - (実行結果)
             */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => $executeResult,
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value,
            ]);
        }
    }

    /**
     * EC受注画面にて指定された条件を元に送り状実績からデータを抽出し作成する。
     *
     * This method retrieve data based on inspection date from parameter
     * The given inspectionDate date.
     * The inspectionDate format should be 'Y-m-d'.
     *
     * @param  date   $inspectionDate  to generate matching data with 送り状実績.出荷日
     * @return array   $outputData  to create tsv file
     */
    private function retrieveData($inspectionDate)
    {
        // If $inspectionDate is not null date format Y-m-d or null
        $date = $inspectionDate ? Carbon::parse($inspectionDate)->format('Y-m-d') : null;

        // Extracting the data
        $outputData = ShippingLabelModel::with(['deliHdr:t_deli_hdr_id,ec_order_num,order_destination_seq,order_total_price']) // Specify only necessary fields
            ->whereHas('deliHdr', function ($query) {
                $query->whereNotNull('ec_order_num'); // Check that ec_order_num is not null
            })
            ->when($date, function ($query) use ($date) {// If $date is null, no where condition is applied, and all records are retrieved.
                return $query->where('delivery_date', $date);
            })
            ->select(['t_shipping_label_id', 't_delivery_hdr_id', 'three_temperature_zone_type', 'shipping_label_number', 'delivery_date']) // Main table fields
            ->orderBy('t_shipping_label_id', 'asc') // Extract correct ordering before grouping
            ->get()
            ->groupBy('t_delivery_hdr_id') // Group records by t_delivery_hdr_id
            ->map(function ($items) {  // Extract the record with the minimum shipping_label_id
                $minShippingLabelId = $items->min('t_shipping_label_id'); // Find the minimum shipping_label_id
                return $items->firstWhere('t_shipping_label_id', $minShippingLabelId); // Select the record with the minimum shipping_label_id
            })
            ->toArray(); //Convert Results to Array

        return $outputData;

    }

    /**
     * Create the tsv file
     *
     * This method create file based on data array that from retrieveData func:
     * The given data array.
     * check validation the data array in this method [using func: of convertToHalfWidth, convertWithinMaxBytes, convertWithinMaxDigits, convertDateFormat]
     *
     * @param  array   $data  to create file with \t
     * @return file   $tsvContent  tsv format file
     */
    private function tsvCreate($data)
    {
        // tsv header(処理日付\tレコード件数)
        $tsvContent = Carbon::now()->format('Ymd') . "\t" . count($data) . "\n";
        $lineNo = self::TSV_START_LINE_NUM; // number for 行番号

        // prepare to convert from esm to ecbeing temperature zone
        $mapEsmToEcbeing = [
            0 => 0, // 室温 -> 室温
            1 => 2, // 冷凍 -> 冷蔵
            2 => 1, // 冷蔵 -> 冷凍
        ];
        foreach ($data as $key => $row) {
            // WEB受注NO.
            $webOrderNo = $this->convertToHalfWidth($row['deli_hdr']['ec_order_num']);
            $webOrderNo = $this->convertWithinMaxBytes($webOrderNo, self::WEB_ORDER_NUM_MAX_BYTES);

            // order_destination_seq
            $destiSeq = $row['deli_hdr']['order_destination_seq'];

            // クール区分
            $temperatureZoneType = $mapEsmToEcbeing[$row['three_temperature_zone_type']];

            // 届出番号
            $deliNumber = $this->convertWithinMaxDigits($destiSeq . $temperatureZoneType, self::DELI_NUM_MAX_DIGITS);

            // 伝票番号
            $invoiceNumber = $this->convertWithinMaxBytes($row['shipping_label_number'], self::INVOICE_NUM_MAX_BYTES);

            // 出荷日
            $deliDate = $this->convertDateFormat($row['delivery_date'], 'Ymd');

            // 注文金額合計
            $orderTotAmt = $row['deli_hdr']['order_total_price'];

            // prepare array to create tsv file
            $tsvRow = [
                $lineNo,
                $webOrderNo,
                $deliNumber,
                $temperatureZoneType,
                $invoiceNumber,
                $deliDate,
                $orderTotAmt
            ];

            // to create the tsv file
            $tsvContent .= implode("\t", $tsvRow) . "\n";

            $lineNo++;
        }

        return $tsvContent;
    }

    /**
     * Convert to Half-Width value
     *
     * This method convert from full-width to half-width value
     * The given value string.
     * Convert full-width to half-width and if something wrong(TypeError, normal error), goto handle function' s catch case
     * TypeError    Handle TypeError (invalid type such as array instead of string)
     * Normal Error    Handle other exceptions
     *
     *
     * @param  string   $value  to convert Half-width
     * @return string   $value  to create tsv file
     */
    private function convertToHalfWidth($value)
    {
        try {
            // Convert Full-width to Half-width
            return mb_convert_kana($value, 'n', 'UTF-8');
        } catch (TypeError $e) {
            // Handle TypeError (invalid type such as array instead of string)
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            // Handle other exceptions
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Convert within max bytes
     *
     * This method truncate to max bytes allowed.
     * The given value string.
     * Convert within the specified maximum bytes and if something wrong(TypeError, normal error), goto handle function' s catch case
     * TypeError    Handle TypeError for invalid types such as array instead of string
     * Normal Error    Handle other exceptions (if any)
     *
     * @param  string   $value  to convert max bytes
     * @param  int  $maxBytes to be allowed bytes
     * @return string   $value  to create tsv file
     */
    private function convertWithinMaxBytes($value, $maxBytes)
    {
        try {
            // Truncate to max bytes allowed
            return mb_strimwidth($value, 0, $maxBytes, '', 'UTF-8');
        } catch (TypeError $e) {
            // Handle TypeError for invalid types such as array instead of string
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            // Handle other exceptions (if any)
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Convert within max digits
     *
     * This method truncate to the max digits allowed
     * The given value string.
     * Truncate within the specified maximum digits and if something wrong(TypeError, normal error), goto handle function' s catch case
     * TypeError   Handle TypeError for invalid types such as array instead of string
     * Normal Error     Handle other exceptions (if any)
     *
     * @param  int   $value  to convert max digits
     * @param  int  $maxDigits to be allowed digits
     * @return string   $value  to create tsv file
     */
    private function convertWithinMaxDigits($value, $maxDigits)
    {
        try {
            // Truncate to the max digits allowed
            return mb_substr($value, 0, $maxDigits);
        } catch (TypeError $e) {
            // Handle TypeError for invalid types such as array instead of string
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            // Handle other exceptions (if any)
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Convert date format
     *
     * This method convert date format
     * The given value date.
     * Convert to the specified dateFormat and if something wrong(TypeError, normal error), goto handle function' s catch case
     * TypeError   Handle type errors, e.g., passing null or non-string types
     * Normal Error     Handle other exceptions
     *
     * @param  date   $value  to convert date with specified date format
     * @param  string  $dateFormat to be allowed dateFormat
     * @return string   $value  to create tsv file
     */
    private function convertDateFormat($value, $dateFormat)
    {
        try {
            // convert date format as $dateFormat
            return ($value) ? Carbon::parse($value)->format($dateFormat) : $value;
        } catch (TypeError $e) {
            // Handle type errors, e.g., passing null or non-string types
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            // Handle other exceptions
            throw new Exception($e->getMessage());
        }
    }
}
