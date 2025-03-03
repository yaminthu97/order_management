<?php

namespace App\Modules\Sample\Base;

use App\Enums\ItemNameType;
use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Master\Base\ItemnameTypeModel;
use App\Models\Master\Base\PrefecturalModel;
use Illuminate\Database\Eloquent\Collection;

class GetCustomerRankSample implements GetCustomerRankSampleInterface
{
    public function execute(): Collection
    {
        ModuleStarted::dispatch(__CLASS__, []);

        try {
            $query = ItemnameTypeModel::query();

            // 項目名称マスタから顧客ランクを取得
            $query->where('m_itemname_type',  ItemNameType::CustomerRank->value);
            $query->where('delete_flg', 0);

            $query->orderBy('m_itemname_type_sort', 'asc');

            $result = $query->get();
        } catch (\Exception $e) {
            ModuleFailed::dispatch(__CLASS__, [], $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, $result->toArray());
        return $result;
    }
}
