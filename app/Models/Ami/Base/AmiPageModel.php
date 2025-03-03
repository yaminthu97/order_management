<?php


namespace App\Models\Ami\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AmiPageModel
 *
 * @package App\Models
 */
class AmiPageModel extends Model
{
    use HasFactory;
    protected $table = 'm_ami_page';
    protected $primaryKey = 'm_ami_page_id';

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
     * ページSKUマスタとのリレーション
     */
    public function pageSku()
    {
        return $this->hasMany(\App\Models\Ami\Base\AmiPageSkuModel::class, 'm_ami_page_id', 'm_ami_page_id');
    }

    /**
     * ページ付属品マスタとのリレーション
     */
    public function pageAttachmentItem()
    {
        return $this->hasMany(\App\Models\Ami\Base\AmiPageAttachmentItemModel::class, 'm_ami_page_id', 'm_ami_page_id');
    }
}
