<?php

namespace App\Models\Master\Gfh1207;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 社員マスタモデル
 * Class OperatorModel
 *
 * @package App\Models
 */
class OperatorModel extends Model
{
    use HasFactory;
    use Authenticatable;
    protected $table = 'm_operators';
    protected $primaryKey = 'm_operators_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    public const CREATED_AT = 'entry_timestamp';
    public const UPDATED_AT = 'update_timestamp';

    protected $fillable = [
        'm_account_id',
        'delete_flg',
        'user_type',
        'm_operation_authority_id',
        'cc_authority_code',
        'login_id',
        'login_password',
        'm_operator_name',
        'm_operator_email',
        'last_login_date',
        'faild_login_count',
        'account_lock_status',
        'account_lock_datetime',
        'entry_operator_id',
        'entry_timestamp',
        'update_operator_id',
        'update_timestamp',
        'g2fa_key',
        'password_update_timestamp',
        'password_history',
    ];

    protected $hidden = [
        'login_password'
    ];

    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }

    public function authorityName()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperationAuthorityModel::class, 'm_operation_authority_id', 'm_operation_authority_id');
    }
}
