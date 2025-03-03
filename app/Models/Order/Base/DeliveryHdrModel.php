<?php


namespace App\Models\Order\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DeliveryHdrModel
 * 
 * @package App\Models
 */
class DeliveryHdrModel extends Model
{
	protected $table = 't_delivery_hdr';
	protected $primaryKey = 't_delivery_hdr_id';
	public $incrementing = false;
	public $timestamps = false;

    /*
     * 企業アカウントとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }

    /**
     * 受注基本とのリレーション
     */
    public function orderHdr()
    {
        return $this->belongsTo(\App\Models\Order\Base\OrderHdrModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }

    /**
     * 顧客とのリレーション
     */
    public function cust()
    {
        return $this->belongsTo(\App\Models\Cc\Base\CustModel::class, 'm_cust_id', 'm_cust_id');
    }

    /**
     * 受注担当者
     */
    public function orderOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'order_operator_id', 'm_operators_id');
    }

    /*
     * ECサイトマスタとのリレーション
     */
    public function ecs()
    {
        return $this->belongsTo(\App\Models\Master\Base\EcsModel::class, 'm_ecs_id', 'm_ecs_id');
    }

    /**
     * 受注配送先とのリレーション
     */
    public function orderDestination()
    {
        return $this->belongsTo(\App\Models\Order\Base\OrderDestinationModel::class, 't_order_destination_id', 't_order_destination_id');
    }

    /*
     * 配送方法マスタとのリレーション
     */
    public function deliveryType()
    {
        return $this->belongsTo(\App\Models\Master\Base\DeliveryTypeModel::class, 'm_deli_type_id', 'm_delivery_types_id');
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

    /**
     * 支払方法マスタとのリレーション
     */
    public function paymentTypes()
    {
        return $this->belongsTo(\App\Models\Master\Base\PaymentTypeModel::class, 'm_payment_types_id', 'm_payment_types_id');
    }

    /**
     * 送付マスタとのリレーション
     */
    public function destination()
    {
        return $this->belongsTo(\App\Models\Order\Base\DestinationModel::class, 'destination_id', 'm_destination_id');
    }

    /**
     * 配送方法-希望時間帯設定とのリレーション
     */
    public function deliveryTimeHope()
    {
        return $this->belongsTo(\App\Models\Common\Base\DeliveryTimeHopeModel::class, 'm_delivery_time_hope_id', 'm_delivery_time_hope_id');
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

    /**
     * 取消ユーザ
     */
    public function cancelOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'cancel_operator_id', 'm_operators_id');
    }
}
