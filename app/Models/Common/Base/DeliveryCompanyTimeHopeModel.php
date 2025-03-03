<?php


namespace App\Models\Common\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DeliveryCompanyTimeHopeModel
 *
 * @package App\Models
 */
class DeliveryCompanyTimeHopeModel extends Model
{
    protected $table = 'm_delivery_company_time_hope';
    protected $primaryKey = 'm_delivery_company_time_hope_id';
    protected $connection = 'global';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    /**
     * 配送会社とのリレーション
     */
    public function deliveryCompany()
    {
        return $this->belongsTo(\App\Models\Common\Base\DeliveryCompanyModel::class, 'm_delivery_company_id', 'm_delivery_company_id');
    }

    /**
     * 配送希望時間とのリレーション
     */
    public function deliveryTimeHope()
    {
        return $this->belongsTo(\App\Models\Common\Base\DeliveryTimeHopeModel::class, 'm_delivery_time_hope_id', 'm_delivery_time_hope_id');
    }
}
