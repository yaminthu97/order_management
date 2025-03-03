<?php

namespace App\Modules\Master\Base;

use App\Models\Warehouse\Base\WarehouseModel;
use App\Models\Master\Base\YmstpostModel;
use App\Models\Master\Base\YmsttimeModel;
use App\Models\Master\Base\PostalCodeModel;
use App\Models\Master\Base\PrefecturalModel;
use App\Models\Master\Base\DeliveryReadtimeModel;
use Illuminate\Database\Eloquent\Builder;
use App\Modules\Master\Base\GetYmstTimeInterface;
use InvalidArgumentException;

class GetYmstTime implements GetYmstTimeInterface
{

    /**
     * 店舗情報取得
     *
     * @param $warehouseId 倉庫ID
     * @param $deliveryZipCode 配送先郵便番号
     * @return YmsttimeModel
     */
    public function execute($warehouseId, $deliveryZipCode): YmsttimeModel
    {
        // $warehouseId から倉庫の郵便番号を取得
        $warehouse = WarehouseModel::query()->find($warehouseId);
        if (!$warehouse) {
            throw new InvalidArgumentException('倉庫が見つかりません');
        }
        $warehouseZipCode = $warehouse->warehouse_postal;

        // 倉庫の郵便番号からYmstpostを検索し、仕分けコード1 cls_code1 の先頭3桁を取得
        $ymstpost = YmstpostModel::query()->where('zip_code', $warehouseZipCode)->first();
        if (!$ymstpost) {
            throw new InvalidArgumentException('倉庫の郵便番号が見つかりません');
        }
        $warehouseClsCode = substr($ymstpost->cls_code1, 0, 3);
        
        // 配送先郵便番号からYmstpostを検索し、仕分けコード1 cls_code1 の先頭5桁を取得
        $ymstpost = YmstpostModel::query()->where('zip_code', $deliveryZipCode)->first();
        if (!$ymstpost) {
            throw new InvalidArgumentException('配送先郵便番号が見つかりません');
        }
        $deliveryClsCode = substr($ymstpost->cls_code1, 0, 5);

        // warehouseClsCode = from_base, deliveryClsCode = cls_code1 でYmsttimeを検索
        $ymsttime = YmsttimeModel::query()
            ->where('from_base', $warehouseClsCode)
            ->where('cls_code1', $deliveryClsCode)
            ->first();
        if (!$ymsttime) {
            throw new InvalidArgumentException('配送時間が見つかりません');
        }

        // 配送先郵便番号からリードタイムを取得
        $postal_code = PostalCodeModel::query()->where('postal_code', $deliveryZipCode)->first();
        if (!$postal_code) {
            throw new InvalidArgumentException('郵便番号が見つかりません');
        }
        $prefectural = PrefecturalModel::query()->where('prefectual_name', $postal_code->postal_prefecture)->first();
        if (!$prefectural) {
            throw new InvalidArgumentException('都道府県が見つかりません');
        }
        $readtimeModel = DeliveryReadtimeModel::query()
            ->where('m_prefecture_id', $prefectural->m_prefectural_id)
            ->where('m_warehouses_id', $warehouseId)
            ->where('delete_flg', 0)
            ->first();
        if (!$readtimeModel) {
            $readtimeModel = DeliveryReadtimeModel::query()
                ->where('m_prefecture_id', '99')
                ->where('m_warehouses_id', $warehouseId)
                ->where('delete_flg', 0)
                ->first();
        }
        if (!$readtimeModel) {
            throw new InvalidArgumentException('リードタイムが見つかりません');
        }

        if ($readtimeModel->master_pack_apply_flg == 1) {
            $ymsttime->delivery_days = $ymsttime->delivery_days + $readtimeModel->delivery_readtime;
        } else {
            $ymsttime->delivery_days = $readtimeModel->delivery_readtime;
        }

        // YmsttimeModel を返却
        return $ymsttime;
    }
}
