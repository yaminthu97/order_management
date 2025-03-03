<?php

namespace App\Console\Commands\Payment;

use App\Console\Commands\Common\FileImportCommon;

use App\Enums\BatchExecuteStatusEnum;
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
use App\Models\Claim\Base\BankPaymentCreditInModel;
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
 * クレジット入金取込
 */
class PaymentCreditIn extends FileImportCommon
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:PaymentCreditIn {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'クレジット入金情報CSVを取込、受注データに対する入金レコードを作成する';

    // 入金登録方法
    private const PAYMENT_ENTRY_WAY = 'クレジットカード登録';

    // 取引状態
    private const DEAL_STATUS_UNPROCESSED = 'UNPROCESSED';
    private const DEAL_STATUS_AUTHENTICATED = 'AUTHENTICATED';
    private const DEAL_STATUS_CHECK = 'CHECK';
    private const DEAL_STATUS_SAUTH = 'SAUTH';
    private const DEAL_STATUS_AUTH = 'AUTH';
    private const DEAL_STATUS_SALES = 'SALES';
    private const DEAL_STATUS_CAPTURE = 'CAPTURE';
    private const DEAL_STATUS_VOID = 'VOID';
    private const DEAL_STATUS_RETURN = 'RETURN';
    private const DEAL_STATUS_RETURNX = 'RETURNX';

    // jsonキー:入金ファイルパス
    protected $json_key_csv_path = 'csv_fullfile_path';
    // jsonキー:入金日
    protected $json_key_payment_date = 'payment_date';
    // jsonキー:顧客入金日
    protected $json_key_cust_payment_date = 'cust_payment_date';
    // jsonキー:口座入金日
    protected $json_key_account_payment_date = 'account_payment_date';

    // ファイルエンコード(utf8以外の場合のみ設定)
    protected $import_file_encode = 'sjis';
    // 取込ファイルフォーマット
    protected $import_file_format = [
        [
            'file_column_idx' => 0, 
            'file_column_name' => 'ショップID', 
            'db_column_name' => 'in_shop_id', 
            'rule' => ['required' => false, 'byteMaxLength' => 13, 'regex' => '/^[0-9a-zA-Z]+$/'] 
        ],
        [
            'file_column_idx' => 1, 
            'file_column_name' => 'オーダーID', 
            'db_column_name' => 'in_order_id',
            'rule' => ['required' => false, 'byteMaxLength' => 27, 'regex' => '/^[0-9a-zA-Z-]+$/'] 
        ],
        [
            'file_column_idx' => 2, 
            'file_column_name' => '取引状態', 
            'db_column_name' => 'in_deal_status',
            'rule' => ['required' => false, 'byteMaxLength' => 13, 'regex' => '/^[a-zA-Z]+$/'] 
        ],
        [
            'file_column_idx' => 3, 
            'file_column_name' => '商品コード', 
            'db_column_name' => 'in_item_code',
            'rule' => ['required' => false, 'byteMaxLength' => 7, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 4, 
            'file_column_name' => '利用金額', 
            'db_column_name' => 'in_amount',
            'rule' => ['required' => false, 'byteMaxLength' => 7, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 5, 
            'file_column_name' => '税送料', 
            'db_column_name' => 'in_tax',
            'rule' => ['required' => false, 'byteMaxLength' => 7, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 6, 
            'file_column_name' => '支払方法', 
            'db_column_name' => 'in_payment_type',
            'rule' => ['required' => false, 'byteMaxLength' => 1, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 7, 
            'file_column_name' => '支払回数', 
            'db_column_name' => 'in_payment_count',
            'rule' => ['required' => false, 'byteMaxLength' => 2, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 8, 
            'file_column_name' => 'カード種別', 
            'db_column_name' => 'in_card_type',
            'rule' => ['required' => false, 'byteMaxLength' => 1] 
        ],
        [
            'file_column_idx' => 9, 
            'file_column_name' => '会員ID', 
            'db_column_name' => 'in_user_id',
            'rule' => ['required' => false, 'byteMaxLength' => 60, 'regex' => '/^[0-9a-zA-Z-_@.]+$/'] 
        ],
        [
            'file_column_idx' => 10, 
            'file_column_name' => '予備', 
            'db_column_name' => 'in_spare_field_1',
            'rule' => ['required' => false, 'byteMaxLength' => 100] 
        ],
        [
            'file_column_idx' => 11, 
            'file_column_name' => '予備', 
            'db_column_name' => 'in_spare_field_2',
            'rule' => ['required' => false, 'byteMaxLength' => 100] 
        ],
        [
            'file_column_idx' => 12, 
            'file_column_name' => '加盟店自由項目1', 
            'db_column_name' => 'in_merchant_custom_field_1',
            'rule' => ['required' => false, 'byteMaxLength' => 100, 'regex' => '/[^!"#$%&' . "'" . '()\*\+\-\.,\/:;<=>?@\[\\\\\\]^_`{|}~]+$/'] 
        ],
        [
            'file_column_idx' => 13, 
            'file_column_name' => '加盟店自由項目2', 
            'db_column_name' => 'in_merchant_custom_field_2',
            'rule' => ['required' => false, 'byteMaxLength' => 100, 'regex' => '/[^!"#$%&' . "'" . '()\*\+\-\.,\/:;<=>?@\[\\\\\\]^_`{|}~]+$/'] 
        ],
        [
            'file_column_idx' => 14, 
            'file_column_name' => '加盟店自由項目3', 
            'db_column_name' => 'in_merchant_custom_field_3',
            'rule' => ['required' => false, 'byteMaxLength' => 100, 'regex' => '/[^!"#$%&' . "'" . '()\*\+\-\.,\/:;<=>?@\[\\\\\\]^_`{|}~]+$/'] 
        ],
        [
            'file_column_idx' => 15, 
            'file_column_name' => '取引ID', 
            'db_column_name' => 'in_deal_id',
            'rule' => ['required' => false, 'byteMaxLength' => 32, 'regex' => '/^[0-9a-zA-Z]+$/'] 
        ],
        [
            'file_column_idx' => 16, 
            'file_column_name' => '取引パスワード', 
            'db_column_name' => 'in_deal_password',
            'rule' => ['required' => false, 'byteMaxLength' => 32, 'regex' => '/^[0-9a-zA-Z]+$/'] 
        ],
        [
            'file_column_idx' => 17, 
            'file_column_name' => 'トランザクションID', 
            'db_column_name' => 'in_transaction_id',
            'rule' => ['required' => false, 'byteMaxLength' => 28, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 18, 
            'file_column_name' => '承認番号', 
            'db_column_name' => 'in_approval_no',
            'rule' => ['required' => false, 'byteMaxLength' => 7, 'regex' => '/^[0-9a-zA-Z]+$/'] 
        ],
        [
            'file_column_idx' => 19, 
            'file_column_name' => '仕向先コード', 
            'db_column_name' => 'in_forwarding_code',
            'rule' => ['required' => false, 'byteMaxLength' => 7, 'regex' => '/^[0-9a-zA-Z]+$/'] 
        ],
        [
            'file_column_idx' => 20, 
            'file_column_name' => 'エラーコード', 
            'db_column_name' => 'in_error_code',
            'rule' => ['required' => false, 'byteMaxLength' => 32] 
        ],
        [
            'file_column_idx' => 21, 
            'file_column_name' => 'エラー詳細コード', 
            'db_column_name' => 'in_error_detail_code',
            'rule' => ['required' => false, 'byteMaxLength' => 32] 
        ],
        [
            'file_column_idx' => 22, 
            'file_column_name' => '処理日時', 
            'db_column_name' => 'in_process_date',
            'rule' => ['required' => false, 'byteMaxLength' => 14, 'byteMinLength' => 14, 'date' => ['format' => 'YmdHis']  ]
        ],
    ];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        /**
         * 本処理
         */
        try
        {
            // 初期処理
            $this->initCommand( BatchListEnum::IMPDAT_PAYMENT_CREDIT->value );

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
             * ファイル取込ループ
             */
            $inCandidateCount = 0; // 入金候補件数
            $inPaymentCount = 0; // 入金消込件数
            $inReturnCount = 0; // 返金件数
            foreach( $fileRows as $idx => $rowData ){
                // 改行のみのレコードは無視
                if( empty( $rowData ) ){
                    continue;
                }

                $fileLineNo = $idx + 1; // ファイル行数：0 オリジン補正

                // 行データCSVパースしてモデルにfill出来る形式に変換
                $rowData = $this->convertEncode( $rowData );
                $inputData = str_getcsv( $rowData );
                $inputData = $this->createInputData( $fileLineNo, $inputData, $rowData );
                $inputData = $this->convertInputData( $inputData );
                if( empty($inputData) ){
                    // 行データがnullの場合はエラーが発生しているので次の行へ
                    continue;
                }

                /**
                 * この時点でエラー情報が一件でも保存されている場合は以降の処理をしない
                 * ※エラー発生時点でrollback確定だが、以降は全てワーニングレベルのため処理する必要が無い
                 */
                if( !empty( $this->errors ) ){
                    continue;
                }

                /**
                 * ワークデータ（入金取込）を登録
                 */
                $workData = new BankPaymentCreditInModel();
                $workData->t_execute_batch_instruction_id = $this->t_execute_batch_instruction_id;
                $workData->amount = $inputData['in_amount'];
                $workData->payment_schedule_date = $this->payment_date;
                $workData->fill( $inputData );
                $workData->save();

                /**
                 * 入金取込レコードの重複チェック
                 * オーダーIDとデータ種別が同じデータが既に存在する
                 */
                $isExists = BankPaymentCreditInModel::query()
                ->where('t_execute_batch_instruction_id', '!=', $workData->t_execute_batch_instruction_id)
                ->where('in_order_id', '=', $workData->in_order_id)
                ->where('in_deal_status', '=', $workData->in_deal_status)
                ->orderBy('w_bank_payment_credit_id')
                ->exists();
                if( $isExists ){
                    $candidateData = $this->saveCandidateData( $fileLineNo, $workData );
                    $inCandidateCount++;
                    $this->addWarningInfo($fileLineNo, $rowData, __('messages.warning.impport_batch.workdata_dupulicate'));
                    continue;
                }

                /**
                 * 受注基本情報の取得
                 * ESMアカウントの受注テーブルにオーダーIDが存在しない
                 */
                $orderData = OrderHdrModel::query()
                ->where('ec_order_num', $workData->in_order_id)
                ->first();
                if( empty($orderData) ){
                    $candidateData = $this->saveCandidateData( $fileLineNo, $workData );
                    $inCandidateCount++;
                    $this->addWarningInfo($fileLineNo, $rowData, __('messages.warning.impport_batch.orderdata_not_found'));
                    continue;
                }

                /**
                 * 入金データ存在フラグ
                 */
                $isPaymentExists = PaymentModel::query()
                ->where('t_order_hdr_id', $orderData->t_order_hdr_id)
                ->where('delete_flg', '=', DeleteFlg::Use->value)
                ->exists();

                /**
                 * 対象受注の入金候補データを取得
                 */
                $candidateList = BankPaymentCandidateModel::query()
                ->where('t_order_hdr_id', '=', $orderData->t_order_hdr_id)
                ->where('delete_flg', '=', DeleteFlg::Use->value)
                ->get();

                /**
                 * データ種別ごとの処理振り分け
                 */
                switch( $workData->in_deal_status ){

                    /**
                     * 実売上/即時売上
                     */
                    case $this::DEAL_STATUS_SALES:
                    case $this::DEAL_STATUS_CAPTURE:
                        // 対象受注の入金データが既に存在する
                        if( $isPaymentExists ){
                            $candidateData = $this->saveCandidateData( $fileLineNo, $workData );
                            $inCandidateCount++;
                            $this->addWarningInfo($fileLineNo, $rowData, __('messages.warning.impport_batch.payment_exists'));
                            break;
                        }
        
                        // 対象受注の請求金額が不一致
                        if( floor( $orderData->order_total_price ) != $workData->in_amount ){
                            $candidateData = $this->saveCandidateData( $fileLineNo, $workData );
                            $inCandidateCount++;
                            $this->addWarningInfo($fileLineNo, $rowData, __('messages.warning.impport_batch.amount_mismatch'));
                            break;
                        }

                        // 対象受注が未入金以外
                        if( $orderData->payment_type != PaymentTypeEnum::NOT_PAID->value ){
                            // 対象受注の入金候補データがない
                            if( count( $candidateList ) == 0 ){
                                $candidateData = $this->saveCandidateData( $fileLineNo, $workData );
                                $inCandidateCount++;
                                $this->addWarningInfo($fileLineNo, $rowData, __('messages.warning.impport_batch.payment_type_wrong.candidate_none'));
                            }
                            // 対象受注の入金候補データがある
                            else{
                                $this->addWarningInfo($fileLineNo, $rowData, __('messages.warning.impport_batch.payment_type_wrong.candidate_exists'));
                            }
                            break;
                        }

                        // 消込する場合のみ、受注情報と紐づける
                        $workData->order_hdr_id = $orderData->t_order_hdr_id;
                        $workData->cust_id = $orderData->m_cust_id;
                        $workData->save();
                        // 入金候補データを作成：対象受注と紐づけ
                        $candidateData = $this->saveCandidateData( $fileLineNo, $workData, $orderData );
                        // 入金データを作成する
                        $paymentData = $this->savePaymentData( $candidateData, $orderData );
                        // 受注データを更新する
                        $orderData = $this->updateOrderData( $candidateData, $orderData );
                        // 処理件数を加算
                        $inCandidateCount++;
                        $inPaymentCount++;
                        break;

                    /**
                     * 返品
                     */
                    case $this::DEAL_STATUS_RETURN:
                    case $this::DEAL_STATUS_RETURNX:

                        // 対象受注の入金候補データが存在する
                        if( count( $candidateList ) > 0 ){
                            // 入金データが存在する
                            if( $isPaymentExists ){
                                // ワーニングのみ
                                $this->addWarningInfo($fileLineNo, $rowData, __('messages.warning.impport_batch.order_paid'));
                            }
                            // 入金データが存在しない
                            else{
                                // 入金候補データを全て論理削除
                                foreach( $candidateList as $candidade ){
                                    $candidade->delete_flg = DeleteFlg::Notuse->value;
                                    $candidade->update_operator_id = $this->batchExecute->m_operators_id; // 更新者ID
                                    $candidade->save();
                                }
                                // 消込する場合のみ、受注情報と紐づける
                                $workData->order_hdr_id = $orderData->t_order_hdr_id;
                                $workData->cust_id = $orderData->m_cust_id;
                                $workData->save();
                                $inReturnCount++;
                                $this->addWarningInfo($fileLineNo, $rowData, __('messages.warning.impport_batch.candidate_deleted'));
                            }
                        }
                        // 対象受注の入金候補データが存在しない
                        else{
                            // ワーニング
                            $this->addWarningInfo($fileLineNo, $rowData, __('messages.warning.impport_batch.candidate_not_found'));
                        }
                        break;

                    /**
                     * その他のデータ種別
                     */
                    default:
                        // 想定外のデータ種別はログだけ出して終了
                        Log::warning( __('messages.warning.impport_batch.record_type_not_covered', ['name' => '取引状態', 'data' => $rowData ]) );
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
            $resultMessage = __('messages.info.import_batch_result_payment_return', [
                'total' => count( $fileRows ), 
                'candidate' => $inCandidateCount,
                'payment' => $inPaymentCount,
                'return' => $inReturnCount
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
     * 入金候補データを作成・保存する
     */
    private function saveCandidateData( $sortOrder, $workData, $orderData = null ){
        $model = new BankPaymentCandidateModel();
        $model->t_execute_batch_instruction_id = $this->t_execute_batch_instruction_id;
        $model->sort_order = $sortOrder;
        $model->m_account_id = $this->batchExecute->m_account_id;
        $model->w_bank_payment_in_type = PaymentImportInTypeEnum::CREDIT->value; // 銀行入金取込種類
        $model->w_bank_payment_id = $workData->w_bank_payment_credit_id; // 銀行入金取込ID
        $model->payment_datetime = $this->payment_date; // 入金日
        $model->cust_payment_date = $this->payment_date; // 顧客入金日
        $model->account_payment_date = $this->payment_date; // 口座入金日
        $model->payment_name = null; // 入金者名
        $model->payment_amount = $workData->in_amount; // 入金額
        $model->payment_note = null; // 備考
        $model->t_order_hdr_id = null; // 受注ID
        $model->m_cust_id = null; // 顧客ID
        $model->payment_import_flg = PaymentImportFlgEnum::MISMATCH->value; // 入金取込フラグ
        $model->delete_flg = DeleteFlg::Use->value; // 削除フラグ

        // 入金ステータス初期値
        $model->payment_status = InputPaymentStatusEnum::NO_MATCH->value; // 該当なし

        // 対象受注が取れた場合
        if( !empty( $orderData ) ){
            // 入金ステータス, 入金取込フラグ
            $model->payment_status = InputPaymentStatusEnum::PART_MATCH->value;
            if( floor( $orderData->order_total_price ) == $workData->in_amount ){
                $model->payment_status = InputPaymentStatusEnum::FULL_MATCH->value;
                $model->payment_import_flg = PaymentImportFlgEnum::MATCH->value;
            }            
            // 受注IDと顧客ID
            $model->t_order_hdr_id = $orderData->t_order_hdr_id;
            $model->m_cust_id = $orderData->m_cust_id;
        }

        // 入金データ種別
        if( in_array( $workData->in_deal_status, [ $this::DEAL_STATUS_SALES, $this::DEAL_STATUS_CAPTURE ] ) ){
            $model->payment_data_type = PaymentImportDataTypeEnum::FIX->value;
        }
        if( in_array( $workData->in_deal_status, [ $this::DEAL_STATUS_RETURN, $this::DEAL_STATUS_RETURNX ] ) ){
            $model->payment_data_type = PaymentImportDataTypeEnum::CANCEL->value;
        }

        $model->entry_operator_id = $this->batchExecute->m_operators_id; // 登録者ID
        $model->update_operator_id = $this->batchExecute->m_operators_id; // 更新者ID
        $model->save();
        return $model;
    }

    /**
     * 入金データを作成・保存する
     */
    private function savePaymentData( $candidateData, $orderData ){
        $item = $this::getPaymentSubject( PaymentSubjectItemCodeEnum::CREDIT->value );

        $payment = new PaymentModel();
        $payment->m_account_id = $this->batchExecute->m_account_id; // 企業アカウントID
        $payment->delete_flg = DeleteFlg::Use->value; // 削除フラグ
        $payment->t_order_hdr_id = $candidateData->t_order_hdr_id; // 受注基本ID
        $payment->m_cust_id = $candidateData->m_cust_id; // 顧客ID
        $payment->payment_price = $candidateData->payment_amount; // 入金金額
        $payment->payment_entry_date = $candidateData->payment_datetime; // 入金登録日
        $payment->cust_payment_date = $candidateData->cust_payment_date; // 顧客入金日
        $payment->account_payment_date = $candidateData->account_payment_date; // 口座入金日
        $payment->payment_subject = empty( $item ) ? null : $item->m_itemname_types_id; // 入金科目
        $payment->payment_entry_way = $this::PAYMENT_ENTRY_WAY; // 入金登録方法
        $payment->payment_company = null; // 入金会社
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
        $orderData->payment_price = $candidateData->payment_amount; // 入金金額
        $orderData->payment_date = $candidateData->payment_datetime; // 入金日

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