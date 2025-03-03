<?php

namespace App\Http\Controllers\Order\Api;

use App\Modules\Common\Base\SearchNoshiFormatInterface;
use App\Modules\Ami\Base\SearchAmiPageNoshiInterface;
use Illuminate\Http\Request;

class NoshiFormatController
{
    public function search(
        Request $request,
        SearchAmiPageNoshiInterface $search,
    ) {
        $amiPageId = $request->route('ami_page_id');

        // amiPageId は必須です
        if (!isset($amiPageId)) {
            return response()->json([
                'error' => 'ami_page_id is required',
            ], 400);
        }

        // 検索
        $conditions = [
            'm_ami_ec_page_id' => $amiPageId,
        ];
        $options = [
            'with' => ['noshiFormat', 'noshiFormat.noshi'],
        ];
        $results = $search->execute($conditions, $options);

        // 必要な項目のみ抽出する
        $formattedResults = [];
        foreach ($results->toArray() as $result) {
            $formattedResults[$result['noshi_format']['noshi']['attachment_item_group_id']][] = [
                'm_ami_page_noshi_id' => $result['m_ami_page_noshi_id'],
                'm_noshi_format_id' => $result['noshi_format']['m_noshi_format_id'],
                'noshi_format_name' => $result['noshi_format']['noshi_format_name'],
                'noshi_type' => $result['noshi_format']['noshi']['noshi_type'],
                'attachment_item_group_id' => $result['noshi_format']['noshi']['attachment_item_group_id'],
                'omotegaki' => $result['noshi_format']['noshi']['omotegaki'],
                'noshi_cd' => $result['noshi_format']['noshi']['noshi_cd'],
            ];
        }

        return response()->json($formattedResults);
    }
    
    public function detail(
        Request $request,
        SearchNoshiFormatInterface $search,
    ) {
        $noshiFormatId = $request->route('noshi_format_id');

        // noshi_format_id は必須です
        if (!isset($noshiFormatId)) {
            return response()->json([
                'error' => 'noshi_format_id is required',
            ], 400);
        }

        $conditions = [
            'm_noshi_format_id' => $noshiFormatId,
        ];

        // モジュール
        $results = $search->execute($conditions);
        
        // $destination が 1件の配列ならば、最初の要素を返す、それ以外はエラー
        if (count($results) === 1) {
            $result = $results[0];
        } else {
            return response()->json([
                'error' => 'm_ami_page is not found',
            ], 404);
        }

        // 必要な項目のみ抽出する
        $result = collect($result)->only([
            "m_noshi_format_id",
            "m_noshi_id",
            "noshi_format_name",
        ]);

        return response()->json($result->toArray());
    }
}
