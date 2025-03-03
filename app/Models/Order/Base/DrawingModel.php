<?php


namespace App\Models\Order\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DrawingModel
 * 
 * @package App\Models
 */
class DrawingModel extends Model
{
    protected $table = 'w_drawing';
    protected $primaryKey = 'w_drawing_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
