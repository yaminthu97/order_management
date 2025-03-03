<?php


namespace App\Models\Ami\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AmiEcPageShopifyModel
 * 
 * @package App\Models
 */
class AmiEcPageShopifyModel extends Model
{
	protected $table = 'm_ami_ec_page_shopify';
	protected $primaryKey = 'm_ami_ec_page_shopify_id';
	public $timestamps = false;

    /**
     * ECページマスタとのリレーション
     */
    public function ecPage()
    {
        return $this->belongsTo(\App\Models\Ami\Base\AmiEcPageModel::class, 'm_ami_ec_page_id', 'm_ami_ec_page_id');
    }

    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }
}
