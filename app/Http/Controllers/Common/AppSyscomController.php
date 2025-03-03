<?php

namespace App\Http\Controllers\Common;

use App\Exceptions\DataNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller as BaseController;

use Illuminate\Support\Str;

class AppSyscomController extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    protected $subsystem = 'Subsystem';
    protected $namespace = '';
    protected $className = '';
    // ログインセッションから取得するように修正
    private $ver = 'v1_0';

    protected $rowLimit = 10;
    protected $paginator;
    protected $dataRows;

    protected $searchViewId = '';
    protected $registerViewId = '';
    protected $notifyViewId = '';
    protected $infoViewId = '';

    protected $submitName = '';

    protected $searchData;

    /**
     * セッションに使用する名称
     * @var string
     */
    protected $registerSessionName = '';
    protected $searchSessionName = '';

    /**
     * 一覧画面でCSV出力用のチェックボックスに使用する項目名称
     */
    protected $outputCheckKeyName = 'csv_output_check_key_id';

    /**
     * CSV出力用のチェックボックスに使用する特定キー
     */
    protected $outputCheckColumnName = 'primary_key';

    public const REGISTER_SESSION_NAME = 'register_request';
    public const SEARCH_SESSION_NAME = 'search_request';
    public const GET_PARAM_KEY = 'params';

    /**
     * CSVの出力に使用するバッチ種類名
     */
    protected $outputBatchTypeName = '';

    /**
     * CSVの取込に使用するバッチ種類名
     */
    protected $inputBatchTypeName = '';


    /**
     * ソートする名称
     */
    protected $listSort = [
        'column_name' => '',
        'sorting_shift' => 'asc',
    ];

    /**
     * 件数表示リミットの一覧
     */
    protected $pageListCount = [
        10,
        30,
        60,
        100,
        150,
        200,
    ];

    /**
     * マスタ系かトランザクション系か
     */
    protected $masterFlg = false;

    /**
     * 画面に表示するメッセージ
     */
    protected $viewMessage = [];

    /**
     * 外部連携で使用するアプリの一覧
     */
    protected $postAppList = [
        'mail-dealer',
    ];

    /**
     * 外部連携かどうかを判断するパラメータ
     */
    protected $postAppKeyName = 'post_app';

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->registerSessionName = $this->registerViewId. '_'. $this::REGISTER_SESSION_NAME;
        $this->searchSessionName = $this->searchViewId. '_'. $this::SEARCH_SESSION_NAME;
    }

    protected function getViewPath(string $viewPath, string $viewName = 'form')
    {
        //		return mb_strtolower(
        //			$this->subsystem . '.' .
        //			$this->ver . '.' .
        //			$viewName. '.'.
        //			str_replace('\\', '.', $this->namespace) . '.' .
        //			$viewPath
        //			);
        return
            $this->subsystem . '.' .
            $this->ver . '.' .
            $viewName. '.'.
            str_replace('\\', '.', $this->namespace) . '.' .
            $viewPath
        ;
    }

    protected function getManager(string $class_name)
    {
        $path = 'App\\Http\\Managers\\' .
            $this->subsystem . '\\' .
            $this->ver . '\\' .
//			$this->namespace .
            $class_name.
            'Manager'
        ;
        return new $path();
    }
    protected function getModules(string $class_name)
    {
        $path = 'App\\Modules\\' .
            $class_name.
            'Module'
        ;
        return new $path();
    }

    public function list(Request $request)
    {

        $this->getSubmitName($request);

        $req = $request->all();
        $sessionKeyId = config('define.session_key_id') ?? 'data_key_id';
        $sessionKey = $this->getSessionKey($request, $req, $sessionKeyId);
        if (empty($sessionKey)) {
            $sessionKey = $this->makeRandomKey();
        }
        $this->searchSessionName .= $sessionKey;

        if(empty($req)) {
            if(session()->exists($this->searchSessionName)) {
                session()->forget($this->searchSessionName);
            }
        }

        try {
            $searchResult = [];
            $dataResult = [];

            $page = 1;

            $manager = $this->getModules($this->className);

            $searchRow = $req;
            if (empty($searchRow[$sessionKeyId])) {
                $searchRow[$sessionKeyId] = $sessionKey;
            }

            $this->searchData = $req;

            $csvOutputErrorResult = '';

            $csvInputErrorResult = '';


            if(!empty($req)) {
                if($this->submitName == 'search') {
                    session()->forget($this->searchSessionName);
                    $page = 1;
                    session()->put($this->searchSessionName, $searchRow);
                } else {
                    if(session()->exists($this->searchSessionName)) {
                        $searchRow = session()->get($this->searchSessionName);
                        $this->searchData = session()->get($this->searchSessionName);
                    }

                    if(isset($req['hidden_next_page_no']) && !is_null($req['hidden_next_page_no'])) {
                        $page = $req['hidden_next_page_no'];
                    }

                    if(isset($req['page_list_count']) && !is_null($req['page_list_count'])) {
                        $this->rowLimit = $req['page_list_count'];
                    }

                    if(isset($req['sorting_column']) && !is_null($req['sorting_column'])) {
                        $this->listSort['column_name'] = $req['sorting_column'];
                    }

                    if(isset($req['sorting_shift']) && !is_null($req['sorting_shift'])) {
                        $this->listSort['sorting_shift'] = $req['sorting_shift'];
                    }

                    // チェックしてCSV出力処理
                    if(preg_match('/\w*csv_output/', $this->submitName)) {
                        $csvOutputErrorResult = $manager->setCsvOutputRows($req, $this->outputCheckKeyName, $this->outputCheckColumnName, $this->outputBatchTypeName);
                        if($csvOutputErrorResult == '') {
                            $this->addViewMessage('CSV出力への登録を行いました。');
                        }
                    }

                    // 一覧でのCSV出力処理
                    if(preg_match('/\w*csv_bulk_output/', $this->submitName)) {
                        $csvOutputErrorResult = $manager->setCsvOutputAll($req, $this->outputBatchTypeName);
                        if($csvOutputErrorResult == '') {
                            $this->addViewMessage('CSV出力への登録を行いました。');
                        }
                    }

                    // CSV取込処理
                    if(preg_match('/\w*csv_input/', $this->submitName)) {
                        $upFile = $request->file('csv_input_file');
                        if(is_null($upFile)) {
                            $csvInputErrorResult = '取込ファイルが指定されていません。';
                        } else {
                            $csvInputErrorResult = $manager->setCsvInput($req, $upFile, $this->inputBatchTypeName);
                            if($csvInputErrorResult == '') {
                                $this->addViewMessage('CSV取込への登録を行いました。');
                            }
                        }
                    }
                }
                $searchRow['page_list_count'] = $this->rowLimit;

                $dataRows = new \stdClass();
                $dataRows = json_decode($manager->search($searchRow), true);

                $searchResult = (array)$dataRows['response']['result'];
                $dataResult = (array)$dataRows['response']['search_result'];
                $errorResult = '';
                if(isset($searchResult['error']['message'])) {
                    $errorResult = (array)json_decode($searchResult['error']['message']);
                }

                // 表示用にデータを加工する場合は以下に書く
                $dataResult = $manager->getListData($dataResult);

                $dataResult = $this->setSearchSort($dataResult);

                // ペジネーション設定
                $this->getPagination($dataResult, $request->path(), $page);

                $paginator = $this->paginator;

                $pageRows = [
                    'min' => $this->rowLimit * ($page - 1) + 1,
                    'max' => $this->rowLimit * $page,
                ];

                if($searchResult['search_record_count'] < $pageRows['max']) {
                    $pageRows['max'] = $searchResult['search_record_count'];
                }

                $dataList = $this->dataRows;
            }

            // 画面にその他渡したいデータをセットする
            $viewExtendData = $manager->setSearchExtendData();

            // ページングの保持
            $viewExtendData['page_list_count'] = $this->pageListCount;
            $searchRow['page_list_count'] = $this->rowLimit;

            // ソートの保持
            $viewExtendData['list_sort'] = $this->listSort;

            // CSV出力にするチェックの項目を指定
            $viewExtendData['output_check_key_name'] = $this->outputCheckKeyName;

            // CSV出力用のチェックボックスのキー項目名を指定
            $viewExtendData['output_check_column_name'] = $this->outputCheckColumnName;

            if($this->outputCheckColumnName == '') {
                $viewExtendData['output_check_column_name'] = $manager->getPkeyName();
            }

            if($csvOutputErrorResult != '') {
                $errorResult['csv_output_error'][] = $csvOutputErrorResult;
            }

            if($csvInputErrorResult != '') {
                $errorResult['csv_input_error'][] = $csvInputErrorResult;
            }

            $viewMessage = $this->viewMessage;

            return view($this->getViewPath($this->searchViewId), compact('searchResult', 'dataList', 'errorResult', 'paginator', 'viewName', 'pageRows', 'viewExtendData', 'searchRow', 'viewMessage'));
        } catch(\Exception $ex) {
            return $ex->__toString();
        }
    }

    /**
     * 検索データをソートする
     */
    protected function setSearchSort($dataList)
    {
        $sortedDataList = $dataList;

        if(!empty($this->listSort['column_name'])) {
            if(empty($this->listSort['sorting_shift'])) {
                $this->listSort['sorting_shift'] = 'asc';
            }

            $sortColumnData = [];
            foreach($dataList as $key => $row) {
                $sortColumnData[$key] = $row[$this->listSort['column_name']];
            }

            $sortKey = $this->listSort['sorting_shift'] == 'desc' ? SORT_DESC : SORT_ASC;

            array_multisort($sortColumnData, $sortKey, SORT_NATURAL | SORT_FLAG_CASE, $sortedDataList);
        }

        return $sortedDataList;
    }

    /**
     * 登録画面
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function new(Request $request)
    {
        $this->getSubmitName($request);

        $editRow = [];
        $sessionKeyId = config('define.session_key_id');
        $sessionKey = $this->getSessionKey($request, $editRow, $sessionKeyId);
        if (empty($sessionKey)) {
            $sessionKey = $this->makeRandomKey();
        }
        $this->registerSessionName .= $sessionKey;

        if(session()->exists($this->registerSessionName)) {
            $editRow = session()->get($this->registerSessionName);
            session()->forget($this->registerSessionName);
        } else {
            if(!empty($request->all())) {
                $editRow = $request->all();
            }

            $editRow = $this->setNewPresetData($editRow, $request);
        }

        if(session()->exists('_old_input')) {
            $oldInput = session()->get('_old_input');
            if (!empty($oldInput['message'])) {
                $this->addViewMessage($oldInput['message']);
            }
        }
        if (empty($editRow[$sessionKeyId])) {
            $editRow[$sessionKeyId] = $sessionKey;
        }

        $manager = $this->getModules($this->className);

        if($this->submitName == 'register') {
            $editRow = $request->all();

            $checkResult = $manager->checkRegisterData($editRow);

            // エラーがある場合はエラー内容を表示して再描画
            if($checkResult['response']['result']['status'] > 0) {
                $errorResult = (array)$checkResult['response']['result']['error']['message'];
            } else {
                // そうでない場合は確認画面に飛ぶ
                session()->put($this->registerSessionName, $editRow);
                $b64_param_url = $this->makeUrlParams($request, $editRow);
                return redirect($request->url().'/../notify'.$b64_param_url)->withInput($editRow);
            }
        }

        $viewExtendData = $manager->setRegisterExtendData($editRow);

        $viewMessage = $this->viewMessage;

        return view($this->getViewPath($this->registerViewId), compact('viewExtendData', 'editRow', 'errorResult', 'viewMessage'));
    }

    /**
     * 新規登録時の初期データを用意する
     */
    protected function setNewPresetData($editRow, $request = null)
    {
        return $editRow;
    }

    /**
     * 編集画面
     */
    public function edit($id = 0, Request $request)
    {
        $this->getSubmitName($request);

        $manager = $this->getModules($this->className);
        $sessionKeyId = config('define.session_key_id');
        $sessionKey = $this->getSessionKey($request, [], $sessionKeyId);
        $this->registerSessionName .= $sessionKey;

        if(session()->exists($this->registerSessionName)) {
            $editRow = session()->get($this->registerSessionName);
            session()->forget($this->registerSessionName);
        } else {
            $editRow = [];
            if(!empty($request->all())) {
                $editRow = $request->all();
            }
            if (count($editRow) === 0 || (count($editRow) === 1 && !empty($editRow[$sessionKeyId]))) {
                try {
                    $editRow = $manager->getEditData($id);
                } catch(\Exception $ex) {
                    throw new DataNotFoundException();
                }
            }
        }
        if (empty($editRow[$sessionKeyId])) {
            $editRow[$sessionKeyId] = $sessionKey;
        }

        if(session()->exists('_old_input')) {
            $oldInput = session()->get('_old_input');
            if (!empty($oldInput['message'])) {
                $this->addViewMessage($oldInput['message']);
            }
        }


        if($this->submitName == 'register') {
            $editRow = $request->all();

            $editRow[$manager->getPkeyName()] = $id;

            $checkResult = $manager->checkRegisterData($editRow);

            // エラーがある場合はエラー内容を表示して再描画
            if($checkResult['response']['result']['status'] > 0) {
                $errorResult = (array)$checkResult['response']['result']['error']['message'];
            } else {
                // そうでない場合は確認画面に飛ぶ
                session()->put($this->registerSessionName, $editRow);
                $b64_param_url = $this->makeUrlParams($request, $editRow);
                return redirect($request->url().'/../../notify'.$b64_param_url)->withInput($editRow);
            }
        }

        $viewExtendData = $manager->setRegisterExtendData($editRow, $id);

        $viewMessage = $this->viewMessage;

        return view($this->getViewPath($this->registerViewId), compact('viewExtendData', 'editRow', 'errorResult', 'viewMessage'));
    }

    /**
     * 確認画面
     */
    public function notify(Request $request)
    {
        $this->getSubmitName($request);

        $manager = $this->getModules($this->className);
        $sessionKeyId = config('define.session_key_id');
        $sessionKey = $this->getSessionKey($request, [], $sessionKeyId);
        $this->registerSessionName .= $sessionKey;

        $editRow = session()->get($this->registerSessionName);

        $viewExtendData = $manager->setRegisterExtendData($editRow);

        // 登録処理を行う
        if($this->submitName == 'register') {
            if($manager->isNewFlg($editRow)) {
                unset($editRow[$manager->getPkeyName()]);
            }

            // 登録処理
            $resultRow = $manager->registerData($editRow);

            $errorResult = $resultRow;

            // 登録が無事完了した場合
            // セッション内容の削除
            session()->forget($this->registerSessionName);

            if($this->masterFlg) {
                // マスタ系なら、一覧に戻す
                return redirect($request->url().'/../list');
            } else {
                // そうでない場合は、新規の場合は新規登録画面へ、編集の場合は該当データの編集画面へ戻す
                if($manager->isNewFlg($editRow)) {
                    return redirect($request->url().'/../new')->withInput(['message' => '登録が完了しました。']);
                } else {
                    return redirect($request->url().'/../edit/'. $editRow[$manager->getPkeyName()])->withInput(['message' => '登録が完了しました。']);
                }
            }
        }

        // キャンセルの場合
        if($this->submitName == 'cancel') {
            $b64_param_url =  $this->makeUrlParams($request, $editRow);
            if($manager->isNewFlg($editRow)) {
                return redirect($request->url().'/../new'.$b64_param_url);
            } else {
                return redirect($request->url().'/../edit/'. $editRow[$manager->getPkeyName()].$b64_param_url);
            }
        }

        return view($this->getViewPath($this->notifyViewId), compact('editRow', 'viewExtendData'));
    }

    /**
     * 詳細画面
     */
    public function info($id = 0, Request $request)
    {
        $manager = $this->getModules($this->className);

        try {
            $infoRow = $manager->getInfoData($id);
        } catch(\Exception $ex) {
            throw new DataNotFoundException();
        }

        $viewExtendData = $manager->setInfoExtendData($infoRow, $id);

        return view($this->getViewPath($this->infoViewId), compact('infoRow', 'viewExtendData'));
    }

    /**
     * 詳細表示で表示前に加工する必要がある場合
     */
    protected function setInfoDataExtend($infoRow)
    {
        return $infoRow;
    }

    /**
     * ペジネーションの設定
     */
    protected function getPagination($dataRows, $path, $page = 1)
    {
        $paging = $page;
        if(is_null($paging) || $paging <= 0) {
            $paging = 1;
        }

        if(count($dataRows) > $this->rowLimit) {
            $dataChunkArray = array_chunk($dataRows, $this->rowLimit);

            $this->dataRows = $dataChunkArray[$paging - 1];

            $this->paginator = new LengthAwarePaginator($dataChunkArray[$page - 1], count($dataRows), $this->rowLimit, $paging, ['path' => '']);
        } else {
            $this->dataRows = $dataRows;

            $this->paginator = new LengthAwarePaginator($dataRows, count($dataRows), $this->rowLimit, $paging, ['path' => '']);
        }

        $this->paginator->appends('search', $this->searchData);
    }

    /**
     * submitボタン内容の取得
     */
    protected function getSubmitName(Request $request)
    {
        $this->submitName = '';

        $req = $request->all();

        foreach($req as $key => $row) {
            if(strpos($key, 'submit_') !== false) {
                $this->submitName = str_replace('submit_', '', $key);
            }
        }
    }

    /**
     * 通知メッセージの追加
     */
    protected function addViewMessage($message)
    {
        $this->viewMessage[] = $message;
    }

    /**
     * 外部アプリからの連携かどうかを判定
     */
    protected function checkPostApp($req)
    {
        if(!isset($req[$this->postAppKeyName])) {
            return false;
        }

        if(in_array($req[$this->postAppKeyName], $this->postAppList)) {
            return true;
        }

        return false;
    }

    /**
     * GETパラメータを複合する
     */
    protected function decodeGetParameter($requestRow)
    {
        if (isset($requestRow[$this::GET_PARAM_KEY]) && strlen($requestRow[$this::GET_PARAM_KEY]) > 0) {
            $b64Param = $requestRow[$this::GET_PARAM_KEY];
            $jsonParam = base64_decode($b64Param);
            $requestRow = array_merge($requestRow, (array)json_decode($jsonParam));
        }

        return $requestRow;
    }

    /**
     * ランダム値作成
     * @return string
     */
    protected function makeRandomKey()
    {
        return Str::random(32);
    }

    /**
     * セッションキー取得
     * @param object $request
     * @param array $editRow
     * @param string $keyName
     * @return string
     */
    protected function getSessionKey($request, $editRow, $keyName)
    {
        // requestデータ
        $requestData = $request->all();
        $requestData = $this->decodeGetParameter($requestData);
        $sessionKey = $requestData[$keyName] ?? '';
        if (!empty($sessionKey)) {
            return $sessionKey;
        }
        // flashデータ
        $sessionKey = $request->old($keyName) ?? '';
        if (!empty($sessionKey)) {
            return $sessionKey;
        }
        // 編集中データ
        $sessionKey = $editRow[$keyName] ?? '';
        return $sessionKey;
    }

    /**
     * セッションキー情報パラメータ編集
     * @param string $keyId
     * @param string $keyValue
     * @return string
     */
    protected function makeSessionParams($keyId, $keyValue)
    {
        $paramUrl = [
            $keyId  => $keyValue
        ];
        $json_param_url = json_encode($paramUrl);
        $b64_param_url = base64_encode($json_param_url);
        $params = '?' . $this::GET_PARAM_KEY . '=' . $b64_param_url;

        return $params;
    }

    /**
     * URLパラメータ編集
     * @param object $request
     * @param array $editRow
     * @return string
     */
    protected function makeUrlParams($request, $editRow)
    {
        $params = '';
        $sessionKeyId = config('define.session_key_id');
        $sessionKey = $this->getSessionKey($request, $editRow, $sessionKeyId);
        if (!empty($sessionKey)) {
            $params = $this->makeSessionParams($sessionKeyId, $sessionKey);
        } else {
            if (!empty($editRow[$this::GET_PARAM_KEY])) {
                $params = '?' . $this::GET_PARAM_KEY . '=' . $editRow[$this::GET_PARAM_KEY];
            }
        }
        return $params;
    }
}
