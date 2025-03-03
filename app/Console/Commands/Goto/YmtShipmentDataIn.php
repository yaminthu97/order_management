<?php

namespace App\Console\Commands\Goto;

use App\Console\Commands\Common\FileImportCommon;
use App\Enums\BatchExecuteStatusEnum;
use App\Models\Master\Base\ShopGfhModel;
use App\Models\Order\Gfh1207\ShippingLabelModel;
use App\Modules\Master\Gfh1207\Enums\BatchListEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Events\ModuleStarted;
use App\Events\ModuleFailed;
use App\Services\TenantDatabaseManager;

use DateTime;
use Exception;
use Storage;

/**
 * ヤマト発送データ取込
 */
class YmtShipmentDataIn extends FileImportCommon
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:YmtShipmentDataIn {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ヤマト発送データをFTPダウンロードし取り込む';

    /**
     * SFTPサーバ情報
     */
    const FTP_DRIVER = 'sftp';
    const FTP_PORT = 22;
    const FTP_DOWNLOAD_FILE = 'hassou.txt';

    /**
     * ファイルフォーマット
     */
    protected const FILE_RECORD_LENGTH = 128; // 1レコード長さ
    protected $import_file_format = [
        [
            'file_column_idx' => 0, 
            'file_column_name' => '運送事業者発店コード', 
            'db_column_name' => 'store_code', 
            'rule' => ['required' => true, 'fixedLength' => 6]
        ],
        [
            'file_column_idx' => 1, 
            'file_column_name' => '予備', 
            'db_column_name' => 'reserve1', 
            'rule' => ['required' => true, 'fixedLength' => 2] 
        ],
        [
            'file_column_idx' => 2, 
            'file_column_name' => '運送依頼番号', 
            'db_column_name' => 'order_no', 
            'rule' => ['required' => true, 'fixedLength' => 12] 
        ],
        [
            'file_column_idx' => 3, 
            'file_column_name' => '運送依頼番号（枝番）', 
            'db_column_name' => 'order_branch_no', 
            'rule' => ['required' => true, 'fixedLength' => 3]
        ],
        [
            'file_column_idx' => 4, 
            'file_column_name' => '情報区分コード', 
            'db_column_name' => 'info_code1', 
            'rule' => ['required' => true, 'fixedLength' => 2] 
        ],
        [
            'file_column_idx' => 5, 
            'file_column_name' => '処理時刻', 
            'db_column_name' => 'process_time',
            'rule' => ['required' => false, 'fixedLength' => 4]
        ],
        [
            'file_column_idx' => 6, 
            'file_column_name' => 'データ処理No', 
            'db_column_name' => 'data_no', 
            'rule' => ['required' => true, 'fixedLength' => 6] 
        ],
        [
            'file_column_idx' => 7, 
            'file_column_name' => '運送送り状番号', 
            'db_column_name' => 'shipping_label_number', 
            'rule' => ['required' => true, 'fixedLength' => 12, 'trim' => true] 
        ],
        [
            'file_column_idx' => 8, 
            'file_column_name' => '出荷日', 
            'db_column_name' => 'delivery_date_yamato', 
            'rule' => ['required' => true, 'fixedLength' => 8, 'date' => ['format' => 'Ymd', 'new_format' => 'Y-m-d']  ]
        ],
        [
            'file_column_idx' => 9, 
            'file_column_name' => '入力時刻', 
            'db_column_name' => 'input_time',
            'rule' => ['required' => true, 'fixedLength' => 4]
        ],
        [
            'file_column_idx' => 10, 
            'file_column_name' => '運送事業者着店コード', 
            'db_column_name' => 'arrival_code', 
            'rule' => ['required' => true, 'fixedLength' => 6] 
        ],
        [
            'file_column_idx' => 11, 
            'file_column_name' => '予備', 
            'db_column_name' => 'reserve2', 
            'rule' => ['required' => true, 'fixedLength' => 2] 
        ],
        [
            'file_column_idx' => 12, 
            'file_column_name' => '着荷予定日', 
            'db_column_name' => 'delivery_complete_date', 
            'rule' => ['required' => true, 'fixedLength' => 8, 'date' => ['format' => 'Ymd', 'new_format' => 'Y-m-d']  ]
        ],
        [
            'file_column_idx' => 13, 
            'file_column_name' => '情報区分コード', 
            'db_column_name' => 'info_code2', 
            'rule' => ['required' => true, 'fixedLength' => 1] 
        ],
        [
            'file_column_idx' => 14, 
            'file_column_name' => '寸法単位コード', 
            'db_column_name' => 'size_unit_code', 
            'rule' => ['required' => true, 'fixedLength' => 4] 
        ],
        [
            'file_column_idx' => 15, 
            'file_column_name' => '合計金額', 
            'db_column_name' => 'total_amount', 
            'rule' => ['required' => true, 'fixedLength' => 6] 
        ],
        [
            'file_column_idx' => 16, 
            'file_column_name' => '個数単位コード', 
            'db_column_name' => 'quantity_unit_code1', 
            'rule' => ['required' => true, 'fixedLength' => 1] 
        ],
        [
            'file_column_idx' => 17, 
            'file_column_name' => '個数単位コード', 
            'db_column_name' => 'quantity_unit_code2', 
            'rule' => ['required' => true, 'fixedLength' => 1] 
        ],
        [
            'file_column_idx' => 18, 
            'file_column_name' => '予備', 
            'db_column_name' => 'reserve3', 
            'rule' => ['required' => true, 'fixedLength' => 40] 
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
            // 取込ファイルの保存メッセージ、保存された時のみセットされる
            $fileSaveMessage = null;

            /**
             * 初期処理
             */
            $this->initCommand( BatchListEnum::IMPDAT_YMT_SHIPMENT_DATA->value );
            // 生成されたバッチ実行指示IDを確保
            $this->t_execute_batch_instruction_id = $this->batchExecute->t_execute_batch_instruction_id;

            DB::beginTransaction();

            // 接続情報を取得
            $shop = ShopGfhModel::first();
            if( empty( $shop ) ){
                throw new Exception(__('messages.error.sftp_connect_error'));
            }

            // SFTPサーバへ接続
            $disk = Storage::build([
                'driver' => $this::FTP_DRIVER,
                'host' => $shop->ftp_server_host_yamato,
                'port' => $this::FTP_PORT,
                'username' => $shop->ftp_server_user_yamato,
                'password' => $shop->ftp_server_password_yamato,
                'root' => null
            ]);
            // ファイルをダウンロード
            try{
                $contents = $disk->get( $this::FTP_DOWNLOAD_FILE );
            }
            catch( Exception $e ){
                // 接続でエラーが発生
                throw new Exception(__('messages.error.sftp_connect_error'));
            }
            // ファイルが取得できなかった場合
            if ( empty( $contents ) ) {
                throw new Exception(__('messages.error.download_file_not_found', ['filename' => $this::FTP_DOWNLOAD_FILE]));
            }

            // ローカルストレージにファイルを保存
            $ext = strrpos($this::FTP_DOWNLOAD_FILE, '.') ? substr($this::FTP_DOWNLOAD_FILE, strrpos($this::FTP_DOWNLOAD_FILE, '.') + 1) : '';
            $putFile = ( new DateTime() )->format('YmdHis') . ( empty( $ext ) ? : '.' . $ext );
            $putDirectory = $this->batchExecute->account_cd . $this::OUTPUT_CSV_BASE_PATH . $this->batch_id;
            $putFilePath = $putDirectory . DIRECTORY_SEPARATOR . $putFile;
            Storage::disk( config('filesystems.default', 'local') )->makeDirectory( $putDirectory );
            Storage::disk( config('filesystems.default', 'local') )->put($putFilePath, $contents);
            if( !Storage::disk( config('filesystems.default', 'local') )->exists($putFilePath) ){
                throw new Exception(__('messages.error.download_file_copy_error'));
            }
            // 取込ファイルの保存メッセージ
            $fileSaveMessage = __('messages.info.import_batch_file_save', ['path' => $putFilePath]);

            /**
             * バリデーションの準備と改行splitを行う 
             **/
            $this->createValidatorRules();
            $fileRows = $this->getFileRows( $contents );
            $executeCount = 0;
            foreach( $fileRows as $idx => $rowData ){
                // 改行のみのレコードは無視
                if( empty( $rowData ) ){
                    continue;
                }

                // ファイル行数：0 オリジン補正
                $fileLineNo = $idx + 1;
                // 行データを解析
                $inputData = $this->createInputData( $fileLineNo, $rowData, $rowData );
                $inputData = $this->convertInputData( $inputData );

                /**
                 * この時点でエラー情報が一件でも保存されている場合は以降の処理をしない
                 * ※エラー発生時点でrollback確定だが、以降は全てワーニングレベルのため処理する必要が無い
                 */
                if( !empty( $this->errors ) ){
                    continue;
                }

                // 送り状番号で対象を取得
                $data = ShippingLabelModel::query()
                ->where('shipping_label_number','=', $inputData['shipping_label_number'])
                ->first();

                // 送り状番号が無い
                if( empty( $data ) ){
                    $this->addWarningInfo($fileLineNo, $this->convertEncode( $rowData ), __('messages.warning.impport_batch.shipping_label_number_not_found'));
                    continue;
                }

                // 出荷日と着荷予定日をセットして更新
                $data->delivery_date_yamato = $inputData['delivery_date_yamato'];
                $data->delivery_complete_date = $inputData['delivery_complete_date'];
                $data->update_operator_id = $this->batchExecute->m_operators_id; // 更新者ID
                $data->save();

                $executeCount++;
            }

            // 全行処理後、エラー情報がある場合はexception
            if( !empty( $this->errors ) ){
                throw new Exception(__('messages.error.impport_batch.error_import_file'));
            }

            /**
             * 終了処理
             */
            $resultMessage = __('messages.info.import_batch_result', ['total' => count( $fileRows ), 'count' => $executeCount]);
            // 結果ファイルに出力するメッセージにはファイルの保存情報を追加
            $this->outputResultFile( $resultMessage . PHP_EOL . $fileSaveMessage );
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
            // ファイル保存メッセージがある場合は追記
            try{
                $this->outputErrorFile( $errorMessage . ( !empty( $fileSaveMessage ) ? PHP_EOL . $fileSaveMessage : '' ) );
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
     * srcDataとoptionはオーバーライドの整合性のためのダミーパラメータ
     */
    protected function createInputData( $rowNum, $rowData, $srcData, $options = [] )
    {
        // レコード長チェック
        if( mb_strlen( $rowData ) != $this::FILE_RECORD_LENGTH ){
            $this->addErrorInfo($this::ERROR_TYPE_IMPORT, $rowNum, $rowData, __('messages.error.impport_batch.file_record_error.length', ['rownum' => $rowNum] ));
            return null;
        }

        // 文字コードを変換
        $rowData = mb_convert_encoding( $rowData, "utf-8", $this->import_file_encode);

        // データレコードをコピー（参照引渡にならないように空文字をつなげる）
        $record = $rowData . '';
        $inputData = [];
        // 項目ごとの配列に変換する
        foreach( $this->import_file_format as $format ){
            $rule = $format['rule'];
            $inputData[] = substr( $record, 0, $rule['fixedLength'] );
            $record = substr( $record, $rule['fixedLength'] );
        }
        // 親クラスに定義されているチェック処理を通してinputDataを作成する
        $inputData =  parent::createInputData( $rowNum, $inputData, $rowData );
        return $inputData;
    }
}