<?php

namespace App\Http\Controllers\Order;

use App\Modules\Order\Base\Enums\ShipNyukinExportTypeEnumInterface;
use App\Modules\Order\Base\Enums\ShipNyukinRunTypeEnumInterface;
use App\Modules\Order\Base\Enums\OrderCustomerImportTypeEnumInterface;
use App\Modules\Order\Base\Enums\OrderCustomerRunTypeEnumInterface;
use App\Modules\Order\Base\Enums\EcbeingExecuteTypeInterface;
use App\Modules\Master\Base\Enums\BatchListEnumInterface;

use App\Services\EsmSessionManager;
use App\Services\FileUploadManager;
use App\Modules\Common\Base\RegisterBatchExecuteInstructionInterface;
use Carbon\Carbon;

use Illuminate\Http\Request;

class EcBeingListController
{
    protected $esmSessionManager;
    // Enums
    protected $shipNyukinExportTypeEnum;
    protected $shipNyukinRunTypeEnum;
    protected $orderCustomerImportTypeEnum;
    protected $orderCustomerRunTypeEnum;
    protected $ecbeingExecuteType;
    protected $batchListEnum;

    protected $checked = '1';

    public function __construct(
        EsmSessionManager $esmSessionManager,
    ) {
        $this->esmSessionManager = $esmSessionManager;
        $this->shipNyukinExportTypeEnum = app(ShipNyukinExportTypeEnumInterface::class);
        $this->shipNyukinRunTypeEnum = app(ShipNyukinRunTypeEnumInterface::class);
        $this->orderCustomerImportTypeEnum = app(OrderCustomerImportTypeEnumInterface::class);
        $this->orderCustomerRunTypeEnum = app(OrderCustomerRunTypeEnumInterface::class);
        $this->ecbeingExecuteType = app(EcbeingExecuteTypeInterface::class);
        $this->batchListEnum = app(BatchListEnumInterface::class);
    }
      
    public function info(
        Request $request,
    ) {
        $searchRow = [
            'import_type' => $this->orderCustomerRunTypeEnum::EXECTUE_ALL->value,
            'export_type' => $this->shipNyukinRunTypeEnum::EXECTUE_ALL->value,
            'order_input' => $this->checked,
            'customer_input' => $this->checked,
            'ship_output' => $this->checked,
            'nyukin_output' => $this->checked,
            'inspection_date' => date('Y-m-d'),
        ];
        return account_view('order.base.ecbeing', [
            'searchRow' => $searchRow,
            'shipNyukinExportTypeEnum' => $this->shipNyukinExportTypeEnum,
            'shipNyukinRunTypeEnum' => $this->shipNyukinRunTypeEnum,
            'orderCustomerImportTypeEnum' => $this->orderCustomerImportTypeEnum,
            'orderCustomerRunTypeEnum' => $this->orderCustomerRunTypeEnum,
        ]);
    }
      
