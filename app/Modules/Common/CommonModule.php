<?php

namespace App\Modules\Common;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * 共通機能ベースマネージャー
 *
 * Class CommonModule
 */
class CommonModule extends AppSyscomModule
{
    private $_subsystem = '';
    private $_ver = '';

    // 情報ログ出力先ディレクトリパス
    protected $infoLogOutDir = '';

    // エラーログ出力先ディレクトリパス
    protected $errorLogOutDir = '';

    // ApiID
    protected $apiID = '';

    // 情報ログメッセージのひな形
    protected $infoLogMsgTemplate = '[情報] [処理対象ID:%s] %S';

    // エラーログメッセージのひな型
    protected $errLogMsgTemplate = '[異常] [処理対象ID:%s] %sに失敗しました。';

    // 日付クラス
    protected $carbon;

    // タイムゾーン（日本）
    protected $timeZone = '+9:00';

    /**
     *　コンストラクタ
     */
    public function __construct()
    {
        $this->_subsystem = 'Common';
        $this->_ver = 'v1_0';
        $this->infoLogOutDir = storage_path('logs/');
        $this->errorLogOutDir = storage_path('logs/');
        $this->carbon = new Carbon($this->timeZone);
    }


    /**
     * API URL配列
     * key_name
     *     subsys  <-- 固定値
     *         subsys_name
     *     apiurl  <-- 固定値
     *         api_url
     */
    protected $apiUris = [
        // 'key_name' => [ 'subsys' => 'subsys_name', 'apiurl' => 'api_url' ],
    ];

    /**
     * 受注進捗区分の一覧
     */
    protected $progressTypes = [
        0,			// 確認待
        10,			// 与信待
        20,			// 前払い入金待ち
        30,			// 引当待ち
        40,			// 出荷待ち
        50,			// 出荷中
        60,			// 出荷済み
        70,			// 後払い待ち
        80,			// 完了
        90,			// キャンセル
        100,		// 返品
    ];

    /**
     * 受注進捗区分の一覧と名称
     */
    protected $progressTypeNames = [
        '0' => '確認待',
        '10' => '与信待',
        '20' => '前払入金待',
        '30' => '引当待',
        '40' => '出荷待',
        '50' => '出荷中',
        '60' => '出荷済',
        '70' => '後払入金待',
        '80' => '完了',
        '90' => 'キャンセル',
        '100' => '返品',
    ];

    /**
     * カレントAPIキー名
     */
    protected $currentApiKey = '';

    /**
     * 内部使用API URL配列
     */
    private $privateApiUris = [
        'searchPrefectural'			=> [ 'subsys' => 'global', 'apiurl' => 'searchPrefectural' ],			// 都道府県マスタ取得
        'searchShops'				=> [ 'subsys' => 'master', 'apiurl' => 'searchShops' ],					// 基本設定情報取得
        'searchOperators'			=> [ 'subsys' => 'master', 'apiurl' => 'searchOperators' ],				// 社員マスタ情報取得
        'searchItemNameTypes'		=> [ 'subsys' => 'master', 'apiurl' => 'searchItemnameTypes' ],			// 項目名称マスタ情報取得
        'searchEcs'					=> [ 'subsys' => 'master', 'apiurl' => 'searchEcs' ],					// ECサイトマスタ情報取得
        'searchDeliveryTypes'		=> [ 'subsys' => 'master', 'apiurl' => 'searchDeliveryTypes' ],			// 配送方法マスタ情報取得
//		'searchDeliveryTimeHopeMap'	=> [ 'subsys' => 'master', 'apiurl' => 'searchDeliveryTimeHopeMap' ],	// 配送方法-希望時間帯設定情報取得
        'searchDeliveryTimeHopeMap'	=> [ 'subsys' => 'global', 'apiurl' => 'searchDeliveryTimeHopeMap' ],	// 配送方法-希望時間帯設定情報取得
        'searchDeliveryTimeHope'	=> [ 'subsys' => 'global', 'apiurl'	=> 'searchDeliveryTimeHope' ],
        'searchEmailTemplates'		=> [ 'subsys' => 'master', 'apiurl' => 'searchEmailTemplates' ],		// メールテンプレートマスタ情報取得
        'searchPaymentTypes'		=> [ 'subsys' => 'master', 'apiurl' => 'searchPaymentTypes' ],			// 支払方法マスタ情報取得
        'searchWarehouses'			=> [ 'subsys' => 'master', 'apiurl' => 'searchWarehouses' ],			// 倉庫マスタ情報取得
        'searchSku'					=> [ 'subsys' => 'ami',    'apiurl' => 'sku/searchSku' ],				// SKUマスタ情報取得
        'searchOrderTagMaster'		=> [ 'subsys' => 'order',  'apiurl' => 'searchOrderTagMaster' ],		// 受注タグマスタ検索
        'searchInvoiceSystem'		=> [ 'subsys' => 'global', 'apiurl' => 'searchInvoiceSystem' ],			// 送り状システムマスタ検索
        'searchOrderListcond'		=> [ 'subsys' => 'order', 'apiurl' => 'searchOrderListcond' ],			// 都道府県マスタ取得
    ];

