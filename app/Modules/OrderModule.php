<?php

namespace App\Modules;

use App\Modules\Common\CommonModule;
use Carbon\Carbon;

class OrderModule extends CommonModule
{
    /**
     * API URL
     */
    protected $searchUri = 'searchOrder';

    /**
     * 編集時に取得する際のデータの主キー名称
     */
    protected $searchPrimaryKey = 't_order_hdr_id';

    /**
     * 確認区分
     */
    protected $checkTypeNames = [
        '0' => '未確認',
        '2' => '確認済み',
        '9' => '対象外',
    ];

    /**
     * 与信区分
     */
    protected $creditTypeNames = [
        '0' => '未処理',
        '1' => '与信NG',
        '2' => '与信OK',
        '9' => '対象外',
    ];

    /**
     * 入金区分
     */
    protected $paymentTypeNames = [
        '0' => '未入金',
        '1' => '一部入金',
        '2' => '入金済み',
        '9' => '対象外',
    ];

    /**
     * 在庫引当区分
     */
    protected $reservationTypeNames = [
        '0' => '未引当',
        '1' => '一部引当',
        '2' => '引当済み',
        '9' => '対象外',
    ];

    /**
     * 出荷指示区分
     */
    protected $deliInstructTypeNames = [
        '0' => '未指示',
        '1' => '一部指示',
        '2' => '指示済み',
        '9' => '対象外',
    ];

    /**
     * 出荷確定区分
     */
    protected $deliDecisionTypeNames = [
        '0' => '未確定',
        '1' => '一部確定',
        '2' => '確定済み',
        '9' => '対象外',
    ];

    /**
     * 決済売上計上区分
     */
    protected $settlementSalesTypeNames = [
        '0' => '未計上',
        '1' => '計上NG',
        '2' => '計上済み',
        '9' => '対象外',
    ];

    /**
     * 売上ステータス反映区分
     */
    protected $salesStatusTypeNames = [
        '0' => '未計上',
        '1' => '計上NG',
        '2' => '計上済み',
        '9' => '対象外',
    ];

    /**
     * 後払い.com請求書送付種別
     */
    protected $cbBilledTypeNames = [
        '0' => '同梱',
        '1' => '別送',
    ];

    /**
     * 後払い.com決済ステータス
     */
    protected $cbCreditStatusNames = [
        '0'		=>	'未処理',
        '10'	=>	'与信待ち',
        '11'	=>	'与信中',
        '12'	=>	'与信完了',
        '19'	=>	'与信NG',
        '90'	=>	'与信取消待ち',
        '91'	=>	'キャンセル完了',
        '99'	=>	'キャンセルNG',
    ];

    /**
     * 後払い.com出荷ステータス
     */
    protected $cbDeliStatusNames = [
        '0'		=> '未処理',
        '10'	=> '出荷連携待ち',
        '11'	=> '出荷連携完了',
        '19'	=> '出荷連携NG',
    ];

    /**
     * 後払い.com請求書送付ステータス
     */
    protected $cbBilledStatusNames = [
        '0'		=>	'未処理',
        '11'	=>	'印刷キュー転送完了',
        '12'	=>	'印刷キュー転送NG',
        '21'	=>	'印字情報取得完了',
        '22'	=>	'印字情報取得NG',
        '31'	=>	'発行報告完了',
        '32'	=>	'発行報告NG',
        '40'	=>	'請求書別送',
    ];

    /**
     * 与信データ出力用一覧
     */
    protected $outputPaymentAuthCsvNames = [
        'expcsv_np_credit_regist' => 'NP与信登録',
    ];

    /**
     * 与信結果取込用一覧
     */
    protected $inputPaymentAuthCsvNames = [
        // 200番台 NP
        'impcsv_np_credit_result' => 'NP与信結果',
    ];

