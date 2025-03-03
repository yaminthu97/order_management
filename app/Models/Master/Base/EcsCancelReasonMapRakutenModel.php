<?php


namespace App\Models\Master\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EcsCancelReasonMapRakutenModel
 *
 * @package App\Models
 */
class EcsCancelReasonMapRakutenModel extends Model
{
    protected $table = 'm_ecs_cancel_reason_map_rakuten';
    protected $primaryKey = 'm_ecs_cancel_reason_map_rakuten_id';

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
     * ECSマスタとのリレーション
     */
    public function ecs()
    {
        return $this->belongsTo(\App\Models\Master\Base\EcsModel::class, 'm_ecs_id', 'm_ecs_id');
    }

    /**
     * 項目名称マスタ(キャンセル理由)とのリレーション
     */
    public function cancelReason()
    {
        return $this->belongsTo(\App\Models\Master\Base\ItemnameTypeModel::class, 'm_itemname_types_cancel_id', 'm_itemname_types_id');
    }
}
