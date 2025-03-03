<?php

namespace App\Console\Commands\Common;

use App\Enums\BatchExecuteStatusEnum;
use App\Enums\DeleteFlg;
use App\Enums\ItemNameType;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;

use App\Models\Common\MasterAccountModel;
use App\Models\Master\Base\ItemnameTypeModel;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;

use App\Services\TenantDatabaseManager;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use DateTime;
use Exception;
use File;
use Storage;
use Validator;

/**
 * ファイル取込共通
 */
class FileImportCommon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:FileImportCommon {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '入金情報CSV取り込みの共通';

    // バッチID
    protected $batch_id = null;
    // バッチ実行情報
    protected $batchExecute = null;
    // バッチ実行指示ID
    protected $t_execute_batch_instruction_id = null;

    // jsonパラメータ
    protected $json_data = null;
    // jsonキー:入金ファイルパス
    protected $json_key_csv_path;
    // jsonキー:入金日
    protected $json_key_payment_date;
    // jsonデータ:入金ファイルパス
    protected $import_csv_path;
    // jsonデータ:入金日
    protected $payment_date;

    // 出力ファイルの基本パス( storage/../(アカウントCD) の下)
    public const OUTPUT_CSV_BASE_PATH = '/csv/import/';
    public const OUTPUT_TEXT_BASE_PATH = '/text/import/';
    // エラー詳細の種別：ファイル取込エラー
    public const ERROR_TYPE_IMPORT = 'import';
    // エラーファイルパス
    protected $error_file_path = null;
    // 処理結果ファイルパス
    protected $result_file_path = null;
    // エラーファイルのsuffix
    protected $error_file_sufix = '_error';
    // 処理結果ファイルのsuffix
    protected $result_file_sufix = '_result';
    // エラー情報
    protected $errors = [];
    // ワーニング情報
    protected $warnings = [];

    // ファイルエンコード(utf8以外の場合のみ設定)
    protected $import_file_encode = null;
    // 取込ファイルとワークテーブルの紐付け
    protected $import_file_format;
    // 取込ファイルポインタ
    protected $import_fp = null;
    // バリデーションrule
    protected $rules = null;

    /**
     * Create a new command instance.
     */
    public function __construct(
        StartBatchExecuteInstructionInterface $startBatchExecute,
        EndBatchExecuteInstructionInterface $endBatchExecute,
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;

        parent::__construct();
    }

    /**
     * 初期処理
     */
    protected function initCommand( $batch_id ) {
        // バッチ種別を設定
        $this->batch_id = $batch_id;

        /**
         * コマンド引数の設定
         */
        $args = $this->argument();
        $options = [
            'execute_batch_type' => $this->batch_id
        ];
        if( isset( $args['t_execute_batch_instruction_id'] ) && $args['t_execute_batch_instruction_id'] != 'null' ){
            $this->t_execute_batch_instruction_id = $args['t_execute_batch_instruction_id'];
            $options['t_execute_batch_instruction_id'] = $args['t_execute_batch_instruction_id'];
        }
        if( isset( $args['json'] ) ){
            $this->json_data = $args['json'];
            $options['execute_conditions'] = $args['json'];
            // アカウントIDがjsonパラメータにある場合はバッチ実行パラメータとして外出しにする
            $json = json_decode($this->json_data, true);
            if( isset( $json['m_account_id'] ) ){
                $options['m_account_id'] = $json['m_account_id'];
            }
        }

        /**
         * バッチ開始処理
         */
        $this->batchExecute = $this->startBatchExecute->execute( 
            $this->t_execute_batch_instruction_id, 
            $options
        );
        // DB接続
        $dbName = $this->batchExecute->account_cd . ( app()->environment('testing') ? '_db_testing' : '_db' );
        TenantDatabaseManager::setTenantConnection( $dbName );
    }

    /**
     * 終了処理
     */
    protected function finalizeCommand( $execute_result, $execute_status, $result_file_path, $error_file_path ) {
        $params = [
            'execute_result' => $execute_result,
            'execute_status' => $execute_status
        ];
        // 正常終了（ワーニング込みの可能性あり）
        if( !empty( $result_file_path ) ){
            $params['file_path'] = $result_file_path;
            // ワーニングメッセージが取得できた場合は、メッセージを追加する
            $warningMessages = $this->getWarningMessages();
            if( !empty( $warningMessages ) ){
                $params['execute_result'] .= PHP_EOL . __('messages.info.import_batch_result_partial_error');
            }
        }
        // エラー終了
        if( !empty( $error_file_path ) ){
            $params['error_file_path'] = $error_file_path;
        }
        $this->batchExecute = $this->endBatchExecute->execute($this->batchExecute, $params);
    }

