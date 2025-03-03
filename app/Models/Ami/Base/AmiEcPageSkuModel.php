<?php


namespace App\Models\Ami\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AmiEcPageSkuModel
 *
 * @package App\Models
 */
class AmiEcPageSkuModel extends Model
{
    use HasFactory;
	protected $table = 'm_ami_ec_page_sku';
	protected $primaryKey = 'm_ami_ec_page_sku_id';
	public $timestamps = false;

    /**
     * ECページマスタとのリレーション
     */
    public function ecPage()
    {
        return $this->belongsTo(\App\Models\Ami\Base\AmiEcPageModel::class, 'm_ami_ec_page_id', 'm_ami_ec_page_id');
    }

    /**
     * SKUマスタとのリレーション
     */
    public function sku()
    {
        return $this->belongsTo(\App\Models\Ami\Base\AmiSkuModel::class, 'm_ami_sku_id', 'm_ami_sku_id');
    }

    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }

}
