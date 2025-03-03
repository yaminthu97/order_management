<?php


namespace App\Models\Master\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DeliveryUniqueSettingYuupackModel
 *
 * @package App\Models
 */
class DeliveryUniqueSettingYuupackModel extends Model
{
    protected $table = 'm_delivery_unique_setting_yuupack';
    protected $primaryKey = 'm_delivery_unique_setting_yuupack_id';

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
     * 配送方法マスタとのリレーション
     */
    public function deliveryType()
    {
        return $this->belongsTo(\App\Models\Master\Base\DeliveryTypeModel::class, 'm_delivery_types_id', 'm_delivery_types_id');
    }
}
