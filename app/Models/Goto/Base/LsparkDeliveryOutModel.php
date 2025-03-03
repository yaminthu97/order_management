<?php


namespace App\Models\Goto\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class LsparkDeliveryOutModel
 * 
 * @package App\Models
 */
class LsparkDeliveryOutModel extends Model
{
    protected $table = 'w_lspark_delivery_out';
    protected $primaryKey = 'w_lspark_delivery_out_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
