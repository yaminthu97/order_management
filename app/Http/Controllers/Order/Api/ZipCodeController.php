<?php

namespace App\Http\Controllers\Order\Api;

use App\Modules\Master\Base\SearchPostalCodeInterface;
use Illuminate\Http\Request;

class ZipCodeController
{
    public function detail(
        Request $request,
        SearchPostalCodeInterface $search,
    ) {
        $zipcode = $request->route('zipcode');

        // zipcode は必須です
        if (!isset($zipcode)) {
            return response()->json([
                'error' => 'zipcode is required',
            ], 400);
        }

        // モジュール
        $results = $search->execute(['postal_code' => $zipcode]);
        
        // $results が1件以上存在すれば最初の要素を$resultとする、0件ならばはエラーとする
        if (count($results) === 0) {
            return response()->json([
                'error' => 'zipcode is not found',
            ], 404);
        }
        $result = $results[0];

        // 必要な項目のみ抽出する
        $result = collect($result)->only([
            "postal_code",
            "postal_prefecture",
            "postal_city",
            "postal_town",
            "postal_prefecture_kana",
            "postal_city_kana",
            "postal_town_kana",
        ]);

        return response()->json($result->toArray());
    }
}
