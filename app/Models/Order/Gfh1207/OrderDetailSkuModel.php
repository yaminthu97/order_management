<?php


namespace App\Models\Order\Gfh1207;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderDtlSkuModel
 *
 * @package App\Models
 */
class OrderDetailSkuModel extends Model
{
    use HasFactory;
    protected $table = 't_order_dtl_sku';
    protected $primaryKey = 't_order_dtl_sku_id';

    protected $fillable = [
        'm_account_id',
        't_order_hdr_id',
        't_order_destination_id',
        'order_destination_seq',
        't_order_dtl_id',
        'order_dtl_seq',
        'ecs_id',
        'sell_cd',
        'order_sell_vol',
        'item_id',
        'item_cd',
        'item_vol',
        'm_supplier_id',
        'temperature_type',
        'order_bundle_type',
        'direct_delivery_type',
        'gift_type',
        'item_cost',
        'm_warehouse_id',
        'entry_operator_id',
        'update_operator_id'
    ];

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
     * 受注基本とのリレーション
     */
    public function order()
    {
        return $this->belongsTo(\App\Models\Order\Gfh1207\OrderHdrModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }

    /**
     * 受注配送先とのリレーション
     */
    public function orderDestination()
    {
        return $this->belongsTo(\App\Models\Order\Gfh1207\OrderDestinationModel::class, 't_order_destination_id', 't_order_destination_id');
    }

    /**
     * 受注明細とのリレーション
     */
    public function orderDetail()
    {
        return $this->belongsTo(\App\Models\Order\Gfh1207\OrderDetailModel::class, 't_order_dtl_id', 't_order_dtl_id');
    }

    /*
     * ECサイトマスタテーブル
     */
    public function ecs()
    {
        return $this->belongsTo(\App\Models\Master\Base\EcsModel::class, 'ecs_id', 'm_ecs_id');
    }

    /**
     * 商品SKUマスタとのリレーション
     */
    public function amiSku()
    {
        return $this->belongsTo(\App\Models\Ami\Base\AmiSkuModel::class, 'item_id', 'm_ami_sku_id');
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

    /**
     * 出荷明細とのリレーション
     */
    public function deliveryDtl()
    {
        return $this->belongsTo(\App\Models\Order\Base\DeliveryDtlModel::class, 't_deli_hdr_id', 't_delivery_dtl_id');
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