    /**
     * API共通条件（必須項目）
     */
    private $privateApiCommonRequires = [
        'global'	=> ['m_account_id'],
        'common'	=> ['operator_id', 'feature_id'],
        'master'	=> ['operator_id', 'feature_id', 'display_csv_flag'],
        'ami'		=> ['operator_id', 'feature_id', 'display_csv_flag', 'account_cd'],
        'order'		=> ['display_csv_flag'],
    ];


    /**
     * メール送信ステータス
     */
    protected $mailSendStatus = [
        0	=> '未送信',
        1	=> '送信成功',
        9	=> '送信エラー',
    ];

    /**
     * 都道府県マスタ取得
     * @param string $featureId APIを呼び出している画面URL(ドメイン以降) 例)master/operators/list
     * @param mixed $searchInfo (string)m_prefectual_id / (array)search_info
     * @param bool $withResultInfo true-response以下を返却 / false-search_result以下を返却
     */
    public function searchPrefectual($featureId, $searchInfo = null, $withResultInfo = false)
    {
        $result = $this->commonMasterSearchApi('searchPrefectural', $featureId, false, 'm_prefectural_id', $searchInfo, $withResultInfo);
        return $result;
    }

    /**
     * 基本設定情報取得
     * @param string $featureId APIを呼び出している画面URL(ドメイン以降) 例)master/operators/list
     * @param mixed $searchInfo (string)m_shops_id / (array)search_info
     * @param bool $withResultInfo true-response以下を返却 / false-search_result以下を返却
     */
    public function searchShops($featureId, $searchInfo = null, $withResultInfo = false)
    {
        $result = $this->commonMasterSearchApi('searchShops', $featureId, false, 'm_shops_id', $searchInfo, $withResultInfo);
        return $result;
    }

    /**
     * 社員マスタ情報取得
     * @param string $featureId APIを呼び出している画面URL(ドメイン以降) 例)master/operators/list
     * @param bool $onlyUse true-使用中のみ / false-使用停止を含む
     * @param mixed $searchInfo (string)m_operators_id / (array)search_info
     * @param bool $withResultInfo true-response以下を返却 / false-search_result以下を返却
     */
    public function searchOperators($featureId, $onlyUse, $searchInfo = null, $withResultInfo = false)
    {
        $result = $this->commonMasterSearchApi('searchOperators', $featureId, $onlyUse, 'm_operators_id', $searchInfo, $withResultInfo);
        return $result;
    }

    /**
     * 項目名称マスタ情報取得
     * @param string $featureId APIを呼び出している画面URL(ドメイン以降) 例)master/operators/list
     * @param bool $onlyUse true-使用中のみ / false-使用停止を含む
     * @param mixed $searchInfo (string)m_itemname_types_id / (array)search_info
     * @param bool $withResultInfo true-response以下を返却 / false-search_result以下を返却
     */
    public function searchItemNameTypes($featureId, $onlyUse, $searchInfo = null, $withResultInfo = false)
    {
        $result = $this->commonMasterSearchApi('searchItemNameTypes', $featureId, $onlyUse, 'm_itemname_types_id', $searchInfo, $withResultInfo);
        return $result;
    }

    /**
     * ECサイトマスタ情報取得
     * @param string $featureId APIを呼び出している画面URL(ドメイン以降) 例)master/operators/list
     * @param bool $onlyUse true-使用中のみ / false-使用停止を含む
     * @param mixed $searchInfo (string)m_ecs_id / (array)search_info
     * @param bool $withResultInfo true-response以下を返却 / false-search_result以下を返却
     */
    public function searchEcs($featureId, $onlyUse, $searchInfo = null, $withResultInfo = false)
    {
        $result = $this->commonMasterSearchApi('searchEcs', $featureId, $onlyUse, 'm_ecs_id', $searchInfo, $withResultInfo);
        return $result;
    }

    /**
     * 配送方法マスタ情報取得
     * @param string $featureId APIを呼び出している画面URL(ドメイン以降) 例)master/operators/list
     * @param bool $onlyUse true-使用中のみ / false-使用停止を含む
     * @param mixed $searchInfo (string)m_delivery_types_id / (array)search_info
     * @param bool $withResultInfo true-response以下を返却 / false-search_result以下を返却
     */
    public function searchDeliveryTypes($featureId, $onlyUse, $searchInfo = null, $withResultInfo = false)
    {
        $result = $this->commonMasterSearchApi('searchDeliveryTypes', $featureId, $onlyUse, 'm_delivery_types_id', $searchInfo, $withResultInfo);
        return $result;
    }

