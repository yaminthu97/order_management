<?php


namespace App\Models\Warehouse\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class WarehouseItemModel
 * 
 * @package App\Models
 */
class WarehouseItemModel extends Model
{
    protected $table = 't_warehouse_items';
    protected $primaryKey = 't_warehouse_items_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

	/**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }

	/**
     * 倉庫マスタとのリレーション
     */
    public function warehouse()
    {
        return $this->belongsTo(\App\Models\Warehouse\Base\WarehouseModel::class, 'm_warehouses_id', 'm_warehouses_id');
    }

    /**
     * SKUマスタとのリレーション
     */
    public function sku()
    {
        return $this->belongsTo(\App\Models\Ami\Base\AmiSkuModel::class, 'm_ami_sku_id', 'items_id');
    }
}
