<?php

namespace App\Http\Controllers\Order;

use App\Modules\Common\Base\RegisterBatchExecuteInstructionInterface;

use App\Modules\Master\Gfh1207\Enums\BatchListEnum;
use App\Enums\ItemNameType;

use App\Modules\Master\Base\SearchItemNameTypesInterface;
use App\Modules\Master\Base\SearchOperatorsInterface;
use App\Modules\Order\Base\SearchShippingOrderInterface;
use App\Modules\Master\Base\Enums\BatchListEnumInterface;

use Illuminate\Http\Request;
use Carbon\Carbon;

class ShippingOrderController
{
      
    public function list(
        Request $request,
        SearchItemNameTypesInterface $searchItemNameTypes,
        SearchOperatorsInterface $searchOperators
    ) {
        // 店舗集計グループ
        $storeGroups = [];
        $customerRanks = $searchItemNameTypes->execute(['m_itemname_type' => ItemNameType::CustomerRank, 'delete_flg' => 0]);
        foreach ($customerRanks as $customerRank) {
            if (isset($customerRank['m_itemname_type_code'])) {
                $storeGroups[$customerRank['m_itemname_type_code']] = $customerRank['m_itemname_type_code'];
            }
        }

        // 受注方法
        $orderTypes = $searchItemNameTypes->execute(['m_itemname_type' => ItemNameType::ReceiptType, 'delete_flg' => 0]);

        // 登録者一覧
        $operators = $searchOperators->execute(['delete_flg' => [0, null]]);

        // 初期値をセット
        $searchForm = [
            'cooperation_status' => [0]
        ];
        return account_view('order.base.shipping_order.list', [
            'searchForm' => $searchForm,
            'storeGroups' => $storeGroups,
            'orderTypes' => $orderTypes,
            'operators' => $operators,
            'collapsed' => true,
        ]);
    }

    public function postList(
        Request $request,
        SearchItemNameTypesInterface $searchItemNameTypes,
        SearchOperatorsInterface $searchOperators,
        SearchShippingOrderInterface $searchShippingOrder,
        RegisterBatchExecuteInstructionInterface $registerBatchExecuteInstruction,
    ) {
        $batchListEnum = app(BatchListEnumInterface::class);
        
        // 店舗集計グループ
        $storeGroups = [];
        $customerRanks = $searchItemNameTypes->execute(['m_itemname_type' => ItemNameType::CustomerRank, 'delete_flg' => 0]);
        foreach ($customerRanks as $customerRank) {
            if (isset($customerRank['m_itemname_type_code'])) {
                $storeGroups[$customerRank['m_itemname_type_code']] = $customerRank['m_itemname_type_code'];
            }
        }

        // 受注方法
        $orderTypes = $searchItemNameTypes->execute(['m_itemname_type' => ItemNameType::ReceiptType, 'delete_flg' => 0]);

        // 登録者一覧
        $operators = $searchOperators->execute(['delete_flg' => [0, null]]);

        $searchForm = $request->all();

        $collapsed = true;
        // 出荷ステータス, 登録者, 送り状番号に値がある場合は詳細検索を開く
        if (!empty($searchForm['gp2_type']) || !empty($searchForm['entry_operator_id']) || !empty($searchForm['invoice_num'])) {
            $collapsed = false;
        }

        if ($request->input('sorting_column') && $request->input('sorting_shift')) {
            $sorts = [
                $request->input('sorting_column') => $request->input('sorting_shift'),
            ];
        }

        // 検索処理, ペジネーション設定
        $searchOptions = [
            'page' => $request->input('hidden_next_page_no', 1),
            'limit' => $request->input('page_list_count', 10),
            'sorts' => $sorts ?? null,
            'should_paginate' => true,
            'with' => 'shippingLabels'
        ];
        $shipments = $searchShippingOrder->execute($searchForm, $searchOptions);

        if ($request->input('submit_shipping_order_csv')) {
            // 出荷連携処理実行
            if ($request->input('bulk_target_type') == 1) {
                $orderDestinationIds = $request->input('csv_output_check_key_id');
            } elseif ($request->input('bulk_target_type') == 2) {
                // 検索条件から全件取得
                $searchOptionsAll = [];
                $shipmentsAll = $searchShippingOrder->execute($searchForm, $searchOptionsAll);
                // t_order_destination_id 一覧を取得
                $orderDestinationIds = [];
                foreach ($shipmentsAll as $shipment) {
                    $orderDestinationIds[] = $shipment['t_order_destination_id'];
                }
            }
            
            // 登録処理
            $nowTime = new Carbon();
            $data = [
                't_order_destination_id' => implode(',', $orderDestinationIds), // カンマ区切り文字列に
                'bulk_target_type' => $request->input('bulk_target_type'),
            ];
            $registerInfo = [
                'execute_batch_type' => $batchListEnum::EXPCSV_SHIPMENT_SCHEDULE_DATA->value,
                'batchjob_create_datetime' => $nowTime->format('Y-m-d H:i:s'),
                'execute_conditions' => $data,
                '_token' => $request->input('_token'),
            ];

            $response = $registerBatchExecuteInstruction->execute($registerInfo);
            return redirect(route('order.shipping_order.list'))->with([
                'messages.info'=>['message'=>__('messages.info.create_completed', ['data' => '出荷予定データ作成処理'])]
            ]);
        }

        return account_view('order.base.shipping_order.list', [
            'searchForm' => $searchForm,
            'shipments' => $shipments,
            'storeGroups' => $storeGroups,
            'orderTypes' => $orderTypes,
            'operators' => $operators,
            'collapsed' => $collapsed,
        ]);
    }

}
