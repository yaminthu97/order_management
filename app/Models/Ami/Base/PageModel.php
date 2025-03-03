<?php

namespace App\Models\Ami\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 商品ページマスタ
 */
class PageModel extends Model
{
    use HasFactory;

    /**
	 * テーブル名称
	 *
	 * @var string
     */
    protected $table = 'm_ami_page';

    /**
	 * テーブルの主キー
	 *
	 * @var string
	 */
    protected $primaryKey = 'm_ami_page_id';

    /**
     * SKUマスタとのリレーション(n:n)
     */
    public function amiSkus()
    {
        return $this->belongsToMany(SkuModel::class, 'm_ami_page_sku', 'm_ami_page_id', 'm_ami_sku_id')
        ->withPivot(['sales_sku_type', 'sku_vol']);
    }

    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }
}
