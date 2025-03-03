<?php

namespace App\Http\Controllers\Order\Api;

use App\Modules\Customer\Base\SearchDestinationsInterface;
use Illuminate\Http\Request;

use App\Modules\Common\Base\GetPrefecturalInterface;
use Config;

class DestinationController
{
    public function search(
        Request $request,
        SearchDestinationsInterface $search,
        GetPrefecturalInterface $getPrefectural,
    ) {
        $req = $request->all();

        $viewExtendData = [
            'pref' => $getPrefectural->execute(),
            'page_list_count' => Config::get('Common.const.disp_limits'),
        ];
        $viewExtendData['list_sort'] = [
            'column_name' => $req['sorting_column'] ?? 'm_destination_id',
            'sorting_shift' => $req['sorting_shift'] ?? 'asc'
        ];

        $option = [
            'should_paginate' => true,
            'limit' => $req['page_list_count'] ?? null,
            'page' => $req['hidden_next_page_no'] ?? null,
            'sorts' => [$viewExtendData['list_sort']['column_name'] => $viewExtendData['list_sort']['sorting_shift']],
        ];

        // 検索処理
        $paginator = $search->execute($req, $option);
        
        // view 向け項目初期値
        $searchRow ??= $req;
        $paginator ??= null;
        $viewExtendData ??= null;
        $compact = [
            'searchRow',
            'paginator',
            'viewExtendData',
        ];

        // modalの返却(view 未作成)
        $viewContent = account_view('order.base.destination-modal', compact($compact))->render();
        return response()->json(['html' => $viewContent]);
    }
    
    public function detail(
        Request $request,
        SearchDestinationsInterface $search,
    ) {
        $destinationId = $request->route('destination_id');

        // destination_id は必須です
        if (!isset($destinationId)) {
            return response()->json([
                'error' => 'destination_id is required',
            ], 404);
        }

        $conditions = [
            'm_destination_id' => $destinationId ?? null,
        ];

        // モジュール
        $results = $search->execute($conditions);

        // $destination が 1件の配列ならば、最初の要素を返す、それ以外はエラー
        if (count($results) === 1) {
            $result = $results[0];
        } else {
            return response()->json([
                'error' => 'm_destination_id is not found',
            ], 404);
        }

        // 必要な項目のみ抽出する
        $result = collect($result)->only([
            "cust_id",
            "destination_name",
            "destination_name_kana",
            "destination_address1",
            "destination_address2",
            "destination_address3",
            "destination_address4",
            "destination_company_name",
            "destination_division_name",
            "destination_postal",
            "destination_tel",
        ]);


        return response()->json($result->toArray());
    }
}