<?php


namespace App\Models\Goto\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DeliveryExternalCooperationInModel
 * 
 * @package App\Models
 */
class DeliveryExternalCooperationInModel extends Model
{
    protected $table = 'w_delivery_external_cooperation_in';
    protected $primaryKey = 'w_delivery_external_cooperation_in_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
