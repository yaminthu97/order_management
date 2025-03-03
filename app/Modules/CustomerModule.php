<?php

namespace App\Modules;

use App\Modules\Common\CommonModule;

class CustomerModule extends CommonModule
{
    /**
     * API URL
     */
    protected $searchUri = 'searchCustomer';
    protected $registerUri = 'registerCustomer';
    protected $subsysName = 'Customer';

    /**
     * 検索画面に追加で渡したいデータをセットする
     */
    public function setSearchExtendData()
    {
        //都道府県
        $prefData = $this->apiSearchPrefectual();

        $custRank = $this->apiSearchItemnameTypes();

        $contactWayTypes = [];
        if(count($custRank) > 0) {
            $contactWayTypes = $custRank['contactWayTypes'];
        }

        return [
            'pref' => $prefData,
            'contactWayTypes' => $contactWayTypes,
        ];
    }

    /**
     * 検索用データを取得する（都道府県マスタ情報取得API）
     */
    private function apiSearchPrefectual()
    {
        $featureId = 'cc/customer/info/';
        $resuit = $this->searchPrefectual($featureId);
        return $resuit;
    }

    /**
     * 検索用データを取得する（顧客ランク）
     */
    private function apiSearchItemnameTypes()
    {
        $newPresetData = [];

        $operatorId = $this->getOperatorId();
        $extendData = [
                'm_account_id' => $this->getAccountId()
            ,	'operator_id' => $operatorId
            ,	'feature_id' => 'order/orderBundle/list'
            ,	'display_csv_flag' => '0'
        ];

        $callSubSystem = 'master';
        $searchUrl = 'searchItemnameTypes';
        $searchInfoData = ['delete_flg' => '0', 'm_itemname_type' => '3'];

        $masterEcs = $this->getValueArray($searchUrl, 'm_itemname_type_name', 'm_itemname_types_id', $callSubSystem, $searchInfoData, $extendData);
        if (isset($masterEcs) && (!empty($masterEcs))) {
            $newPresetData['contactWayTypes'] = $masterEcs;
        }

        return $newPresetData;
    }



    /**
     * 検索用データを加工する
     */
    protected function editSearchData($data)
    {
        // 使用区分
        if(isset($data['delete_flg']['0']) && isset($data['delete_flg']['1'])) {
            $data = array_merge($data, ['delete_flg' => '0,1']);
        } elseif(isset($data['delete_flg']['0'])) {
            $data = array_merge($data, ['delete_flg' => $data['delete_flg']['0']]);
        }

        // 顧客ランク
        if(isset($data['m_cust_runk_id'])) {
            $mCustRunkId = '';
            foreach($data['m_cust_runk_id'] as $m_cust_runk_id => $m_cust_runk_value) {
                if($mCustRunkId == '') {
                    $mCustRunkId = $m_cust_runk_value;
                } else {
                    $mCustRunkId = $mCustRunkId.','.$m_cust_runk_value;
                }
            }
            $data = array_merge($data, ['m_cust_runk_id' => $mCustRunkId ]);
        }

        // 性別
        if(isset($data['sex_type']['0']) && isset($data['sex_type']['1']) && isset($data['sex_type']['2'])) {
            $data = array_merge($data, ['sex_type' => '0,1,2']);
        } elseif(isset($data['sex_type']['0']) && isset($data['sex_type']['1'])) {
            $str = $data['sex_type']['0'] .','. $data['sex_type']['1'];
            $data = array_merge($data, ['sex_type' => $str]);
        } elseif(isset($data['sex_type']['0'])) {
            $data = array_merge($data, ['sex_type' => $data['sex_type']['0']]);
        }

        // 備考の有無
        if(isset($data['note_existence']['0']) && isset($data['note_existence']['1'])) {
            $data = array_merge($data, ['note_existence' => '1,2']);
        } elseif(isset($data['note_existence']['0'])) {
            $data = array_merge($data, ['note_existence' => $data['note_existence']['0']]);
        }

        // 要注意区分
        if(isset($data['alert_cust_type']['0']) && isset($data['alert_cust_type']['1']) && isset($data['alert_cust_type']['2'])) {
            $data = array_merge($data, ['alert_cust_type' => '0,1,2']);
        } elseif(isset($data['alert_cust_type']['0']) && isset($data['alert_cust_type']['1'])) {
            $str = $data['alert_cust_type']['0'] .','. $data['alert_cust_type']['1'];
            $data = array_merge($data, ['alert_cust_type' => $str]);
        } elseif(isset($data['alert_cust_type']['0'])) {
            $data = array_merge($data, ['alert_cust_type' => $data['alert_cust_type']['0']]);
        }

        return $data;
    }

