<?php

namespace App\Http\Controllers\Order;

use App\Http\Requests\Order\Gfh1207\OrderShipmentListRequest;
use App\Modules\Common\Base\GetOrderTypeInterface;
use App\Modules\Common\Base\GetStoreGroupInterface;
use App\Modules\Common\Base\RegisterBatchExecuteInstructionInterface;
use App\Modules\Master\Gfh1207\Enums\BatchListEnum;
use Illuminate\Http\Request;

class OrderShipmentListController
{
      
    public function list(Request $request, GetStoreGroupInterface $getStoreGroup, GetOrderTypeInterface $getOrderType)
    {
        $valueArray = [];
        $itemNameData = [];
        $searchInfo = $request->input();
        $dataList = $getOrderType->execute();
        if (isset($dataList['error'])) {
            session()->flash('messages.error', ['message' => __('messages.error.connection_error')]);
        } else {
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
            $storeData = $getStoreGroup->execute();
            if (isset($storeData['error'])) {
                session()->flash('messages.error', ['message' => __('messages.error.connection_error')]);
            }else{
                $itemNameData = $storeData;
            }
        }
        return account_view('order.gfh_1207.shipment', [
            'valueArray' => $valueArray,
            'itemNameData' => $itemNameData,
            'searchInfo'  => $searchInfo
        ]);
    }


    public function csvOutput(OrderShipmentListRequest $request, RegisterBatchExecuteInstructionInterface $registerBatchExecute)
    {
        $req = $request->all();
        $batchId = BatchListEnum::EXPXLSX_SHIPPED_SEARCH;
        $execute_conditions = [
            'search_info' => [
                'process_type' => $req['process_type'],
                'order_date_from' => $req['order_date_from'],
                'order_date_to' => $req['order_date_to'],
                'deli_plan_date_from' => $req['deli_plan_date_from'],
                'deli_plan_date_to' => $req['deli_plan_date_to'],
                'inspection_date_from' => $req['inspection_date_from'],
                'inspection_date_to' => $req['inspection_date_to'],
                'order_id_from' => $req['order_id_from'],
                'order_id_to' => $req['order_id_to'],
                'one_item_only' => $req['one_item_only'],
                'has_noshi' => $req['has_noshi'],
                'page_cd' => $req['page_cd'],
                'store_group' => $req['store_group'],
                'order_type' => $req['order_type'],
            ]
        ];

        $params = [
            'execute_batch_type' =>  $batchId->value,
            'execute_conditions' => $execute_conditions,
        ];

        $result = $registerBatchExecute->execute($params);

        if ($result['result']['status'] == 0) {
            session()->flash('messages.info', ['message' => __('messages.info.csv_output')]);
        } else {
            session()->flash('messages.error', ['message' => __('messages.error.process_something_wrong', ['process' => '登録'])]);
        }
        return redirect()->route('order.shipped_search.list')->withInput();
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
