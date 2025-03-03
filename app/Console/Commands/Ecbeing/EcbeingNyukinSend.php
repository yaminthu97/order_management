<?php

namespace App\Console\Commands\Ecbeing;

use App\Enums\BatchExecuteStatusEnum;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Common\Gfh1207\CheckBatchParameter;
use App\Modules\Order\Gfh1207\Enums\ShipNyukinExportTypeEnum;
use App\Modules\Order\Gfh1207\Enums\ShipNyukinRunTypeEnum;
use App\Modules\Order\Gfh1207\SendEcbeingNyukinOrderData;
use App\Services\TenantDatabaseManager;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EcbeingNyukinSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:EcbeingNyukinSend {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'EC受注画面、出荷確定データ出力、入金・受注修正データ出力機能により Ecbeingへ入金・受注変更データの送信を行う。';

    // バッチ名
    protected $batchName = '入金・受注変更データ送信';

    // disk name
    protected $disk;

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    // 入金・受注変更データ送信
    protected $sendEcbeingNyukinOrderData;

    // parameter check
    protected $checkBatchParameter;

    // const variable
    private const PRIVATE_THROW_ERR_CODE = -1;
    private const HEADER_ROW_COUNT = 1;

    /**
     * Create a new command instance.
     */
    public function __construct(
        StartBatchExecuteInstructionInterface $startBatchExecute,
        EndBatchExecuteInstructionInterface $endBatchExecute,
        SendEcbeingNyukinOrderData $sendEcbeingNyukinOrderData,
        CheckBatchParameter $checkBatchParameter,
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->sendEcbeingNyukinOrderData = $sendEcbeingNyukinOrderData;
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

            $accountCode = $batchExecute->account_cd; // account code
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
            $paramKey = ['type', 'export_type', 'nyukin_input_file'];
            $checkResult = $this->checkBatchParameter->execute($this->argument('json'), $paramKey);  // to check batch json parameter

            if (!$checkResult) {
                // [パラメータが不正です。] message save to 'execute_result'
                throw new \Exception(__('messages.error.invalid_parameter'), self::PRIVATE_THROW_ERR_CODE);
            }

            // get json from parameter
            $searchInfo = json_decode($this->argument('json'), true)['search_info'];

            $type = $searchInfo['type']; // processing type
            $exportType = $searchInfo['export_type']; // export type
            $nyukinInputFile = $searchInfo['nyukin_input_file']; // nyukin input file

            // 入力パラメータチェック
            $executeResult = '';
            if ($type == ShipNyukinRunTypeEnum::CREATE->value) {
                // 「処理種別：作成のみ」は入金・受注変更データ送信バッチ実行対象外です。
                $executeResult = (__('messages.error.batch_error.not_match_param1', ['functype' => '処理種別：' . ShipNyukinRunTypeEnum::CREATE->label(), 'batchname' => $this->batchName]));
            } elseif ($exportType == null || !in_array(ShipNyukinExportTypeEnum::NYUKIN_EXPORT->value, $exportType)) {
                // 「出力種別：入金・受注修正データ出力」がチェックされていない場合は、入金・受注変更データ送信バッチ実行対象外です。
                $executeResult = (__('messages.error.batch_error.not_match_param2', ['functype' => '出力種別：' . ShipNyukinExportTypeEnum::NYUKIN_EXPORT->label(), 'batchname' => $this->batchName]));

            } elseif ($nyukinInputFile == null) {
                // 入金・受注変更データファイルが設定されていない場合は、入金・受注変更データ送信バッチ実行対象外です。
                $executeResult = (__('messages.error.batch_error.file_not_exists', ['file' => '入金・受注変更データ', 'batchname' => $this->batchName]));
            }

            // 入金・受注変更データ送信バッチ実行対象外です。
            if ($executeResult !== '') {
                // when 入力パラメータチェック is not correct, end the batch
                // [$executeResult] message save to 'execute_result'
                throw new Exception($executeResult, self::PRIVATE_THROW_ERR_CODE);
            }

            // Check if the file exists in S3
            if (!Storage::disk($this->disk)->exists($nyukinInputFile)) {
                // [入金・受注修正データファイルが見つかりません。（:nyukinInputFile）] message save to 'execute_result'
                throw new Exception(__('messages.error.file_not_found', ['file' => '入金・受注変更データ', 'path' => $nyukinInputFile]), self::PRIVATE_THROW_ERR_CODE);

            }
            /**
             * [個別処理]入金・受注修正データ
             * 下記のEcbeingAPIを利用して、入金・受注修正データの送信を行う。
             * - 入金・受注修正データ取込API
             * - 入金・受注変更データ更新API
             */
            $this->sendEcbeingNyukinOrderData->execute(['nyukinInputFile' => $nyukinInputFile, 'batchStartDateAndTime' => $batchExecute->batchjob_start_datetime, 'disk' => $this->disk]);

            // Read the content of the file from S3
            $fileContents = Storage::disk($this->disk)->get($nyukinInputFile);

            // tsv to array based on newlines
            $tsvArr = explode("\n", $fileContents);

            // Get all lines and remove empty ones
            $dataRowCnt = array_filter($tsvArr);

            // Get total number of rows without header
            $rowCntExcludeHeader = count($dataRowCnt) - self::HEADER_ROW_COUNT;

            $executeStatus = BatchExecuteStatusEnum::SUCCESS->value;

            // [〇〇件の確認済み入金・受注修正データが処理されました。] message save to 'execute_result'
            $executeResult = (__('messages.success.batch_success.process_rowcnt2', ['rowcnt' => $rowCntExcludeHeader]));
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
                'execute_status' => $executeStatus,
                'file_path' => $nyukinInputFile
            ]);

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();

            // Write the error message log in laravel.log file
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
}
