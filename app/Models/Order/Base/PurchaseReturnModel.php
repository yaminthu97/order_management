<?php


namespace App\Models\Order\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PurchaseReturnModel
 * 
 * @package App\Models
 */
class PurchaseReturnModel extends Model
{
    protected $table = 't_purchase_return';
    protected $primaryKey = 't_purchase_return_id';

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

    /*
     * 発注とのリレーション
     */
    public function orderPlacements()
    {
        return $this->belongsTo(\App\Models\Order\Base\OrderPlacementsHdrModel::class, 't_order_placements_id', 't_order_placements_id');
    }

    /*
     * 仕入基本情報とのリレーション
     */
    public function purchaseHdr()
    {
        return $this->belongsTo(\App\Models\Order\Base\PurchaseHdrModel::class, 't_purchase_hdr_id', 't_purchase_hdr_id');
    }

    /**
     * 倉庫マスタとのリレーション
     */
    public function warehouse()
    {
        return $this->belongsTo(\App\Models\Warehouse\Base\WarehouseModel::class, 'm_warehouse_id', 'm_warehouse_id');
    }

    /**
     * 仕入先マスタとのリレーション
     */
    public function supplier()
    {
        return $this->belongsTo(\App\Models\Master\Base\SupplierModel::class, 'm_supplier_id', 'm_suppliers_id');
    }

    /*
     * 配送方法マスタとのリレーション
     */
    public function deliveryType()
    {
        return $this->belongsTo(\App\Models\Master\Base\DeliveryTypeModel::class, 'm_delivery_type_id', 'm_delivery_types_id');
    }

    /*
     * 希望時間帯マスタとのリレーション
     */
    public function deliveryTimeHopeMap()
    {
        return $this->belongsTo(\App\Models\Master\Base\DeliveryTimeHopeMapModel::class, 'm_delivery_time_hope_map_id', 'm_delivery_time_hope_map_id');
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
