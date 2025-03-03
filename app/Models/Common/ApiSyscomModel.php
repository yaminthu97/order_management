<?php

namespace App\Models\Common;

use App\Http\HttpClients\DefaultHttpClient;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * API Modelベースクラス
 * 各サブシステムのCommonModelは、必ずこのクラスを継承してください
 * use App\Http\Syscom\v1_0\Models\ApiSyscomModel
 *
 * @author Ryougo Himeno
 * @copyright 2018-2018 Scroll360
 * @category Syscom
 * @package Model
 */
class ApiSyscomModel extends Model
{
    /**
     * INSERT UPDATEに使用するカラム
     *
     * @var array
     */
    protected $fillAble = [];

    /**
     * SELECTに使用するカラム
     *
     * @var array
     */
    protected $selectTableColumns = [];

    /**
     * テーブル名称
     *
     * @var string
     */
    protected $table = '';

    /**
     * テーブルの主キー
     *
     * @var string
     */
    protected $primaryKey = '';

    /**
     * 全文検索するカラム名があればここに書く
     *
     * @var array
     */
    protected $fulltextColumn = [];

    /**
     * DbSelect
     *
     * @var Model
     */
    public $dbSelect = null;

    /**
     * 全文検索のパーサのトークンサイズ
     *
     * @var int
     */
    protected $ngramTokenSize = 2;

    /**
     * 全文検索の特定文字
     * （この文字が混在している場合、必ずlike検索にします）
     *
     * @var string
     */
    protected $fulltextOperators = '/[+-@<>()*"]/';

    /**
     * 結合するテーブル
     *
     * @var array
     */
    protected $joinTables = [];

    /**
     * 画面系でのSELECT時の件数上限
     * (CSV時は無視する)
     *
     * @var int
     */
    protected $selectLimit = 100;

    /**
     * m_userテーブルをjoinするか
     * (基本やらない)
     *
     * @var bool
     */
    protected $joinUser = false;

    /**
     * 参照先のデータベースがグローバルなのか、ローカルなのか
     * (デフォルトはグローバルへ接続)
     *
     * @var string
     */
    protected $connection = 'global';

    /**
     * timestampの自動更新はしない(laravel設定用)
     */
    public $timestamps = false;

    /**
     * 検証に使用するバリデータ
     *
     * @var \validator
     */
    protected $validator = null;

    /**
     * ログインするアカウント情報
     *
     * @var Model
     */
    protected $accountData;

    /**
     * バリデーションのエラー情報
     *
     * @var array
     */
    protected $validateError = null;

    /**
     * オペレータIDに対応するカラム
     * entry_operator_id, update_operator_idに対応
     */
    protected $operatorIdColumn = '';

    /**
     * localで接続するDB名
     */
    protected $localDbName = '';

    /**
     * 件数の制限をするかどうか
     */
    protected $displayCsvFlag = false;

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        // m_userのjoin
        if($this->joinUser) {
            $this->setJoinUserTable();
        }

        // オペレータIDと日付のセット
        $this->setTableBaseRow();

