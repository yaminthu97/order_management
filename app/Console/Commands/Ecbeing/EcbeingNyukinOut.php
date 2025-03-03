<?php

namespace App\Console\Commands\Ecbeing;

use App\Enums\BatchExecuteStatusEnum;
use App\Models\Order\Base\OrderHdrModel;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Common\Gfh1207\CheckBatchParameter;
use App\Modules\Order\Gfh1207\Enums\ShipNyukinExportTypeEnum;
use App\Modules\Order\Gfh1207\Enums\ShipNyukinRunTypeEnum;
use App\Modules\Order\Gfh1207\GetTsvExportFilePath;
use App\Modules\Order\Gfh1207\SendEcbeingNyukinOrderData;
use App\Services\TenantDatabaseManager;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use TypeError;

class EcbeingNyukinOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:EcbeingNyukinOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'EC受注画面、出荷確定データ出力、入金・受注修正データ出力機能により入金・受注修正データの出力を行う。パラメータによってはEcbeingAPIを利用し、入金・受注変更データの送信を行う。';

    // バッチ名
    protected $batchName = '入金・受注修正データ出力';

    // disk name
    protected $disk;

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    // 入金・受注修正データ出力
    protected $sendEcbeingNyukinOrderData;

    // parameter check
    protected $checkBatchParameter;

    // export file path
    protected $getTsvExportFilePath;

    // const variable
    private const PRIVATE_THROW_ERR_CODE = -1;
    private const HEADER_ROW_COUNT = 1;
    private const TSV_START_LINE_NUM = 1;
    private const WEB_ORDER_NUM_MAX_BYTES = 30;

    /**
     * Create a new command instance.
     */
    public function __construct(
        StartBatchExecuteInstructionInterface $startBatchExecute,
        EndBatchExecuteInstructionInterface $endBatchExecute,
        SendEcbeingNyukinOrderData $sendEcbeingNyukinOrderData,
        CheckBatchParameter $checkBatchParameter,
        GetTsvExportFilePath $getTsvExportFilePath,
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->sendEcbeingNyukinOrderData = $sendEcbeingNyukinOrderData;
        $this->checkBatchParameter = $checkBatchParameter;
        $this->getTsvExportFilePath = $getTsvExportFilePath;

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

            // account code from global_db.t_execute_batch_instruction
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
            $paramKey = ['type', 'export_type'];
            $checkResult = $this->checkBatchParameter->execute($this->argument('json'), $paramKey);  // to check batch json parameter

            if (!$checkResult) {
                // [パラメータが不正です。] message save to 'execute_result'
                throw new \Exception(__('messages.error.invalid_parameter'), self::PRIVATE_THROW_ERR_CODE);
            }

            // get json from parameter
            $searchInfo = json_decode($this->argument('json'), true)['search_info'];
            $type = $searchInfo['type']; // processing type
            $exportType = $searchInfo['export_type']; // export type

            // 処理種別 は「3:送信のみ」
            if ($type == ShipNyukinRunTypeEnum::SEND->value) {

                // '「処理種別：送信のみ」は入金・受注修正データ出力バッチ実行対象外です。' message save to execute_result
                throw new Exception((__('messages.error.batch_error.not_match_param1', ['functype' => '処理種別：' . ShipNyukinRunTypeEnum::SEND->label(), 'batchname' => $this->batchName])), self::PRIVATE_THROW_ERR_CODE);
            }

            // 出力種別 は「2:入金・受注修正データ出力」が選択されていない場合
            if ($exportType == null || !in_array(ShipNyukinExportTypeEnum::NYUKIN_EXPORT->value, $exportType)) {
                // '「出力種別：入金・受注修正データ出力」がチェックされていない場合は入金・受注修正データ出力バッチ実行対象外です。' message save to execute_result
                throw new Exception((__('messages.error.batch_error.not_match_param2', ['functype' => '出力種別：' . ShipNyukinExportTypeEnum::NYUKIN_EXPORT->label(), 'batchname' => $this->batchName])), self::PRIVATE_THROW_ERR_CODE);

            }

            // EC受注画面にて指定された条件を元に受注基本からデータを抽出し作成する。
            $outputData = $this->retrieveData();

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

            // Define the file path and name (save in the storage or s3)
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
                 * [個別処理]入金・受注修正データ
                 * 下記のEcbeingAPIを利用して、入金・受注修正データの送信を行う。
                 * - 入金・受注修正データ取込API
                 * - 入金・受注変更データ更新API
                 */
                $this->sendEcbeingNyukinOrderData->execute(['nyukinInputFile' => $fileSavePath, 'batchStartDateAndTime' => $batchExecute->batchjob_start_datetime, 'disk' => $this->disk]);
            }

            // 処理種別 は「2:作成のみ」
            if ($type == ShipNyukinRunTypeEnum::CREATE->value) {
                // 「処理種別：作成のみ」は入金・受注修正データ出力バッチ実行対象外です。message save to 'execute_result'
                $executeResult = (__('messages.error.batch_error.not_match_param1', ['functype' => '処理種別：' . ShipNyukinRunTypeEnum::CREATE->label(), 'batchname' => $this->batchName]));

                throw new Exception($executeResult, self::PRIVATE_THROW_ERR_CODE);
            }

            // [〇〇件出力しました。] message save to 'execute_result'
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
            // set fail result (異常)
            $executeResult = BatchExecuteStatusEnum::FAILURE->label();

            // Write the error message log in laravel.log file
            Log::error('error_message : ' . $e->getMessage());

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
     * EC受注画面にて指定された条件を元に受注基本からデータを抽出し作成する。
     *
     * This method retrieve data based on 出荷基本.Web受注注文変更フラグ が NULL ではない and 出荷基本.Web受注変更連携フラグ が NULL from parameter
     * This method sort the order based on 出荷基本ID（昇順）
     *
     *
     * @return array   $outputData  to create tsv file
     */
    private function retrieveData()
    {
        $outputData = OrderHdrModel::query()
            ->select(
                'ec_order_num',
                'ec_order_change_flg',
                'ec_order_change_datetime',
                'order_total_price'
            )
            ->whereNotNull('ec_order_change_flg')
            ->whereNull('ec_order_sync_flg')
            ->whereNotNull('ec_order_num') // [Additional condition]Check that ec_order_num is not null
            ->orderBy('t_order_hdr_id', 'asc')
            ->get()->toArray();

        return $outputData;
    }

    /**
     * Create the tsv file
     *
     * This method create file based on data array that from retrieveData func:
     * The given data array.
     * check validation the data array in this method [using func: of convertToHalfWidth, convertWithinMaxBytes]
     *
     * @param  array   $data  to create file with \t
     * @return file   $tsvContent  tsv format file
     */
    private function tsvCreate($data)
    {
        // tsv header(処理日付-出荷完了日（YYYYMMDD）\tレコード件数-明細部の件数)
        $tsvContent = Carbon::now()->format('Ymd') . "\t" . count($data) . "\n";
        $lineNo = self::TSV_START_LINE_NUM; // number for 行番号

        // looping data array for creating tsv file
        foreach ($data as $key => $row) {
            // WEB受注NO.
            // convert from full-width to half-width for WEB受注NO.
            $webOrderNo = $this->convertToHalfWidth($row['ec_order_num']);

            // The number of bytes for WEB受注NO. is converted to 30 bytes.
            $webOrderNo = $this->convertWithinMaxBytes($webOrderNo, self::WEB_ORDER_NUM_MAX_BYTES);

            // 入金・注文変更フラグ
            $ecOrderChgFlg = $row['ec_order_change_flg'];

            // 入金・注文変更日時
            $ecOrderChgDate = '';
            $ecOrderChgtime = '';
            if ($row['ec_order_change_datetime']) {
                // Additional requirements
                // Web受注注文変更日時 (YYYYMMDD)
                $ecOrderChgDate = Carbon::parse($row['ec_order_change_datetime'])->format('Ymd');
                // Web受注注文変更日時 (HHMMSS)
                $ecOrderChgtime = Carbon::parse($row['ec_order_change_datetime'])->format('His');
            }

            // 注文金額合計
            $orderTotAmt = $this->convertToHalfWidth($row['order_total_price']);

            // prepare array to create tsv file
            $tsvRow = [
                $lineNo,
                $webOrderNo,
                $ecOrderChgFlg,
                $ecOrderChgDate,
                $ecOrderChgtime,
                $orderTotAmt,
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
}
