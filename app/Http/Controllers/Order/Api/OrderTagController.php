<?php

namespace App\Http\Controllers\Order\Api;

use App\Modules\Order\Base\UpdateOrderTagInterface;
use Illuminate\Http\Request;
use App\Modules\Order\Base\SearchInterface;
class OrderTagController
{
    public function add(
        Request $request,
        UpdateOrderTagInterface $updateOrderTag,
    ) {
        $req = $request->all();

        // order_hdr_id と order_tag_id は必須です
        if (!isset($req['order_hdr_id'])) {
            return response()->json([
                'error' => 'order_hdr_id is required',
            ], 400);
        }
        if (!isset($req['order_tag_id'])) {
            return response()->json([
                'error' => 'order_tag_id is required',
            ], 400);
        }

        // 文字列ならばJsonデコードを試みる
        if (is_string($req['order_tag_id'])) {
            $decodedInput = json_decode($req['order_tag_id'], false);
        } else {
            $decodedInput = $req['order_tag_id'];
        }

        // 配列であればそのまま使い、単一の値であれば配列に変換する
        $orderTagIdList = is_array($decodedInput) ? $decodedInput : [$decodedInput];

        // 各要素をループで処理
        $results = [];
        foreach ($orderTagIdList as $orderTagId) {
            // モジュール
            $params = [];
            $results[] = $updateOrderTag->execute($req['order_hdr_id'], $orderTagId, $params);
        }

        // results の t_order_tag_id をカンマ区切りで返却する
        return response()->json([
            't_order_tag_id' => implode(',', array_column($results, 't_order_tag_id')),
        ]);
    }
    
    public function remove(
        Request $request,
        UpdateOrderTagInterface $updateOrderTag,
    ) {
        $req = $request->all();

        // order_hdr_id と order_tag_id は必須です
        if (!isset($req['order_hdr_id'])) {
            return response()->json([
                'error' => 'order_hdr_id is required',
            ], 400);
        }
        if (!isset($req['order_tag_id'])) {
            return response()->json([
                'error' => 'order_tag_id is required',
            ], 400);
        }

        // 文字列ならばJsonデコードを試みる
        if (is_string($req['order_tag_id'])) {
            $decodedInput = json_decode($req['order_tag_id'], false);
        } else {
            $decodedInput = $req['order_tag_id'];
        }

        // 配列であればそのまま使い、単一の値であれば配列に変換する
        $orderTagIdList = is_array($decodedInput) ? $decodedInput : [$decodedInput];

        // 各要素をループで処理
        $results = [];
        foreach ($orderTagIdList as $orderTagId) {
            // モジュール
            $params = ['cancel_flg' => 1];
            $results[] = $updateOrderTag->execute($req['order_hdr_id'], $orderTagId, $params);
        }

        // results の t_order_tag_id をカンマ区切りで返却する
        return response()->json([
            't_order_tag_id' => implode(',', array_column($results, 't_order_tag_id')),
        ]);
    }
    
    public function orderInfo(
        Request $request,
        SearchInterface $search,
    ) {
        $orderId = $request->route('id');

        if (!$orderId) {
            return response()->json([
                'error' => 'order_hdr_id is required',
            ], 400);
        }

        $order = $search->execute(['t_order_hdr_id' => $orderId], [
            'with' => ['orderTags', 'orderTags.orderTag']
        ])->first();
        
        // orderTagsをorderTagのm_order_tag_sortでソート
        $order->orderTags = $order->orderTags->sortBy(function ($orderTag) {
            return $orderTag->orderTag->m_order_tag_sort;
        });

        if (!$order) {
            return response()->json([
                'error' => 'order not found',
            ], 404);
        }

        $orderTags = [];
        foreach ($order->orderTags as $orderTag) {
            $orderTags[] = [
                't_order_tag_id' => $orderTag->t_order_tag_id,
                'm_order_tag_id' => $orderTag->m_order_tag_id,
                'tag_name' => $orderTag->orderTag->tag_name,
                'tag_display_name' => $orderTag->orderTag->tag_display_name,
                'tag_icon' => $orderTag->orderTag->tag_icon,
                'tag_color' => $orderTag->orderTag->tag_color,
                'font_color' => $orderTag->orderTag->font_color,
                'deli_stop_flg' => $orderTag->orderTag->deli_stop_flg,
            ];
        }
        return response()->json($orderTags);
    }
}
