<?php


namespace App\Models\Ami\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AmiEcPageWowmaImageModel
 * 
 * @package App\Models
 */
class AmiEcPageWowmaImageModel extends Model
{
	protected $table = 'm_ami_ec_page_wowma_image';
	protected $primaryKey = 'm_ami_ec_page_wowma_image_id';
	public $timestamps = false;

    /**
     * ECページWowma固有マスタとのリレーション
     */
    public function ecPageWowma()
    {
        return $this->belongsTo(\App\Models\Ami\Base\AmiEcPageWowmaModel::class, 'm_ami_ec_page_wowma_id', 'm_ami_ec_page_wowma_id');
    }

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