    /**
     * 配送方法-希望時間帯設定情報取得
     * @param string $featureId APIを呼び出している画面URL(ドメイン以降) 例)master/operators/list
     * @param bool $onlyUse true-使用中のみ / false-使用停止を含む
     * @param mixed $searchInfo (string)m_delivery_time_hope_map_id / (array)search_info
     * @param bool $withResultInfo true-response以下を返却 / false-search_result以下を返却
     */
    public function searchDeliveryTimeHopeMap($featureId, $onlyUse, $searchInfo = null, $withResultInfo = false)
    {
        $result = $this->commonMasterSearchApi('searchDeliveryTimeHopeMap', $featureId, $onlyUse, 'm_delivery_time_hope_map_id', $searchInfo, $withResultInfo);
        return $result;
    }

    /**
     * メールテンプレートマスタ情報取得
     * @param string $featureId APIを呼び出している画面URL(ドメイン以降) 例)master/operators/list
     * @param bool $onlyUse true-使用中のみ / false-使用停止を含む
     * @param mixed $searchInfo (string)m_email_templates_id / (array)search_info
     * @param bool $withResultInfo true-response以下を返却 / false-search_result以下を返却
     */
    public function searchMailTemplateInfo($featureId, $onlyUse, $searchInfo = null, $withResultInfo = false)
    {
        $result = $this->commonMasterSearchApi('searchEmailTemplates', $featureId, $onlyUse, 'm_email_templates_id', $searchInfo, $withResultInfo);
        return $result;
    }

    /**
     * 支払方法マスタ情報取得
     * @param string $featureId APIを呼び出している画面URL(ドメイン以降) 例)master/operators/list
     * @param bool $onlyUse true-使用中のみ / false-使用停止を含む
     * @param mixed $searchInfo (string)m_payment_types_id / (array)search_info
     * @param bool $withResultInfo true-response以下を返却 / false-search_result以下を返却
     */
    public function searchPaymentTypes($featureId, $onlyUse, $searchInfo = null, $withResultInfo = false)
    {
        $result = $this->commonMasterSearchApi('searchPaymentTypes', $featureId, $onlyUse, 'm_payment_types_id', $searchInfo, $withResultInfo);
        return $result;
    }

    /**
     * 倉庫マスタ情報取得
     * @param string $featureId APIを呼び出している画面URL(ドメイン以降) 例)master/operators/list
     * @param bool $onlyUse true-使用中のみ / false-使用停止を含む
     * @param mixed $searchInfo (string)m_warehouses_id / (array)search_info
     * @param bool $withResultInfo true-response以下を返却 / false-search_result以下を返却
     */
    public function searchWarehouses($featureId, $onlyUse, $searchInfo = null, $withResultInfo = false)
    {
        $result = $this->commonMasterSearchApi('searchWarehouses', $featureId, $onlyUse, 'm_warehouses_id', $searchInfo, $withResultInfo);
        return $result;
    }

    /**
     * SKUマスタ情報取得
     * @param string $featureId APIを呼び出している画面URL(ドメイン以降) 例)master/operators/list
     * @param mixed $searchInfo (string)m_ami_sku_id / (array)search_info
     * @param bool $withResultInfo true-response以下を返却 / false-search_result以下を返却
     */
    public function searchSku($featureId, $searchInfo = null, $withResultInfo = false)
    {
        $isCountSecondLevel = false;

        $result = $this->commonMasterSearchApi('searchSku', $featureId, false, 'm_ami_sku_id', $searchInfo, $withResultInfo, $isCountSecondLevel);
        return $result;
    }

    /**
     * 受注タグマスタ検索
     * @param mixed $searchInfo (string)m_order_tag_id / (array)search_info
     * @param bool $withResultInfo true-response以下を返却 / false-search_result以下を返却
     */
    public function searchOrderTagMaster($searchInfo = null, $withResultInfo = false)
    {
        $featureId = '';
        $onlyUse = false;
        $isCountSecondLevel = false;

        $result = $this->commonMasterSearchApi('searchOrderTagMaster', $featureId, $onlyUse, 'm_order_tag_id', $searchInfo, $withResultInfo, $isCountSecondLevel);
        return $result;
    }

    /**
     * 送り状システムマスタ取得
     * @param string $featureId APIを呼び出している画面URL(ドメイン以降) 例)master/operators/list
     * @param mixed $searchInfo (string)m_prefectual_id / (array)search_info
     * @param bool $withResultInfo true-response以下を返却 / false-search_result以下を返却
     */
    public function searchInvoiceSystem($featureId, $searchInfo = null, $withResultInfo = false)
    {
        $result = $this->commonMasterSearchApi('searchInvoiceSystem', $featureId, false, 'm_invoice_system_id', $searchInfo, $withResultInfo);
        return $result;
    }


