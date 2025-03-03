<?php

return [
    'design_path' => env('VIEW_STATIC_COMMON_URL'),

    // 'sybsys_name' => env('APP_SUBSYSTEM_NAME', 'ORDER'),
    'subsys_name' => [
        'cc' =>env('CC_APP_SUBSYSTEM_NAME', 'CC'),
    ],

    'use_proxy' => env('USE_PROXY', '0'),

    'proxy_address' => env('HTTP_PROXY', ''),

    'no_proxy_address' => env('NO_PROXY_SERVER', ''),

    'api_connection_debug' => env('API_CONNECTION_DEBUG'),

    'api_subsys_url' => [
        'global' => env('GLOBAL_API_BASE_URL', ''),
        'syscom' => env('SYSCOM_API_BASE_URL', ''),
        'master' => env('MASTER_API_BASE_URL', ''),
        'warehouse' => env('WAREHOUSE_API_BASE_URL', ''),
        'common' => env('COMMON_API_BASE_URL', ''),
        'stock' => env('STOCK_API_BASE_URL', ''),
        'order' => env('ORDER_API_BASE_URL', ''),
        'cc' => env('CC_API_BASE_URL', ''),
        'claim' => env('CLAIM_API_BASE_URL', ''),
        'ami' => env('AMI_API_BASE_URL', ''),
        'goto' => env('GOTO_API_BASE_URL', ''),
    ],

    'app_subsys_url' => [
        'syscom' => env('SYSCOM_APP_BASE_URL', ''),
        'master' => env('MASTER_APP_BASE_URL', ''),
        'warehouse' => env('WAREHOUSE_APP_BASE_URL', ''),
        'common' => env('COMMON_APP_BASE_URL', ''),
        'stock' => env('STOCK_APP_BASE_URL', ''),
        'order' => env('ORDER_APP_BASE_URL', ''),
        'cc' => env('CC_APP_BASE_URL', ''),
        'claim' => env('CLAIM_APP_BASE_URL', ''),
        'ami' => env('AMI_APP_BASE_URL', ''),
        'goto' => env('GOTO_APP_BASE_URL', ''),
    ],

    'aws_s3' => [
        'access_key' => env('AWS_S3_ACCESS_KEY', ''),
        'secret' => env('AWS_S3_SECRET', ''),
        'region' => env('AWS_S3_REGION', ''),
        'version' => env('AWS_S3_VERSION', 'latest'),
        'bucket' => env('AWS_S3_BUCKET', ''),
    ],

    'aes' => [
        'aes_key' => env('AES_KEY', ''),
        'aes_iv' => env('AES_IV', ''),
    ],

    'csv_data_limit' => env('CSV_DATA_LIMIT', '1000'),
];
