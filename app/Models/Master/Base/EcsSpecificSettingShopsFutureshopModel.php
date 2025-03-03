<?php


namespace App\Models\Master\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EcsSpecificSettingShopsFutureshopModel
 *
 * @package App\Models
 */
class EcsSpecificSettingShopsFutureshopModel extends Model
{
    protected $table = 'm_ecs_specific_setting_shops_futureshop';
    protected $primaryKey = 'm_ecs_specific_setting_shops_store_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    protected $hidden = [
        'api_password',
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