    /**
     * 情報取得API共通
     */
    private function commonMasterSearchApi($apiKey, $featureId, $onlyUse, $searchKey, $searchInfo = null, $withResultInfo = false, $isCountSecondLevel = true)
    {
        // 実行API
        $apiUrl = $this->privateApiUris[$apiKey]['apiurl'];
        $subsys = $this->privateApiUris[$apiKey]['subsys'];

        // 共通条件構築
        $extends = [];
        foreach ($this->privateApiCommonRequires[$subsys] as $param) {
            if ($param == 'operator_id') {
                $extends[$param] = $this->getOperatorId();
            }
            if ($param == 'feature_id') {
                $extends[$param] = $featureId;
            }
            if ($param == 'display_csv_flag') {
                $extends[$param] = 1;
            }
            if ($param == 'account_cd') {
                $extends[$param] = $this->getAccountCode();
            }
            if ($param == 'm_account_id') {
                $extends[$param] = $this->getAccountId();
            }
        }

        $where = [];
        // 使用中のみ
        if ($onlyUse === true) {
            $where['delete_flg'] = '0';
        }
        // 個別条件あり
        if (isset($searchInfo)) {
            if (is_array($searchInfo) && count($searchInfo)) {
                $where = $searchInfo;
            } elseif (strlen($searchInfo) > 0) {
                $where[$searchKey] = $searchInfo;
            }
        }

        $result = [];
        $responseRows = $this->executeSearchApi($apiUrl, $subsys, $where, $extends);

        // response以下を返却
        if ($withResultInfo) {
            $result = $responseRows;
        }
        // search_result以下を返却
        else {
            $count = 0;
            if ($isCountSecondLevel === false) {
                $count = $responseRows['result']['total_record_count'];
            } else {
                $count = $responseRows['total_record_count'];
            }

            if ($count > 0) {
                $result = $responseRows['search_result'];
            }
        }

        return $result;
    }

    /**
     * カレントAPIキー名の設定
     */
    public function setCurrentApiKey($key)
    {
        $this->currentApiKey = $key;
    }

    /**
     * 検索一覧の取得
     */
    protected function getRows($data)
    {
        if (!isset($this->currentApiKey) || strlen($this->currentApiKey) == 0) {
            return parent::getRows($data);
        }

        unset($data->_token);

        $reqSearchData = [
            'search_info' => $this->getSearchData($data),
        ];

        $reqSearchData['m_account_id'] = $this->getAccountId();

        $reqSearchData = $this->addSearchParameterExtend($reqSearchData);

        $requestData = [
            'request' => $reqSearchData
        ];

        $subsys = $this->apiUris[$this->currentApiKey]['subsys'];
        $apiUrl = $this->apiUris[$this->currentApiKey]['apiurl'];

        $responseBody = $this->connectionApi($requestData, $apiUrl, $subsys);

        return $responseBody;
    }

    /**
     * 登録項目の検証APIにデータを渡して実行する
     */
    protected function registerCheck($data)
    {
        if (!isset($this->currentApiKey) || strlen($this->currentApiKey) == 0) {
            return parent::registerCheck($data);
        }

        unset($data->_token);

        $reqRegisterData = [
            'register_info' => $this->getRegisterData($data),
        ];

        $reqRegisterData['m_account_id'] = $this->getAccountId();

        $reqRegisterData = $this->addRegisterParameterExtend($reqRegisterData);

        $requestData = ['request' => $reqRegisterData];

        $subsys = $this->apiUris[$this->currentApiKey]['subsys'];
        $apiUrl = $this->apiUris[$this->currentApiKey]['apiurl'];

        $registerCheckData = $this->connectionApi($requestData, $apiUrl . '/check', $subsys);

        return json_decode($registerCheckData, true);
    }

    /**
     * 登録APIを実行する
     */
    protected function register($data)
    {
        if (!isset($this->currentApiKey) || strlen($this->currentApiKey) == 0) {
            return parent::register($data);
        }

        unset($data->_token);

        $reqRegisterData = [
            'register_info' => $this->getRegisterData($data),
        ];

        $reqRegisterData['m_account_id'] = $this->getAccountId();

        $reqRegisterData = $this->addRegisterParameterExtend($reqRegisterData);

        $requestData = ['request' => $reqRegisterData];

        $subsys = $this->apiUris[$this->currentApiKey]['subsys'];
        $apiUrl = $this->apiUris[$this->currentApiKey]['apiurl'];

        $registerCheckData = $this->connectionApi($requestData, $apiUrl, $subsys);

        return json_decode($registerCheckData, true);
    }

    /**
     * 詳細画面のデータ取得の実行
     */
    public function getInfoData($id)
    {
        if (!isset($this->currentApiKey) || strlen($this->currentApiKey) == 0) {
            return parent::getInfoData($id);
        }

        $searchData = [$this->searchPrimaryKey => $id];

        $reqData = [
            'search_info' => $searchData,
        ];

        $reqData['m_account_id'] = $this->getAccountId();


        $reqData = $this->setEditSearchExtend($reqData);

        $requestData = [
            'request' => $reqData
        ];

        $subsys = $this->apiUris[$this->currentApiKey]['subsys'];
        $apiUrl = $this->apiUris[$this->currentApiKey]['apiurl'];

        $responseRows = json_decode($this->connectionApi($requestData, $apiUrl, $subsys), true);

        return $responseRows['response']['search_result'][0];
    }

