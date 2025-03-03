<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Ami\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AmiAttachmentItemModel
 * 
 * @package App\Models
 */
class AmiAttachmentItemModel extends Model
{
    use HasFactory;
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
     * 項目名称マスタとのリレーション
     */
    public function category()
    {
        return $this->belongsTo(\App\Models\Master\Base\ItemnameTypeModel::class, 'category_id', 'm_itemname_types_id');
    }
}
