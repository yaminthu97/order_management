<?php


namespace App\Models\Common\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DeliveryTimeHopeModel
 *
 * @package App\Models
 */
class DeliveryTimeHopeModel extends Model
{
    protected $table = 'm_delivery_time_hope';
    protected $primaryKey = 'm_delivery_time_hope_id';
    protected $connection = 'global';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
