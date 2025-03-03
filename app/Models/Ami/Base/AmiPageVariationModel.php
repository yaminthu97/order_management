<?php


namespace App\Models\Ami\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AmiPageVariationModel
 * 
 * @package App\Models
 */
class AmiPageVariationModel extends Model
{
	protected $table = 'm_ami_page_variation';
	protected $primaryKey = 'm_ami_page_variation_id';
	public $timestamps = false;

    /**
     * ページマスタとのリレーション
     */
    public function page()
    {
        return $this->belongsTo(\App\Models\Ami\Base\AmiPageModel::class, 'm_ami_page_id', 'm_ami_page_id');
    }

	/**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }
}
