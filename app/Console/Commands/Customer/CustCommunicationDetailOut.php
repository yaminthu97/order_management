<?php

namespace App\Console\Commands\Customer;

use App\Enums\BatchExecuteStatusEnum;
use App\Enums\SexTypeEnum;
use App\Models\Cc\Gfh1207\CustCommunicationModel;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Common\Gfh1207\CheckBatchParameter;
use App\Modules\Master\Gfh1207\Enums\TemplateFileNameEnum;
use App\Modules\Order\Gfh1207\GetExcelExportFilePath;
use App\Modules\Order\Gfh1207\GetTemplateFileName;
use App\Modules\Order\Gfh1207\GetTemplateFilePath;

use App\Services\ExcelReportManager;
use App\Services\TenantDatabaseManager;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustCommunicationDetailOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CustCommunicationDetailOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '顧客対応履歴IDから顧客対応照会履歴ファイルを作成し、バッチ実行確認へと出力する';

    // バッチ名
    protected $batchName = '顧客対応照会履歴出力';

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    // for check batch parameter
    protected $checkBatchParameter;

    // for template file name
    protected $getTemplateFileName;

    // for template file path
    protected $getTemplateFilePath;

    // for excel export file path
    protected $getExcelExportFilePath;

    //throw error code constants
    private const PRIVATE_THROW_ERR_CODE = -1;

    /**
     * Create a new command instance.
     */
    public function __construct(
        StartBatchExecuteInstructionInterface $startBatchExecute,
        EndBatchExecuteInstructionInterface $endBatchExecute,
        CheckBatchParameter $checkBatchParameter,
        GetTemplateFileName $getTemplateFileName,
        GetTemplateFilePath $getTemplateFilePath,
        GetExcelExportFilePath $getExcelExportFilePath,
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->checkBatchParameter = $checkBatchParameter;
        $this->getTemplateFileName = $getTemplateFileName;
        $this->getTemplateFilePath = $getTemplateFilePath;
        $this->getExcelExportFilePath = $getExcelExportFilePath;

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

            // to required parameter
            $paramKey = ['cust_communication_id'];

            // to check batch json parameter
            $checkResult = $this->checkBatchParameter->execute($this->argument('json'), $paramKey);

            if (!$checkResult) {
                //エラーメッセージはパラメータが不正です。
                throw new Exception(__('messages.error.invalid_parameter'), self::PRIVATE_THROW_ERR_CODE);
            }

            $param = $this->argument('json');

            $searchData = (json_decode($param, true))['search_info'];

            //パラメータから顧客対応履歴IDを取得する。
            $custCommunicationId = $searchData['cust_communication_id'];

            //顧客対応履歴IDがnullまたは空の文字列の場合
            if ($custCommunicationId == null || $custCommunicationId == "") {
                //エラーメッセージはパラメータが不正です。
                throw new Exception(__('messages.error.invalid_parameter'), self::PRIVATE_THROW_ERR_CODE);
            }

            $data =  CustCommunicationModel::where('t_cust_communication_id', $custCommunicationId)
                ->select(
                    't_cust_communication_id',
                    'm_cust_id',
                    'receive_operator_id',
                    'answer_operator_id',
                    'inquiry_type',
                    'title',
                    'sales_channel',
                    'name_kanji',
                    'm_cust_id',
                    'tel',
                    'postal',
                    'address1',
                    'address2',
                    'address3',
                    'address4',
                    'receive_datetime'
                )
                ->with([
                    'receiveOperator:m_operators_id,m_operator_name',
                    'answerOperator:m_operators_id,m_operator_name',
                    'custCommunicationDtl:t_cust_communication_dtl_id,t_cust_communication_id,receive_datetime,receive_detail,answer_detail',
                    'cust:m_cust_id,sex_type,birthday',
                    'inquiry:m_itemname_types_id,m_itemname_type_name',
                    'saleChannal:m_itemname_types_id,m_itemname_type_name',
                ])
                ->first();

            // to check excel data have or not condition
            if (!$data) {
                // [出力対象のデータがありませんでした。] message save to 'execute_result'
                $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                    'execute_result' =>  __('messages.error.data_not_found2', ['datatype' => '出力']),
                    'execute_status' => BatchExecuteStatusEnum::SUCCESS->value
                ]);

                DB::commit();
                return;
            }

            $value = $this->getValues($data->toArray());

            $totalRowCnt = 1; // data row count will be always one record

            $reportName = TemplateFileNameEnum::EXPXLSX_CUST_COMMUNICATION_DETAIL->value;  // レポート名
            $templateFileName = $this->getTemplateFileName->execute($reportName, $accountId);  // template file name from database
            $templateFilePath = $this->getTemplateFilePath->execute($accountCode, $templateFileName);   // to get template file path

            // テンプレートファイルパスが存在するかどうかをチェック
            if (empty($templateFilePath)) {
                // [テンプレートファイルが見つかりません。] メッセージを'execute_result'にセットする
                throw new Exception(__('messages.error.file_not_found2', ['file' => 'テンプレート']), self::PRIVATE_THROW_ERR_CODE);
            }

            // Excelにデータを書き込む
            $erm = new ExcelReportManager($templateFilePath);
            $erm->setValues($value, []);

            $fileName = 'customer-history_' . Carbon::now()->format('YmdHis');

            // to get base file path
            $savePath = $this->getExcelExportFilePath->execute($accountCode, $batchType, $fileName);
            $result = $erm->save($savePath);

            // check to upload permission allow or not allow
            if (!$result) {
                // [AWS S3へのファイルのアップロードに失敗しました。] message save to 'execute_result'
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
                'execute_result' => __('messages.success.batch_success.success_rowcnt', ['rowcnt' => $totalRowCnt, 'process' => '出力']), // 〇〇件出力しました。
                'execute_status' => BatchExecuteStatusEnum::SUCCESS->value,
                'file_path' => $savePath,
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            // write the log error in laravel.log for error message
            Log::error($e->getMessage());
            /**
             * [共通処理] エラー時の処理
             * バッチ実行指示テーブルの該当バッチに以下の情報を書き込む
             * - バッチ終了時刻
             * - (実行状態)
             * - (実行結果)
             * - (エラーファイルパス)
             */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => ($e->getCode() == self::PRIVATE_THROW_ERR_CODE) ? $e->getMessage() : BatchExecuteStatusEnum::FAILURE->label(),
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value,
            ]);
        }
    }

    /**
     * Excel テーブルの連続値を取得
     *
     * @param array mixed $data Excel テーブルに追加するデータの配列
     *
     * @return array (Excel テーブルデータ)
     *               - items: テーブルのヘッダー項目の配列
     *               - data: 各行のデータを含む配列
     */
    private function getValues($data)
    {
        //ascending order  to receive_datetime
        $custCommunicationDtl = collect($data['cust_communication_dtl'])->sortBy('receive_datetime')->toArray();

        //remove answer_detail in cust_communication_dtl
        $receiveDetailArray = array_map(function ($item) {
            unset($item['answer_detail']);
            return $item;
        }, $custCommunicationDtl);

        //remove receive_detail in cust_communication_dtl
        $answerDetailArray = array_map(function ($item) {
            unset($item['receive_detail']);
            return $item;
        }, $custCommunicationDtl);

        $receiveDetail = $this->receiveAnswerDetailFormat($receiveDetailArray);
        $answerDetail = $this->receiveAnswerDetailFormat($answerDetailArray);

        //for values
        $result = [
            'items' => ['対応番号', '報告', '受信者', '対応者', '内容種別', 'タイトル', '販売窓口', '受信内容一覧', '性別', '年齢', '顧客ID', '顧客電話番号', '顧客氏名', '顧客住所', '対応結果一覧'],
            'data' => [
                '問' . Carbon::parse($data['receive_datetime'])->format('Y.m.d') . '-' . $data['t_cust_communication_id'], //対応番号
                Carbon::now()->format('Y-m-d H:i'), //報告(報告日時)
                $data['receive_operator']['m_operator_name'] ?? '', // 受信者
                $data['answer_operator']['m_operator_name'] ?? '', // 対応者
                $data['inquiry']['m_itemname_type_name'] ?? '', //内容種別
                $data['title'] ?? '', //タイトル
                $data['sale_channal']['m_itemname_type_name'] ?? '', //販売窓口
                $receiveDetail, //受信内容一覧
                isset($data['cust']['sex_type']) ? SexTypeEnum::from($data['cust']['sex_type'])->label() : '指定なし', //性別
                Carbon::hasFormat($data['cust']['birthday'] ?? '', 'Y-m-d')  ? Carbon::createFromFormat('Y-m-d', $data['cust']['birthday'])->age : '', //年齢
                $data['m_cust_id'] ?? '', //顧客ID
                $data['tel'] ?? '', //顧客電話番号
                $data['name_kanji'] ?? '', //顧客氏名
                $data['postal'] ? '〒' .  preg_replace('/^(\d{3})(\d{4})$/', '$1-$2', $data['postal']) : '-' . ' ' . $data['address1'] ?? '' . $data['address2'] ?? '' . $data['address3'] ?? '' . $data['address4'] ?? '', //顧客住所
                $answerDetail, //対応結果一覧
            ],
        ];

        return $result;
    }

    /**
     *  receive detail and answer detail format
     *
     * @param array
     *
     * @return string A formatted string with each entry separated by a newline.
     * Format:
     * 対応日：{receive_datetime}
     * {receive_detail} or {answer_detail}
     */
    private function receiveAnswerDetailFormat($dataList)
    {
        // Initialize an empty array
        $detailsArray = [];

        foreach ($dataList as $data) {
            $detailsArray[] =
                "対応日： {$data['receive_datetime']}\n" .
                ($data['receive_detail'] ?? $data['answer_detail']) . "\n"; //receive_detail is null , use answer_detail.
        }
        // Combine all formatted details into a single string separated by newlines
        return implode("\n", $detailsArray);
    }
}
