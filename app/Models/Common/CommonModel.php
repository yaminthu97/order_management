<?php

namespace App\Models\Common;

/**
 * API 顧客モデル基底クラス
 *
 * @author Ryougo Himeno
 * @copyright 2018-2018 Scroll360
 * @category Cc
 * @package Manager
 */
class CommonModel extends ApiSyscomModel
{
    /**
     * 内部使用API URL配列
     */
    private $privateApiUris = [
        'searchMailTemplateInfo'	=> [ 'subsys' => 'master', 'apiurl' => 'searchMailTemplateInfo' ],		// メールテンプレートマスタ情報取得
        'searchPrefectural'			=> [ 'subsys' => 'global', 'apiurl' => 'searchPrefectural' ],           // 都道府県
        'searchOrderListcond'		=> [ 'subsys' => 'order', 'apiurl' => 'searchOrderListcond' ],           // 受注検索保存
    ];

    /**
     * メールテンプレートマスタ情報取得
     * @param string $requestData リクエストデータ
     * @param string $featureId APIを呼び出している画面URL(ドメイン以降) 例)master/operators/list
     * @param bool $onlyUse true-使用中のみ / false-使用停止を含む
     * @param mixed $searchInfo (string)m_email_templates_id / (array)search_info
     * @param bool $withResultInfo true-response以下を返却 / false-search_result以下を返却
     */
    public function searchMailTemplateInfo($requestData, $featureId, $onlyUse, $searchInfo = null, $withResultInfo = false)
    {
        $result = $this->commonMasterSearchApi('searchMailTemplateInfo', $requestData, $featureId, $onlyUse, 'm_email_templates_id', $searchInfo, $withResultInfo);
        return $result;
    }

    /**
     * 都道府県情報取得
     * @param string $requestData リクエストデータ
     * @param string $featureId APIを呼び出している画面URL(ドメイン以降) 例)master/operators/list
     * @param bool $onlyUse true-使用中のみ / false-使用停止を含む
     * @param mixed $searchInfo (string)m_email_templates_id / (array)search_info
     * @param bool $withResultInfo true-response以下を返却 / false-search_result以下を返却
     */
    public function searchPrefectural($requestData, $featureId, $onlyUse, $searchInfo = null, $withResultInfo = false)
    {
        $result = $this->commonMasterSearchApi('searchPrefectural', $requestData, $featureId, $onlyUse, 'm_prefectural_id', $searchInfo, $withResultInfo);
        return $result;
    }

    /**
     * 情報取得API共通
     */
    private function commonMasterSearchApi($apiKey, $requestData, $featureId, $onlyUse, $searchKey, $searchInfo = null, $withResultInfo = false, $isCountSecondLevel = true)
    {
        // 実行API
        $apiUrl = $this->privateApiUris[$apiKey]['apiurl'];
        $subsys = $this->privateApiUris[$apiKey]['subsys'];

        $extends = [
            'm_account_id'		=> $requestData['m_account_id'],
            'operator_id'		=> $this->getOperatorId($requestData),
            'feature_id'		=> $featureId,
            'display_csv_flag'	=> '0',
        ];

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
     * APIで取得したデータを配列で返却（検索用）
     */
    protected function executeSearchApi($connectionApiUrl, $subsystemName = null, $where = [], $extendData = [])
    {
        $requestData =  [
            'search_info' => $where,
        ];

        foreach($extendData as $key => $row) {
            $requestData[$key] = $row;
        }

        $apiData = $this->getApiData(['request' => $requestData], $subsystemName, $connectionApiUrl);

        return $apiData['response'];
    }

    /**
     * 郵便番号の正当性を検証する
     */
    protected function checkPostal($postalCode, $sevenOnly = false)
    {
        $isValid = false;

        // 7桁のみを認めるかどうか
        if($sevenOnly) {
            if(strlen($postalCode) != 7) {
                return false;
            }

            $isValid = preg_match('/^\d{7}$/', $postalCode);
        } else {
            // 7桁or8桁（ハイフン込み）でない場合
            if(strlen($postalCode) != 7 && strlen($postalCode) != 8) {
                return false;
            }

            $isValid = preg_match('/^\d{3}-\d{4}$/', $postalCode);

            if(!$isValid) {
                $isValid = preg_match('/^\d{7}$/', $postalCode);
            }
        }

        return $isValid;
    }
}
