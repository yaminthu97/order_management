<?php


namespace App\Models\Order\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderPlacementsExternalCooperationModel
 * 
 * @package App\Models
 */
class OrderPlacementsExternalCooperationModel extends Model
{
    protected $table = 'order_placements_external_cooperation';
    protected $primaryKey = 'order_placements_external_cooperation_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
