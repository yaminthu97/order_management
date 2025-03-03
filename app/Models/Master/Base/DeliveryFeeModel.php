<?php


namespace App\Models\Master\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DeliveryFeeModel
 *
 * @package App\Models
 */
class DeliveryFeeModel extends Model
{
	protected $table = 'm_delivery_fees';
	protected $primaryKey = 'm_delivery_fee_id';
	public $timestamps = false;

    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }
}
