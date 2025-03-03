<?php


namespace App\Models\Order\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderListDispModel
 *
 * @package App\Models
 */
class OrderListDispModel extends Model
{
    protected $table = 'm_order_list_disp';
    protected $primaryKey = 'm_order_list_disp_id';

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
     * 受注検索項目マスタとのリレーション
     */
    public function orderListColumn()
    {
        return $this->belongsTo(\App\Models\Order\Base\OrderListColumnModel::class, 'm_order_list_column_id', 'm_order_list_column_id');
    }
}
