<?php

namespace App\Models\Order\Gfh1207;

use App\Enums\ThreeTemperatureZoneTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 送り状実績モデル
 */
class ShippingLabelModel extends Model
{
    use HasFactory;

    /**
	 * テーブル名称
	 *
	 * @var string
     */
    protected $table = 't_shipping_labels';

    /**
	 * テーブルの主キー
	 *
	 * @var string
	 */
    protected $primaryKey = 't_shipping_label_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';


    /**
     * 受注基本とのリレーション
     */
    public function order()
    {
        return $this->belongsTo('App\Models\Order\OrderModel', 't_order_hdr_id', 't_order_hdr_id');
    }

    /**
     * 受注送付先とのリレーション
     */
    public function orderDestination()
    {
        return $this->belongsTo('App\Models\Order\OrderDestinationModel', 't_order_destination_id', 't_order_destination_id');
    }
    /**
     * 出荷基本とのリレーション
     */
    public function delivery()
    {
        return $this->belongsTo('App\Models\Order\DeliveryModel', 't_delivery_hdr_id', 't_delivery_hdr_id');
    }

    /**
     * enumのキャスト
     */
    public function casts()
    {
        return [
            'three_temperature_zone_type' => ThreeTemperatureZoneTypeEnum::class,
        ];
    }

    /**
     * Define the relationship
     */
    public function deliHdr()
    {
        return $this->belongsTo('App\Models\Order\Base\DeliHdrModel', 't_delivery_hdr_id', 't_deli_hdr_id');
    }
}
