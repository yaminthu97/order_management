<?php

namespace App\Models\Ami\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * SKUマスタ
 */
class SkuModel extends Model
{
    use HasFactory;

    /**
	 * テーブル名称
	 *
	 * @var string
     */
    protected $table = 'm_ami_sku';

    /**
	 * テーブルの主キー
	 *
	 * @var string
	 */
    protected $primaryKey = 'm_ami_sku_id';

	/**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }
}
