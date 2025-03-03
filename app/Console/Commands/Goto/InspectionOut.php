<?php

namespace App\Console\Commands\Goto;

use App\Enums\BatchExecuteStatusEnum;
use App\Enums\ShipmentStatusEnum;
use App\Enums\ThreeTemperatureZoneTypeEnum;

use App\Models\Order\Gfh1207\DeliHdrModel;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Master\Gfh1207\Enums\DeliveryTypeEnum;
use App\Modules\Master\Gfh1207\Enums\PaymentTypeEnum;
use App\Modules\Master\Gfh1207\Enums\SlipTypeEnum;
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

class InspectionOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:InspectionOut {t_execute_batch_instruction_id : バッチ実行指示ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '検品済一覧ファイル（Excel）の作成';

    // バッチ名
    protected $batchName = '検品済み一覧';

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

            $reportName = TemplateFileNameEnum::EXPXLSX_INSPECTION_OUT->value;  // レポート名
            $templateFileName = $this->getTemplateFileName->execute($reportName, $accountId);  // template file name from database
            $templateFilePath = $this->getTemplateFilePath->execute($accountCode, $templateFileName);   // to get template file path

            // テンプレートファイルパスが存在するかどうかをチェック
            if (empty($templateFilePath)) {
                // [テンプレートファイルが見つかりません。] メッセージを'execute_result'にセットする
                throw new Exception(__('messages.error.file_not_found2', ['file' => 'テンプレート']), self::PRIVATE_THROW_ERR_CODE);
            }

            $yesterday = Carbon::yesterday(); // 前日 (yesterday)
            $nextWeek = Carbon::today()->addDays(7); // 本日＋7日 (today + 7 days)
            $Linked = ShipmentStatusEnum::LINKED->value; //出荷連携済

            $dataList = DeliHdrModel::select('deli_plan_date', 't_order_hdr_id', 'm_deli_type_id', 't_deli_hdr_id', 'gp2_type', 'temperature_zone', 'sell_total_price')
                ->with([
                    'deliveryType:m_delivery_types_id,m_delivery_type_code',
                    'orderHdr:t_order_hdr_id,m_payment_types_id,sell_total_price',
                    'orderHdr.paymentTypes:m_payment_types_id,m_payment_types_code',
                ])
                ->whereBetween('deli_plan_date', [$yesterday, $nextWeek]) //前日 ～（本日＋7日）
                ->where('gp2_type', '>', $Linked) //出荷指示済み以降
                ->get();

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

            $continuousValues = $this->getContinuousValues($dataList);

            $totalRowCnt = count($continuousValues['data']); // data row counts

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
                'file_path' => "$savePath",
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
        $data = $dataList->map(function ($item) {
            $gp2TypeLabel = ShipmentStatusEnum::tryFrom($item->gp2_type)?->label() ?? "";
            $slipType = $this->getSlipType(
                $item->deliveryType?->m_delivery_type_code,
                $item->orderHdr?->paymentTypes?->m_payment_types_code,
                $item->temperature_zone,
            );

            return [
                'deli_plan_date' => Carbon::parse($item->deli_plan_date)->format('Y/m/d'), // 出荷予定日
                'slip_type' => $slipType, // 伝票種別
                'gp2_type' => $gp2TypeLabel, // 出荷ステータス
                'sell_total_price' => $item->sell_total_price ?? 0, // 商品税抜計
            ];
        });

        // Group by 出荷予定日, 伝票種別, 出荷ステータス
        $groupedData = $data->groupBy(function ($item) {
            return $item['deli_plan_date'];
        })->map(function ($dateGroup) {
            return $dateGroup->groupBy('slip_type')->map(function ($slipGroup) {
                return $slipGroup->groupBy('gp2_type');
            });
        });

        $result = [];
        foreach ($groupedData as $deliPlanDate => $slipGroups) {
            foreach ($slipGroups as $slipType => $gp2Groups) {
                foreach ($gp2Groups as $gp2Type => $items) {
                    $result[] = [
                        $deliPlanDate, // 出荷予定日
                        $slipType, // 伝票種別
                        $gp2Type, // 出荷ステータス
                        count($items), // 出荷ステータス件数
                        collect($items)->sum('sell_total_price'), // total 商品税抜計
                    ];
                }
            }
        }

        $continuousValues = [
            'items' => ['出荷予定日', '伝票種別', '出荷ステータス', '出荷ステータス件数', '商品税抜計'],
            'data' => $result,
        ];

        return $continuousValues;
    }

    /**
     * 伝票種別は、次の組合せで決定する。
     *
     * @param int ($deliTypeCode, $paymentTypeCode, $temperatureZone)
     *
     * @return string (伝票種別)
     */
    private function getSlipType($deliTypeCode, $paymentTypeCode, $temperatureZone)
    {
        $yamato = DeliveryTypeEnum::YAMATO->value; // ヤマト
        $ownShipment = DeliveryTypeEnum::OWN_SHIPMENT->value; // 自社便
        $noShipment = DeliveryTypeEnum::NO_SHIPMENT->value; // 出荷無し
        $other = DeliveryTypeEnum::OTHER->value; // その他

        $cashOnDelivery = PaymentTypeEnum::CASH_ON_DELIVERY->value; //着払い：代金引換（コレクト）

        $nomal = ThreeTemperatureZoneTypeEnum::NORMAL->value; // 常温
        $cool = ThreeTemperatureZoneTypeEnum::COOL->value; // 冷蔵
        $frozen = ThreeTemperatureZoneTypeEnum::FROZEN->value; // 冷凍

        switch (true) {
            case $deliTypeCode == $yamato && $paymentTypeCode != $cashOnDelivery && $temperatureZone == $nomal:
                return SlipTypeEnum::YAMATO_SHIPMENT_PAID->value;

            case  $deliTypeCode == $yamato && $paymentTypeCode != $cashOnDelivery && in_array($temperatureZone, [$cool, $frozen]):
                return SlipTypeEnum::COOL_SHIPMENT_PAID->value;

            case $deliTypeCode == $yamato && $paymentTypeCode == $cashOnDelivery && $temperatureZone == $nomal:
                return SlipTypeEnum::COLLECT->value;

            case $deliTypeCode == $yamato && $paymentTypeCode == $cashOnDelivery && in_array($temperatureZone, [$cool, $frozen]):
                return SlipTypeEnum::COOL_COLLECT->value;

            case $deliTypeCode == $ownShipment:
                return SlipTypeEnum::OWN_SHIPMENT->value;

            case $deliTypeCode == $other:
                return SlipTypeEnum::OTHER->value;

            case $deliTypeCode == $noShipment:
                return SlipTypeEnum::NO_SHIPMENT->value;

            default:
                return "";
        }
    }
}
