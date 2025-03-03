<?php


namespace App\Models\Master\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class NoshiFormatModel
 *
 * @package App\Models
 */
class NoshiFormatModel extends Model
{
    use HasFactory;
    protected $table = 'm_noshi_format';
    protected $primaryKey = 'm_noshi_format_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    protected $fillable = [
        'm_noshi_format_id',
        'm_account_id',
        'm_noshi_id',
        'delete_flg',
        'noshi_format_name',
        'entry_operator_id',
        'update_operator_id',
    ];

    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }

    /**
     * 熨斗マスタとのリレーション
     */
    public function noshi()
    {
        return $this->belongsTo(\App\Models\Master\Base\NoshiModel::class, 'm_noshi_id', 'm_noshi_id');
    }
}
