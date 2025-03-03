<?php


namespace App\Models\Stock\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class StockHistoryModel
 * 
 * @package App\Models
 */
class StockHistoryModel extends Model
{
    protected $table = 't_stock_history';
    protected $primaryKey = 't_stock_history_id';

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
     * 受注基本マスタとのリレーション
     */
    public function order()
    {
        return $this->belongsTo(\App\Models\Order\Base\OrderHdrModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }
}
