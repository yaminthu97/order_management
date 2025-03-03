<?php

namespace App\Models\Order\Gfh1207;

use App\Models\Cc\Gfh1207\CustModel;
use App\Models\Master\Base\DeliveryTypeModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliHdrModel extends Model
{
    use HasFactory;

    /**
     * テーブル名称
     *
     * @var string
     */
    protected $table = 't_deli_hdr';

    /**
     * テーブルの主キー
     *
     * @var string
     */
    protected $primaryKey = 't_deli_hdr_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    /**
     * 出荷詳細とのリレーション
     */
    public function deliveryDetails()
    {
        return $this->hasMany(DeliveryDetailModel::class, 't_delivery_hdr_id', 't_deli_hdr_id');
    }

    /**
     * 受注基本とのリレーション
     */
    public function orderHdr()
    {
        return $this->hasOne(OrderHdrModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }

    /**
     * 配送方法とのリレーション
     */
    public function deliveryType()
    {
        return $this->hasOne(DeliveryTypeModel::class, 'm_delivery_types_id', 'm_deli_type_id');
    }

    /**
     * 出荷明細とのリレーション
     */
    public function deliveryDtl()
    {
        return $this->hasMany(\App\Models\Order\Base\DeliveryDtlModel::class, 't_delivery_hdr_id', 't_deli_hdr_id');
    }

    // Accessor: deli_hope_status
    public function deliHopeDateStatus(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            return $attribute['deli_hope_date'] ? "○" : "×" ;
        });
    }

    /**
     * 顧客マスタとのリレーション
     */
    public function cust()
    {
        return $this->hasOne(CustModel::class, 'm_cust_id', 'm_cust_id');
    }
}

