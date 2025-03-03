<?php

namespace App\Http\Controllers\Order;

use App\Modules\Order\Base\Enums\InputSubmitCommandsInterface;
use App\Modules\Order\Base\Enums\OutputSubmitCommandsInterface;

use App\Modules\Order\Base\GetExtendDataInterface;
use App\Modules\Order\Base\GetOrderListConditionsInterface;
use App\Modules\Order\Base\SearchInterface;
use App\Modules\Order\Base\SetInputBatchExecuteInterface;

use App\Modules\Order\Base\SetOrderListConditionsInterface;
use App\Modules\Order\Base\SetOutputBatchExecuteInterface;

use App\Services\EsmSessionManager;

use Config;

use Illuminate\Http\Request;

class OrderListController
{
    // list
    public function list(
        Request $request,
        GetExtendDataInterface $getExtendData,
    ) {
        // viewExtendData の取得
        $viewExtendData = $getExtendData->execute('list');

        $viewMessage = [];
        $paginator = [];

        $compact = [
            'paginator',
            'viewExtendData',
            'viewMessage'
        ];

        // 通常のview
        return account_view('order.base.list', compact($compact));
    }

    // search
    public function search(
        Request $request,
        GetExtendDataInterface $getExtendData,
        SetInputBatchExecuteInterface $setInputBatchExecute,
        SetOutputBatchExecuteInterface $setOutputBatchExecute,
        GetOrderListConditionsInterface $getOrderListConditions,
        SetOrderListConditionsInterface $setOrderListConditions,
        SearchInterface $Search,
        EsmSessionManager $esmSessionManager,
    ) {

        $req = $request->all();
        $submitName = $this->getSubmitName($req);
        $viewMessage = [];

        // 検索処理
        if($submitName === 'search_clear') {
            $req = [];
        } else {
            // 受注お気に入り検索の登録
            if($submitName === 'add_order_list_cond') {
                [$orderListCond, $orderListCondMessage] = $this->addOrderListCond($setOrderListConditions, $req, $viewMessage);
                $viewMessage[] = $orderListCondMessage;
            }

            // 受注お気に入り検索の更新
            if($submitName === ('modify_order_list_cond')) {
                [$orderListCond, $orderListCondMessage] = $this->modifyOrderListCond($setOrderListConditions, $req, $viewMessage);
                $viewMessage[] = $orderListCondMessage;
            }

            // 受注お気に入り検索を削除
            if($submitName === ('delete_order_list_cond')) {
                [$orderListCond, $orderListCondMessage] = $this->deleteOrderListCond($setOrderListConditions, $req, $viewMessage);
                $viewMessage[] = $orderListCondMessage;
            }

            // 受注お気に入り検索の呼び出し
            if($submitName === ('read_order_list_cond')) {
                [$orderListCond, $orderListCondMessage, $req] = $this->readOrderListCond($getOrderListConditions, $req);
            }

            $sorts = [
                $req['sorting_column'] ?? 't_order_hdr_id' => $req['sorting_shift'] ?? 'desc'
            ];
            $options = [
                'should_paginate' => true,
                'page' => $req['hidden_next_page_no'] ?? 1,
                'limit' => $req['page_list_count'] ?? 10,
                'sorts' => $sorts,
                'join_table' => ['t_order_destination'],
                'with' => ['orderTags', 'orderMemo', 'ecs', 'paymentTypes'],
            ];

            $req['page'] = $req['hidden_next_page_no'] ?? 1;
            $req['m_account_id'] = $esmSessionManager->getAccountId();

            $paginator = $Search->execute($req, $options);
            
            // Output系処理
            $outputSubmitCommands = app(OutputSubmitCommandsInterface::class);
            $outputSubmitArray = collect($outputSubmitCommands::cases())->map(fn ($type) => $type->value)->all();
            if (in_array($submitName, $outputSubmitArray)) {
                $csvOutputErrorResult = $setOutputBatchExecute->execute($request, $paginator, $submitName);
                // メッセージ生成処理
                if ($csvOutputErrorResult !== '') {
                    $viewMessage[] = $csvOutputErrorResult;
                } else {
                    $viewMessage[] = $this->returnOutputViewMessage($submitName);
                }
            }

            // Input系処理
            $inSubmitCommands = app(InputSubmitCommandsInterface::class);
            $inSubmitArray = collect($inSubmitCommands::cases())->map(fn ($type) => $type->value)->all();
            if (in_array($submitName, $inSubmitArray)) {
                $csvInputErrorResult = $setInputBatchExecute->execute($request, $submitName);
                // メッセージ生成処理
                if ($csvInputErrorResult !== '') {
                    $viewMessage[] = $csvInputErrorResult;
                } else {
                    $viewMessage[] = $this->returnInputViewMessage($submitName);
                }
            }

            // メッセージ内の改行を展開
            foreach ($viewMessage as $message) {
                if (strpos($message, '<br>') !== false) {
                    $message = explode('<br>', $message);
                    $viewMessage = array_merge($viewMessage, $message);
                    unset($viewMessage[array_search($message, $viewMessage)]);
                }
            }
        }
        // viewExtendData の取得
        $viewExtendData = $getExtendData->execute('list');

        $viewExtendData['page_list_count'] = Config::get('Common.const.disp_limits');

        $viewExtendData['list_sort'] = [
            'column_name' => $req['sorting_column'] ?? 't_order_hdr_id',
            'sorting_shift' => $req['sorting_shift'] ?? 'desc'
        ];

        $paginator ??= [];
        $searchRow = $req;

        $compact = [
            'searchRow',
            'paginator',
            'viewExtendData',
            'viewMessage'
        ];

        // AJAX による検索の場合は結果のみ返却
        if (request()->ajax()) {
            $viewContent = account_view('order.base.list_search', compact($compact))->render();
            return response()->json(['html' => $viewContent]);
        }

        // 通常のview
        return account_view('order.base.list', compact($compact));
    }

