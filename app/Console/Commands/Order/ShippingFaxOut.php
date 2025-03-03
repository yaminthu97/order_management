<?php

namespace App\Console\Commands\Order;

use App\Enums\BatchExecuteStatusEnum;
use App\Models\Order\Gfh1207\OrderDestinationModel;
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

class ShippingFaxOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ShippingFaxOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json? :  JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '出荷確定確認FAX送付状を作成し、バッチ実行確認へと出力する';

    // バッチ名
    protected $batchName = '出荷確定確認FAX送付状出力';

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

            // $searchInfo に必要なパラメータが含まれているか確認する
            $paramKey = ['t_order_destination_id'];

            // to check batch json parameter
            $checkResult = $this->checkBatchParameter->execute($this->argument('json'), $paramKey);

            if (!$checkResult) {
                //エラーメッセージはパラメータが不正です。
                throw new Exception(__('messages.error.invalid_parameter'), self::PRIVATE_THROW_ERR_CODE);
            }

            $param = $this->argument('json');

            $searchData = (json_decode($param, true))['search_info'];

            //パラメータから受注IDを取得する。
            $orderDestinationId = $searchData['t_order_destination_id'];

            $shippingData = $this->getShippingData($orderDestinationId);

            // to check excel data have or not condition
            if (!$shippingData) {
                // [出力対象のデータがありませんでした。] message save to 'execute_result'
                $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                    'execute_result' =>  __('messages.error.data_not_found2', ['datatype' => '出力']),
                    'execute_status' => BatchExecuteStatusEnum::SUCCESS->value
                ]);

                DB::commit();
                return;
            }

            $blockValues = $this->getblockValues($shippingData->toArray());

            $totalRowCnt = 1;

            $reportName = TemplateFileNameEnum::EXPXLSX_SHPPING_FAX->value;  // レポート名
            $templateFileName = $this->getTemplateFileName->execute($reportName, $accountId);  // template file name from database
            $templateFilePath = $this->getTemplateFilePath->execute($accountCode, $templateFileName);   // to get template file path

            $fileName = $reportName . '_' . $orderDestinationId;

            // テンプレートファイルパスが存在するかどうかをチェック
            if (empty($templateFilePath)) {
                // [テンプレートファイルが見つかりません。] メッセージを'execute_result'にセットする
                throw new Exception(__('messages.error.file_not_found2', ['file' => 'テンプレート']), self::PRIVATE_THROW_ERR_CODE);
            }

            // Excelにデータを書き込む
            $erm = new ExcelReportManager($templateFilePath);
            $erm->setValues(null, [], $blockValues);

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
                'file_path' => $savePath
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
             * - (エラーファイルパス)
             */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => ($e->getCode() == self::PRIVATE_THROW_ERR_CODE) ? $e->getMessage() : BatchExecuteStatusEnum::FAILURE->label(),
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value,
                'error_file_path' => null,
            ]);
        }
    }

    /**
     * Get Common data of Order Destination Id
     * @param t_order_destination_id
     * @return object
     */
    private function getShippingData($filter)
    {
        $result = OrderDestinationModel::where('t_order_destination_id', $filter)
            ->select(
                't_order_destination_id',
                't_order_hdr_id',
                'destination_name',
                'destination_tel',
                'destination_postal',
                'destination_address1',
                'destination_address2',
                'destination_address3',
                'destination_address4',
            )
            ->with([
                'orderHdr:t_order_hdr_id,order_name,order_tel1,order_postal,order_address1,order_address2,order_address3,order_address4',
                'deliHdrOne:t_deli_hdr_id,t_order_destination_id,deli_decision_date,deli_hope_date,deli_hope_time_name,deli_package_vol',
                'shippingLabels:t_order_destination_id,shipping_label_number'
            ])
            ->first();
        return $result;
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
    private function getblockValues($data)
    {
        $result = [];

        //when shipping labels are empty
        if (empty($data['shipping_labels'])) {
            $result[] = [
                'singleData' => $this->getSingleData($data),
                'listData' => [],
            ];
            //when shipping labels are not empty
        } else {
            foreach ($data['shipping_labels'] as $index => $shippingLabel) {
                $result[] = [
                    'singleData' => $this->getSingleData($data, $shippingLabel, $index),
                    'listData' => [],
                ];
            }
        }

        // データをブロックに結合する
        $blockValues = [
            'singleItems' => [
                '出荷日',
                '配送希望日',
                '配送日時',
                '送り状番号',
                '配送先氏名',
                '配送先電話番号',
                '配送先郵便番号',
                '配送先住所1',
                '配送先住所2',
                '注文主氏名',
                '注文主電話番号',
                '注文主郵便番号',
                '注文主住所1',
                '注文主住所2',
                '個口数',
                '枚数',
                '現在時刻'
            ],
            'data' => $result,
        ];

        return $blockValues;
    }

    /**
     * get single data array
     *
     * @param data
     * @param shippingLabel
     * @param index
     *
     * @return array
     */
    private function getSingleData($data, $shippingLabel = null, $index = 0)
    {
        $currentDate = Carbon::now()->format('Y年m月d日 H時i分s秒');
        return [
            Carbon::parse($data['deli_hdr_one']['deli_decision_date'])->format('Y/m/d'), // 出荷日(YYYY/MM/DD)
            Carbon::hasFormat($data['deli_hdr_one']['deli_hope_date'] ?? '', 'Y-m-d') ? Carbon::parse($data['deli_hdr_one']['deli_hope_date'])->format('Y/m/d') : '指定なし', // 配送希望日(YYYY/MM/DD) 空白の場合は「指定なし」とする。
            $data['deli_hdr_one']['deli_hope_time_name'], //配送日時
            $shippingLabel ? ($shippingLabel['shipping_label_number'] ?: '（伝票番号未登録）') : '（伝票番号未登録）', // 送り状番号(空白の場合は「（伝票番号未登録）」とする。) shipping lables
            $data['destination_name'], // 配送先氏名
            $data['destination_tel'], // 配送先電話番号
            // 配送先郵便番号(ハイフンあり郵便番号) + 配送先都道府県 + 配送先市区町村
            $data['destination_postal'] ? '〒' . preg_replace('/^(\d{3})(\d{4})$/', '$1-$2', $data['destination_postal']) : '',
            $data['destination_address1'] . $data['destination_address2'], // 配送先都道府県 + 配送先市区町村
            $data['destination_address3'] . $data['destination_address4'], // 配送先番地 + 配送先建物名
            $data['order_hdr']['order_name'], // 注文主氏名
            $data['order_hdr']['order_tel1'], // 注文主電話番号
            // 注文主郵便番号(ハイフンあり郵便番号) + 注文主都道府県 + 注文主市区町村
            $data['order_hdr']['order_postal'] ? '〒' . preg_replace('/^(\d{3})(\d{4})$/', '$1-$2', $data['order_hdr']['order_postal']) : '',
            $data['order_hdr']['order_address1'] . $data['order_hdr']['order_address2'], // 注文主都道府県 + 注文主市区町村
            $data['order_hdr']['order_address3'] . $data['order_hdr']['order_address4'], // 注文主番地 + 注文主建物名
            '計　' . $data['deli_hdr_one']['deli_package_vol'] . '　個口', // 個口数
            ($index + 1), // 枚数
            $currentDate, // 現在時刻(YYYY年MM月DD日 HH時MM分SS秒)
        ];
    }
}
