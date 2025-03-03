<?php


namespace App\Models\Order\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderDestinationModel
 * 
 * @package App\Models
 */
class OrderDestinationModel extends Model
{
    protected $table = 't_order_destination';
    protected $primaryKey = 't_order_destination_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

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
        'ec_destination_num'
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

    /*
     * 配送方法マスタとのリレーション
     */
    public function deliveryType()
    {
        return $this->belongsTo(\App\Models\Master\Base\DeliveryTypeModel::class, 'm_delivery_type_id', 'm_delivery_type_id');
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