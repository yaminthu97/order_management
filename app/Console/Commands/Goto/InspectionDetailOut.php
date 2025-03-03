<?php

namespace App\Console\Commands\Goto;

use App\Enums\BatchExecuteStatusEnum;
use App\Enums\ShipmentStatusEnum;
use App\Models\Order\Gfh1207\DeliHdrModel;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
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

class InspectionDetailOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:InspectionDetailOut {t_execute_batch_instruction_id : バッチ実行指示ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '検品済一覧明細ファイル（Excel）の作成';

    // バッチ名
    protected $batchName = '検品済み一覧明細';

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

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
        GetTemplateFileName $getTemplateFileName,
        GetTemplateFilePath $getTemplateFilePath,
        GetExcelExportFilePath $getExcelExportFilePath,
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
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

            $reportName = TemplateFileNameEnum::EXPXLSX_INSPECTION_DETAIL_OUT->value;  // レポート名
            $templateFileName = $this->getTemplateFileName->execute($reportName, $accountId);  // template file name from database
            $templateFilePath = $this->getTemplateFilePath->execute($accountCode, $templateFileName);   // to get template file path

            // テンプレートファイルパスが存在するかどうかをチェック
            if (empty($templateFilePath)) {
                // [テンプレートファイルが見つかりません。] メッセージを'execute_result'にセットする
                throw new Exception(__('messages.error.file_not_found2', ['file' => 'テンプレート']), self::PRIVATE_THROW_ERR_CODE);
            }

            $yesterday = Carbon::yesterday(); // 前日 (yesterday)
            $nextWeek = Carbon::today()->addDays(7); // 本日＋7日 (today + 7 days)
            $Inspected = ShipmentStatusEnum::INSPECTED->value; //検品済み以降

            $dataList = DeliHdrModel::select(
                'deli_plan_date',
                't_order_hdr_id',
                't_deli_hdr_id',
                't_order_destination_id',
                'order_destination_seq',
                'gp2_type',
                'cancel_operator_id'
            )
                ->with([
                    'deliveryDetails:t_delivery_hdr_id,sell_id,sell_cd,order_sell_vol',
                    'deliveryDetails.amiEcPage:m_ami_ec_page_id,m_ami_page_id,ec_page_title,sales_price',
                ])
                ->whereBetween('deli_plan_date', [$yesterday, $nextWeek]) //前日 ～（本日＋7日）
                ->where('gp2_type', '>=', $Inspected) //検品済み以降
                ->where('cancel_operator_id', null) //取消ユーザIDがnullの場合
                ->get()
                ->groupBy([
                    'deli_plan_date',
                    't_order_hdr_id',
                    't_order_destination_id',
                ]);

            // to check excel data have or not condition
            if ($dataList->isEmpty()) {
                // [出力対象のデータがありませんでした。] message save to 'execute_result'
                $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                    'execute_result' =>  __('messages.error.data_not_found2', ['datatype' => '出力']),
                    'execute_status' => BatchExecuteStatusEnum::SUCCESS->value
                ]);

                DB::commit();
                return;
            }

            $continuousValues = $this->getContinuousValues($dataList->toArray());

            // data row counts
            $totalRowCnt = count($continuousValues['data']);

            // Excelにデータを書き込む
            $erm = new ExcelReportManager($templateFilePath);
            $erm->setValues(null, $continuousValues);

            // to get base file path
            $savePath = $this->getExcelExportFilePath->execute($accountCode, $batchType, $batchExecutionId);
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
                'execute_result' => __('messages.success.batch_success.success_rowcnt', ['rowcnt' => $totalRowCnt, 'process' => '取込']), // 〇〇件取込しました。
                'execute_status' => BatchExecuteStatusEnum::SUCCESS->value,
                'file_path' => $savePath,
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            // write the log error in laravel.log for error message
            Log::error($e->getMessage());
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
            ]);
        }
    }

    /**
     * Excel テーブルの連続値を取得
     *
     * @param array mixed $dataList Excel テーブルに追加するデータの配列
     *
     * @return array (Excel テーブルデータ)
     *               - items: テーブルのヘッダー項目の配列
     *               - data: 各行のデータを含む配列
     */
    private function getContinuousValues($dataList)
    {

        $result = [];

        foreach ($dataList as $deliPlanDate => $orderData) { // loop 出荷予定日 group
            foreach ($orderData as $orderHdrId => $destinations) { // loop 受注ID group
                foreach ($destinations as $destinationId => $deliHdrs) { // loop 受注配送先ID group
                    foreach ($deliHdrs as $deliHdr) { // loop  deliHdr data
                        $deliveryDetails = $deliHdr['delivery_details'];

                        foreach ($deliveryDetails as $deliveryDetail) { // Loop deliveryDetail data
                            $orderCount = $deliveryDetail['order_sell_vol']; // 受注数量
                            $salesPrice = $deliveryDetail['ami_ec_page']['sales_price']; // 商品単価税抜
                            $totalPrice = $salesPrice * $orderCount; //ページマスタ.販売単価 × 受注数量

                            $result[] = [
                                Carbon::parse($deliPlanDate)->format('Y/m/d'), // 出荷予定日
                                $orderHdrId, // 受注ID
                                $deliHdr['order_destination_seq'], // 配送番号
                                $deliveryDetail['sell_cd'], // 商品コード
                                $deliveryDetail['ami_ec_page']['ec_page_title'], // 商品名
                                $orderCount, // 受注数量
                                $salesPrice, // 商品単価税抜
                                $totalPrice, // 商品税抜計
                            ];
                        }
                    }
                }
            }
        }

        $continuousValues = [
            'items' => ['出荷予定日', '受注ID', '配送番号', '商品コード', '商品名', '受注数量', '商品単価税抜', '商品税抜計'],
            'data' => $result,
        ];

        return $continuousValues;
    }
}
