<?php


namespace App\Models\Order\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderPlacementsHdrModel
 * 
 * @package App\Models
 */
class OrderPlacementsHdrModel extends Model
{
    protected $table = 't_order_placements_hdr';
    protected $primaryKey = 't_order_placements_id';

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
     * 仕入先マスタとのリレーション
     */
    public function supplier()
    {
        return $this->belongsTo(\App\Models\Master\Base\SupplierModel::class, 'm_supplier_id', 'm_suppliers_id');
    }

    /**
     * 倉庫マスタとのリレーション
     */
    public function warehouse()
    {
        return $this->belongsTo(\App\Models\Warehouse\Base\WarehouseModel::class, 'm_warehouse_id', 'm_warehouse_id');
    }

    /*
     * 発注担当者
     */
    public function orderPlacementsOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'order_placements_operator_id', 'm_operators_id');
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
