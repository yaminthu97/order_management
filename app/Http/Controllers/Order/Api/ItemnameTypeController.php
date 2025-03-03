<?php

namespace App\Http\Controllers\Order\Api;

use App\Modules\Master\Base\Enums\AttentionTypeInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Enums\ItemNameType;

use App\Modules\Master\Base\SearchItemNameTypesInterface;
use App\Services\EsmSessionManager;

class ItemnameTypeController
{
    public function info(
        Request $request,
        SearchItemNameTypesInterface $searchItemnameType,
        EsmSessionManager $esmSessionManager
        //ProgressType $checkType,
    ) {
        $itemnameTypeId = $request->route('id');

        // 許可する項目名称区分
        $itemnameTypeList = [
            ItemNameType::AttachmentCategory->value, // 12: 付属品カテゴリ
            ItemNameType::AttachmentGroup->value, // 13: 付属品グループ
            ItemNameType::NoshiSize->value, // 14: 熨斗サイズ
        ];

        // 許可する項目名称区分以外の場合はエラー
        if (!in_array($itemnameTypeId, $itemnameTypeList)) {
            return response()->json([
                'error' => 'itemname_type_id is invalid',
            ], 400);
        }

        // モジュール
        $results = $searchItemnameType->execute([
            'm_account_id' => $esmSessionManager->getAccountId(),
            'm_itemname_type' => $itemnameTypeId,
        ]);

        // $results の配列から必要な項目のみ抽出する
        $results = collect($results)->map(function($result) {
            return collect($result)->only([
                "m_itemname_types_id",
                "m_itemname_type",
                "m_itemname_type_code",
                "m_itemname_type_name",
                "m_itemname_type_sort",
            ]);
        });
        return response()->json($results->toArray());
    }
}
