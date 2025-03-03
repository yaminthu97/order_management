<?php

namespace App\Http\Controllers\Common;

use Illuminate\Http\Request;

class CommonController extends AppSyscomController
{
    protected $subsystem = 'Cc';

    public function list(Request $request)
    {

        $this->getSubmitName($request);

        $req = $request->all();
        $sessionKeyId = config('define.session_key_id');
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
                //$dataRows = json_decode($manager->search($searchRow), true);
                $dataRows = $manager->search($searchRow);

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
            } else {
                //頁初期表示時
                if(empty($req['_token'])) {
                    // 頁初期表示時は、デフォルトで電話番号、FAX番号の前方一致検索チェックの選択あり
                    $searchRow['tel_forward_match'] = '1';
                    $searchRow['fax_forward_match'] = '1';
                }
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

            if(!isset($errorResult)) {
                $errorResult = null;
            }
            if(!isset($dataList)) {
                $dataList = null;
            }
            if(!isset($paginator)) {
                $paginator = null;
            }
            if(!isset($viewName)) {
                $viewName  = null;
            }
            if(!isset($pageRows)) {
                $pageRows   = null;
            }

            $viewMessage = $this->viewMessage;

            return view($this->getViewPath($this->searchViewId), compact('searchResult', 'dataList', 'errorResult', 'paginator', 'viewName', 'pageRows', 'viewExtendData', 'searchRow', 'viewMessage'));
        } catch(\Exception $ex) {
            return $ex->__toString();
        }
    }

}
