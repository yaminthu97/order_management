<?php


namespace App\Models\Master\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class NoshiModel
 *
 * @package App\Models
 */
class NoshiModel extends Model
{
    use HasFactory;
    protected $table = 'm_noshi';
    protected $primaryKey = 'm_noshi_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    protected $fillable = [
        'm_noshi_id',
        'm_account_id',
        'delete_flg',
        'noshi_type',
        'attachment_item_group_id',
        'omotegaki',
        'noshi_cd',
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
     * 熨斗種類とのリレーション
     */
    public function noshiFormatList()
    {
        return $this->hasMany(\App\Models\Master\Base\NoshiFormatModel::class, 'm_noshi_id', 'm_noshi_id');
    }

    /**
     * 付属品マスタとのリレーション
     */
    public function attachmentItemGroup()
    {
        return $this->hasOne(\App\Models\Master\Base\ItemnameTypeModel::class, 'm_itemname_types_id', 'attachment_item_group_id');
    }
}
