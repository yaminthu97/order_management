<?php

namespace App\Console\Commands\Order;

use App\Enums\BatchExecuteStatusEnum;
use App\Models\Order\Gfh1207\OrderHdrModel;
use App\Models\Order\Gfh1207\OrderDestinationModel;
use App\Models\Order\Gfh1207\OrderDtlModel;
use App\Models\Order\Base\OrderDtlNoshiModel;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Common\Gfh1207\CheckBatchParameter;
use App\Modules\Master\Gfh1207\Enums\TemplateFileNameEnum;
use App\Modules\Order\Gfh1207\GetExcelExportFilePath;
use App\Modules\Order\Gfh1207\GetTemplateFileName;
use App\Modules\Order\Gfh1207\GetTemplateFilePath;
use App\Services\ExcelReportManager;
use App\Services\TenantDatabaseManager;
use DateTime;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderConfirmationOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:OrderConfirmationOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '出力対象の受注IDを設定';

    // テンプレートファイルパス
    protected $templateFilePath;

    // テンプレートファイル名
    protected $templateFileName;

    // バッチ名
    protected $batchName = 'ご注文承り確認書';

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    // for report name
    protected $reportName = TemplateFileNameEnum::EXPXLSX_ORDER_CONFIRMATION->value;

    // for template file name
    protected $getTemplateFileName;

    // for template file path
    protected $getTemplateFilePath;

    // for excel export file path
    protected $getExcelExportFilePath;

    // for check batch parameter
    protected $checkBatchParameter;

    // for error code
    private const PRIVATE_THROW_ERR_CODE = -1;
        /**
     * Create a new command instance.
     */
    public function __construct(
        StartBatchExecuteInstructionInterface $startBatchExecute,
        EndBatchExecuteInstructionInterface $endBatchExecute,
        GetTemplateFileName $getTemplateFileName,
        GetTemplateFilePath $getTemplateFilePath,
        CheckBatchParameter $checkBatchParameter,
        GetExcelExportFilePath $getExcelExportFilePath,
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->getTemplateFileName = $getTemplateFileName;
        $this->getTemplateFilePath = $getTemplateFilePath;
        $this->checkBatchParameter = $checkBatchParameter;
        $this->getExcelExportFilePath = $getExcelExportFilePath;

        // get disk name s3
        $this->disk = config('filesystems.default', 'local');

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try{
            //バッチ実行ID
            $batchExecutionId  = $this->argument('t_execute_batch_instruction_id');

             /**
             * [共通処理] 開始処理
             * バッチ実行指示テーブル（t_execute_batch_instruction ）から該当バッチの取得と開始処理
             * t_execute_batch_instruction_id より該当バッチを取得し以下の情報を書き込む
             * - バッチ開始時刻
             */
            $batchExecute = $this->startBatchExecute->execute($batchExecutionId);


            $accountCode = $batchExecute->account_cd;
            $accountId   = $batchExecute->m_account_id;
            $batchType   = $batchExecute->execute_batch_type;

            if (app()->environment('testing')) {
                // テスト環境の場合
                TenantDatabaseManager::setTenantConnection($accountCode . '_db_testing');
            } else {
                TenantDatabaseManager::setTenantConnection($accountCode . '_db');
            }
        }catch(Exception $e){
            // エラーメッセージのログエラーをlaravel.logに書き込みます
            Log::error('error_message : ' . $e->getMessage());
            return;
        }

        DB::beginTransaction();

        try{
             // 必須パラメータに
             $requiredFields = [ 't_order_hdr_id'];
             $checkResult = $this->checkBatchParameter->execute($this->argument('json'), $requiredFields);  // バッチのjsonパラメータを確認するには
             if (!$checkResult) {
                // [パラメータが不正です。] message save to 'execute_result'
                throw new Exception(__('messages.error.invalid_parameter'), self::PRIVATE_THROW_ERR_CODE);
            }

            $searchCondition = (json_decode($this->argument('json'), true))['search_info'];  // すべての検索パラメータに対して
            $templateFileName = $this->getTemplateFileName->execute($this->reportName, $accountId);    // データベースからのテンプレート ファイル名
            $templateFilePath = $this->getTemplateFilePath->execute($accountCode, $templateFileName);   // テンプレートファイルのパスを取得するには
            // テンプレートファイルのパスが存在するかどうかを確認する条件
             if (empty($templateFilePath)) {
                // [テンプレートファイルが見つかりません。] message save to 'execute_result'
                throw new Exception(__('messages.error.file_not_found', ['file' => 'テンプレート']), self::PRIVATE_THROW_ERR_CODE);
            }

            // パラメータで渡された受注IDを受注データベースに存在するかどうかを確認する
            $orderId = $searchCondition['t_order_hdr_id'][0];
            $orderExists = OrderHdrModel::where('t_order_hdr_id', $orderId)->exists();
            if (!$orderExists) {
                // 受注基本テーブルから受注データ
                throw new Exception(__('messages.error.data_not_found',['data'=>"請求書出力履歴ID",'id'=>$orderId] ),self::PRIVATE_THROW_ERR_CODE);
            }

            $dataList = $this->getData($orderId);

           // 抽出結果がない場合、[出力対象のデータがありませんでした。]メッセージを'execute_result'にセットする
           if (empty($dataList)) {
            // [出力対象のデータがありませんでした。] message save to 'execute_result'
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' =>  __('messages.error.data_not_found2', ['datatype' => '出力']),
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value,
            ]);

            DB::commit();
            return ;
            }
            $values = $dataList['tableHeaders'];
            $continuousData = $dataList['tableData'];
            $blockData = $dataList['tableBlock'];

            $erm = new ExcelReportManager($templateFilePath);
            $erm->setValues($values,[],$blockData);
            $savePath = $this->getExcelExportFilePath->execute($accountCode, $batchType, $batchExecutionId);
            $result = $erm->save($savePath);

            //アップロード許可を許可するかどうかを確認します
            if (!$result) {
                // [AWS S3へのファイルのアップロードに失敗しました。] message save to 'execute_result'
                throw new \Exception(__('messages.error.upload_s3_failed'), self::PRIVATE_THROW_ERR_CODE);
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
                'execute_result' => __('messages.info.notice_output_count', ['count' => count($dataList)]),  // 〇〇件出力しました。
                'execute_status' => BatchExecuteStatusEnum::SUCCESS->value,
                'file_path'      => $savePath,
            ]);

            DB::commit();

        } catch(Exception $e){
            DB::rollBack();

            // エラーメッセージのログエラーをlaravel.logに書き込みます
            Log::error('error_message : ' . $e->getMessage());

            /**
             * [共通処理] 終了処理
             * バッチ実行指示テーブルの該当バッチに以下の情報を書き込む
             * - バッチ終了時刻
             * - (実行状態)
             * - (実行結果)
             */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => ($e->getCode() == self::PRIVATE_THROW_ERR_CODE) ? $e->getMessage() : BatchExecuteStatusEnum::FAILURE->label(),
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value
            ]);
        }

    }

    /**
    * 検索パラメータに関連するデータを取得するには
    *
    * @param array $param 受注ID
    * @return array ( search data )
    */
    private function getData($orderId)
    {
        $orderData          = $this->fetchOrderData($orderId);
        $data               = $this->generateExcelHeaders($orderData);
        $orderDestinations  = $orderData['order_destination'];
        $continuousData = [];
        $blockData          = $this->generateExcelBlock($orderDestinations);

        $result = [];
        $result = [
            'tableHeaders' => $data,
            'tableData'    => $continuousData,
            'tableBlock'   => $blockData
        ];

        return $result;
    }

    /**
    * リレーションシップを含むデータをフェッチする
    *
    * @param array $param torderhdrId
    * @return array ( Excelバインディング用のデータが必要 )
    */
    private function fetchOrderData($orderId)
    {
        $order = OrderHdrModel::select(['t_order_hdr_id','order_datetime','order_total_price','standard_total_price','reduce_total_price','standard_tax_price','reduce_tax_price',
                                'billing_corporate_name','billing_division_name','billing_name','billing_postal','order_email1','billing_address1','billing_address2','billing_address3','billing_address4',
                                'billing_tel1','order_corporate_name','order_division_name','order_name','order_postal','order_address1','order_address2','order_address3','order_address4','order_tel1',
                                'discount','standard_discount','reduce_discount'
                            ])
                ->with([
                    // 1つのt_order_hdr_idは複数のt_order_destination_idを持つ（hasManyリレーション）
                    // relation with  ONE t_order_hdr_id (hasMany) t_order_destination_id
                    'orderDestination' => function ($query) {
                        $query->select([
                            't_order_destination_id',
                            't_order_hdr_id',
                            'destination_company_name','destination_division_name','destination_name',
                            'destination_postal',
                            'destination_address1','destination_address2','destination_address3','destination_address4',
                            'destination_tel','deli_hope_date','deli_hope_time_name','invoice_comment',
                            'shipping_fee','payment_fee','wrapping_fee',
                        ])
                        // 1つのt_order_destination_idは複数のt_order_dtl_idを持つ（hasManyリレーション）。
                        // relation with ONE t_order_destination_id (hasMany) t_order_dtl_id
                        ->with(['orderDtl' => function ($query) {
                            $query->select([
                                't_order_dtl_id', 't_order_destination_id', 'sell_cd',
                                'sell_name', 'order_sell_vol', 'order_sell_price'
                            ])
                            ->with([
                                //1つのt_order_detail_idは複数のt_order_dtl_attachment_item_idを持つ（hasManyリレーション）
                                //relation with ONE t_order_detail_id (hasMany) t_order_dtl_attachment_item_id
                                'orderDtlAttachmentItem' => function ($query) {
                                    $query->select([
                                        't_order_dtl_attachment_item_id','t_order_dtl_id',
                                        'attachment_vol'
                                    ]);
                                },
                                //1つのt_order_detail_idは1つのt_order_dtl_noshi_idを持つ（hasOneリレーション）。
                                //relation with ONE t_order_detail_id (hasOne) t_order_dtl_noshi_id
                                'orderDtlNoshi' => function ($query) {
                                    $query->select([
                                        't_order_dtl_noshi_id','t_order_dtl_id',
                                        'noshi_type', 'name1', 'name2', 'name3',
                                        'name4', 'name5', 'omotegaki'
                                    ]);
                                }
                            ]);
                        }]);
                    },
                ])
                ->where('t_order_hdr_id', $orderId)
                ->first();

            // 注文データを配列に変換する
            $orderArray = $order->toArray();
            // 関係を検証する
            if (empty($orderArray['order_destination'])) {
                Log::error("指定された注文 ID には注文の宛先がありません。");
                throw new Exception(__('messages.error.record_not_found'), self::PRIVATE_THROW_ERR_CODE);
            }
            foreach ($orderArray['order_destination'] as $destination) {
                if (empty($destination['order_dtl'])) {
                    // エラーメッセージを含む例外をスロー
                    Log::error("配送先 ID の注文詳細がありません。");
		            throw new Exception(__('messages.error.record_not_found'), self::PRIVATE_THROW_ERR_CODE);
                }
                foreach ($destination['order_dtl'] as $detail) {
                    if (empty($detail['order_dtl_attachment_item'])) {
                        Log::error("詳細 ID の添付ファイル項目がありません。");
		                throw new Exception(__('messages.error.record_not_found'), self::PRIVATE_THROW_ERR_CODE);
                    }
                    if (empty($detail['order_dtl_noshi'])) {
                        Log::error("詳細 ID の添付ファイル項目がありません。");
		                throw new Exception(__('messages.error.record_not_found'), self::PRIVATE_THROW_ERR_CODE);
                    }
                }
            }
            return $orderArray;
    }

    /**
    * Excel テンプレート ヘッダーのデータを準備する
    *
    * @param array $OrderData Excel テーブルに追加するデータの配列
    *
    * @return array ( Excel テンプレート ヘッダー )
    **/
    private function generateExcelHeaders($orderData)
    {
        return [
            'items' => [
                        '受注日','請求金額','標準対象額','標準税','軽減対象額','軽減税','請求先名',
                        '請求先郵便','注文牛郵便','請求先住所','請求先電話','注文主名','注文主郵便','注文主住所','注文主電話','割引金額','標準割引','軽減割引'
                    ],
            'data'  => [
                isset($orderData['order_datetime'])
                ? (new DateTime($orderData['order_datetime']))->format("Y/m/d")
                : null,//受注日
                $orderData['order_total_price'] ?? null,//請求金額
                $orderData['standard_total_price'] ?? null,//標準対象額
                $orderData['reduce_total_price'] ?? null,//標準税
                $orderData['standard_tax_price'] ?? null,//軽減対象額
                $orderData['reduce_tax_price'] ?? null,//軽減税
                implode(' ', array_filter([
                    $orderData['billing_corporate_name'] ?? null,
                    $orderData['billing_division_name'] ?? null,
                    $orderData['billing_name'] ?? null,
                ])),//請求先名
                $orderData['billing_postal'] ?? null,//請求先郵便
                $orderData['order_email1'] ?? null, //注文牛郵便
                implode(' ', array_filter([
                    $orderData['billing_address1'] ?? null,
                    $orderData['billing_address2'] ?? null,
                    $orderData['billing_address3'] ?? null,
                    $orderData['billing_address4'] ?? null,
                ])),//請求先住所
                $orderData['billing_tel1'] ?? null,//請求先電話
                implode(' ', array_filter([
                    $orderData['order_corporate_name'] ?? null,
                    $orderData['order_division_name'] ?? null,
                    $orderData['order_name'] ?? null,
                ])),//注文主名
                $orderData['order_postal'] ?? null,//注文主郵便
                implode(' ', array_filter([
                    $orderData['order_address1'] ?? null,
                    $orderData['order_address2'] ?? null,
                    $orderData['order_address3'] ?? null,
                    $orderData['order_address4'] ?? null,
                ])),//注文主住所
                $orderData['order_tel1'] ?? null,//注文主電話
                $orderData['discount'] ?? null,//割引金額
                $orderData['standard_discount'] ?? null,//標準割引
                $orderData['reduce_discount'] ?? null,//軽減割引
            ]
        ];
    }

    /**
     * Excel テーブルの連続値を取得
     *
     * @param array $dataList Excel テーブルに追加するデータの配列
     *
     * @return array (Excel テーブルデータ)
     *               - listItems: テーブルのヘッダー項目の配列
     *               - data: 各行のデータを含む配列
    **/
    private function generateExcelBlock($orderDestinations)
    {

        $blockDatas = [];
        $counter = 1;
        $totalFee = 0;
        foreach ($orderDestinations as $orderDestination) {
            // 注文先ごとの処理 listData
            if (isset($orderDestination['order_dtl']) && is_array($orderDestination['order_dtl'])) {
                foreach ($orderDestination['order_dtl'] as $orderDetail) {
                    $noshiDetails = array_filter([
                        $orderDetail['order_dtl_noshi']['noshi_type'] ?? null,
                        $orderDetail['order_dtl_noshi']['name1'] ?? null,
                        $orderDetail['order_dtl_noshi']['name2'] ?? null,
                        $orderDetail['order_dtl_noshi']['name3'] ?? null,
                        $orderDetail['order_dtl_noshi']['name4'] ?? null,
                        $orderDetail['order_dtl_noshi']['name5'] ?? null,
                        $orderDetail['order_dtl_noshi']['omotegaki'] ?? null,
                    ]);

                    $noshiDetailsString = implode(' ', $noshiDetails);

                    $attachmentVolume = null;
                    if (isset($orderDetail['order_dtl_attachment_item'])) {
                        foreach ($orderDetail['order_dtl_attachment_item'] as $orderAttachment) {
                            $attachmentVolume = $orderAttachment['attachment_vol'] ?? null;
                        }
                    }

                    $orderAmount = isset($orderDetail['order_sell_vol'], $orderDetail['order_sell_price'])
                                    ? $orderDetail['order_sell_vol'] * $orderDetail['order_sell_price']
                                    : 0;

                    $totalFee += $orderAmount;

                    $listData[] = [
                        $orderDetail['sell_cd'] ?? null,  // 商品コード
                        $orderDetail['sell_name'] ?? null,  // 商品名
                        $noshiDetailsString,  // 熨斗
                        $orderDetail['order_sell_vol'] ?? null,  // 受注数量
                        $orderAmount,  // 受注金額
                        $attachmentVolume,  // 手提げ数量
                    ];
                }
            }
            // singleData データを準備する
            $singleData = [
                isset($orderDestination['t_order_hdr_id'])
                    ? $orderDestination['t_order_hdr_id'] . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT)
                    : null,// 配送番号
                implode(' ', array_filter([
                    $orderDestination['destination_company_name'] ?? null,
                    $orderDestination['destination_division_name'] ?? null,
                    $orderDestination['destination_name'] ?? null
                ])),// 配送先名
                $orderDestination['destination_postal'] ?? null,//配送先郵便"
                implode(' ', array_filter([
                    $orderDestination['destination_address1'] ?? null,
                    $orderDestination['destination_address2'] ?? null,
                    $orderDestination['destination_address3'] ?? null,
                    $orderDestination['destination_address4'] ?? null
                ])),// 配送先住所"
                $orderDestination['destination_tel'] ?? null,//配送先電話
                $orderDestination['sender_name'] ?? null,//配送先送り主
                $orderDestination['deli_hope_date'] ?? null,//配送希望日
                $orderDestination['deli_hope_time_name'] ?? null,//配送時間帯
                $orderDestination['invoice_comment'] ?? null,//送り状コメント
                $totalFee ?? null//受注金額計
            ];
            $counter++;
            $data[] = [
                'singleData'=>$singleData,
                'listData' => $listData
            ];
            // データをブロックに結合する
            $blockDatas = [
                'singleItems' => ['配送番号', '配送先名', '配送先郵便', '配送先住所','配送先電話','配送先送り主','配送希望日','配送時間帯','送り状コメント','受注金額計'],
                'listItems' => ['商品コード', '商品名', '熨斗', '受注数量', '受注金額', '手提げ数量'],
                'data' => $data,
            ];
        }
        return $blockDatas;
    }
}
