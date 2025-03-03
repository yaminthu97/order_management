<?php


namespace App\Models\Master\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EnableSellVolModel
 *
 * @package App\Models
 */
class EnableSellVolModel extends Model
{
    protected $table = 't_enable_sell_vol';
    protected $primaryKey = 't_sells_vol_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }

    /**
     * SKUマスタとのリレーション
     */
    public function amiSku()
    {
        return $this->belongsTo(\App\Models\Ami\Base\SkuModel::class, 'item_id', 'm_sku_id');
    }
}
