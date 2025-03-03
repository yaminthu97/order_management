<?php


namespace App\Models\Common\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PagenaviModel
 *
 * @package App\Models
 */
class PagenaviModel extends Model
{
    protected $table = 'm_pagenavi';
    protected $primaryKey = 'm_pagenavi_id';
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
     * スクリーンマスタとのリレーション
     */
    public function screen()
    {
        return $this->belongsTo(\App\Models\Master\Base\ScreenModel::class, 'm_screens_id', 'm_screens_id');
    }
}
