<?php


namespace App\Models\Ami\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AmiEcPageSkuProductAttributeModel
 * 
 * @package App\Models
 */
class AmiEcPageSkuProductAttributeModel extends Model
{
    protected $table = 'm_ami_ec_page_sku_product_attributes';
    protected $primaryKey = 'm_ami_ec_page_rakuten_sku_attribute_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    /**
     * ECページSKUマスタとのリレーション
     */
    public function ecPageSku()
    {
        return $this->belongsTo(\App\Models\Ami\Base\AmiEcPageSkuModel::class, 'm_ami_ec_page_sku_id', 'm_ami_ec_page_sku_id');
    }

    /**
     * ECページ楽天市場固有マスタとのリレーション
     */
    public function ecPageRakuten()
    {
        return $this->belongsTo(\App\Models\Ami\Base\AmiEcPageRakutenModel::class, 'm_ami_ec_page_rakuten_id', 'm_ami_ec_page_rakuten_id');
    }

    /**
     * ECページマスタとのリレーション
     */
    public function ecPage()
    {
        return $this->belongsTo(\App\Models\Ami\Base\AmiEcPageModel::class, 'm_ami_ec_page_id', 'm_ami_ec_page_id');
    }

    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }
}
