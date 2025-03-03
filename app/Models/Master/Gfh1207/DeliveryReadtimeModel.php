<?php

namespace App\Models\Master\Gfh1207;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DeliveryReadtimeModel
 *
 * @package App\Models
 */
class DeliveryReadtimeModel extends Model
{
    protected $table = 'm_delivery_readtime';
    protected $primaryKey = 'm_delivery_readtime_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    public const CREATED_AT = 'entry_timestamp';
    public const UPDATED_AT = 'update_timestamp';

    protected $fillable = [
        'm_account_id',
        'delete_flg',
        'm_warehouses_id',
        'm_delivery_types_id',
        'm_prefecture_id',
        'delivery_readtime',
        'master_pack_apply_flg',
        'entry_operator_id',
        'entry_timestamp',
        'update_operator_id',
        'update_timestamp'
    ];

    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }

    /**
     * 倉庫とのリレーション
     */
    public function warehouse()
    {
        return $this->belongsTo(\App\Models\Warehouse\Base\WarehouseModel::class, 'm_warehouse_id', 'm_warehouse_id');
    }

    /**
     * 配送方法とのリレーション
     */
    public function deliveryType()
    {
        return $this->belongsTo(\App\Models\Master\Base\DeliveryTypeModel::class, 'm_delivery_type_id', 'm_delivery_type_id');
    }
}
