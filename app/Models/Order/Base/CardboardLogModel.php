<?php


namespace App\Models\Order\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CardboardLogModel
 * 
 * @package App\Models
 */
class CardboardLogModel extends Model
{
    protected $table = 't_cardboard_log';
    protected $primaryKey = 't_cardboard_log_id';
	public $timestamps = false;

    /*
     * 企業アカウントとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }

    /*
     * 出荷基本とのリレーション
     */
    public function deliveryHdr()
    {
        return $this->belongsTo(\App\Models\Order\Base\DeliveryHdrModel::class, 't_delivery_hdr_id', 't_delivery_hdr_id');
    }
}
