<?php


namespace App\Models\Master\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EcsSpecificSettingShopsAmazonModel
 *
 * @package App\Models
 */
class EcsSpecificSettingShopsAmazonModel extends Model
{
    protected $table = 'm_ecs_specific_setting_shops_amazon';
    protected $primaryKey = 'm_ecs_specific_setting_shops_amazon_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    protected $hidden = [
        'mws_auth_token',
        'aws_access_secret_key',
        'sp_api_secret',
        'sp_api_reflesh_token',
        'sp_api_access_token'
    ];

    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }
}
