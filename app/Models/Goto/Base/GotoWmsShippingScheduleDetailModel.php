<?php


namespace App\Models\Goto\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GotoWmsShippingScheduleDetailModel
 *
 * @package App\Models
 */
class GotoWmsShippingScheduleDetailModel extends Model
{
    protected $table = 't_goto_wms_shipping_schedule_detail';
    protected $primaryKey = 't_goto_wms_shipping_schedule_detail_id';
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
     * 登録ユーザ
     */
    public function entryOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'entry_operator_id', 'm_operators_id');
    }
}
