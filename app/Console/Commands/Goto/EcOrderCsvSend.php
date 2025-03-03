<?php

namespace App\Console\Commands\Goto;

use App\Enums\BatchExecuteStatusEnum;
use App\Mail\EcOrderCsvSendMail;
use App\Models\Master\Base\ShopGfhModel;
use App\Models\Order\Gfh1207\DeliHdrModel;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Order\Gfh1207\GetCsvExportFilePath;
use App\Services\TenantDatabaseManager;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class EcOrderCsvSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:EcOrderCsvSend {t_execute_batch_instruction_id : バッチ実行指示ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '通販の売上と受注残をEXCEL出力し、メール送信する。';

    // バッチ名
    protected $batchName = '通信販売売上・受注残（CSV）';

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    // S3 サーバーに保存するファイル パスを取得
    protected $getCsvExportFilePath;

    //csv header
    protected $csvHeader = [
        '売上日',
        '商品コード',
        '商品名',
        '単価',
        '売上数',
        '売上消費税',
        '売上金額',
        '返品数',
        '返品消費税',
        '返品金額',
        '店舗集計グループ',
        '店舗コード',
    ];

    //throw error code constants
    private const PRIVATE_THROW_ERR_CODE = -1;

    /**
     * Create a new command instance.
     */
    public function __construct(
        StartBatchExecuteInstructionInterface $startBatchExecute,
        EndBatchExecuteInstructionInterface $endBatchExecute,
        GetCsvExportFilePath $getCsvExportFilePath,
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->getCsvExportFilePath = $getCsvExportFilePath;

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

            $today = Carbon::now();

            if ($today->day === 1) {
                //当日が1日の場合、先々月の1日から前日までを対象にする。
                $startDate = $today->subMonths(2)->startOfMonth();
            } else {
                //当日が1日以外の場合、先月の1日から前日までを対象にする。
                $startDate = $today->subMonths(1)->startOfMonth();
            }

            $endDate = Carbon::yesterday();

            $deliHdrData = DeliHdrModel::select('t_deli_hdr_id', 't_order_hdr_id', 'm_cust_id', 'deli_decision_date')
                ->whereBetween('deli_decision_date', [$startDate, $endDate])
                ->with([
                    'deliveryDetails:t_delivery_hdr_id,sell_cd,sell_name,tax_price,order_sell_price,order_sell_vol,order_return_vol',
                    'cust:m_cust_id,m_cust_runk_id',
                    'cust.custRunk:m_itemname_types_id,m_itemname_type_code',
                    'orderHdr:t_order_hdr_id,sales_store',
                    'orderHdr.salesStoreItemnameTypes:m_itemname_types_id,m_itemname_type_code'
                ])
                ->get();

            // to check excel data have or not condition
            if ($deliHdrData->isEmpty()) {
                // [出力対象のデータがありませんでした。] message save to 'execute_result'
                $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                    'execute_result' =>  __('messages.error.data_not_found2', ['datatype' => '出力']),
                    'execute_status' => BatchExecuteStatusEnum::SUCCESS->value
                ]);

                DB::commit();
                return;
            }

            $groupedData = $this->groupedData($deliHdrData->toArray());

            $restructuredDataArray = $this->restructuredDataArray($groupedData);

            $getData = $this->csvData($restructuredDataArray);

            // CSVファイルのパスを取得する
            $savePath =  $this->getCsvExportFilePath->execute($accountCode, $batchType, $batchExecutionId);

            // save file on s3
            $fileuploaded = Storage::disk(config('filesystems.default', 'local'))->put($savePath, $getData['csvData']);

            //ファイルがAWS S3にアップロードされていない場合
            if (!$fileuploaded) {
                //AWS S3へのファイルのアップロードに失敗しました。
                throw new Exception(__('messages.error.upload_s3_failed'), self::PRIVATE_THROW_ERR_CODE);
            }

            $filePath = $this->downloadFileFromS3($savePath);

            $fileName = Carbon::now()->format('Ymd') . 'Uriage';

            $this->mailSend($filePath, $fileName);

            /**
             * [共通処理] 終了処理
             * バッチ実行指示テーブルの該当バッチに以下の情報を書き込む
             * - バッチ終了時刻
             * - (実行状態)
             * - (実行結果)
             * - (格納ファイルパス)
             */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => __('messages.success.batch_success.success_rowcnt', ['rowcnt' => $getData['recordCount'], 'process' => '出力']), // 〇〇件取込しました。
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
     * grouped array data
     *
     * @param array $dataList
     *
     * @return array
     */
    private function groupedData($dataList)
    {
        $flattenedData = [];

        foreach ($dataList as $data) {

            $deliHdr = [
                'deli_decision_date' => $data['deli_decision_date'],
            ];

            $cust = $data['cust'] ?? [];

            $orderHdr = $data['order_hdr'] ?? [];

            foreach ($data['delivery_details'] as $deliveryDetail) {
                // Merge common details with individual delivery details
                $flattenedData[] = array_merge($deliveryDetail, $deliHdr, $cust, $orderHdr);
            }
        }

        $groupedData = collect($flattenedData)
            ->groupBy([
                'deli_decision_date',
                'sell_cd',
                'm_cust_runk_id',
                'sales_store',
            ])->toArray();

        return  $groupedData;
    }

    /**
     * restructured Data Array
     *
     * @param array $dataList
     *
     * @return array
     */
    private function restructuredDataArray($dataList)
    {
        $result = [];

        foreach ($dataList as $deliDecisionDate => $sellCdGroup) {
            foreach ($sellCdGroup as $sellCd => $custRunkIdGroups) {
                foreach ($custRunkIdGroups as $custRunkId => $salesStoreGroup) {
                    foreach ($salesStoreGroup as $salesStore => $items) {

                        // Calculate the sum of 'order_sell_vol' and 'order_return_vol'
                        $totalOrderSellVol = collect($items)->sum('order_sell_vol');
                        $totalOrderReturnVol = collect($items)->sum('order_return_vol');

                        foreach ($items as $item) {
                            $result[] = [
                                Carbon::parse($deliDecisionDate)->format('Ymd'), //売上日(YYYYMMDD形式)
                                $sellCd, //商品コード
                                $item['sell_name'], //商品名
                                $item['order_sell_price'], //単価
                                $totalOrderSellVol, //売上数
                                $item['tax_price'] * $totalOrderSellVol, //売上消費税
                                $item['order_sell_price'] * $totalOrderSellVol, //売上金額
                                $totalOrderReturnVol ?: 0, // 返品数
                                $totalOrderReturnVol ? $item['tax_price'] * $totalOrderReturnVol : 0, // 返品消費税
                                $totalOrderReturnVol ? $item['order_sell_price'] * $totalOrderReturnVol : 0, // 返品金額
                                $item['cust_runk'] ? $item['cust_runk']['m_itemname_type_code'] : '', // 店舗集計グループ
                                $item['sales_store_itemname_types'] ? $item['sales_store_itemname_types']['m_itemname_type_code'] : '', //店舗コード
                            ];
                        }
                    }
                }
            }
        }

        //出荷確定日を昇順で出力する。
        return collect($result)->sortBy(0)->values()->toArray();
    }

    /**
     * change array to csv data
     *
     * @param array $dataList
     *
     * @return string
     */

    private function csvData($dataList)
    {
        // Open a memory stream (in-memory file)
        $memoryStream = fopen('php://temp', 'r+');

        // Write the header row
        fputcsv($memoryStream, $this->csvHeader);

        // Write each data row
        foreach ($dataList as $row) {
            fputcsv($memoryStream, $row);
        }
        // Rewind the stream to read its contents
        rewind($memoryStream);
        $csvData = stream_get_contents($memoryStream);

        // Close the memory stream
        fclose($memoryStream);

        return [
            'recordCount' => count($dataList),
            'csvData' => $csvData,
        ];
    }

    /**
     * downloadFileFromS3
     *
     * @param  string $filePath
     * @return string
     */
    private function downloadFileFromS3($filePath)
    {
        // Stream file from S3
        $stream = Storage::disk(config('filesystems.default', 'local'))->readStream($filePath);

        if (!$stream) {
            // [通信販売売上・受注残（CSV）ファイルが見つかりません。（:filePath] message save to 'execute_result'
            throw new Exception(__('messages.error.file_not_found', ['file' => '通信販売売上・受注残（CSV）', 'path' => $filePath]), self::PRIVATE_THROW_ERR_CODE);
        }

        // Create a local file and write the stream to it
        $localPath = sys_get_temp_dir() . '/' . basename($filePath);
        $localFile = fopen($localPath, 'w+');

        // if the file could not be created or opened
        if ($localFile === false) {
            //ローカルファイルの作成に失敗しました。
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
     * mailSend
     *
     * @param  string $filePath
     * @param  string $fileName
     * @return string
     */
    private function mailSend($filePath, $fileName)
    {

        // メールアドレス取得
        $mailData = ShopGfhModel::query()
            ->select('mail_address_festa', 'mail_address_from')
            ->orderBy('m_shop_gfh_id', 'desc')
            ->first();

        // when there is not m_shop_gfh record
        if (!$mailData) {
            //メールアドレス情報が取得できませんでした。
            throw new Exception(__('messages.error.batch_error.data_not_found3', ['data' => 'メールアドレス']), self::PRIVATE_THROW_ERR_CODE);
        }

        $toEmails =  explode(',', $mailData['mail_address_festa']);
        $fromEmail = $mailData['mail_address_from'];

        // check toEmails address
        foreach ($toEmails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                //メール送信処理に失敗しました。
                throw new Exception(__('messages.error.process_failed', ['process' => 'メール送信処理']), self::PRIVATE_THROW_ERR_CODE);
            }
        }

        // check fromEmail address
        if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            //メール送信処理に失敗しました。
            throw new Exception(__('messages.error.process_failed', ['process' => 'メール送信処理']), self::PRIVATE_THROW_ERR_CODE);
        }

        // check mail server configuration
        $mailerConfig = config('mail.mailers.smtp');
        if (empty($mailerConfig['host']) || empty($mailerConfig['port']) || empty($mailerConfig['username']) || empty($mailerConfig['password'])) {
            //メール送信処理に失敗しました。
            throw new Exception(__('messages.error.process_failed', ['process' => 'メール送信処理']), self::PRIVATE_THROW_ERR_CODE);
        }

        try {
            // メール送信
            Mail::to((array) $toEmails)->send(new EcOrderCsvSendMail($filePath, $fileName, $fromEmail));

            return;
        } catch (Exception $e) {

            throw new Exception($e->getMessage(), self::PRIVATE_THROW_ERR_CODE);
        }
    }
}
