<?php

namespace App\Http\Controllers\Order\Api;

use App\Modules\Customer\Base\SearchCustomerInterface;
use Illuminate\Http\Request;

use Config;
use App\Modules\Common\Base\GetPrefecturalInterface;
use App\Modules\Master\Base\GetCustomerRankInterface;

class CustomerController
{
    public function search(
        Request $request,
        SearchCustomerInterface $search,
    ) {
        $getPrefecture = app(GetPrefecturalInterface::class);
        $getCustomerRank = app(GetCustomerRankInterface::class);

        $prefectures = $getPrefecture->execute()->mapWithKeys(function ($item) {
            return ['' => '']+[$item['m_prefectural_id'] => $item['prefectual_name']];
        });

        $req = $request->all();

        $viewExtendData = [
            'm_prefectures' => $prefectures,
            'contactWayTypes' => $getCustomerRank->execute(),
            'page_list_count' => Config::get('Common.const.disp_limits'),
        ];
        $viewExtendData['list_sort'] = [
            'column_name' => $req['sorting_column'] ?? 'm_cust_id',
            'sorting_shift' => $req['sorting_shift'] ?? 'asc'
        ];

        $inputData = [[
            'search_info' => $request
        ]];

        $option = [
            'should_paginate' => true,
            'page' => $req['hidden_next_page_no'] ?? 1,
            'limit' => $req['page_list_count'] ?? Config::get('Common.const.page_limit'),
            'sorts' => [$viewExtendData['list_sort']['column_name'] => $viewExtendData['list_sort']['sorting_shift']],
        ];

        // 検索処理
        // 顧客受付、顧客検索の検索処理を利用する
        $paginator = $search->execute($req, $option);

        // view 向け項目初期値
        $paginator ??= null;
        $viewExtendData ??= null;
        $searchRow ??= $req;
        $viewMessage = [];

        $compact = [
            'paginator',
            'viewExtendData',
            'searchRow',
            'viewMessage'
        ];

        // modalの返却
        $viewContent = account_view('order.base.cust-modal', compact($compact))->render();
        return response()->json(['html' => $viewContent]);
    }
    
    public function detail(
        Request $request,
        SearchCustomerInterface $search,
    ) {
        $customerId = $request->route('customer_id');

        // customer_id は必須です
        if (!isset($customerId)) {
            return response()->json([
                'error' => 'customer_id is required',
            ], 400);
        }

        $conditions = [
            'm_cust_id' => $customerId ?? null,
        ];

        // モジュール
        $results = $search->execute($conditions, []);

        // $results が 1件の配列ならば、最初の要素を返す、それ以外はエラー
        if (count($results) === 1) {
            $result = $results[0];
        } else {
            return response()->json([
                'error' => 'm_destination_id is not found',
            ], 404);
        }
        // 必要な項目のみ抽出する
        $result = collect($result)->only([
            "name_kana",
            "name_kanji",
            "address1",
            "address2",
            "address3",
            "address4",
            "alert_cust_comment",
            "alert_cust_type",
            "birthday",
            "corporate_kana",
            "corporate_kanji",
            "corporate_tel",
            "cust_cd",
            "customer_category",
            "customer_type",
            "discount_rate",
            "division_name",
            "dm_send_letter_flg",
            "dm_send_mail_flg",
            "email1",
            "email2",
            "email3",
            "email4",
            "email5",
            "fax",
            "m_cust_id",
            "m_cust_runk_id",
            "note",
            "postal",
            "reserve1",
            "reserve2",
            "reserve3",
            "reserve4",
            "reserve5",
            "reserve6",
            "reserve7",
            "reserve8",
            "reserve9",
            "reserve10",
            "reserve11",
            "reserve12",
            "reserve13",
            "reserve14",
            "reserve15",
            "reserve16",
            "reserve17",
            "reserve18",
            "reserve19",
            "reserve20",
            "sex_type",
            "tel1",
            "tel2",
            "tel3",
            "tel4",
        ]);

        return response()->json($result->toArray());
    }
}
