<?php

namespace App\Models\Warehouse\Gfh1207;

use Illuminate\Database\Eloquent\Model;

/**
 * Class WarehouseModel
 *
 * @package App\Models
 */
class WarehouseModel extends Model
{
    protected $table = 'm_warehouses';
    protected $primaryKey = 'm_warehouses_id';
    protected $guarded = [];
    protected $dateFormat = 'Y-m-d H:i:s';

    public const CREATED_AT = 'entry_timestamp';
    public const UPDATED_AT = 'update_timestamp';

    protected $fillable = [
        'm_account_id',
        'delete_flg',
        'm_warehouse_cd',
        'm_warehouse_name',
        'm_warehouse_type',
        'm_warehouse_sort',
        'm_warehouse_priority_flg',
        'm_warehouse_priority',
        'warehouse_personnel_name',
        'warehouse_personnel_name_kana',
        'warehouse_company',
        'warehouse_postal',
        'warehouse_prefectural',
        'warehouse_address',
        'warehouse_house_number',
        'warehouse_adding_building',
        'warehouse_telephone',
        'warehouse_fax',
        'base_delivery_type',
        'sell_stock_adding_flg',
        'delivery_futoff_time',
        'destination_emailaddress_for_lspark',
        'lspark_ftp_server_host',
        'lspark_ftp_server_login_id',
        'lspark_ftp_server_password',
        'lspark_ftp_server_password_expiration_date',
        'cash_on_delivery_flg',
        'deliveryslip_bundle_flg',
        'entry_operator_id',
        'entry_timestamp',
        'update_operator_id',
        'update_timestamp'
    ];

    // Define relationships
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(\App\Models\Warehouse\Base\WarehouseModel::class, 'm_warehouses_id', 'm_warehouses_id');
    }
}
