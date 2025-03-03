<?php


namespace App\Models\Common\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class InvoiceSystemModel
 *
 * @package App\Models
 */
class InvoiceSystemModel extends Model
{
    protected $table = 'm_invoice_system';
    protected $primaryKey = 'm_invoice_system_id';
    protected $connection = 'global';

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
}
