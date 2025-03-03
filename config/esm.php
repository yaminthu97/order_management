<?php
/**
 * ESMに関連する設定ファイル
 */
return [

    'app_base_url' => env('APP_BASE_URL',''),

	'api_subsys_url' => [
		'global' => env('GLOBAL_API_BASE_URL',''),
		'syscom' => env('SYSCOM_API_BASE_URL',''),
		'master' => env('MASTER_API_BASE_URL',''),
		'warehouse' => env('WAREHOUSE_API_BASE_URL',''),
		'common' => env('COMMON_API_BASE_URL',''),
		'stock' => env('STOCK_API_BASE_URL',''),
		'order' => env('ORDER_API_BASE_URL',''),
		'cc' => env('CC_API_BASE_URL',''),
		'claim' => env('CLAIM_API_BASE_URL',''),
		'ami' => env('AMI_API_BASE_URL',''),
		'goto' => env('GOTO_API_BASE_URL',''),
	],

	'app_subsys_url' => [
		'syscom' => env('SYSCOM_APP_BASE_URL',''),
		'master' => env('MASTER_APP_BASE_URL',''),
		'warehouse' => env('WAREHOUSE_APP_BASE_URL',''),
		'common' => env('COMMON_APP_BASE_URL',''),
		'stock' => env('STOCK_APP_BASE_URL',''),
		'order' => env('ORDER_APP_BASE_URL',''),
		'cc' => env('CC_APP_BASE_URL',''),
		'claim' => env('CLAIM_APP_BASE_URL',''),
		'ami' => env('AMI_APP_BASE_URL',''),
		'goto' => env('GOTO_APP_BASE_URL',''),
	],

    'default_page_size' => [
        'common' => 10,
        'master' => 30,
        'warehouse' => 10,
        'stock' => 10,
        'order' => 10,
        'cc' => 10,
        'claim' => 10,
        'ami' => 10,
        'goto' => 10,
    ],

];
