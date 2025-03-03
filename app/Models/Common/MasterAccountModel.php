<?php

namespace App\Models\Common;

class MasterAccountModel extends ApiSyscomModel
{
    protected $fillAble = [
        'm_account_id',
//		'm_account_name',
//		'local_database_name'
        'account_cd',
        'syscom_use_version',
        'master_use_version',
        'warehouse_use_version',
        'common_use_version',
        'stock_use_version',
        'order_use_version',
        'cc_use_version',
        'claim_use_version',
        'ami_use_version',
        'goto_use_version',
    ];

    protected $primaryKey = 'm_account_id';

    protected $table = 'global_db.m_account';
}
