<?php


namespace App\Models\Master\Base;

use Carbon\Carbon;
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
    use HasFactory, Authenticatable;
    protected $table = 'm_operators';
    protected $primaryKey = 'm_operators_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

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
}
