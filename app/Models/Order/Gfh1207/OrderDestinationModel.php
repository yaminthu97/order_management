<?php

namespace App\Models\Order\Gfh1207;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 受注送付先モデル
 */
class OrderDestinationModel extends Model
{
    use HasFactory;

    /**
     * テーブル名称
     *
     * @var string
     */
    protected $table = 't_order_destination';


    /**
     * テーブルの主キー
     *
     * @var string
     */
    protected $primaryKey = 't_order_destination_id';

    protected $fillable = [
        't_order_destination_id',
        'm_account_id',
        't_order_hdr_id',
        'order_destination_seq',
        'destination_alter_flg',
        'destination_tel',
        'destination_postal',
        'destination_address1',
        'destination_address2',
        'destination_address3',
        'destination_address4',
        'destination_company_name',
        'destination_division_name',
        'destination_name_kana',
        'destination_name',
        'm_delivery_type_id',
        'deli_hope_date',
        'deli_hope_time_name',
        'deli_hope_time_cd',
        'm_delivery_time_hope_id',
        'shipping_fee',
        'payment_fee',
        'wrapping_fee',
        'deli_plan_date',
        'gift_message',
        'gift_wrapping',
        'campaign_flg',
        'nosi_type',
        'nosi_name',
        'sender_name',
        'invoice_comment',
        'picking_comment',
        'pending_flg',
        'total_deli_flg',
        'total_temperature_zone_type',
        'partial_deli_flg',
        'ec_destination_num',
        'gp1_type',
        'gp2_type',
        'gp3_type',
        'gp4_type',
        'gp5_type',
        'entry_operator_id',
        'update_operator_id'
    ];

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    public const CREATED_AT = 'entry_timestamp';
    public const UPDATED_AT = 'update_timestamp';

    /**
     * 受注基本とのリレーション
     */
    public function orderHdr()
    {
        return $this->belongsTo(\App\Models\Order\Gfh1207\OrderHdrModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }

    /**
     * 受注明細マスタとのリレーション
     */
    public function orderDtl()
    {
        return $this->hasMany(\App\Models\Order\Base\OrderDtlModel::class, 't_order_destination_id', 't_order_destination_id');
    }

    /**
     * 出荷基本マスタとのリレーション
     */
    public function deliHdr()
    {
        return $this->hasMany(\App\Models\Order\Base\DeliHdrModel::class, 't_order_destination_id', 't_order_destination_id');
    }

    /**
     * 受注明細とのリレーション
     */
    public function orderDtls()
    {
        return $this->hasMany(\App\Models\Order\Gfh1207\OrderDtlModel::class, 't_order_destination_id', 't_order_destination_id');
    }

    /**
     * 送り状実績とのリレーション
     */
    public function shippingLabels()
    {
        return $this->hasMany(\App\Models\Order\Base\ShippingLabelModel::class, 't_order_destination_id', 't_order_destination_id');
    }

    /**
     * Relationship with customer base
     */
    public function customer()
    {
        return $this->hasOneThrough(\App\Models\Cc\Base\CustModel::class, \App\Models\Order\Base\OrderHdrModel::class, 't_order_hdr_id', 'm_cust_id', 't_order_destination_id', 't_order_hdr_id');
    }

    /*
     * 配送方法マスタとのリレーション
     */
    public function deliveryType()
    {
        return $this->belongsTo(\App\Models\Master\Base\DeliveryTypeModel::class, 'm_delivery_type_id', 'm_delivery_types_id');
    }

    /**
     * 出荷基本マスタとのリレーション (one to one)
     */
    public function deliHdrOne()
    {
        return $this->hasOne(\App\Models\Order\Base\DeliHdrModel::class, 't_order_destination_id', 't_order_destination_id');
    }

    /**
     * 受注明細とのリレーション
     */
    public function orderDtlsWithDestinationSeq()
    {
        return $this->hasMany(OrderDtlModel::class, 'order_destination_seq', 'order_destination_seq');
    }

    /**
     * 更新ユーザ
     */
    public function updateOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'update_operator_id', 'm_operators_id');
    }

    /**
     * 出荷明細とのリレーション
     */
    public function deliveryDtl()
    {
        return $this->hasMany(\App\Models\Order\Base\DeliveryDtlModel::class, 't_order_destination_id', 't_order_destination_id');
    }

    /**
     * 受注明細SKUとのリレーション
     */
    public function orderDtlSku()
    {
        return $this->hasMany(\App\Models\Order\Base\OrderDtlSkuModel::class, 't_order_destination_id', 't_order_destination_id');

    }
}
