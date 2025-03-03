<?php


namespace App\Models\Order\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderDtlModel
 * 
 * @package App\Models
 */
class OrderDtlModel extends Model
{
    protected $table = 't_order_dtl';
    protected $primaryKey = 't_order_dtl_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    protected $fillable = [
		't_order_dtl_id',
		'm_account_id',
		't_order_hdr_id',
		't_order_destination_id',
		'order_destination_seq',
		'order_dtl_seq',
		'ecs_id',
		'sell_id',
		'sell_cd',
		'sell_option',
		'sell_name',
		'order_dtl_coupon_id',
		'order_dtl_coupon_price',
		'order_sell_price',
		'order_time_sell_vol',
		'order_sell_vol',
		'tax_rate',
		'tax_price',
		'cancel_operator_id',
		'cancel_timestamp'
    ];

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
     * 受注明細SKUとのリレーション
     */
    public function orderDtlSku()
    {
        return $this->hasMany(\App\Models\Order\Base\OrderDtlSkuModel::class, 't_order_dtl_id', 't_order_dtl_id');
    }

    /*
     * ECサイトマスタとのリレーション
     */
    public function ecs()
    {
        return $this->belongsTo(\App\Models\Master\Base\EcsModel::class, 'ecs_id', 'm_ecs_id');
    }

    /**
     * 商品ページマスタとのリレーション
     */
    public function orderDtl()
    {
        return $this->belongsTo(\App\Models\Order\Base\OrderDtlSkuModel::class, 't_order_dtl_id', 't_order_dtl_id');
    }

    /**
     * 商品ECページマスタとのリレーション
     */
    public function amiEcPage()
    {
        return $this->belongsTo(\App\Models\Ami\Base\AmiEcPageModel::class, 'sell_id', 'm_ami_ec_page_id');
    }
    
    /*
     * 出荷基本とのリレーション
     */
    public function deliveryHdr()
    {
        return $this->belongsTo(\App\Models\Order\Base\DeliveryHdrModel::class, 't_deli_hdr_id', 't_delivery_hdr_id');
    }

    /*
     * 同梱元受注
     */
    public function fromOrder()
    {
        return $this->belongsTo(\App\Models\Order\Base\OrderHdrModel::class, 'bundle_from_order_id', 't_order_hdr_id');
    }

    /*
     * 熨斗
     */
    public function orderDtlNoshi()
    {
        return $this->hasOne(\App\Models\Order\Base\OrderDtlNoshiModel::class, 't_order_dtl_id', 't_order_dtl_id');
    }

    /*
     * 付属品
     */
    public function orderDtlAttachmentItem()
    {
        return $this->hasMany(\App\Models\Order\Base\OrderDtlAttachmentItemModel::class, 't_order_dtl_id', 't_order_dtl_id');
    }

    /*
     * 同梱元明細
     */
    public function fromOrderDtl()
    {
        return $this->belongsTo(\App\Models\Order\Base\OrderDtlModel::class, 'bundle_from_order_id', 't_order_dtl_id');
    }

    /*
     * 項目名称マスタとのリレーション（付属品グループ区分）
     */
    public function itemGroup()
    {
        return $this->belongsTo(\App\Models\Master\Base\ItemnameTypeModel::class, 'attachment_item_group_id', 'm_itemname_types_id');
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

    /**
     * attachment Items
     */
    public function attachmentItems()
    {
        return $this->hasMany(\App\Models\Order\Base\OrderDtlAttachmentItemModel::class, 't_order_dtl_id');
    }
}
