<?php


namespace App\Models\Ami\Gfh1207;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CampaignModel
 *
 * @package App\Models
 */
class AttachmentitemModel extends Model
{
    protected $table = 'm_ami_attachment_items';
    protected $primaryKey = 'm_ami_attachment_item_id';

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
     * 項目名称マスタとのリレーション
     */
    public function category()
    {
        return $this->belongsTo(\App\Models\Master\Base\ItemnameTypeModel::class, 'category_id', 'm_itemname_types_id');
    }

}
