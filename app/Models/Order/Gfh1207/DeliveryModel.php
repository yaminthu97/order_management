<?php

namespace App\Models\Order\Gfh1207;

use App\Models\Master\Base\DeliveryTypeModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 出荷基本モデル
 */
class DeliveryModel extends Model
{
    use HasFactory;

    /**
	 * テーブル名称
	 *
	 * @var string
     */
    protected $table = 't_delivery_hdr';

    /**
	 * テーブルの主キー
	 *
	 * @var string
	 */
    protected $primaryKey = 't_delivery_hdr_id';

    protected $fillable = [
        't_order_hdr_id',
        't_order_destination_id',
        'm_deli_type_id',
        't_order_hdr_id',
        'deli_decision_date',
        'deli_package_vol',
        'invoice_num1',
        'invoice_num2',
        'invoice_num3',
        'invoice_num4',
        'invoice_num5',
    ];

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    /**
     * 企業アカウントマスタとのリレーション
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
        return $this->belongsTo(OrderModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }

    /**
     * 受注送付先とのリレーション
     */
    public function orderDestination()
    {
        return $this->belongsTo(OrderDestinationModel::class, 't_order_destination_id', 't_order_destination_id');
    }


    /**
     * 出荷詳細とのリレーション
     */
    public function deliveryDetails()
    {
        return $this->hasMany(DeliveryDetailModel::class, 't_delivery_hdr_id', 't_delivery_hdr_id');
    }

    /**
     * 送り状実績とのリレーション
     */
    public function shippingLabels()
    {
        return $this->hasMany(ShippingLabelModel::class, 't_delivery_hdr_id', 't_delivery_hdr_id');
    }

    /**
     * 配送方法マスタとのリレーション
     */
    public function deliveryType()
    {
        return $this->belongsTo(DeliveryTypeModel::class, 'm_deli_type_id', 'm_delivery_types_id');
    }
}
