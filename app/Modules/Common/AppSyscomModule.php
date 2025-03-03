<?php

namespace App\Modules\Common;

use App\Http\HttpClients\DefaultHttpClient;
use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class AppSyscomModule
{
    /**
     * API URL
     */
    protected $apiUri = '';
    protected $searchUri = '';
    protected $registerUri = '';

    protected $version = 'v1_0';

    /**
     * 複数項目のいずれかのうち、存在する項目を出力する定義
     */
    protected $multiChoiceColumn = [];

    /**
     * 編集時に取得する際のデータの主キー名称
     */
    protected $searchPrimaryKey = '';

    /**
     * 出力するCSVの名称
     */
    protected $outputCsvName = '';

    /**
     * 登録したバッチ実行指示ID
     */
    protected $registedBatchExecuteId = 0;

    /**
     * ログイン情報
     */
    protected $loginSessionInfo = [];

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->apiUri = config('app.url');
    }

    /**
     * 検索用データの格納
     */
    protected function getSearchData($data)
    {
        $searchData = [];
        foreach($data as $key => $row) {
            if((!empty($data[$key])) || (strlen($data[$key]) > 0)) {
                $searchData[$key] = $data[$key];
            }
        }

        $searchData = $this->editSearchData($searchData);

        return $searchData;
    }

    /**
     * 検索用データを加工する
     */
    protected function editSearchData($data)
    {
        return $data;
    }


    /**
     * データ検索
     *
     * @param $request Request
     * @return array
     */
    public function search($requestData)
    {
        $dataModel = $this->dataModel;

        //$requestData = $request->all();
        logger($requestData);

        $inputData = [[
            'search_info' => $requestData
            ]];

        $dataModel->setAccount($inputData);

        $dataModel->setSearchValidator();

        $resArray = [
            'status' => 1,
            'total_record_count' => 0,
            'search_record_count' => 0,
            'error' => ''
        ];

        $rowArray = [];


        //try {
        $dataModel->validateSearchValues($inputData);

        $dataModel->checkSearchColumns($inputData);

        $validateError = $dataModel->getValidateError();

        if(empty($validateError)) {
            $rowCount = $dataModel->getRowCount($inputData);

            $rows = $dataModel->getRows($inputData);

            foreach ($rows as $row) {
                $rowArray[] = $this->setColumnRow($row);
            }

            $resArray['status'] = 0;
            $resArray['error'] = [
                'code' => '',
                'message' => ''
            ];
            $resArray['total_record_count'] = $rowCount->count;
            $resArray['search_record_count'] = count($rowArray);
        } else {
            $resArray['status'] = 1;
            $resArray['error'] = [
                'code' => '',
                'message' => $validateError
            ];
        }
        /*} catch (\Exception $e) {
            logger($e->__toString());
            $resArray['status'] = 1;
            $resArray['error'] = array(
                'code' => '',
                'message' => $e->getMessage()
            );
        }*/

        return [
            'response' => [
                'result' => $resArray,
                'search_result' => $rowArray
            ],
        ];
    }

    /**
     * 検索一覧の取得
     */
    protected function getRows($data)
    {
        unset($data->_token);

        $reqSearchData = [
            'search_info' => $this->getSearchData($data),
        ];

        $reqSearchData['m_account_id'] = $this->getAccountId();
        $reqSearchData['display_csv_flag'] = 1;
        if(!empty($data['page_list_count'])) {
            $reqSearchData['page_list_count'] = $data['page_list_count'];
        } else {
            $reqSearchData['page_list_count'] = 10;
        }

        $reqSearchData = $this->addSearchParameterExtend($reqSearchData);

        $requestData = [
            'request' => $reqSearchData
        ];

        $responseBody = $this->connectionApi($requestData, $this->searchUri);
        return $responseBody;
    }

    /**
     * 検索時に検索データと別にパラメータを追加する
     */
    protected function addSearchParameterExtend($reqSearchData)
    {
        return $reqSearchData;
    }

    /**
     * 出力データを加工するための処理
     */
    public function getListData($dataResult)
    {
        if(!empty($this->multiChoiceColumn)) {
            foreach($dataResult as $key => $dataRow) {
                foreach($this->multiChoiceColumn as $outputColumnName => $outputColumnArray) {
                    $dataRow[$outputColumnName] = '';
                    foreach($outputColumnArray as $columnName) {
                        if(!empty($dataRow[$columnName]) || strlen($dataRow[$columnName] > 0)) {
                            $dataRow[$outputColumnName] = $dataRow[$columnName];
                            break;
                        }
                    }
                }
                $dataResult[$key] = $dataRow;
            }
        }

        return $dataResult;
    }

    /**
     * 編集用データの出力
     */
    public function getEditData($id)
    {
        $searchData = [$this->searchPrimaryKey => $id];

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
     * 編集画面のAPIで追加する追加パラメータのセット
     * @param array $reqData
     * @return array
     */
    protected function setEditSearchExtend($reqData)
    {
        return $reqData;
    }

    /**
     * アカウントIDの取得
     */
    protected function getAccountId()
    {
        // セッションからアカウントIDを取得する処理を書く
        return $this->getLoginSessionInfo('m_account_id');
    }

    /**
     * アカウントコードの取得
     */
    protected function getAccountCode()
    {
        // セッションからアカウントコードを取得する処理を書く
        return $this->getLoginSessionInfo('account_cd');
    }

    /**
     * オペレータIDの取得
     */
    protected function getOperatorId()
    {
        // セッションからオペレータIDを取得する処理を書く
        return $this->getLoginSessionInfo('m_operators_id');
    }

    /**
     * ログインセッション情報の取得
     */
    protected function getLoginSessionInfo($columnName)
    {
        if(empty($this->loginSessionInfo)) {
            $this->loginSessionInfo = session('OperatorInfo');
        }
        return $this->loginSessionInfo[$columnName];
    }

    /**
     * 検索画面に追加で渡したいデータをセットする
     */
    public function setSearchExtendData()
    {
    }

    /**
     * 登録用データの格納
     */
    protected function getRegisterData($data)
    {
        $registerData = [];
        foreach($data as $key => $row) {
            if((!empty($data[$key])) || (strlen($data[$key]) > 0)) {
                $registerData[$key] = $data[$key];
            }
        }

        $registerData = $this->editRegisterData($registerData);

        return $registerData;
    }

    /**
     * 検索用データを加工する
     */
    protected function editRegisterData($data)
    {
        return $data;
    }


    /**
     * 登録用の項目を検証する
     */
    public function checkRegisterData($data)
    {
        $dataRow = $this->registerCheck($data);

        return $dataRow;
    }

    /**
     * 登録項目の検証APIにデータを渡して実行する
     */
    protected function registerCheck($data)
    {
        unset($data->_token);

        $reqRegisterData = [
            'register_info' => $this->getRegisterData($data),
        ];

        $reqRegisterData['m_account_id'] = $this->getAccountId();

        $reqRegisterData = $this->addRegisterParameterExtend($reqRegisterData);

        $requestData = ['request' => $reqRegisterData];

        $registerCheckData = $this->connectionApi($requestData, $this->registerUri. '/check');

        return json_decode($registerCheckData, true);
    }

    /**
     * 登録処理を行う
     */
    public function registerData($data)
    {
        $dataRow = $this->register($data);

        return $dataRow;
    }

    /**
     * 登録APIを実行する
     */
    protected function register($data)
    {
        unset($data->_token);

        $reqRegisterData = [
            'register_info' => $this->getRegisterData($data),
        ];

        $reqRegisterData['m_account_id'] = $this->getAccountId();

        $reqRegisterData = $this->addRegisterParameterExtend($reqRegisterData);

        $requestData = ['request' => $reqRegisterData];

        $registerCheckData = $this->connectionApi($requestData, $this->registerUri);

        return json_decode($registerCheckData, true);
    }

    /**
     * 登録項目に追加したいパラメータを設定する
     */
    protected function addRegisterParameterExtend($reqRegisterData)
    {
        return $reqRegisterData;
    }


    /**
     * 登録画面に追加で渡したいデータをセットする
     */
    public function setRegisterExtendData($editRow, $pKey = null)
    {
    }

    /**
     * 詳細画面のデータ取得の実行
     */
    public function getInfoData($id)
    {
        $searchData = [$this->searchPrimaryKey => $id];

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
     * 詳細画面に追加で渡したいデータをセットする
     */
    public function setInfoExtendData($infoRow, $pKey = null)
    {
    }

    /**
     * APIの実行
     */
    protected function connectionApi($requestData, $connectionUrl, $subsystemName = null)
    {
        $subsysName = strtolower(config('env.sybsys_name'));

        if(!is_null($subsystemName)) {
            $subsysName = strtolower($subsystemName);
        }

        // 共通及びマスタの場合は全数取得するようにする
        if($subsysName == 'master' || $subsysName == 'global') {
            if(isset($requestData['request']['search_info'])) {
                $requestData['request']['search_info']['search_use_type'] = 1;
            }
        }

        // バージョン取得処理を書く
        $version = 'v1_0';

        $baseUrl = config('env.api_subsys_url.'. $subsysName);

        $res =  [
            'json' => $requestData,
            'http_errors' => true,
            'headers' => ['Content-Type' => 'application/json'],
        ];

        $client = new DefaultHttpClient();

        $requestUrl = "{$baseUrl}{$version}/{$connectionUrl}";

        try {
            // デバッグがONの場合はリクエストを表示する
            if(config('env.api_connection_debug')) {
                logger($connectionUrl);
                logger(json_encode($requestData));
            }

            Log::debug("req", ["message" => "connectionApi", "url" => $requestUrl, "request" => $requestData]);
            $response = $client->request('POST', $requestUrl, $res);
            $response_body = (string) $response->getBody();

            // デバッグがONの場合はレスポンスを表示する
            if(config('env.api_connection_debug')) {
                logger($response_body);
            }
            return $response_body;
        } catch(\Exception $ex) {
            logger($ex->__toString());
            logger('[[requestUrl]] : '.$requestUrl);
            logger('[[baseUrl]] : '.$baseUrl);
            logger('[[version]] : '.$version);
            logger('[[connectionUrl]] : '.$connectionUrl);
            logger('[[subsysName]] : '.$subsysName);

            throw new \Exception($ex->getMessage());
        }
    }

    /**
     * 新規か編集かどうか判断する
     */
    public function isNewFlg($editRow)
    {
        // 主キーがセットされていれば編集、そうでない場合は新規扱い
        return empty($editRow[$this->searchPrimaryKey]);
    }

    /**
     * PKEY項目名の取得
     */
    public function getPkeyName()
    {
        return $this->searchPrimaryKey;
    }

    /**
     * CSV出力キューに登録（行指定）
     */
    public function setCsvOutputRows($requestData, $checkKeyName, $pkeyName, $batchType)
    {

        if(empty($requestData[$checkKeyName])) {
            return '出力行が指定されていません。';
        }

        $checkRows = $requestData[$checkKeyName];


        $csvRequestRow = [
            'search_info' => [$pkeyName => implode(',', $requestData[$checkKeyName])],
            'bulk_output_flg' => 0
        ];

        // キューに登録する
        if($this->setCsvQueue($csvRequestRow, $batchType, $requestData)) {
            return '';
        }

        return 'CSV出力処理の登録に失敗しました。';
    }

    /**
     * CSV出力キューに登録（一括）
     */
    public function setCsvOutputAll($requestData, $batchType)
    {
        $reqData = [];

        foreach($requestData as $key => $row) {
            if(!is_null($row)) {
                $reqData[$key] = $row;
            }
        }

        unset($reqData['_token']);
        unset($reqData['hidden_next_page_no']);
        unset($reqData['page_list_count']);
        unset($reqData['submit_csv_bulk_output']);
        unset($reqData['sorting_shift']);

        $csvRequestRow = [
            'search_info' => $reqData,
            'bulk_output_flg' => 1
        ];

        // キューに登録する
        if($this->setCsvQueue($csvRequestRow, $batchType, $requestData)) {
            return '';
        }

        return 'CSV出力処理の登録に失敗しました。';
    }

    /**
     * CSV取込キューに登録
     */
    public function setCsvInput($requestData, UploadedFile $csvFile, $batchType)
    {
        if(empty($csvFile)) {
            return '取込ファイルが指定されていません。';
        }

        if(strtolower($csvFile->getClientOriginalExtension()) != 'csv') {
            return '取込ファイルはCSVを指定してください。';
        }

        $nowTime = new Carbon();

        $originalFileName = $csvFile->getClientOriginalName();

        $uploadFileName = $nowTime->format('Ymdhis'). '_'. $originalFileName;

        $uploadSavePath = 'csv/'.config('env.sybsys_name');

        $csvFile->storeAs($uploadSavePath, $uploadFileName);

        $csvRequestRow = [
            'original_file_name' => $originalFileName,
            'upload_file_name' => $uploadFileName,
        ];

        // TODO:S3へのアップロード処理

        $csvRequestRow['aws_s3_token'] = '';

        $this->registedBatchExecuteId = 0;

        // キューに登録する
        if($this->setCsvQueue($csvRequestRow, $batchType, $requestData)) {
            if($this->registedBatchExecuteId > 0) {
                $this->uploadAmazonS3($uploadSavePath. '/'. $uploadFileName, $batchType);
            }

            return '';
        }

        return 'CSV取込処理の登録に失敗しました。';
    }

    /**
     * キュー登録処理
     */
    protected function setCsvQueue($data, $batchType, $requestData)
    {
        $this->registedBatchExecuteId = 0;

        $nowTime = new Carbon();

        // バッチ登録の処理を行う
        $registerInfo = [
            'execute_batch_type' => $batchType,
            'm_operators_id' => $this->getOperatorId(),
            'batchjob_create_datetime' => $nowTime->format('Y-m-d H:i:s'),
            'execute_conditions' => $data,
            '_token' => $requestData['_token'],
        ];

        $requestData = ['request' => ['register_info' => $registerInfo, 'm_account_id' => $this->getAccountId()]];

        $response = json_decode($this->connectionApi($requestData, 'registerBatchInstruction', 'common'), true);

        // 登録処理の結果を返す
        if($response['response']['result']['status'] == 0) {
            $this->registedBatchExecuteId = $response['response']['register_result']['t_execute_batch_instruction_id'];
            return true;
        }

        return false;
    }

    /**
     * APIで取得したデータを配列で返却
     */
    protected function getValueArray($connectionApiUrl, $displayName, $valueName, $subsystemName = null, $where = [], $extendData = [])
    {
        $valueArray = [];

        $requestData =  [
            'search_info' => $where,
            'm_account_id' => $this->getAccountId()
        ];

        foreach($extendData as $key => $row) {
            $requestData[$key] = $row;
        }

        Log::debug("getValueArray", ["message" => "connectionApi", "url" => $connectionApiUrl, "request" => $requestData]);
        $apiData = $this->connectionApi(['request' => $requestData], $connectionApiUrl, $subsystemName);

        $searchResult = json_decode($apiData, true);

        foreach($searchResult['response']['search_result'] as $resRow) {
            $valueArray[$resRow[$displayName]] = $resRow[$valueName];
        }

        return $valueArray;
    }

    /**
     * AWS S3へのアップロード
     */
    protected function uploadAmazonS3($uploadFilePath, $batchName, $fileType = 'csv', $uploadType = 'import')
    {
        if(empty(config('env.aws_s3.region')) || empty(config('env.aws_s3.secret')) || empty(config('env.aws_s3.access_key')) || empty(config('env.aws_s3.bucket'))) {
            return 'AWS S3の設定がされていません';
        }

        $credentials = new Credentials(config('env.aws_s3.access_key'), config('env.aws_s3.secret'));

        $s3Config = [
            'region' => config('env.aws_s3.region'),
            'version' => config('env.aws_s3.version', 'latest'),
            'credentials' => $credentials,
        ];

        if(config('env.use_proxy', '0')) {
            $s3Config['http'] = [
                'http' => config('env.proxy_address'),
                'https' => config('env.proxy_address'),
            ];
        }

        $s3Client = new S3Client($s3Config);

        $s3UploadFileName = $this->registedBatchExecuteId. '.'. $fileType;

        $accountCode = $this->getAccountCode();

        $s3FileKey = $accountCode. '/'. $fileType. '/'. $uploadType. '/'. $batchName. '/'. $s3UploadFileName;

        $uploadLocalFile = storage_path('app/'. $uploadFilePath);

        $contentType = 'text/csv';

        if($fileType != 'csv') {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            $contentType = finfo_file($finfo, $uploadLocalFile);
        }

        try {
            $result = $s3Client->putObject([
                'Bucket' => config('env.aws_s3.bucket'),
                'Key' => $s3FileKey,
                'SourceFile' => $uploadLocalFile,
                'ContentType' => $contentType,
            ])->toArray();

            if($result['@metadata']['statusCode'] == 200) {
                // S3にアップロードした情報でバッチ実行指示を更新する
                $registerData = [
                    't_execute_batch_instruction_id' => $this->registedBatchExecuteId,
                    'file_path' => '/'. $s3FileKey
                ];

                $response = json_decode($this->connectionApi(['request' => ['register_info' => $registerData, 'm_account_id' => $this->getAccountId()]], 'registerBatchInstruction', 'common'), true);

                // 登録処理の結果を返す
                if($response['response']['result']['status'] == 0) {
                    return '';
                }
            }

            return 'AWS S3へのファイルのアップロードに失敗しました';
        } catch (\Exception $ex) {
            logger($ex->__toString());
            return 'AWS S3へのファイルのアップロードに失敗しました';
        }

    }

    /**
     * Modelの呼び出し
     *
     * @param $modelName string
     */
    public function createModel($modelName)
    {
        $modelPass = 'App\Models';

        $className = $modelPass. "\\". $this->subsysName. "\\". $modelName. 'Model';

        return new $className();
    }

    /**
     * 出力列のセット
     *
     * @param $dataRow array
     * @return array
     */
    protected function setColumnRow($dataRow)
    {
        $columns = $this->dataModel->getColumnNames();
        $iRow = [];

        foreach ($columns as $columnName) {
            $iRow[$columnName] = $dataRow->$columnName;
        }

        $iRow = $this->getExtendData($iRow);

        return $iRow;
    }
    /**
     * 追加取得データのセット
     * （配列の中に配列をセットする場合など）
     *
     * @param $iRow array
     * @return array
     */
    protected function getExtendData($iRow)
    {
        return $this->dataModel->getExtendData($iRow);
    }

    /**
     * バージョンを設定する
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }
}
