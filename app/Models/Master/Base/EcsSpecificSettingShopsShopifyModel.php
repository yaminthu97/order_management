<?php


namespace App\Models\Master\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EcsSpecificSettingShopsShopifyModel
 *
 * @package App\Models
 */
class EcsSpecificSettingShopsShopifyModel extends Model
{
    protected $table = 'm_ecs_specific_setting_shops_shopify';
    protected $primaryKey = 'm_ecs_specific_setting_shops_shopify_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    protected $hidden = [
        'api_access_token'
    ];

    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }

}
