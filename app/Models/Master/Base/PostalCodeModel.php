<?php


namespace App\Models\Master\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PostalCodeModel
 *
 * @package App\Models
 */
class PostalCodeModel extends Model
{
    protected $table = 'm_postal_codes';
    protected $primaryKey = 'm_postal_id';
    protected $connection = 'global';

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
}