    /**
     * submitボタン内容の取得
     */
    protected function getSubmitName($req)
    {
        $submitName = '';
        if(!empty($req['submit'])) {
            $submitName = $req['submit'];
        }
        return $submitName;
    }

    // 受注お気に入り検索の登録
    public function addOrderListCond($setOrderListConditions, $req, $viewMessage)
    {
        if(empty($req['order_list_cond_name'])) {
            $viewMessage = __('messages.error.order_search.enter_name', ['name' => '検索条件名']);
            return [null, $viewMessage];
        }
        $order_list_cond = $this->filterOrderListCond($req);

        $orderListCond = $setOrderListConditions->execute([
            'm_order_list_cond_id' => null,
            'order_list_cond_name' => $req['order_list_cond_name'],
            'public_flg' => $req['public_flg'] ?? 0,
            'order_list_cond' => $order_list_cond,
        ]);

        $viewMessage = __('messages.info.create_completed', ['data' => '現在の検索条件']);
        return [$orderListCond, $viewMessage];
    }

    // 受注お気に入り検索の更新
    public function modifyOrderListCond($setOrderListConditions, $req, $viewMessage)
    {
        if(empty($req['m_order_list_cond_id'])) {
            $viewMessage = __('messages.error.order_search.select_name', ['name' => '検索条件']);
            return [null, $viewMessage];
        }
        if(empty($req['order_list_cond_name'])) {
            $viewMessage = __('messages.error.order_search.enter_name', ['name' => '検索条件名']);
            return [null, $viewMessage];
        }
        $order_list_cond = $this->filterOrderListCond($req);

        $orderListCond = $setOrderListConditions->execute([
            'm_order_list_cond_id' => $req['m_order_list_cond_id'],
            'order_list_cond_name' => $req['order_list_cond_name'],
            'public_flg' => $req['public_flg'] ?? 0,
            'order_list_cond' => $order_list_cond,
        ]);

        $viewMessage = __('messages.info.update_completed', ['data' => '検索条件']);
        return [$orderListCond, $viewMessage];
    }