    /**
     * jsonパラメータのチェックと取得
     */
    protected function checkJsonParameter() {
        $searchInfo = json_decode($this->json_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception(__('messages.error.impport_batch.invalid_parameter', ['key' => $this->json_data]));
        }
        // jsonパラメータの必須要素チェック
        if( !array_key_exists( $this->json_key_csv_path, $searchInfo ) || empty( $searchInfo[ $this->json_key_csv_path ] ) ){
            throw new Exception(__('messages.error.impport_batch.invalid_parameter', ['key' => $this->json_key_csv_path]));
        }
        if( !array_key_exists( $this->json_key_payment_date, $searchInfo ) || empty( $searchInfo[ $this->json_key_payment_date ] ) ){
            throw new Exception(__('messages.error.impport_batch.invalid_parameter', ['key' => $this->json_key_payment_date]));
        }
        // 日付パラメータのチェック
        if( !$this->checkDate( 'Y-m-d', $searchInfo[ $this->json_key_payment_date ] ) ){
            throw new Exception(__('messages.error.impport_batch.invalid_parameter', ['key' => $this->json_key_payment_date]));
        }
        // ファイルチェック
        if( !File::exists( $searchInfo[ $this->json_key_csv_path ] ) || !is_file( $searchInfo[ $this->json_key_csv_path ] ) ){
            throw new Exception(__('messages.error.impport_batch.no_import_file'));
        }

        // jsonの設定値を確保
        $this->import_csv_path = $searchInfo[ $this->json_key_csv_path ];
        $this->payment_date = $searchInfo[ $this->json_key_payment_date ];
    }

    /**
     * 取込ファイルチェック
     * ファイルフォーマット情報を基にバリデーションルール作成
     */
    protected function createValidatorRules( $formatInfo = null ){
        $this->rules = [];
        $formatInfo = empty( $formatInfo ) ? $this->import_file_format : $formatInfo;
        if( empty( $formatInfo ) ){
            return;
        }
        foreach( $formatInfo as $format ){
            if( !empty( $format['rule'] ) ){
                $this->rules[ $format['db_column_name'] ] = $format['rule'];
            }
        }
        return $this->rules;
    }

