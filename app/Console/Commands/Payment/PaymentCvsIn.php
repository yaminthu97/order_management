<?php

namespace App\Console\Commands\Payment;

use App\Console\Commands\Common\FileImportCommon;

use App\Enums\BatchExecuteStatusEnum;
use App\Enums\CvsPostOfficeCodeEnum;
use App\Enums\DeleteFlg;
use App\Enums\InputPaymentStatusEnum;
use App\Enums\PaymentImportDataTypeEnum;
use App\Enums\PaymentImportFlgEnum;
use App\Enums\PaymentImportInTypeEnum;
use App\Enums\PaymentSubjectItemCodeEnum;
use App\Enums\PaymentTypeEnum;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;

use App\Models\Claim\Base\BankPaymentCandidateModel;
use App\Models\Claim\Base\BankPaymentSmbcInModel;
use App\Models\Order\Base\OrderHdrModel;
use App\Models\Order\Base\PaymentModel;

use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Master\Gfh1207\Enums\BatchListEnum;

use App\Services\TenantDatabaseManager;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use File;

/**
 * コンビニ・郵便振込取込
 */
class PaymentCvsIn extends FileImportCommon
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:PaymentCvsIn {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'コンビニ・郵便振込取込データファイルを取込、受注データに対する入金レコードを作成する';

    // 入金登録方法
    private const PAYMENT_ENTRY_WAY = 'コンビニ・郵便振込登録';

    // レコード区分
    private const RECORD_TYPE_HEADER = '1'; // ヘッダレコード
    private const RECORD_TYPE_DATA = '2'; // データレコード
    private const RECORD_TYPE_TRAILER = '8'; // トレーラレコード
    private const RECORD_TYPE_END = '9'; // エンドレコード
    // データ種別
    private const DATA_TYPE_FLASH = '01'; // 速報
    private const DATA_TYPE_FIX = '02'; // 確定
    private const DATA_TYPE_FLASH_CANCEL = '03'; // 速報取消

    // jsonキー:入金ファイルパス
    protected $json_key_csv_path = 'csv_fullfile_path';
    // jsonキー:入金日
    protected $json_key_payment_date = 'payment_date';

    // ファイルエンコード(ebsdicは変則的なので全ての処理はこちらで行う)
    protected $import_file_encode = 'jis';
    // 取込ファイルフォーマット
    protected const FILE_RECORD_LENGTH = 120; // 1レコード長さ

    protected $import_file_format = [
        [
            'file_column_idx' => 0, 
            'file_column_name' => 'レコード区分', 
            'db_column_name' => 'record_type', 
            'rule' => ['required' => true, 'fixedLength' => 1, 'regex' => '/^[0-9a-zA-Z]+$/'] 
        ],
        [
            'file_column_idx' => 1, 
            'file_column_name' => 'データ種別', 
            'db_column_name' => 'data_type', 
            'rule' => ['required' => true, 'fixedLength' => 2, 'in_data' => [ '01', '02', '03' ] ] // 01:速報 02:確報 03:取消
        ],
        [
            'file_column_idx' => 2, 
            'file_column_name' => '収納日・年', 
            'db_column_name' => 'collected_year', 
            'rule' => ['required' => true, 'fixedLength' => 4, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 3, 
            'file_column_name' => '収納日・月', 
            'db_column_name' => 'collected_month', 
            'rule' => ['required' => true, 'fixedLength' => 2, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 4, 
            'file_column_name' => '収納日・日', 
            'db_column_name' => 'collected_day', 
            'rule' => ['required' => true, 'fixedLength' => 2, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 5, 
            'file_column_name' => '収納日・時', 
            'db_column_name' => 'collected_hour', 
            'rule' => ['required' => true, 'fixedLength' => 2, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 6, 
            'file_column_name' => '収納日・分', 
            'db_column_name' => 'collected_minute', 
            'rule' => ['required' => true, 'fixedLength' => 2, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 7, 
            'file_column_name' => 'バーコード種別', 
            'db_column_name' => 'barcode_type', 
            'rule' => ['required' => true, 'fixedLength' => 1, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 8, 
            'file_column_name' => '国コード', 
            'db_column_name' => 'country_code', 
            'rule' => ['required' => true, 'fixedLength' => 1, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 9, 
            'file_column_name' => 'ファイナンスコード', 
            'db_column_name' => 'finance_code', 
            'rule' => ['required' => true, 'fixedLength' => 5, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 10, 
            'file_column_name' => '全体チェックデジット', 
            'db_column_name' => 'total_check_digit', 
            'rule' => ['required' => true, 'fixedLength' => 1, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 11, 
            'file_column_name' => '予備1', 
            'db_column_name' => 'spare1', 
            'rule' => ['required' => true, 'fixedLength' => 1, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 12, 
            'file_column_name' => '予備2', 
            'db_column_name' => 'spare2', 
            'rule' => ['required' => true, 'fixedLength' => 1, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 13, 
            'file_column_name' => '収納企業コード', 
            'db_column_name' => 'storage_company_code', 
            'rule' => ['required' => true, 'fixedLength' => 5, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 14, 
            'file_column_name' => '収納企業ユニークコード1', 
            'db_column_name' => 'storage_company_unique_code1', 
            'rule' => ['required' => true, 'fixedLength' => 6, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 15, 
            'file_column_name' => '予備3', 
            'db_column_name' => 'spare3', 
            'rule' => ['required' => true, 'fixedLength' => 1, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 16, 
            'file_column_name' => '予備4', 
            'db_column_name' => 'spare4', 
            'rule' => ['required' => true, 'fixedLength' => 1, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 17, 
            'file_column_name' => '収納企業ユニークコード2', 
            'db_column_name' => 'storage_company_unique_code2', 
            'rule' => ['required' => true, 'fixedLength' => 11, 'regex' => '/^[0-9]+$/', 'numeric' => true] 
        ],
        [
            'file_column_idx' => 18, 
            'file_column_name' => '印紙フラグ', 
            'db_column_name' => 'stamp_flg', 
            'rule' => ['required' => true, 'fixedLength' => 1, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 19, 
            'file_column_name' => '支払期限1', 
            'db_column_name' => 'payment_due_date_str_1', 
            'rule' => ['required' => true, 'fixedLength' => 1, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 20, 
            'file_column_name' => '支払期限2', 
            'db_column_name' => 'payment_due_date_str_2', 
            'rule' => ['required' => true, 'fixedLength' => 5, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 21, 
            'file_column_name' => '料金', 
            'db_column_name' => 'amount', 
            'rule' => ['required' => true, 'fixedLength' => 6, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 22, 
            'file_column_name' => '予備5', 
            'db_column_name' => 'spare5', 
            'rule' => ['required' => true, 'fixedLength' => 1, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 23, 
            'file_column_name' => '受付店コード', 
            'db_column_name' => 'store_code', 
            'rule' => ['required' => true, 'fixedLength' => 7, 'regex' => '/^[0-9a-zA-Z]+$/', 'trim' => true] 
        ],
        [
            'file_column_idx' => 24, 
            'file_column_name' => '予備', 
            'db_column_name' => 'spare6', 
            'rule' => ['required' => true, 'fixedLength' => 2, 'regex' => '/^[0-9a-zA-Z]+$/'] 
        ],
        [
            'file_column_idx' => 25, 
            'file_column_name' => 'データ取得日・年', 
            'db_column_name' => 'data_get_year', 
            'rule' => ['required' => true, 'fixedLength' => 4, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 26, 
            'file_column_name' => 'データ取得日・月', 
            'db_column_name' => 'data_get_month', 
            'rule' => ['required' => true, 'fixedLength' => 2, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 27, 
            'file_column_name' => 'データ取得日・日', 
            'db_column_name' => 'data_get_day', 
            'rule' => ['required' => true, 'fixedLength' => 2, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 28, 
            'file_column_name' => '振込予定日・年', 
            'db_column_name' => 'payment_schedule_year', 
            'rule' => ['required' => true, 'fixedLength' => 4, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 29, 
            'file_column_name' => '振込予定日・月', 
            'db_column_name' => 'payment_schedule_month', 
            'rule' => ['required' => true, 'fixedLength' => 2, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 30, 
            'file_column_name' => '振込予定日・日', 
            'db_column_name' => 'payment_schedule_day', 
            'rule' => ['required' => true, 'fixedLength' => 2, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 31, 
            'file_column_name' => '手数料計算年月日・年', 
            'db_column_name' => 'fee_calc_year', 
            'rule' => ['required' => true, 'fixedLength' => 4, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 32, 
            'file_column_name' => '手数料計算年月日・月', 
            'db_column_name' => 'fee_calc_month', 
            'rule' => ['required' => true, 'fixedLength' => 2, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 33, 
            'file_column_name' => '手数料計算年月日・日', 
            'db_column_name' => 'fee_calc_day', 
            'rule' => ['required' => true, 'fixedLength' => 2, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 34, 
            'file_column_name' => 'CSVコード', 
            'db_column_name' => 'cvs_code', 
            'rule' => ['required' => true, 'fixedLength' => 6, 'regex' => '/^[0-9a-zA-Z]+$/', 'trim' => true] 
        ],
        [
            'file_column_idx' => 35, 
            'file_column_name' => '予備', 
            'db_column_name' => 'spare7', 
            'rule' => ['required' => true, 'fixedLength' => 18, 'regex' => '/^[0-9a-zA-Z]+$/', 'trim' => true] 
        ],
    ];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try
        {
            // 初期処理
            $this->initCommand( BatchListEnum::IMPDAT_PAYMENT_CVS->value );

            DB::beginTransaction();
            /**
             * jsonパラメータのチェックと取得
             */
            $this->checkJsonParameter();

            /**
             * ファイルフォーマット情報を基にバリデーション定義を生成
             */
            $this->createValidatorRules();

            /**
             * ファイルを改行でsplit
             */
            $fileRows = $this->getFileRows();

            /**
             * 取込ファイルの行データを分類する
             *   ヘッダレコード : 1 件
             *   データレコード : n 件
             *   トレーラレコード : 1 件
             *   エンドレコード : 1 件
             */
            $headerRecord = null;
            $dataRecordList = [];
            $trailerRecord = null;
            $endRecord = null;
            foreach( $fileRows as $idx => $rowData ){
                // 改行のみのレコードは無視
                if( empty( $rowData ) ){
                    continue;
                }

                // 先頭1文字のデータ種別で判定
                $recordType = substr( $rowData, 0, 1 );

                switch( $recordType ){
                    // ヘッダレコード
                    case $this::RECORD_TYPE_HEADER:
                        $headerRecord = $rowData;
                        break;
                    // データレコード
                    case $this::RECORD_TYPE_DATA:
                        $dataRecordList[] = $rowData;
                        break;
                    // トレーラレコード
                    case $this::RECORD_TYPE_TRAILER:
                        $trailerRecord = $rowData;
                        break;
                    // エンドレコード
                    case $this::RECORD_TYPE_END:
                        $endRecord = $rowData;
                        break;
                    // その他レコード（不正データ）：レコード種別エラーで検出されるのでデータレコードに格納
                    default:
                        $dataRecordList[] = $rowData;
                        break;
                }
            }
            // ヘッダレコードがない
            if( empty( $headerRecord ) ){
                throw new Exception(__('messages.error.impport_batch.header_record_not_found'));
            }
            // トレーラレコードがない
            if( empty( $trailerRecord ) ){
                throw new Exception(__('messages.error.impport_batch.trailer_record_not_found'));
            }
            // エンドレコードがない
            if( empty( $endRecord ) ){
                throw new Exception(__('messages.error.impport_batch.end_record_not_found'));
            }

            /**
             * データレコードの処理ループ
             */
            $inCandidateCount = 0; // 入金候補件数
            $inPaymentCount = 0; // 入金消込件数
            foreach( $dataRecordList as $idx => $dataRecord ){
                $fileLineNo = $idx + 2; // ファイル行数：0 オリジン補正 + ヘッダレコード分

                // 行データをモデルにfill出来る形式に変換
                $inputData = $this->createCvnInputData( $fileLineNo, $dataRecord );

                /**
                 * この時点でエラー情報が一件でも保存されている場合は以降の処理をしない
                 * ※エラー発生時点でrollback確定だが、以降は全てワーニングレベルのため処理する必要が無い
                 */
                if( !empty( $this->errors ) ){
                    continue;
                }

                // ワークデータ登録
                $workData = new BankPaymentSmbcInModel();
                $workData->t_execute_batch_instruction_id = $this->t_execute_batch_instruction_id;
                $workData->header_record = $headerRecord;
                $workData->data_record = $dataRecord;
                $workData->trailer_record = $trailerRecord;
                $workData->end_record = $endRecord;

                // 受注情報とデータレコードの解析結果をワークデータに書き起こす
                $collected_date = $inputData['collected_year'] . $inputData['collected_month'] . $inputData['collected_day'];
                $payment_schedule_date = $inputData['payment_schedule_year'] . $inputData['payment_schedule_month'] . $inputData['payment_schedule_day'];
                $workData->collected_date = $this->convertDateFormat( 'Ymd', 'Y-m-d', $collected_date );
                $workData->payment_schedule_date = $this->convertDateFormat( 'Ymd', 'Y-m-d', $payment_schedule_date );
                $workData->payment_due_date_str_1 = $inputData['payment_due_date_str_1'];
                $workData->payment_due_date_str_2 = $inputData['payment_due_date_str_2'];
                $workData->amount = intval( $inputData['amount'] );
                $workData->save();

                /**
                 * 取込データのデータ種別での分岐
                 */
                switch( $inputData['data_type'] ){
                    // 速報/確報
                    case $this::DATA_TYPE_FLASH:
                    case $this::DATA_TYPE_FIX:

                        /**
                         * 受注基本情報の取得
                         * 収納企業ユニークコード2 にゼロ詰め11桁で受注IDが入ってる
                         */
                        $orderData = OrderHdrModel::find( intval( $inputData['storage_company_unique_code2'] ) );
                        if( empty($orderData) ){
                            $candidateData = $this->saveCandidateData( $idx + 1, $inputData, $workData );
                            $inCandidateCount++;
                            $this->addWarningInfo($fileLineNo, $dataRecord, __('messages.warning.impport_batch.orderdata_not_found'));
                            break;
                        }
                        // 対象受注が取れた時点でワークテーブルに書き戻す
                        $workData->order_hdr_id = $orderData->t_order_hdr_id;
                        $workData->cust_id = $orderData->m_cust_id;
                        $workData->save();

                        /**
                         * 判定用の共通情報を事前取得
                         */
                        // 受注データが未入金かどうか
                        $isNotPaid = ( $orderData->payment_type == PaymentTypeEnum::NOT_PAID->value );
                        // 請求金額と料金の一致状態
                        $isMatchAmount = ( floor( $orderData->order_total_price ) == $workData->amount );
                        // 入金候補データの重複
                        $candidateList = BankPaymentCandidateModel::query()
                        ->where('t_order_hdr_id', '=', $orderData->t_order_hdr_id)
                        ->where('delete_flg', '=', DeleteFlg::Use->value)
                        ->get();

                        // 入金データの有無
                        $paymentList = PaymentModel::query()
                        ->where('t_order_hdr_id', $orderData->t_order_hdr_id)
                        ->where('delete_flg', '=', DeleteFlg::Use->value)
                        ->get();
                                
                        // 未入金以外
                        if( $isNotPaid == false ){
                            $candidateData = $this->saveCandidateData( $idx + 1, $inputData, $workData );
                            $inCandidateCount++;
                            $this->addWarningInfo($fileLineNo, $dataRecord, __('messages.warning.impport_batch.payment_type_wrong.candidate_none'));
                            break;
                        }
                        // 金額不一致
                        if( $isMatchAmount == false ){
                            $candidateData = $this->saveCandidateData( $idx + 1, $inputData, $workData );
                            $inCandidateCount++;
                            $this->addWarningInfo($fileLineNo, $dataRecord, __('messages.warning.impport_batch.amount_mismatch'));
                            break;
                        }

                        if( count( $candidateList ) > 0 ){
                            // 速報
                            if( $inputData['data_type'] == $this::DATA_TYPE_FLASH ){
                                $candidateData = $this->saveCandidateData( $idx + 1, $inputData, $workData );
                                $inCandidateCount++;
                                $this->addWarningInfo($fileLineNo, $dataRecord, __('messages.warning.impport_batch.candidate_dupilicate'));
                            }
                            else{

                                // 確報
                                foreach( $paymentList as $payment ){
                                    $payment->account_payment_date = $workData->payment_schedule_date;
                                    $payment->save();
                                }
                                $this->addWarningInfo($fileLineNo, $dataRecord, __('messages.warning.impport_batch.candidate_dupilicate_update_payment_schedule'));
                            }
                            break;
                        }

                        /**
                         * 正常消込
                         */
                        // 入金候補の登録
                        $candidateData = $this->saveCandidateData( $idx + 1, $inputData, $workData, $orderData );
                        // 入金データの登録
                        $paymentData = $this->savePaymentData( $candidateData, $orderData, $inputData );
                        // 対象受注の更新
                        $orderData = $this->updateOrderData( $candidateData, $orderData );
                        // 処理件数インクリメント
                        $inPaymentCount++;
                        $inCandidateCount++;
        
                        break;

                    // 速報取消
                    case $this::DATA_TYPE_FLASH_CANCEL:
                        $this->addWarningInfo($fileLineNo, $dataRecord, __('messages.warning.impport_batch.invalid_return_data'));
                        break;

                    default:
                        // 想定外のデータ種別はログだけ出して終了
                        Log::warning( __('messages.warning.impport_batch.record_type_not_covered', ['name' => 'データ種別', 'data' => json_encode( $workData ) ]) );
                        break;
                }
            }

            // 全行処理後、エラー情報がある場合はexception
            if( !empty( $this->errors ) ){
                throw new Exception(__('messages.error.impport_batch.error_import_file'));
            }

            /**
             * 終了処理
             */
            $resultMessage = __('messages.info.import_batch_result_payment', [
                'total' => count( $dataRecordList ), 
                'candidate' => $inCandidateCount,
                'payment' => $inPaymentCount
            ]);
            $this->outputResultFile( $resultMessage );
            $this->finalizeCommand(
                $resultMessage,
                BatchExecuteStatusEnum::SUCCESS->value,
                $this->result_file_path,
                null
            );
            
            DB::commit();
        }
        catch (Exception $e)
        {
            /**
             * 終了処理
             */
            DB::rollBack();
            $errorMessage = $e->getMessage();
            try{
                $this->outputErrorFile( $e->getMessage() );
            }
            catch( Exception $e2 )
            {
                $errorMessage .= ", " . $e2->getMessage();
            }
            \Log::error( $errorMessage );
            \Log::error( $e->getTraceAsString() );
            $this->finalizeCommand(
                $errorMessage,
                BatchExecuteStatusEnum::FAILURE->value,
                null,
                $this->error_file_path
            );
        }
    }

    /**
     * 固定長のデータレコードを項目ごとの配列にする
     */
    private function createCvnInputData( $rowNum, $rowData )
    {
        $rowData = mb_convert_encoding( $rowData, "utf-8", $this->import_file_encode);

        // レコード長チェック
        if( mb_strlen( $rowData ) != $this::FILE_RECORD_LENGTH ){
            $this->addErrorInfo($this::ERROR_TYPE_IMPORT, $rowNum, $rowData, __('messages.error.impport_batch.file_record_error.length', ['rownum' => $rowNum] ));
            return null;
        }

        // データレコードをコピー（参照引渡にならないように空文字をつなげる）
        $dataRecord = $rowData . '';
        $inputData = [];
        // 項目ごとの配列に変換する
        foreach( $this->import_file_format as $format ){
            $rule = $format['rule'];
            $inputData[] = substr( $dataRecord, 0, $rule['fixedLength'] );
            $dataRecord = substr( $dataRecord, $rule['fixedLength'] );
        }
        // 親クラスに定義されているチェック処理を通してinputDataを作成する
        $inputData =  parent::createInputData( $rowNum, $inputData, $rowData );
        return $inputData;
    }

    /**
     * 入金候補データを作成・保存する
     */
    private function saveCandidateData( $sortOrder, $inputData, $workData, $orderData = null ){
        // 郵便局判定（CVS店舗コードが郵便局系かどうか）
        $isPostOffice = empty( CvsPostOfficeCodeEnum::tryFrom( trim( $inputData['store_code'] ) ) ) ? false : true;

        $model = new BankPaymentCandidateModel();
        $model->t_execute_batch_instruction_id = $this->t_execute_batch_instruction_id;
        $model->sort_order = $sortOrder;
        $model->m_account_id = $this->batchExecute->m_account_id;
        $model->w_bank_payment_in_type = PaymentImportInTypeEnum::CVS->value; // コンビニ・郵便振込取込
        $model->w_bank_payment_id = $workData->w_bank_payment_smbc_id; // 入金取込ID
        $model->payment_datetime = $this->payment_date; // 入金日

        // 収納日：コンビニ＋速報 or 郵便局
        if( ( $isPostOffice == false && $inputData['data_type'] == $this::DATA_TYPE_FLASH ) || $isPostOffice == true ){
            $model->cust_payment_date = $workData->collected_date;
        }
        // 収納日：コンビニ＋確報 or 郵便局
        if( ( $isPostOffice == false && $inputData['data_type'] == $this::DATA_TYPE_FIX ) || $isPostOffice == true ){
            $model->account_payment_date = $workData->payment_schedule_date;
        }

        $model->payment_name = null; // 入金者名
        $model->payment_amount = $workData->amount; // 入金額
        $model->payment_note = null; // 備考

        // 入金ステータスと入金取込フラグ
        $model->payment_status = InputPaymentStatusEnum::NO_MATCH->value; // 該当なし
        $model->payment_import_flg = PaymentImportFlgEnum::MISMATCH->value; // 不一致

        // 対象受注がある場合
        if( !empty( $orderData ) ){
            // 請求金額と料金を比較して「部分一致」「完全一致」を設定
            $model->payment_status = InputPaymentStatusEnum::PART_MATCH->value;
            if( floor( $orderData->order_total_price ) == $workData->amount ){
                $model->payment_status = InputPaymentStatusEnum::FULL_MATCH->value;
                $model->payment_import_flg = PaymentImportFlgEnum::MATCH->value; // 一致
            }            
            $model->t_order_hdr_id = $orderData->t_order_hdr_id; // 受注ID
            $model->m_cust_id = $orderData->m_cust_id; // 顧客ID
        }

        // データ種別：01 -> 1, 02 -> 2, 03 -> 3 だがずれる可能性もある為、ちゃんと置換する
        $paymentDataType = null;
        switch( $inputData['data_type'] ){
            case $this::DATA_TYPE_FLASH:
                $paymentDataType = PaymentImportDataTypeEnum::FLASH->value;
                break;
            case $this::DATA_TYPE_FIX:
                $paymentDataType = PaymentImportDataTypeEnum::FIX->value;
                break;
            case $this::DATA_TYPE_FLASH_CANCEL:
                $paymentDataType = PaymentImportDataTypeEnum::CANCEL->value;
                break;
        }
        $model->payment_data_type = $paymentDataType;

        $model->delete_flg = DeleteFlg::Use->value; // 削除フラグ
        $model->entry_operator_id = $this->batchExecute->m_operators_id; // 登録者ID
        $model->update_operator_id = $this->batchExecute->m_operators_id; // 更新者ID

        $model->save();
        return $model;
    }

    /**
     * 入金データを作成・保存する
     */
    private function savePaymentData( $candidateData, $orderData, $inputData ){
        // コンビニ or 郵便振込の入金科目を取得
        $itemCode = PaymentSubjectItemCodeEnum::CONVENIENCE->value;
        if( empty( CvsPostOfficeCodeEnum::tryFrom( trim( $inputData['store_code'] ) ) ) ){
            $itemCode = PaymentSubjectItemCodeEnum::POST_OFFICE->value;
        }
        $item = $this::getPaymentSubject( $itemCode );

        $payment = new PaymentModel();
        $payment->m_account_id = $this->batchExecute->m_account_id; // 企業アカウントID
        $payment->delete_flg = DeleteFlg::Use->value; // 削除フラグ
        $payment->t_order_hdr_id = $orderData->t_order_hdr_id; // 受注基本ID
        $payment->m_cust_id = $candidateData->m_cust_id; // 顧客ID
        $payment->payment_price = $candidateData->payment_amount; // 入金金額
        $payment->payment_entry_date = $candidateData->payment_datetime; // 入金登録日
        $payment->cust_payment_date = $candidateData->cust_payment_date; // 顧客入金日
        $payment->account_payment_date = $candidateData->account_payment_date; // 口座入金日
        $payment->payment_subject = empty( $item ) ? null : $item->m_itemname_types_id; // 入金科目
        $payment->payment_entry_way = $this::PAYMENT_ENTRY_WAY; // 入金登録方法
        $payment->payment_company = trim( $inputData['store_code'] ); // 入金会社
        $payment->payment_store = null; // 入金店舗
        $payment->payment_comment = $candidateData->payment_note; // 備考
        $payment->payment_status = $candidateData->payment_status; // 入金状態
        $payment->entry_operator_id = $this->batchExecute->m_operators_id; // 登録者ID
        $payment->update_operator_id = $this->batchExecute->m_operators_id; // 更新者ID
        $payment->save();
        return $payment;
    }


    /**
     * 受注基本データを更新する
     */
    private function updateOrderData( $candidateData, $orderData ){
        $orderData->payment_date = $candidateData->payment_datetime; // 入金日
        $orderData->payment_price = $candidateData->payment_amount; // 入金金額

        // 入金区分
        if( $orderData->order_total_price == $candidateData->payment_amount ){
            $orderData->payment_type = PaymentTypeEnum::PAID->value; // 入金済み
        }
        else{
            // 一部入金だけど、入金額が請求金額を超えている場合は過入金
            $orderData->payment_type = PaymentTypeEnum::PARTIALLY_PAID->value;
            if( $orderData->order_total_price < $candidateData->payment_amount ){
                $orderData->payment_type = PaymentTypeEnum::OVER_PAID->value;
            }
        }

        $orderData->payment_datetime = Carbon::now(); // 入金区分変更日時
        $orderData->update_operator_id = $this->batchExecute->m_operators_id; // 更新者ID
        $orderData->save();
        return $orderData;
    }
}