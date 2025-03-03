<?php

namespace App\Http\Controllers\Order\Api;

use App\Modules\Common\Base\SearchNoshiNamingPatternInterface;
use App\Modules\Common\Base\SearchNoshiDetailInterface;
use Illuminate\Http\Request;

class NoshiNamingPatternController
{
    public function search(
        Request $request,
        SearchNoshiDetailInterface $search,
    ) {
        $noshiFormatId = $request->route('noshi_format_id');

        // noshiFormastId は必須です
        if (!isset($noshiFormatId)) {
            return response()->json([
                'error' => 'noshi_format_id is required',
            ], 400);
        }

        // 検索
        $conditions = [
            'm_noshi_format_id' => $noshiFormatId,
        ];
        $options = [
            'with' => ['noshi', 'noshiNamingPattern'],
        ];
        // 検索
        $results = $search->execute($conditions, $options);

        // 必要な項目のみ抽出する
        $formattedResults = [];
        foreach ($results->toArray() as $result) {
            $formattedResults[] = [
                'm_noshi_detail_id' => $result['m_noshi_detail_id'],
                'm_noshi_id' => $result['m_noshi_id'],
                'm_noshi_format_id' => $result['m_noshi_format_id'],
                'm_noshi_naming_pattern_id' => $result['m_noshi_naming_pattern_id'],
                'template_file_name' => $result['template_file_name'],
                'noshi_type' => $result['noshi']['noshi_type'],
                'attachment_item_group_id' => $result['noshi']['attachment_item_group_id'],
                'omotegaki' => $result['noshi']['omotegaki'],
                'noshi_cd' => $result['noshi']['noshi_cd'],
                'pattern_name' => $result['noshi_naming_pattern']['pattern_name'],
                'pattern_code' => $result['noshi_naming_pattern']['pattern_code'],
                'company_name_count' => $result['noshi_naming_pattern']['company_name_count'],
                'section_name_count' => $result['noshi_naming_pattern']['section_name_count'],
                'title_count' => $result['noshi_naming_pattern']['title_count'],
                'f_name_count' => $result['noshi_naming_pattern']['f_name_count'],
                'name_count' => $result['noshi_naming_pattern']['name_count'],
                'ruby_count' => $result['noshi_naming_pattern']['ruby_count'],
            ];
        }

        return response()->json($formattedResults);
    }
    
    public function detail(
        Request $request,
        SearchNoshiNamingPatternInterface $search,
    ) {
        $noshiNamingPatternId = $request->route('noshi_naming_pattern_id');

        // noshi_naming_pattern_id は必須です
        if (!isset($noshiNamingPatternId)) {
            return response()->json([
                'error' => 'noshi_naming_pattern_id is required',
            ], 400);
        }

        $conditions = [
            'm_noshi_naming_pattern_id' => $noshiNamingPatternId ?? null,
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
            "m_noshi_naming_pattern_id",
            "noshi_format_name",
            "pattern_name",
            "pattern_code",
            "company_name_count",
            "section_name_count",
            "title_count",
            "f_name_count",
            "name_count",
            "ruby_count",
        ]);

        return response()->json($result->toArray());
    }
}
