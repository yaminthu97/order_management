<?php

namespace App\Http\Controllers\Order\Api;

use App\Modules\Ami\Base\SearchAmiPageInterface;
use Illuminate\Http\Request;
use Config;

class AmiPageController
{
    public function search(
        Request $request,
        SearchAmiPageInterface $search,
    ) {
        $req = $request->all();
        
        $viewExtendData = [
            'page_list_count' => Config::get('Common.const.disp_limits'),
        ];
        
        $viewExtendData['list_sort'] = [
            'column_name' => $req['sorting_column'] ?? 'ec_page_cd',
            'sorting_shift' => $req['sorting_shift'] ?? 'asc'
        ];
        
        $options = [
            'should_paginate' => true,
            'limit' => $req['page_list_count'] ?? null,
            'page' => $req['hidden_next_page_no'] ?? null,
            'sorts' => [$viewExtendData['list_sort']['column_name'] => $viewExtendData['list_sort']['sorting_shift']],
            'with' => [
                'page',
                'page.pageSku',
                'page.pageSku.sku',
                'page.pageAttachmentItem',
                'page.pageAttachmentItem.attachmentItem',
            ],
        ];

        // 検索処理
        $paginator = $search->execute($req, $options);

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
        $viewContent = account_view('order.base.ami-modal', compact($compact))->render();
        return response()->json(['html' => $viewContent]);
    }
    
    public function detail(
        Request $request,
        SearchAmiPageInterface $search,
    ) {
        $amiPageId = $request->route('ami_page_id');

        // ami_page_id が必須です
        if (!isset($amiPageId)) {
            return response()->json([
                'error' => 'ami_page_id is required',
            ], 400);
        }

        $conditions = [
            'm_ami_ec_page_id' => $amiPageId ?? null,
        ];
        $options = [
            'with' => [
                'page',
                'page.pageSku',
                'page.pageSku.sku',
                'page.pageAttachmentItem',
                'page.pageAttachmentItem.attachmentItem',
            ],
        ];

        // モジュール
        $results = $search->execute($conditions, $options);

        // $destination が 1件の配列ならば、最初の要素を返す、それ以外はエラー
        if (count($results) === 1) {
            $result = $results[0];
        } else {
            return response()->json([
                'error' => 'm_ami_page is not found',
            ], 404);
        }

        // 必要な項目のみ抽出する
        $result = $this->extractAmiPage($result);

        return response()->json($result->toArray());
    }
    
    public function searchAmiPage(
        Request $request,
        SearchAmiPageInterface $search,
    ) {
        $req = $request->all();

        // m_ecs_id が必須です
        if (!isset($req['m_ecs_id'])) {
            return response()->json([
                'error' => 'm_ecs_id is required',
            ], 400);
        }

        // ec_page_cd が必須です
        if (!isset($req['ec_page_cd'])) {
            return response()->json([
                'error' => 'ec_page_cd is required',
            ], 400);
        }

        $conditions = [
            'm_ecs_id' => $req['m_ecs_id'],
            'ec_page_cd' => $req['ec_page_cd'],
            'ec_page_cd_strict' => true, // 完全一致のみ検索
        ];
        $options = [
            'with' => [
                'page',
                'page.pageSku',
                'page.pageSku.sku',
                'page.pageAttachmentItem',
                'page.pageAttachmentItem.attachmentItem',
            ],
        ];

        // モジュール
        $results = $search->execute($conditions, $options);

        // $destination が 1件の配列ならば、最初の要素を返す、それ以外はエラー
        if (count($results) === 1) {
            $result = $results[0];
        } else {
            return response()->json([
                'error' => 'm_ami_page is not found',
            ], 404);
        }

        // 必要な項目のみ抽出する
        $result = $this->extractAmiPage($result);

        return response()->json($result->toArray());
    }

    // 必要な項目のみ抽出する
    private function extractAmiPage($result)
    {
        // AmiEcPage の抽出
        $result = collect($result)->only([
            "m_ami_ec_page_id",
            "m_ami_page_id",
            "m_ecs_id",
            "m_ec_type",
            "auto_stock_cooperation_flg",
            "auto_ec_page_cooperation_flg",
            "ec_page_cd",
            "ec_page_title",
            "ec_page_type",
            "sales_price",
            "tax_rate",
            "page",
        ]);

        // AmiPage の抽出
        $result['page'] = collect($result['page'])->only([
            "m_ami_page_id",
            "page_cd",
            "page_title",
            "sales_price",
            "tax_rate",
            "page_desc",
            "image_path",
            "page_attachment_item",
            'remarks1',
            'remarks2',
            'remarks3',
            'remarks4',
            'remarks5',
            'page_sku',
        ]);

        // PageAttachmentItem の抽出
        $result['page']['page_attachment_item'] = collect($result['page']['page_attachment_item'])
            ->filter(function ($grandchild) {
                // 削除済みのデータは除外
                if ($grandchild['attachment_item']['delete_flg'] !== 0) {
                    return false;
                }
                return true;
            })
            ->map(function ($grandchild) {
                $grandchildData = collect($grandchild)->only([
                    "m_ami_attachment_item_id",
                    "category_id",
                    "group_id",
                    "item_vol",
                    "attachment_item",
                ]);

                // AttachmentItem の抽出
                $grandchildData['attachment_item'] = collect($grandchildData['attachment_item'])->only([
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

                return $grandchildData;
            });

        // page_sku の抽出
        $result['page']['page_sku'] = collect($result['page']['page_sku'])->map(function ($pageSkuList) {
            return collect($pageSkuList['sku'])->only([
                "m_ami_sku_id",
                "sku_cd",
                "sku_name",
                "jan_cd",
                "including_package_flg",
                "direct_delivery_flg",
                "three_temperature_zone_type",
                "gift_flg",
                "search_result_display_flg",
                "stock_cooperation_status",
                "warehouse_cooperation_status",
                "m_suppliers_id",
                "sales_price",
                "item_price",
                "item_cost",
                "remarks1",
                "remarks2",
                "remarks3",
                "remarks4",
                "remarks5",
            ]);
        });

        return $result;
    }

}