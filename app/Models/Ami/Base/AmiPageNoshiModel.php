<?php


namespace App\Models\Ami\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AmiPageNoshiModel
 * 
 * @package App\Models
 */
class AmiPageNoshiModel extends Model
{
    use HasFactory;
    protected $table = 'm_ami_page_noshi';
    protected $primaryKey = 'm_ami_page_noshi_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    /**
     * ページマスタとのリレーション
     */
    public function page()
    {
        return $this->belongsTo(\App\Models\Ami\Base\AmiPageModel::class, 'm_ami_page_id', 'm_ami_page_id');
    }

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
     * 熨斗詳細マスタとのリレーション
     */
    public function noshiFormat()
    {
        return $this->hasOne(\App\Models\Master\Base\NoshiFormatModel::class, 'm_noshi_format_id', 'm_noshi_format_id');
    }
}
