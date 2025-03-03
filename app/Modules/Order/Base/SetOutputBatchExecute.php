<?php

namespace App\Modules\Order\Base;

use App\Enums\Esm2SubSys;
use App\Models\Order\Base\DeliHdrModel;
use App\Models\Order\Base\OrderDtlModel;
use App\Models\Order\Base\OrderDestinationModel;
use App\Modules\Common\Base\RegisterBatchExecuteInstructionInterface;
use App\Services\Esm2ApiManager;
use App\Services\EsmSessionManager;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SetOutputBatchExecute implements SetOutputBatchExecuteInterface
{
    /**
     * Output系バッチ実行指示に登録するキー項目
     */
    protected $outputSubmitColumn = [
        'change_progress'				=> 't_order_hdr_id',
        'send_template_mail'			=> 't_order_hdr_id',
        'payment'						=> 't_order_hdr_id',
        'send_recipt_mail'				=> 't_order_hdr_id',
        'new_send_recipt_mail'			=> 't_order_hdr_id',
        'reserve_stock'					=> 't_order_hdr_id',
        'change_delivery_type'			=> 't_order_hdr_id',
        'change_deli_hope_date'			=> 't_order_hdr_id',
        'change_deli_plan_date'			=> 't_order_hdr_id',
        'change_deli_decision_date'		=> 't_order_hdr_id',
        'change_operator_comment'		=> 't_order_hdr_id',
        'add_order_tag'					=> 't_order_hdr_id',
        'remove_order_tag'				=> 't_order_hdr_id',
        'output_payment_auth_csv'		=> 't_order_hdr_id',
        'output_pdf'					=> 't_deli_hdr_id',
        'output_delivery_csv'			=> 't_deli_hdr_id',
        'output_payment_delivery_result_csv' => 't_deli_hdr_id',
        'output_delivery_file' 			=> 't_deli_hdr_id',
        're_output_delivery_file'       => 't_deli_hdr_id',
        'output_order_file'				=> 't_order_hdr_id',
    ];

    /**
     * 受注更新バッチを使用する実行指示の一覧
     */
    protected $orderPartBatchName = [
        'change_delivery_type',
        'change_deli_hope_date',
        'change_deli_plan_date',
        'change_deli_decision_date',
        'change_operator_comment',
    ];

    /**
     * Output系バッチ実行指示に登録する際のバッチ実行種類
     */
    protected $outputBatchExecutingTypes = [
        'change_progress'				=> 'change_progress',
        'send_template_mail'			=> 'send_template_mail',
        'payment'						=> 'set_payment',
        'send_recipt_mail'				=> 'send_recipt_mail',
        'new_send_recipt_mail'			=> 'new_send_recipt_mail',
        'reserve_stock'					=> 'reserve_stock',
        'change_delivery_type'			=> 'change_delivery_type',
        'change_deli_hope_date'			=> 'change_deli_hope_date',
        'change_deli_plan_date'			=> 'change_deli_plan_date',
        'change_deli_decision_date'		=> 'change_deli_decision_date',
        'change_operator_comment'		=> 'change_operator_comment',
        'add_order_tag'					=> 'change_order_tag',
        'remove_order_tag'				=> 'change_order_tag',
        'output_payment_auth_csv'		=> 'expcsv_payment_auth_csv',
        'output_pdf'					=> 'exppdf_delivery',
        'output_delivery_csv'			=> 'expcsv_delivery',
        'output_payment_delivery_result_csv' => 'expcsv_payment_delivery_result',
        'output_delivery_file' 			=> '',
        're_output_delivery_file'       => '',
        'output_order_file'				=> '',
    ];

    /**
     * Output系バッチ実行指示に登録する際のその他参照項目
     */
    protected $outputBatchTargetControl = [
        'change_progress'						=> ['set_change_progress_type'],
        'send_template_mail'					=> ['send_email_templates_id'],
        'payment'								=> ['payment_paytype_id', 'set_cust_payment_date', 'set_account_payment_date'],
        'send_recipt_mail'						=> ['send_email_templates_id'],
        'new_send_recipt_mail'					=> ['send_email_templates_id'],
        'reserve_stock'							=> [],
        'change_delivery_type'					=> ['set_change_delivery_type'],
        'change_deli_hope_date'					=> ['set_deli_hope_date'],
        'change_deli_plan_date'					=> ['set_deli_plan_date'],
        'change_deli_decision_date'				=> ['set_deli_decision_date'],
        'change_operator_comment'				=> ['set_operator_comment', 'add_operator_comment_flg'],
        'add_order_tag'							=> ['set_add_tag_id'],
        'remove_order_tag'						=> ['set_remove_tag_id'],
        'output_payment_auth_csv'				=> ['output_payment_auth_csv_type'],
        'output_pdf'							=> ['output_queue_report'],
        'output_delivery_csv'					=> ['output_queue_delivery'],
        'output_payment_delivery_result_csv' 	=> ['output_payment_delivery_result_csv_type'],
        'output_delivery_file'					=> ['output_queue_delivery', 'output_queue_report'],
        're_output_delivery_file'               => ['output_queue_delivery', 'output_queue_report'],
        'output_order_file'						=> ['output_order_csv_type'],
    ];


    /**
     * 出力帳票の一覧
     */
    protected $outputPdfNames = [
        'exppdf_total_picking' => 'トータルピッキングリスト',
        'exppdf_detail_picking' => '個別ピッキングリスト',
        'exppdf_submission' => '納品書',
        'exppdf_direct_delivery_order_placement' => '直送発注書',
        'exppdf_receipt' => '領収書',
//		'exppdf_sagawa_yuumail' => '飛脚ゆうメール便ラベル',
    ];

    /**
     * 確認する指示タイムスタンプ
     */
    protected $outputCheckInstructTimestamp = [
        'exppdf_total_picking' => 'total_pick_instruct_datetime',
        'exppdf_detail_picking' => 'order_pick_instruct_datetime',
        'exppdf_submission' => 'deliveryslip_instruct_datetime',
        'exppdf_direct_delivery_order_placement' => 'purchase_order_instruct_datetime',
        'exppdf_receipt' => 'invoice_create_datetime',
    ];

    /**
     * ESM2.0 APIマネージャー
     */
    protected $esm2ApiManager;

    /**
     * ESMセッション管理クラス
     */
    protected $esmSessionManager;

    protected $registedBatchExecuteId;

    protected $registerBatchExecuteInstruction;
    public function __construct(
        Esm2ApiManager $esm2ApiManager,
        EsmSessionManager $esmSessionManager,
        RegisterBatchExecuteInstructionInterface $registerBatchExecuteInstruction,
    ) {
        $this->esm2ApiManager = $esm2ApiManager;
        $this->esmSessionManager = $esmSessionManager;
        $this->registerBatchExecuteInstruction = $registerBatchExecuteInstruction;
    }

    /**
     * 出力系バッチの処理
     */
    public function execute($request, $paginator, $submitType)
    {
        Log::info('SetOutputBatchExecute::execute');

        $req = $request->all();
        $search = app(SearchInterface::class);
        $searchInfo = [];

        $targetOrderDestination = [];
        $outputTarget = [];

        $result = '';

        $searchKey = $this->outputSubmitColumn[$submitType];

        $outputCheckColumnName = 'check_t_order_destination_id';
        $outputSearchColumnName = 'check_t_order_destination_id';

        // $paginator を dataRows に変換
        $dataRows = [];
        foreach($paginator as $row) {
            $dataRows[] = $row;
        }

        $errorMsg = [];
        // 必須項目の検証
        Log::info('submitType::' . $submitType);
        switch($submitType) {
            case 'payment':
                if(!isset($req['set_cust_payment_date']) || strlen($req['set_cust_payment_date']) == 0) {
                    $errorMsg[] = __('messages.error.order_search.enter_name', ['name' => '顧客入金日']);
                }
                if(!isset($req['set_account_payment_date']) || strlen($req['set_account_payment_date']) == 0) {
                    $errorMsg[] = __('messages.error.order_search.enter_name', ['name' => '口座入金日']);
                }
                break;
            case 'output_delivery_file':
            case 're_output_delivery_file':
                if(empty($req['output_queue_report']) && (!isset($req['output_queue_delivery']) || strlen($req['output_queue_delivery']) == 0)) {
                    $errorMsg[] = __('messages.error.order_search.select_name', ['name' => '出力する帳票・CSVの種類']);
                }
                break;
                // 			case 'change_deli_hope_date':
                // 				if(!isset($req['set_change_deli_hope_date']) || strlen($req['set_change_deli_hope_date']))
                // 				{
                // 					$errorMsg[] = '配送希望日をセットしてください。';
                // 				}
                // 				break;
            default:
                break;
        }

        if(count($errorMsg) != 0) {
            return implode('<br>', $errorMsg);
        }


        if($req['bulk_target_type'] == 1) {
            if(empty($req[$outputCheckColumnName])) {
                return __('messages.error.order_search.no_processing_row');
            }
            $targetOrderDestination = $req[$outputSearchColumnName];
        } else {
            $options = [
                'join_table' => ['t_order_destination'],
                'with' => ['orderTags', 'orderMemo'],
                'should_idList' => true,
            ];
            $targetOrderDestination = $search->execute($req, $options);
        }
        Log::info('targetOrderDestination::' . print_r($targetOrderDestination, true));

        // $searchKey により変換
        if($searchKey == 't_order_hdr_id') {
            $query = OrderDestinationModel::query();
            $outputTarget = $query
                ->whereIn('t_order_destination_id', $targetOrderDestination)
                ->pluck('t_order_hdr_id')
                ->unique()->toArray();
        } elseif ($searchKey == 't_deli_hdr_id') {
            $query = OrderDtlModel::query();
            $outputTarget = $query
                ->whereIn('t_order_destination_id', $targetOrderDestination)
                ->pluck('t_deli_hdr_id')->toArray();
        }
        Log::info('outputTarget::' . print_r($outputTarget, true));

        if(empty($outputTarget)) {
            return __('messages.error.order_search.no_processing_target');
        }
        if($submitType == 'new_send_recipt_mail' && count($outputTarget) > 200) {
            return __('messages.error.order_search.search_results_exceeded', ['count' => '200']);
        }

        $searchInfo = [
            $searchKey => array_unique($outputTarget),
        ];

        switch($submitType) {
            case 'output_pdf':
                // 帳票出力
                $searchInfo['output_queue_report'] = $req['output_queue_report'];
                $searchInfo['output_warehouse_id'] = $req['output_warehouse_id'];
                $result = $this->setOutputQueueReport($searchInfo);
                break;
            case 'output_delivery_csv':
                // 送り状データ出力
                $searchInfo['output_queue_delivery'] = $req['output_queue_delivery'];
                $searchInfo['output_warehouse_id'] = $req['output_warehouse_id'];
                $result = $this->setOutputQueueDelivery($searchInfo);
                break;
            case 'output_delivery_file':
            case 're_output_delivery_file':
                $pdfResult = '';
                $csvResult = '';
                $searchInfo['output_warehouse_id'] = $req['output_warehouse_id'];

                // 出荷帳票・データ出力
                if(!empty($req['output_queue_report'])) {
                    foreach($req['output_queue_report'] as $outputQueueReport) {
                        $searchInfo['output_queue_report'] = $outputQueueReport;
                        if(strlen($outputQueueReport) != 0) {
                            switch($submitType) {
                                case 'output_delivery_file':
                                    $pdfResult = $this->setOutputQueueReport($searchInfo);
                                    break;
                                case 're_output_delivery_file':
                                    $pdfResult = $this->setOutputQueueReport($searchInfo, 't_deli_hdr_id', true);
                                    break;
                            }
                            if($pdfResult != '') {
                                break;
                            }
                        }
                    }
                }
                if(!empty($req['output_queue_delivery']) || strlen($req['output_queue_delivery']) != 0) {
                    $searchInfo['output_queue_delivery'] = $req['output_queue_delivery'];
                    switch($submitType) {
                        case 'output_delivery_file':
                            $csvResult = $this->setOutputQueueDelivery($searchInfo);
                            break;
                        case 're_output_delivery_file':
                            $csvResult = $this->setOutputQueueDelivery($searchInfo, 't_deli_hdr_id', true);
                            break;
                    }
                }

                $result = $pdfResult. $csvResult;
                if($pdfResult != '' && $csvResult != '') {
                    $result = $pdfResult. '<br>'. $csvResult;
                }
                break;
            case 'output_order_file':
                // 受注CSV出力
                $res = $this->setCsvQueue(
                    [
                        'search_info' => [$this->outputSubmitColumn[$submitType] => implode(',', $searchInfo[$searchKey])],
                        'bulk_output_flg' => 0
                    ],
                    $req['output_order_csv_type'],
                    ['_token' => '']
                );

                if(!$res) {
                    return __('messages.error.order_search.processing_failed');
                }
                break;
            default:
                $setQueueRow = [
                    'search_info' => [$this->outputSubmitColumn[$submitType] => implode(',', $searchInfo[$searchKey])],
                    'bulk_output_flg' => 0
                ];

                if(isset($req['reissue'])) {
                    $setQueueRow['search_info']['reissue'] = $req['reissue'];
                }
                // 受注更新バッチ使用の場合は画面からの更新である旨を記載
                if(in_array($submitType, $this->orderPartBatchName)) {
                    $setQueueRow['app_register_flg'] = 1;
                }

                if(!empty($this->outputBatchTargetControl[$submitType])) {
                    foreach($this->outputBatchTargetControl[$submitType] as $targetControl) {
                        if(isset($req[$targetControl])) {
                            $setQueueRow[$targetControl] = $req[$targetControl];
                        }
                    }
                }

                // 受注コメントは空欄の場合があるので、追記する
                if($submitType == 'change_operator_comment') {
                    $setQueueRow['set_operator_comment'] = !empty($req['set_operator_comment']) ? $req['set_operator_comment'] : '';
                    $setQueueRow['add_operator_comment_flg'] = !empty($req['add_operator_comment_flg']) ? $req['add_operator_comment_flg'] : 0;
                }

                $res = $this->setCsvQueue(
                    $setQueueRow,
                    $this->outputBatchExecutingTypes[$submitType],
                    ['_token' => $req['_token']]
                );

                if(!$res) {
                    switch($submitType) {
                        default:
                            return __('messages.error.order_search.processing_failed');
                            break;
                    }
                }
                break;
        }

        return $result;
    }



    /**
     * 帳票出力キュー登録
     * @param array $requestRows Httpリクエスト内のPOSTデータ(※対象IDの単複不問)
     * @param string $targetIdKeyName requestRows内の出力対象ID取得先(Default：t_deli_hdr_id)
     * @param bool $reOutputFlg 再出力かどうか(falseの場合、指示タイムスタンプがセットされている場合はキューに登録しない
     * @param bool $infoFlg 詳細画面からかどうか(trueの場合は再出力かどうかの検証をしない)
     */
    public function setOutputQueueReport($requestRows, $targetIdKeyName = 't_deli_hdr_id', $reOutputFlg = false, $infoFlg = false)
    {
        if (!isset($requestRows['output_queue_report']) || !isset($requestRows[$targetIdKeyName])) {
            if($requestRows['output_queue_report'] != 'exppdf_receipt') {
                return __('messages.error.order_search.no_output_target');
            }
        }

        $batchType = $requestRows['output_queue_report'];

        $targetIds = [];
        if (is_array($requestRows[$targetIdKeyName])) {
            $targetIds = $requestRows[$targetIdKeyName];
        } else {
            $targetIds[] = $requestRows[$targetIdKeyName];
        }

        // 詳細画面からでない場合、出力済みかどうかを判断する
        if(!$infoFlg) {
            $deliveryRequest = [
                'request' => [
                    'm_account_id' => $this->esmSessionManager->getAccountId(),
                    'list_detail_flg' => 0,
                    'display_csv_flag' => 0,
                    'search_info' => [
                        't_deli_hdr_id' => implode(',', $targetIds),
                    ]
                ]
            ];

            $deliveryRows = $this->esm2ApiManager->connectionApi($deliveryRequest, 'searchOrderDelivery', Esm2SubSys::ORDER_API);

            if(!empty($deliveryRows['result']['status'])) {
                return __('messages.error.order_search.failed_output_registration', ['batchtype' => '帳票']);
            }

            if(empty($deliveryRows['result']['search_record_count'])) {
                return __('messages.error.order_search.no_shipment_for_output', ['batchtype' => $this->outputPdfNames[$batchType]]);
            }

            $outputDeliveryIds = [];

            foreach($deliveryRows['search_result'] as $row) {
                if($reOutputFlg) {
                    // 指示時刻が未設定の場合、再出力対象にしない
                    if(empty($row[$this->outputCheckInstructTimestamp[$batchType]]) || $row[$this->outputCheckInstructTimestamp[$batchType]] == '0000-00-00 00:00:00') {
                        continue;
                    }
                } else {
                    // 指示時刻が既に設定されている場合、出力対象にしない
                    if(!empty($row[$this->outputCheckInstructTimestamp[$batchType]]) && $row[$this->outputCheckInstructTimestamp[$batchType]] != '0000-00-00 00:00:00') {
                        continue;
                    }
                }

                // 出力倉庫が指定されている場合、対象の倉庫でない出荷は出力対象にしない
                if(!empty($requestRows['output_warehouse_id'])) {
                    if($row['m_warehouse_id'] != $requestRows['output_warehouse_id']) {
                        continue;
                    }
                }

                $outputDeliveryIds[] = $row['t_deli_hdr_id'];
            }

            if(empty($outputDeliveryIds)) {
                if($reOutputFlg) {
                    return __('messages.error.order_search.no_shipment_for_reoutput', ['batchtype' => $this->outputPdfNames[$batchType]]);
                } else {
                    return __('messages.error.order_search.no_shipment_for_output3', ['batchtype' => $this->outputPdfNames[$batchType]]);
                }
            }

            $targetIds = $outputDeliveryIds;
        }

        $THdrId = 't_deli_hdr_id';
        if($requestRows['output_queue_report'] == 'exppdf_receipt') {
            // 領収書の場合は受注IDを取得する？
            $targetIds = $requestRows[$THdrId];
        }

        $queueRequestRow = [
            'search_info' => [$THdrId => implode(',', $targetIds)],
            'bulk_output_flg' => 0
        ];
        if(isset($requestRows['reissue'])) {
            $queueRequestRow['search_info']['reissue'] = $requestRows['reissue'];
        }

        // キューに登録する
        if (!$this->setCsvQueue($queueRequestRow, $batchType, ['_token' => ''])) {
            return __('messages.error.order_search.failed_output_registration', ['batchtype' => '帳票']);
        }

        // 出荷の指示日付を更新する
        $instructDateTime = new Carbon();

        $deliIds = explode(',', implode(',', $targetIds));

        foreach($deliIds as $deliId) {
            $instuctDateRequestData = [
                'request' => [
                    'm_account_id' => $this->esmSessionManager->getAccountId(),
                    'account_cd' => $this->esmSessionManager->getAccountCode(),
                    'operator_id' => $this->esmSessionManager->getOperatorId(),
                    'register_info' => [
                        'deli_id' => $deliId,
                        $this->outputCheckInstructTimestamp[$batchType] => $instructDateTime->format('Y-m-d H:i:s'),
                        'update_operator_id' => $this->esmSessionManager->getOperatorId(),
                    ]
                ]
            ];

            $response = $this->esm2ApiManager->connectionApi($instuctDateRequestData, 'registerOrderDeliStatus', Esm2SubSys::ORDER_API);

            if(!empty($response['result']['status'])) {
                logger($response['result']['error']);
            }
        }

        return '';
    }

    /**
     * 出荷データ出力キュー登録
     * @param array $requestRows Httpリクエスト内のPOSTデータ(※対象IDの単複不問)
     * @param string $targetIdKeyName requestRows内の出力対象ID取得先(Default：t_deli_hdr_id)
     * @param bool $reOutputFlg 再出力かどうか(falseの場合、指示タイムスタンプがセットされている場合はキューに登録しない
     * @param bool $infoFlg 詳細画面からかどうか(trueの場合は再出力かどうかの検証をしない)
     */
    private function setOutputQueueDelivery($requestRows, $targetIdKeyName = 't_deli_hdr_id', $reOutputFlg = false, $infoFlg = false)
    {
        if (!isset($requestRows['output_queue_delivery']) || !isset($requestRows[$targetIdKeyName])) {
            return __('messages.error.order_search.no_output_target');
        }

        $batchType = 'expcsv_delivery_'. $requestRows['output_queue_delivery'];

        $targetIds = [];
        if (is_array($requestRows[$targetIdKeyName])) {
            $targetIds = $requestRows[$targetIdKeyName];
        } else {
            $targetIds[] = $requestRows[$targetIdKeyName];
        }

        // 詳細画面からでない場合、出力済みかどうかを判断する
        if(!$infoFlg) {
            $deliveryRequest = [
                'request' => [
                    'm_account_id' => $this->esmSessionManager->getAccountId(),
                    'list_detail_flg' => 0,
                    'display_csv_flag' => 0,
                    'search_info' => [
                        't_deli_hdr_id' => implode(',', $targetIds),
                    ]
                ]
            ];

            $deliveryRows = $this->esm2ApiManager->connectionApi($deliveryRequest, 'searchOrderDelivery', Esm2SubSys::ORDER_API);

            if(!empty($deliveryRows['result']['status'])) {
                return __('messages.error.order_search.failed_output_registration', ['batchtype' => '出荷データ']);
            }

            if(empty($deliveryRows['result']['search_record_count'])) {
                return __('messages.error.order_search.no_shipment_for_output', ['batchtype' => '出荷データ']);
            }

            $outputDeliveryIds = [];

            foreach($deliveryRows['search_result'] as $row) {
                if($reOutputFlg) {
                    // 指示時刻が既に設定されている場合、出力対象にしない
                    if(empty($row['invoice_instruct_datetime']) || $row['invoice_instruct_datetime'] == '0000-00-00 00:00:00') {
                        continue;
                    }
                } else {
                    // 指示時刻が既に設定されている場合、出力対象にしない
                    if(!empty($row['invoice_instruct_datetime']) && $row['invoice_instruct_datetime'] != '0000-00-00 00:00:00') {
                        continue;
                    }
                }

                // 出力倉庫が指定されている場合、対象の倉庫でない出荷は出力対象にしない
                if(!empty($requestRows['output_warehouse_id'])) {
                    if($row['m_warehouse_id'] != $requestRows['output_warehouse_id']) {
                        continue;
                    }
                }

                $outputDeliveryIds[] = $row['t_deli_hdr_id'];
            }

            if(empty($outputDeliveryIds)) {
                if($reOutputFlg) {
                    return __('messages.error.order_search.no_shipment_for_output2', ['batchtype' => '出荷データ']);
                } else {
                    return __('messages.error.order_search.no_shipment_for_output3', ['batchtype' => '出荷データ']);
                }
            }

            $targetIds = $outputDeliveryIds;
        }

        $queueRequestRow = [
            'search_info' => ['t_deli_hdr_id' => implode(',', $targetIds)],
            'bulk_output_flg' => 0
        ];

        // キューに登録する
        if (!$this->setCsvQueue($queueRequestRow, $batchType, ['_token' => ''])) {
            return __('messages.error.order_search.failed_output_registration', ['batchtype' => '出荷データ']);
        }

        // 出荷の指示日付を更新する
        $instructDateTime = new Carbon();

        $deliIds = explode(',', implode(',', $targetIds));

        foreach($deliIds as $deliId) {
            $instuctDateRequestData = [
                'request' => [
                    'm_account_id' => $this->esmSessionManager->getAccountId(),
                    'account_cd' => $this->esmSessionManager->getAccountCode(),
                    'operator_id' => $this->esmSessionManager->getOperatorId(),
                    'register_info' => [
                        'deli_id' => $deliId,
                        'invoice_instruct_datetime' => $instructDateTime->format('Y-m-d H:i:s'),
                        'update_operator_id' => $this->esmSessionManager->getOperatorId(),
                    ]
                ]
            ];

            $response = $this->esm2ApiManager->connectionApi($instuctDateRequestData, 'registerOrderDeliStatus', Esm2SubSys::ORDER_API);

            if(!empty($response['result']['status'])) {
                logger($response['result']['error']);
            }
        }

        return '';
    }

    /**
     * キュー登録処理
     */
    protected function setCsvQueue($data, $batchType, $requestData)
    {
        $this->registedBatchExecuteId = 0;

        $nowTime = new Carbon();

        // バッチ登録の処理を行う
        $registerInfo = [
            'execute_batch_type' => $batchType,
            'batchjob_create_datetime' => $nowTime->format('Y-m-d H:i:s'),
            'execute_conditions' => $data,
            '_token' => $requestData['_token'],
        ];

        $response = $this->registerBatchExecuteInstruction->execute($registerInfo);

        // 登録処理の結果を返す
        if($response['result']['status'] == 0) {
            $this->registedBatchExecuteId = $response['result']['t_execute_batch_instruction_id'];
            return true;
        }

        return false;
    }
}
