<?php

namespace App\Models\Order\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryDtlSkuModel extends Model
{
    use HasFactory;

        /**
	 * テーブル名称
	 *
	 * @var string
     */
    protected $table = 't_delivery_dtl_sku';

    /**
	 * テーブルの主キー
	 *
	 * @var string
	 */
    protected $primaryKey = 't_delivery_dtl_sku_id';

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
     * 受注配送先とのリレーション
     */
    public function orderDestination()
    {
        return $this->belongsTo(\App\Models\Order\Base\OrderDestinationModel::class, 't_order_destination_id', 't_order_destination_id');
    }

    /**
     * 受注明細とのリレーション
     */
    public function orderDtl()
    {
        return $this->belongsTo(\App\Models\Order\Base\OrderDtlModel::class, 't_order_dtl_id', 't_order_dtl_id');
    }

    /**
     * 受注明細SKUとのリレーション
     */
    public function orderDtlSku()
    {
        return $this->belongsTo(\App\Models\Order\Base\OrderDtlSkuModel::class, 't_order_dtl_sku_id', 't_order_dtl_sku_id');
    }

    /*
     * ECサイトマスタテーブル
     */
    public function ecs()
    {
        return $this->belongsTo(\App\Models\Master\Base\EcsModel::class, 'm_ecs_id', 'm_ecs_id');
    }
    
    /**
     * 商品SKUマスタとのリレーション
     */
    public function amiSku()
    {
        return $this->belongsTo(\App\Models\Ami\Base\AmiSkuModel::class, 'item_id', 'm_ami_sku_id');
    }

    /**
     * 倉庫マスタとのリレーション
     */
    public function warehouse()
    {
        return $this->belongsTo(\App\Models\Warehouse\Base\WarehouseModel::class, 'm_warehouse_id', 'm_warehouse_id');
    }

    /**
     * 出荷基本とのリレーション
     */
    public function deliveryHdr()
    {
        return $this->belongsTo(\App\Models\Order\Base\DeliveryHdrModel::class, 't_delivery_hdr_id', 't_delivery_hdr_id');
    }

    /**
     * 出荷明細とのリレーション
     */
    public function deliveryDtl()
    {
        return $this->belongsTo(\App\Models\Order\Base\DeliveryDtlModel::class, 't_delivery_dtl_id', 't_delivery_dtl_id');
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
