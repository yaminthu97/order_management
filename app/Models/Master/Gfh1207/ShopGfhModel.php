<?php

namespace App\Models\Master\Gfh1207;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ShopGfhModel
 *
 * @package App\Models
 */
class ShopGfhModel extends Model
{
    protected $table = 'm_shop_gfh';
    protected $primaryKey = 'm_shop_gfh_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    public const CREATED_AT = 'entry_timestamp';
    public const UPDATED_AT = 'update_timestamp';

    protected $fillable = [
        'payment_due_dates',
        'mail_address_festa_sales',
        'mail_address_festa_inspection',
        'mail_address_prod_dept',
        'mail_address_ec_uriage',
        'mail_address_accounting_dept',
        'mail_address_from',
        'ftp_server_host_yamato',
        'ftp_server_user_yamato',
        'ftp_server_password_yamato',
        'ecbeing_api_base_url',
        'ecbeing_api_exp_customer',
        'ecbeing_api_dl_customer',
        'ecbeing_api_exp_sales',
        'ecbeing_api_dl_sales',
        'ecbeing_api_imp_ship',
        'ecbeing_api_update_ship',
        'ecbeing_api_imp_nyukin',
        'ecbeing_api_update_nyukin',
        'entry_operator_id',
        'entry_timestamp',
        'update_operator_id',
        'update_timestamp',
    ];
}
