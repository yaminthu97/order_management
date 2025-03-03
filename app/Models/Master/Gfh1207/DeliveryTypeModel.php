<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Master\Gfh1207;

use Illuminate\Database\Eloquent\Casts\Attribute;
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

    protected $table = 'm_delivery_types';
    protected $primaryKey = 'm_delivery_types_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    public const CREATED_AT = 'entry_timestamp';
    public const UPDATED_AT = 'update_timestamp';

    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }

    /**
     * 更新ユーザ
     */
    public function updateOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'update_operator_id', 'm_operators_id');
    }

    public function deliveryUniqueSettingSeino()
    {
        return $this->hasOne(\App\Models\Master\Base\DeliveryUniqueSettingSeinoModel::class, 'm_delivery_types_id', 'm_delivery_types_id');
    }

    public function deliveryUniqueSettingYamato()
    {
        return $this->hasOne(\App\Models\Master\Base\DeliveryUniqueSettingYamatoModel::class, 'm_delivery_types_id', 'm_delivery_types_id');
    }

    /**
     * 削除フラグの表示文字列
     */
    public function displayDeleteFlg(): Attribute
    {// Accessor to display the label for delete_flg
        return Attribute::make(function ($value, $attribute) {
            return \App\Enums\DeleteFlg::tryFrom($attribute['delete_flg'])?->label();
        });
    }

    public function displayDeliverType(): Attribute
    {// Accessor to display the label for delete_flg
        return Attribute::make(function ($value, $attribute) {
            return \App\Modules\Master\Gfh1207\Enums\DeliveryCompanyEnum::tryFrom($attribute['delivery_type'])?->label();
        });
    }

}
