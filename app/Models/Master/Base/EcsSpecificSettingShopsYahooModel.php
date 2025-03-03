<?php


namespace App\Models\Master\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EcsSpecificSettingShopsYahooModel
 *
 * @package App\Models
 */
class EcsSpecificSettingShopsYahooModel extends Model
{
    protected $table = 'm_ecs_specific_setting_shops_yahoo';
    protected $primaryKey = 'm_ecs_specific_setting_shops_yahoo_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    protected $hidden = [
        'secret_key',
        'secret_key_expiration_date',
        'access_token',
        'refresh_token',
        'ftp_server_password',
        'order_api_secret_file'
    ];

    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }
}
