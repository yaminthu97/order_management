<?php


namespace App\Models\Master\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EcsDeliveryMapAmazonModel
 *
 * @package App\Models
 */
class EcsDeliveryMapAmazonModel extends Model
{
	protected $table = 'm_ecs_delivery_map_amazon';
	protected $primaryKey = 'm_ecs_delivery_map_amazon_id';
	public $timestamps = false;

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
        return $this->belongsTo(\App\Models\Master\Base\DeliveryTypeModel::class, 'm_delivery_type_id', 'm_delivery_type_id');
    }

    /**
     * 配送会社マスタとのリレーション
     */
    public function deliveryCompany()
    {
        return $this->belongsTo(\App\Models\Common\Base\DeliveryCompanyModel::class, 'm_delivery_company_id', 'm_delivery_company_id');
    }
}
