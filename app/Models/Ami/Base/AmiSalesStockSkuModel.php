<?php


namespace App\Models\Ami\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AmiSalesStockSkuModel
 * 
 * @package App\Models
 */
class AmiSalesStockSkuModel extends Model
{
    protected $table = 't_ami_sales_stock_sku';
    protected $primaryKey = 't_ami_sales_stock_sku_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    /**
     * SKUマスタとのリレーション
     */
    public function sku()
    {
        return $this->belongsTo(\App\Models\Ami\Base\AmiSkuModel::class, 'm_ami_sku_id', 'm_ami_sku_id');
    }

	/**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }
}
