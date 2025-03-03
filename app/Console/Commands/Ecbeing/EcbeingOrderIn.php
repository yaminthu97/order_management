<?php

namespace App\Console\Commands\Ecbeing;

use App\Enums\BatchExecuteStatusEnum;
use App\Modules\Claim\Base\UpdateBillingHdrInterface;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Common\Gfh1207\CheckBatchParameter;
use App\Modules\Order\Base\RegisterOrderTagAutoInterface;
use App\Modules\Order\Base\UpdateCampaignItemInterface;
use App\Modules\Order\Gfh1207\Enums\OrderCustomerImportTypeEnum;
use App\Modules\Order\Gfh1207\Enums\OrderCustomerRunTypeEnum;
use App\Modules\Order\Gfh1207\GetTsvExportFilePath;
use App\Modules\Order\Gfh1207\ImportEcbeingOrderData;
use App\Services\TenantDatabaseManager;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EcbeingOrderIn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:EcbeingOrderIn {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Web受注連携画面にてEcbeingから受信したファイル、' .
        'もしくはブラウザよりアップロードしたファイルから受注の取り込みを行う。';

    // バッチ名
    protected $batchName = 'Ecbeing受注取込';

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    // 受注取込データ
    protected $importEcbeingOrderData;

    // for check batch parameter
    protected $checkBatchParameter;

    //get file path to save on S3 server
    protected $getTsvExportFilePath;

    //キャンペーン商品追加
    protected $updateCampaignItem;

    //受注タグ付与判定
    protected $registerOrderTagAuto;

    //請求基本追加
    protected $updateBillingHdr;

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
        GetTsvExportFilePath $getTsvExportFilePath,
        UpdateCampaignItemInterface $updateCampaignItem,
        RegisterOrderTagAutoInterface $registerOrderTagAuto,
        UpdateBillingHdrInterface $updateBillingHdr
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->importEcbeingOrderData = $importEcbeingOrderData;
        $this->checkBatchParameter = $checkBatchParameter;
        $this->getTsvExportFilePath = $getTsvExportFilePath;
        $this->updateCampaignItem = $updateCampaignItem;
        $this->registerOrderTagAuto = $registerOrderTagAuto;
        $this->updateBillingHdr = $updateBillingHdr;

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
            $orderImportFile = null;

            // to required parameter
            $paramKey = ['type', 'import_type', 'order_import_file'];

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

            //パラメータから受注ファイルパスを取得する。
            $orderImportFile = $searchData['order_import_file'];

            // 入力パラメータチェック
            $executeResult = '';

            //受信のみの場合
            if ($type == OrderCustomerRunTypeEnum::RECEIVE->value) {
                //「処理種別：受信のみ」はEcbeing受注取込バッチ実行対象外です。
                $executeResult = __('messages.error.batch_error.not_match_param1', [
                    'functype' => '処理種別:' . OrderCustomerRunTypeEnum::RECEIVE->label(),
                    'batchname' => $this->batchName,
                ]);
            } elseif ($importType !== OrderCustomerImportTypeEnum::IMPORT_ORDER_DATA->value) { //受注データ取込を選択されていない場合
                //「取込種別:受注データ取込」以外はEcbeing受注取込バッチ実行対象外です。
                $executeResult = __('messages.error.batch_error.not_match_param2', [
                    'functype' => '取込種別:' . OrderCustomerImportTypeEnum::IMPORT_ORDER_DATA->label(),
                    'batchname' => $this->batchName,
                ]);
            } elseif ($orderImportFile == null || $orderImportFile == "") { //order_import_fileパスがnullまたは空の文字列の場合
                //受注取込データファイルが設定されていない場合は、Ecbeing受注取込バッチ実行対象外です
                $executeResult = __('messages.error.batch_error.file_not_exists', ['file' => "受信取込データ", 'batchname' => $this->batchName]);
            }

            // Ecbeing受注取込バッチ実行対象外です。
            if ($executeResult !== '') {
                // when 入力パラメータチェック is not correct, end the batch
                // [$executeResult] message save to 'execute_result'
                throw new Exception($executeResult, self::PRIVATE_THROW_ERR_CODE);
            }

            //for S3
            $s3 = config('filesystems.default', 'local');

            // Get the file type
            $fileType = pathinfo($orderImportFile, PATHINFO_EXTENSION);

            //check file is tsv file type or not
            if ($fileType != "tsv") {
                //取込ファイルはtsvを指定してください。
                throw new Exception(__('messages.error.order_search.specify_import_file', ['extension' => 'tsv',]), self::PRIVATE_THROW_ERR_CODE);
            }

            //search file path on S3
            $fileExisted = Storage::disk($s3)->exists($orderImportFile);

            //ファイルパスが存在しない場合
            if (!$fileExisted) {
                //受注取込データファイルが見つかりません。〇〇
                throw new Exception(__('messages.error.file_not_found', ['file' => '受注取込データ', 'path' => $orderImportFile]), self::PRIVATE_THROW_ERR_CODE);
            }

            //get tsv file contents
            $fileContents = Storage::disk($s3)->get($orderImportFile);

            // to check tsv data have or not condition
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
            $fileuploaded = Storage::disk($s3)->copy($orderImportFile, $savePath);

            //ファイルがAWS S3にアップロードされていない場合
            if (!$fileuploaded) {
                //AWS S3へのファイルのアップロードに失敗しました。
                throw new Exception(__('messages.error.upload_s3_failed'), self::PRIVATE_THROW_ERR_CODE);
            }

            //呼び出しインポートモジュールプロセスが実行される
            $importEcbeingOrderData = $this->importEcbeingOrderData->execute($savePath, $accountId, $accountCode, $batchType, $batchExecutionId, $operatorsId);
            $ordersInfo = $importEcbeingOrderData['orders_info'];

            /**
             * [共通処理] 終了処理
             * バッチ実行指示テーブルの該当バッチに以下の情報を書き込む
             * - バッチ終了時刻
             * - (実行状態)
             * - (実行結果)
             * - (格納ファイルパス)
             */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => __('messages.success.batch_success.success_rowcnt', ['rowcnt' => $importEcbeingOrderData['total_row_count'], 'process' => '取込']), // 〇〇件件取込しました。,
                'execute_status' => BatchExecuteStatusEnum::SUCCESS->value,
                'file_path' => $savePath,
            ]);

            DB::commit();

            // Create an array to track processed order_hdr_ids with personal_flag == 1
            $processedOrderHdrIds = [];

            foreach ($ordersInfo as $orderInfo) {
                $orderHdrId = $orderInfo['order_hdr_id'];
                $orderDestinationId = $orderInfo['order_destination_id'];
                $personalFlag = $orderInfo['personal_flag'];
                $isOrderHdrCreate = $orderInfo['order_hdr_create_flag'];

                // Check if the order_hdr_id has been processed already with personal_flag == 1
                if ($personalFlag == 1 && !in_array($orderHdrId, $processedOrderHdrIds)) {
                    //キャンペーン商品追加モジュール (UpdateCampaignItem)
                    $this->updateCampaignItem->execute($orderHdrId, $orderDestinationId);
                    $processedOrderHdrIds[] = $orderHdrId;
                }
                if ($isOrderHdrCreate) {
                    // 受注タグ付与判定モジュール
                    $this->registerOrderTagAuto->execute($orderHdrId, 1);

                    // 請求基本追加モジュール
                    $this->updateBillingHdr->execute($orderHdrId);
                }
            }
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
                'error_file_path' => $orderImportFile,
            ]);
        }
    }
}
