<?php

namespace App\Models\Master\Gfh1207;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DeliveryFeeModel
 *
 * @package App\Models
 */
class DeliveryFeeModel extends Model
{
    protected $table = 'm_delivery_fees';
    protected $primaryKey = 'm_delivery_fee_id';

    protected $dateFormat = 'Y-m-d H:i:s';

    public const CREATED_AT = 'entry_timestamp';
    public const UPDATED_AT = 'update_timestamp';

    protected $fillable = [
        'm_account_id',
        'delete_flg',
        'm_warehouses_id',
        'm_delivery_types_id',
        'm_prefecture_id',
        'delivery_fee',
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
}
