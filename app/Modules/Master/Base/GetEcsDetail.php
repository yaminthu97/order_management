<?php

namespace App\Modules\Master\Base;

use App\Enums\Esm2SubSys;
use App\Services\Esm2ApiManager;
use App\Services\EsmSessionManager;
use Illuminate\Support\Facades\Log;

class GetEcsDetail implements GetEcsDetailInterface
{
    /**
     * ESM2.0 APIマネージャー
     */
    protected $esm2ApiManager;

    /**
     * ESMセッション管理クラス
     */
    protected $esmSessionManager;

    /**
     * API接続先
     */
    protected $connectionApiUrl = 'searchEcs';

    /**
     * 表示名(フォーマット用)
     */
    protected $displayName = 'm_itemname_type_name';

    /**
     * 値(フォーマット用)
     */
    protected $valueName = 'm_ecs_id';

    /**
     * ECサイト情報
     * @var array
     */
    protected $m_ecs_info = [
        1   =>  ['ec_type_uri' =>  'yahoo',         'ec_type_name'  =>  'Yahoo!ショッピング'],
        3   =>  ['ec_type_uri' =>  'rakuten',       'ec_type_name'  =>  '楽天市場'],
        4   =>  ['ec_type_uri' =>  'amazon',        'ec_type_name'  =>  'Amazon'],
        5   =>  ['ec_type_uri' =>  'wowma',         'ec_type_name'  =>  'Wowma'],
        6   =>  ['ec_type_uri' =>  'shop',          'ec_type_name'  =>  '店舗'],
        7   =>  ['ec_type_uri' =>  'futureshop',    'ec_type_name'  =>  'futureshop'],
    ];

    public function __construct(Esm2ApiManager $esm2ApiManager, EsmSessionManager $esmSessionManager)
    {
        $this->esm2ApiManager = $esm2ApiManager;
        $this->esmSessionManager = $esmSessionManager;
    }

    public function execute($key)
    {
        // 取得処理
        $extendData = [
            'm_account_id' => $this->esmSessionManager->getAccountId(),
            'operator_id' => $this->esmSessionManager->getOperatorId(),
            'feature_id' => 'order/order-delivery/info/' . $key,
            'display_csv_flag' => 1,
            'account_cd' => $this->esmSessionManager->getAccountCode(),
        ];

        $requestData =  [
            // 'delete_flg' => '0',
        ];
        $searchResult = $this->esm2ApiManager->executeSearchApi($this->connectionApiUrl, Esm2SubSys::MASTER, $requestData, $extendData);
        if (isset($searchResult) && is_array($searchResult) && count($searchResult) > 0) {
            $resultRows = [];
            foreach ($searchResult['search_result'] as $work) {
                $ecDetail = null;
                //Amazon,店舗以外の場合は詳細情報を取得する
                if ($work['m_ec_type'] != '4' && $work['m_ec_type'] != '6') {
                    $requestData = [
                        'with_detail'   =>  '1',
                        'm_ecs_id'      =>  $work['m_ecs_id']
                    ];
                    $ecDetail = $this->esm2ApiManager->executeSearchApi($this->connectionApiUrl, Esm2SubSys::MASTER, $requestData, $extendData);
                }
                $ecPageUrl = '';
                switch($work['m_ec_type']) {
                    case '1':
                        $storeAccount = $ecDetail['m_ecs_specific_setting_shops_yahoo'][0]['store_account'] ?? '';
                        if (!empty($storeAccount)) {
                            $ecPageUrl = 'https://store.shopping.yahoo.co.jp/' . $storeAccount . '/{sell_cd}.html';
                        }
                        break;
                    case '3':
                        $shopUrl = $ecDetail['m_ecs_specific_setting_shops_rakuten'][0]['api_shop_url'] ?? '';
                        if (!empty($shopUrl)) {
                            $ecPageUrl = 'https://item.rakuten.co.jp/' . $shopUrl . '/{sell_cd}/';
                        }
                        break;
                    case '4':
                        $ecPageUrl = 'https://www.amazon.co.jp/dp/{amazon_product_code}';
                        break;
                    case '5':
                        $shopId = $ecDetail['m_ecs_specific_setting_shops_wowma'][0]['shop_id'] ?? '';
                        if (!empty($shopId)) {
                            $ecPageUrl = 'https://wowma.jp/u/' . $shopId . '/c/{sell_cd}';
                        }
                        break;
                    case '6':
                        break;
                    case '7':
                        //CC利用なしの場合
                        $ccFlag = $ecDetail['m_ecs_specific_setting_shops_futureshop'][0]['commerce_creator_flg'] ?? '9';
                        if ($ccFlag == 0) {
                            $storeUrl = $ecDetail['m_ecs_specific_setting_shops_futureshop'][0]['shop_product_url'] ?? '';
                            $storeKey = $ecDetail['m_ecs_specific_setting_shops_futureshop'][0]['api_shop_key'] ?? '';
                            if (!empty($storeUrl) && !empty($storeKey)) {
                                $ecPageUrl = $storeUrl . '/fs/'. $storeKey .'/{sell_cd}';
                            }
                        }
                        break;
                    default:
                        break;
                }
                $work['ec_type_uri'] = $this->m_ecs_info[$work['m_ec_type']]['ec_type_uri'] ?? '';
                $work['ec_page_url'] = $ecPageUrl;
                $resultRows[] = $work;
            }
            $searchResult = $resultRows;
        }
        return $searchResult;
    }
}
