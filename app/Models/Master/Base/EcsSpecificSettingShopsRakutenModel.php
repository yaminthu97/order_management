<?php


namespace App\Models\Master\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EcsSpecificSettingShopsRakutenModel
 *
 * @package App\Models
 */
class EcsSpecificSettingShopsRakutenModel extends Model
{
	protected $table = 'm_ecs_specific_setting_shops_rakuten';
	protected $primaryKey = 'm_ecs_specific_setting_shops_rakuten_id';
	public $timestamps = false;

	protected $hidden = [
		'ftp_server_password'
	];

    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }
}
