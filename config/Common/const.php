<?php

return [
    'APP_URL' => env('APP_URL'),
    'API_DOMAIN_COMMON' => env('API_DOMAIN_COMMON'),
    'API_DOMAIN' => env('API_DOMAIN'),
    'APP_RESOURCE_URL' => env('APP_RESOURCE_URL'),
    'AWS_S3_ACCESS_KEY' => env('AWS_S3_ACCESS_KEY'),
    'AWS_S3_SECRET' => env('AWS_S3_SECRET'),
    'AWS_S3_BACKET' => env('AWS_S3_BACKET'),
    'AWS_S3_REGION' => env('AWS_S3_REGION'),
    'MANUAL_SITE_URL' => env('MANUAL_SITE_URL'),
    'disp_limit_default' => 30,
    'disp_limits' => [10, 30, 60, 100, 150, 200],
    'disp_limit_max' => 100,
    'page_limit' => 10,
    'LoginManager' => [
        'FailedMaxCount' => 4,
        'AccountLockStatus' => [
            'UnLock' => 0,
            'Locked' => 1,
        ],
        'StatusCode' => [
            'Success' => '0',
            'Failed' => '1',
        ],
        'EcSite' => [
            ['id' => 1, 'namespace' => 'EcsSpecificSettingShopsYahoo'],    // Yahoo
            ['id' => 3, 'namespace' => 'EcsSpecificSettingShopsRakuten'],    // 楽天市場
            ['id' => 5, 'namespace' => 'EcsSpecificSettingShopsWowma'],    // Wowma
        ],
        'expirationAlertStartDays' => env('EXPIRATION_ALERT_START_DAYS'),
        'AlertMsgTemplate' => '%s：%sが迫っています。%sまでに変更してください',
    ],
    'TopManager' => [
        'BatchExecInstructionDays' => env('BATCH_EXEC_INSTRUCTION_DAYS'),
    ],
    'ec_site_types' => [
        '0' => ['id' => 0, 'label' => 'amazon'],
        '1' => ['id' => 1, 'label' => '楽天市場'],
    ],
    'cooperation_types' => [
        '0' => ['id' => 0, 'label' => '連携1'],
        '1' => ['id' => 1, 'label' => '連携2'],
    ],
    'cooperation_data_types' => [
        '0' => ['id' => 0, 'label' => '連携データ種類1'],
        '1' => ['id' => 1, 'label' => '連携データ種類2'],
    ],
    'cooperation_status' => [
        '0' => ['id' => 0, 'label' => '完了'],
        '1' => ['id' => 1, 'label' => '未完'],
    ],
    'api_types' => [
        '0' => ['id' => 0, 'label' => 'API1'],
        '1' => ['id' => 1, 'label' => 'API2'],
    ],
    'commonHeader' => [
        'defaultUserName' => 'ゲスト',
        'noNoticeMessage' => 'お知らせはありません。',
        'noAlertMessage' => 'アラート情報はありません。',
    ],
    'execute_statuses' => [
            '0' => ['id' => 0,  'label' => '正常'],
            '1' => ['id' => 1,  'label' => '異常'],
    ],
    'execute_batch_types' => [
            '1' => ['id' => 1, 'label' => 'バッチA'],
            '2' => ['id' => 2, 'label' => 'バッチB'],
            '3' => ['id' => 3, 'label' => 'バッチC'],
    ],
    'inquiry_type' => [
      '10' => ['id' => 10, 'label' => '質問'],
      '20' => ['id' => 20, 'label' => '要望'],
      '30' => ['id' => 30, 'label' => '不具合報告'],
      '40' => ['id' => 40, 'label' => 'オプション追加'],
      '50' => ['id' => 50, 'label' => 'カスタマイズ見積'],
      '60' => ['id' => 60, 'label' => 'API連携の申請'],
      '70' => ['id' => 70, 'label' => '本申込'],
    ],
    'relation_category' => [
      '10' => ['id' => 10,  'label' => '料金プランやその他'],
      '20' => ['id' => 20,  'label' => '受注管理'],
      '30' => ['id' => 30,  'label' => '債権管理'],
      '40' => ['id' => 40,  'label' => '出荷管理'],
      '50' => ['id' => 50,  'label' => '在庫管理'],
      '60' => ['id' => 60,  'label' => '商品・商品ページ管理'],
      '70' => ['id' => 70,  'label' => '発注・仕入管理'],
      '80' => ['id' => 80,  'label' => '顧客管理'],
      '90' => ['id' => 90,  'label' => '集計管理'],
      '100' => ['id' => 100, 'label' => 'マスタ管理'],
      '110' => ['id' => 110, 'label' => '自動出荷'],
      '120' => ['id' => 120, 'label' => '自動在庫同期'],
      '130' => ['id' => 130, 'label' => 'その他'],
    ],
    'notice_priorities' => [
        '1' => ['id' => 1, 'label' => '情報'],
        '2' => ['id' => 2, 'label' => '警告'],
        '3' => ['id' => 3, 'label' => '重要'],
    ],
    'APP_URL' => env('APP_URL'),
    'disp_limit' => 30,
    'PageHeader' => [
        'NoResultsMessage' => '検索結果は0件でした。',
    ],
    'Breadcrumb_Hierarchy' => [
        'first',
        'second',
        'third',
        'fourth',
        'fifth',
        'sixth',
        'seventh',
        'display',
    ],
    'CLARITY_TAG_ID' => env('CLARITY_TAG_ID', "h274k5k0kr"),
];
