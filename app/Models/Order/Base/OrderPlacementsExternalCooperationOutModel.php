<?php


namespace App\Models\Order\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderPlacementsExternalCooperationOutModel
 * 
 * @package App\Models
 */
class OrderPlacementsExternalCooperationOutModel extends Model
{
    protected $table = 'w_order_placements_external_cooperation_out';
    protected $primaryKey = 'w_order_placements_external_cooperation_out_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
