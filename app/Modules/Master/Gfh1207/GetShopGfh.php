<?php

namespace App\Modules\Master\Gfh1207;

use App\Models\Master\Base\ShopGfhModel;
use App\Modules\Master\Base\GetShopGfhInterface;
use Exception;

class GetShopGfh implements GetShopGfhInterface
{
    public function execute()
    {
        try {
            // 基本設定IDが最大のレコードを取得
            $model = ShopGfhModel::query()
                ->orderBy('m_shop_gfh_id', 'desc')
                ->first();

            $query = $model ? $model->toArray() : [];

            return $query;
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }
}