    // 受注お気に入り検索を削除
    public function deleteOrderListCond($setOrderListConditions, $req, $viewMessage)
    {
        if(empty($req['m_order_list_cond_id'])) {
            $viewMessage = __('messages.warning.no_search_criteria');
            return [null, $viewMessage];
        } else {
            $orderListCond = $setOrderListConditions->execute([
                'm_order_list_cond_id' => $req['m_order_list_cond_id'],
                'delete' => 1,
            ]);

            $viewMessage = __('messages.info.delete_completed', ['data' => '検索条件']);
            return [$orderListCond, $viewMessage];
        }
    }

    // 受注お気に入り検索の呼び出し
    //if($submitName === ('read_order_list_cond')) {
    public function readOrderListCond($getOrderListConditions, $req)
    {
        if(empty($req['m_order_list_cond_id'])) {
            $viewMessage = __('messages.warning.no_search_criteria');
            return [null, $viewMessage, $req];
        } else {
            $orderListCond = $getOrderListConditions->execute(['m_order_list_cond_id' => $req['m_order_list_cond_id']]);

            $orderCond = json_decode($orderListCond[0]['order_list_cond'], true);

            $orderCond = array_merge($orderCond, ['m_order_list_cond_id' => $req['m_order_list_cond_id']]);
            $req = $orderCond;
            return [$orderListCond, null, $req];
        }
    }

    public function filterOrderListCond($req)
    {
        $keysToRemove = [
            '_token', 'm_order_list_cond_id', 'order_list_cond_name', 'public_flg',
            'page_list_count', 'hidden_next_page_no', 'sorting_column', 'sorting_shift'
        ];
        // フィルタリング処理
        $order_list_cond = array_filter($req, function ($key) use ($keysToRemove) {
            return !in_array($key, $keysToRemove) && strpos($key, 'submit_') !== 0;
        }, ARRAY_FILTER_USE_KEY);
        return $order_list_cond;
    }

    public function returnOutputViewMessage($submitName)
    {
        $viewMessage = '';
        switch($submitName) {
            case 'change_progress':
                $viewMessage = __('messages.info.create_completed', ['data' => '進捗区分変更処理']);
                break;
            case 'send_template_mail':
            case 'send_recipt_mail':
                $viewMessage = __('messages.info.create_completed', ['data' => 'メール送信処理']);
                break;
            case 'new_send_recipt_mail':
                $viewMessage = __('messages.info.create_completed', ['data' => 'メール送信処理']);
                break;
            case 'payment':
                $viewMessage = __('messages.info.create_completed', ['data' => '入金処理']);
                break;
            case 'change_delivery_type':
                $viewMessage = __('messages.info.create_completed', ['data' => '配送方法変更処理']);
                break;
            case 'change_deli_hope_date':
                $viewMessage = __('messages.info.create_completed', ['data' => '配送希望日変更処理']);
                break;
            case 'change_deli_plan_date':
                $viewMessage = __('messages.info.create_completed', ['data' => '出荷予定日変更処理']);
                break;
            case 'change_deli_decision_date':
                $viewMessage = __('messages.info.create_completed', ['data' => '出荷確定日変更処理']);
                break;
            case 'change_operator_comment':
                $viewMessage = __('messages.info.create_completed', ['data' => '社内メモ更新処理']);
                break;
            case 'add_order_tag':
                $viewMessage = __('messages.info.create_completed', ['data' => '受注タグ登録処理']);
                break;
            case 'remove_order_tag':
                $viewMessage = __('messages.info.create_completed', ['data' => '受注タグ削除処理']);
                break;
            case 'reserve_stock':
                $viewMessage = __('messages.info.create_completed', ['data' => '在庫引当処理']);
                break;
            case 'output_pdf':
                $viewMessage = __('messages.info.create_completed', ['data' => 'PDF出力']);
                break;
            case 'output_delivery_file':
                $viewMessage = __('messages.info.create_completed', ['data' => 'データ出力']);
                break;
            default:
                $viewMessage = __('messages.info.create_completed', ['data' => 'CSV出力']);
                break;
        }
        return $viewMessage;
    }

    public function returnInputViewMessage($submitName)
    {
        $viewMessage = '';
        switch($submitName) {
            default:
                $viewMessage = __('messages.info.create_completed', ['data' => '取込処理']);
                break;
        }
        return $viewMessage;

    }
}
