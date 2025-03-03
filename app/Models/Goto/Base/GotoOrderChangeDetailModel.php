<?php


namespace App\Models\Goto\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GotoOrderChangeDetailModel
 *
 * @package App\Models
 */
class GotoOrderChangeDetailModel extends Model
{
    protected $table = 't_goto_order_change_detail';
    protected $primaryKey = 't_goto_order_change_detail_id';
    protected $connection = 'global';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    /*
     * 注文変更受付とのリレーション
     */
    public function gotoOrderChangeRecept()
    {
        return $this->belongsTo(\App\Models\Goto\Base\GotoOrderChangeReceptModel::class, 't_goto_order_change_recept_id', 't_goto_order_change_recept_id');
    }

    /**
     * 登録ユーザ
     */
    public function entryOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'entry_operator_id', 'm_operators_id');
    }
}
