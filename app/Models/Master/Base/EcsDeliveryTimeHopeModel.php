<?php


namespace App\Models\Master\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EcsDeliveryTimeHopeModel
 *
 * @package App\Models
 */
class EcsDeliveryTimeHopeModel extends Model
{
    protected $table = 'm_ecs_delivery_time_hope';
    protected $primaryKey = 'm_ecs_delivery_time_hope_id';

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
     * ECSマスタとのリレーション
     */
    public function ecs()
    {
        return $this->belongsTo(\App\Models\Master\Base\EcsModel::class, 'm_ecs_id', 'm_ecs_id');
    }

    /**
     * 配送方法マスタとのリレーション
     */
    public function deliveryType()
    {
        return $this->belongsTo(\App\Models\Master\Base\DeliveryTypeModel::class, 'm_delivery_id', 'm_delivery_type_id');
    }

    /**
     * 配送方法-希望時間帯設定とのリレーション
     */
    public function deliveryTimeHope()
    {
        return $this->belongsTo(\App\Models\Master\Base\DeliveryTimeHopeMapModel::class, 'm_delivery_time_hope_map_id', 'm_delivery_time_hope_map_id');
    }
}