    /**
     * 出荷報告データ出力用一覧
     */
    protected $outputPaymentDeliveryCsvNames = [
        'outcsv_np_delivery_result' => 'NP出荷報告',
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
                return '出力対象が指定されていません。';
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
                    'm_account_id' => $this->getAccountId(),
                    'list_detail_flg' => 0,
                    'display_csv_flag' => 0,
                    'search_info' => [
                        't_deli_hdr_id' => implode(',', $targetIds),
                    ]
                ]
            ];

            $deliveryRows = json_decode($this->connectionApi($deliveryRequest, 'searchOrderDelivery'), true);

            if(!empty($deliveryRows['response']['result']['status'])) {
                return '帳票出力処理の登録に失敗しました。';
            }

            if(empty($deliveryRows['response']['result']['search_record_count'])) {
                return $this->outputPdfNames[$batchType].'を出力可能な出荷が見つかりませんでした。';
            }

            $outputDeliveryIds = [];

            foreach($deliveryRows['response']['search_result'] as $row) {
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
                    return $this->outputPdfNames[$batchType].'を再出力可能な出荷が見つかりませんでした。未出力の場合は出力を実行してください。';
                } else {
                    return $this->outputPdfNames[$batchType].'を出力可能な出荷が見つかりませんでした。既に出力済みの場合は再出力を実行してください。';
                }
            }

            $targetIds = $outputDeliveryIds;
        }

        $THdrId = 't_deli_hdr_id';
        if($requestRows['output_queue_report'] == 'exppdf_receipt') {
            $THdrId = 't_order_hdr_id';
            $targetIds = str_split($requestRows[$THdrId], 10);
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
            return '帳票出力処理の登録に失敗しました。';
        }

        // 出荷の指示日付を更新する
        $instructDateTime = new Carbon();

        $deliIds = explode(',', implode(',', $targetIds));

        foreach($deliIds as $deliId) {
            $instuctDateRequestData = [
                'request' => [
                    'm_account_id' => $this->getAccountId(),
                    'account_cd' => $this->getAccountCode(),
                    'operator_id' => $this->getOperatorId(),
                    'register_info' => [
                        'deli_id' => $deliId,
                        $this->outputCheckInstructTimestamp[$batchType] => $instructDateTime->format('Y-m-d H:i:s'),
                        'update_operator_id' => $this->getOperatorId(),
                    ]
                ]
            ];

            $response = json_decode($this->connectionApi($instuctDateRequestData, 'registerOrderDeliStatus'), true);

            if(!empty($response['response']['result']['status'])) {
                logger($response['response']['result']['error']);
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
    public function setOutputQueueDelivery($requestRows, $targetIdKeyName = 't_deli_hdr_id', $reOutputFlg = false, $infoFlg = false)
    {
        if (!isset($requestRows['output_queue_delivery']) || !isset($requestRows[$targetIdKeyName])) {
            return '出力対象が指定されていません。';
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
                    'm_account_id' => $this->getAccountId(),
                    'list_detail_flg' => 0,
                    'display_csv_flag' => 0,
                    'search_info' => [
                        't_deli_hdr_id' => implode(',', $targetIds),
                    ]
                ]
            ];

            $deliveryRows = json_decode($this->connectionApi($deliveryRequest, 'searchOrderDelivery'), true);

            if(!empty($deliveryRows['response']['result']['status'])) {
                return '出荷データ出力処理の登録に失敗しました。';
            }

            if(empty($deliveryRows['response']['result']['search_record_count'])) {
                return '出荷データを出力可能な出荷が見つかりませんでした。';
            }

            $outputDeliveryIds = [];

            foreach($deliveryRows['response']['search_result'] as $row) {
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
                    return '出荷データを出力可能な出荷が見つかりませんでした。未出力の場合は出力を実行してください。';
                } else {
                    return '出荷データを出力可能な出荷が見つかりませんでした。既に出力済みの場合は再出力を実行してください。';
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
            return '出荷データ出力処理の登録に失敗しました。';
        }

        // 出荷の指示日付を更新する
        $instructDateTime = new Carbon();

        $deliIds = explode(',', implode(',', $targetIds));

        foreach($deliIds as $deliId) {
            $instuctDateRequestData = [
                'request' => [
                    'm_account_id' => $this->getAccountId(),
                    'account_cd' => $this->getAccountCode(),
                    'operator_id' => $this->getOperatorId(),
                    'register_info' => [
                        'deli_id' => $deliId,
                        'invoice_instruct_datetime' => $instructDateTime->format('Y-m-d H:i:s'),
                        'update_operator_id' => $this->getOperatorId(),
                    ]
                ]
            ];

            $response = json_decode($this->connectionApi($instuctDateRequestData, 'registerOrderDeliStatus'), true);

            if(!empty($response['response']['result']['status'])) {
                logger($response['response']['result']['error']);
            }
        }

        return '';
    }

    /**
     * 希望時間帯設定情報取得
     * @param string $featureId
     * @param bool $onlyUse
     */
    protected function searchDeliveryTimeHope($featureId, $onlyUse)
    {
        $this->setCurrentApiKey('searchDeliveryTimeHope');
        logger($this->currentApiKey);
        $requestData = [];
        $apiResults = json_decode($this->getRows($requestData), true);
        $dataList = [];
        if (isset($apiResults['response']['search_result'])) {
            $dataList = $apiResults['response']['search_result'];
        }
        $this->setCurrentApiKey('');

        return $dataList;
    }

    /**
     * 希望時間帯設定情報取得
     * @param string $featureId
     * @param bool $onlyUse
     */
    protected function searchDeliveryTypeTimeHopeMap($featureId, $onlyUse)
    {
        $this->setCurrentApiKey('searchDeliveryTypeTimeHopeMap');
        logger($this->currentApiKey);
        $requestData = [
            'm_warehouse_type' => 1,
        ];
        $apiResults = json_decode($this->getRows($requestData), true);
        $dataList = [];
        if (isset($apiResults['response']['search_result'])) {
            $dataList = $apiResults['response']['search_result'];
        }
        $this->setCurrentApiKey('');

        return $dataList;
    }

    //送付先タブのデフォルト名
    public const DESTINATION_NAME_DEFAULT	=	'送付先';
    //登録API
    protected $registerUri = 'registerOrderInfo';
    //共通処理以外のAPI
    protected $apiUris = [
        'searchEcPage'				=>	[ 'subsys'	=>	'ami',		'apiurl'	=>	'ec-page/searchEcPage' ],
        'searchCustomer'			=>	[ 'subsys'	=>	'cc',		'apiurl'	=>	'searchCustomer' ],
        'registerOrderTag'			=>	[ 'subsys'	=>	'order',	'apiurl'	=>	'registerOrderTagTransaction' ],
//		'searchDeliveryTimeHope'	=>	[ 'subsys'	=>	'common',	'apiurl'	=>	'searchDeliveryTimeHope' ],
        'searchDeliveryTimeHope'	=>	[ 'subsys'	=>	'global',	'apiurl'	=>	'searchDeliveryTimeHope' ],
        'searchDeliveryTypeTimeHopeMap'	=> [ 'subsys' => 'common', 'apiurl' => 'searchDeliveryTimehopeMap' ],	// 配送方法-希望時間帯設定情報取得
        'searchSalesVolInfo'		=>	[ 'subsys'	=>	'stock',	'apiurl'	=>	'searchSalesVolInfo' ]
    ];
    //パラメータ変換カラム設定(before ⇒ after)
    protected $exchangeColomns = [
        'tel'			=>	'order_tel1',
        'name_kanji'	=>	'order_name',
        'name_kana'		=>	'order_name_kana',
        'postal'		=>	'order_postal',
        'address1'		=>	'order_address1',
        'address2'		=>	'order_address2',
        'email'			=>	'order_email1',
        'm_cust_id'		=>	'm_cust_id'
    ];
    //エラーメッセージ
    protected $errorMessages = [
        'DELETE_TAG_NOT_SELECT'					=>	'削除する受注タグをチェックしてください。',
        'DELETE_DESTINAION_ERROR'				=>	'この配送先は削除できません。',
        'DELETE_DESTINAION_ERROR_DTL_EXISTS'	=>	'明細番号が存在する配送先は削除できません。',
        'CHANGE_DELIVERY_FEE_FREE'				=>	'送料が無料に変更になりました。',
        'CHANGE_DELIVERY_FEE_CHARGE'			=>	'送料が有料に変更になりました。'
    ];
    //販売タイプ
    protected $sellType = [
        'ITEM'	=>	1,
        'SET'	=>	2,
        'SKU'	=>	3
    ];
    //画面URL
    protected $featureId;
    //マスタデータ
    protected $masterDatas;
    //変更前受注データ
    protected $oldOrder;

    /**
     * 変更前の受注データ取得
     * @param int $pKey
     * @return array
     */
    public function getOrderData($pKey)
    {
        //受注検索
        $this->oldOrder = $this->getEditData($pKey);

        //受注検索で取得した不要データ削除
        unset($this->oldOrder['progress_update_history']);
        unset($this->oldOrder['cooperation_history']);
        unset($this->oldOrder['settlement_history']);
        unset($this->oldOrder['payment']);
        unset($this->oldOrder['mail_send_history_id']);
        unset($this->oldOrder['report_output_history_id']);

        return $this->oldOrder;
    }

    /**
     * 画面URLセット
     * @param string $id
     */
    public function setFeatureId($url)
    {
        $this->featureId = $url;
    }

    /**
     * マスタデータ取得処理
     * @param int $orderId
     */
    public function getMasterDatas($orderId)
    {
        if (!empty($this->masterDatas)) {
            return $this->masterDatas;
        }

        $valueArray = [];

        //基本情報
        $valueArray['m_shops'] = [];
        $dataList = $this->searchShops($this->featureId);

        if (isset($dataList) && is_array($dataList) && count($dataList) > 0) {
            $valueArray['m_shops'] = [
                'base_delivery_fee'						=>	$dataList[0]['base_delivery_fee'],
                'item_price_for_free_delivery_fee'		=>	$dataList[0]['item_price_for_free_delivery_fee'],
                'receipt_proviso'						=>	$dataList[0]['receipt_proviso']
            ];
        }

        //配送方法マスタ
        $valueArray['m_delivery_types'] = [];
        $dataList = $this->searchDeliveryTypes($this->featureId, true);
        if (is_array($dataList) && count($dataList) > 0) {
            $valueArray['m_delivery_types'] = $this->changeIdValuesList(
                $dataList,
                [
                    'id'	=>	'm_delivery_types_id',
                    'value'	=>	'm_delivery_type_name'
                ],
                false,
                true
            );

            $valueArray['m_delivery_type_list'] = $dataList;
        }

        //配送時間帯
        $valueArray['m_delivery_time_hope'] = [];
        $dataList = $this->searchDeliveryTypeTimeHopeMap($this->featureId, true);
        if (is_array($dataList) && count($dataList) > 0) {
            $deliTimeHopeList = collect($dataList)->unique(function ($value) {
                return $value['m_delivery_time_hope_id'].$value['m_delivery_company_id'];
            });

            $valueArray['m_delivery_time_hope'] = $deliTimeHopeList;
        }

        //支払方法マスタ
        $valueArray['m_payment_types'] = [];
        $dataList = $this->searchPaymentTypes($this->featureId, true);
        if (is_array($dataList) && count($dataList) > 0) {
            $valueArray['m_payment_types'] = $this->changeIdValuesList(
                $dataList,
                [
                    'id'	=>	'm_payment_types_id',
                    'value'	=>	'm_payment_types_name'
                ],
                false,
                true
            );
        }

        //ECサイトマスタ
        $valueArray['m_ecs'] = [];
        $valueArray['m_ec_urls'] = [];
        $dataList = $this->searchEcs($this->featureId, true);
        if (is_array($dataList) && count($dataList) > 0) {
            $valueArray['m_ecs'] = $this->changeIdValuesList(
                $dataList,
                [
                    'id'	=>	'm_ecs_id',
                    'value'	=>	'm_ec_name'
                ],
                false,
                true
            );
            //URL
            if (is_array($dataList) && count($dataList) > 0) {
                $valueArray['m_ec_urls'] = $this->changeIdValuesList(
                    $dataList,
                    [
                        'id'	=>	'm_ecs_id',
                        'value'	=>	'm_ec_url'
                    ],
                    false,
                    false
                );
            }
        }

        //受注担当者マスタ
        $valueArray['m_operators'] = [];
        $dataList = $this->searchOperators($this->featureId, true);
        if (is_array($dataList) && count($dataList) > 0) {
            $valueArray['m_operators'] = $this->changeIdValuesList(
                $dataList,
                [
                    'id'	=>	'm_operators_id',
                    'value'	=>	'm_operator_name'
                ],
                false,
                true
            );
        }

        //受注方法
        $dataList = $this->searchItemNameTypes($this->featureId, true, [
            'delete_flg'		=>	'0',	//有効データ
            'm_itemname_type'	=>	'1'		//1:受注方法
        ]);
        $valueArray['m_ordertypes'] = [];
        if (is_array($dataList) && count($dataList) > 0) {
            $valueArray['m_ordertypes'] = $this->changeIdValuesList(
                $dataList,
                [
                    'id'	=>	'm_itemname_types_id',
                    'value'	=>	'm_itemname_type_name'
                ],
                false,
                true
            );
        }

        //都道府県
        $valueArray['m_prefectures'] = [];
        $dataList = $this->searchPrefectual($this->featureId);
        if (is_array($dataList) && count($dataList) > 0) {
            $valueArray['m_prefectures'] = $this->changeIdValuesList(
                $dataList,
                [
                    'id'	=>	'm_prefectural_id',
                    'value'	=>	'prefectual_name'
                ],
                false,
                true
            );
        }

        //受注タグマスタ
        $valueArray['m_order_tag'] = [];
        $requestData = [
            'm_order_tag_id'	=>	'',
            'm_order_tag_sort'	=>	'',
            'tag_name'			=>	'',
            'tag_icon'			=>	'',
            'tag_color'			=>	'',
            'tag_context'		=>	'',
            'and_or'			=>	'',
            'auto_timming'		=>	'',
            'deli_stop_flg'		=>	''
        ];
        $dataList = $this->searchOrderTagMaster($requestData);
        if (is_array($dataList) && count($dataList) > 0) {
            $valueArray['m_order_tag'] = $dataList;
        }
        return $valueArray;
    }

    /**
     * 編集画面のAPIで追加する追加パラメータのセット
     * @param array $reqData
     * @return array
     */
    protected function setEditSearchExtend($reqData)
    {
        //受注検索(詳細取得)
        $reqData['list_detail_flg'] = '1';
        return $reqData;
    }

    /**
     * 顧客情報取得
     * @param int $mCustId
     * @return array
     */
    protected function getCustInfo($mCustId)
    {
        $custInfo = [];
        if (!empty($mCustId)) {
            //顧客検索
            $this->setCurrentApiKey('searchCustomer');
            $requestData = [
                'delete_flg'	=>	'0',
                'm_cust_id'		=>	$mCustId
            ];
            $apiResults = json_decode($this->getRows($requestData), true);
            $dataList = [];
            if (isset($apiResults['response']['search_result'][0])) {
                $dataList = $apiResults['response']['search_result'][0];
                //顧客ランク名
                $dataList['cust_rank_name'] = '';
                if (!empty($dataList['m_cust_runk_id'])) {
                    $dataRank = $this->searchItemNameTypes($this->featureId, true, [
                        'delete_flg'			=>	'0',		//有効データ
                        'm_itemname_type'		=>	'3',		//3:顧客ランク
                        'm_itemname_types_id'	=>	$dataList['m_cust_runk_id']
                    ]);
                    if (is_array($dataRank) && count($dataRank) > 0) {
                        if (isset($dataRank[0]['m_itemname_type_name'])) {
                            $dataList['cust_rank_name'] = $dataRank[0]['m_itemname_type_name'];
                        }
                    }
                }
            }
            $this->setCurrentApiKey('');
            $custInfo = $dataList;
        }
        return $custInfo;
    }

    /**
     * 前画面からの遷移情報を受注データ用に変換
     * @param array $editRow
     * @return array
     */
    public function exchangeInitParamName($editRow)
    {
        foreach($this->exchangeColomns as $beforeKey => $afterKey) {
            if (isset($editRow[$beforeKey]) && ($beforeKey != $afterKey)) {
                $editRow[$afterKey] = $editRow[$beforeKey];
                unset($editRow[$beforeKey]);
            }
        }
        return $editRow;
    }

    /**
     * マスタデータ設定
     * @param array $mstDatas
     */
    public function setMasterDatas($mstDatas)
    {
        $this->masterDatas = $mstDatas;
    }

    /**
     * 登録画面に追加で渡したいデータをセットする
     */
    public function setRegisterExtendData($editRow, $pKey = null)
    {
        //マスタデータ取得
        $valueArray = $this->getMasterDatas($pKey);
        $this->masterDatas = $valueArray;
        //顧客情報
        if (isset($editRow['m_cust_id']) && strlen($editRow['m_cust_id']) > 0) {
            //受注登録、または、受注修正でまだ顧客情報を退避していない場合
            if(empty($pKey) || empty($valueArray['m_cust'])) {
                $valueArray['m_cust'] = $this->getCustInfo($editRow['m_cust_id']);
                if (!empty($pKey)) {
                    $this->masterDatas = $valueArray;
                }
            }
        }

        //受注修正時
        if (!empty($pKey)) {
            if (empty($this->oldOrder)) {
                $this->oldOrder = $this->getOrderData($pKey);
            }
            //受注タグ情報
            if (isset($this->oldOrder['order_tag'])) {
                $tagList = [];
                $arrayKeys = array_column($valueArray['m_order_tag'], 'm_order_tag_id');
                foreach($this->oldOrder['order_tag'] as $tagRow) {
                    //取消済はスキップ
                    if (!is_null($tagRow['cancel_timestamp']) && ($tagRow['cancel_timestamp'] > 0)) {
                        continue;
                    }
                    $searchIndex = array_search($tagRow['m_order_tag_id'], $arrayKeys);
                    if ($searchIndex === false) {
                        continue;
                    }
                    $tagList[] = [
                        't_order_tag_id'	=>	$tagRow['t_order_tag_id'],
                        'm_order_tag_id'	=>	$tagRow['m_order_tag_id'],
                        'tag_display_name'	=>	$valueArray['m_order_tag'][$searchIndex]['tag_display_name'],
                        'tag_color'			=>	$valueArray['m_order_tag'][$searchIndex]['tag_color'],
                        'font_color'		=>	$valueArray['m_order_tag'][$searchIndex]['font_color'],
                        'tag_context'		=>	$valueArray['m_order_tag'][$searchIndex]['tag_context'],
                        'deli_stop_flg' 	=>	$valueArray['m_order_tag'][$searchIndex]['deli_stop_flg']
                    ];
                }
                $valueArray['register_order_tag'] = $tagList;
            }

            //受注指定時は該当サイトのURLを設定
            $valueArray['m_ecs_info']['m_ec_url'] = '';
            if (!empty($this->oldOrder['m_ecs_id']) && !empty($valueArray['m_ec_urls'][$this->oldOrder['m_ecs_id']])) {
                $valueArray['m_ecs_info']['m_ec_url'] = $valueArray['m_ec_urls'][$this->oldOrder['m_ecs_id']];
            }
        }

        $this->setCurrentApiKey('');

        return $valueArray;
    }

    /**
     * 検索時に検索データと別にパラメータを追加する
     */
    protected function addSearchParameterExtend($reqSearchData)
    {
        //顧客検索時は詳細取得区分を追加
        if ($this->currentApiKey == 'searchCustomer') {
            $reqSearchData['display_csv_flag']	=	0;
            $reqSearchData['list_detail_flg']	=	1;
        }
        if ($this->currentApiKey	==	'searchSalesVolInfo'		||
            $this->currentApiKey	==	'searchDeliveryTimeHope'	||
            $this->currentApiKey	==	'searchEcPage'
        ) {
            $reqSearchData['feature_id']		=	$this->featureId;
            $reqSearchData['display_csv_flag']	=	0;
            $reqSearchData['operator_id']		=	$this->getOperatorId();
            $reqSearchData['account_cd']		=	$this->getAccountCode();
        }

        return $reqSearchData;
    }
    /**
     * 受注タグ追加
     * @param array $editRow
     */
    public function addOrderTag($editRow)
    {
        //API設定
        $this->setCurrentApiKey('registerOrderTag');
        $requestData = [
            'process_type'		=>	'0',
            't_order_hdr_id'	=>	$editRow['t_order_hdr_id'],
            'm_order_tag_id'	=>	$editRow['m_order_tag_id'],
            'auto_self_flg'		=>	'1',
            'operator_id'		=>	$this->getOperatorId()
        ];
        $apiResult = $this->register($requestData);
        $resultData = null;
        $errMsgs = null;
        if($apiResult['response']['result']['status'] > 0) {
            $errMsgs = [];
            foreach($apiResult['response']['result']['error']['message'] as $msg) {
                if (is_array($msg)) {
                    foreach($msg as $msgIndex => $messageDetail) {
                        $errMsgs[] = $messageDetail;
                    }
                } else {
                    $errMsgs[] = $msg;
                }
            }

        }
        if (!is_null($errMsgs)) {
            $resultData['m_order_tag_id'] = $errMsgs;
        } else {
            $this->oldOrder = $this->getOrderData($editRow[$this->getPkeyName()]);
        }
        return $resultData;

    }

    /**
     * 受注タグ削除
     * @param array $editRow
     */
    public function deleteOrderTag($editRow)
    {
        //API設定
        $this->setCurrentApiKey('registerOrderTag');
        $count = 0;
        $errMsgs = null;
        if (isset($editRow['chk_order_tag'])) {
            foreach($editRow['chk_order_tag'] as $data) {
                $count++;
                $keyData = explode('_', $data);
                $requestData = [
                    'process_type'			=>	'1',
                    't_order_tag_id'		=>	$keyData[0],
                    't_order_hdr_id'		=>	$editRow['t_order_hdr_id'],
                    'm_order_tag_id'		=>	$keyData[1],
                    'auto_self_flg'			=>	'1',
                    'cancel_operator_id'	=>	$this->getOperatorId(),
                    'cancel_timestamp'		=>	Carbon::now()->format('Y-m-d H:i:s.u'),
                    'operator_id'			=>	$this->getOperatorId()
                ];
                $apiResult = $this->register($requestData);
                $resultData = null;
                if($apiResult['response']['result']['status'] > 0) {
                    $errMsgs = [];
                    foreach($apiResult['response']['result']['error']['message'] as $msg) {
                        if (is_array($msg)) {
                            foreach($msg as $msgIndex => $messageDetail) {
                                $errMsgs[] = $messageDetail;
                            }
                        } else {
                            $errMsgs[] = $msg;
                        }
                    }
                }
            }
        }
        if ($count === 0) {
            $errMsgs = [];
            $errMsgs[] = $this->errorMessages['DELETE_TAG_NOT_SELECT'];
        }
        if (!is_null($errMsgs)) {
            $resultData['chk_order_tag'] = $errMsgs;
        } else {
            $this->oldOrder = $this->getOrderData($editRow[$this->getPkeyName()]);
        }
        return $resultData;

    }

    /**
     * 送付先削除チェック
     * @param array $editRow
     * @param array $destIndex
     * @return array
     */
    public function checkDelDestData($editRow, $destIndex)
    {
        $checkResult = [];
        $checkResult['response']['result']['status'] = 0;
        //配送先が１箇所のみはエラー
        if (isset($editRow['register_destination']) && count($editRow['register_destination']) <= 1) {
            $checkResult['response']['result']['status'] = 1;
            $checkResult['response']['result']['error']['message'] = ['submit_deldest.' . $destIndex	=>	[$this->errorMessages['DELETE_DESTINAION_ERROR']]];
        } elseif (isset($editRow['register_destination'][$destIndex]['register_detail'])) {
            //明細が存在する場合はエラー
            foreach($editRow['register_destination'][$destIndex]['register_detail'] as $orderDtl) {
                if (isset($orderDtl['sell_cd']) && strlen($orderDtl['sell_cd']) > 0) {
                    $checkResult['response']['result']['status'] = 1;
                    $checkResult['response']['result']['error']['message'] = ['submit_deldest.' . $destIndex	=>	[$this->errorMessages['DELETE_DESTINAION_ERROR_DTL_EXISTS']]];
                    break;
                }
            }
        }
        return $checkResult;
    }

    /**
     * 配送先情報追加
     * @param array $editRow
     * @return array 配送先情報
     */
    public function addNewOrderDestination($editRow)
    {

        //マスタデータ取得
        $pKeyName = $this->getPkeyName();
        $pKey = empty($editRow[$pKeyName]) ? null : $editRow[$pKeyName];
        $this->masterDatas = $this->getMasterDatas($pKey);
        //基本送料の取得
        if (isset($editRow['base_delivery_fee']) && is_numeric($editRow['base_delivery_fee'])) {
            $deliveryFee = $editRow['base_delivery_fee'];
        } else {
            $deliveryFee = $this->masterDatas['m_shops']['base_delivery_fee'];
        }
        //初期選択配送業者
        $initTransporter = '';
        if (isset($this->masterDatas['m_delivery_types']) &&
            is_array($this->masterDatas['m_delivery_types']) &&
            count($this->masterDatas['m_delivery_types']) > 1
        ) {
            $initTransporter = key(array_slice($this->masterDatas['m_delivery_types'], 1, 1, true));
        }
        //配送先連番の採番
        $destSeq = $this->makeNewOrderDestinationSeq($editRow);
        //配送先追加
        if (!isset($editRow['register_destination'])) {
            $newDest = [];
        } else {
            $newDest = $editRow['register_destination'];
        }
        $newDest[] = [
            't_order_destination_id'		=>	'',
            'order_destination_seq'			=>	$destSeq,
            'destination_tel'				=>	'',
            'destination_postal'			=>	'',
            'destination_address1'			=>	'',
            'destination_address2'			=>	'',
            'destination_address3'			=>	'',
            'destination_address4'			=>	'',
            'destination_company_name'		=>	'',
            'destination_division_name'		=>	'',
            'destination_name_kana'			=>	'',
            'destination_name'				=>	'',
            'deli_hope_date'				=>	'',
            'deli_hope_time_name'			=>	'',
            'delivery_time_hope_cd'			=>	'',
            'm_delivery_type_id'			=>	$initTransporter,
            'delivery_name'					=>	'',
            'shipping_fee'					=>	$deliveryFee,
            'payment_fee'					=>	0,
            'wrapping_fee'					=>	0,
            'deli_plan_date'				=>	'',
            'gift_message'					=>	'',
            'gift_wrapping'					=>	'',
            'nosi_type'						=>	'',
            'nosi_name'						=>	'',
            'invoice_comment'				=>	'',
            'picking_comment'				=>	'',
            'partial_deli_flg'				=>	'',
            'destination_tab_display_name'	=>	$this::DESTINATION_NAME_DEFAULT . $destSeq,
            'register_detail'				=>	[]
        ];

        $editRow['register_destination'] = $newDest;
        $editRow['active_destination_index'] = count($newDest) - 1;

        //合計金額再計算
        $editRow = $this->recalcTotal($editRow);

        return $editRow;
    }

    /**
     * 受注明細情報追加
     * @param array $editRow
     * @param int $配送先インデックス
     * @return array 編集情報
     */
    public function addNewOrderDetail($editRow, $destIndex)
    {
        $dtlSeq = $this->makeNewOrderDtlSeq($editRow);
        $dtlRow = $this->makeNewOrderDtlRow($dtlSeq);
        if (isset($editRow['register_destination'][$destIndex])) {
            $targetDest = $editRow['register_destination'][$destIndex];
            $targetDest['register_detail'][] = $dtlRow;
            $editRow['register_destination'][$destIndex] = $targetDest;
        }
        $editRow['active_destination_index'] = $destIndex;
        return $editRow;
    }

    /**
     * 配送先をコピー
     * @param array $editRow
     * @param number $srcIndex
     * @return array 画面情報
     */
    public function copyNewOrderDestination($editRow, $srcIndex)
    {
        //配送先連番の採番
        $destSeq = $this->makeNewOrderDestinationSeq($editRow);
        //送付先追加
        if (!isset($editRow['register_destination'])) {
            $newDest = [];
        } else {
            $newDest = $editRow['register_destination'];
            $destIndex = -1;
            foreach ($editRow['register_destination'] as $destRow) {
                $destIndex++;
                if ($srcIndex == $destIndex) {
                    $srcDest = $destRow;
                    break;
                }
            }
        }
        if (isset($srcDest)) {
            $newDest[] = [
                't_order_destination_id'		=>	'',
                'order_destination_seq'			=>	$destSeq,
                'destination_tel'				=>	$srcDest['destination_tel'],
                'destination_postal'			=>	$srcDest['destination_postal'],
                'destination_address1'			=>	$srcDest['destination_address1'],
                'destination_address2'			=>	$srcDest['destination_address2'],
                'destination_address3'			=>	$srcDest['destination_address3'],
                'destination_address4'			=>	$srcDest['destination_address4'],
                'destination_company_name'		=>	$srcDest['destination_company_name'],
                'destination_division_name'		=>	$srcDest['destination_division_name'],
                'destination_name_kana'			=>	$srcDest['destination_name_kana'],
                'destination_name'				=>	$srcDest['destination_name'],
                'deli_hope_date'				=>	$srcDest['deli_hope_date'],
                'deli_hope_time_name'			=>	(isset($srcDest['deli_hope_time_name']) ? $srcDest['deli_hope_time_name'] : '') ,
//				'delivery_time_hope_cd'			=>	$srcDest['delivery_time_hope_cd'],
                'm_delivery_type_id'			=>	$srcDest['m_delivery_type_id'],
                'm_delivery_time_hope_id'       =>  $srcDest['m_delivery_time_hope_id'],
                'delivery_name'					=>	(isset($srcDest['delivery_name']) ? $srcDest['delivery_name'] : ''),
                'shipping_fee'					=>	$srcDest['shipping_fee'],
                'payment_fee'					=>	$srcDest['payment_fee'],
                'wrapping_fee'					=>	$srcDest['wrapping_fee'],
                'deli_plan_date'				=>	$srcDest['deli_plan_date'],
                'gift_message'					=>	$srcDest['gift_message'],
                'gift_wrapping'					=>	$srcDest['gift_wrapping'],
                'nosi_type'						=>	$srcDest['nosi_type'],
                'nosi_name'						=>	$srcDest['nosi_name'],
                'invoice_comment'				=>	$srcDest['invoice_comment'],
                'picking_comment'				=>	$srcDest['picking_comment'],
                'partial_deli_flg'				=>	(isset($srcDest['partial_deli_flg']) ? $srcDest['partial_deli_flg'] : ''),
                'destination_tab_display_name'	=>	$this::DESTINATION_NAME_DEFAULT . $destSeq,
                'register_detail'				=>  array_map(
                    function ($dtl) {
                        unset($dtl['t_order_dtl_id']);
                        unset($dtl['order_dtl_seq']);
                        $skuData = json_decode($dtl['sku_data'], true);
                        foreach($skuData['sku_dtl'] as &$skuDtl) {
                            $skuDtl['t_order_dtl_sku_id'] = '';
                        }
                        $dtl['sku_data'] = json_encode($skuData);
                        $dtl['sell_checked'] = '1';
                        return $dtl;
                    },
                    array_filter($srcDest['register_detail'], function ($dtl) {return isset($dtl['check_copy']);})
                ),
            ];
            //order_dtl_seqが抜けた行に対し、最大以降の連番を振り直す
            $idx = $this->makeNewOrderDtlSeq($editRow);
            foreach ($newDest as &$dest) {
                foreach($dest['register_detail'] as &$dtl) {
                    if(!isset($dtl['order_dtl_seq'])) {
                        $dtl['order_dtl_seq'] = $idx;
                        $idx++;
                    }
                }
            }
        }
        $editRow['register_destination'] = $newDest;
        $editRow['active_destination_index'] = count($newDest) - 1;
        //合計金額再計算
        $editRow = $this->recalcTotal($editRow);
        return $editRow;
    }

    /**
     * 送付先を削除
     * @param array $editRow
     * @param number $srcIndex
     * @return array 画面情報
     */
    public function delOrderDestination($editRow, $srcIndex)
    {
        //送付先削除
        $newDest = [];
        if (isset($editRow['register_destination'])) {
            $destIndex = -1;
            foreach($editRow['register_destination'] as $destRow) {
                $destIndex++;
                if ($srcIndex != $destIndex) {
                    $newDest[] = $destRow;
                }
            }
        }
        $editRow['active_destination_index'] = 0;
        $editRow['register_destination'] = $newDest;

        //合計金額再計算
        $editRow = $this->recalcTotal($editRow);

        return $editRow;
    }

    /**
     * 受注明細番号の採番
     * @param array $editRow
     * @return int 受注明細番号
     */
    protected function makeNewOrderDtlSeq($editRow)
    {
        $dtlSeq = 0;
        if (isset($editRow['register_destination'])) {
            $arraySeqKeys = array_column($editRow['register_destination'], 'order_destination_seq');
            $arraySeqKeys = array_filter($arraySeqKeys);
            foreach ($editRow['register_destination'] as $destRow) {
                //受注明細
                if (!isset($destRow['register_detail'])) {
                    continue;
                }
                $dtlSeqArray = array_column($destRow['register_detail'], 'order_dtl_seq');
                $dtlSeqArray = array_filter($dtlSeqArray);
                if (is_array($dtlSeqArray) && count($dtlSeqArray) > 0) {
                    if ($dtlSeq < max($dtlSeqArray)) {
                        $dtlSeq = max($dtlSeqArray);
                    }
                }
            }
        }
        return ++$dtlSeq;
    }

    /**
     * 新規受注明細行の取得
     * @param int $受注明細番号
     * @return array 受注明細レコード
     */
    protected function makeNewOrderDtlRow($dtlSeq)
    {
        return [
            't_order_dtl_id'			=>	'',
            'order_dtl_seq'				=>	$dtlSeq,
            'sell_cd'					=>	'',
            'sell_name'					=>	'',
            'order_dtl_coupon_id'		=>	'',
            'order_dtl_coupon_price'	=>	'',
            'order_sell_price'			=>	'',
            'order_sell_vol'			=>	'1',
            'cancel_timestamp'			=>	'',
            'order_sell_amount'			=>	'0',
            'cancel_flg'				=>	'',
            'reservation_date'			=>	'',
            'drawing_status_name'		=>	'',
            'sku_data'					=>	null
        ];
    }

    /**
     * 配送先番号の採番
     * @param array $editRow
     * @return int 配送先番号
     */
    protected function makeNewOrderDestinationSeq($editRow)
    {
        $destSeq = 0;
        if (isset($editRow['register_destination'])) {
            $arraySeqKeys = array_column($editRow['register_destination'], 'order_destination_seq');
            $arraySeqKeys = array_filter($arraySeqKeys);
            $destSeq = max($arraySeqKeys);
        }
        return ++$destSeq;
    }

    /**
     * データをID/VALUEのみの配列に変換する（ドロップダウンリスト設定用）
     * @param array $dataRows
     * @param array $changeList
     * @param bool $dataShift
     * @param bool $addEmptyRecord
     * @return array
     */
    protected function changeIdValuesList($dataRows, $changeList, $dataShift = false, $addEmptyRecord = false)
    {
        $returnArray = [];
        if ($addEmptyRecord) {
            $returnArray[''] = '';
        }
        if ($dataShift) {
            $dataRows = array_shift($dataRows);
        }
        foreach($dataRows as $row) {
            $returnArray[$row[$changeList['id']]] = $row[$changeList['value']];
        }
        return $returnArray;
    }

    /**
     * API登録データ整形
     * @param array $editRow
     */
    public function formatRegisterData($editRow)
    {
        $pKeyName = $this->getPkeyName();
        $pKey = empty($editRow[$pKeyName]) ? null : $editRow[$pKeyName];
        $this->masterDatas = $this->getMasterDatas($pKey);

        // 画面からの受注なので、ギフトチェックを行う
        $editRow['gift_check_flg'] = 1;

        // 画面からの受注のチェックを考慮する
        $editRow['app_register_flg'] = 1;

        //注文主郵便番号(8桁)の場合はハイフンを除く
        $editRow['order_postal'] = empty($editRow['order_postal']) ? '' : $this->toDbPostalCode($editRow['order_postal']);
        //受注日時
        $editRow['order_datetime'] = empty($editRow['order_datetime']) ? '' : $this->toDbDatetime($editRow['order_datetime'], 'Y/m/d H:i:s');
        // 注文者番地
        if(!isset($editRow['order_address3'])) {
            $editRow['order_address3'] = '';
        }
        // 注文者建物
        if(!isset($editRow['order_address4'])) {
            $editRow['order_address4'] = '';
        }
        // ギフトフラグ
        if(!isset($editRow['gift_flg'])) {
            $editRow['gift_flg'] = 0;
        }

        $destRows = [];
        $sumSellTotal = 0;
        $sellTotalPrice = 0;
        $shippingFee = 0;
        $paymentFee = 0;
        $packageFee = 0;
        foreach($editRow['register_destination'] as $destRow) {
            //配送先郵便番号(8桁)の場合はハイフンを除く
            $destRow['destination_postal'] = empty($destRow['destination_postal']) ? '' : $this->toDbPostalCode($destRow['destination_postal']);
            //配送方法
            if (isset($destRow['m_delivery_type_id']) && isset($this->masterDatas['m_delivery_types'][$destRow['m_delivery_type_id']])) {
                $destRow['delivery_name'] = $this->masterDatas['m_delivery_types'][$destRow['m_delivery_type_id']];
            }

            //配送希望日
            $destRow['deli_hope_date'] = empty($destRow['deli_hope_date']) ? '' : $this->toDbDatetime($destRow['deli_hope_date']) ;
            //配送希望時間帯
            $destRow['deli_hope_time_name'] = '';
            if(!empty($destRow['m_delivery_time_hope_id']) && !empty($destRow['m_delivery_type_id'])) {
                $deliveryRow = collect($this->masterDatas['m_delivery_type_list'])->filter(function ($value) use ($destRow) {
                    return $value['m_delivery_types_id'] == $destRow['m_delivery_type_id'];
                })->first();

                $deliveryHopeList = collect($this->masterDatas['m_delivery_time_hope'])->filter(function ($value) use ($destRow, $deliveryRow) {
                    return $value['m_delivery_time_hope_id'] == $destRow['m_delivery_time_hope_id']
                        && $value['delivery_company_cd'] == $deliveryRow['delivery_type'];
                })->first();

                $destRow['deli_hope_time_name'] = $deliveryHopeList['delivery_company_time_hope_name'];
            }
            //出荷予定日
            if(!empty($destRow['deli_plan_date'])) {
                $destRow['deli_plan_date'] = $this->toDbDatetime($destRow['deli_plan_date']);
            }
            //送料
            if (!empty($destRow['shipping_fee'])) {
                $destRow['shipping_fee'] = $this->toDbNumberValue($destRow['shipping_fee']);
                $shippingFee += (int)$destRow['shipping_fee'];
            }
            //手数料
            if (!empty($destRow['payment_fee'])) {
                $destRow['payment_fee'] = $this->toDbNumberValue($destRow['payment_fee']);
                $paymentFee += (int)$destRow['payment_fee'];
            }
            //包装料
            if (!empty($destRow['wrapping_fee'])) {
                $destRow['wrapping_fee'] = $this->toDbNumberValue($destRow['wrapping_fee']);
                $packageFee += (int)$destRow['wrapping_fee'];
            }
            //配送先変更フラグ
            $destRow['destination_alter_flg'] = null;
            //名前、または、郵便番号、住所が異なる場合は配送先が異なる
            if (($editRow['order_name'] != $destRow['destination_name']) ||
                ($editRow['order_postal'] != $destRow['destination_postal']) ||
                ($editRow['order_address1'] . $editRow['order_address2'] . $editRow['order_address3'] . $editRow['order_address4']
                    !=
                    $destRow['destination_address1'] . $destRow['destination_address2'] . $destRow['destination_address3'] . $destRow['destination_address4'])
            ) {
                $destRow['destination_alter_flg'] = '1';
            }

            $sumSellTotal = 0;
            $dtlRows = [];
            $timestamp = Carbon::now()->format('Y-m-d H:i:s.u');
            if (!isset($destRow['register_detail'])) {
                $destRow['register_detail'] = [];
            }
            foreach($destRow['register_detail'] as $dtlRow) {
                // 販売コード未設定行はスキップ
                if (empty($dtlRow['sell_cd'])) {
                    continue;
                }
                //販売単価
                $dtlRow['order_sell_price'] = $this->toDbNumberValue($dtlRow['order_sell_price']);
                //数量
                $dtlRow['order_sell_vol'] = $this->toDbNumberValue($dtlRow['order_sell_vol']);
                //クーポン金額情報(カンマがあったらカンマを除く)  ← 画面では表示のみのため更新データには不要
                $dtlRow['order_dtl_coupon_price'] = $this->toDbNumberValue($dtlRow['order_dtl_coupon_price']);
                //明細金額
                if ((isset($dtlRow['cancel_timestamp']) && strlen($dtlRow['cancel_timestamp']) > 0) ||
                    (isset($dtlRow['cancel_flg']) && $dtlRow['cancel_flg'] == '1')
                ) {
                    $dtlRow['order_sell_amount'] = '';
                    if (isset($dtlRow['cancel_flg']) && $dtlRow['cancel_flg'] == '1') {
                        $dtlRow['cancel_timestamp'] = $timestamp;
                    } else {
                        $dtlRow['cancel_timestamp'] = $this->toDbDatetime($dtlRow['cancel_timestamp']);
                    }
                } else {
                    if (is_numeric($dtlRow['order_sell_price']) && is_numeric($dtlRow['order_sell_vol'])) {
                        $dtlRow['order_sell_amount'] = (int)$dtlRow['order_sell_price'] * (int)$dtlRow['order_sell_vol'];
                        $sumSellTotal += $dtlRow['order_sell_amount'];
                    } else {
                        $dtlRow['order_sell_amount'] = '0';
                    }
                }
                $dtlRow['sell_option'] = '';
                $skuRow = [];
                $skuData = json_decode($dtlRow['sku_data'], true);
                if (isset($skuData['sku_dtl']) && is_array($skuData['sku_dtl']) && count($skuData['sku_dtl']) > 0) {
                    //項目選択肢
                    if (isset($skuData['sell_option'])) {
                        $dtlRow['sell_option'] = $skuData['sell_option'];
                    }
                    //SKU情報
                    foreach($skuData['sku_dtl'] as $skuDtl) {
                        $itemVol = $skuDtl['compose_vol'];
                        if (is_numeric($skuDtl['compose_vol']) && is_numeric($dtlRow['order_sell_vol'])) {
                            $itemVol *= $dtlRow['order_sell_vol'];
                        }
                        $skuRow[] = [
                            't_order_dtl_sku_id'	=>	$skuDtl['t_order_dtl_sku_id'],
                            'item_cd'				=>	$skuDtl['item_cd'],
                            'item_vol'				=>	$itemVol
                        ];
                    }
                }
                $dtlRow['register_detail_sku'] = $skuRow;

                $dtlRows[] = $dtlRow;
            }
            $destRow['register_detail'] = $dtlRows;
            $destRow['sum_sell_total'] = $sumSellTotal;
            $sellTotalPrice += $sumSellTotal;

            $destRows[] = $destRow;
        }
        $editRow['register_destination'] = $destRows;

        //商品合計計
        $editRow['sell_total_price'] = $sellTotalPrice;
        //消費税額
        $editRow['tax_price'] = empty($editRow['tax_price']) ? 0 : (int)$editRow['tax_price'];
        //送料
        $editRow['shipping_fee'] = $shippingFee;
        //手数料
        $editRow['payment_fee'] = $paymentFee;
        //包装料
        $editRow['package_fee'] = $packageFee;
        //合計金額
        $editRow['total_price'] = $sellTotalPrice + $editRow['tax_price'] + $shippingFee + $paymentFee + $packageFee;
        //割引金額
        $editRow['discount'] = (empty($editRow['discount'])) ? 0 : $this->toDbNumberValue($editRow['discount']);
        //ストアクーポン
        $totakUseCoupon = 0;
        $editRow['use_coupon_store'] = (empty($editRow['use_coupon_store'])) ? 0 : $this->toDbNumberValue($editRow['use_coupon_store']);
        $totakUseCoupon += (int)$editRow['use_coupon_store'];
        //モールクーポン
        $editRow['use_coupon_mall'] = (empty($editRow['use_coupon_mall'])) ? 0 : $this->toDbNumberValue($editRow['use_coupon_mall']);
        $totakUseCoupon += (int)$editRow['use_coupon_mall'];
        //クーポン合計
        $editRow['total_use_coupon'] = $totakUseCoupon;
        //利用ポイント
        $editRow['use_point'] = (empty($editRow['use_point'])) ? 0 : $this->toDbNumberValue($editRow['use_point']);
        //請求金額
        $editRow['order_total_price'] = $editRow['total_price'] - ((int)$editRow['discount'] + $totakUseCoupon + (int)$editRow['use_point']);

        //支払方法
        if (isset($editRow['m_pay_type_id']) && isset($this->masterDatas['m_payment_types'][$editRow['m_pay_type_id']])) {
            $editRow['pay_type_name'] = $this->masterDatas['m_payment_types'][$editRow['m_pay_type_id']];
        }

        if(!isset($editRow['order_corporate_name'])) {
            $editRow['order_corporate_name'] = " ";
        }
        if(!isset($editRow['order_division_name'])) {
            $editRow['order_division_name'] = " ";
        }

        //担当者コード
        if (!isset($editRow['operator_id'])) {
            $editRow['operator_id'] = $this->getOperatorId();
        }
        $this->setCurrentApiKey('');

        return $editRow;
    }

    /**
     * 検索用データを加工する
     */
    protected function editRegisterData($data)
    {
        // 未設定なら空文字で渡す項目
        $emptyColumns = [
            'order_comment',			// 備考
            'operator_comment',			// 社内メモ
            'order_address3',			// 注文者番地
            'order_address4',			// 注文者建物
        ];

        foreach($emptyColumns as $cName) {
            if(!isset($data[$cName])) {
                $data[$cName] = '';
            }
        }

        return $data;
    }
    /**
     * 画面表示用データ編集
     * @param array $editRow
     * @param array $viewExtendData
     * @param bool $custReplaceFlag
     * @return array
     */
    public function editDisplayData($editRow, $viewExtendData, $custReplaceFlag = false, $returnFlag = false)
    {
        //顧客情報上書きフラグがONの場合、顧客検索情報で注文主情報を書き換える
        if ($custReplaceFlag && isset($viewExtendData['m_cust']) &&
            is_array($viewExtendData['m_cust']) && count($viewExtendData['m_cust']) > 0) {
            $editRow['order_tel1'] = $viewExtendData['m_cust']['tel1'];
            $editRow['order_tel2'] = $viewExtendData['m_cust']['tel2'];
            $editRow['order_fax'] = $viewExtendData['m_cust']['fax'];
            $editRow['order_name_kana'] = $viewExtendData['m_cust']['name_kana'];
            $editRow['order_name'] = $viewExtendData['m_cust']['name_kanji'];
            $editRow['order_email1'] = $viewExtendData['m_cust']['email1'];
            $editRow['order_email2'] = $viewExtendData['m_cust']['email2'];
            $editRow['order_postal'] = $viewExtendData['m_cust']['postal'];
            $editRow['order_address1'] = $viewExtendData['m_cust']['address1'];
            $editRow['order_address2'] = $viewExtendData['m_cust']['address2'];
            $editRow['order_address3'] = $viewExtendData['m_cust']['address3'];
            $editRow['order_address4'] = $viewExtendData['m_cust']['address4'];
            $editRow['order_corporate_name'] = $viewExtendData['m_cust']['corporate_kanji'];
            $editRow['order_division_name'] = $viewExtendData['m_cust']['division_name'];
        }

        //DB受注情報⇒画面情報へ変換
        //支払い方法
        if (!empty($editRow['m_payment_types_id'])) {
            $editRow['m_pay_type_id'] = $editRow['m_payment_types_id'];
            unset($editRow['m_payment_types_id']);
        }
        //DB受注情報⇒画面情報へ変換
        //配送先情報
        if (isset($editRow['order_destination'])) {
            $destRows = [];
            foreach($editRow['order_destination'] as $destRow) {
                //時間帯
                $destRow['delivery_time_hope_cd'] = $destRow['deli_hope_time_cd'];


                // 配送方法の種類を取得しておく
                if(!empty($destRow['m_delivery_type_id'])) {
                    $deliveryTypeRow = collect($this->masterDatas['m_delivery_type_list'])->filter(function ($value) use ($destRow) {
                        return $value['m_delivery_types_id'] == $destRow['m_delivery_type_id'];
                    })->first();
                    $destRow['delivery_type'] = $deliveryTypeRow['delivery_type'];
                }

                //受注明細
                $dtlRows = [];
                if (isset($destRow['order_dtl'])) {
                    foreach($destRow['order_dtl'] as $dtlRow) {
                        if (!isset($dtlRow['cancel_flg'])) {
                            $dtlRow['cancel_flg'] = '';
                        }
                        //明細取消日時が初期値の場合は画面には''を送信する
                        if($dtlRow['cancel_timestamp'] == 0) {
                            $dtlRow['cancel_timestamp'] = '';
                        }
                        //返品の場合、引き当て日をセットしない
                        if($returnFlag) {
                            unset($dtlRow['reservation_date']);
                        }

                        //SKU退避情報
                        $skuData = [];
                        $skuData['ecs_id'] = $dtlRow['ecs_id'];
                        $skuData['sell_id'] = $dtlRow['sell_id'];
                        $skuData['sell_cd'] = $dtlRow['sell_cd'];
                        $skuData['sell_type'] = (count($dtlRow['order_dtl_sku']) == 1) ? $this->sellType['ITEM'] : $this->sellType['SET'];

                        $skuData['sell_option'] = '';
                        if (!empty($dtlRow['sell_option'])) {
                            $skuData['sell_option'] = $dtlRow['sell_option'];
                            $skuData['sell_type'] = $this->sellType['SKU'];
                        }
                        $skuDtl = [];
                        foreach($dtlRow['order_dtl_sku'] as $skuRow) {
                            //返品の場合、idをセットしない
                            if($returnFlag) {
                                $skuRow['t_order_dtl_sku_id'] = '';
                            }
                            $skuDtl[] = [
                                't_order_dtl_sku_id'	=>	$skuRow['t_order_dtl_sku_id'],
                                'item_id'				=>	$skuRow['item_id'],
                                'item_cd'				=>	$skuRow['item_cd'],
                                'compose_vol'			=>	($skuRow['item_vol'] / $skuRow['order_sell_vol'])
                            ];
                        }
                        $skuData['sku_dtl'] = $skuDtl;
                        //SKUデータ
                        $dtlRow['sku_data'] = json_encode($skuData);
                        $dtlRows[] = $dtlRow;
                    }
                }
                $destRow['register_detail'] = $dtlRows;
                //送付先タブ名称
                $destRow['destination_tab_display_name'] = $destRow['destination_name'];
                $destRows[] = $destRow;
            }
            unset($editRow['order_destination']);
            $editRow['register_destination'] = $destRows;
        }
        // 確認画面は空白行は追加しない
        $notifyDisplay = $editRow['notifyDisplay'] ?? '';
        if (empty($notifyDisplay)) {
            // 明細行を追加
            $destIndex = 0;
            foreach($editRow['register_destination'] as $destRow) {
                $orderDtl = array_last($destRow['register_detail']);
                if (!empty($orderDtl) && empty($orderDtl['t_order_dtl_id']) && empty($orderDtl['sell_checked'])) {
                    continue;
                }
                $editRow = $this->addNewOrderDetail($editRow, $destIndex);
                $destIndex++;
            }
        }

        //画面項目の編集

        //注文日時
        $editRow['order_datetime'] = $this->toDisplayDatetime($editRow['order_datetime'], 'Y/m/d H:i');
        //注文主郵便番号(7桁)の場合はハイフンセット
        if (!empty($editRow['order_postal'])) {
            $editRow['order_postal'] = $this->toDisplayPostalCode($editRow['order_postal']);
        }
        //返品の場合
        if($returnFlag) {
            //注文日時リセット
            $editRow['order_datetime'] = $this->toDisplayDatetime((new \Datetime())->format('Y-m-d H:i'), 'Y/m/d H:i');
            //EC受注番号を社内メモへ
            $editRow['operator_comment'] = '元受注ID：'.$editRow['t_order_hdr_id']."\n".'元ECサイト注文ID：'.$editRow['ec_order_num'];
            $editRow['ec_order_num'] = '';
        }

        $editRow['paytype_readonly'] = '';
        if(isset($editRow['progress_type']) && $editRow['progress_type'] >= 30) {
            $editRow['paytype_readonly'] = 'readonly';
        }
        //返品の場合、支払方法入力可能
        if($returnFlag) {
            $editRow['paytype_readonly'] = '';
        }

        $destRows = [];
        foreach($editRow['register_destination'] as $destRow) {
            //配送先郵便番号(7桁)の場合はハイフンセット
            $destRow['destination_postal'] = $this->toDisplayPostalCode($destRow['destination_postal']);

            //配送希望日
            $destRow['deli_hope_date'] = $this->toDisplayDatetime($destRow['deli_hope_date']);
            //出荷予定日
            $destRow['deli_plan_date'] = $this->toDisplayDatetime($destRow['deli_plan_date']);

            //返品の場合、配送希望日、出荷予定日は空にする
            if($returnFlag) {
                unset($destRow['deli_hope_date']);
                unset($destRow['delivery_time_hope_cd']);
                unset($destRow['deli_plan_date']);
            }
            // 配送方法の種類を取得しておく
            if(!empty($destRow['m_delivery_type_id'])) {
                $deliveryTypeRow = collect($this->masterDatas['m_delivery_type_list'])->filter(function ($value) use ($destRow) {
                    return $value['m_delivery_types_id'] == $destRow['m_delivery_type_id'];
                })->first();
                $destRow['delivery_type'] = $deliveryTypeRow['delivery_type'];
            }
            //小計
            $sumSellTotal = 0;
            if (isset($destRow['register_detail'])) {
                //受注明細
                $dtlRows = [];
                foreach($destRow['register_detail'] as $dtlRow) {
                    //明細削除フラグ（画面[削除]ボタン押下でON）がない場合は追加
                    //返品の場合、数量*-1
                    if($returnFlag) {
                        $dtlRow['order_sell_vol'] = $dtlRow['order_sell_vol'] * -1;
                        // 出荷IDも削除しておく
                        unset($dtlRow['t_deli_hdr_id']);
                        //明細クーポン金額
                        if (!empty($dtlRow['order_dtl_coupon_price'])) {
                            $dtlRow['order_dtl_coupon_price'] = $this->toDisplayNumberValue($dtlRow['order_dtl_coupon_price'] * -1);
                        }
                    } else {
                        //明細クーポン金額
                        if (!empty($dtlRow['order_dtl_coupon_price'])) {
                            $dtlRow['order_dtl_coupon_price'] = $this->toDisplayNumberValue($dtlRow['order_dtl_coupon_price']);
                        }
                    }
                    //販売金額
                    if($dtlRow['cancel_timestamp'] > 0 || $dtlRow['cancel_flg'] == '1') {
                        $dtlRow['order_sell_amount'] = '';
                    } else {
                        if (!empty($dtlRow['order_sell_price']) && !empty($dtlRow['order_sell_vol'])) {
                            $orderSellTotal = (int)$dtlRow['order_sell_price'] * (int)$dtlRow['order_sell_vol'];
                            $dtlRow['order_sell_amount'] = $this->toDisplayNumberValue($orderSellTotal);
                            $sumSellTotal += $orderSellTotal;
                        }
                    }
                    //販売単価
                    $dtlRow['order_sell_price'] = $this->toDisplayNumberValue($dtlRow['order_sell_price']);
                    //数量
                    $dtlRow['order_sell_vol'] = $this->toDisplayNumberValue($dtlRow['order_sell_vol']);
                    //[削除]ラベル
                    $dtlRow['cancel_string'] = '';
                    if(isset($dtlRow['cancel_flg']) && $dtlRow['cancel_flg'] == '1') {
                        unset($dtlRow['cancel_timestamp']);
                        $dtlRow['cancel_string'] = '削除';
                    } else {
                        if(isset($dtlRow['cancel_timestamp']) && $dtlRow['cancel_timestamp'] > 0) {
                            $dtlRow['cancel_string'] = '削除済';
                        }
                    }
                    //[削除]ボタン表示
                    $dtlRow['btn_delete_visible'] = '';
                    if (empty($dtlRow['cancel_string']) && empty($dtlRow['t_deli_hdr_id'])) {
                        $sellCheck = $dtlRow['sell_checked'] ?? '';
                        if (!empty($dtlRow['t_order_dtl_id']) || $sellCheck == '1') {
                            $dtlRow['btn_delete_visible'] = '1';
                        }
                    }
                    //引当状態
                    if (empty($dtlRow['cancel_string']) && empty($dtlRow['drawing_status_name'])) {
                        $skuData = json_decode($dtlRow['sku_data'], true);
                        $dtlRow['drawing_status_name'] = $this->getDrawingStatusName($dtlRow['t_order_dtl_id'], $dtlRow['order_sell_vol'], $skuData);
                    }

                    //表示のみ判定
                    $dtlRow['order_sell_vol_readonly'] = '';
                    $dtlRow['order_sell_price_readonly'] = '';
                    $dtlRow['sell_name_readonly'] = '';
                    if ((isset($dtlRow['cancel_timestamp']) && $dtlRow['cancel_timestamp'] > 0) ||
                        (isset($dtlRow['cancel_flg']) && $dtlRow['cancel_flg'] == '1') ||
                        !empty($dtlRow['t_deli_hdr_id'])
                    ) {
                        $dtlRow['order_sell_price_readonly'] = 'readonly';
                        $dtlRow['sell_name_readonly'] = 'readonly';
                    }
                    $dtlRow['order_sell_vol_readonly'] = $dtlRow['order_sell_price_readonly'];
                    if (empty($dtlRow['order_sell_vol_readonly'])) {
                        if (isset($dtlRow['reservation_date']) && $dtlRow['reservation_date'] > 0) {
                            $dtlRow['order_sell_vol_readonly'] = 'readonly';
                        }
                    }
                    $dtlRows[] = $dtlRow;
                }
                //小計
                $destRow['sum_sell_total'] = $this->toDisplayNumberValue($sumSellTotal);
                $destRow['register_detail'] = $dtlRows;
            }
            //返品の場合、金額*-1
            if($returnFlag) {
                //送料
                $destRow['shipping_fee'] = (isset($destRow['shipping_fee'])) ? $this->toDisplayNumberValue($destRow['shipping_fee'] * -1) : '';
                //手数料
                $destRow['payment_fee'] = (isset($destRow['payment_fee'])) ? $this->toDisplayNumberValue($destRow['payment_fee'] * -1) : '';
                //包装料
                $destRow['wrapping_fee'] = (isset($destRow['wrapping_fee'])) ? $this->toDisplayNumberValue($destRow['wrapping_fee'] * -1) : '';
            } else {
                //送料
                $destRow['shipping_fee'] = (isset($destRow['shipping_fee'])) ? $this->toDisplayNumberValue($destRow['shipping_fee']) : '';
                //手数料
                $destRow['payment_fee'] = (isset($destRow['payment_fee'])) ? $this->toDisplayNumberValue($destRow['payment_fee']) : '';
                //包装料
                $destRow['wrapping_fee'] = (isset($destRow['wrapping_fee'])) ? $this->toDisplayNumberValue($destRow['wrapping_fee']) : '';
            }

            $destRows[] = $destRow;
        }
        $editRow['register_destination'] = $destRows;
        //返品の場合、金額*-1
        if($returnFlag) {
            //商品金額計
            $editRow['sell_total_price'] = (isset($editRow['sell_total_price'])) ? $this->toDisplayNumberValue($editRow['sell_total_price'] * -1) : 0;
            //消費税
            $editRow['tax_price'] = (isset($editRow['tax_price']) && !empty($editRow['tax_price'])) ? $this->toDisplayNumberValue($editRow['tax_price'] * -1) : '';
            //請求金額
            $editRow['order_total_price'] = (isset($editRow['order_total_price'])) ? $this->toDisplayNumberValue($editRow['order_total_price'] * -1) : '0';
            //送料
            $editRow['shipping_fee'] = (isset($editRow['shipping_fee'])) ? $this->toDisplayNumberValue($editRow['shipping_fee'] * -1) : 0;
            //手数料
            $editRow['payment_fee'] = (isset($editRow['payment_fee'])) ? $this->toDisplayNumberValue($editRow['payment_fee'] * -1) : 0;
            //包装料
            $editRow['package_fee'] = (isset($editRow['package_fee'])) ? $this->toDisplayNumberValue($editRow['package_fee'] * -1) : 0;
            //割引金額
            $editRow['discount'] = (isset($editRow['discount'])) ? $this->toDisplayNumberValue($editRow['discount'] * -1) : '0';
            //ストアクーポン
            $editRow['use_coupon_store'] = (isset($editRow['use_coupon_store'])) ? $this->toDisplayNumberValue($editRow['use_coupon_store'] * -1) : '0';
            //モールクーポン
            $editRow['use_coupon_mall'] = (isset($editRow['use_coupon_mall'])) ? $this->toDisplayNumberValue($editRow['use_coupon_mall'] * -1) : '0';
            //クーポン合計
            $editRow['total_use_coupon'] = (isset($editRow['total_use_coupon'])) ? $this->toDisplayNumberValue($editRow['total_use_coupon'] * -1) : '0';
            //利用ポイント
            $editRow['use_point'] = (isset($editRow['use_point'])) ? $this->toDisplayNumberValue($editRow['use_point'] * -1) : '0';

        } else {
            //商品金額計
            $editRow['sell_total_price'] = (isset($editRow['sell_total_price'])) ? $this->toDisplayNumberValue($editRow['sell_total_price']) : 0;
            //消費税
            $editRow['tax_price'] = (isset($editRow['tax_price']) && !empty($editRow['tax_price'])) ? $this->toDisplayNumberValue($editRow['tax_price']) : '';
            //請求金額
            $editRow['order_total_price'] = (isset($editRow['order_total_price'])) ? $this->toDisplayNumberValue($editRow['order_total_price']) : '0';
            //送料
            $editRow['shipping_fee'] = (isset($editRow['shipping_fee'])) ? $this->toDisplayNumberValue($editRow['shipping_fee']) : 0;
            //手数料
            $editRow['payment_fee'] = (isset($editRow['payment_fee'])) ? $this->toDisplayNumberValue($editRow['payment_fee']) : 0;
            //包装料
            $editRow['package_fee'] = (isset($editRow['package_fee'])) ? $this->toDisplayNumberValue($editRow['package_fee']) : 0;
            //割引金額
            $editRow['discount'] = (isset($editRow['discount'])) ? $this->toDisplayNumberValue($editRow['discount']) : '0';
            //ストアクーポン
            $editRow['use_coupon_store'] = (isset($editRow['use_coupon_store'])) ? $this->toDisplayNumberValue($editRow['use_coupon_store']) : '0';
            //モールクーポン
            $editRow['use_coupon_mall'] = (isset($editRow['use_coupon_mall'])) ? $this->toDisplayNumberValue($editRow['use_coupon_mall']) : '0';
            //クーポン合計
            $editRow['total_use_coupon'] = (isset($editRow['total_use_coupon'])) ? $this->toDisplayNumberValue($editRow['total_use_coupon']) : '0';
            //利用ポイント
            $editRow['use_point'] = (isset($editRow['use_point'])) ? $this->toDisplayNumberValue($editRow['use_point']) : '0';

        }
        //合計金額
        $editRow['total_price'] = $this->toDisplayNumberValue(
            (int)$this->toDbNumberValue($editRow['sell_total_price']) +
            (int)$this->toDbNumberValue($editRow['tax_price']) +
            (int)$this->toDbNumberValue($editRow['shipping_fee']) +
            (int)$this->toDbNumberValue($editRow['payment_fee']) +
            (int)$this->toDbNumberValue($editRow['package_fee'])
        );
        if($returnFlag) {
            $editRow['progress_type']  = '100';
            // 新規モードのため、IDは削除しておく
            unset($editRow['t_order_hdr_id']);
            foreach($editRow['register_destination'] as &$destRow) {
                unset($destRow['t_order_destination_id']);
                if (isset($destRow['register_detail'])) {
                    foreach($destRow['register_detail'] as &$dtlRow) {
                        unset($dtlRow['t_order_dtl_id']);
                        if (isset($dtlRow['order_dtl_sku'])) {
                            foreach($dtlRow['order_dtl_sku'] as &$skuRow) {
                                unset($skuRow['t_order_dtl_sku_id']);
                            }
                        }
                    }
                }
            }
        }
        return $editRow;
    }

    /**
     * 引当状態取得
     * @param int $orderDtlId
     * @param int $orderSellVol
     * @param array $skuData
     * @return string
     */
    protected function getDrawingStatusName($orderDtlId, $orderSellVol, $skuData)
    {
        if (empty($skuData) || !is_array($skuData) || empty($orderSellVol) || !is_numeric($orderSellVol)) {
            return '';
        }
        //受注修正時
        if (!empty($orderDtlId)) {
            foreach($this->oldOrder['order_destination'] as $oldDestRow) {
                foreach($oldDestRow['order_dtl'] as $oldDtlRow) {
                    if ($oldDtlRow['t_order_dtl_id'] != $orderDtlId) {
                        continue;
                    }
                    $statusName = '';
                    //数量が変更前以下の場合は状態は変わらない
                    if ((int)$oldDtlRow['order_sell_vol'] >= (int)$orderSellVol) {
                        //引当済の場合
                        if (!empty($oldDtlRow['reservation_date'])) {
                            $statusName = '引当済';
                        } else {
                            $statusName = '引当前';
                            foreach($oldDtlRow['order_dtl_sku'] as $oldSkuRow) {
                                if (is_null($oldSkuRow['temp_reservation_flg'])) {
                                    $statusName = '未引当予定';
                                    break;
                                }
                            }
                        }
                        return $statusName;
                    }
                }
            }
        }
        //数量変更、または、明細追加時
        $statusName = '';
        $arrayKeys = array_column($skuData['sku_dtl'], 'item_cd');
        foreach($arrayKeys as $itemCd) {
            $this->setCurrentApiKey('searchSalesVolInfo');
            $requestData = [
                'item_cd' => $itemCd,
            ];
            $apiResults = json_decode($this->getRows($requestData), true);
            $this->setCurrentApiKey('');

            if (isset($apiResults['response']['search_result']) && count($apiResults['response']['search_result']) > 0) {
                $stockRows = $apiResults['response']['search_result'];
                $statusName = '引当予定';

                $skuCollection = collect($skuData['sku_dtl']);

                $itemRow = $skuCollection->filter(function ($value) use ($itemCd) {
                    return $value['item_cd'] = $itemCd;
                })->first();

                $orderVol = (int)$orderSellVol * (int)$itemRow['compose_vol'];

                if(!isset($stockRows[0]['enable_sell_vol'])) {
                    $statusName = '';
                    break;
                }
                if ($stockRows[0]['enable_sell_vol'] >= $orderVol) {
                    continue;
                }
                if ($stockRows[0]['enable_sell_vol'] < $orderVol && $stockRows[0]['itembuying_enable_sell_vol'] >= $orderVol) {
                    $statusName = '入荷予定';
                    continue;
                }
                if ($stockRows[0]['itembuying_enable_sell_vol'] < $orderVol) {
                    $statusName = '未引当予定';
                    break;
                }
            }
        }
        return $statusName;

    }
    /**
     * 新規登録時の初期取得データ
     * @return number[]
     */
    public function getNewData()
    {
        $valueArray = [];
        //受注担当者の初期設定
        $valueArray['order_operator_id'] = $this->getOperatorId();
        return $valueArray;
    }

    /**
     *数値編集（DB登録用）
     * @param string $postalCode
     * @return string
     */
    protected function toDbNumberValue($value)
    {
        return str_replace(',', '', $value);
    }

    /**
     *数値編集（画面表示用）
     * @param string $postalCode
     * @return string
     */
    protected function toDisplayNumberValue($value)
    {
        if (is_numeric($value)) {
            $returnValue = number_format($value);
        } else {
            $returnValue = $value;
        }
        return $returnValue;
    }

    protected function changeDateFormat($datetime, $from, $to, $format = null)
    {
        $returnCd = '';
        if (!empty($datetime)) {
            $returnCd = str_replace($from, $to, $datetime);
        }
        if (!empty($returnCd) && !empty($format)) {
            $tempDate = new Carbon($returnCd);
            if (!empty($tempDate)) {
                $returnCd = $tempDate->format($format);
            }
        }
        return $returnCd;
    }
    /**
     * 日付編集（DB登録用）
     * @param string $postalCode
     * @return string
     */
    protected function toDbDatetime($datetime, $format = null)
    {
        return $this->changeDateFormat($datetime, '/', '-', $format);
    }

    /**
     * 日付編集（画面表示用）
     * @param string $postalCode
     * @return string
     */
    protected function toDisplayDatetime($datetime, $format = null)
    {
        return $this->changeDateFormat($datetime, '-', '/', $format);
    }

    /**
     * 郵便番号編集（DB登録用）
     * @param string $postalCode
     * @return string
     */
    protected function toDbPostalCode($postalCode)
    {
        if (strlen($postalCode) == 8) {
            $returnCd = str_replace('-', '', $postalCode);
        } else {
            $returnCd = $postalCode;
        }
        return $returnCd;
    }

    /**
     * 郵便番号編集（画面表示用）
     * @param string $postalCode
     * @return string
     */
    protected function toDisplayPostalCode($postalCode)
    {
        if (strlen($postalCode) == 7) {
            $returnCd = substr($postalCode, 0, 3) . '-' . substr($postalCode, 3);
        } else {
            $returnCd = $postalCode;
        }
        return $returnCd;
    }

    /**
     * 販売情報検索
     * @param array $editRow
     * @return array
     */
    public function findSellInfo($editRow)
    {
        $indexs = explode('-', $editRow['sell_find_index']);
        if (count($indexs) <= 1) {
            return $editRow;
        }
        $destIndex = $indexs[0];
        $dtlIndex = count($editRow['register_destination'][$destIndex]['register_detail']) - 1;
        $editRow['sell_find_index'] = $destIndex . '-' . $dtlIndex;

        //[送付先]タブの選択状態を事前に保持しておく
        $editRow['active_destination_index'] = $destIndex;

        if (!isset($editRow['register_destination'][$destIndex]['register_detail'][$dtlIndex]['sell_cd']) ||
            strlen($editRow['register_destination'][$destIndex]['register_detail'][$dtlIndex]['sell_cd']) == 0
        ) {
            return $editRow;
        }
        $sellId = $editRow['register_destination'][$destIndex]['register_detail'][$dtlIndex]['sell_id'];

        $variationValues = $editRow['register_destination'][$destIndex]['register_detail'][$dtlIndex]['variation_values'];
        $sellCd = $editRow['register_destination'][$destIndex]['register_detail'][$dtlIndex]['sell_cd'];

        $wkArray = explode('__', $variationValues);
        $var1 = $wkArray[0];
        $var2 = count($wkArray) > 1 ? $wkArray[1] : '';
        $var3 = count($wkArray) > 2 ? $wkArray[2] : '';
        $var4 = count($wkArray) > 3 ? $wkArray[3] : '';
        $var5 = count($wkArray) > 4 ? $wkArray[4] : '';
        $var6 = count($wkArray) > 5 ? $wkArray[5] : '';
        //販売情報検索
        $this->setCurrentApiKey('searchEcPage');
        $requestData = [];
        $requestData['ec_page_cd'] = $sellCd;
        if (!empty($sellId)) {
            $requestData['m_ami_ec_page_id'] = $sellId;
        } else {
            $requestData['m_ecs_id'] = $editRow['m_ecs_id'];
        }
        $json_param = json_encode($requestData);
        $editRow['sales_param'] = base64_encode($json_param);

        $apiResults = json_decode($this->getRows($requestData), true);
        if (isset($apiResults['response']['search_result'])) {
            $apiResult = $apiResults['response']['search_result'];
            foreach($apiResult as $tempRec) {
                if ($tempRec["ec_page_cd"] === $sellCd) {
                    $apiResult = [$tempRec];
                    break;
                }
            }
        } else {
            $apiResult = null;
        }
        $this->setCurrentApiKey('');

        //skuが未選択の場合、選択画面を表示させる
        if (isset($apiResult[0]['m_ami_ec_page_variation']) &&
            count($apiResult[0]['m_ami_ec_page_variation']) > 0 &&
            empty($variationValues)
        ) {
            return $editRow;
        }
        //検索結果=0の場合も選択画面を表示させる
        if (!is_null($apiResult) && is_array($apiResult) && count($apiResult) == 0) {
            return $editRow;
        }
        $skuData = [];
        if (!is_null($apiResult) && is_array($apiResult) && count($apiResult) == 1) {
            //販売コードが一致していなければ選択画面を表示させる
            if ($apiResult[0]['ec_page_cd'] !== $sellCd) {
                return $editRow;
            }
            $skuName = '';
            //販売ID
            $editRow['register_destination'][$destIndex]['register_detail'][$dtlIndex]['sell_id'] = $apiResult[0]['m_ami_ec_page_id'];

            //SKUの場合
            if(!empty($variationValues)) {
                foreach($apiResult[0]['m_ami_ec_page_variation'] as $skuValiations) {
                    if (($skuValiations['variation1']['variation_choice_child_no'] == $var1) &&
                        ((!isset($skuValiations['variation2']['variation_axis_name'])) || $skuValiations['variation2']['variation_choice_child_no'] == $var2) &&
                        ((!isset($skuValiations['variation3']['variation_axis_name'])) || $skuValiations['variation3']['variation_choice_child_no'] == $var3) &&
                        ((!isset($skuValiations['variation4']['variation_axis_name'])) || $skuValiations['variation4']['variation_choice_child_no'] == $var4) &&
                        ((!isset($skuValiations['variation5']['variation_axis_name'])) || $skuValiations['variation5']['variation_choice_child_no'] == $var5) &&
                        ((!isset($skuValiations['variation6']['variation_axis_name'])) || $skuValiations['variation6']['variation_choice_child_no'] == $var6)
                    ) {
                        $skuName = $skuValiations['variation1']['variation_axis_name'] . "=" . $skuValiations['variation1']['variation_choice_name'];
                        if (isset($skuValiations['variation2']['variation_axis_name'])) {
                            $skuName .= ";" . $skuValiations['variation2']['variation_axis_name'] . "=" . $skuValiations['variation2']['variation_choice_name'];
                        }
                        if (isset($skuValiations['variation3']['variation_axis_name'])) {
                            $skuName .= ";" . $skuValiations['variation3']['variation_axis_name'] . "=" . $skuValiations['variation3']['variation_choice_name'];
                        }
                        if (isset($skuValiations['variation4']['variation_axis_name'])) {
                            $skuName .= ";" . $skuValiations['variation4']['variation_axis_name'] . "=" . $skuValiations['variation4']['variation_choice_name'];
                        }
                        if (isset($skuValiations['variation5']['variation_axis_name'])) {
                            $skuName .= ";" . $skuValiations['variation5']['variation_axis_name'] . "=" . $skuValiations['variation5']['variation_choice_name'];
                        }
                        if (isset($skuValiations['variation6']['variation_axis_name'])) {
                            $skuName .= ";" . $skuValiations['variation6']['variation_axis_name'] . "=" . $skuValiations['variation6']['variation_choice_name'];
                        }
                        break;
                    }
                }
            }

            //名前
            $editRow['register_destination'][$destIndex]['register_detail'][$dtlIndex]['sell_name'] = $apiResult[0]['ec_page_title'] . (empty($skuName) ? '' : ' '. $skuName);
            //単価
            $editRow['register_destination'][$destIndex]['register_detail'][$dtlIndex]['order_sell_price'] = $apiResult[0]['sales_price'];
            //数量
            $orderVol = $editRow['register_destination'][$destIndex]['register_detail'][$dtlIndex]['order_sell_vol'];
            if(empty($orderVol) || !is_numeric($orderVol)) {
                $orderVol = 1;
            }
            $editRow['register_destination'][$destIndex]['register_detail'][$dtlIndex]['order_sell_vol'] = $orderVol;
            //金額
            $editRow['register_destination'][$destIndex]['register_detail'][$dtlIndex]['order_sell_amount'] = (int)$apiResult[0]['sales_price'] * $orderVol;
            // 税率
            $taxRate = !empty($apiResult[0]['tax_rate']) ? $apiResult[0]['tax_rate'] : config('env.tax_rate.normal');
            $editRow['register_destination'][$destIndex]['register_detail'][$dtlIndex]['tax_rate'] = $taxRate;

            //商品確定 ⇒ これで画面の販売コードは変更不可となる
            $editRow['register_destination'][$destIndex]['register_detail'][$dtlIndex]['sell_checked'] = 1;

            //SKU退避情報
            $skuData['ecs_id'] = $apiResult[0]['m_ecs_id'];
            $skuData['sell_id'] = $apiResult[0]['m_ami_ec_page_id'];
            $skuData['sell_cd'] = $apiResult[0]['ec_page_cd'];
            $skuData['sell_option'] = $skuName;
            //単品、または、セット
            $skuDtl = [];
            if(isset($apiResult[0]['m_ami_sku']) && is_array($apiResult[0]['m_ami_sku']) && count($apiResult[0]['m_ami_sku']) > 0) {
                $skuData['sell_type'] = (count($apiResult[0]['m_ami_sku']) == 1) ? $this->sellType['ITEM'] : $this->sellType['SET'];
                foreach($apiResult[0]['m_ami_sku'] as $itemRow) {
                    $skuDtl[] = [
                        't_order_dtl_sku_id'	=>	null,
                        'item_id'				=>	$itemRow['m_ami_sku_id'],
                        'item_cd'				=>	$itemRow['sku_cd'],
                        'compose_vol'			=>	$itemRow['sku_vol']
                    ];
                }
            }
            //sku
            else {
                $skuData['sell_type'] = $this->sellType['SKU'];
                foreach($apiResult[0]['m_ami_ec_page_variation'] as $skuValiations) {
                    if (!empty($variationValues)) {
                        if (($skuValiations['variation1']['variation_choice_child_no'] == $var1) &&
                            ((!isset($skuValiations['variation2']['variation_axis_name'])) ||
                                 $skuValiations['variation2']['variation_choice_child_no'] == $var2) &&
                                 ((!isset($skuValiations['variation3']['variation_axis_name'])) ||
                                 $skuValiations['variation3']['variation_choice_child_no'] == $var3) &&
                                 ((!isset($skuValiations['variation4']['variation_axis_name'])) ||
                                 $skuValiations['variation4']['variation_choice_child_no'] == $var4) &&
                                 ((!isset($skuValiations['variation5']['variation_axis_name'])) ||
                                 $skuValiations['variation5']['variation_choice_child_no'] == $var5) &&
                                 ((!isset($skuValiations['variation6']['variation_axis_name'])) ||
                                 $skuValiations['variation6']['variation_choice_child_no'] == $var6)
                        ) {
                            foreach($skuValiations['m_ami_sku'] as $rows) {
                                $skuDtl[] = [
                                    't_order_dtl_sku_id'	=>	null,
                                    'item_id'				=>	$rows['m_ami_sku_id'],
                                    'item_cd'				=>	$rows['sku_cd'],
                                    'compose_vol'			=>	$rows['sku_vol']
                                ];
                            }
                            break;
                        }
                    }
                }
            }
            $skuData['sku_dtl'] = $skuDtl;

            //販売検索用のキー情報はクリアする
            $editRow['sell_find_index'] = '';

            //送料再計算
            $editRow = $this->recalcDestinationTotal($editRow, $destIndex);

            //合計金額再計算
            $editRow = $this->recalcTotal($editRow);

            $editRow['sales_param'] = '';

        }
        $editRow['register_destination'][$destIndex]['register_detail'][$dtlIndex]['sku_data'] = json_encode($skuData);

        return $editRow;
    }
    /**
     * 送料再計算
     * @param array $editRow
     * @param int $destIndex
     * @return array
     */
    protected function recalcDestinationTotal($editRow, $destIndex)
    {
        //商品小計
        $sumSellTotal = 0;
        foreach($editRow['register_destination'][$destIndex]['register_detail'] as $registerDtl) {
            //取消明細はスキップ
            if (!empty($registerDtl['cancel_timestamp']) || !empty($registerDtl['cancel_flg'])) {
                continue;
            }
            if (!empty($registerDtl['order_sell_amount']) && is_numeric($registerDtl['order_sell_amount'])) {
                $sumSellTotal += (int)$registerDtl['order_sell_amount'];
            }
        }
        $editRow['register_destination'][$destIndex]['sum_sell_total'] = $sumSellTotal;

        //ECサイトマスタの送料設定がある場合のみ処理を行う
        if (!empty($editRow['base_delivery_fee']) && !empty($editRow['item_price_for_free_delivery_fee'])) {
            if ($sumSellTotal >= (int)$editRow['item_price_for_free_delivery_fee']) {
                if (!empty($editRow['register_destination'][$destIndex]['shipping_fee'])) {
                    $editRow['alertMessage'] = $this->errorMessages['CHANGE_DELIVERY_FEE_FREE'];
                }
                $editRow['register_destination'][$destIndex]['shipping_fee'] = 0;
            } elseif ($sumSellTotal <= (int)$editRow['item_price_for_free_delivery_fee']) {
                if (empty($editRow['register_destination'][$destIndex]['shipping_fee'])) {
                    $editRow['alertMessage'] = $this->errorMessages['CHANGE_DELIVERY_FEE_CHARGE'];
                }
                $editRow['register_destination'][$destIndex]['shipping_fee'] = $editRow['base_delivery_fee'];
            }
        }
        return $editRow;
    }
    /**
     * 合計再計算
     * @param array $editRow
     * @param int $destIndex
     * @return array
     */
    protected function recalcTotal($editRow)
    {

        //金額初期化
        $sellTotalPrice = 0;
        $shippingFee = 0;
        $paymentFee = 0;
        $packageFee = 0;
        //配送先毎の計算
        foreach($editRow['register_destination'] as $destRow) {
            //商品小計
            if (!empty($destRow['sum_sell_total'])) {
                $sellTotalPrice += (int)$destRow['sum_sell_total'];
            }
            //送料
            if (!empty($destRow['shipping_fee'])) {
                $shippingFee += (int)$destRow['shipping_fee'];
            }
            //手数料
            if (!empty($destRow['payment_fee'])) {
                $paymentFee += (int)$destRow['payment_fee'];
            }
            //包装料
            if (!empty($destRow['wrapping_fee'])) {
                $packageFee += (int)$destRow['wrapping_fee'];
            }
        }
        //商品合計計
        $editRow['sell_total_price'] = $sellTotalPrice;
        //消費税額
        $taxPrice = !empty($editRow['tax_price']) ? (int)$editRow['tax_price'] : 0;
        //送料
        $editRow['shipping_fee'] = $shippingFee;
        //手数料
        $editRow['payment_fee'] = $paymentFee;
        //包装料
        $editRow['package_fee'] = $packageFee;
        //合計金額
        $editRow['total_price'] = $sellTotalPrice + $taxPrice + $shippingFee + $paymentFee + $packageFee;

        $discountAmount = 0;
        //割引金額
        if (!empty($editRow['discount'])) {
            $discountAmount += (int)$editRow['discount'];
        }
        //クーポン合計
        if (!empty($editRow['total_use_coupon'])) {
            $discountAmount += (int)$editRow['total_use_coupon'];
        }
        //ポイント利用
        if (!empty($editRow['use_point'])) {
            $discountAmount += (int)$editRow['use_point'];
        }

        //請求金額
        $editRow['order_total_price'] = $editRow['total_price'] - $discountAmount;
        return $editRow;
    }

    /**
     * 在庫状況クリア
     * @param array $editRow
     * @param int $destIndex
     * @return array
     */
    public function clearDrawingStatus($editRow, $destIndex)
    {
        $destRows = [];
        foreach($editRow['register_destination'] as $destRow) {
            $dtlRows = [];
            foreach($destRow['register_detail'] as $dtlRow) {
                $dtlRow['drawing_status_name'] = '';
                $dtlRows[] = $dtlRow;
            }
            $destRow['register_detail'] = $dtlRows;
            $destRows[] = $destRow;
        }
        $editRow['register_destination'] = $destRows;
        $editRow['active_destination_index'] = $destIndex;
        return $editRow;
    }
}
