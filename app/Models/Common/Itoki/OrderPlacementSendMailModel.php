<?php


namespace App\Models\Common\Itoki;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderPlacementSendMailModel
 *
 * @package App\Models
 */
class OrderPlacementSendMailModel extends Model
{
    protected $table = 't_order_placement_send_mail';
    protected $primaryKey = 't_order_placement_send_mail_id';
    public $timestamps = false;
    protected $connection = 'global';

    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }

    /**
     * 受注基本とのリレーション
     */
    public function order()
    {
        return $this->belongsTo(\App\Models\Order\Base\OrderModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }

    /**
     * 仕入れ先マスタとのリレーション
     */
    public function supplier()
    {
        return $this->belongsTo(\App\Models\Master\Base\SupplierModel::class, 'm_suppliers_id', 'm_suppliers_id');
    }
}
