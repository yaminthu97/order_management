<?php

namespace App\Models\Order\Gfh1207;

use App\Models\Ami\Base\AmiPageSkuModel;
use App\Models\Ami\Base\SkuModel;
use App\Models\Order\DeliveryModel;
use App\Models\Order\Base\DeliHdrModel;
use App\Models\Order\Gfh1207\DeliveryDetailModel;
use App\Models\Order\Gfh1207\DeliveryDetailModel as Gfh1207DeliveryDetailModel;
use App\Models\Order\Gfh1207\DeliveryModel as Gfh1207DeliveryModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryDetailSkuModel extends Model
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

    /**
     * 出荷基本とのリレーション
     */
    public function delivery()
    {
        return $this->belongsTo(Gfh1207DeliveryModel::class, 't_delivery_hdr_id', 't_delivery_hdr_id');
    }

    /**
    * Define the relationship
    */
    public function deliHdr()
    {
        return $this->belongsTo(DeliHdrModel::class, 't_delivery_hdr_id', 't_deli_hdr_id');
    }

    /**
     * 出荷詳細とのリレーション
     */
    public function deliveryDetail()
    {
        return $this->belongsTo(Gfh1207DeliveryDetailModel::class, 't_delivery_dtl_id', 't_delivery_dtl_id');
    }

    /**
     * SKUマスタとのリレーション
     */
    public function amiSku()
    {
        return $this->hasMany(SkuModel::class, 'm_ami_sku_id', 'item_id');
    }

    /**
     * 出荷明細とのリレーション
    */
    public function deliveryDtl()
    {
        return $this->belongsTo(DeliveryDetailModel::class, 't_delivery_dtl_id', 't_delivery_dtl_id');
    }


}