    /**
     * APIで取得したデータを配列で返却（検索用）
     */
    protected function executeSearchApi($connectionApiUrl, $subsystemName = null, $where = [], $extendData = [])
    {
        $requestData =  [
            'search_info' => $where,
            'm_account_id' => $this->getAccountId()
        ];

        foreach($extendData as $key => $row) {
            $requestData[$key] = $row;
        }

        $apiData = $this->connectionApi(['request' => $requestData], $connectionApiUrl, $subsystemName);

        $searchResult = json_decode($apiData, true);

        return $searchResult['response'];
    }

    /**
     * APIで取得したデータを配列で返却（登録・更新用）
     */
    protected function executeRegisterApi($connectionApiUrl, $subsystemName = null, $editData = [], $extendData = [], $checkFlg = '0')
    {
        $requestData =  [
            'register_info' => $editData,
            'm_account_id' => $this->getAccountId()
        ];

        foreach($extendData as $key => $row) {
            $requestData[$key] = $row;
        }

        $apiUrl = $connectionApiUrl;
        if ($checkFlg == '1') {
            $apiUrl = str_replace($apiUrl, '/check', '');
            $apiUrl = $apiUrl. '/check';
        }

        $apiData = $this->connectionApi(['request' => $requestData], $apiUrl, $subsystemName);

        $searchResult = json_decode($apiData, true);

        return $searchResult['response'];
    }

    /**
     * バージョンを取得.
     *
     * @return string バージョン
     */
    public function getVer()
    {
        return $this->_ver;
    }

    /**
     * バリデーションエラー取得.
     *
     * @param// array $request
     *
     * @return array
     */
    public function getValidationErrors($request)
    {
        $errors = [
            'messages' => [],
            'warning_tabs' => [],
        ];
        foreach ($request->session()->get('errors') as $ViewErrorBag) {
            foreach ((array) $ViewErrorBag as $bags) {
                foreach ((array) $bags as $errorMessages) {
                    foreach ((array) $errorMessages as $field => $errorMessage) {
                        $errors['messages'][$field] = $errorMessage;
                        $errors['warning_tabs'][str_replace(['_yahoo', '_amazon', '_rakuten', '_wowma'], '', substr($field, 0, strpos($field, '__')))] = 1;
                    }
                    break 3;
                }
            }
        }

        return $errors;
    }

    /**
     * APIを呼び出す.
     *
     * @param $method
     * @param $url
     * @param $json
     * @param $subsystem
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function callApi($method, $url, $json, $subsystem)
    {
        $apiDomain = Config::get('Common.const.API_DOMAIN');

        // $apiDomain の末尾にスラッシュが無ければ追加
        if (substr($apiDomain, -1) !== '/') {
            $apiDomain .= '/';
        }

        $client = new \GuzzleHttp\Client([
            'base_uri' => 'http://'.$apiDomain,
        ]);

        return $client->request($method, $url, ['json' => $json]);
    }

    /**
     * マスタ検索APIを呼び出す.
     *
     * @param $search_info
     * @param $modelName
     * @param string $subsystem
     * @param null   $accountId
     * @param null   $operaterId
     *
     * @return mixed
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function callSearchApi($search_info, $modelName, $subsystem = 'master', $accountId = null, $operaterId = null)
    {
        $opeInfo = session()->get('OperatorInfo');

        if (!empty($accountId)) {
            $reqAccountId = $accountId;
        } else {
            $reqAccountId = !empty($opeInfo) ? $opeInfo['m_account_id'] : '';
        }

        if (!empty($operaterId)) {
            $reqOperatorId = $operaterId;
        } else {
            $reqOperatorId = !empty($opeInfo) ? $opeInfo['m_operators_id'] : '';
        }

        $json = [
            'request' => [
                'm_account_id' => $reqAccountId,
                'operator_id' => $reqOperatorId,
                'feature_id' => str_replace(Config::get('Common.const.APP_URL'), '', url()->current()),
                'display_csv_flag' => 0,
                'search_info' => $search_info,
            ],
        ];

        $response = $this->callApi('POST', $subsystem.'/'.$this->getVer().'/search'.$modelName, $json, $subsystem);
        $body = json_decode($response->getBody(), true);
        if (!empty($body['response']['error']['code'])) {
            dd($body);
        }

        return $body;
    }

    /**
     * register系APIを呼び出す.
     *
     * @param $register_info
     * @param $modelName
     * @param $subsystem
     * @param null $accountId
     * @param null $operaterId
     *
     * @return mixed
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function callRegisterApi($register_info, $modelName, $subsystem, $accountId = null, $operaterId = null)
    {
        $opeInfo = session()->get('OperatorInfo');

        if (!empty($accountId)) {
            $reqAccountId = $accountId;
        } else {
            $reqAccountId = !empty($opeInfo) ? $opeInfo['m_account_id'] : '';
        }

        if (!empty($operaterId)) {
            $reqOperatorId = $operaterId;
        } else {
            $reqOperatorId = !empty($opeInfo) ? $opeInfo['m_operators_id'] : '';
        }

        $json = [
            'request' => [
                'm_account_id' => $reqAccountId,
                'operator_id' => $reqOperatorId,
                'feature_id' => str_replace(Config::get('Common.const.APP_URL'), '', url()->current()),
                'register_info' => $register_info,
            ],
        ];
        $response = $this->callApi('POST', $subsystem.'/'.$this->getVer().'/register'.$modelName, $json, $subsystem);

        $body = json_decode($response->getBody(), true);
        if (!empty($body['response']['error']['code'])) {
            throw new ApiException(json_encode($body['response']['error']));
        }

        return $body['response']['search_result'];
    }

    /**
     * エラーログを出力する.
     *
     * @param $data array ログ出力内容
     */
    public function errorLog($data)
    {
        $logPath = $this->errorLogOutDir.$this->apiID.'_'.date('Ymd').'.log';
        file_put_contents($logPath, join(', ', $data)."\n", FILE_APPEND);
    }

