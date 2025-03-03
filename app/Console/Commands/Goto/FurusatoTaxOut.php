<?php

namespace App\Console\Commands\Goto;

use App\Enums\BatchExecuteStatusEnum;
use App\Enums\ProgressTypeEnum;
use App\Modules\Master\Gfh1207\Enums\BatchListEnum;
use App\Modules\Master\Gfh1207\Enums\TemplateFileNameEnum;
use App\Models\Master\Base\ItemnameTypeModel;
use App\Models\Order\Base\OrderHdrModel;
use App\Models\Cc\Gfh1207\CustModel;

use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Order\Base\SearchInterface;

use App\Modules\Order\Gfh1207\GetExcelExportFilePath;
use App\Modules\Order\Gfh1207\GetTemplateFileName;
use App\Modules\Order\Gfh1207\GetTemplateFilePath;

use App\Services\ExcelReportManager;
use App\Services\TenantDatabaseManager;

use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\ModuleFailed;

class FurusatoTaxOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:FurusatoTaxOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '特定の顧客からの注文を抽出し、ふるさと納税一覧を作成する';

    // テンプレートファイルパス
    protected $templateFilePath;

    // テンプレートファイル名
    protected $templateFileName;

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    // 受注検索モジュール
    protected $search;

    // ふるさと納税対象者となる顧客の reserve20 の値
    protected $furusatoTaxReserve20  = 'FNZ';

    /**
     * Create a new command instance.
     */
    public function __construct(
        StartBatchExecuteInstructionInterface $startBatchExecute,
        EndBatchExecuteInstructionInterface $endBatchExecute,
        SearchInterface $search,
        GetTemplateFileName $getTemplateFileName,
        GetTemplateFilePath $getTemplateFilePath,
        GetExcelExportFilePath $getExcelExportFilePath,
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->search = $search;
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
        $searchInfo = json_decode($this->argument('json'), true);

        // $searchInfo に必要なパラメータが含まれているかチェック
        $paramKey = ['m_account_id'];
        try {
            // json decode error
            if (json_last_error() !== JSON_ERROR_NONE) {
                // [パラメータが不正です。] message save to 'execute_result'
                throw new \Exception(__('messages.error.invalid_parameter'));
            }
            if (!empty(array_diff($paramKey, array_keys($searchInfo)))) {
                // [パラメータが不正です。] message save to 'execute_result'
                throw new \Exception(__('messages.error.invalid_parameter'));
            }

            /**
             * [共通処理] 開始処理
             * バッチ実行指示テーブル（t_execute_batch_instruction ）に新規作成と開始処理
             * - バッチ開始時刻
            */
            $batchExecute = $this->startBatchExecute->execute(null, [
                'm_account_id' => $searchInfo['m_account_id'],
                'execute_batch_type' => BatchListEnum::EXPXLSX_FURUSATO_TAX->value,
                'execute_conditions' => $searchInfo,
            ]);

            $accountCode = $batchExecute->account_cd;     // for account cd
            $accountId = $batchExecute->m_account_id;   // for m account id
            $batchType = $batchExecute->execute_batch_type;  // for batch type


            if (app()->environment('testing')) {
                // テスト環境の場合
                TenantDatabaseManager::setTenantConnection($accountCode . '_db_testing');
            } else {
                TenantDatabaseManager::setTenantConnection($accountCode . '_db');
            }
        } catch (Exception $e) {
            //Log::error($e->getMessage());
            ModuleFailed::dispatch(__CLASS__, [$searchInfo], $e);
            return;
        }

        DB::beginTransaction();

        try {
            // $furusatoTaxReserve20 から顧客一覧を取得
            $furusatoCusts = CustModel::where('reserve20', $this->furusatoTaxReserve20)
                ->where('m_account_id', $accountId)
                ->where('delete_flg', 0)
                ->whereIn('delete_operator_id', [0, null])
                ->get();
            if ($furusatoCusts->count() === 0) {
                // [ふるさと納税対象者が見つかりませんでした。] message save to 'execute_result'
                $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                    'execute_result' => __('messages.error.data_not_found', ['data' => 'ふるさと納税顧客', 'id' => $this->furusatoTaxReserve20]),
                    'execute_status' => BatchExecuteStatusEnum::FAILURE->value
                ]);

                DB::commit();
                return ;
            }

            // カンマ区切りの顧客ID一覧
            $mCustIds = implode(',', $furusatoCusts->pluck('m_cust_id')->toArray());

            // 顧客IDから過去1年の受注を取得(受注配送先、受注明細も合わせ)
            // 受注は常に1受注基本1受注送付先となる
            $orders = $this->search->execute([
                'm_cust_id' => $mCustIds,
                'm_account_id' => $accountId,
                'order_datetime_from' => Carbon::now()->subYear()->format('Y-m-d H:i:s'),
            ], [
                'with' => [
                    'orderDestination',
                    'orderDestination.orderDtl',
                ],
            ]);
            
            // to check excel data have or not condition
            if (count($orders) === 0) {
                // [出力対象のデータがありませんでした。] message save to 'execute_result'
                $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                    'execute_result' =>  __('messages.error.data_not_found2', ['data' => '出力']),
                    'execute_status' => BatchExecuteStatusEnum::SUCCESS->value
                ]);

                DB::commit();
                return ;
            }

            $reportName = TemplateFileNameEnum::EXPXLSX_FURUSATO_TAX->value;

            $this->templateFileName = $this->getTemplateFileName->execute($reportName, $accountId);
            $this->templateFilePath = $this->getTemplateFilePath->execute($accountCode, $this->templateFileName);

            // to check exist template file path or not condition
            if (empty($this->templateFilePath)) {
                // [テンプレートファイルが見つかりません。（:templateFilePath）] message save to 'execute_result'
                throw new \Exception(__('messages.error.file_not_found', ['file' => 'テンプレート', 'path' => $this->templateFilePath]));
            }

            $values = $this->getValues($orders);
            $continuousValues = $this->getContinuousValues($orders);

            // write data to excel
            $erm = new ExcelReportManager($this->templateFilePath);
            $erm->setValues($values, $continuousValues);

            $savePath = $this->getExcelExportFilePath->execute($accountCode, $batchType, $batchExecute->t_execute_batch_instruction_id);  // to get base file path
            $saveResult = $erm->save($savePath);

            // 保存失敗時の処理
            if (!$saveResult) {
                // [出力ファイルの保存に失敗しました。] message save to 'execute_result'
                throw new \Exception(__('messages.error.file_save_failure'));
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
                'execute_result' => __('messages.info.notice_output_count', ['count' => count($continuousValues['data'])]),  // 〇〇件出力しました。
                'execute_status' => BatchExecuteStatusEnum::SUCCESS->value,
                'file_path' => $savePath
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            // default fail message
            $errorMessage = BatchExecuteStatusEnum::FAILURE->label();

            $errorMessage = $e->getMessage();

            /**
             * [共通処理] 終了処理
             * バッチ実行指示テーブルの該当バッチに以下の情報を書き込む
             * - バッチ終了時刻
             * - (実行状態)
             * - (実行結果)
             */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => $errorMessage,
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value
            ]);
        }
    }

    /**
    * orderHdrModel から excel 用のデータを生成
    *
    * @return array ( excel table data )
    */
    private function getValues($orders)
    {
        $data = [];
        return $data;
    }

    /**
    * orderHdrModel から excel 用のデータを生成
    *
    * @return array ( excel table data )
    */
    private function getContinuousValues($orders)
    {
        $data = [];
        // 受注日	出荷予定日	受注ID	配送番号	届け先コード	届け先名	商品コード	受注数量	商品売価内税	送料	金額合計

        foreach ($orders as $item) {
            // 異常データはスキップ
            if (!isset($item->orderDestination[0])) {
                continue;
            }
            if (!isset($item->orderDestination[0]->orderDtl[0])) {
                continue;
            }
            $orderDestination = $item->orderDestination[0];
            $orderDetail = $item->orderDestination[0]->orderDtl[0];
            $data[] = [
                Carbon::parse($item->order_datetime)->format('Y/m/d'),
                Carbon::parse($orderDestination->deli_plan_date)->format('Y/m/d'),
                $item->t_order_hdr_id,
                $orderDestination->order_destination_seq,
                $orderDestination->destination_id,
                $orderDestination->destination_name,
                $orderDetail->sell_cd,
                $orderDetail->order_sell_vol,
                $orderDetail->order_sell_price,
                $orderDestination->shipping_fee,
                $item->order_total_price,
            ];
        }
        $continuousValues = [
            'items' => ['受注日', '出荷予定日', '受注ID', '配送番号', '届け先コード', '届け先名', '商品コード', '受注数量', '商品売価内税', '送料', '金額合計'],
            'data' => $data,
        ];
        return $continuousValues;
    }
}