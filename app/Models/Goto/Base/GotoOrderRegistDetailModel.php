<?php


namespace App\Models\Goto\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GotoOrderRegistDetailModel
 *
 * @package App\Models
 */
class GotoOrderRegistDetailModel extends Model
{
    protected $table = 't_goto_order_regist_detail';
    protected $primaryKey = 't_goto_order_regist_detail_id';
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
     * 注文登録受付管理とのリレーション
     */
    public function gotoOrderRegistRecept()
    {
        return $this->belongsTo(\App\Models\Goto\Base\GotoOrderRegistReceptModel::class, 't_goto_order_regist_recept_id', 't_goto_order_regist_recept_id');
    }

    /**
     * 登録ユーザ
     */
    public function entryOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'entry_operator_id', 'm_operators_id');
    }
}
