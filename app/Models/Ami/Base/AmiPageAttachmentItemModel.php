<?php


namespace App\Models\Ami\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AmiPageAttachmentItemModel
 * 
 * @package App\Models
 */
class AmiPageAttachmentItemModel extends Model
{
    protected $table = 'm_ami_page_attachment_items';
    protected $primaryKey = 'm_ami_page_attachment_item_id';

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
     * 付属品マスタとのリレーション
     */
    public function attachmentItem()
    {
        return $this->belongsTo(\App\Models\Ami\Base\AmiAttachmentItemModel::class, 'm_ami_attachment_item_id', 'm_ami_attachment_item_id');
    }

    /**
     * 項目名称マスタとのリレーション
     */
    public function category()
    {
        return $this->belongsTo(\App\Models\Master\Base\ItemnameTypeModel::class, 'category_id', 'm_itemname_types_id');
    }
    public function group()
    {
        return $this->belongsTo(\App\Models\Master\Base\ItemnameTypeModel::class, 'group_id', 'm_itemname_types_id');
    }

    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }
}