        // 表示するカラム名
        $this->selectTableColumns = array_merge([$this->primaryKey], $this->fillAble);
    }

    /**
     * Validator呼び出し
     */
    protected function getValidator($validatorName)
    {
        $syscomManager = new \App\Http\Syscom\v1_0\Managers\ApiSyscomManager();
        $version = $this->getSubsysVersion('cc');
        $syscomManager->setVersion($version);

        return $syscomManager->createValidator($validatorName);
    }

    /**
     * 接続アカウント情報の作成
     *
     * @param $request Request
     */
    public function setAccount($request)
    {
        //$requestData = array_shift($request);

        //$accountId = $requestData['m_account_id'];
        $accountId = 1;

        if(!empty($requestData['page_list_count'])) {
            $this->selectLimit = $requestData['page_list_count'] * 10;
        }

        // ここに企業アカウントIDからローカルの接続先DBを設定する処理を書く
        $accountModel = new MasterAccountModel();

        $accountSelect = $accountModel::query();

        $accountSelect->where($accountModel->getPrimary(), '=', $accountId);

        $this->accountData = $accountSelect->first();

        $this->addLocalConfig();
    }

    /**
     * 対象テーブルのDB接続を作成
     *
     * @return $this
     */
    protected function getDbtableAdapter()
    {
        $this->getConnection()->setDatabaseName($this->localDbName);
        return $this;
    }

    /**
     * DbSelectの作成
     */
    protected function getDbSelect()
    {
        $this->dbSelect = $this::query();
    }

    /**
     * 検索情報を取得する
     *
     * @param $request Request
     * @return array
     */
    protected function getSearchInfoFromRequest($request)
    {

        $requestInfo = array_shift($request);
        return $requestInfo['search_info'];
    }

    /**
     * joinテーブル設定
     *
     * @param $countFlag boolean
     */
    protected function setJoinTable($countFlag = false)
    {
        if(!empty($this->joinTables)) {
            foreach($this->joinTables as $joinTableAsName => $joinTable) {
                $dbName = config('env.db_name');
                if($joinTable['local_db_flag'] == true) {
                    $dbName = $this->localDbName;
                }

                $joinTableName = $dbName. '.'. $joinTable['join_table_name']. ' AS '. $joinTableAsName;

                $this->dbSelect->leftjoin($joinTableName, function ($join) use ($joinTable, $joinTableAsName) {
                    foreach($joinTable['join_rules'] as $joinRule) {
                        $join->on($this->table. '.'. $joinRule['base_table_column'], '=', $joinTableAsName. '.'. $joinRule['join_table_column']);
                    }
                });

                if(!$countFlag) {
                    if(!empty($joinTable['select_columns'])) {
                        if(array_values($joinTable['select_columns']) === $joinTable['select_columns']) {
                            foreach($joinTable['select_columns'] as $selectColumn) {
                                $this->dbSelect->addSelect($joinTableAsName. '.'. $selectColumn);
                                $this->selectTableColumns[] = $selectColumn;
                            }
                        } else {
                            foreach($joinTable['select_columns'] as $columnAsName => $selectColumn) {
                                $this->dbSelect->addSelect($joinTableAsName. '.'. $selectColumn. ' AS '. $columnAsName);
                                $this->selectTableColumns[] = $columnAsName;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * JOINするテーブルにm_userをセットする
     */
    protected function setJoinUserTable()
    {
        // 仕様変更により廃止
        // // 登録者
        // $this->joinTables['m_user_entry'] = array(
        // 	'join_table_name'	=> 'm_user',
        // 	'local_db_flag'		=> false,
        // 	'join_rules'		=> array(array('base_table_column' => 'entry_operator_id', 'join_table_column' => 'm_user_id')),
        // 	'select_columns'	=> array('entry_user_name' => 'm_user_name'),
        // );

        // $this->selectTableColumns[] = 'entry_user_name';

        // // 最終更新者
        // $this->joinTables['m_user_update'] = array(
        // 	'join_table_name'	=> 'm_user',
        // 	'local_db_flag'		=> false,
        // 	'join_rules'		=> array(array('base_table_column' => 'update_operator_id', 'join_table_column' => 'm_user_id')),
        // 	'select_columns'	=> array('update_user_name' => 'm_user_name'),
        // );

        // $this->selectTableColumns[] = 'update_user_name';
    }

    /**
     * テーブルに必須の情報をセットする
     */
    protected function setTableBaseRow()
    {
        $this->fillAble[] = 'entry_operator_id';
        $this->fillAble[] = 'entry_timestamp';
        $this->fillAble[] = 'update_operator_id';
        $this->fillAble[] = 'update_timestamp';
    }

    /**
     * 件数の制限
     *
     * @param $request Request
     */
    protected function setSelectLimit($request)
    {
        $req = array_shift($request);

        if(!empty($req['display_csv_flag'])) {
            $this->dbSelect->limit($this->selectLimit);
            $this->displayCsvFlag = true;
        } else {
            // csv一括出力
            if (isset($req['batch_offset_value']) && isset($req['get_data_count'])) {
                // オフセット値及び取得件数を設定する
                $this->dbSelect->limit($req['get_data_count'])->offset($req['batch_offset_value']);
            }
        }
    }

    /**
     * 検索用バリデータのセット
     */
    public function setSearchValidator()
    {
    }

    /**
     * 登録用バリデータのセット
     */
    public function setRegsiterValidator()
    {
    }

    /**
     * 列情報を返す
     *
     * @return array
     */
    public function getColumnNames()
    {
        $columnArray = $this->selectTableColumns;

        return $columnArray;
    }

    /**
     * 検索
     *
     * @param $request Request
     */
    public function getRows($request)
    {
        $searchInfo = $this->getSearchInfoFromRequest($request);

        $this->getDbSelect();


        $this->addSelectColumn();
        $this->setJoinTable(false);
        $this->setSelectLimit($request);

        $this->addWhere($searchInfo);
        $this->addQueryExtend($searchInfo);

        $this->setQueryOrder();

        $dbRow = $this->dbSelect->get();

        return $dbRow;
    }

    /**
     * 行数のカウント
     *
     * @param $request Request
     * @return array
     */
    public function getRowCount($request)
    {
        $searchInfo = $this->getSearchInfoFromRequest($request);

        $this->getDbSelect();

        $this->setJoinTable(true);
        $this->dbSelect->selectRaw('count(1) AS count');

        $this->addWhere($searchInfo);
        $this->addQueryExtend($searchInfo);

        $dbRow = $this->dbSelect->first();

        return $dbRow;
    }

    /**
     * SELECTするカラムのセット
     */
    public function addSelectColumn()
    {
        $columnArray = $this->fillAble;
        if(array_search($this->primaryKey, $columnArray) === false) {
            $columnArray[] = $this->primaryKey;
        }
        foreach($columnArray as $columnName) {
            $this->dbSelect->addSelect($this->table. '.'. $columnName);
        }
    }

    /**
     * Where条件の追加
     *
     * @param $searchInfo array
     */
    protected function addWhere($searchInfo)
    {
    }

    /**
     * SQLクエリの追加（拡張）
     * （GROUP BYなどの条件はここに書く）
     *
     * @param $searchInfo array
     */
    protected function addQueryExtend($searchInfo)
    {
    }

    /**
     * ORDER BYの条件
     */
    protected function setQueryOrder()
    {
        if(is_array($this->primaryKey)) {
            foreach($this->primaryKey as $pKey) {
                $this->dbSelect->orderBy($this->table. '.'. $pKey);
            }
        } else {
            $this->dbSelect->orderBy($this->table. '.'. $this->primaryKey);
        }
    }

    /**
     * FULLTEXTのWHERE
     *
     * @param $columnName string
     * @param $value string
     */
    protected function whereFulltext($columnName, $value)
    {
        if (mb_strlen($value) < $this->ngramTokenSize) {
            // パーサのトークンサイズより小さい文字数の場合はインデックスが使用できないので、like検索にする
            $this->dbSelect->where($columnName, 'like', '%' . $value . '%');
        } elseif (preg_match($this->fulltextOperators, $value) !== false) {
            // BOOLEAN検索の演算子に該当する文字があった場合もlike検索にする
            $this->dbSelect->where($columnName, 'like', '%' . $value . '%');
        } else {
            $this->dbSelect->whereRaw("match({$columnName}) against(:value IN BOOLEAN MODE)", [
                'value' => $value
            ]);
        }
    }

    /**
     * WHERE句のセット
     *
     * @param $columnName string
     * @param $operation string
     * @param $value string
     */
    protected function addWhereSelect($columnName, $operation, $value)
    {
        if (in_array($columnName, $this->fulltextColumn)) {
            if (mb_strlen($value) < $this->ngramTokenSize) {
                $this->dbSelect->where($columnName, 'like', "%%{$value}%%");
            } elseif (preg_match($this->fulltextOperators, $value) !== false) {
                $this->dbSelect->where($columnName, 'like', "%%{$value}%%");
            } else {
                $this->dbSelect->whereRaw("match({$columnName}) against(:matchData IN BOOLEAN MODE)", [
                    'matchData' => $value
                ]);
            }
        } else {
            $this->dbSelect->where($columnName, $operation, $value);
        }
    }

    /**
     * 検索データの検証
     *
     * @param $request Request
     */
    public function validateSearchValues($request)
    {
        $this->validateError = null;

        if(empty($this->validator)) {
            return;
        }

        $searchInfo = $this->getSearchInfoFromRequest($request);

        $validator = $this->validator->getValidator($searchInfo, $this->table, $this->getConnectionName());

        if($validator->fails()) {
            $errorArray = json_decode($validator->messages());
            $this->validateError = $errorArray;
        }
    }

    /**
     * 登録データの検証
     *
     * @param $request Request
     */
    public function validateRegisterValues($request)
    {
        $this->validateError = null;

        if(empty($this->validator)) {
            return;
        }

        $registerInfo = $this->getRegisterData($request);

        $validator = $this->validator->getValidator($registerInfo, $this->table, $this->getConnectionName());

        if($validator->fails()) {
            $errorArray = json_decode($validator->messages(), true);
            $this->validateError = (array)$errorArray;
        }
    }

    /**
     * その他データの検証を行う(検索)
     */
    public function checkSearchColumns($request)
    {
    }

    /**
     * その他データの検証を行う(登録)
     */
    public function checkRegisterColumns($request)
    {
    }

    /**
     * エラー情報をエラー結果に反映する
     */
    public function setError($errorValue, $columnName = 'other_error')
    {
        $validateError = $this->validateError;

        if(isset($validateError[$columnName])) {
            $errorArray = $validateError[$columnName];

            $errorArray[] = $errorValue;

            $validateError[$columnName] = $errorArray;
        } else {
            $validateError[$columnName] = [$errorValue];
        }

        $this->validateError = $validateError;
    }

    /**
     * UPDATE or INSERT
     *
     * @param $request Request
     */
    public function updateInsert($request)
    {
        $registerData = $this->getRegisterData($request);

        $errorMsg = '';

        $retIds = '';

        try {
            if(!empty($registerData[$this->primaryKey])) {
                // UPDATE
                $retIds = $this->updateData($registerData);
            } else {
                // INSERT
                $retIds = $this->insertData($registerData);
            }
        } catch(\Exception $ex) {
            $errorMsg = $ex->getMessage();
            throw new \Exception($errorMsg);
        }

        return [
            $this->primaryKey => $retIds,
            'error_message' => $errorMsg
        ];
    }

    /**
     * 登録データの取得
     *
     * @param $request Request
     */
    public function getRegisterData($request)
    {
        $req = array_shift($request);

        $registerInfo = $req['register_info'];

        $registerInfo['m_account_id'] = $req['m_account_id'];

        // fillAbleにaccountIDを追加
        if(array_search('m_account_id', $this->fillAble) === false) {
            $this->fillAble[] = 'm_account_id';
        }

        return $registerInfo;
    }

    /**
     * 登録データを加工する場合
     */
    protected function editInsertData($requestData, $insertData)
    {
        return $insertData;
    }

    /**
     * 更新データを加工する場合
     */
    protected function editUpdateData($requestData, $updateData)
    {
        return $updateData;
    }

    /**
     * INSERT処理
     *
     * @param $registerData array
     */
    protected function insertData($registerData)
    {
        $insertTime = Carbon::now();

        if(empty($registerData)) {
            return false;
        }

        $insertData = [];

        foreach($registerData as $columnName => $row) {
            if(array_search($columnName, $this->fillAble) !== false) {
                if(is_null($row)) {
                    $insertData[$columnName] = '';
                } else {
                    $insertData[$columnName] = $row;
                }
            }
        }

        $insertData['entry_operator_id'] = $this->getOperatorId($registerData);
        $insertData['update_operator_id'] = $this->getOperatorId($registerData);
        $insertData['entry_timestamp'] = $insertTime->format('Y-m-d H:i:s.u');
        $insertData['update_timestamp'] = $insertTime->format('Y-m-d H:i:s.u');

        $insertData = $this->editInsertData($registerData, $insertData);

        $dbAdapter = $this->getDbtableAdapter();
        $dbAdapter->getConnection()->beginTransaction();

        try {
            // INSERT処理
            $retId = $dbAdapter->insertGetId($insertData);
            // INSERT追加処理（同一トランザクション）
            $this->afterInsert($registerData, $retId);
            $dbAdapter->getConnection()->commit();

            // INSERT追加処理（トランザクション外）
            $this->afterInsertTransaction($registerData, $retId);
        } catch(\Exception $e) {
            logger($e->__toString());
            $dbAdapter->getConnection()->rollBack();
            throw new \Exception($e->getMessage());
        }


        return $retId;
    }

    /**
     * INSERT後の処理(トランザクション内)
     * 別のテーブルに追加したい場合などは、ここに書く
     *
     * @param $registerData array
     * @param $pKey
     */
    protected function afterInsert($registerData, $pKey)
    {
    }

    /**
     * INSERT後の処理(トランザクション後)
     * トランザクション完了後に処理したい場合は、こっちに書く
     *
     * @param $registerData array
     * @param $pKey
     */
    protected function afterInsertTransaction($registerData, $pKey)
    {
    }

    /**
     * UPDATE処理
     *
     * @param $registerData array
     */
    protected function updateData($registerData)
    {
        $updateTime = Carbon::now();

        if(empty($registerData)) {
            return false;
        }

        $updateData = [];

        foreach($registerData as $columnName => $row) {
            if(array_search($columnName, $this->fillAble) !== false) {
                if(is_null($row)) {
                    $updateData[$columnName] = '';
                } else {
                    $updateData[$columnName] = $row;
                }
            }
        }
        $updateData['update_operator_id'] = $this->getOperatorId($registerData);
        $updateData['update_timestamp'] = $updateTime->format('Y-m-d H:i:s.u');

        $updateData = $this->editUpdateData($registerData, $updateData);

        unset($updateData[$this->primaryKey]);

        $dbAdapter = $this->getDbtableAdapter();

        $dbAdapter->getConnection()->beginTransaction();

        $res = 0;
        try {
            // UPDATE処理
            $res = $dbAdapter->where($this->primaryKey, $registerData[$this->primaryKey])->update($updateData);
            // UPDATE追加処理（同一トランザクション）
            $this->afterUpdate($registerData);

            $dbAdapter->getConnection()->commit();

            // UPDATE追加処理（トランザクション外）
            $this->afterUpdateTransaction($registerData);

            // 更新が成功すれば更新件数を返すため、PKEYをそのまま返す
            return $registerData[$this->primaryKey];
        } catch (\Exception $e) {
            logger($e->__toString());
            $dbAdapter->getConnection()->rollBack();
            throw new \Exception($e->getMessage());
        }

        // 更新が0件の場合は0を返す
        return $res;
    }

    /**
     * UPDATE後の処理(トランザクション内)
     * 別のテーブルも更新したい場合などは、ここに書く
     *
     * @param $registerData array
     */
    protected function afterUpdate($registerData)
    {
    }

    /**
     * UPDATE後の処理(トランザクション後)
     * トランザクション完了後に処理したい場合は、こっちに書く
     *
     * @param $registerData array
     */
    protected function afterUpdateTransaction($registerData)
    {
    }

    /**
     * PKEYの取得
     *
     * @return string
     */
    public function getPrimary()
    {
        return $this->primaryKey;
    }

    /**
     * テーブル名の取得
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->table;
    }

    /**
     * ローカル接続情報の作成
     */
    protected function addLocalConfig()
    {
        if(empty($this->accountData)) {
            throw new \Exception('localDB接続に失敗しました');
        }

        if(isset($this->accountData['account_cd'])) {
            $this->localDbName = $this->accountData['account_cd']. '_db';
            config(['database.connections.local.database' => $this->localDbName]);
        }
    }

    /**
     * 別のサブシステムのAPIから情報を取得する
     */
    protected function getApiData($requestData, $subsysName, $requestUrl = '')
    {
        // 		$version = $this->accountData[$subsysName.'_use_version'];

        // $version = 'v1_0';
        $version = $this->getSubsysVersion($subsysName);
        if(empty($version)) {
            $version = 'v1_0';
        }

        // 共通及びマスタの場合は全数取得するようにする
        if($subsysName == 'master' || $subsysName == 'global') {
            if(isset($requestData['request']['search_info'])) {
                $requestData['request']['search_info']['search_use_type'] = 1;
            }
        }

        $baseUrl = config('env.api_subsys_url.'. strtolower($subsysName));

        $reqUrl = "{$baseUrl}{$version}/{$requestUrl}";

        return json_decode($this->requestApiJson($reqUrl, $requestData), true);
    }

    /**
     * API送信(Json)
     *
     * @param $requestUri string
     * @param $requestData array
     * @param $baseUriAddress string
     */
    protected function requestApiJson($requestUri, $requestData)
    {
        if(empty($requestUri) || empty($requestData)) {
            throw new \Exception('JSON送受信に失敗しました');
        }

        $client = new DefaultHttpClient();

        $reqBody = [
            'json' => $requestData,
            'http_errors' => true,
            'headers' => ['Content-Type' => 'application/json'],
        ];

        try {
            if(config('env.api_connection_debug')) {
                logger($requestUri);
                logger(json_encode($requestData));
            }
            $res = $client->request('POST', $requestUri, $reqBody);

            $returnStr = (string)$res->getBody();

            if(config('env.api_connection_debug')) {
                logger($returnStr);
            }
            return $returnStr;
        } catch(\Exception $ex) {
            logger($ex->__toString());

            throw new \Exception($ex->getMessage());
        }
    }

    /**
     * バリデーションエラーの内容取得
     */
    public function getValidateError()
    {
        return $this->validateError;
    }

    /**
     * バリデーションのみかどうかの判定
     */
    public function checkValidationOnly($request)
    {
        $requestData = array_shift($request);

        if(empty($requestData['validation_only_flag'])) {
            return false;
        }

        return true;
    }

    /**
     * 担当者IDの取得
     */
    protected function getOperatorId($registerData)
    {
        if(strlen($this->operatorIdColumn) > 0) {
            if(isset($registerData[$this->operatorIdColumn])) {
                return $registerData[$this->operatorIdColumn];
            }
        }

        return 0;
    }

    /**
     * 追加取得データのセット
     * （配列の中に配列をセットする場合など）
     *
     * @return array
     */
    public function getExtendData($iRow)
    {
        return $iRow;
    }

    /**
     * SELECTクエリを基に結果を配列で返す
     *
     * @param $columns array
     * @param $dbSelect DB::table()
     * @return array
     */
    protected function getArrayDbSelect($columns, $dbSelect)
    {
        $dataRowSet = $dbSelect->get();

        $dataRows = [];
        if(!empty($dataRowSet)) {
            foreach($dataRowSet as $row) {
                $dataRow = [];
                foreach($columns as $columnName) {
                    $dataRow[$columnName] = $row->$columnName;
                }

                $dataRows[] = $dataRow;
            }
        }

        return $dataRows;
    }

    /**
     * サブシステムごとのバージョンを取得する
     */
    protected function getSubsysVersion($subsysName)
    {
        $versions = config('define.subsystem_version');

        return $this->accountData[$versions[strtolower($subsysName)]];
    }
}