    /**
     * 行データをDBにfillable出来る連想配列に変換する
     */
    protected function createInputData( $rowNum, $rowData, $srcData, $options = [] ){
        $inputData = [];
        $errorMessages = [];

        // 項目数チェック
        if( count( $rowData ) != count( $this->import_file_format ) ) {
            $errorMessages[] = $this->getErrorMessageColumnCount( $rowNum, $options );
        }
        // 項目数が一致した場合
        else {
            // ファイルフォーマットを基に項目を1つずつ取り出す
            foreach( $this->import_file_format as $format ){
                // ファイルの項目インデックス
                $fileColumnIdx = $format['file_column_idx'];
                // ファイルの項目名
                $fileColumnName = $format['file_column_name'];
                // DBテーブルの項目名
                $dbColumnName = $format['db_column_name'];
                // チェックルール
                $rule = $format['rule'] ?? [];
                // ファイルの項目設定値
                $value = $rowData[ $fileColumnIdx ];

                // 必須チェック
                if( ( $rule['required'] ?? false ) == true ){
                    if( strlen( $value ) == 0 ){
                        $errorMessages[] = $this->getErrorMessageColumnRequired( $rowNum, $fileColumnName, $options );
                    }
                }

                // 最大バイト数チェック
                if( !empty( ( $rule['byteMaxLength'] ?? null ) ) ){
                    $length = $rule['byteMaxLength'];
                    if( strlen( $value ) > $length ){
                        $errorMessages[] = $this->getErrorMessageColumnByteLonger( $rowNum, $fileColumnName, $length, $options );
                    }
                }

                // 最小バイト数チェック（入力されている場合のみ）
                if( !empty( ( $rule['byteMinLength'] ?? null ) ) ){
                    $length = $rule['byteMinLength'];
                    if( strlen( $value ) > 0  && strlen( $value ) < $length ){
                        $errorMessages[] = $this->getErrorMessageColumnByteShort( $rowNum, $fileColumnName, $length, $options );
                    }
                }

                // 固定長チェック
                if( !empty( ( $rule['fixedLength'] ?? null ) ) ){
                    $length = $rule['fixedLength'];
                    if( mb_strlen( $value ) != $length ){
                        $errorMessages[] = $this->getErrorMessageColumnFixedLength( $rowNum, $fileColumnName, $length, $options );
                    }
                }

                /**
                 * 文字列比較や正規表現チェックの前にutf8に変換する
                 *   1. utf8エンコードのDBに格納するため最終的には変換する
                 *   2. sjisだと2バイト文字がutf8だと3バイト以上になるため、バイト数チェックはsjisで行う
                 * 、3. utf8環境でsjisに対する全角文字チェックが難しいため、正規表現の前にutf8にする
                 */
                // if( !empty( $this->import_file_encode ) ){
                //     $value = mb_convert_encoding($value, "utf-8", $this->import_file_encode);
                // }
                $value = $this->convertEncode( $value );

                // trim処理
                if( ( $rule['trim'] ?? false ) == true ){
                    $value = trim( $value );
                }

                // 何らかの値が設定されている
                if( mb_strlen( $value ) > 0 ){
                    // 日付フォーマットチェック
                    if( !empty( ( $rule['date'] ?? null ) ) ){
                        // 日時に文字が紛れ込むとExceptionが発生する為、try-catchで拾う
                        $dateFormat = $rule['date']['format'];
                        if( !$this->checkDate( $dateFormat, $value ) ){
                            $errorMessages[] = $this->getErrorMessageColumnDateFormat( $rowNum, $fileColumnName, $options );
                        }
                    }

                    // 対象値フォーマットチェック
                    if( !empty( ( $rule['in_data'] ?? null ) ) ){
                        // 日時に文字が紛れ込むとExceptionが発生する為、try-catchで拾う
                        $dataList = $rule['in_data'];
                        if( !in_array( $value, $dataList ) ){
                            $errorMessages[] = $this->getErrorMessageColumnNotIn( $rowNum, $fileColumnName, $options );
                        }
                    }

                    // 正規表現チェック
                    if( !empty( ( $rule['regex'] ?? null ) ) ){
                        $regex = $rule['regex'];
                        if( preg_match($regex, $value) == false ){
                            $errorMessages[] = $this->getErrorMessageColumnRegex( $rowNum, $fileColumnName, $options );
                        }
                    }

                    // 数値化処理
                    if( ( $rule['numeric'] ?? false ) == true ){
                        $value = intval( $value );
                    }
                }
                // 空文字の場合
                else{
                    // is_empty_valueオプションがあるる場合は設定する
                    if( array_key_exists('is_empty_value', $rule) ){
                        $value = $rule['is_empty_value'];
                    }
                }

                $inputData[ $dbColumnName ] = $value;
            }
        }

        // エラーがある場合は保存して戻り値をnullにする
        if( !empty( $errorMessages ) ){
            $this->addErrorInfo($this::ERROR_TYPE_IMPORT, $rowNum, $srcData, $errorMessages);
            $inputData = null;
        }

        return $inputData;
    }


    /**
     * 入力データを必要に応じて形式変換する
     */
    protected function convertInputData( $inputData ){
        if( !empty( $inputData ) ){
            // 形式変換が必要な項目を処理する
            foreach( $this->import_file_format as $format ){
                $rule = $format['rule'] ?? [];
                $columnName = $format['db_column_name'];
                $value = $inputData[ $columnName ];
                if( empty( $value ) ){
                    continue;
                }

                // 日付フォーマット変換
                if( !empty( ( $rule['date'] ?? null ) ) ){
                    // 新旧フォーマットを取得
                    $format = $rule['date']['format'] ?? null;
                    $newFormat = $rule['date']['new_format'] ?? null;
                    if( empty($newFormat) ){
                        continue;
                    }
                    // 新フォーマットに変換してセットし直す
                    $dt = DateTime::createFromFormat($format, $value);
                    $inputData[ $columnName ]= $dt->format($newFormat);
                }
            }
        }
        return $inputData;
    }

    /**
     * ワーニング情報追加
     */
    protected function addWarningInfo($rowNum, $rowData, $message) {
        $this->warnings[] = [
            'rowNum' => $rowNum,
            'data' => $rowData, 
            'message' => $message
        ];
    }

