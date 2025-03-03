<?php

namespace App\Console\Commands\Goto;

use App\Enums\BatchExecuteStatusEnum;
use App\Mail\InspectionDataMail;
use App\Models\Goto\Gfh1207\DepositorNumberModel;
use App\Models\Master\Base\ShopGfhModel;
use App\Models\Order\Base\DeliHdrModel;
use App\Models\Order\Gfh1207\DeliveryDetailSkuModel;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Common\Gfh1207\CheckBatchParameter;
use App\Modules\Order\Gfh1207\GetCsvExportFilePath;
use App\Modules\Order\Gfh1207\GetInspectionData;
use App\Services\TenantDatabaseManager;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class InspectionDataSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:InspectionDataSend {t_execute_batch_instruction_id : バッチ実行指示ID} {json :  JSON化した引数}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '出荷検品データCSVファイルを作成し、FESTAへメール送信する。';

    // バッチ名
    protected $batchName = '出荷検品データ作成＆送信';

    // テンプレートファイルパス
    protected $templateFilePath;

    // テンプレートファイル名
    protected $templateFileName;

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    // 検品データ取得
    protected $getInspectionData;

    // パラメータチェック
    protected $checkBatchParameter;

    // S3 サーバーに保存するファイル パスを取得
    protected $getCsvExportFilePath;

    // エラーコード
    private const PRIVATE_THROW_ERR_CODE = -1;

    // 新規作成
    private const NEW_CREATE = 1;

    // 再作成
    private const RE_CREATE = 2;

    // 空のデータ数
    private const EMPTY_DATA_ROW_CNT = 0;

    // 作成区分の値
    private const STARTUP_TYPE_LIST = [1, 2];

    /**
     * Create a new command instance.
     */
    public function __construct(
        StartBatchExecuteInstructionInterface $startBatchExecute,
        EndBatchExecuteInstructionInterface $endBatchExecute,
        CheckBatchParameter $checkBatchParameter,
        GetCsvExportFilePath $getCsvExportFilePath,
        GetInspectionData $getInspectionData
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->checkBatchParameter = $checkBatchParameter;
        $this->getCsvExportFilePath = $getCsvExportFilePath;
        $this->getInspectionData = $getInspectionData;
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
            /**
             * [共通処理] 開始処理
             * バッチ実行指示テーブル（t_execute_batch_instruction ）から該当バッチの取得と開始処理
             * t_execute_batch_instruction_id より該当バッチを取得し以下の情報を書き込む
             * - バッチ開始時刻
             */
            $batchExecute = $this->startBatchExecute->execute($this->argument('t_execute_batch_instruction_id'));
            $batchType = $batchExecute->execute_batch_type;
            $accountCode = $batchExecute->account_cd;     // for account cd

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
            // パラメータチェック
            $paramKey = [
                'type',
                'process_date',
                'date'
            ];

            $checkResult = $this->checkBatchParameter->execute($this->argument('json'), $paramKey);  // バッチJSONパラメータをチェックする

            if (!$checkResult) {
                // [パラメータが不正です。] メッセージを'execute_result'にセットする
                throw new \Exception(__('messages.error.invalid_parameter'), self::PRIVATE_THROW_ERR_CODE);
            }

            $searchCondition = (json_decode($this->argument('json'), true))['search_info'];

            // 'type', 'process_date', 'date' を取得
            $type = $searchCondition['type'];
            $processDate = $searchCondition['process_date'];
            $date = $searchCondition['date'];

            // 無効な 'type' をチェックする
            if($type == null || !in_array($type, self::STARTUP_TYPE_LIST)) {
                // 'パラメータが不正です。'
                throw new Exception(__('messages.error.invalid_parameter'), self::PRIVATE_THROW_ERR_CODE);
            }

            // 'type' に基づいて必須パラメータをチェック
            if ($type == self::NEW_CREATE && empty($processDate)) {
                // 新規作成の場合、処理日が必須
                throw new Exception(__('messages.error.missing_process_date'), self::PRIVATE_THROW_ERR_CODE);
            }

            if ($type == self::RE_CREATE && empty($date)) {
                // 再作成の場合、処理日時が必須
                throw new Exception(__('messages.error.missing_date'), self::PRIVATE_THROW_ERR_CODE);
            }

            // 「処理日」が Y/m/d 形式でない
            if ($processDate != null) {
                $processDate = DateTime::createFromFormat('Y/m/d', $processDate);
                if ($processDate == false) {
                    // 「処理日」日付フォーマットが無効です。
                    throw new Exception(__('messages.error.invalid_date_format', ['date' => '処理日']), self::PRIVATE_THROW_ERR_CODE);
                }
            }

            // 「処理日時」が Y/m/d H:i:s 形式でない
            if ($date != null) {
                $date = DateTime::createFromFormat('Y/m/d H:i:s', $date);
                if ($date == false) {
                    // 「処理日時」日付フォーマットが無効です。
                    throw new Exception(__('messages.error.invalid_date_format', ['date' => '処理日時']), self::PRIVATE_THROW_ERR_CODE);
                }
            }

            // 検品データ取得
            $searchResult = $this->getData($searchCondition);

            $depositorNumber = $this->getDepositorNumber($searchCondition);

            $csvDate = $type == self::NEW_CREATE ? $processDate->format('Ymd') : ($date ? $date->format('Ymd') : '');

            $fileName = $csvDate . '_' . $depositorNumber;

            // CSVファイルのパスを取得する
            $savePath =  $this->getCsvExportFilePath->execute($accountCode, $batchType, $fileName);

            // CSVファイル出力
            $getData = $this->getInspectionData->execute($searchResult, $depositorNumber);

            $recordCount = $getData['recordCount'];

            $csvData = $getData['csvData'];

            // 抽出結果がない場合、[出力対象のデータがありませんでした。]メッセージを'execute_result'にセットする
            if ($recordCount === self::EMPTY_DATA_ROW_CNT) {
                if ($type == self::NEW_CREATE) {
                    $currentTimestamp = Carbon::now()->format('Y-m-d H:i:s');
                    // 寄託者管理番号を設定する処理
                    $this->createDepositorNumber($processDate, $depositorNumber, $currentTimestamp);
                }
                $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                    'execute_result' =>  __('messages.error.data_not_found2', ['datatype' => '出力']),
                    'execute_status' => BatchExecuteStatusEnum::SUCCESS->value
                ]);

                DB::commit();
                return;
            }

            // save file on s3
            $fileuploaded = Storage::disk(config('filesystems.default', 'local'))->put($savePath, $csvData);

            //ファイルがAWS S3にアップロードされていない場合
            if (!$fileuploaded) {
                //AWS S3へのファイルのアップロードに失敗しました。
                throw new Exception(__('messages.error.upload_s3_failed'), self::PRIVATE_THROW_ERR_CODE);
            }

            $filePath = $this->downloadFileFromS3($savePath);

            // メールアドレス取得
            $mailData = $this->getFromToMailAddress();

            if ($mailData) {
                $emailStr = $mailData['to_mail']; // get recipient email address
                $fromEmail = $mailData['from_mail']; // get sender email address
                // Validate email are valid or not and transform string to array                
                $toEmail = explode(",", $emailStr);

                try {
                    // check toEmail address
                    foreach ($toEmail as $email) {
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            throw new Exception(__('messages.error.process_failed', ['process' => 'メール送信処理']));
                        }
                    }
                    // check fromEmail address
                    if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception(__('messages.error.process_failed', ['process' => 'メール送信処理']));
                    }

                    // check mail server configuration
                    $mailerConfig = config('mail.mailers.smtp');
                    if (empty($mailerConfig['host']) || empty($mailerConfig['port']) || empty($mailerConfig['username']) || empty($mailerConfig['password'])) {
                        throw new Exception(__('messages.error.process_failed', ['process' => 'メール送信処理']));
                    }

                    // メール送信
                    Mail::to($toEmail)->send(new InspectionDataMail($filePath, $fileName, $fromEmail));

                    // ファイルを削除
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                } catch (Exception $e) {
                    log::Error($e->getMessage());
                    throw new Exception($e->getMessage(), self::PRIVATE_THROW_ERR_CODE);
                }
            } else {
                Log::error(__('messages.error.batch_error.data_not_found3', ['data' => 'メールアドレス']));
            }

            if ($type == self::NEW_CREATE) {
                $this->processNewCreate($processDate, $depositorNumber, $searchResult);
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
                // 〇〇件出力しました。
                'execute_result' => __('messages.info.notice_output_count', ['count' => $recordCount]),
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
             */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => ($e->getCode() == self::PRIVATE_THROW_ERR_CODE) ? $e->getMessage() : BatchExecuteStatusEnum::FAILURE->label(),
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value
            ]);
        }
    }

    /**
     * Get from/to mail addresses
     *
     * @return array
     */
    private function getFromToMailAddress()
    {
        // メールアドレス取得
        $query = ShopGfhModel::query()
            ->select('mail_address_festa_inspection as to_mail', 'mail_address_from as from_mail')
            ->orderBy('m_shop_gfh_id', 'desc')
            ->first();

        return $query;
    }

    /**
     * getData
     *
     * @param  array $searchCondition
     * @return array data
     */
    private function getData($searchCondition)
    {
        $type = $searchCondition['type'];
        $date = $searchCondition['date'];

        $query = DeliveryDetailSkuModel::select(
            't_delivery_dtl_sku_id',
            'item_cd',
            'item_id',
            't_delivery_hdr_id',
            'order_sell_vol',
            't_delivery_dtl_id'
        )
            ->with([
                'deliHdr' => function ($query) use ($type, $date) {
                    $query->select('t_deli_hdr_id', 'destination_id', 'deli_inspection_date', 'm_cust_id', 'gp3_type');
                },
                'deliHdr.cust' => function ($query) {
                    $query->select('m_cust_id', 'name_kanji', 'm_cust_runk_id');
                },
                'amiSku' => function ($query) {
                    $query->select('m_ami_sku_id', 'remarks1');
                },
                'deliveryDtl' => function ($query) {
                    $query->select('t_delivery_dtl_id', 'sell_id');
                },
                'deliveryDtl.amiPage' => function ($query) {
                    $query->select('m_ami_page_id', 'page_cd');
                }
            ])
            // 作成区分が新規作成の場合
            ->when($type == self::NEW_CREATE, function ($query) {
                $from = Carbon::now()->subDays(7)->toDateString();
                $to = Carbon::now()->toDateString();
                $query->whereHas('deliHdr', fn ($q) => $q->whereBetween('deli_inspection_date', [$from, $to])
                ->whereNull('gp3_type'));
            })
            // 作成区分が再作成の場合
            ->when($type == self::RE_CREATE, function ($query) use ($date) {
                $formattedDate = Carbon::createFromFormat('Y/m/d H:i:s', $date)->format('Y-m-d H:i:s');
                $query->whereHas('deliHdr', fn ($q) => $q->where('gp3_type', $formattedDate));
            })
            ->get()
            ->groupBy([
                fn ($delivery) => $delivery->item_cd,
                fn ($delivery) => optional(optional($delivery->deliHdr)->cust)->m_cust_runk_id,
                fn ($delivery) => optional($delivery->deliHdr)->destination_id,
                fn ($delivery) => optional($delivery->deliHdr)->deli_inspection_date
            ]);

        $result = [];

        foreach ($query as $item_cd => $itemData) {
            foreach ($itemData as $m_cust_runk_id => $level1) {
                foreach ($level1 as $destination_id => $level2) {
                    foreach ($level2 as $deli_inspection_date => $orders) {
                        $t_deli_hdr_ids = $orders->pluck('deliHdr.t_deli_hdr_id')->filter()->unique()->implode(',');
                        $remarks1 = $orders->pluck('amiSku.*.remarks1')->flatten()->unique()->implode('/');
                        $page_cd = $orders->pluck('deliveryDtl.amiPage')->flatten()->pluck('page_cd')->filter()->unique()->implode('/');

                        // set result item
                        $resultItem = [
                            'total_order_sell_vol' => $orders->sum('order_sell_vol'),
                            'item_cd' => $item_cd,
                            'm_cust_runk_id' => $m_cust_runk_id,
                            'destination_id' => $destination_id,
                            'deli_inspection_date' => $deli_inspection_date,
                            't_deli_hdr_id' => $t_deli_hdr_ids,
                            'remarks1' => $remarks1,
                            'page_cd' => $page_cd
                        ];

                        // set result
                        $result[$item_cd][$m_cust_runk_id][$destination_id][$deli_inspection_date] = $resultItem;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * getDepositorNumber
     *
     * @param  array $searchCondition
     * @return array data
     */
    private function getDepositorNumber($searchCondition)
    {
        $type = $searchCondition['type'];
        $processDate = Carbon::parse($searchCondition['process_date'])->format('Y-m-d');
        $date = Carbon::parse($searchCondition['date'])->format('Y-m-d H:i:s');

        if ($type == self::NEW_CREATE) {
            // 新規作成の場合
            $query = DepositorNumberModel::where('process_date', $processDate)->max('deposit_number');

            $query = $query ? $query + 1 : 1;

            return $query;
        } elseif ($type == self::RE_CREATE) {
            // 再作成の場合
            $query = DepositorNumberModel::where('process_timestamp', $date)->value('deposit_number');

            if (!$query) {
                // レコードが0件の場合はエラーとする
                throw new Exception(__('messages.error.record_not_found'), self::PRIVATE_THROW_ERR_CODE);
            }

            // 寄託者管理番号を返す
            return $query;
        }
    }

    /**
     * downloadFileFromS3
     *
     * @param  string $filePath
     * @return string
     */
    private function downloadFileFromS3(string $filePath): string
    {
        // Stream file from S3
        $stream = Storage::disk(config('filesystems.default', 'local'))->readStream($filePath);

        if (!$stream) {
            // [出荷検品データ作成＆送信ファイルが見つかりません。（:filePath] message save to 'execute_result'
            throw new Exception(__('messages.error.file_not_found', ['file' => '出荷検品データ作成＆送信', 'path' => $filePath]), self::PRIVATE_THROW_ERR_CODE);
        }

        $localPath = sys_get_temp_dir() . '/' . basename($filePath);

        // Create a local file and write the stream to it
        $localFile = fopen($localPath, 'w+');

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
     * 新規作成の場合の処理
     *
     * @param  string $processDate
     * @param  string $depositorNumber
     * @param  array $searchResult
     * @return void
     */
    private function processNewCreate($processDate, $depositorNumber, $searchResult)
    {
        $currentTimestamp = Carbon::now()->format('Y-m-d H:i:s');
        // 寄託者管理番号を設定する処理
        $this->createDepositorNumber($processDate, $depositorNumber, $currentTimestamp);

        // 汎用区分3に現在のタイムスタンプを設定する処理
        $this->updateGp3Type($searchResult, $currentTimestamp);
    }

    /**
     * 寄託者管理番号を新規作成する
     */
    private function createDepositorNumber($processDate, $depositorNumber, $currentTimestamp)
    {
        DB::beginTransaction();
        try {
            $new = new DepositorNumberModel();
            $new->process_date = $processDate;
            $new->process_timestamp = $currentTimestamp;
            $new->deposit_number = $depositorNumber;
            $new->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 出荷基本のレコードを更新し、汎用区分3に現在のタイムスタンプを設定する
     */
    private function updateGp3Type($searchResult, $currentTimestamp)
    {
        try {
            $t_deli_hdr_ids = [];

            foreach ($searchResult as $item_cd => $ranks) {
                foreach ($ranks as $rank_id => $destinations) {
                    foreach ($destinations as $destination_id => $dates) {
                        foreach ($dates as $date => $details) {
                            $t_deli_hdr_id = $details['t_deli_hdr_id'] ?? null;

                            if (!empty($t_deli_hdr_id)) {
                                // カンマ区切りの場合は複数のIDに分割
                                $ids = explode(',', $t_deli_hdr_id);

                                // 空でないIDをトリミングして収集する
                                foreach ($ids as $id) {
                                    $id = trim($id);
                                    if (!empty($id)) {
                                        $t_deli_hdr_ids[] = $id;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // 収集したIDから重複を削除する
            $t_deli_hdr_ids = array_unique($t_deli_hdr_ids);

            // データベーストランザクションを開始する
            DB::beginTransaction();
            try {
                // データベースレコードを更新する
                DeliHdrModel::whereIn('t_deli_hdr_id', $t_deli_hdr_ids)->update(['gp3_type' => $currentTimestamp]);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback(); // エラー時のロールバック
                Log::error("Error updating gp3_type: " . $e->getMessage());
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error("Processing error: " . $e->getMessage());
        }
    }
}
