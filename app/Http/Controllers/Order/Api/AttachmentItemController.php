<?php

namespace App\Http\Controllers\Order\Api;

use App\Modules\Ami\Base\SearchAmiAttachmentInterface;
use App\Modules\Master\Base\SearchItemNameTypesInterface;
use App\Modules\Master\Base\Enums\ItemnameTypeInterface;
use App\Services\EsmSessionManager;
use App\Enums\ItemNameType;
use App\Enums\DisplayFlg;

use Illuminate\Http\Request;
use Config;

class AttachmentItemController
{
    public function search(
        Request $request,
        SearchAmiAttachmentInterface $search,
        SearchItemNameTypesInterface $searchItemNameTypes,
        EsmSessionManager $esmSessionManager,
    ) {
        $req = $request->all();
        
        $viewExtendData = [
            'page_list_count' => Config::get('Common.const.disp_limits'),
        ];
        $viewExtendData['list_sort'] = [
            'column_name' => $req['sorting_column'] ?? 'm_ami_attachment_item_id',
            'sorting_shift' => $req['sorting_shift'] ?? 'asc'
        ];
        
        // 付属品カテゴリ一覧
        $m_itemname_types = $searchItemNameTypes->execute([
            'm_itemname_type' => ItemNameType::AttachmentCategory->value,
            'delete_flg' => 0,
            'm_account_id' => $esmSessionManager->getAccountId(),
        ]);
        $catetories = [];
        foreach ($m_itemname_types as $m_itemname_type) {
            $catetories[$m_itemname_type['m_itemname_types_id']] = $m_itemname_type['m_itemname_type_name'];
        }
        $viewExtendData['category_list'] = $catetories;

        $option = [
            'should_paginate' => true,
            'limit' => $req['page_list_count'] ?? null,
            'page' => $req['hidden_next_page_no'] ?? null,
            'sorts' => [$viewExtendData['list_sort']['column_name'] => $viewExtendData['list_sort']['sorting_shift']],
        ];

        if (isset($req['sorting_column'])) {
            if (isset($req['sorting_shift'])) {
                $option['sorts'] = [$req['sorting_column'] ,$req['sorting_shift']];
            } else {
                $option['sorts'] = $req['sorting_column'];
            }
        }

        // 受注時表示フラグが設定されているもののみ
        $req['display_flg'] = DisplayFlg::VISIBLE->value;

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
        $viewContent = account_view('order.base.attachment-modal', compact($compact))->render();
        return response()->json(['html' => $viewContent]);
    }
    
    public function detail(
        Request $request,
        SearchAmiAttachmentInterface $search,
    ) {
        $attachmentItemId = $request->route('attachment_item_id');

        // attachment_item_id が必須です
        if (!isset($attachmentItemId)) {
            return response()->json([
                'error' => 'attachment_item_id is required',
            ], 404);
        }

        $conditions = [
            'm_ami_attachment_item_id' => $attachmentItemId ?? null,
        ];

        // モジュール
        $results = $search->execute($conditions);

        // $results が 1件の配列ならば、最初の要素を返す、それ以外はエラー
        if (count($results) === 1) {
            $result = $results[0];
        } else {
            return response()->json([
                'error' => 'm_ami_page is not found',
            ], 404);
        }
    
        // 必要な項目のみ抽出する
        $result = collect($result)->only([
            "m_ami_attachment_item_id",
            "attachment_item_cd",
            "attachment_item_name",
            "category_id",
            "display_flg",
            "invoice_flg",
            "reserve1",
            "reserve2",
            "reserve3",
        ]);
        return response()->json($result->toArray());
    }
    
    // m_ami_attachment_items.attachment_item_cd による検索
    public function searchItemCd(
        Request $request,
        SearchAmiAttachmentInterface $search,
    ) {
        $req = $request->all();

        // item_cd が必須です
        if (!isset($req['item_cd'])) {
            return response()->json([
                'error' => 'item_cd is required',
            ], 404);
        }

        $conditions = [
            'attachment_item_cd' => $req['item_cd'],
            'attachment_item_cd_strict' => true, // 完全一致のみ検索
            'display_flg' => DisplayFlg::VISIBLE->value,
        ];

        // モジュール
        $results = $search->execute($conditions);

        // $results が 1件の配列ならば、最初の要素を返す、それ以外はエラー
        if (count($results) === 1) {
            $result = $results[0];
        } else {
            return response()->json([
                'error' => 'm_ami_page is not found',
            ], 404);
        }
    
        // 必要な項目のみ抽出する
        $result = collect($result)->only([
            "m_ami_attachment_item_id",
            "attachment_item_cd",
            "attachment_item_name",
            "category_id",
            "display_flg",
            "invoice_flg",
            "reserve1",
            "reserve2",
            "reserve3",
        ]);
        return response()->json($result->toArray());
    }
    
    public function categoryList(
        Request $request,
        SearchItemNameTypesInterface $searchItemNameTypes,
    ) {
        $m_itemname_types = $searchItemNameTypes->execute(['m_itemname_type' => ItemNameType::AttachmentCategory->value, 'delete_flg' => 0]);
        
        //m_itemname_types を m_itemname_types_id =>  m_itemname_type_name の配列に
        $catetories = [];
        foreach ($m_itemname_types as $m_itemname_type) {
            $catetories[$m_itemname_type['m_itemname_types_id']] = $m_itemname_type['m_itemname_type_name'];
        }
        return response()->json($catetories);
    }
}
