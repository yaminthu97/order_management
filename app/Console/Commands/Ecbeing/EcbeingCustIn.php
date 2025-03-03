<?php

namespace App\Console\Commands\Ecbeing;

use App\Enums\BatchExecuteStatusEnum;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Common\Gfh1207\CheckBatchParameter;
use App\Modules\Order\Gfh1207\Enums\OrderCustomerImportTypeEnum;
use App\Modules\Order\Gfh1207\Enums\OrderCustomerRunTypeEnum;
use App\Modules\Order\Gfh1207\GetTsvExportFilePath;
use App\Modules\Order\Gfh1207\ImportEcbeingCustData;
use App\Services\TenantDatabaseManager;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EcbeingCustIn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:EcbeingCustIn {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Web受注連携画面にてEcbeingから受信したファイル、' .
        'もしくはブラウザよりアップロードしたファイルから顧客の取り込みを行う。';

    // バッチ名
    protected $batchName = 'Ecbeing顧客取込';

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    // 顧客取込データ
    protected $importEcbeingCustData;

    // for check batch parameter
    protected $checkBatchParameter;

    //get file path to save on S3 server
    protected $getTsvExportFilePath;

    //throw error code constants
    private const PRIVATE_THROW_ERR_CODE = -1;

    /**
     * Create a new command instance.
     */
    public function __construct(
        StartBatchExecuteInstructionInterface $startBatchExecute,
        EndBatchExecuteInstructionInterface $endBatchExecute,
        ImportEcbeingCustData $importEcbeingCustData,
        CheckBatchParameter $checkBatchParameter,
        GetTsvExportFilePath $getTsvExportFilePath
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->importEcbeingCustData = $importEcbeingCustData;
        $this->checkBatchParameter = $checkBatchParameter;
        $this->getTsvExportFilePath = $getTsvExportFilePath;

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
            //バッチ実行ID
            $batchExecutionId = $this->argument('t_execute_batch_instruction_id');

            /**
             * [共通処理] 開始処理
             * バッチ実行指示テーブル（t_execute_batch_instruction ）から該当バッチの取得と開始処理
             * t_execute_batch_instruction_id より該当バッチを取得し以下の情報を書き込む
             * - バッチ開始時刻
             */
            $batchExecute = $this->startBatchExecute->execute($batchExecutionId);

            //バッチタイプ
            $batchType = $batchExecute->execute_batch_type;

            //アカウントコード
            $accountCode = $batchExecute->account_cd;

            //アカウントID
            $accountId = $batchExecute->m_account_id;

            //m_operators_id
            $operatorsId = $batchExecute->m_operators_id;

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
            $customerImportFile = null;

            // to required parameter
            $paramKey = ['type', 'import_type', 'customer_import_file'];

            // to check batch json parameter
            $checkResult = $this->checkBatchParameter->execute($this->argument('json'), $paramKey);

            if (!$checkResult) {
                //エラーメッセージはパラメータが不正です。
                throw new Exception(__('messages.error.invalid_parameter'), self::PRIVATE_THROW_ERR_CODE);
            }

            $param = $this->argument('json');

            $searchData = (json_decode($param, true))['search_info'];

            //パラメータから処理種別を取得する。
            $type = $searchData['type'];

            //パラメータから取込種別を取得する。
            $importType = $searchData['import_type'];

            //パラメータから顧客ファイルパスを取得する。
            $customerImportFile = $searchData['customer_import_file'];

            // 入力パラメータチェック
            $executeResult = '';

            //受信のみの場合
            if ($type == OrderCustomerRunTypeEnum::RECEIVE->value) {
                //「処理種別：受信のみ」はEcbeing顧客取込バッチ実行対象外です。
                $executeResult = __('messages.error.batch_error.not_match_param1', [
                    'functype' => '処理種別:' . OrderCustomerRunTypeEnum::RECEIVE->label(),
                    'batchname' => $this->batchName,
                ]);
            } elseif ($importType !== OrderCustomerImportTypeEnum::IMPORT_CUSTOMER_DATA->value) { //顧客データ取込を選択されていない場合
                //「取込種別:顧客データ取込」以外はEcbeing顧客取込バッチ実行対象外です。
                $executeResult = __('messages.error.batch_error.not_match_param2', [
                    'functype' => '取込種別:' . OrderCustomerImportTypeEnum::IMPORT_CUSTOMER_DATA->label(),
                    'batchname' => $this->batchName,
                ]);
            } elseif ($customerImportFile == null || $customerImportFile == "") { //customer_import_fileがnullまたは空の文字列の場合
                //顧客取込データファイルが設定されていない場合は、Ecbeing顧客取込バッチ実行対象外です。
                $executeResult = __('messages.error.batch_error.file_not_exists', ['file' => "顧客取込データ", 'batchname' => $this->batchName]);
            }

            // Ecbeing顧客取込バッチ実行対象外です。
            if ($executeResult !== '') {
                // when 入力パラメータチェック is not correct, end the batch
                // [$executeResult] message save to 'execute_result'
                throw new Exception($executeResult, self::PRIVATE_THROW_ERR_CODE);
            }

            //for S3
            $s3 = config('filesystems.default', 'local');

            // Get the file type
            $fileType = pathinfo($customerImportFile, PATHINFO_EXTENSION);

            //check file is tsv file type or not
            if ($fileType != "tsv") {
                //取込ファイルはtsvを指定してください。
                throw new Exception(__('messages.error.order_search.specify_import_file', ['extension' => 'tsv',]), self::PRIVATE_THROW_ERR_CODE);
            }

            //search file path on S3
            $fileExisted = Storage::disk($s3)->exists($customerImportFile);

            //ファイルパスが存在しない場合
            if (!$fileExisted) {
                //顧客取込データファイルが見つかりません。〇〇
                throw new Exception(__('messages.error.file_not_found', ['file' => '顧客取込データ', 'path' => $customerImportFile]), self::PRIVATE_THROW_ERR_CODE);
            }

            //get tsv file contents
            $fileContents = Storage::disk($s3)->get($customerImportFile);

            // to check excel data have or not condition
            if (!$fileContents) {
                // [入力対象のデータがありませんでした。] message save to 'execute_result'
                $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                    'execute_result' =>  __('messages.error.data_not_found2', ['datatype' => '入力']),
                    'execute_status' => BatchExecuteStatusEnum::SUCCESS->value
                ]);

                DB::commit();
                return;
            }

            //get tsv file path
            $savePath = $this->getTsvExportFilePath->execute($accountCode, $batchType, $batchExecutionId);

            // Directly copy the file path from parameter to save path
            $fileuploaded = Storage::disk($s3)->copy($customerImportFile, $savePath);

            //ファイルがAWS S3にアップロードされていない場合
            if (!$fileuploaded) {
                //AWS S3へのファイルのアップロードに失敗しました。
                throw new Exception(__('messages.error.upload_s3_failed'), self::PRIVATE_THROW_ERR_CODE);
            }

            //呼び出しインポートモジュールプロセスが実行される
            $totalRowCnt = $this->importEcbeingCustData->execute($savePath, $accountId, $accountCode, $batchType, $batchExecutionId, $operatorsId);

            /**
             * [共通処理] 終了処理
             * バッチ実行指示テーブルの該当バッチに以下の情報を書き込む
             * - バッチ終了時刻
             * - (実行状態)
             * - (実行結果)
             * - (格納ファイルパス)
             */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => __('messages.success.batch_success.success_rowcnt', ['rowcnt' => $totalRowCnt, 'process' => '取込']), // 〇〇件取込しました。
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
             * - (エラーファイルパス)
             */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => ($e->getCode() == self::PRIVATE_THROW_ERR_CODE) ? $e->getMessage() : BatchExecuteStatusEnum::FAILURE->label(),
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value,
                'error_file_path' => $customerImportFile,
            ]);
        }
    }
}
