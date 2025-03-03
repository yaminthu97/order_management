<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Master\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DeliveryTypeModel
 *
 * @package App\Models
 */
class DeliveryTypeModel extends Model
{
    use HasFactory;
    protected $table = 'm_delivery_types';
    protected $primaryKey = 'm_delivery_types_id';

    protected $fillable = [
        'm_account_id',
        'delete_flg',
        'm_delivery_type_name',
        'm_delivery_type_code',
        'delivery_type',
        'm_delivery_sort',
        'delivery_date_output_type',
        'delivery_date_create_type',
        'deferred_payment_delivery_id',
        'standard_fee',
        'frozen_fee',
        'chilled_fee',
        'delivery_tracking_url',
        'entry_operator_id',
        'entry_timestamp',
        'update_operator_id',
        'update_timestamp',
    ];
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


}
