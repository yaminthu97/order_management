<?php

namespace App\Models\Order\Gfh1207;

use App\Models\Ami\Base\PageModel;
use App\Models\Ami\SkuModel;
use App\Models\Order\Gfh1207\DeliveryDetailSkuModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class DeliveryDetailModel extends Model
{
    use HasFactory;

    /**
	 * テーブル名称
	 *
	 * @var string
     */
    protected $table = 't_delivery_dtl';

    /**
	 * テーブルの主キー
	 *
	 * @var string
	 */
    protected $primaryKey = 't_delivery_dtl_id';

    /**
     * 出荷基本とのリレーション
     */
    public function delivery()
    {
        return $this->belongsTo('App\Models\Order\DeliveryModel', 't_delivery_hdr_id', 't_delivery_hdr_id');
    }

    /**
     * 出荷基本とのリレーション
     */
    public function deliHdr()
    {
        return $this->belongsTo('App\Models\Order\Gfh1207\DeliHdrModel', 't_delivery_hdr_id', 't_deli_hdr_id');
    }

    /**
     * 出荷詳細SKUとのリレーション
     */
    public function deliveryDetailSkus()
    {
        return $this->hasMany(DeliveryDetailSkuModel::class, 't_delivery_dtl_id', 't_delivery_dtl_id');
    }

    /**
     * ECページマスタテーブルとのリレーション
     */
    public function amiEcPage()
    {
        return $this->belongsTo(\App\Models\Ami\Base\AmiEcPageModel::class, 'sell_id', 'm_ami_ec_page_id');
    }

}