    /**
     * 検索時に検索データと別にパラメータを追加する
     */
    protected function addSearchParameterExtend($reqSearchData)
    {
        $reqSearchData = $reqSearchData + [
            'display_csv_flag' => 1,
            'list_detail_flg' => 0
        ];

        return $reqSearchData;
    }


    /**
     * 出力データを加工するための処理
     */
    public function getListData($dataResult)
    {
        $custRank = $this->apiSearchItemnameTypes();

        foreach($dataResult as $key => $dataRow) {
            // 備考
            if(isset($dataRow['note']) && strlen($dataRow['note']) > 100) {
                $dataRow = array_merge($dataRow, [ 'note_min' => $dataRow['note'] ]);

                $result = mb_strcut($dataRow['note'], 0, 100, 'UTF8');
                $dataRow['note'] = $result . '...';

            } else {
                $dataRow = array_merge($dataRow, [ 'note_min' => null ]);
            }

            // 顧客ランク
            if(isset($dataRow['m_cust_runk_id'])) {
                if(count($custRank) > 0) {
                    $dataRow['m_cust_runk_id'] = array_search($dataRow['m_cust_runk_id'], $custRank['contactWayTypes']);
                } else {
                    $dataRow['m_cust_runk_id'] = '';
                }
            }

            // 郵便番号
            if(isset($dataRow['postal']) && strlen($dataRow['postal']) > 0) {
                $dataRow['postal'] = substr($dataRow['postal'], 0, 3) .'-'.substr($dataRow['postal'], 3, 4);
            }

            $dataResult[$key] = $dataRow;

        }

        return parent::getListData($dataResult);
    }

    /**
     * 複数項目のいずれかのうち、存在する項目を出力する定義
     */
    protected $multiChoiceColumn = [
        'email' => ['email1','email2','email3','email4','email5'],
    ];

    /**
     * 編集時に取得する際のデータの主キー名称
     */
    protected $searchPrimaryKey = 'm_cust_id';

    /**
     * 顧客登録修正画面ID
     */
    protected $registerCustomerURL = 'ccApi/registerCustmer';

    /**
     * 顧客ランクをセットする
     */
    public function setCustRunkData($editRow)
    {
        $custRank = [];

        //コンボボックス用
        $connectionApiUrl = 'searchItemnameTypes';
        $where = [];
        $where['delete_flg'] = 0;
        $where['m_itemname_type'] = 3;
        $extendData = [];
        $extendData['operator_id'] = $this->getOperatorId();
        $extendData['feature_id'] = $this->registerCustomerURL;
        $extendData['display_csv_flag'] = 0;
        $value = [];
        $value[''] = '';

        $response = $this->executeSearchApi($connectionApiUrl, 'master', $where, $extendData);
        if (isset($response['search_result'])) {
            foreach($response['search_result'] as $resRow) {
                $value[$resRow['m_itemname_types_id']] = $resRow['m_itemname_type_name'];
            }
            $custRank['cust_runk_list'] = $value;
        }

        return $custRank;
    }
    /**
     * 都道府県をセットするをセットする
     */
    public function setPrefectual()
    {
        $prefectual = [];
        $response = $this->searchPrefectual($this->registerCustomerURL, null, true);
        if (isset($response['search_result'])) {
            foreach($response['search_result'] as $resRow) {
                $value[$resRow['prefectual_name']] = $resRow['prefectual_name'];
            }
            $prefectual['pref'] = $value;
        }
        return $prefectual;
    }
    /**
     * 登録画面に追加で渡したいデータをセットする
     */
    public function setRegisterExtendData($editRow, $pKey = null)
    {
        //顧客ランクをセットする
        $custRunkdata = $this->setCustRunkData($editRow);
        //都道府県をセットする
        $prefData = $this->setPrefectual();
        // 		$prefData = [];
        // 		$prefData['pref'] = array('北海道'=>'北海道');
        $extenddata = [];
        $extenddata = $custRunkdata + $prefData;

        return $extenddata;

    }

