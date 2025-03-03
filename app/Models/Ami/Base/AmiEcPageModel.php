<?php


namespace App\Models\Ami\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AmiEcPageModel
 *
 * @package App\Models
 */
class AmiEcPageModel extends Model
{
    use HasFactory;
    protected $table = 'm_ami_ec_page';
    protected $primaryKey = 'm_ami_ec_page_id';

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
     * ECサイトマスタとのリレーション
     */
    public function ecs()
    {
        return $this->belongsTo(\App\Models\Master\Base\EcsModel::class, 'm_ecs_id', 'm_ecs_id');
    }

    /**
     * ページ付属品マスタとのリレーション
     */
    public function pageAttachmentItem()
    {
        return $this->hasMany(\App\Models\Ami\Base\AmiPageAttachmentItemModel::class, 'm_ami_page_id', 'm_ami_page_id');
    }

    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }
}
