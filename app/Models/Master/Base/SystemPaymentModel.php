<?php


namespace App\Models\Master\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SystemPaymentModel
 *
 * @package App\Models
 */
class SystemPaymentModel extends Model
{
    protected $table = 'm_system_payment';
    protected $primaryKey = 'm_system_payment_id';
    protected $connection = 'global';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

/*
 */
}
