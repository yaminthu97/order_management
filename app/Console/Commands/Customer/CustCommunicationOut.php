<?php

namespace App\Console\Commands\Customer;

use App\Enums\BatchExecuteStatusEnum;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Common\Gfh1207\CheckBatchParameter;
use App\Modules\Customer\Gfh1207\SearchCustCommunication;
use App\Modules\Master\Gfh1207\Enums\BatchListEnum;
use App\Modules\Order\Gfh1207\GetCsvExportFilePath;
use App\Services\TenantDatabaseManager;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CustCommunicationOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CustCommunicationOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '顧客対応履歴検索画面で検索した条件で顧客対応履歴一覧を作成し、バッチ実行確認へと出力する';

    // バッチ名
    protected $batchName = '顧客対応履歴一覧出力';

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    // バッチType
    protected $batchType = BatchListEnum::EXPCSV_CUST_COMMUNICATION->value;

    // for batch Execution Id
    private $batchExecutionId;  

    // 検品データ取得
    protected $getCsvExportFilePath;

    // for check batch parameter
    protected $checkBatchParameter;

    // search condition added
    protected $searchCustCommunication;

    // エラーコード
    private const PRIVATE_THROW_ERR_CODE = -1;

    /**
     * Create a new command instance.
     */
    public function __construct(
        StartBatchExecuteInstructionInterface $startBatchExecute,
        EndBatchExecuteInstructionInterface $endBatchExecute,
        GetCsvExportFilePath $getCsvExportFilePath,
        CheckBatchParameter $checkBatchParameter,
        SearchCustCommunication $searchCustCommunication,
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->getCsvExportFilePath = $getCsvExportFilePath;
        $this->checkBatchParameter = $checkBatchParameter;
        $this->searchCustCommunication = $searchCustCommunication;
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->batchExecutionId = $this->argument('t_execute_batch_instruction_id');

            $batchExecute = $this->startBatchExecute->execute($this->batchExecutionId);

            /**
             * [共通処理] 開始処理
             * バッチ実行指示テーブル（t_execute_batch_instruction ）から該当バッチの取得と開始処理
             * t_execute_batch_instruction_id より該当バッチを取得し以下の情報を書き込む
             * - バッチ開始時刻
             */
            $accountCode = $batchExecute->account_cd;     // for account cd
            $accountId = $batchExecute->m_account_id;   // for m account id
            $batchType = $batchExecute->execute_batch_type;  // for batch type

            if (app()->environment('testing')) {
                // テスト環境の場合
                TenantDatabaseManager::setTenantConnection($accountCode.'_db_testing');
            } else {
                TenantDatabaseManager::setTenantConnection($accountCode.'_db');
            }

        } catch (Exception $e) {
            // write the log error in laravel.log for error message
            Log::error('error_message : ' . $e->getMessage());
            return;
        }

        DB::beginTransaction();
        try {
            $paramKey = [
                'm_account_id',
                't_cust_communication_id',
                'contact_way_type',
                'title',
                'status',
                'receive_datetime_from',
                'receive_datetime_to',
                'receive_operator_id',
                'receive_detail',
            ];

            $checkResult = $this->checkBatchParameter->execute($this->argument('json'), $paramKey);  // バッチJSONパラメータをチェックする

            if (!$checkResult) {
                // [パラメータが不正です。] メッセージを'execute_result'にセットする
                throw new Exception(__('messages.error.invalid_parameter'), self::PRIVATE_THROW_ERR_CODE);
            }

            $searchCondition = (json_decode($this->argument('json'), true))['search_info'];  // for all search parameters

            // argument  array of m_account_id and batch for searchCustCommunication()
            $searchOption = [
                'm_account_id' => $searchCondition['m_account_id'],
                'isBatch' => true,
            ];

            $query = $this->searchCustCommunication->execute($searchCondition, $searchOption); // get query data from searchCustCommunication
            $custCommunicationData = $this->getCustCommunication($query); // get Customer Communication Data

            // check total rows are empty or not
            if (empty($custCommunicationData)) {
                // [出力対象のデータがありませんでした。] message save to 'execute_result'
                $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                    'execute_result' =>  __('messages.error.data_not_found2', ['datatype' => '出力']),
                    'execute_status' => BatchExecuteStatusEnum::SUCCESS->value
                ]);

                DB::commit();
                return ;
            }
            $date = Carbon::now(); // Or any Carbon instance
            // File name date format
            $formattedDate = $date->format('YmdHis');

            $fileName = 'cust_history_' . $formattedDate; // cust_history file name set
            $csvData = $this->createCsvData($custCommunicationData); // get csv data
            $recordCount = $csvData['recordCount']; // get record

            // Save CSV file to file system
            $filePathSave = $this->getCsvExportFilePath->execute($accountCode, $batchType, $fileName);
            $fileuploaded = Storage::disk(config('filesystems.default', 'local'))->put($filePathSave, $csvData['csvData']);

            // return fail if there is an error
            if($fileuploaded === false) {
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
                'execute_result' => __('messages.info.notice_output_count', ['count' => $recordCount]),
                'execute_status' => BatchExecuteStatusEnum::SUCCESS->value,
                'file_path' => $filePathSave,
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
             */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => $e->getMessage(),
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value
            ]);
        }
    }

    /**
     * Get data from Customer Communication Table
     * @param $query get Customer value from SearchCustCommunication
    */
    public function getCustCommunication($query)
    {
        $custData = $query->load(['custCommunicationDtl', 'entryOperator', 'updateOperator']) // Load DB relationship after get method.
                    ->groupBy([
                        't_cust_communication_id',
                        fn ($item) => $item->custCommunicationDtl->pluck('receive_datetime')->toArray(),
                    ])
                    ->flatMap(function ($group) {
                        return $group->flatten(3)->map(function ($item) {
                            return $item->custCommunicationDtl->map(function ($data) use ($item) {
                                return [
                                    't_cust_communication' => $item['t_cust_communication_id'],
                                    'm_cust_id' => $item['m_cust_id'] ?? null,
                                    't_order_hdr_id' => $item['t_order_hdr_id'],
                                    'page_cd' => $item['page_cd'],
                                    'name_kanji' => $item['name_kanji'],
                                    'name_kana' => $item['name_kana'],
                                    'tel' => $item['tel'],
                                    'email' => $item['email'],
                                    'postal' => $item['postal'],
                                    'address1' => $item['address1'],
                                    'address2' => $item['address2'],
                                    'address3' => $item['address3'],
                                    'address4' => $item['address4'],
                                    'note' => $item['note'],
                                    'title' => $item['title'],
                                    'sales_channel' => $item['sales_channel'],
                                    'inquiry_type' => $item['inquiry_type'],
                                    'open' => $item['open'],
                                    'contact_way_type' => $data['contact_way_type'],
                                    'status' => $data['status'],
                                    'category' => $data['category'],
                                    'receive_detail' => $data['receive_detail'],
                                    'receive_operator_id' => $data['receive_operator_id'],
                                    'receive_datetime_dtl' => $data['receive_datetime'], 
                                    'escalation_operator_id' => $data['escalation_operator_id'],
                                    'answer_detail' => $data['answer_detail'],
                                    'answer_operator_id' => $data['answer_operator_id'],
                                    'answer_datetime' => $data['answer_datetime'],
                                    'entry_operator_name' => $item->entryOperator['m_operator_name'] ?? null, // display null data can't obtain
                                    'entry_timestamp' => $data['entry_timestamp']->toDateTimeString(),
                                    'update_operator_name' => $item->updateOperator['m_operator_name'] ?? null, // display null data can't obtain
                                    'update_timestamp' => $data['update_timestamp']->toDateTimeString(),
                                ];
                                return $result;
                            });
                        });
                    })
                    ->toArray();
        $result =  array_merge(...$custData); // mergre all array into one.
        $result = array_values(array_unique($result, SORT_REGULAR)); // remove duplicate values.
        return $result;
    }
    /**
     * Create the database data as CSV file
     *
     * @param array $csvData
     * @return string
     */
    private function createCsvData($csvData)
    {
        try {
            // create tempory csv file path
            $csvFilePath = tempnam(sys_get_temp_dir(), 'csv') . '.csv';
            // open and write data
            $csvFile = fopen($csvFilePath, 'w');

            $header = [
                '顧客対応履歴ID',
                '受信日時',
                '顧客ID',
                '受注ID',
                '商品コード',
                '顧客氏名',
                '顧客氏名（フリガナ）',
                '連絡先電話番号',
                '連絡先メールアドレス',
                '連絡先郵便番号',
                '連絡先都道府県',
                '連絡先市区町村',
                '連絡先番地',
                '連絡先建物名',
                '連絡先その他',
                'タイトル',
                '販売窓口',
                '問合せ内容種別',
                '公開フラグ',
                '顧客対応連絡方法',
                'ステータス',
                '分類',
                '受信内容',
                '受信者',
                'エスカレーション担当者',
                '回答内容',
                '回答者',
                '回答日時',
                '登録ユーザ名',
                '登録タイムスタンプ',
                '更新ユーザ名',
                '更新タイムスタンプ'
            ];

            fputcsv($csvFile, $header); // insert header data
            $countRecord = 0;
            foreach ($csvData as $key => $csvRow) {
                $countRecord++;
                fputcsv($csvFile, $csvRow);
            }

            // csv file close
            fclose($csvFile);
            // read csv file path
            $csvContent = file_get_contents($csvFilePath);
            unlink($csvFilePath);   // clear temp file path
            $result = [
                'csvData' => $csvContent,
                'recordCount' => $countRecord,
            ];

            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