    /**
     * エラー情報追加
     */
    protected function addErrorInfo($type, $rowNum, $rowData, $messages) {
        // メッセージが単発で送られてきた場合は配列化する
        if( !is_array( $messages ) ){
            $messages = [ $messages ];
        }
        $this->errors[] = [
            'type' => $type,
            'rowNum' => $rowNum,
            'rowData' => $rowData,
            'messages' => $messages
        ];
    }

    /**
     * ファイル出力
     * エラーファイル
     */
    protected function outputErrorFile( $message ) {
        // 出力先ディレクトリの作成
        $dir = $this->batchExecute->account_cd . $this::OUTPUT_TEXT_BASE_PATH . $this->batch_id;
        Storage::disk( config('filesystems.default', 'local') )->makeDirectory($dir);

        // 出力情報の基本部分
        $contents = ( new DateTime() )->format('Y-m-d H:i:s') . PHP_EOL;
        $contents .= "t_execute_batch_instruction_id:" . $this->t_execute_batch_instruction_id . PHP_EOL;
        $contents .= $message . PHP_EOL . PHP_EOL;

        /**
         * 出力内容詳細の生成
         */
        foreach( $this->errors as $error ){

            switch( $error['type'] ){

                /**
                 * 入金取込エラー
                 */
                case $this::ERROR_TYPE_IMPORT:
                    // 行番号
                    $contents .= "行:" . $error['rowNum'] . PHP_EOL;
                    // 発生エラー情報
                    foreach( $error['messages'] as $text ){
                        $contents .= $text . PHP_EOL;
                    }
                    // 行データ
                    $contents .= ( is_array($error['rowData']) ? implode(",", $error['rowData']) : $error['rowData'] ) . PHP_EOL . PHP_EOL;
                    break;

                default:
                    // 設計上、ここを通ることはありえない
                    break;
            }
        }

        // ファイル出力
        $this->error_file_path = $dir . DIRECTORY_SEPARATOR . $this->t_execute_batch_instruction_id . $this->error_file_sufix . '.txt';
        $result = Storage::disk( config('filesystems.default', 'local') )->put($this->error_file_path, $contents);
        if( !$result ){
            $message = __('messages.error.file_ooutput_error', ['filename' => $this->error_file_path]);
            $this->error_file_path = null;
            throw new Exception($message);
        }
        return true;
    }

    /**
     * ファイル出力
     * 処理結果ファイル
     */
    protected function outputResultFile( $message ) {
        // 出力先ディレクトリの作成
        $dir = $this->batchExecute->account_cd . $this::OUTPUT_TEXT_BASE_PATH . $this->batch_id;
        Storage::disk( config('filesystems.default', 'local') )->makeDirectory($dir);

        // 出力内容の共通部
        $contents = ( new DateTime() )->format('Y-m-d H:i:s') . PHP_EOL;
        $contents .= "t_execute_batch_instruction_id:" . $this->t_execute_batch_instruction_id . PHP_EOL;
        $contents .= $message . PHP_EOL . PHP_EOL;

        // ワーニングメッセージを追加
        $contents .= $this->getWarningMessages();

        // ファイル出力
        $this->result_file_path = $dir . DIRECTORY_SEPARATOR . $this->t_execute_batch_instruction_id . $this->result_file_sufix . '.txt';
        $result = Storage::disk( config('filesystems.default', 'local') )->put($this->result_file_path, $contents);

        if( !$result ){
            throw new Exception(__('messages.error.file_ooutput_error', ['filename' => $this->result_file_path]));
        }
        return true;
    }

    /**
     * ワーニングメッセージ本体生成
     */
    protected function getWarningMessages() {
        $messages = "";
        // 出力内容詳細の生成
        foreach( $this->warnings as $warning ){
            if( is_array( $warning ) ){
                // 行番号
                $messages .= "行:" . $warning['rowNum'] . PHP_EOL;
                // ワーニングメッセージ
                $messages .= $warning['message'] . PHP_EOL;
                // データ
                if( !empty( $warning['data'] ) ){
                    $messages .= ( is_array($warning['data']) ? json_encode( $warning['data'] ) : $warning['data'] ) . PHP_EOL;
                }
            }
            else {
                $messages = $warning;
            }
            $messages .= PHP_EOL;
        }
        return $messages;
    }

    /**
     * 日付チェック
     */
    protected function checkDate( $format, $value ){
        $dt = DateTime::createFromFormat($format, $value);
        if( !$dt || $value != $dt->format( $format ) ){
            return false;
        }
        return true;
    }

