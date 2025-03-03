<?php


namespace App\Models\Order\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PurchaseReturnItemModel
 * 
 * @package App\Models
 */
class PurchaseReturnItemModel extends Model
{
    protected $table = 't_purchase_return_items';
    protected $primaryKey = 't_purchase_return_items_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    /*
     * 企業アカウントとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }
    
    /*
     * 仕入返品情報とのリレーション
     */
    public function purchaseReturn()
    {
        return $this->belongsTo(\App\Models\Order\Base\PurchaseReturnModel::class, 't_purchase_return_id', 't_purchase_return_id');
    }
    
    /*
     * 仕入商品情報とのリレーション
     */
    public function purchaseItems()
    {
        return $this->belongsTo(\App\Models\Order\Base\PurchaseItemModel::class, 't_purchase_items_id', 't_purchase_items_id');
    }
    
    /**
     * SKUマスタとのリレーション
     */
    public function amiSku()
    {
        return $this->belongsTo(\App\Models\Ami\Base\AmiSkuModel::class, 'items_id', 'm_ami_sku_id');
    }

    /**
     * 登録ユーザ
     */
    public function entryOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'entry_operator_id', 'm_operators_id');
    }

    /**
     * 更新ユーザ
     */
    public function updateOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'update_operator_id', 'm_operators_id');
    }
}
