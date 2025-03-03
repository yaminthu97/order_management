<?php


namespace App\Models\Master\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OperationAuthorityDetailModel
 *
 * @package App\Models
 */
class OperationAuthorityDetailModel extends Model
{
	protected $table = 'm_operation_authority_detail';
	protected $primaryKey = 'm_operation_authority_detail_id';
	public $timestamps = false;

    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }

    /**
     * 操作権限マスタとのリレーション
     */
    public function operationAuthority()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperationAuthorityModel::class, 'm_operation_authority_id', 'm_operation_authority_id');
    }
}