    /**
     * 情報ログを出力する.
     *
     * @param $data
     */
    public function infoLog($data)
    {
        $logPath = $this->infoLogOutDir.$this->apiID.'_'.date('Ymd').'.log';
        file_put_contents($logPath, join(', ', $data)."\n", FILE_APPEND);
    }

    /**
     * 画面CDに一致するぱんくずデータを取得.
     *
     * @param $screenCd
     *
     * @return array
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function searchBreadcrumb($screenCd)
    {
        $condition = [
            'display_screen_cd' => $screenCd,
        ];

        $res = $this->callSearchApi($condition, 'Breadcrumb', 'gcommon');
        $result = [];

        if (!empty($res) && array_key_exists('response', $res) && 0 < count($res['response']['search_result'])) {
            $result = $res['response']['search_result'][0];
        }

        return $result;
    }

    /**
     * お知らせ情報確認日時／検索.
     *
     * @param null $accountId
     * @param null $operatorId
     *
     * @return array
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function searchNoticeUserConfirmInfo($accountId = null, $operatorId = null)
    {
        $ids = $this->getRequiredId($accountId, $operatorId);
        if (count($ids) === 0) {
            return [];
        }

        $json = [
            'request' => [
                'm_account_id' => $ids['AccountID'],
                'operator_id' => $ids['OperatorID'],
                'feature_id' => str_replace(Config::get('Common.const.APP_URL'), '', url()->current()),
                'display_csv_flag' => 0,
                'search_info' => [
                ],
            ],
        ];
        $response = $this->callApi('POST', 'common/'.$this->getVer().'/searchNoticeUserConfirmInfo', $json, 'common');
        $body = json_decode($response->getBody(), true);
        if (!empty($body['response']['error']['code'])) {
            dd($body);
        }

        return $body['response']['search_result'];
    }

    /**
     * お知らせ情報確認日時／登録・更新.
     *
     * @return array
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function registerNoticeConfirm()
    {
        $opeInfo = session()->get('OperatorInfo');
        if (empty($opeInfo)) {
            return [];
        }

        $json = [
            'request' => [
                'm_account_id' => $opeInfo['m_account_id'],
                'operator_id' => $opeInfo['m_operators_id'],
                'feature_id' => str_replace(Config::get('Common.const.APP_URL'), '', url()->current()),
                'display_csv_flag' => 0,
                'search_info' => [
                    'notice_confirm_date' => Carbon::now($this->timeZone)->format('Y-m-d H:i:s'),
                ],
            ],
        ];
        $response = $this->callApi('POST', 'common/'.$this->getVer().'/registerNoticeConfirm', $json, 'common');
        $body = json_decode($response->getBody(), true);
        if (!empty($body['response']['error']['code'])) {
            dd($body);
        }

        return $body['response']['search_result'];
    }

    /**
     * アラート情報確認日時／検索.
     *
     * @param null $accountId
     * @param null $operatorId
     *
     * @return array
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function searchSystemLogConfirmInfo($accountId = null, $operatorId = null)
    {
        $ids = $this->getRequiredId($accountId, $operatorId);

        if (count($ids) === 0) {
            return [];
        }

        $json = [
            'request' => [
                'm_account_id' => $ids['AccountID'],
                'operator_id' => $ids['OperatorID'],
                'feature_id' => str_replace(Config::get('Common.const.APP_URL'), '', url()->current()),
                'display_csv_flag' => 0,
                'search_info' => [
                ],
            ],
        ];
        $response = $this->callApi('POST', 'common/'.$this->getVer().'/searchSystemLogConfirmInfo', $json, 'common');
        $body = json_decode($response->getBody(), true);
        if (!empty($body['response']['error']['code'])) {
            dd($body);
        }

        return $body['response']['search_result'];
    }

    /**
     * アラート情報確認日時／登録・更新.
     *
     * @return array
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function registerSystemLogConfirmInfo()
    {
        try {
            $opeInfo = session()->get('OperatorInfo');
            if (empty($opeInfo)) {
                return [];
            }

            $json = [
                'request' => [
                    'm_account_id' => $opeInfo['m_account_id'],
                    'operator_id' => $opeInfo['m_operators_id'],
                    'feature_id' => str_replace(Config::get('Common.const.APP_URL'), '', url()->current()),
                    'display_csv_flag' => 0,
                    'search_info' => [
                        'alert_confirm_date' => Carbon::now($this->timeZone)->format('Y-m-d H:i:s'),
                    ],
                ],
            ];

            $response = $this->callApi('POST', 'common/'.$this->getVer().'/registerAlertConfirm', $json, 'common');
            $body = json_decode($response->getBody(), true);
            if (!empty($body['response']['error']['code'])) {
                dd($body);
            }

            return $body['response']['search_result'];
        } catch (\Exception $e) {
            Log::error('registerSystemLogConfirmInfo error:' . $e->getMessage());
            return [];
        }
    }

    /**
     * 画面情報取得.
     *
     * @param $screenCd
     *
     * @return array
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function searchScreens($screenCd)
    {
        try {
            // TODO： 仕様が確定するまで疎通レベルでの呼び出しを行う
            $opeInfo = session()->get('OperatorInfo');
            if (empty($opeInfo)) {
                return [];
            }

            $json = [
                'request' => [
                    'm_account_id' => $opeInfo['m_account_id'],
                    'operator_id' => $opeInfo['m_operators_id'],
                    'feature_id' => str_replace(Config::get('Common.const.APP_URL'), '', url()->current()),
                    'display_csv_flag' => 0,
                    'search_info' => [
                        'screen_cd' => $screenCd,
                        'search_use_type' => 1,
                        'delete_flg' => 0,
                    ],
                ],
            ];

            $response = $this->callApi('POST', 'gcommon/'.$this->getVer().'/searchScreens', $json, 'gcommon');
            $body = json_decode($response->getBody(), true);
            if (!empty($body['response']['error']['code'])) {
                dd($body);
            }

            return $body['response']['search_result'];
        } catch (\Exception $e) {
            Log::error('searchScreens error:' . $e->getMessage());
            return [];
        }
    }

    /**
     * ページナビ情報取得.
     *
     * @param $screenCd
     *
     * @return array
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function searchPagenavi($screenCd)
    {
        try {
            $opeInfo = session()->get('OperatorInfo');
            if (empty($opeInfo)) {
                return [];
            }
    
            $json = [
                'request' => [
                    'm_account_id' => $opeInfo['m_account_id'],
                    'operator_id' => $opeInfo['m_operators_id'],
                    'feature_id' => str_replace(Config::get('Common.const.APP_URL'), '', url()->current()),
                    'display_csv_flag' => 0,
                    'search_info' => [
                        'delete_flg' => 0,
                        'search_use_type' => 1,
                        'screen_cd' => $screenCd,
                    ],
                ],
            ];
    
            $response = $this->callApi('POST', 'gcommon/'.$this->getVer().'/searchPagenavi', $json, 'gcommon');
            $body = json_decode($response->getBody(), true);
            if (!empty($body['response']['error']['code'])) {
                dd($body);
            }
    
            return $body['response']['search_result'];
        } catch (\Exception $e) {
            Log::error('searchPagenavi error:' . $e->getMessage());
            return [];
        }
    }

    /**
     * メニュー情報取得.
     *
     * @param $menuList
     *
     * @return array
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function searchMenu($menuList)
    {
        try {
            $opeInfo = session()->get('OperatorInfo');
            if (empty($opeInfo)) {
                return [];
            }
    
            $json = [
                'request' => [
                    'm_account_id' => $opeInfo['m_account_id'],
                    'operator_id' => $opeInfo['m_operators_id'],
                    'feature_id' => str_replace(Config::get('Common.const.APP_URL'), '', url()->current()),
                    'display_csv_flag' => 0,
                    'search_info' => [
                        'delete_flg' => 0,
                        'search_use_type' => 1,
                        'menu_type' => $menuList,
                    ],
                ],
            ];
    
            //        info($json);
            $response = $this->callApi('POST', 'gcommon/'.$this->getVer().'/searchMenus', $json, 'gcommon');
            $body = json_decode($response->getBody(), true);
            //        info($body);
    
            if (!empty($body['response']['error']['code'])) {
                dd($body);
            }
    
            $menus = [];
            foreach ($body['response']['search_result'] as $val) {
                if (!empty($val['menu_position_third_display_name'])) {
                    $menus[$val['menu_position_first_display_name']]['children'][$val['menu_position_second_display_name']]['children'][$val['menu_position_third_display_name']]['children'][$val['m_menus_id']] = $val;
                } elseif (!empty($val['menu_position_second_display_name'])) {
                    $menus[$val['menu_position_first_display_name']]['children'][$val['menu_position_second_display_name']]['children'][$val['m_menus_id']] = $val;
                } elseif (!empty($val['menu_position_first_display_name'])) {
                    $menus[$val['menu_position_first_display_name']]['children'][$val['m_menus_id']] = $val;
                } else {
                    $menus[$val['m_menus_id']] = $val;
                    $menus[$val['m_menus_id']]['children'] = [];
                }
            }
    
            return $menus;
        } catch (\Exception $e) {
            Log::error('searchMenu error:' . $e->getMessage());
            return [];
        }
    }

    /**
     * お知らせ一覧を取得する
     * 　・未ログイン時・・・表示区分：全公開
     * 　・ログイン時・・・表示区分：全公開、ログイン時のみ　＆　お知らせ情報確認日時＜お知らせ表示開始日.
     *
     * @param bool $isAllOpen
     * @param null $accountId
     * @param null $operatorId
     *
     * @return mixed
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function searchNotice($isAllOpen = true, $accountId = null, $operatorId = null)
    {
        try {
            $ids = $this->getRequiredId($accountId, $operatorId);

            //      TODO: gCommonのお知らせ情報取得APIにてソート対応された場合はここで公開日降順のソート条件を追加する。
            $search_info = [
                'delete_flg' => 0,
            ];

            if ($isAllOpen) {
                // 全公開のお知らせを取得
                $search_info += ['notice_display_flg' => '0'];
            } else {
                $noticeConfirm = $this->searchNoticeUserConfirmInfo();
                if (0 < count($noticeConfirm)) {
                    //                TODO: gCommonのお知らせ情報取得APIにて以上・以下の検索条件に対応された場合はここで確認日以降を取得する検索条件を追加する
                    $search_info += [
                        'notice_display_date_from' => $noticeConfirm[0]['notice_confirm_date'],
                    ];
                }
            }

            $json = [
                'request' => [
                    'm_account_id' => empty($ids['AccountID']) ?? '',
                    'operator_id' => empty($ids['OperatorID']) ?? '',
                    'feature_id' => str_replace(Config::get('Common.const.APP_URL'), '', url()->current()),
                    'display_csv_flag' => 0,
                    'search_info' => $search_info,
                ],
            ];
            $response = $this->callApi('POST', 'gcommon/'.$this->getVer().'/searchNotices', $json, 'gcommon');
            $body = json_decode($response->getBody(), true);
            if (!empty($body['response']['error']['code'])) {
                dd($body);
            }

            return $body['response']['search_result'];
        } catch (\Exception $e) {
            Log::error('searchNotice error:' . $e->getMessage());
            return [];
        }
    }

    /**
     * バッチ実行指示情報を取得
     * 　・ログインユーザーの社員IDが一致かつ実行結果が「0：正常」以外のデータを取得.
     *
     * @param null $accountId
     * @param null $operatorId
     *
     * @return array
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function searchBatchInstruction($accountId = null, $operatorId = null)
    {
        $ids = $this->getRequiredId($accountId, $operatorId);
        if (count($ids) === 0) {
            return [];
        }

        $search_info = [
            'm_operator_id' => $ids['OperatorID'],
            'orderby' => 'batchjob_end_datetime',
            'desc' => '1',
        ];

        $sysLogConfirm = $this->searchSystemLogConfirmInfo($ids['AccountID'], $ids['OperatorID']);

        if (0 < count($sysLogConfirm)) {
            $search_info += [
                'batchjob_end_datetime_value_more' => $sysLogConfirm[0]['alert_confirm_date'],
            ];
        }

        $json = [
            'request' => [
                'm_account_id' => $ids['AccountID'],
                'operator_id' => $ids['OperatorID'],
                'feature_id' => str_replace(Config::get('Common.const.APP_URL'), '', url()->current()),
                'display_csv_flag' => 0,
                'search_info' => $search_info,
            ],
        ];
        $response = $this->callApi('POST', 'common/'.$this->getVer().'/searchExecuteBatchInstruction', $json, 'common');
        $body = json_decode($response->getBody(), true);
        if (!empty($body['response']['error']['code'])) {
            dd($body);
        }

        return $body['response']['search_result'];
    }

    /**
     * 企業IDと社員IDがパラメータで渡されていない場合はセッションから取得して返却.
     *
     * @param null $accountId
     * @param null $operatorId
     *
     * @return array
     */
    protected function getRequiredId($accountId = null, $operatorId = null)
    {
        if (empty($accountId) && empty($operatorId)) {
            $opeInfo = session()->get('OperatorInfo');
            if (empty($opeInfo)) {
                return [];
            }

            $accId = $opeInfo['m_account_id'];
            $opeId = $opeInfo['m_operators_id'];
        } else {
            $accId = $accountId;
            $opeId = $operatorId;
        }

        return [
            'AccountID' => $accId,
            'OperatorID' => $opeId,
        ];
    }
}
