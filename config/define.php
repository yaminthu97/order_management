<?php

// 共通で定数を設定するものについてはここに書く
return [
    'api_subsys_name' => [
        'global' => 'gcommonApi',
        'syscom' => 'syscomApi',
        'master' => 'masterApi',
        'warehouse' => 'warehouseApi',
        'common' => 'commonApi',
        'stock' => 'stockApi',
        'order' => 'orderApi',
        'cc' => 'ccApi',
        'claim' => 'claimApi',
        'ami' => 'amiApi',
        'goto' => 'gotoApi',
    ],

    'prefectual_region_foreign' => 99,

    // サブシステムごとに参照するバージョンの名称
    'subsystem_version' => [
        'global' => 'syscom_use_version',
        'syscom' => 'syscom_use_version',
        'master' => 'master_use_version',
        'warehouse' => 'warehouse_use_version',
        'common' => 'common_use_version',
        'stock' => 'stock_use_version',
        'order' => 'order_use_version',
        'cc' => 'cc_use_version',
        'claim' => 'claim_use_version',
        'ami' => 'ami_use_version',
        'goto' => 'goto_use_version',
    ],
    // 受注取込み対象のECサイト種別
    'input_ec_order_csv_type' => [4],
    'input_ec_order_csv_batch' => [
        4 => 'impcsv_ec_order_amazon',
    ],
    'cc' => [
        'send_mail_parameter_session' => 'NEOSM0233_send_param',
        'search_custCommunication_session' => 'NECSM0120_send_param',
        'customer_register_request' => 'NECSM0112_register_request',
        'customer_search_request' => 'NECSM0110_search_request',
        'custCommunication_register_request' => 'NECSM0121_register_request',
        'session_key_id' => 'data_key_id',
        'search_customer_list_session' => 'NECSM0110_search_customer'
    ],
    'order' => [
        'send_mail_parameter_session' => 'NEOSM0233_send_param',
        'session_key_id' => 'data_key_id',
    ],
    'session_key_id' => 'data_key_id',
    'master' => [
        'deliverytype_update_request' => 'NEMSMF0020_update_request',
        'operators_register_request' => 'NEMSMJ0020_register_request',
        'ordertag_request' => 'NEOSM0242_request',
        'payment_types_register_request' => 'NEMSMJ0020_register_request',
        'warehouses_register_request' => 'NEMSMH0020_register_request',
        'session_key_id' => 'data_key_id',
        'itemnametype_request' => 'NEMSMA0020_request'
    ],
    'payment_types_register_request' => 'NEMSMJ0020_register_request',
    'session_key_id' => 'data_key_id',
    'create_noshi_cmd'=>env('CREATE_NOSHI_CMD'),

    // OCR 固定値の定義
    'ocr' => [
        //ocr文字列
        'OCR1TOKEN2' => '[9,1,8,1,7,1,6,1,5,1,4,1,3,1,2,1,9,1,8,1,7,1,6,1,5,1,4,1,3,1,2,1,9,1,8,1,7]',
        'OCR1TOKEN1' => '[2,1,3,1,4,1,5,1,6,1,7,1,8,1,9,1,2,1,3,1,4,1,5,1,6,1,7,1,8,1,9,1,2,1,3,1,4,1]',
        //ocr文字列2
        'OCR2TOKEN2' => '[9,1,8,1,7,1,6,1,5,1,4,1,3,1,2,1,9,1,8,1,7,1,6,1,5,1,4,1,3,1,2,1,9,1,8,1,7,1,6,1,5,1]',
        'OCR2TOKEN1' => '[2,1,3,1,4,1,5,1,6,1,7,1,8,1,9,1,2,1,3,1,4,1,5,1,6,1,7,1,8,1,9,1,2,1,3,1,4,1,5,1,6,1,7]',
    ],
];
