<?php


namespace App\Models\Goto\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GotoWmsReceivingScheduleReceptModel
 *
 * @package App\Models
 */
class GotoWmsReceivingScheduleReceptModel extends Model
{
    protected $table = 't_goto_wms_receiving_schedule_recept';
    protected $primaryKey = 't_goto_wms_receiving_schedule_recept_id';
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
     * 企業アカウントとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }

    /**
     * 倉庫マスタとのリレーション
     */
    public function warehouse()
    {
        return $this->belongsTo(\App\Models\Warehouse\Base\WarehouseModel::class, 'm_warehouses_id', 'm_warehouse_id');
    }

    /**
     * 仕入先マスタとのリレーション
     */
    public function supplier()
    {
        return $this->belongsTo(\App\Models\Master\Base\SupplierModel::class, 'm_suppliers_id', 'm_suppliers_id');
    }

    /*
     * 利用者
     */
    public function requestOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'request_operator_id', 'm_operators_id');
    }

    /**
     * 登録ユーザ
     */
    public function entryOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'entry_operator_id', 'm_operators_id');
    }

    /**
     * 更新ユーザ
     */
    public function updateOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'update_operator_id', 'm_operators_id');
    }
}
