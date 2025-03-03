<?php

namespace App\Console\Commands\Customer;

use App\Enums\BatchExecuteStatusEnum;
use App\Models\Cc\Gfh1207\CustModel;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Common\Gfh1207\CheckBatchParameter;
use App\Modules\Order\Gfh1207\GetCsvExportFilePath;
use App\Services\TenantDatabaseManager;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CustOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CustOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '顧客検索画面で選択した顧客の一覧を作成し、バッチ実行確認へと出力する';

    // バッチ名
    protected $batchName = '顧客一覧出力';

    // disk name
    protected $disk;

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    // parameter check
    protected $checkBatchParameter;

    // parameter check
    protected $getCsvExportFilePath;

    // const variable
    private const PRIVATE_THROW_ERR_CODE = -1;
    private const EMPTY_DATA_ROW_COUNT = 0;  // specify empty value

    /**
     * Create a new command instance.
     */
    public function __construct(
        StartBatchExecuteInstructionInterface $startBatchExecute,
        EndBatchExecuteInstructionInterface $endBatchExecute,
        CheckBatchParameter $checkBatchParameter,
        GetCsvExportFilePath $getCsvExportFilePath,
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->checkBatchParameter = $checkBatchParameter;
        $this->getCsvExportFilePath = $getCsvExportFilePath;

        // get disk name s3
        $this->disk = config('filesystems.default', 'local');

        parent::__construct();
    }

    /**
     * Execute the console command.
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
            $batchType = $batchExecute->execute_batch_type;
            $accountCode = $batchExecute->account_cd;

            // 環境チェック
            if (app()->environment('testing')) {
                // テスト環境の場合
                TenantDatabaseManager::setTenantConnection($accountCode . '_db_testing');
            } else {
                // 本番環境の場合
                TenantDatabaseManager::setTenantConnection($accountCode . '_db');
            }
        } catch (Exception $e) {
            // Write the log in laravel.log for error message
            Log::error('error_message : ' . $e->getMessage());
            return;
        }

        DB::beginTransaction();
        try {
            $param = $this->argument('json');

            // 必須パラメータの確認
            $paramKey = ['m_cust_id'];

            // to check batch json parameter
            $checkResult = $this->checkBatchParameter->execute($this->argument('json'), $paramKey);

            if (!$checkResult) {
                // [パラメータが不正です。] message save to 'execute_result'
                throw new \Exception(__('messages.error.invalid_parameter'), self::PRIVATE_THROW_ERR_CODE);
            }

            // パラメータをデコードし、検索情報を取得
            $searchInfo = json_decode($this->argument('json'), true)['search_info'];

            // m_cust_id のチェック（配列であること、空ではないことを確認）
            if (!is_array($searchInfo['m_cust_id']) || $searchInfo['m_cust_id'] == null || count($searchInfo['m_cust_id']) == self::EMPTY_DATA_ROW_COUNT) {
                // [パラメータが不正です。] message save to 'execute_result'
                throw new \Exception(__('messages.error.invalid_parameter'), self::PRIVATE_THROW_ERR_CODE);
            }

            // 顧客データの取得
            $customerResults = $this->getCustomerData($searchInfo);

            // データがない場合の処理
            if (count($customerResults) == self::EMPTY_DATA_ROW_COUNT) {
                // [出力対象のデータがありませんでした。] message save to 'execute_result'
                $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                    'execute_result' =>  __('messages.error.data_not_found2', ['datatype' => '出力']),
                    'execute_status' => BatchExecuteStatusEnum::SUCCESS->value
                ]);

                DB::commit();
                return ;
            }

            // CSV データ作成
            $customerCsvContents = $this->convertToCsv($customerResults);

            // 一意なファイル名を生成
            $fileName = 'customer_' . Carbon::now()->format('YmdHis');

            // エクスポートされた CSV ファイルを保存するためのファイル パスを生成します。
            $savePath =  $this->getCsvExportFilePath->execute($accountCode, $batchType, $fileName);

            // S3 に保存
            $fileSaveOnS3 = Storage::disk($this->disk)->put($savePath, $customerCsvContents);

            // ファイル保存失敗時の処理
            if (!$fileSaveOnS3) {
                // if the file fails to be saved, it will also terminate abnormally (the return value of put() should be false).
                throw new Exception(__('messages.error.upload_s3_failed'), self::PRIVATE_THROW_ERR_CODE);
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
                'execute_result' => (__('messages.info.notice_output_count', ['count' => "顧客一覧出力が" . count($customerResults)])), // [顧客一覧出力が〇〇件出力しました。] message save to 'execute_result'
                'execute_status' => BatchExecuteStatusEnum::SUCCESS->value,
                'file_path' => $savePath,
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

    /**
     * Fetches detailed customer data including related information.
     *
     * This function retrieves customer data from the database along with associated
     * information such as customer rank, customer type, and order summary. The data
     * is filtered based on the provided customer IDs, sorted by customer ID, and
     * returned as an array.
     *
     * @param array $params Parameters containing filter criteria, including:
     *                      - 'm_cust_id' (array): List of customer IDs to fetch data for.
     *
     * @return array Returns an array of customer data, including related details.
     *
     * Relationships:
     * - `custRunk`: Retrieves the customer rank with the fields `m_itemname_types_id` and `m_itemname_type_name`.
     * - `customerType`: Retrieves the customer type with fields `m_itemname_type`, `m_itemname_types_id`, and `m_itemname_type_name`.
     * - `custOrderSum`: Retrieves order summary data for the customer.
     */
    private function getCustomerData($params)
    {

        // Execute the search customer data and convert the result to an array
        // Fetch the customer data from the CustModel with related models using eager loading
        $customerResults = CustModel::with([
            'custRunk' => function ($query) {
                // Fetch only the relevant fields from the m_itemname_types table
                $query->select('m_itemname_types_id', 'm_itemname_type_name');
            },
            'customerType' => function ($query) {
                // Fetch only the relevant fields from the m_itemname_types table
                $query->select('m_itemname_type', 'm_itemname_types_id', 'm_itemname_type_name');
            },
            'custOrderSum' => function ($query) {
                // Fetch only the relevant fields from the m_itemname_types table
                $query->select('m_cust_id', 'total_order_money', 'total_order_count', 'newest_order_date', 'total_unbilled_money', 'total_undeposited_money', 'total_remind_count', 'total_return_count');
            },
        ])
        ->whereIn('m_cust_id', $params['m_cust_id']) // Filter by m_cust_id from parameter
        ->orderBy('m_cust_id', 'asc') // Sort by m_cust_id ascending order
        ->get()->toArray(); // Retrieve the data as a collection and convert it to an array

        // Return the resulting array of customer data
        return $customerResults;
    }

    /**
     * Converts customer data into a CSV file
     *
     * This function takes an array of customer data, generates a CSV file with a predefined structure.
     *
     *
     * @param array $customerArray Array of customer data, with nested data for customer rank, type, and order summary.
     *
     * @return string The full file path of the saved temporary CSV file.
     */
    protected function convertToCsv($customerArray)
    {
        // Prepare the CSV header row with the column names
        $csvHeader = [
            '使用区分', '顧客ID', '顧客コード', '顧客ランク', '名前', 'フリガナ', '性別', '生年月日', 'メールアドレス',
            'メールアドレス２', 'メールアドレス３', 'メールアドレス４', 'メールアドレス５', '電話番号', '電話番号２',
            '電話番号３', '電話番号４', 'ＦＡＸ番号', '郵便番号', '都道府県', '市区町村', '番地', '建物名',
            '法人・団体名', '法人・団体名（フリガナ）', '部署名', '電話番号（勤務先）', '要注意顧客区分', '備考',
            '要注意顧客コメント', '自由項目１', '自由項目２', '自由項目３', '自由項目４', '自由項目５',
            '自由項目６', '自由項目７', '自由項目８', '自由項目９', '自由項目１０', '自由項目１１',
            '自由項目１２', '自由項目１３', '自由項目１４', '自由項目１５', '自由項目１６', '自由項目１７',
            '自由項目１８', '自由項目１９', '自由項目２０', '顧客区分', '割引率', 'DM配送方法 郵便',
            'DM配送方法 メール', '累計購入金額', '購入回数', '最新受注日', '未請求金額', '未入金金額', '累計督促回数', '累計返品回数'
        ];

        // Open a memory stream (in-memory file)
        $memoryStream = fopen('php://temp', 'r+');

        // Write the header row
        fputcsv($memoryStream, $csvHeader);

        // Iterate over each customer in the array to create CSV rows
        foreach ($customerArray as $row) {
            // Prepare a single CSV row by extracting necessary data
            $csvRow = [
                $row['delete_flg'] ?? null, // 用区分
                $row['m_cust_id'] ?? null, // 顧客ID
                $row['cust_cd'] ?? null, // 顧客コード
                $row['cust_runk']['m_itemname_type_name'] ?? null, // 顧客ランク
                $row['name_kanji'] ?? null, // 名前
                $row['name_kana'] ?? null, // フリガナ
                $row['sex_type'] ?? null, // 性別
                $row['birthday'] ?? null, // 生年月日
                $row['email1'] ?? null, // メールアドレス
                $row['email2'] ?? null, // メールアドレス２
                $row['email3'] ?? null, // メールアドレス３
                $row['email4'] ?? null, // メールアドレス４
                $row['email5'] ?? null, // メールアドレス５
                $row['tel1'] ?? null, // 電話番号
                $row['tel2'] ?? null, // 電話番号２
                $row['tel3'] ?? null, // 電話番号３
                $row['tel4'] ?? null, // 電話番号４
                $row['fax'] ?? null, // ＦＡＸ番号
                $row['postal'] ?? null, // 郵便番号
                $row['address1'] ?? null, // 都道府県
                $row['address2'] ?? null, // 市区町村
                $row['address3'] ?? null, // 番地
                $row['address4'] ?? null, // 建物名
                $row['corporate_kanji'] ?? null, // 法人・団体名
                $row['corporate_kana'] ?? null, // 法人・団体名（フリガナ）
                $row['division_name'] ?? null, // 部署名
                $row['corporate_tel'] ?? null, // 電話番号（勤務先）
                $row['alert_cust_type'] ?? null, // 要注意顧客区分
                $row['note'] ?? null, // 備考
                $row['alert_cust_comment'] ?? null, // 要注意顧客コメント
                $row['reserve1'] ?? null, // 自由項目１
                $row['reserve2'] ?? null, // 自由項目２
                $row['reserve3'] ?? null, // 自由項目３
                $row['reserve4'] ?? null, // 自由項目４
                $row['reserve5'] ?? null, // 自由項目５
                $row['reserve6'] ?? null, // 自由項目６
                $row['reserve7'] ?? null, // 自由項目７
                $row['reserve8'] ?? null, // 自由項目８
                $row['reserve9'] ?? null, // 自由項目９
                $row['reserve10'] ?? null, // 自由項目１０
                $row['reserve11'] ?? null, // 自由項目１１
                $row['reserve12'] ?? null, // 自由項目１２
                $row['reserve13'] ?? null, // 自由項目１３
                $row['reserve14'] ?? null, // 自由項目１４
                $row['reserve15'] ?? null, // 自由項目１５
                $row['reserve16'] ?? null, // 自由項目１６
                $row['reserve17'] ?? null, // 自由項目１７
                $row['reserve18'] ?? null, // 自由項目１８
                $row['reserve19'] ?? null, // 自由項目１９
                $row['reserve20'] ?? null, // 自由項目２０
                $row['customer_type']['m_itemname_type_name'] ?? null, // 顧客区分
                $row['discount_rate'] ?? null, // 割引率
                $row['dm_send_letter_flg'] ?? null, // DM配送方法 郵便
                $row['dm_send_mail_flg'] ?? null, // DM配送方法 メール
                $row['cust_order_sum']['total_order_money'] ?? 0, // 累計購入金額
                $row['cust_order_sum']['total_order_count'] ?? 0, // 購入回数
                $row['cust_order_sum']['newest_order_date'] ?? null, // 最新受注日
                $row['cust_order_sum']['total_unbilled_money'] ?? 0, // 未請求金額
                $row['cust_order_sum']['total_undeposited_money'] ?? 0, // 未入金金額
                $row['cust_order_sum']['total_remind_count'] ?? 0, // 累計督促回数
                $row['cust_order_sum']['total_return_count'] ?? 0, // 累計返品回数
            ];

            // Write each data row
            fputcsv($memoryStream, $csvRow);
        }

        // Rewind the stream to read its contents
        rewind($memoryStream);
        $csvData = stream_get_contents($memoryStream);

        // Close the memory stream
        fclose($memoryStream);

        return $csvData;
    }
}
