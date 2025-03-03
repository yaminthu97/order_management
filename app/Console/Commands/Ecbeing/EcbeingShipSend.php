<?php

namespace App\Console\Commands\Ecbeing;

use App\Enums\BatchExecuteStatusEnum;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Common\Gfh1207\CheckBatchParameter;
use App\Modules\Order\Gfh1207\Enums\ShipNyukinExportTypeEnum;
use App\Modules\Order\Gfh1207\Enums\ShipNyukinRunTypeEnum;
use App\Modules\Order\Gfh1207\SendEcbeingShipData;
use App\Services\TenantDatabaseManager;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EcbeingShipSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:EcbeingShipSend {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'EC受注画面、出荷確定データ出力、入金・受注修正データ出力機能により Ecbeingから出荷確定データの送信を行う。';

    // バッチ名
    protected $batchName = '出荷確定データ送信';

    // disk name
    protected $disk;

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    // 出荷確定データ取込と更新
    protected $sendEcbeingShipData;

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
        SendEcbeingShipData $sendEcbeingShipData,
        CheckBatchParameter $checkBatchParameter,
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->sendEcbeingShipData = $sendEcbeingShipData;
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
            $paramKey = ['type', 'export_type', 'ship_input_file'];
            $checkResult = $this->checkBatchParameter->execute($this->argument('json'), $paramKey);  // to check batch json parameter

            if (!$checkResult) {
                // [パラメータが不正です。] message save to 'execute_result'
                throw new \Exception(__('messages.error.invalid_parameter'), self::PRIVATE_THROW_ERR_CODE);
            }

            $searchInfo = json_decode($this->argument('json'), true)['search_info'];

            $type = $searchInfo['type'];
            $exportType = $searchInfo['export_type'];
            $shipInputFile = $searchInfo['ship_input_file'];

            // 入力パラメータチェック
            $executeResult = '';
            if ($type == ShipNyukinRunTypeEnum::CREATE->value) {

                // 「処理種別：作成のみ」は出荷確定データ送信バッチ実行対象外です。
                $executeResult = (__('messages.error.batch_error.not_match_param1', ['functype' => '処理種別：' . ShipNyukinRunTypeEnum::CREATE->label(), 'batchname' => $this->batchName]));
            } elseif ($exportType == null || !in_array(ShipNyukinExportTypeEnum::SHIP_EXPORT->value, $exportType)) {

                // 「出荷確定データ出力」がチェックされていない場合は、出荷確定データ送信バッチ実行対象外です。
                $executeResult = (__('messages.error.batch_error.not_match_param2', ['functype' => ShipNyukinExportTypeEnum::SHIP_EXPORT->label(), 'batchname' => $this->batchName]));
            } elseif ($shipInputFile == null) {

                // 出荷確定データファイルが設定されていない場合は、出荷確定データ送信バッチ実行対象外です。
                $executeResult = (__('messages.error.batch_error.file_not_exists', ['file' => '出荷確定データ', 'batchname' => $this->batchName]));
            }

            // 出荷確定データ送信バッチ実行対象外です。
            if ($executeResult !== '') {
                // when 入力パラメータチェック is not correct, end the batch
                // [$executeResult] message save to 'execute_result'
                throw new Exception($executeResult, self::PRIVATE_THROW_ERR_CODE);
            }

            // Check if the file exists in S3
            if (!Storage::disk($this->disk)->exists($shipInputFile)) {
                // [出荷データファイルが見つかりません。（:shipInputFile）] message save to 'execute_result'
                throw new Exception(__('messages.error.file_not_found', ['file' => '出荷データ', 'path' => $shipInputFile]), self::PRIVATE_THROW_ERR_CODE);
            }

            /**
             * [個別処理]出荷確定データ送信処理
             * 下記のEcbeingAPIを利用して、出荷確定データの送信を行う。
             * - 出荷確定データ取込API
             * - 出荷確定データ更新API
             */
            $this->sendEcbeingShipData->execute(['shipInputFile' => $shipInputFile, 'disk' => $this->disk]);

            // Read the content of the file from S3
            $fileContents = Storage::disk($this->disk)->get($shipInputFile);

            // tsv to array based on newlines
            $tsvArr = explode("\n", $fileContents);

            // Get all lines and remove empty ones
            $dataRowCnt = array_filter($tsvArr);

            // Get total number of rows without header
            $rowCntExcludeHeader = count($dataRowCnt) - self::HEADER_ROW_COUNT;

            $executeStatus = BatchExecuteStatusEnum::SUCCESS->value;

            // [〇〇件の確認済み出荷データが処理されました。] message save to 'execute_result'
            $executeResult = (__('messages.success.batch_success.process_rowcnt', ['rowcnt' => $rowCntExcludeHeader]));

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
                'file_path' => $shipInputFile
            ]);

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();

            // Write the log in laravel.log for error message
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
