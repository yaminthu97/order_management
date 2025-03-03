<?php

namespace App\Console\Commands\Ecbeing;

use App\Enums\BatchExecuteStatusEnum;
use App\Models\Master\Base\ShopGfhModel;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Common\Gfh1207\CheckBatchParameter;
use App\Modules\Order\Gfh1207\CallEcbeingApi;
use App\Modules\Order\Gfh1207\Enums\ApiNameListEnum;
use App\Modules\Order\Gfh1207\Enums\OrderCustomerRunTypeEnum;
use App\Modules\Order\Gfh1207\GetSecurityValue;
use App\Modules\Order\Gfh1207\GetTsvExportFilePath;
use App\Modules\Order\Gfh1207\ImportEcbeingOrderData;
use App\Services\TenantDatabaseManager;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EcbeingOrderOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:EcbeingOrderOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'EC受注画面、顧客・受注取込機能によりEcbeingから受注データの受信を行う。' .
        'パラメータによっては「Ecbeing受注取込」モジュールを利用し、受注の取込を行う。';

    // バッチ名
    protected $batchName = 'Ecbeing受注受信';

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    // 受注取込データ
    protected $importEcbeingOrderData;

    // for check batch parameter
    protected $checkBatchParameter;

    // APIセキュリティ値を取得する
    protected $getSecurityValue;

    //get file path to save on S3 server
    protected $getTsvExportFilePath;

    //get Call Ecbeing Api
    protected $callEcbeingApi;

    // 注文データ作成API Response Constants
    private const NO_RECORD_TO_PROCESS = 2;

    //throw error code constants
    private const PRIVATE_THROW_ERR_CODE = -1;

    /**
     * Create a new command instance.
     */
    public function __construct(
        StartBatchExecuteInstructionInterface $startBatchExecute,
        EndBatchExecuteInstructionInterface $endBatchExecute,
        ImportEcbeingOrderData $importEcbeingOrderData,
        CheckBatchParameter $checkBatchParameter,
        GetSecurityValue $getSecurityValue,
        GetTsvExportFilePath $getTsvExportFilePath,
        CallEcbeingApi $callEcbeingApi
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->importEcbeingOrderData = $importEcbeingOrderData;
        $this->checkBatchParameter = $checkBatchParameter;
        $this->getSecurityValue = $getSecurityValue;
        $this->getTsvExportFilePath = $getTsvExportFilePath;
        $this->callEcbeingApi = $callEcbeingApi;

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

            $param = $this->argument('json');

            // to required parameter
            $paramKey = ['type'];

            // to check batch json parameter
            $checkResult = $this->checkBatchParameter->execute($this->argument('json'), $paramKey);

            if (!$checkResult) {
                //エラーメッセージはパラメータが不正です。
                throw new Exception(__('messages.error.invalid_parameter'), self::PRIVATE_THROW_ERR_CODE);
            }

            $searchData = (json_decode($param, true))['search_info'];

            //パラメータから処理種別を取得する。
            $type = $searchData['type'];

            //取込のみの場合
            if ($type == OrderCustomerRunTypeEnum::IMPORT->value) {
                //「処理種別：取込のみ」はEcbeing受注受信バッチ実行対象外です。
                throw new Exception(__('messages.error.batch_error.not_match_param1', [
                    'functype' => '処理種別:' . OrderCustomerRunTypeEnum::IMPORT->label(),
                    'batchname' => $this->batchName,
                ]), self::PRIVATE_THROW_ERR_CODE);
            }

            //処理日時
            $key = Carbon::now()->format('YmdHis');

            // API基本情報取得
            $apiInfo = ShopGfhModel::orderBy('m_shop_gfh_id', 'desc')->first();

            //$apiInfoの結果がnullまたはエラーの場合、バッチは終了します。
            if (!$apiInfo) {
                // エラーメッセージはAPIの基本情報が取得できませんでした。
                throw new Exception(__('messages.error.batch_error.data_not_found3', ['data' => 'APIの基本']), self::PRIVATE_THROW_ERR_CODE);
            }

            $client = new Client([
                'base_uri' =>  $apiInfo->ecbeing_api_base_url, // ecbeing APIベースURL
            ]);

            $expOrderApiName = ApiNameListEnum::EXP_SALES;  //get expOrder Api name form ApiNameListEnum
            $expOrderSecurity = $this->getSecurityValue->execute($key, $apiInfo->ecbeing_api_exp_sales); //key+注文データ作成の特定文字列をMD5で暗号化したモジュール

            // write the log info in laravel.log for 注文データ作成 API
            Log::info("url : " . $apiInfo->ecbeing_api_base_url . $expOrderApiName->value);
            Log::info("key : " . $key);
            Log::info("security : " . $expOrderSecurity);

            //注文データ作成 api call
            $expOrderApiResponse = $this->callEcbeingApi->execute($key, $client, $expOrderApiName, $expOrderSecurity)->getBody()->getContents();

            //注文データ作成APIの結果が2以上の場合、バッチは終了します。
            if ($expOrderApiResponse >= self::NO_RECORD_TO_PROCESS) {
                //Ecbeing APIからエラーが戻されました。 API名: 注文データ作成、 レスポンスコード→[Apiレスポンスコード]
                throw new Exception(
                    __('messages.error.api_error_with_response_code', ['APIname' => $expOrderApiName->label(), 'APIresponse' => $expOrderApiResponse]),
                    self::PRIVATE_THROW_ERR_CODE
                );
            }

            // show response log in laravel.log for 注文データ作成 API
            Log::info("response : " . $expOrderApiResponse);

            $dlOrderApiName = ApiNameListEnum::DL_SALES; //get dlOrder Api name form ApiNameListEnum
            $dlOrderSecurity = $this->getSecurityValue->execute($key, $apiInfo->ecbeing_api_dl_sales); //key+注文データ作成の特定文字列をMD5で暗号化したモジュール

            // write the log info in laravel.log for 注文データ作成 API
            Log::info("url : " . $apiInfo->ecbeing_api_base_url . $dlOrderApiName->value);
            Log::info("key : " . $key);
            Log::info("security : " . $dlOrderSecurity);

            //注文データダウンロード api call
            $dlOrderApiResponse = $this->callEcbeingApi->execute($key, $client, $dlOrderApiName, $dlOrderSecurity)->getBody()->getContents();

            //注文データダウンロード API の結果が数値の場合、バッチは終了します。
            if (is_numeric($dlOrderApiResponse)) {
                //Ecbeing APIからエラーが戻されました。 API名: 注文データダウンロード、 レスポンスコード→[Apiレスポンスコード]
                throw new Exception(
                    __('messages.error.api_error_with_response_code', ['APIname' => $dlOrderApiName->label(), 'APIresponse' => $dlOrderApiResponse]),
                    self::PRIVATE_THROW_ERR_CODE
                );
            }

            // show response log in laravel.log for 注文データダウンロード API
            Log::info("response : " . $dlOrderApiResponse);

            // tsv to array based on newlines
            $tsvArr = explode("\n", $dlOrderApiResponse);

            // Get all lines and remove empty ones
            $dataRowCnt = array_filter($tsvArr);

            // Get total number of rows
            $totalRowCnt = count($dataRowCnt);

            //get tsv file path
            $savePath =  $this->getTsvExportFilePath->execute($accountCode, $batchType, $batchExecutionId);

            //save file on s3
            $fileuploaded = Storage::disk(config('filesystems.default', 'local'))->put($savePath, $dlOrderApiResponse);

            //ファイルがAWS S3にアップロードされていない場合
            if (!$fileuploaded) {
                //AWS S3へのファイルのアップロードに失敗しました。
                throw new Exception(__('messages.error.upload_s3_failed'), self::PRIVATE_THROW_ERR_CODE);
            }

            //処理種別が「EXECUE_ALL」と一致する場合、インポート プロセスが実行されます。
            if ($type == OrderCustomerRunTypeEnum::EXECTUE_ALL->value) {
                $totalRowCnt = $this->importEcbeingOrderData->execute($savePath, $accountId, $accountCode, $batchType, $batchExecutionId, $operatorsId);
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
                'execute_result' => __('messages.success.batch_success.success_rowcnt', ['rowcnt' => $totalRowCnt, 'process' => '受信']), // 〇〇件受信しました。
                'execute_status' => BatchExecuteStatusEnum::SUCCESS->value,
                'file_path' => $savePath,
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            // Error message to laravel.log
            Log::error('error message : ' . $e->getMessage());

            /**
             * [共通処理] 終了処理
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
}
