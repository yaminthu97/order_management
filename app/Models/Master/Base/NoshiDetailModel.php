<?php


namespace App\Models\Master\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class NoshiDetailModel
 *
 * @package App\Models
 */
class NoshiDetailModel extends Model
{
    use HasFactory;
    protected $table = 'm_noshi_detail';
    protected $primaryKey = 'm_noshi_detail_id';

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
     * 熨斗マスタとのリレーション
     */
    public function noshi()
    {
        return $this->belongsTo(\App\Models\Master\Base\NoshiModel::class, 'm_noshi_id', 'm_noshi_id');
    }

    /**
     * 熨斗フォーマットマスタとのリレーション
     */
    public function noshiFormat()
    {
        return $this->belongsTo(\App\Models\Master\Base\NoshiFormatModel::class, 'm_noshi_format_id', 'm_noshi_format_id');
    }

    /**
     * 熨斗名入れパターンマスタとのリレーション
     */
    public function noshiNamingPattern()
    {
        return $this->belongsTo(\App\Models\Master\Base\NoshiNamingPatternModel::class, 'm_noshi_naming_pattern_id', 'm_noshi_naming_pattern_id');
    }
}