    /**
     * 日付フォーマット変換
     */
    protected function convertDateFormat( $nowFormat, $newFormat, $data ){
        // 現在のフォーマットで日付が正しいかチェック
        if( !$this->checkDate( $nowFormat, $data ) ){
            return null;
        }
        $dt = DateTime::createFromFormat($nowFormat, $data);
        return $dt->format( $newFormat );
    }

    /**
     * 入金科目取得
     * コンビニ・郵便振込の場合に項目コードが違うので、項目コードはパラメータで受け取る
     */
    protected function getPaymentSubject( $m_itemname_type_code ){
        $item = ItemnameTypeModel::query()
        ->where('m_itemname_type', '=', ItemNameType::Deposit->value)
        ->where('delete_flg', '=', DeleteFlg::Use->value)
        ->where('m_account_id', '=', $this->batchExecute->m_account_id)
        ->where('m_itemname_type_code', '=', $m_itemname_type_code)
        ->orderBy('m_itemname_types_id', 'ASC')
        ->first();
        return $item;
    }

    /**
     * ファイル、もしくは文字データを改行でsplitする
     */
    protected function getFileRows( $contents = null ){
        if( empty( $this->import_csv_path ) && empty( $contents ) ){
            return [];
        }

        $data = $contents;
        if( empty( $contents ) && !empty( $this->import_csv_path ) ){
            $data = file_get_contents( $this->import_csv_path );
        }

        $data = str_replace(array("\r\n","\r"), "\n", $data);
        $list = explode("\n", $data);

        $ret = [];
        foreach( $list as $row ){
            if( empty( $row ) ){
                continue;
            }
            $ret[] = $row;
        } 
        return $ret;
    }

    /**
     * 文字エンコードを変換する
     */
    protected function convertEncode( $value ){
        if( !empty( $this->import_file_encode ) ){
            return mb_convert_encoding($value, "utf-8", $this->import_file_encode);
        }
        return $value;
    }

    /**
     * エラーメッセージ取得：ファイル項目数エラー
     */
    protected function getErrorMessageColumnCount( $rowNum, $options = [] ){
        return __('messages.error.impport_batch.file_record_error.column_count', ['rownum' => $rowNum]);
    }
    /**
     * エラーメッセージ取得：必須項目エラー
     */
    protected function getErrorMessageColumnRequired( $rowNum, $columnName, $options = [] ){
        return __('messages.error.impport_batch.file_record_error.column_empty', ['rownum' => $rowNum, 'name' => $columnName] );
    }
    /**
     * エラーメッセージ取得：最大バイト数エラー
     */
    protected function getErrorMessageColumnByteLonger( $rowNum, $columnName, $length, $options = [] ){
        return __('messages.error.impport_batch.file_record_error.column_byte_longer', ['rownum' => $rowNum, 'name' => $columnName, 'length' => $length] );
    }
    /**
     * エラーメッセージ取得：最小バイト数エラー
     */
    protected function getErrorMessageColumnByteShort( $rowNum, $columnName, $length, $options = [] ){
        return __('messages.error.impport_batch.file_record_error.column_byte_short', ['rownum' => $rowNum, 'name' => $columnName, 'length' => $length] );
    }
    /**
     * エラーメッセージ取得：固定長エラー
     */
    protected function getErrorMessageColumnFixedLength( $rowNum, $columnName, $length, $options = [] ){
        return __('messages.error.impport_batch.file_record_error.column_fixed_length', ['rownum' => $rowNum, 'name' => $columnName, 'length' => $length] );
    }
    /**
     * エラーメッセージ取得：日付フォーマット
     */
    protected function getErrorMessageColumnDateFormat( $rowNum, $columnName, $options = [] ){
        return __('messages.error.impport_batch.file_record_error.column_date_format', ['rownum' => $rowNum, 'name' => $columnName] );
    }
    /**
     * エラーメッセージ取得：対象外エラー
     */
    protected function getErrorMessageColumnNotIn( $rowNum, $columnName, $options = [] ){
        return __('messages.error.impport_batch.file_record_error.column_value_not_correct', ['rownum' => $rowNum, 'name' => $columnName] );
    }
    /**
     * エラーメッセージ取得：正規表現エラー
     */
    protected function getErrorMessageColumnRegex( $rowNum, $columnName, $options = [] ){
        return  __('messages.error.impport_batch.file_record_error.column_format', ['rownum' => $rowNum, 'name' => $columnName] );
    }
}