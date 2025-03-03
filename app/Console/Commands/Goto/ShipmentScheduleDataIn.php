<?php
namespace App\Console\Commands\Goto;

use App\Console\Commands\Common\FileImportCommon;

use App\Enums\BatchExecuteStatusEnum;
use App\Enums\DeliDecisionTypeEnum;
use App\Enums\ProgressTypeEnum;
use App\Enums\ShipmentStatusEnum;

use App\Models\Order\Base\CardboardLogModel;
use App\Models\Order\Gfh1207\DeliHdrModel;
use App\Models\Order\Gfh1207\DeliveryModel;
use App\Models\Order\Gfh1207\ShippingLabelModel;
use App\Models\Order\Gfh1207\OrderHdrModel;
use App\Models\Order\Gfh1207\OrderDtlModel;

use App\Modules\Master\Gfh1207\Enums\BatchListEnum;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;
use DateTime;
use Exception;

/**
 * 出荷検品データ取込
 */
// class ShipmentScheduleDataIn extends Command
class ShipmentScheduleDataIn extends FileImportCommon
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ShipmentScheduleDataIn {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '出荷状況データを取込、出荷データを更新後に取り込んだファイルを退避する。';

    // 取込元に関する情報
    private const IMPORT_DISK_NAME = "gfh_mount";
    private const IMPORT_DIR = "import";
    private const IMPORT_FLG_EXT = "flg";
    // バックアップ先に関する情報
    private const BACKUP_DISK_NAME = "esm_mount";
    private const BACKUP_DIR = "import_back";
    // 取扱ファイルの名前
    private const FILENAME_SHIP_RESULT = "ship_result.csv";
    private const FILENAME_SHIP_RESULT_SUB = "ship_result_sub.csv";

    // 出荷状況データのファイルフォーマット
    private const RULE_RESULT = [
        [
            'file_column_idx' => 0, 
            'file_column_name' => '受注ID', 
            'db_column_name' => 't_order_hdr_id', 
            'rule' => ['required' => true, 'byteMaxLength' => 20, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 1, 
            'file_column_name' => '受注配送先ID', 
            'db_column_name' => 't_order_destination_id', 
            'rule' => ['required' => true, 'byteMaxLength' => 20, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 2, 
            'file_column_name' => '出荷ID', 
            'db_column_name' => 't_deli_hdr_id',
            'rule' => ['required' => true, 'byteMaxLength' => 11, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 3, 
            'file_column_name' => 'ステータス', 
            'db_column_name' => 'status',
            'rule' => ['required' => true, 'byteMaxLength' => 4, 'in_data' => [ '1', '2', '3', '4', '5' ], 'numeric' => true] 
        ],
        [
            'file_column_idx' => 4, 
            'file_column_name' => 'ピッキングリスト出力日', 
            'db_column_name' => 'picking_date',
            'rule' => ['required' => false, 'byteMaxLength' => 10, 'date' => ['format' => 'Y/m/d', 'new_format' => 'Y-m-d']  ]
        ],
        [
            'file_column_idx' => 5, 
            'file_column_name' => '送り状発行日', 
            'db_column_name' => 'invoice_date',
            'rule' => ['required' => false, 'byteMaxLength' => 10, 'date' => ['format' => 'Y/m/d', 'new_format' => 'Y-m-d']  ]
        ],
        [
            'file_column_idx' => 6, 
            'file_column_name' => '検品日', 
            'db_column_name' => 'inspection_date',
            'rule' => ['required' => false, 'byteMaxLength' => 10, 'date' => ['format' => 'Y/m/d', 'new_format' => 'Y-m-d']  ]
        ],
        [
            'file_column_idx' => 7, 
            'file_column_name' => '出荷完了日', 
            'db_column_name' => 'decision_date',
            'rule' => ['required' => false, 'byteMaxLength' => 10, 'date' => ['format' => 'Y/m/d', 'new_format' => 'Y-m-d']  ]
        ],
        [
            'file_column_idx' => 8, 
            'file_column_name' => '個口数', 
            'db_column_name' => 'package_vol',
            'rule' => ['required' => false, 'byteMaxLength' => 4, 'regex' => '/^[0-9]+$/', 'numeric' => true] 
        ],
    ];
    // 出荷状況データ（個口情報）のファイルフォーマット
    private const RULE_RESULT_SUB = [
        [
            'file_column_idx' => 0, 
            'file_column_name' => '受注ID', 
            'db_column_name' => 't_order_hdr_id', 
            'rule' => ['required' => true, 'byteMaxLength' => 20, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 1, 
            'file_column_name' => '受注配送先ID', 
            'db_column_name' => 't_order_destination_id', 
            'rule' => ['required' => true, 'byteMaxLength' => 20, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 2, 
            'file_column_name' => '出荷ID', 
            'db_column_name' => 't_deli_hdr_id',
            'rule' => ['required' => true, 'byteMaxLength' => 11, 'regex' => '/^[0-9]+$/'] 
        ],
        [
            'file_column_idx' => 3, 
            'file_column_name' => '送り状番号', 
            'db_column_name' => 'invoice_num',
            'rule' => ['required' => true, 'byteMaxLength' => 20] 
        ],
        [
            'file_column_idx' => 4, 
            'file_column_name' => '段ボール型番', 
            'db_column_name' => 'cardboard_type',
            'rule' => ['required' => true, 'byteMaxLength' => 50] 
        ],
        [
            'file_column_idx' => 5, 
            'file_column_name' => '温度帯', 
            'db_column_name' => 'temperature_zone',
            'rule' => ['required' => true, 'byteMaxLength' => 4, 'in_data' => [ '0', '1', '2' ], 'numeric' => true] 
        ],
    ];
    // ファイルエンコード(utf8以外の場合のみ設定)
    protected $import_file_encode = 'sjis';

    /**
     * 本処理
     */
    public function handle()
    {
        /**
         * 退避ディレクトリの最終タイムスタンプを前回取得日時として取込対象を特定する為、
         * 複数ディレクトリの取込が発生し、かつ途中でエラーが発生した場合は
         * 全てを巻き戻さないと取込こぼしが発生するため、全体をトランザクションで囲っている
         */

        // 取込対象ディレクトリのリスト：catch時にも参照する可能性がある為、最上位に定義しておく
        $dirList = [];

        try
        {
            /**
             * 初期処理
             */
            $this->initCommand( BatchListEnum::IMPCSV_SHIPMENT_SCHEDULE_DATA->value );
            // 生成されたバッチ実行指示IDを確保
            $this->t_execute_batch_instruction_id = $this->batchExecute->t_execute_batch_instruction_id;


            DB::beginTransaction();

            /**
             * 前回取得日時の設定
             * バックアップ( esm : gfh/import_back )の最終ディレクトリの日時を取得
             */
            $lastImport = 0;
            $dirs = Storage::disk(self::BACKUP_DISK_NAME)->directories(self::BACKUP_DIR);
            foreach( $dirs as $dir ){
                $lastModify = Storage::disk(self::BACKUP_DISK_NAME)->lastModified($dir);
                if( $lastModify > $lastImport ){
                    $lastImport = $lastModify;
                }
            }

            /**
             * インポート元ディレクトリから最終更新日時より後のflgファイルを取得
             */
            $files = Storage::disk(self::IMPORT_DISK_NAME)->files(self::IMPORT_DIR);
            foreach( $files as $file ){
                $fileInfo = pathinfo($file);
                // 拡張子チェック
                if( $fileInfo['extension'] != self::IMPORT_FLG_EXT ){
                    continue;
                }
                // 最終更新日時チェック
                $lastModify = Storage::disk(self::IMPORT_DISK_NAME)->lastModified( $file );
                if( $lastModify < $lastImport ){
                    // ワーニングに出しておく
                    $this->warnings[] = __('messages.error.shipment_schedule_import.flg_file_exists_imported', ['path' => $fileInfo['filename']] );
                    continue;
                }
                // 拡張子を除いたファイル名のみ確保
                $dirList[] = $fileInfo['filename'];
            }

            /**
             * インポート( esm:gfh/import )の処理
             * 処理済みのファイルは全て移動させてしまうので、flgファイルがある＝未処理
             */
            $resultCount = 0;
            $subCount = 0;
            foreach( $dirList as $dir ){
                // 出荷状況データの内容
                $contenstShipResult = null;
                // 出荷状況（個口）データの内容
                $contenstShipSub = null;
                // インポートディレクトリ
                $importPath = self::IMPORT_DIR . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR;
                // 個口データ必須フラグ
                $requireSub = false;

                /**
                 * 出荷状況データのパース
                 */
                $this->import_file_format = self::RULE_RESULT;
                $importResultPath = $importPath . self::FILENAME_SHIP_RESULT;
                $resultRowList = [];
                // 存在チェック
                if( Storage::disk(self::IMPORT_DISK_NAME)->exists( $importResultPath ) == false ){
                    $this->addErrorInfo(null, null, null, __('messages.error.shipment_schedule_import.result_file_not_found', ['filename' => $importResultPath] ));
                    continue;
                }
                // 取得とパース
                $contenstShipResult = Storage::disk(self::IMPORT_DISK_NAME)->get( $importResultPath );
                $rowDataList = self::getFileRows( $contenstShipResult );
                foreach( $rowDataList as $idx => $rowData ){
                    // 一行目はタイトル行
                    if( $idx == 0 ){
                        continue;
                    }
                    // 行番号
                    $rowNum = $idx + 1;
                    // 出荷状況データ用のフォーマット情報をセットしてバリデーションを通し、入力用データに整形する
                    $inputData = str_getcsv( $rowData );
                    $inputData = $this->createInputData( $idx + 1, $inputData, $rowData, ['fileName' => $importResultPath] );
                    $inputData = $this->convertInputData( $inputData );
                    if( empty( $inputData ) ){
                        continue;
                    }
                    // ログに出しやすいよう、元の行データも確保
                    $inputData['rowNum'] = $rowNum;
                    $inputData['source'] = $rowData;
                    // 主要IDをキーにリストに保存
                    $key = $this->getListKey( $inputData );

                    $resultRowList[ $key ] = $inputData;

                    // 発送済みデータの場合、個口ファイル必須フラグを立てる
                    if( $inputData['status'] == ShipmentStatusEnum::SHIPPED->value ){
                        $requireSub = true;
                    }
                }
                /**
                 * 出荷状況データ（個口情報）のパース
                 * 出荷状況データの取込時に必要になる（かもしれない）データなので先に取得と解析を済ませる
                 */
                $this->import_file_format = self::RULE_RESULT_SUB;
                $importSubPath = $importPath . self::FILENAME_SHIP_RESULT_SUB;
                $isSubOK = true; // 個口データが無いまたは正常にパース出来た場合はtrueのままになる
                $subRowList = [];
                // 存在する場合のみパースする
                if( Storage::disk(self::IMPORT_DISK_NAME)->exists( $importSubPath ) ){
                    $contenstShipSub = Storage::disk(self::IMPORT_DISK_NAME)->get( $importSubPath );
                    $rowDataList = self::getFileRows( $contenstShipSub );
                    foreach( $rowDataList as $idx => $rowData ){
                        // 一行目はタイトル行
                        if( $idx == 0 ){
                            continue;
                        }
                        // 行番号
                        $rowNum = $idx + 1;
                        // 出荷状況データ用のフォーマット情報をセットしてバリデーションを通し、入力用データに整形する
                        $inputData = str_getcsv( $rowData );
                        $inputData = $this->createInputData( $idx + 1, $inputData, $rowData, ['fileName' => $importSubPath]  );
                        $inputData = $this->convertInputData( $inputData );
                        if( empty( $inputData ) ){
                            $isSubOK = false;
                            continue;
                        }
                        // 主要IDをキーにリストに保存
                        $key = $this->getListKey( $inputData );
                        // 出荷状況データと紐づかない
                        if( !array_key_exists( $key, $resultRowList ) ){
                            $this->addErrorInfo(null, null, null, __('messages.error.shipment_schedule_import.sub_data_not_join', ['filename' => $importSubPath, 'rownum' => $rowNum] ));
                            $isSubOK = false;
                            continue;
                        }
                        // 個口データの初出のキーの場合、空のリストを作成
                        if( !array_key_exists( $key, $subRowList ) ){
                            $subRowList[ $key ] = [];
                        }
                        // ログに出しやすいよう、元の行データも確保
                        $inputData['rowNum'] = $rowNum;
                        $inputData['source'] = $rowData;
                        $subRowList[ $key ][] = $inputData;
                    }
                }
                else {
                    // 個口データが存在しないが、必須フラグが立っている場合
                    if( $requireSub ){
                        $this->addErrorInfo(null, null, null, __('messages.error.shipment_schedule_import.result_sub_file_not_found', ['filename' => $importSubPath] ));
                        $isSubOK = false;
                    }
                }
                if( $isSubOK == false ){
                    // 個口データのパースでエラーが発生しているので取込スキップ
                    continue;
                }

                /**
                 * データの更新・追加の実行
                 */
                $fileLineNo = 1; // ファイル行番号:項目行を考慮して1オリジン
                foreach( $resultRowList as $key => $resultRow ){
                    $fileLineNo++;

                    /**
                     * 出荷基本データ取得
                     */
                    $deliHdr = DeliHdrModel::query()->where('t_order_destination_id', '=', $resultRow['t_order_destination_id'])->first();
                    $deliveryHdr = DeliveryModel::query()->where('t_order_destination_id', '=', $resultRow['t_order_destination_id'])->first();
                    if( empty( $deliHdr ) ){
                        $this->addErrorInfo(null, null, null, __('messages.error.shipment_schedule_import.deli_hdr_not_found', ['filename' => $importResultPath, 'rownum' => $resultRow['rowNum']] ));
                        continue;
                    }

                    /**
                     * 出荷基本データの更新
                     * ステータスで更新項目が異なるが、共通設置があるので保存は最後に行う
                     */
                    $isUpdateError = false;
                    switch( $resultRow['status'] ) {

                        // 出荷指示済み(ピッキングリスト出力済み)
                        case ShipmentStatusEnum::INSTRUCTED->value:
                            if( empty( $resultRow['picking_date'] ) ){
                                $this->addErrorInfo(null, null, null, __('messages.error.shipment_schedule_import.empty_picking_date', ['filename' => $importResultPath, 'rownum' => $resultRow['rowNum']] ));
                                $isUpdateError = true;
                                break;
                            }
                            $deliHdr->total_pick_create_datetime = $resultRow['picking_date'];
                            if( !empty( $deliveryHdr ) ){
                                $deliveryHdr->total_pick_create_datetime = $resultRow['picking_date'];
                            }
                            break;

                        // 伝票発行済み, 検品済み
                        case ShipmentStatusEnum::SLIP_ISSUED->value:
                        case ShipmentStatusEnum::INSPECTED->value:
                            if( empty( $resultRow['inspection_date'] ) ){
                                $this->addErrorInfo(null, null, null, __('messages.error.shipment_schedule_import.empty_inspection_date', ['filename' => $importResultPath, 'rownum' => $resultRow['rowNum']] ));
                                $isUpdateError = true;
                                break;
                            }
                            $deliHdr->deli_inspection_date = $resultRow['inspection_date'];
                            if( !empty( $deliveryHdr ) ){
                                $deliveryHdr->deli_inspection_date = $resultRow['inspection_date'];
                            }
                            /**
                             * TODO 請求金額の減算モジュールを呼び出す
                             */
                            break;

                        // 発送済み
                        case ShipmentStatusEnum::SHIPPED->value:
                            // 出荷日
                            if( empty( $resultRow['decision_date'] ) ){
                                $this->addErrorInfo(null, null, null, __('messages.error.shipment_schedule_import.empty_decision_date', ['filename' => $importResultPath, 'rownum' => $resultRow['rowNum']] ));
                                $isUpdateError = true;
                                break;
                            }
                            // 個口数
                            if( empty( $resultRow['package_vol'] ) ){
                                $this->addErrorInfo(null, null, null, __('messages.error.shipment_schedule_import.empty_package_vol', ['filename' => $importResultPath, 'rownum' => $resultRow['rowNum']] ));
                                $isUpdateError = true;
                                break;
                            }
                            // 個口データ
                            if( !array_key_exists( $key, $subRowList ) ){
                                $this->addErrorInfo(null, null, null, __('messages.error.shipment_schedule_import.empty_sub_data', ['filename' => $importResultPath, 'rownum' => $resultRow['rowNum']] ));
                                $isUpdateError = true;
                                break;
                            }
                            // 出荷基本：出荷完了日、個口数、進捗区分（出荷済み）
                            $deliHdr->deli_decision_date = $resultRow['decision_date'];
                            $deliHdr->deli_package_vol = $resultRow['package_vol'];
                            $deliHdr->progress_type = ProgressTypeEnum::Shipped->value;
                            $deliHdr->save();
                            if( !empty( $deliveryHdr ) ){
                                $deliveryHdr->deli_decision_date = $resultRow['decision_date'];
                                $deliveryHdr->deli_package_vol = $resultRow['package_vol'];
                                $deliveryHdr->progress_type = ProgressTypeEnum::Shipped->value;
                                $deliveryHdr->save();
                            }
                            break;

                        // その他
                        default:
                            $this->addErrorInfo(null, null, null, __('messages.error.shipment_schedule_import.other_status', ['filename' => $importResultPath, 'rownum' => $resultRow['rowNum']] ));
                            $isUpdateError = true;
                            break;
                    }
                    // 出荷基本データ更新の過程でエラーが発生した場合、この後の処理はスルー
                    if( $isUpdateError ){
                        continue;
                    }

                    // 発送済みの場合は個口データの処理を実行
                    if( $resultRow['status'] == ShipmentStatusEnum::SHIPPED->value ){
                        // 受注基本データを取得
                        $orderHdr = OrderHdrModel::query()
                        ->where('t_order_hdr_id', '=', $deliHdr->t_order_hdr_id)
                        ->first();
                        if( empty( $orderHdr ) ){
                            $this->addErrorInfo(null, null, null, __('messages.error.shipment_schedule_import.order_hdr_not_found', ['filename' => $importResultPath, 'rownum' => $resultRow['rowNum']] ));
                            continue;
                        }

                        // 受注明細データを取得
                        $orderDtl = OrderDtlModel::query()
                        ->where('t_order_destination_id', '=', $deliHdr->t_order_destination_id)
                        ->where('order_destination_seq', '=', $deliHdr->order_destination_seq)
                        ->first();
                        if( empty( $orderDtl ) ){
                            $this->addErrorInfo(null, null, null, __('messages.error.shipment_schedule_import.order_dtl_not_found', ['filename' => $importResultPath, 'rownum' => $resultRow['rowNum']] ));
                            continue;
                        }

                        // 個口データを取得
                        $packageList = $subRowList[ $key ];
                        $cardboardList = [];
                        // 送り状実績データの登録
                        foreach( $packageList as $package ){
                            $shippingLabel = new ShippingLabelModel();
                            $shippingLabel->m_account_id = $deliHdr->m_account_id;
                            $shippingLabel->t_order_hdr_id = $deliHdr->t_order_hdr_id;
                            $shippingLabel->t_order_destination_id = $deliHdr->t_order_destination_id;
                            $shippingLabel->t_order_dtl_id = $orderDtl->t_order_dtl_id;
                            $shippingLabel->order_destination_seq = $deliHdr->order_destination_seq;
                            $shippingLabel->t_delivery_hdr_id = $deliHdr->t_deli_hdr_id;
                            $shippingLabel->shipping_label_number = $package['invoice_num'];
                            $shippingLabel->three_temperature_zone_type = $package['temperature_zone'];
                            $shippingLabel->delivery_date = $resultRow['decision_date'];
                            $shippingLabel->entry_operator_id = $this->batchExecute->m_operators_id;
                            $shippingLabel->save();
                            // 段ボール実績に備えて型番別に集計をする
                            if( !array_key_exists( $package['cardboard_type'], $cardboardList ) ){
                                $cardboardList[ $package['cardboard_type'] ] = [
                                    'type' => $package['cardboard_type'],
                                    'use_vol' => 0
                                ];
                            }
                            $cardboardList[ $package['cardboard_type'] ]['use_vol']++;
                            // 個口データ処理カウントはここで増やす
                            $subCount++;
                        }
                        // 段ボール出荷実績データの登録
                        foreach( $cardboardList as $cardboard ){
                            $cardboardLog = new CardboardLogModel();
                            $cardboardLog->m_account_id = $deliHdr->m_account_id;
                            $cardboardLog->t_delivery_hdr_id = $deliHdr->t_deli_hdr_id;
                            // $cardboardLog->deli_inspection_date = null;
                            $cardboardLog->cardboard_type = $cardboard['type'];
                            $cardboardLog->use_vol = $cardboard['use_vol'];
                            $cardboardLog->save();
                        }                        

                        // 受注データの取得
                        // 受注IDに紐づく出荷基本データの件数
                        $deliCount = DeliHdrModel::query()
                        ->where('t_order_hdr_id', '=', $deliHdr->t_order_hdr_id)
                        ->whereNull('cancel_operator_id')
                        ->count();
                        // 受注IDに紐づく出荷基本データ(出荷済み)の件数
                        $shippedCount = DeliHdrModel::query()
                        ->where('t_order_hdr_id', '=', $deliHdr->t_order_hdr_id)
                        ->where('progress_type', '=', ProgressTypeEnum::Shipped->value)
                        ->whereNull('cancel_operator_id')
                        ->count();

                        // 全件出荷済み
                        if( $deliCount == $shippedCount ){
                            $orderHdr->deli_decision_type = DeliDecisionTypeEnum::DECIDED->value; // 出荷確定区分:全部出荷
                            $orderHdr->deli_decision_datetime = Carbon::now(); // 出荷確定区分変更日時
                            $orderHdr->progress_type = ProgressTypeEnum::Shipped->value; // 出荷済み
                            $orderHdr->progress_update_operator_id = 0; // 進捗区分変更者：システム
                            $orderHdr->progress_update_datetime = Carbon::now(); // 進捗区分変更日時
                        }
                        else{
                            $orderHdr->deli_decision_type = DeliDecisionTypeEnum::PARTIALLY_DECIDED->value; // 出荷確定区分:一部出荷
                            $orderHdr->deli_decision_datetime = Carbon::now(); // 出荷確定区分変更日時
                        }
                        $orderHdr->update_operator_id = $this->batchExecute->m_operators_id;
                        $orderHdr->save();
                    }

                    // 汎用区分2にステータスをセットして更新
                    $deliHdr->gp2_type = $resultRow['status'];
                    $deliHdr->update_operator_id = $this->batchExecute->m_operators_id;
                    $deliHdr->save();
                    if( !empty( $deliveryHdr ) ){
                        $deliveryHdr->gp2_type = $resultRow['status'];
                        $deliveryHdr->update_operator_id = $this->batchExecute->m_operators_id;
                        $deliveryHdr->save();
                    }
                    $resultCount++;
                }
            }

            /**
             * インポートファイルをバックアップディレクトリへ追加
             */
            if( count( $this->errors ) == 0 ){
                $flgFileList = [];
                foreach( $dirList as $dir ){
                    // インポート元の情報を取得
                    $importPath = self::IMPORT_DIR . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR;
                    $importResultPath = $importPath . self::FILENAME_SHIP_RESULT;
                    $importSubPath = $importPath . self::FILENAME_SHIP_RESULT_SUB;
                    $contenstShipResult = Storage::disk(self::IMPORT_DISK_NAME)->get( $importResultPath );
                    $contenstShipSub = Storage::disk(self::IMPORT_DISK_NAME)->get( $importPath . self::FILENAME_SHIP_RESULT_SUB );

                    // バックアップディレクトリへ退避
                    $backupPath = self::BACKUP_DIR . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR;

                    Storage::disk(self::BACKUP_DISK_NAME)->makeDirectory( $backupPath );
                    if( !empty( $contenstShipResult ) ){
                        $backupResultPath = $backupPath . self::FILENAME_SHIP_RESULT;
                        Storage::disk(self::BACKUP_DISK_NAME)->put( $backupResultPath, $contenstShipResult );
                        if( !Storage::disk(self::BACKUP_DISK_NAME)->exists( $backupResultPath ) ){
                            $this->addErrorInfo(null, null, null, __('messages.error.shipment_schedule_import.backup_copy_error', ['path' => $backupResultPath] ));
                            $flgFileList = null;
                            break;
                        }
                    }
                    if( !empty( $contenstShipSub ) ){
                        $backupSubPath = $backupPath . self::FILENAME_SHIP_RESULT_SUB;
                        Storage::disk(self::BACKUP_DISK_NAME)->put( $backupSubPath, $contenstShipSub );
                        if( !Storage::disk(self::BACKUP_DISK_NAME)->exists( $backupSubPath ) ){
                            $this->addErrorInfo(null, null, null, __('messages.error.shipment_schedule_import.backup_copy_error', ['path' => $backupSubPath] ));
                            $flgFileList = null;
                            break;  
                        }
                    }

                    // フラグファイル名を削除候補としてリストに追加
                    $flgFileList[] = self::IMPORT_DIR . DIRECTORY_SEPARATOR . $dir . "." . self::IMPORT_FLG_EXT;
                }

                /**
                 * フラグファイルを一括削除
                 */
                if( !empty( $flgFileList ) ){
                    // 削除
                    Storage::disk(self::IMPORT_DISK_NAME)->delete( $flgFileList );
                    // 全てのファイルが削除されているか確認
                    foreach( $flgFileList as $flgFile ){
                        if( Storage::disk(self::IMPORT_DISK_NAME)->exists( $flgFile ) ){
                            $this->addErrorInfo(null, null, null, __('messages.error.shipment_schedule_import.flg_file_delete_error', ['path' => $flgFile] ));
                        }
                    }
                }
            }

            // 全件完了してエラーがある場合、throwしてエラー処理する
            if( count( $this->errors ) > 0 ){
                throw new Exception(__('messages.error.impport_batch.error_import_file'));
            }
            /**
             * 終了処理
             */
            $resultMessage = __('messages.info.shipment_schedule_result_nodata');
            if( !empty( $dirList ) && count( $dirList ) > 0 ){
                $resultMessage = __('messages.info.shipment_schedule_result', [
                    'dirlist' => implode(',', $dirList), 
                    'resultcount' => $resultCount, 
                    'subcount' => $subCount
                ]);
            }

            $this->outputResultFile( $resultMessage );
            $this->finalizeCommand(
                 $resultMessage,
                 BatchExecuteStatusEnum::SUCCESS->value,
                 $this->result_file_path,
                 null
             );

            DB::commit();
        }
        catch (Exception $e)
        {
            /**
             * 終了処理
             */
            DB::rollBack();

            $errorMessage = $e->getMessage();
            try{
                // 中途半端な処理でフラグファイルが削除されている可能性があるので、その場合は復元する
                foreach( $dirList as $dir ){
                    $flgFile = self::IMPORT_DIR . DIRECTORY_SEPARATOR . $dir . "." . self::IMPORT_FLG_EXT;
                    if( !Storage::disk(self::IMPORT_DISK_NAME)->exists( $flgFile ) ){
                        Storage::disk(self::IMPORT_DISK_NAME)->put( $flgFile, '' );
                    }
                }
                $this->outputErrorFile( $e->getMessage() );
            }
            catch( Exception $e2 )
            {
                $errorMessage .= ", " . $e2->getMessage();
            }

            \Log::error( $errorMessage );
            \Log::error( $e->getTraceAsString() );
            $this->finalizeCommand(
                $errorMessage,
                BatchExecuteStatusEnum::FAILURE->value,
                null,
                $this->error_file_path
            );
        }
    }

    /**
     * エラーファイル出力
     * 出力ファイルが複数となり、エラー形式が異なるためオーバーライドしている
     */
    protected function outputErrorFile( $message ) {
        // 出力先ディレクトリの作成
        $dir = $this->batchExecute->account_cd . $this::OUTPUT_TEXT_BASE_PATH . $this->batch_id;
        Storage::disk( config('filesystems.default', 'local') )->makeDirectory($dir);

        // 出力情報の基本部分
        $contents = ( new DateTime() )->format('Y-m-d H:i:s') . PHP_EOL;
        $contents .= "t_execute_batch_instruction_id:" . $this->t_execute_batch_instruction_id . PHP_EOL;
        $contents .= $message . PHP_EOL;

        /**
         * 出力内容詳細の生成
         */
        foreach( $this->errors as $error ){
            // 発生エラー情報
            foreach( $error['messages'] as $text ){
                $contents .= $text . PHP_EOL;
            }
        }

        // ファイル出力
        $this->error_file_path = $dir . DIRECTORY_SEPARATOR . $this->t_execute_batch_instruction_id . $this->error_file_sufix . '.txt';
        $result = Storage::disk( config('filesystems.default', 'local') )->put($this->error_file_path, $contents);
        if( !$result ){
            $message = __('messages.error.file_ooutput_error', ['filename' => $this->error_file_path]);
            $this->error_file_path = null;
            throw new Exception($message);
        }
        return true;
    }

    /**
     * 二つのデータ内容のリストを繋ぐためのキーを作成して返す
     */
    private function getListKey( $data )
    {
        $key = $data['t_order_hdr_id'];
        $key .= '_' . $data['t_order_destination_id'];
        $key .= '_' . $data['t_deli_hdr_id'];
        return $key;
    }

    /**
     * エラーメッセージ取得：ファイル項目数エラー
     */
    protected function getErrorMessageColumnCount( $rowNum, $options = [] ){
        $fileName = $options['fileName'] ?? null;
        return __('messages.error.shipment_schedule_import.file_record_error.column_count', ['filename' => $fileName, 'rownum' => $rowNum]);
    }
    /**
     * エラーメッセージ取得：必須項目エラー
     */
    protected function getErrorMessageColumnRequired( $rowNum, $columnName, $options = [] ){
        $fileName = $options['fileName'] ?? null;
        return __('messages.error.shipment_schedule_import.file_record_error.column_empty', ['filename' => $fileName, 'rownum' => $rowNum, 'name' => $columnName] );
    }
    /**
     * エラーメッセージ取得：最大バイト数エラー
     */
    protected function getErrorMessageColumnByteLonger( $rowNum, $columnName, $length, $options = [] ){
        $fileName = $options['fileName'] ?? null;
        return __('messages.error.shipment_schedule_import.file_record_error.column_byte_longer', ['filename' => $fileName, 'rownum' => $rowNum, 'name' => $columnName, 'length' => $length] );
    }
    /**
     * エラーメッセージ取得：固定長エラー
     * 未使用であることが明確に分かるよう、敢えてコメントで残す
     */
    // protected function getErrorMessageColumnFixedLength( $rowNum, $columnName, $length, $options = [] ){
    //     return __('messages.error.impport_batch.file_record_error.column_fixed_length', ['rownum' => $rowNum, 'name' => $columnName, 'length' => $length] );
    // }
    /**
     * エラーメッセージ取得：日付フォーマット
     */
    protected function getErrorMessageColumnDateFormat( $rowNum, $columnName, $options = [] ){
        $fileName = $options['fileName'] ?? null;
        return __('messages.error.shipment_schedule_import.file_record_error.column_date_format', ['filename' => $fileName, 'rownum' => $rowNum, 'name' => $columnName] );
    }
    /**
     * エラーメッセージ取得：対象外エラー
     */
    protected function getErrorMessageColumnNotIn( $rowNum, $columnName, $options = [] ){
        $fileName = $options['fileName'] ?? null;
        return __('messages.error.shipment_schedule_import.file_record_error.column_value_not_correct', ['filename' => $fileName, 'rownum' => $rowNum, 'name' => $columnName] );
    }
    /**
     * エラーメッセージ取得：正規表現エラー
     */
    protected function getErrorMessageColumnRegex( $rowNum, $columnName, $options = [] ){
        $fileName = $options['fileName'] ?? null;
        return  __('messages.error.shipment_schedule_import.file_record_error.column_format', ['filename' => $fileName, 'rownum' => $rowNum, 'name' => $columnName] );
    }
}