    /**
     * 検索用データを加工する
     */
    protected function editRegisterData($data)
    {
        $operatorId = $this->getOperatorId();

        if(!empty($data[$this->searchPrimaryKey])) {
            // 修正なら顧客IDがセットされるはずなので名寄せしない
            $data['name_sorting_flg'] = '1';
        } else {
            //名寄せする
            $data['name_sorting_flg'] = '0';
        }
        //性別
        if (isset($data['sex_type']) && is_array($data['sex_type'])) {
            $data['sex_type'] = implode(',', $data['sex_type']);
        }
        //要注意顧客区分
        if (isset($data['alert_cust_type']) && is_array($data['alert_cust_type'])) {
            $data['alert_cust_type'] = implode(',', $data['alert_cust_type']);
        }
        //削除フラグ
        if (isset($data['delete_flg'])) {
            //			$data['delete_flg'] = $data['delete_flg'][0];
            if (is_array($data['delete_flg'])) {
                $data['delete_flg'] = implode(',', $data['delete_flg']);
            }
            if($data['delete_flg'] != '1') {
                $data['delete_flg'] = '';
            }
        } else {
            $data['delete_flg'] = '';
        }
        //郵便番号ハイフン除去
        if (isset($data['postal'])) {
            $data['postal'] = str_replace('-', '', $data['postal']);
        }

        //未設定項目の追加
        //顧客コード
        if (!isset($data['cust_cd'])) {
            $data['cust_cd'] = '';
        }
        //電話番号
        if (!isset($data['tel1'])) {
            $data['tel1'] = '';
        }
        if (!isset($data['tel2'])) {
            $data['tel2'] = '';
        }
        if (!isset($data['tel3'])) {
            $data['tel3'] = '';
        }
        if (!isset($data['tel4'])) {
            $data['tel4'] = '';
        }
        //FAX番号
        if (!isset($data['fax'])) {
            $data['fax'] = '';
        }
        //フリガナ
        if (!isset($data['name_kana'])) {
            $data['name_kana'] = '';
        }
        //住所
        if (!isset($data['address3'])) {
            $data['address3'] = '';
        }
        if (!isset($data['address4'])) {
            $data['address4'] = '';
        }
        //メールアドレス
        if (!isset($data['email1'])) {
            $data['email1'] = '';
        }
        if (!isset($data['email2'])) {
            $data['email2'] = '';
        }
        if (!isset($data['email3'])) {
            $data['email3'] = '';
        }
        if (!isset($data['email4'])) {
            $data['email4'] = '';
        }
        if (!isset($data['email5'])) {
            $data['email5'] = '';
        }
        //備考
        if (!isset($data['note'])) {
            $data['note'] = '';
        }
        //誕生日
        if (!isset($data['birthday'])) {
            $data['birthday'] = '';
        }
        //法人情報
        //フリガナ
        if (!isset($data['corporate_kana'])) {
            $data['corporate_kana'] = '';
        }
        //法人名・団体名
        if (!isset($data['corporate_kanji'])) {
            $data['corporate_kanji'] = '';
        }
        //部署名
        if (!isset($data['division_name'])) {
            $data['division_name'] = '';
        }
        //勤務先電話番号
        if (!isset($data['corporate_tel'])) {
            $data['corporate_tel'] = '';
        }
        //顧客ランク
        if (!isset($data['m_cust_runk_id'])) {
            $data['m_cust_runk_id'] = '';
        }
        //要注意コメント
        if (!isset($data['alert_cust_comment'])) {
            $data['alert_cust_comment'] = '';
        }
        //自由項目
        if (!isset($data['reserve1'])) {
            $data['reserve1'] = '';
        }
        if (!isset($data['reserve2'])) {
            $data['reserve2'] = '';
        }
        if (!isset($data['reserve3'])) {
            $data['reserve3'] = '';
        }
        if (!isset($data['reserve4'])) {
            $data['reserve4'] = '';
        }
        if (!isset($data['reserve5'])) {
            $data['reserve5'] = '';
        }
        if (!isset($data['reserve6'])) {
            $data['reserve6'] = '';
        }
        if (!isset($data['reserve7'])) {
            $data['reserve7'] = '';
        }
        if (!isset($data['reserve8'])) {
            $data['reserve8'] = '';
        }
        if (!isset($data['reserve9'])) {
            $data['reserve9'] = '';
        }
        if (!isset($data['reserve10'])) {
            $data['reserve10'] = '';
        }
        if (!isset($data['reserve11'])) {
            $data['reserve11'] = '';
        }
        if (!isset($data['reserve12'])) {
            $data['reserve12'] = '';
        }
        if (!isset($data['reserve13'])) {
            $data['reserve13'] = '';
        }
        if (!isset($data['reserve14'])) {
            $data['reserve14'] = '';
        }
        if (!isset($data['reserve15'])) {
            $data['reserve15'] = '';
        }
        if (!isset($data['reserve16'])) {
            $data['reserve16'] = '';
        }
        if (!isset($data['reserve17'])) {
            $data['reserve17'] = '';
        }
        if (!isset($data['reserve18'])) {
            $data['reserve18'] = '';
        }
        if (!isset($data['reserve19'])) {
            $data['reserve19'] = '';
        }
        if (!isset($data['reserve20'])) {
            $data['reserve20'] = '';
        }
        //削除ボタン押下
        // if (isset($data['submit_register']))
        // {
        // 	if ($data['submit_register'] == '削除')
        // 	{
        // 	    $data['delete_operator_id'] = $operatorId;

        // 	}
        // 	else{
        // 		if (isset($data['delete_operator_id']))
        // 		{
        // 			unset($data['delete_operator_id']);
        // 		}
        // 	}
        // }
        if((isset($data['submit_delete']))) {
            $data['delete_operator_id'] = $operatorId;
        } else {
            if (isset($data['delete_operator_id'])) {
                unset($data['delete_operator_id']);
            }
        }
        //作業ユーザID
        $data['operator_id'] = $operatorId;

        return $data;
    }
    /**
     * 編集画面のAPIで追加する追加パラメータのセット
     * @param array $reqData
     * @return array
     */
    protected function setEditSearchExtend($reqData)
    {
        //一覧/詳細フラグ
        $reqData['list_detail_flg'] = '1';
        //削除顧客を含む
        $reqData['search_info']['delete_include'] = '1';

        return $reqData;
    }
    /**
     * editRowを編集
     */
    public function setEditRow($editRow)
    {
        //使用区分
        if (isset($editRow["delete_flg"]) && is_array($editRow["delete_flg"])) {
            $editRow["delete_flg"] = implode(',', $editRow['delete_flg']);
        }
        //性別
        if (isset($editRow["sex_type"]) && is_array($editRow["sex_type"])) {
            $editRow["sex_type"] = implode(',', $editRow['sex_type']);
        }
        //要注意区分
        if (isset($editRow["alert_cust_type"]) && is_array($editRow["alert_cust_type"])) {
            $editRow["alert_cust_type"] = implode(',', $editRow['alert_cust_type']);
        }
        //生年月日
        if (isset($editRow["birthday"]) && $editRow["birthday"] == '0000-00-00') {
            $editRow["birthday"] = '';
        }
        //郵便番号（ハイフン付加）
        if (isset($editRow["postal"]) && strlen($editRow['postal']) > 0 && strpos($editRow["postal"], '-') === false) {
            if (mb_strlen($editRow["postal"]) == 7) {
                $editRow["postal"] = substr($editRow["postal"], 0, 3).'-'.substr($editRow["postal"], 3);
            }
        }

        return $editRow;
    }

    /**
     * 編集用データの出力
     */
    public function getEditData($id)
    {
        $searchData = [
            $this->searchPrimaryKey => $id,
            'delete_include' => 1,				//追加（※削除顧客を含む）
        ];

        $reqData = [
            'search_info' => $searchData,
        ];

        $reqData['m_account_id'] = $this->getAccountId();

        $reqData = $this->setEditSearchExtend($reqData);

        $requestData = [
            'request' => $reqData
        ];

        $responseRows = json_decode($this->connectionApi($requestData, $this->searchUri), true);

        return $responseRows['response']['search_result'][0];
    }


    /**
     * データ検索
     *
     * @param $request Request
     * @return array
     */
    public function search($request)
    {
        // 顧客一覧のモデル
        $this->modelName = 'CustomerList';

        $req = array_shift($request);

        // 詳細検索の場合は、顧客詳細のモデル
        if(isset($req['list_detail_flg']) && $req['list_detail_flg'] == 1) {
            $this->modelName = 'CustomerDetail';
        }

        $this->dataModel = $this->createModel($this->modelName);

        return parent::search($request);
    }
}