    public function postInfo(
        Request $request,
        RegisterBatchExecuteInstructionInterface $registerBatchExecute,
        FileUploadManager $fileUploadManager
    ) {
        $req = $request->all();

        $create_batch = [];
        try {
            if ($req['submit'] === 'import') {
                // 受注データ取込
                if (isset($req['order_input']) && $req['order_input'] == $this->checked) {
                    $params = [
                        'execute_batch_type' => $this->getBatchName('order_input', $req['import_type']),
                        'execute_conditions' => [
                            'search_info' => [
                                'type' => $this->ecbeingExecuteType::IMPORT->value,
                                'import_type' => $this->orderCustomerImportTypeEnum::IMPORT_ORDER_DATA->value,
                            ]
                        ],
                    ];

                    // import_type = 3 かつ order_input = 1 で order_input_file= null の場合は例外
                    if ($req['import_type'] == $this->orderCustomerRunTypeEnum::IMPORT->value) {
                        if (empty($req['order_input_file'])) {
                            throw new \Exception(__('messages.error.order_search.select_name',['name'=>'受注入力ファイル']));
                        }
                        // ファイルアップロード処理
                        $params = $this->fileUpload($fileUploadManager, $request->file('order_input_file'), $params);
                    }
                
                    $result = $registerBatchExecute->execute($params);
                    $create_batch[] = __('messages.info.create_completed',['data'=>"受注データ取込バッチ"]);
                }
                // 顧客データ取込
                if (isset($req['customer_input']) && $req['customer_input'] == $this->checked) {
                    $params = [
                        'execute_batch_type' => $this->getBatchName('customer_input', $req['import_type']),
                        'execute_conditions' => [
                            'search_info' => [
                                'type' => $this->ecbeingExecuteType::IMPORT->value,
                                'import_type' => $this->orderCustomerImportTypeEnum::IMPORT_CUSTOMER_DATA->value,
                            ]
                        ],
                    ];

                    // import_type = 3 かつ customer_input = 1 で customer_input_file= null の場合は例外
                    if ($req['import_type'] == $this->orderCustomerRunTypeEnum::IMPORT->value) {
                        if (empty($req['customer_input_file'])) {
                            throw new \Exception(__('messages.error.order_search.select_name',['name'=>'顧客入力ファイル']));
                        }
                        // ファイルアップロード処理
                        $params = $this->fileUpload($fileUploadManager, $request->file('customer_input_file'), $params);
                    }
                
                    $registerBatchExecute->execute($params);
                    $create_batch[] = __('messages.info.create_completed',['data'=>"顧客データ取込バッチ"]);
                }
            } else if ($req['submit'] === 'export') {
                if (empty($req['inspection_date'])) {
                    throw new \Exception(__('messages.error.order_search.select_name',['name'=>'対象検品日']));
                }
                // 出荷確定データ出力
                if (isset($req['ship_output']) && $req['ship_output'] == $this->checked) {
                    $params = [
                        'execute_batch_type' => $this->getBatchName('ship_output', $req['export_type']),
                        'execute_conditions' => [
                            'search_info' => [
                                'type' => $this->ecbeingExecuteType::EXPORT->value,
                                'export_type' => $this->shipNyukinExportTypeEnum::SHIP_EXPORT->value,
                                'inspection_date' => $req['inspection_date'],
                            ]
                        ],
                    ];

                    // export_type = 3 かつ order_input = 1 で ship_output_file= null の場合は例外
                    if ($req['export_type'] == $this->shipNyukinRunTypeEnum::SEND->value) {
                        if (empty($req['ship_output_file'])) {
                            throw new \Exception(__('messages.error.order_search.select_name',['name'=>'出荷確定データ出力ファイル']));
                        }
                        // ファイルアップロード処理
                        $params = $this->fileUpload($fileUploadManager, $request->file('ship_output_file'), $params);
                    }
                
                    $registerBatchExecute->execute($params);
                    $create_batch[] = __('messages.info.create_completed',['data'=>"出荷確定データ出力バッチ"]);
                }
                // 入金・受注修正データ出力
                if (isset($req['nyukin_output']) && $req['nyukin_output'] == $this->checked) {
                    $params = [
                        'execute_batch_type' => $this->getBatchName('nyukin_output', $req['export_type']),
                        'execute_conditions' => [
                            'search_info' => [
                                'type' => $this->ecbeingExecuteType::EXPORT->value,
                                'export_type' => $this->shipNyukinExportTypeEnum::NYUKIN_EXPORT->value,
                                'inspection_date' => $req['inspection_date'],
                            ],
                        ],
                    ];

                    // export_type = 3 かつ order_input = 1 で nyukin_output_file= null の場合は例外
                    if ($req['export_type'] == $this->shipNyukinRunTypeEnum::SEND->value) {
                        if (empty($req['nyukin_output_file'])) {
                            throw new \Exception(__('messages.error.order_search.select_name',['name'=>'入金・受注修正データ出力ファイル']));
                        }
                        // ファイルアップロード処理
                        $params = $this->fileUpload($fileUploadManager, $request->file('nyukin_output_file'), $params);
                    }
                
                    $registerBatchExecute->execute($params);
                    $create_batch[] = __('messages.info.create_completed',['data'=>"入金・受注修正データ出力バッチ"]);
                }
            }
            if (count($create_batch) > 0) {
                session()->flash('messages.info', ['message' => $create_batch]);
                session()->flash('messages.error', []);
            } else {
                throw new \Exception(__('messages.error.order_search.select_name',['name'=>'バッチ対象']));
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            session()->flash('messages.error', ['message' => $message]);
            session()->flash('messages.info', []);
        }

        return account_view('order.base.ecbeing', [
            'searchRow' => $req,
            'shipNyukinExportTypeEnum' => $this->shipNyukinExportTypeEnum,
            'shipNyukinRunTypeEnum' => $this->shipNyukinRunTypeEnum,
            'orderCustomerImportTypeEnum' => $this->orderCustomerImportTypeEnum,
            'orderCustomerRunTypeEnum' => $this->orderCustomerRunTypeEnum,
        ]);
    }

    // 処理タイプにより実行するバッチを取得
    protected function getBatchName($type, $value) {
        $batchName = '';
        if ($type === 'order_input') {
            if ($value == $this->orderCustomerRunTypeEnum::EXECTUE_ALL->value) {
                $batchName = $this->batchListEnum::EXPCSV_ECBEING_ORDER->value;
            } else if ($value == $this->orderCustomerRunTypeEnum::RECEIVE->value) {
                $batchName = $this->batchListEnum::EXPCSV_ECBEING_ORDER->value;
            } else if ($value == $this->orderCustomerRunTypeEnum::IMPORT->value) {
                $batchName = $this->batchListEnum::IMPCSV_ECBEING_ORDER->value;
            }
        } else if ($type === 'customer_input') {
            if ($value == $this->orderCustomerRunTypeEnum::EXECTUE_ALL->value) {
                $batchName = $this->batchListEnum::EXPCSV_ECBEING_CUST->value;
            } else if ($value == $this->orderCustomerRunTypeEnum::RECEIVE->value) {
                $batchName = $this->batchListEnum::EXPCSV_ECBEING_CUST->value;
            } else if ($value == $this->orderCustomerRunTypeEnum::IMPORT->value) {
                $batchName = $this->batchListEnum::IMPCSV_ECBEING_CUST->value;
            }
        } else if ($type === 'ship_output') {
            if ($value == $this->shipNyukinRunTypeEnum::EXECTUE_ALL->value) {
                $batchName = $this->batchListEnum::EXPCSV_ECBEING_SHIP->value;
            } else if ($value == $this->shipNyukinRunTypeEnum::CREATE->value) {
                $batchName = $this->batchListEnum::EXPCSV_ECBEING_SHIP->value;
            } else if ($value == $this->shipNyukinRunTypeEnum::SEND->value) {
                $batchName = $this->batchListEnum::SEND_ECBEING_SHIP->value;
            }
        } else if ($type === 'nyukin_output') {
            if ($value == $this->shipNyukinRunTypeEnum::EXECTUE_ALL->value) {
                $batchName = $this->batchListEnum::EXPCSV_ECBEING_NYUKIN->value;
            } else if ($value == $this->shipNyukinRunTypeEnum::CREATE->value) {
                $batchName = $this->batchListEnum::EXPCSV_ECBEING_NYUKIN->value;
            } else if ($value == $this->shipNyukinRunTypeEnum::SEND->value) {
                $batchName = $this->batchListEnum::SEND_ECBEING_NYUKIN->value;
            }
        }
        return $batchName;
    }

    // ファイルアップロード処理
    protected function fileUpload($manager, $file, $params) {
        $uploadSavePath = 'tsv/order';
        $nowTime = new Carbon();
        $originalFileName = $file->getClientOriginalName();
        $uploadFileName = $nowTime->format('Ymdhis'). '_'. $originalFileName;
        $manager->upload($file, $uploadSavePath, $uploadFileName);

        $upload_file_param = '';
        if (isset($params['execute_batch_type']) && ($params['execute_batch_type'] == $this->batchListEnum::IMPCSV_ECBEING_ORDER->value)) {
            $upload_file_param = 'order_import_file';
        } else if (isset($params['execute_batch_type']) && ($params['execute_batch_type'] == $this->batchListEnum::IMPCSV_ECBEING_CUST->value)) {
            $upload_file_param = 'customer_import_file';
        }

        $params['execute_conditions']['search_info'][$upload_file_param] = $this->esmSessionManager->getAccountCode() . '/' . $uploadSavePath . '/' . $uploadFileName;
        $params['execute_conditions']['search_info']['original_file_name'] = $uploadFileName;
        return $params;
    }
}
