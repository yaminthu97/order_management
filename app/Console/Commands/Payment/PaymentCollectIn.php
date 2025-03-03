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
use App\Models\Claim\Base\BankPaymentCollectInModel;
use App\Models\Order\Base\OrderHdrModel;
use App\Models\Order\Base\PaymentModel;
use App\Models\Order\Base\ShippingLabelModel;

use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Master\Gfh1207\Enums\BatchListEnum;

use App\Services\TenantDatabaseManager;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use DateTime;
use Exception;
use File;

/**
 * コレクト入金取込
 */
class PaymentCollectIn extends FileImportCommon
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:PaymentCollectIn {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'コレクト入金取込データファイルを取込、受注データに対する入金レコードを作成する';

    // 入金登録方法
    private const PAYMENT_ENTRY_WAY = 'コレクト登録';

    // レコード区分
    private const RECORD_TYPE_HEADER = '0'; // ヘッダレコード
    private const RECORD_TYPE_TRAILER = '8'; // トレーラレコード
    private const RECORD_TYPE_END = '9'; // エンドレコード
    // 取込対象の支払区分
    private const IMPORT_PAYMENT_TYPE = "5"; // 5:立替払い
    // 取込対象のデータ区分
    private const IMPORT_DATA_TYPE = "00"; // 00:売上伝票

    // jsonキー:入金ファイルパス
    protected $json_key_csv_path = 'csv_fullfile_path';
    // jsonキー:入金日
    protected $json_key_payment_date = 'payment_date';

    // ファイルエンコード(ebsdicは変則的なので全ての処理はこちらで行う)
    protected $import_file_encode = 'sjis';

    // 取込ファイル（明細レコード）フォーマット
    protected const FILE_RECORD_LENGTH = 84; // 1レコード長さ
    protected $import_file_format = [
        [
            'file_column_idx' => 0, 
            'file_column_name' => '支払区分', 
            'db_column_name' => 'payment_type', 
            'rule' => ['required' => true, 'fixedLength' => 1, 'in_data' => ['5', '6'] ] // 5:立替払い 6:集金払い
        ],
        [
            'file_column_idx' => 1, 
            'file_column_name' => 'お客様コード', 
            'db_column_name' => 'customer_code', 
            'rule' => ['required' => true, 'fixedLength' => 21, 'regex' => '/^[0-9a-zA-Z]+$/', 'trim' => true ] 
        ],
        [
            'file_column_idx' => 2, 
            'file_column_name' => '伝票番号', 
            'db_column_name' => 'shipping_label', 
            'rule' => ['required' => true, 'fixedLength' => 12, 'regex' => '/^[0-9a-zA-Z-_()]+$/', 'trim' => true ] 
        ],
        [
            'file_column_idx' => 3, 
            'file_column_name' => 'データ区分', 
            'db_column_name' => 'data_type', 
            'rule' => ['required' => true, 'fixedLength' => 2, 'in_data' => ['00', '20'] ] // 00:売上伝票 20:返品伝票
        ],
        [
            'file_column_idx' => 4, 
            'file_column_name' => '訂正区分', 
            'db_column_name' => 'correction_type', 
            'rule' => ['required' => true, 'fixedLength' => 2, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 5, 
            'file_column_name' => '発送日', 
            'db_column_name' => 'shipping_date',
            'rule' => ['required' => false, 'fixedLength' => 8, 'date' => ['format' => 'Ymd', 'new_format' => 'Y-m-d']  ]
        ],
        [
            'file_column_idx' => 6, 
            'file_column_name' => '品代金', 
            'db_column_name' => 'sell_amount', 
            'rule' => ['required' => true, 'fixedLength' => 8, 'regex' => '/^[+-][0-9]+$/', 'numeric' => true] 
        ],
        [
            'file_column_idx' => 7, 
            'file_column_name' => '代引手数料', 
            'db_column_name' => 'collect_fee', 
            'rule' => ['required' => true, 'fixedLength' => 6, 'regex' => '/^[+-][0-9]+$/', 'numeric' => true] 
        ],
        [
            'file_column_idx' => 8, 
            'file_column_name' => '印紙代', 
            'db_column_name' => 'revenue_stamp_fee', 
            'rule' => ['required' => true, 'fixedLength' => 4, 'regex' => '/^[+-][0-9]+$/', 'numeric' => true] 
        ],
        [
            'file_column_idx' => 9, 
            'file_column_name' => '返品日', 
            'db_column_name' => 'return_date',
            'rule' => ['required' => false, 'fixedLength' => 8, 'date' => ['format' => 'Ymd', 'new_format' => 'Y-m-d'], 'trim' => true, 'is_empty_value' => null  ]
        ],
        [
            'file_column_idx' => 10, 
            'file_column_name' => '返品伝票番号', 
            'db_column_name' => 'return_shipping_label', 
            'rule' => ['required' => false, 'fixedLength' => 12, 'regex' => '/^[0-9a-zA-Z]+$/', 'trim' => true ] 
        ],
    ];

    // 取込ファイル（ヘッダーレコード）フォーマット
    protected const FILE_HEADER_RECORD_LENGTH = 9; // 1レコード長さ
    private $import_header_format = [
        [
            'file_column_idx' => 0, 
            'file_column_name' => 'データ区分', 
            'db_column_name' => 'data_type', 
            'rule' => ['required' => true, 'fixedLength' => 1 ]
        ],
        [
            'file_column_idx' => 1, 
            'file_column_name' => 'ダウンロード日付', 
            'db_column_name' => 'file_created_date', 
            'rule' => ['required' => true, 'fixedLength' => 8, 'date' => ['format' => 'Ymd', 'new_format' => 'Y-m-d']  ]
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
            $this->initCommand( BatchListEnum::IMPDAT_PAYMENT_COLLECT->value );

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
            $recordCount = 0;
            foreach( $fileRows as $idx => $rowData ){
                $recordCount++;
                // 先頭1文字のデータ種別で判定
                $recordType = substr( $rowData, 0, 1 );
                switch( $recordType ){

                    // ヘッダレコード
                    case $this::RECORD_TYPE_HEADER:
                        /**
                         * ヘッダーレコードの解析
                         * 処理方針がぶれないように、共通フォーマット処理の流れに即した処理で行う
                         */
                        $headerRecord = $rowData;
                        $headerData = [];
                        // 長さチェック
                        $rowData = mb_convert_encoding( rtrim( $rowData ), "utf-8", $this->import_file_encode);
                        if( mb_strlen( $rowData ) != $this::FILE_HEADER_RECORD_LENGTH ){
                            throw new Exception(__('messages.error.impport_batch.file_record_error.length', ['rownum' => 1]));
                        }
                        // 項目パース
                        foreach( $this->import_header_format as $format ){
                            // 指定の文字数を取得し、残りの文字列から取得部分を削る
                            $value = substr( $rowData, 0, $format['rule']['fixedLength'] );
                            $rowData = substr( $rowData, $format['rule']['fixedLength'] );

                            $rule = $format['rule'];
                            if( !empty( ( $rule['date'] ?? null ) ) ){
                                // 日付項目のチェック
                                $dateFormat = $rule['date']['format'];
                                if( !$this->checkDate( $dateFormat, $value ) ){
                                    throw new Exception( __('messages.error.impport_batch.file_record_error.column_date_format', ['rownum' => 1, 'name' => $format['file_column_name']] ) );
                                }
                                // 必要に応じて日付フォーマットを変換した値を確保
                                $newDateFormat = $rule['date']['new_format'] ?? $rule['date']['format'];
                                $dt = DateTime::createFromFormat($dateFormat, $value);
                                $value = $dt->format( $newDateFormat );
                            }
                            $headerData[ $format['db_column_name'] ] = $value;
                        }
                        break;
                    // トレーラレコード
                    case $this::RECORD_TYPE_TRAILER:
                        $trailerRecord = $rowData;
                        break;
                    // エンドレコード
                    case $this::RECORD_TYPE_END:
                        $endRecord = $rowData;
                        break;
                    // その他レコードは明細レコードとして取得）
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
            $recordCount = 0; // レコード件数
            $inCandidateCount = 0; // 入金候補件数
            $inPaymentCount = 0; // 入金消込件数
            foreach( $dataRecordList as $dataRecord ){
                $recordCount++;
                $fileLineNo = $recordCount + 1; // ファイル行数：ヘッダレコードのぶん加算

                // 行データをモデルにfill出来る形式に変換
                $inputData = $this->createCollectInputData( $fileLineNo, $dataRecord );
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

                // ワークデータ登録
                $workData = new BankPaymentCollectInModel();
                $workData->fill( $inputData );
                $workData->t_execute_batch_instruction_id = $this->t_execute_batch_instruction_id;
                $workData->file_created_date = $headerData['file_created_date'];
                $workData->header_record = $headerRecord;
                // $workData->data_record = $dataRecord;
                $workData->trailer_record = $trailerRecord;
                $workData->end_record = $endRecord;
                $workData->collected_date = $this->payment_date;
                $workData->order_hdr_id = null;
                $workData->cust_id = null;
                $workData->amount = $inputData['sell_amount'];
                $workData->account_payment_date = $this->payment_date;
                $workData->save();

                // 支払区分チェック
                if( $inputData['payment_type'] != $this::IMPORT_PAYMENT_TYPE ){
                    $this->addWarningInfo($fileLineNo, $dataRecord, __('messages.warning.impport_batch.invalid_column', ['rownum' => $fileLineNo, 'name' => '支払区分']));
                    continue;
                }
                // データ区分チェック
                if( $inputData['data_type'] != $this::IMPORT_DATA_TYPE ){
                    $this->addWarningInfo($fileLineNo, $dataRecord, __('messages.warning.impport_batch.invalid_column', ['rownum' => $fileLineNo, 'name' => 'データ区分']));
                    continue;
                }

                /**
                 * 受注基本情報の取得
                 * 伝票番号を基に送り状データを検索し、受注IDを特定する
                 */
                $shippingLabelData = ShippingLabelModel::query()
                ->where('shipping_label_number', '=', $inputData['shipping_label'] )
                ->first();
                $orderData = empty( $shippingLabelData ) ? null : $shippingLabelData->orderHdr;
                if( empty($orderData) ){
                    $candidateData = $this->saveCandidateData( $recordCount, $inputData, $workData );
                    $inCandidateCount++;
                    $this->addWarningInfo($fileLineNo, $dataRecord, __('messages.warning.impport_batch.orderdata_not_found'));
                    continue;
                }

                // 対象受注が取れた時点でワークテーブルに書き戻す
                $workData->order_hdr_id = $orderData->t_order_hdr_id;
                $workData->cust_id = $orderData->m_cust_id;
                $workData->save();

                // 入金データの存在チェック
                $existsPayment = PaymentModel::query()
                ->where('t_order_hdr_id', $orderData->t_order_hdr_id)
                ->where('delete_flg', '=', DeleteFlg::Use->value)
                ->exists();
                if( $existsPayment ){
                    $candidateData = $this->saveCandidateData( $recordCount, $inputData, $workData );
                    $inCandidateCount++;
                    $this->addWarningInfo($fileLineNo, $dataRecord, __('messages.warning.impport_batch.order_paid'));
                    continue;
                }

                // 請求金額の一致チェック
                if( floor( $orderData->order_total_price ) != $workData->amount ){
                    $candidateData = $this->saveCandidateData( $recordCount, $inputData, $workData );
                    $inCandidateCount++;
                    $this->addWarningInfo($fileLineNo, $dataRecord, __('messages.warning.impport_batch.amount_mismatch'));
                    continue;
                }

                // 未入金かどうか
                if( $orderData->payment_type != PaymentTypeEnum::NOT_PAID->value ){
                    $candidateData = $this->saveCandidateData( $recordCount, $inputData, $workData );
                    $inCandidateCount++;
                    $this->addWarningInfo($fileLineNo, $dataRecord, __('messages.warning.impport_batch.payment_type_wrong.candidate_none'));
                    continue;
                }

                // 入金候補の登録
                $candidateData = $this->saveCandidateData( $recordCount, $inputData, $workData, $orderData );
                // 入金データの登録
                $paymentData = $this->savePaymentData( $candidateData, $orderData );
                // 対象受注の更新
                $orderData = $this->updateOrderData( $candidateData, $orderData );
                // 処理件数インクリメント
                $inPaymentCount++;
                $inCandidateCount++;
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
    protected function createCollectInputData( $rowNum, $rowData )
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
        $model = new BankPaymentCandidateModel();
        $model->t_execute_batch_instruction_id = $this->t_execute_batch_instruction_id;
        $model->sort_order = $sortOrder;
        $model->m_account_id = $this->batchExecute->m_account_id;
        $model->w_bank_payment_in_type = PaymentImportInTypeEnum::COLLECT->value; // コレクト入金取込
        $model->w_bank_payment_id = $workData->w_bank_payment_collect_id; // 入金取込ID
        $model->payment_datetime = $this->payment_date; // 入金日
        $model->cust_payment_date = $this->payment_date;
        $model->account_payment_date = $this->payment_date;
        $model->payment_name = null;
        $model->payment_amount = $workData->sell_amount;
        $model->payment_note = null;

        // 入金ステータスと入金取込フラグ
        $model->payment_status = InputPaymentStatusEnum::NO_MATCH->value; // 該当なし
        $model->payment_import_flg = PaymentImportFlgEnum::MISMATCH->value; // 不一致
        
        // 対象受注がある場合
        if( !empty( $orderData ) ){
            // 請求金額と料金を比較して「部分一致」「完全一致」を設定
            $model->payment_status = InputPaymentStatusEnum::PART_MATCH->value;
            if( $orderData->order_total_price == $workData->sell_amount ){
                $model->payment_status = InputPaymentStatusEnum::FULL_MATCH->value;
                $model->payment_import_flg = PaymentImportFlgEnum::MATCH->value; // 一致
            }            
            $model->t_order_hdr_id = $orderData->t_order_hdr_id; // 受注ID
            $model->m_cust_id = $orderData->m_cust_id; // 顧客ID
        }

        $model->delete_flg = DeleteFlg::Use->value; // 削除フラグ
        $model->payment_data_type = PaymentImportDataTypeEnum::FIX->value;
        $model->entry_operator_id = $this->batchExecute->m_operators_id; // 登録者ID
        $model->update_operator_id = $this->batchExecute->m_operators_id; // 更新者ID

        $model->save();
        return $model;
    }

    /**
     * 入金データを作成・保存する
     */
    private function savePaymentData( $candidateData, $orderData ){
        $item = $this::getPaymentSubject( PaymentSubjectItemCodeEnum::COLLECT->value );

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