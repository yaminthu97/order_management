<?php

namespace App\Http\Controllers\Shipment;

use App\Enums\DeleteFlg;
use App\Http\Requests\Order\Gfh1207\ShipmentReportsRequest;
use App\Modules\Common\Base\GetOrderTypeInterface;
use App\Modules\Common\Base\RegisterBatchExecuteInstructionInterface;
use App\Modules\Master\Base\Enums\ItemnameType;
use App\Modules\Master\Base\GetPaymentTypesInterface;
use App\Modules\Master\Base\SearchDeliveryTypesInterface;
use App\Modules\Master\Base\SearchItemNameTypesInterface;
use App\Modules\Master\Gfh1207\Enums\BatchListEnum;
use App\Modules\Order\Base\GetProcessDateTimeInterface;
use Illuminate\Http\Request;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class ShipmentReportsController
{
    /**
     * Get data that customer rank,order type,payment type,delivery method and process date time to show at formload
    */
    public function list(
        Request $request,
        GetProcessDateTimeInterface $getProcessDateTime,
        GetOrderTypeInterface $getOrderType,
        GetPaymentTypesInterface $getPaymentType,
        SearchItemNameTypesInterface $getCustomerRank,
        SearchDeliveryTypesInterface $searchDeliveryTypes,
    ) {
        $orderTypeArray = [];
        $compact = [];

        $dataList = $getOrderType->execute();    // to get order type data

        $getPayment = $getPaymentType->execute();   // to get payment type data

        if (isset($dataList['error'])) {
            // for connection error
            session()->flash('messages.error', ['message' => __('messages.error.connection_error')]);
        } else {
            $orderTypeArray['m_ordertypes'] = [];
            if (is_array($dataList) && count($dataList) > 0) {

                // change data format
                $orderTypeArray['m_ordertypes'] = $this->changeIdValuesList(
                    $dataList,
                    [
                        'id'	=>	'm_itemname_types_id',
                        'value'	=>	'm_itemname_type_name'
                    ],
                    false,
                    true
                );
            }
            // to get customer rank data
            $customerRank = [
                'customer_rank_list' => $getCustomerRank->execute([
                    'delete_flg' => DeleteFlg::Use->value,
                    'm_itemname_type' => ItemnameType::CustomerRank->value
                ])->toArray()
            ];

            // to get delivery method data
            $deliveryMethod = $searchDeliveryTypes->execute()->toArray();

            // to get process date time
            $processData = $getProcessDateTime->execute();

            $compact = [
                'custRank'  => $customerRank['customer_rank_list'],
                'orderType' => $orderTypeArray,
                'paymentType' => $getPayment,
                'deliveryMethod' => $deliveryMethod,
                'processDateTime' => $processData
            ];
        }

        return account_view('order.gfh_1207.shipment-report', compact('compact'));
    }

    /**
     * フォームに従ってバッチ実行を保存する
     * @param array $request
     */
    public function csvOutput(
        ShipmentReportsRequest $request,
        RegisterBatchExecuteInstructionInterface $registerBatchExecute,
        GetOrderTypeInterface $getOrderType,
        GetPaymentTypesInterface $getPaymentType,
        SearchItemNameTypesInterface $getCustomerRank,
        SearchDeliveryTypesInterface $searchDeliveryTypes
    ) {

        $req = $request->all();
        $execute_conditions = [];
        // 各種処理
        $submit = $request->input('submit');
        switch ($submit) {
            // 手提げ出荷未出荷一覧・出荷予定日別手提げ枚数の場合
            case 'hand_carried_submit':
                // to get customer rank data
                $customerRank = [
                    'customer_rank_list' => $getCustomerRank->execute([
                        'delete_flg' => DeleteFlg::Use->value,
                        'm_itemname_type' => ItemnameType::CustomerRank->value
                    ])->toArray()
                ]['customer_rank_list'];

                $checkCusRank = array_values(array_filter($customerRank, function ($item) use ($req) {
                    return $item['m_itemname_types_id'] == $req['cust_runk_id'];
                }));

                if ($req['cust_runk_id'] == null || count($checkCusRank) > 0) {
                    $execute_conditions = [
                        'search_info' => [
                            'deli_plan_date_from' => $req['deli_plan_date_from'],
                            'deli_plan_date_to' => $req['deli_plan_date_to'],
                            'cust_runk_id' => $req['cust_runk_id']
                        ]
                    ];
                }
                $batchId = $req['hand_carried_radio'] == "0" ? BatchListEnum::EXPXLSX_SHIPMENT_REPORTS_SHIPPED_BAG : BatchListEnum::EXPXLSX_SHIPMENT_REPORTS_SCHEDULED_BAG;
                break;

                // 段ボール作業日別使用枚数一覧の場合
            case 'cardboard_submit':
                $execute_conditions = [
                    'search_info' => [
                        'deli_inspection_date_from' => $req['deli_inspection_date_from'],
                        'deli_inspection_date_to' => $req['deli_inspection_date_to'],
                    ]
                ];
                $batchId = BatchListEnum::EXPXLSX_SHIPMENT_REPORTS_CARDBOARD;
                break;

                // 出荷ステータスPGの場合
            case 'shipping_pg_submit':

                $dataList = $getOrderType->execute();    // to get order type data
                $checkOrder = array_values(array_filter($dataList, function ($item) use ($req) {
                    return $item['m_itemname_types_id'] == $req['order_type'];
                }));

                $getPayment = $getPaymentType->execute();   // to get payment type data
                $checkPayment = array_values(array_filter($getPayment, function ($item) use ($req) {
                    return $item['m_payment_types_id'] == $req['payment_type'];
                }));

                $deliveryMethod = $searchDeliveryTypes->execute()->toArray();
                $checkDeliType = array_values(array_filter($deliveryMethod, function ($item) use ($req) {
                    return $item['m_delivery_types_id'] == $req['shipping_method'];
                }));


                if (($req['order_type'] == null || count($checkOrder) > 0) && ($req['payment_type'] == null || count($checkPayment) > 0) && ($req['shipping_method'] == null || count($checkDeliType) > 0)) {
                    $execute_conditions = [
                        'search_info' => [
                            'deli_plan_date_from' => $req['deli_plan_date_from1'],
                            'deli_plan_date_to' => $req['deli_plan_date_to1'],
                            'order_type' => $req['order_type'],
                            'payment_type' => $req['payment_type'],
                            'm_delivery_types_id' => $req['shipping_method'],
                            'deli_instruct' => isset($req['deli_instruct']) ? ($req['deli_instruct'] == '1' ? 1 : 2) : null ,
                        ]
                    ];
                }

                $batchId = BatchListEnum::EXPXLSX_SHIPMENT_REPORTS_STATUS_PG;
                break;

                // 出荷検品チェックリストの場合for submit "shipping_inspection_checklist_submit" button
            case 'shipping_inspection_checklist_submit':
                $execute_conditions = [
                    'search_info' => [
                        'type' => ($req['shipping_inspection_checklist'] > 0 && $req['shipping_inspection_checklist'] < 3) ? $req['shipping_inspection_checklist'] : 3,
                        'deli_plan_date_from' => $req['deli_plan_date_from3'],
                        'deli_plan_date_to' => $req['deli_plan_date_to3'],
                        'order_id_from' => $req['order_id_from'],
                        'order_id_to' => $req['order_id_to'],
                    ]
                ];
                $batchId = BatchListEnum::EXPXLSX_SHIPMENT_REPORTS_CHECKLIST;
                break;

                // 出荷検品データ作成の場合
            case 'shipping_inspection_data_creation_submit':
                $execute_conditions = [
                    'search_info' => [
                        'is_email' => (isset($req['is_email']) == true &&  $req['is_email'] == 1) ? $req['is_email'] : null,
                        'is_yesterday' => (isset($req['is_yesterday']) == true &&  $req['is_yesterday'] == 1) ? $req['is_yesterday'] : null,
                        'type' => $req['shipping-inspection-checklist'] == 2 ? $req['shipping-inspection-checklist'] : 1,
                        'date' => $req['shipping-inspection-checklist'] == 1 ? null : $req['process_date'],
                    ]
                ];
                $batchId = BatchListEnum::EXPXLSX_SHIPMENT_REPORTS_SCHEDULED_BAG;
                break;
            default:
                throw new InvalidParameterException('不正なリクエストです');
        }

        if (count($execute_conditions) > 0) {
            $params = [
                'execute_batch_type' =>  $batchId->value,
                'execute_conditions' => $execute_conditions,
            ];
            $result = $registerBatchExecute->execute($params);  // to save batch execution data

            if ($result['result']['status'] == 0) {
                session()->flash('messages.info', ['message' => __('messages.info.csv_output')]);
            } else {
                session()->flash('messages.error', ['message' => __('messages.error.process_something_wrong', ['process' => '出力'])]);
            }
        } else {
            session()->flash('messages.error', ['message' => __('messages.error.process_something_wrong', ['process' => '出力'])]);
        }

        return redirect()->route('order.shipment_reports.list')->withInput();
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

        foreach ($dataRows as $row) {
            $returnArray[$row[$changeList['id']]] = $row[$changeList['value']];
        }
        return $returnArray;
    }

}
