<?php

namespace App\Console\Commands\Goto;

use App\Enums\BatchExecuteStatusEnum;
use App\Mail\EcOrderXlsxSendMail;
use App\Models\Master\Base\ShopGfhModel;
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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class EcOrderXlsxSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:EcOrderXlsxSend {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '通販の売上と受注残をEXCEL出力し、メール送信する。';

    // バッチ名
    protected $batchName = '通信販売売上・受注残(EXCEL)';

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

    private const PRIVATE_THROW_ERR_CODE = -1; // specify customize error code
    private const SEARCH_RANGE_DAYS = 100; // specify day range
    private const EMPTY_DATA_ROW_COUNT = 0;

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
            // バッチ実行ID
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
                // 本番環境の場合
                TenantDatabaseManager::setTenantConnection($accountCode . '_db');
            }
        } catch (Exception $e) {
            // write the log error in laravel.log for error message
            Log::error('error_message : ' . $e->getMessage());
            return;
        }

        DB::beginTransaction();
        try {
            // レポート名
            $reportName = TemplateFileNameEnum::SEND_EC_ORDER_XLSX->value;

            // データベースからのテンプレートファイル名
            $templateFileName = $this->getTemplateFileName->execute($reportName, $accountId);

            // テンプレートファイルパス
            $templateFilePath = $this->getTemplateFilePath->execute($accountCode, $templateFileName);

            // テンプレートファイルパスが存在するかどうかをチェック
            if (empty($templateFilePath)) {
                // [テンプレートファイルが見つかりません。] メッセージを'execute_result'にセットする
                throw new Exception(__('messages.error.file_not_found2', ['file' => 'テンプレート']), self::PRIVATE_THROW_ERR_CODE);
            }

            // 出荷基本テーブルと出荷明細テーブルから売上データを生成する。
            $salesRecords = $this->getSalesRecords();

            // レコードの総数
            $recordCount = count($salesRecords);

            // check salesRecords array exist or not
            if ($recordCount === self::EMPTY_DATA_ROW_COUNT) {
                // [出力対象のデータがありませんでした。] message save to 'execute_result'
                $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                    'execute_result' =>  __('messages.error.data_not_found2', ['datatype' => '出力']),
                    'execute_status' => BatchExecuteStatusEnum::SUCCESS->value
                ]);

                DB::commit();
                return ;
            }

            // Prepare data for Excel export.
            $continuousValues = $this->getContinuousValues($salesRecords);

            // Excelにデータを書き込む
            $erm = new ExcelReportManager($templateFilePath);
            $erm->setValues(null, $continuousValues);

            // s3 file path
            $fileSavePath = $this->getExcelExportFilePath->execute($accountCode, $batchType, $batchExecutionId);

            // Save the Excel file to s3.
            $result = $erm->save($fileSavePath);

            // check to upload permission allow or not allow
            if (!$result) {
                // [AWS S3へのファイルのアップロードに失敗しました。] message save to 'execute_result'
                throw new Exception(__('messages.error.upload_s3_failed'), self::PRIVATE_THROW_ERR_CODE);
            }

            // S3からローカルにファイルをダウンロードする
            $tempLocalPath = $this->downloadFileFromS3($fileSavePath);

            // 作成したExcelファイルをメール送信する。
            $this->sendMail($tempLocalPath);
            /**
             * [共通処理] 終了処理
             * バッチ実行指示テーブルの該当バッチに以下の情報を書き込む
             * - バッチ終了時刻
             * - (実行状態)
             * - (実行結果)
             * - (格納ファイルパス)
             */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => __('messages.info.notice_output_count', ['count' => "通信販売売上・受注残(EXCEL)が{$recordCount}"]), // [通信販売売上・受注残(EXCEL)が〇〇件出力しました。] message save to 'execute_result'
                'execute_status' => BatchExecuteStatusEnum::SUCCESS->value,
                'file_path' => $fileSavePath,
            ]);

            DB::commit();

        } catch (Exception $e) {
            // Roll back the transaction on error.
            DB::rollBack();

            // write the log error in laravel.log for error message
            Log::error('error_message : ' . $e->getMessage());

            /**
             * [共通処理] エラー時の処理
             * バッチ実行指示テーブルの該当バッチに以下の情報を書き込む
             * - バッチ終了時刻
             * - (実行状態)
             * - (実行結果)
             */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => ($e->getCode() == self::PRIVATE_THROW_ERR_CODE) ? $e->getMessage() : BatchExecuteStatusEnum::FAILURE->label(),
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value,
            ]);
        }
    }

    /**
      * Retrieves and processes sales records within a defined date range.
      *
      * This function extracts sales data from the `DeliHdrModel` and its related models, then processes the data
      * by grouping it and calculating various totals such as 出荷基本.配送希望日, 出荷明細.商品ページコード, 出荷基本.出荷指示日時, 出荷基本.配送希望日, 出荷基本.顧客ID, 出荷基本.送付先ID. The result is returned as a summarized dataset.
      *
      * @return \Illuminate\Support\Collection The processed sales summary data.
     */
    private function getSalesRecords()
    {
        // Define the date range: from 100 days ago to the current date
        $startDate = Carbon::now()->subDays(self::SEARCH_RANGE_DAYS)->format('Y-m-d');
        $endDate =  Carbon::now()->format('Y-m-d');

        // Extract sales data from the DeliHdrModel with eager loading
        $salesRecords = DeliHdrModel::with([
            'deliveryDtl:t_delivery_dtl_id,t_order_hdr_id,t_order_dtl_id,order_destination_seq,order_dtl_seq,order_sell_price,order_sell_vol,tax_price,t_delivery_hdr_id,sell_cd,sell_name', // DeliveryDtlModel
            'deliveryDtl.orderDtl:t_order_dtl_id,t_order_hdr_id,t_order_destination_id,order_destination_seq,order_dtl_seq,order_sell_price,order_sell_vol,tax_price,attachment_item_group_id', // OrderDtlModel
            'deliveryDtl.orderDtl.orderDtlAttachmentItem:t_order_dtl_attachment_item_id,m_account_id,t_order_hdr_id,t_order_dtl_id,order_dtl_seq,attachment_item_id,attachment_item_cd,attachment_item_name,attachment_vol,group_id', // OrderDtlAttachmentItemModel
            'deliveryDtl.orderDtl.orderDtlAttachmentItem.group:m_itemname_types_id,m_account_id,delete_flg,m_itemname_type,m_itemname_type_code,m_itemname_type_name', // ItemnameTypeModel
        ])
        ->select('t_deli_hdr_id', 'm_account_id', 't_order_hdr_id', 'ec_order_num', 'm_cust_id', 'order_datetime', 'deli_hope_date', 'destination_id', 'sell_total_price', 'shipping_fee', 'payment_fee', 'tax_price', 'deli_instruct_timestamp', 'order_name', 'destination_name', 'deli_decision_date') // DeliHdrModel
        ->whereDate('order_datetime', '>=', $startDate) // Filter by startDate
        ->whereDate('order_datetime', '<=', $endDate) // Filter by endDate
        ->get();

        // Flatten and map the data to structure it by combining relevant fields from related tables
        $salesRecordGroups = $salesRecords->flatMap(function ($deliHdr) {
            // Create an array for t_deli_hdr data (出荷基本)
            $shipmentHeader = [
                'deli_hope_status' => $deliHdr->deliHopeDateStatus,
                'deli_hope_date' => $deliHdr->deli_hope_date, // 出荷基本.配送希望日
                'deli_instruct_timestamp' => $deliHdr->deli_instruct_timestamp, // 出荷基本.出荷指示日時
                'm_cust_id' => $deliHdr->m_cust_id, // 出荷基本.顧客ID
                'destination_id' => $deliHdr->destination_id, // 出荷基本.送付先ID
                'order_name' => $deliHdr->order_name, // 出荷基本.注文主氏名
                'destination_name' => $deliHdr->destination_name, // 出荷基本.配送先氏名
                'shipping_fee' => $deliHdr->shipping_fee, // 出荷基本.送料
                'deli_decision_date' => $deliHdr->deli_decision_date, // 出荷基本.出荷確定日
                'order_datetime' => $deliHdr->order_datetime, // 受注基本.受注日時
                't_order_hdr_id' => $deliHdr->t_order_hdr_id, // 出荷基本.受注ID
            ];
            // Process each 出荷明細 record within the current 出荷基本
            return $deliHdr->deliveryDtl->map(function ($deliveryDtl) use ($shipmentHeader) {
                // Create an array for 出荷明細
                $shipmentDetail = [
                    'deli_hope_status' => $shipmentHeader['deli_hope_status'],
                    'deli_hope_date' => $shipmentHeader['deli_hope_date'], // 出荷基本.配送希望日
                    'deli_instruct_timestamp' => $shipmentHeader['deli_instruct_timestamp'], // 出荷基本.出荷指示日時
                    'm_cust_id' => $shipmentHeader['m_cust_id'], // 出荷基本.顧客ID
                    'destination_id' => $shipmentHeader['destination_id'], // 出荷基本.送付先ID
                    'order_name' => $shipmentHeader['order_name'], // 出荷基本.注文主氏名
                    'destination_name' => $shipmentHeader['destination_name'], // 出荷基本.配送先氏名
                    'shipping_fee' => $shipmentHeader['shipping_fee'], // 出荷基本.送料
                    'deli_decision_date' => $shipmentHeader['deli_decision_date'], // 出荷基本.出荷確定日
                    'order_datetime' => $shipmentHeader['order_datetime'], // 受注基本.受注日時
                    't_order_hdr_id' => $shipmentHeader['t_order_hdr_id'], // 出荷基本.受注ID
                    'sell_cd' => $deliveryDtl->sell_cd, // 出荷明細.商品ページコード
                    'sell_name' => $deliveryDtl->sell_name, // 出荷明細.商品ページ名
                    'tax_price' => $deliveryDtl->tax_price, // 出荷明細.販売単価
                    'order_sell_vol' => $deliveryDtl->order_sell_vol, // 出荷明細.受注数量
                    'order_sell_price' => $deliveryDtl->order_sell_price, // 出荷明細.販売単価
                ];
                // Further map and retrieve attachment items for the 出荷明細
                return $deliveryDtl->orderDtl->orderDtlAttachmentItem->map(function ($orderDtlAttachmentItem) use ($shipmentDetail) {
                    return [
                        'deli_hope_status' => $shipmentDetail['deli_hope_status'],
                        'deli_hope_date' => $shipmentDetail['deli_hope_date'], // 出荷基本.配送希望日
                        'deli_instruct_timestamp' => $shipmentDetail['deli_instruct_timestamp'], // 出荷基本.出荷指示日時
                        'm_cust_id' => $shipmentDetail['m_cust_id'], // 出荷基本.顧客ID
                        'destination_id' => $shipmentDetail['destination_id'], // 出荷基本.送付先ID
                        'order_name' => $shipmentDetail['order_name'], // 出荷基本.注文主氏名
                        'destination_name' => $shipmentDetail['destination_name'], // 出荷基本.配送先氏名
                        'shipping_fee' => $shipmentDetail['shipping_fee'], // 出荷基本.送料
                        'sell_cd' => $shipmentDetail['sell_cd'], // 出荷明細.商品ページコード
                        'sell_name' => $shipmentDetail['sell_name'], // 出荷明細.商品ページ名
                        'tax_price' => $shipmentDetail['tax_price'], // 出荷明細.販売単価
                        'order_sell_vol' => $shipmentDetail['order_sell_vol'], // 出荷明細.受注数量
                        'order_sell_price' => $shipmentDetail['order_sell_price'], // 出荷明細.販売単価
                        'deli_decision_date' => $shipmentDetail['deli_decision_date'], // 出荷基本.出荷確定日
                        'order_datetime' => $shipmentDetail['order_datetime'], // 受注基本.受注日時
                        't_order_hdr_id' => $shipmentDetail['t_order_hdr_id'], // 出荷基本.受注ID
                        'attachment_item_cd' => $orderDtlAttachmentItem->attachment_item_cd, // 受注明細付属品.付属品コード
                        'attachment_vol' => $orderDtlAttachmentItem->attachment_vol, // 受注明細付属品.付属品数量
                        'm_itemname_types_id' => $orderDtlAttachmentItem->group->m_itemname_types_id, // 項目名称マスタ.項目名称マスタID
                        'm_itemname_type' => $orderDtlAttachmentItem->group->m_itemname_type, // 項目名称マスタ.項目名称区分
                        'm_itemname_type_code' => $orderDtlAttachmentItem->group->m_itemname_type_code, // 項目名称マスタ.項目CODE
                        'm_itemname_type_name' => $orderDtlAttachmentItem->group->m_itemname_type_name, // 項目名称マスタ.項目名称
                    ];
                });
            });
        })->flatMap(function ($collection) {
            // Flatten the collection after mapping and create a single-level collection
            return $collection;
        })->groupBy(function ($item) {
            // Group by combination of deli_plan_date, sell_cd, and sell_name
            return $item['deli_hope_date'] . '-' . $item['sell_cd'] . '-' . $item['deli_instruct_timestamp'] . '-' . $item['m_cust_id'] . '-' . $item['destination_id'];
        });

        // Process the grouped data, calculating totals and other necessary values
        $salesSummaryData = $salesRecordGroups->map(function ($group) {
            // 受注数量
            $totalOrderSellVol = $group->filter(function ($item) {
                return !is_null($item['deli_decision_date']);
            })->sum('order_sell_vol');

            // 未出荷数量
            $totalUnshippedOrderVol = $group->filter(function ($item) {
                return is_null($item['deli_decision_date']);
            })->sum('order_sell_vol');

            // 受注金額 = 販売単価　×　受注数量
            $totalOrderSellPrice = $group->filter(function ($item) {
                return !is_null($item['deli_decision_date']);
            })->sum(function ($item) {
                return $item['order_sell_price'] * $item['order_sell_vol'];
            });

            // 受注送料
            $orderShippingFee = $group->filter(function ($item) {
                return !is_null($item['deli_decision_date']);
            })->sum('shipping_fee');

            // 未出荷金額 = 販売単価　×　受注数量
            $unshippedAmount = $group->filter(function ($item) {
                return is_null($item['deli_decision_date']);
            })->sum(function ($item) {
                return $item['order_sell_price'] * $item['order_sell_vol'];
            });

            // 未出荷送料
            $unshippedShippingFee = $group->filter(function ($item) {
                return is_null($item['deli_decision_date']);
            })->sum('shipping_fee');

            // 銀シール数量
            $silverSealVol = $group->filter(function ($item) {
                return ($item['m_itemname_type_code'] == \App\Enums\AttachmentGroupEnum::BUTSU->value) && ($item['attachment_item_cd'] == \App\Enums\AttachmentItemCodeEnum::SILVER_SEAL->value);
            })->sum('attachment_vol');

            // Determine the status (whether delivery hope date exists)
            $status = $group->pluck('deli_hope_status')->first();

            // deli_hope_date
            $deliHopeDate = $group->pluck('deli_hope_date')->first();

            // Return the processed data for each group
            return [
                '配達希望有無' => "希望{$status}", // 配達希望有無
                '商品コード' => $group->pluck('sell_cd')->first(), // 商品コード
                '出荷指示指定日' => Carbon::parse($group->pluck('deli_instruct_timestamp')->first())->format('Ymd'), // 出荷指示指定日
                '配達希望日' => Carbon::hasFormat($deliHopeDate ?? '', 'Y-m-d') ?  Carbon::parse($deliHopeDate)->format('Ymd') : '', // 配達希望日
                '受注日' => Carbon::parse($group->pluck('order_datetime')->first())->format('Ymd'), // 受注日
                // ※1 //
                '受注数量合計' => $totalOrderSellVol, // 受注数量合計
                '受注税込金額' => $totalOrderSellPrice, // 受注税込金額
                '受注税込送料' => $orderShippingFee, // 受注税込送料
                // ※2 //
                '未出荷数量合計' => $totalUnshippedOrderVol, // 未出荷数量合計
                '未出荷税込金額' => $unshippedAmount, // 未出荷税込金額
                '未出荷税込送料' => $unshippedShippingFee, // 未出荷税込送料
                '受注ナンバー' => $group->pluck('t_order_hdr_id')->first(), // 受注ナンバー
                '依頼主コード' => $group->pluck('m_cust_id')->first(), // 依頼主コード
                '依頼主名' => $group->pluck('order_name')->first(), // 依頼主名
                '届け先コード' => $group->pluck('destination_id')->first(), // 届け先コード
                '届け先名' => $group->pluck('destination_name')->first(), // 届け先名
                '銀シール数量' => $silverSealVol // 銀シール数量
            ];
        })
        ->values() // Reindex the array numerically starting from 0
        ->sortBy('deli_instruct_timestamp'); // Sort by deli_instruct_timestamp ascending

        // Return the final processed data
        return $salesSummaryData;
    }

    /**
     * Extract continuous values from a list of data based on specific keys.
     *
     * This function processes an input list (`$dataList`) of data items and extracts specific values from each item.
     * It returns the extracted data in a structured format, where:
     * - `items` contains the list of keys being retrieved from each item.
     * - `data` contains the actual extracted values, organized by those keys.
     *
     * The function is useful for organizing and processing tabular data, where each row corresponds to a set of values for each key.
     *
     * @param array $dataList List of data items to extract values from.
     * @return array Processed continuous values with `items` and `data` keys.
     */
    private function getContinuousValues($dataList)
    {
        // Define the keys (columns) to be extracted from each item in the data list
        $keys = [
            '配達希望有無', '商品コード',  '出荷指示指定日',  '配達希望日',  '受注日',  '受注数量合計',
            '受注税込金額', '受注税込送料', '未出荷数量合計',  '未出荷税込金額',  '未出荷税込送料',
            '受注ナンバー', '依頼主コード', '依頼主名', '届け先コード', '届け先名', '銀シール数量'
        ];

        // Initialize an empty array to store the processed data
        $data = [];
        // Loop through each item in the provided data list
        foreach ($dataList as $item) {
            // Initialize an array to store the row's values based on the defined keys
            $row = [];
            // Loop through each key and extract its value from the current item
            foreach ($keys as $key) {
                // Use the key to extract the value, and if it's not available, set it to null
                $row[] = $item[$key] ?? null;
            }
            // Append the row of values to the data array
            $data[] = $row;
        }

        // Organize the extracted values into a structured array to return
        $continuousValues = [
            'items' => $keys, // List of keys used for extraction
            'data' => $data, // Extracted data, structured by rows
        ];
        // Return the structured continuous values
        return $continuousValues;
    }

    /**
     * downlaod the xlsx file from s3 server
     *
     * This method downlaod the file from s3 and then save into the created local path
     *
     *
     * @param  string   $filePath  s3 server file path
     *
     *
     * @return string   $localPath file saving path
     */
    public function downloadFileFromS3($filePath)
    {
        // Open a stream to read the file from the S3 storage disk
        $stream = Storage::disk(config('filesystems.default', 'local'))->readStream($filePath);

        // check file have or not
        if (!$stream) {
            // [日別商品別出荷未出荷ファイルが見つかりません。（:filePath] message save to 'execute_result'
            throw new Exception(__('messages.error.file_not_found', ['file' => '日別商品別出荷未出荷', 'path' => $filePath]), self::PRIVATE_THROW_ERR_CODE);
        }

        // Define the local path to temporarily save the file in the system's temporary directory
        $localPath = sys_get_temp_dir() . '/' . basename($filePath);

        // Open or create a local file at the specified path in write mode ('w+')
        // If the file doesn't exist, it will be created; if it does exist, it will be truncated
        $localFile = fopen($localPath, 'w+');

        // Check if the file handle was successfully created
        if ($localFile === false) {
            // if the file could not be created or opened
            throw new Exception(__('messages.error.file_create_failed'));
        }

        // Write the stream contents to the local file
        stream_copy_to_stream($stream, $localFile);

        // Close the streams
        fclose($localFile);
        fclose($stream);

        return $localPath;
    }

    /**
     * 作成したExcelをそれぞれメール送信する。
     * 原田設定テーブルより送信先のメールアドレスを取得する。
     * 各Excelファイルにつき、メールを送信する。
     *
     * This method sends an email when a file is extracted.
     * The given process string.
     *
     * @param  string   $process  file name
     *
     * Used try/catch
     * If mail sending is something wrong, goto the main function(handle) catch and save to execute_result
     * after sending mail, remove temporary file
     */
    private function sendMail($filePath)
    {

        try {
            // 原田設定テーブルより送信先のメールアドレスを取得する。
            $mailData = ShopGfhModel::query()
                ->select('mail_address_ec_uriage as to_mail', 'mail_address_from as from_mail')
                ->orderBy('m_shop_gfh_id', 'desc')
                ->first();

            if ($mailData) {
                $toEmails = explode(',', $mailData['to_mail']);// get receiver email address
                $fromEmail = $mailData['from_mail']; // get sender email address

                // check fromEmail and toEmail address
                foreach ($toEmails as $toEmail) {
                    if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL) || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception(__('messages.error.process_failed', ['process' => 'メール送信処理']));
                    }
                }

                // check mail server configuration
                $mailerConfig = config('mail.mailers.smtp');
                if (empty($mailerConfig['host']) || empty($mailerConfig['port']) || empty($mailerConfig['username']) || empty($mailerConfig['password'])) {
                    throw new Exception(__('messages.error.process_failed', ['process' => 'メール送信処理']));
                }

                // attchfile Name
                $fileName = Carbon::now()->format('Ymd') . 'Chuzan';

                // テンプレートファイルを使用してメールを送信する
                Mail::to($toEmails)->send(new EcOrderXlsxSendMail($filePath, $fileName, $fromEmail));

                // ファイルを削除
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            } else {
                // Write the log in error.log file
                Log::error(__('messages.error.batch_error.data_not_found3', ['data' => 'メールアドレス']));
            }
        } catch (Exception $e) {
            // ファイルを削除
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            // write the error in laravel.log file
            log::Error($e->getMessage());
            throw new Exception($e->getMessage(), self::PRIVATE_THROW_ERR_CODE);
        }
    }
}
