<?php


namespace App\Models\Common\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EmailConvertStringModel
 *
 * @package App\Models
 */
class EmailConvertStringModel extends Model
{
    protected $table = 'm_email_convert_string';
    protected $primaryKey = 'm_email_convert_string_id';
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
     * 使用対象企業アカウントマスタとのリレーション
     */
    public function usingAccount()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'use_m_account_id', 'm_account_id');
    }
}
