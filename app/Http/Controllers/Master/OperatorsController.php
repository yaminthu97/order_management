<?php

namespace App\Http\Controllers\Master;

use App\Exceptions\DataNotFoundException;
use App\Http\Requests\Master\Base\EditNotifyOperatorsRequest;
use App\Http\Requests\Master\Base\EditOperatorsRequest;
use App\Http\Requests\Master\Base\NewNotifyOperatorsRequest;
use App\Http\Requests\Master\Base\NewOperatorsRequest;
use App\Modules\Master\Base\FindOperatorsInterface;
use App\Modules\Master\Base\GetOperationAuthoritiesInterface;
use App\Modules\Master\Base\GetOperatorUserTypeInterface;
use App\Modules\Master\Base\NewOperatorsInterface;
use App\Modules\Master\Base\NotifyOperatorsInterface;
use App\Modules\Master\Base\SearchOperatorsInterface;
use App\Modules\Master\Base\StoreOperatorsInterface;
use App\Modules\Master\Base\UpdateOperatorsInterface;
use App\Modules\Master\Gfh1207\Enums\UserTypeEnum;
use App\Services\EsmSessionManager;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class OperatorsController
{
    public const CANCEL_BUTTON_CLICK = 'cancel';
    public const EMPTY_COUNT = 0;
    public const NOTIFY_METHOD_NAME = 'postORput';

    public function __construct(
        private EsmSessionManager $esmSessionManager
    ) {
    }

    /**
     * 社員マスタ検索画面表示
     *
     * @param Request $request
     * @param SearchOperatorsInterface $search
     * @return View
     */
    public function list(
        Request $request,
        SearchOperatorsInterface $search,
    ) {
        $viewExtendData =
            [
                'page_list_count' => Config::get('Common.const.disp_limits'),
                'list_sort' => [
                    'column_name' => 'm_operators_id',
                    'sorting_shift' => 'asc',
                ]
            ];

        $viewExtendData ??= null;
        $paginator ??= null;
        $paginator = [];

        if ($paginator) {
            $searchResult['search_record_count'] = $paginator->count();
            $searchResult['total_record_count'] = $paginator->total();
        } else {
            $searchResult['search_record_count'] = self::EMPTY_COUNT;
            $searchResult['total_record_count'] = self::EMPTY_COUNT;
        }

        $req = $request->all();
        $searchRow = $req;
        $searchRow['page_list_count'] = Config::get('esm.default_page_size.master');
        $searchRow['delete_flg'] = [0];// Filter for active records

        $options = [
            'should_paginate' => true,
            'limit' => $req['page_list_count'] ?? Config::get('esm.default_page_size.master'),
            'page' => $req['hidden_next_page_no'] ?? 1,
        ];

        if (!empty($req['sorting_column']) && !empty($req['sorting_shift'])) {
            $viewExtendData['list_sort'] = [
                'column_name' => $req['sorting_column'],
                'sorting_shift' => $req['sorting_shift'],
            ];
            $options['sorts'][$req['sorting_column']] = $req['sorting_shift'];
        }

        // 検索処理
        $paginator = $search->execute($req, $options);

        if (isset($paginator['error'])) {
            $paginator = null;
            $this->checkerrorexception('connectionError');
        } else {
            if ($paginator) {
                $searchResult['search_record_count'] = $paginator->count();
                $searchResult['total_record_count'] = $paginator->total();
            }
        }

        // view 向け項目初期値
        $compact = [
            'searchResult' => $searchResult ?? [],
            'paginator' => $paginator ?? null,
            'viewExtendData' => $viewExtendData ?? null,
            'searchRow' => $searchRow ?? $req,
        ];
        return account_view('master.operators.base.list', $compact);
    }

    /**
     * 社員マスタ検索画面 検索処理
     *
     * @param Request $request
     * @param SearchOperatorsInterface $search
     * @return View
     */
    public function postList(
        Request $request,
        SearchOperatorsInterface $search
    ) {
        $viewExtendData =
            [
                'page_list_count' => Config::get('Common.const.disp_limits'),
                'list_sort' => [
                    'column_name' => 'm_operators_id',
                    'sorting_shift' => 'asc',
                ]
            ];

        $req = $request->all();
        $submitName = $this->getSubmitName($req);
        $searchRow = $req;

        $searchResult = [
            'search_record_count' => self::EMPTY_COUNT,
            'total_record_count' => self::EMPTY_COUNT,
        ];

        $options = [
            'should_paginate' => true,
            'limit' => $req['page_list_count'] ?? Config::get('esm.default_page_size.master'),
            'page' => $req['hidden_next_page_no'] ?? 1,
        ];


        if (!empty($req['sorting_column']) && !empty($req['sorting_shift'])) {
            $viewExtendData['list_sort'] = [
                'column_name' => $req['sorting_column'],
                'sorting_shift' => $req['sorting_shift'],
            ];
            $options['sorts'][$req['sorting_column']] = $req['sorting_shift'];
        }

        // 検索処理
        $paginator = $search->execute($req, $options);

        if (isset($paginator['error'])) {
            $paginator = null;
            $this->checkerrorexception('connectionError');
        } else {
            if ($paginator) {
                $searchResult['search_record_count'] = $paginator->count();
                $searchResult['total_record_count'] = $paginator->total();
            }
        }

        // view 向け項目初期値
        $compact = [
            'searchResult' => $searchResult ?? [],
            'paginator' => $paginator ?? null,
            'viewExtendData' => $viewExtendData ?? null,
            'searchRow' => $searchRow ?? $req,
        ];

        return account_view('master.operators.base.list', $compact);
    }

    /**
     * 社員マスタ新規登録画面
     *
     * @param Request $request
     * @param NewOperatorsInterface $newOperators
     * @return View
     */
    public function new(
        NewOperatorsInterface $newOperators,
        GetOperationAuthoritiesInterface $getOperationAuthorities,
        GetOperatorUserTypeInterface $operatorUserType
    ) {

        $operatorUserType = $operatorUserType->execute();

        if (is_null($operatorUserType) || $operatorUserType === UserTypeEnum::GENERAL_USER->value) {
            throw new \App\Exceptions\AccessPermissionException();
        }

        if (isset($operatorUserType['error'])) {
            $this->checkerrorexception('connectionError');
        }

        try {
            // 編集対象のデータ取得
            $editRow = $newOperators->execute();

            $operationAuthorities = $getOperationAuthorities->execute();

            $account_cd = $this->esmSessionManager->getAccountCode();

        } catch (Exception $e) {
            $editRow = [];
            $editRow['g2fa_key'] = 0;
            $operationAuthorities = null;
            $operatorUserType = null;
            $account_cd = null;

            Log::error('Database connection error: ' . $e->getMessage());
            $this->checkErrorException('connectionError');
        }

        $compact = [
            'editRow',
            'operationAuthorities',
            'operatorUserType',
            'account_cd'
        ];

        return account_view('master.operators.base.edit', compact($compact));
    }

    /**
     * 社員マスタ新規登録確認処理
     *
     * @param Request $request
     * @param NewOperatorsRequest $operatorsRequest
     * @return RedirectResponse
     */
    public function postNew(
        NewOperatorsRequest $operatorsRequest
    ) {
        // Validate request
        $input = $operatorsRequest->validated();

        $encodedParams = $this->esmSessionManager->setSessionKeyName(
            config('define.master.operators_register_request'),
            config('define.session_key_id'),
            $input + [
                'previousUrl' => route('master.operators.new'),
                'mode' => 'new',
            ]
        );

        return redirect()->route('master.operators.notify', ['params' => $encodedParams])
            ->withInput($input);
    }

    /**
     * 社員マスタ編集画面
     *
     * @param Request $request
     * @param FindOperatorsInterface $findOperators
     * @return View
     */
    public function edit(
        Request $request,
        FindOperatorsInterface $findOperators,
        GetOperationAuthoritiesInterface $getOperationAuthorities,
        GetOperatorUserTypeInterface $operatorUserType
    ) {
        $input = $request->input();

        $operatorId = $this->esmSessionManager->getOperatorId();

        $operatorUserType = $operatorUserType->execute();

        if (is_null($operatorUserType) || $operatorUserType === UserTypeEnum::GENERAL_USER->value) {
            if ($operatorId != $request->route('id')) {
                throw new \App\Exceptions\AccessPermissionException();
            }
        }

        try {
            // 編集対象のデータ取得
            $editRow = $findOperators->execute($request->route('id'));
            $operationAuthorities = $getOperationAuthorities->execute();

            $account_cd = $this->esmSessionManager->getAccountCode();

        } catch (DataNotFoundException $e) {
            // データが見つからなかった場合のエラーハンドリング
            Log::error('Data Not Found Error: ' . $e->getMessage());
            $this->checkErrorException('', $e->getMessage());
            return redirect()->route('master.operators.list');
        } catch (Exception $e) {
            $editRow = [];
            $editRow['g2fa_key'] = 0;
            $operationAuthorities = null;
            $operatorUserType = null;
            $account_cd = null;

            Log::error('Database connection error: ' . $e->getMessage());
            $this->checkErrorException('connectionError');
        }

        $compact = [
            'editRow',
            'operationAuthorities',
            'operatorUserType',
            'account_cd'
        ];

        return account_view('master.operators.base.edit', compact($compact));
    }

    /**
     * 社員マスタ編集確認処理
     *
     * @param Request $request
     * @param EditOperatorsRequest $operatorsRequest
     * @param FindOperatorsInterface $findOperators
     * @return RedirectResponse
     */
    public function postEdit(
        Request $request,
        EditOperatorsRequest $operatorsRequest,
        FindOperatorsInterface $findOperators
    ) {
        $input = $operatorsRequest->validated();

        try {
            // 編集対象のデータ取得
            $editRow = $findOperators->execute($request->route('id'));

            if (isset($request['login_password']) && $request['login_password']) {
                // 最終更新日時の1日後と現在時刻を取得
                $passwordUpdateTimestamp = Carbon::parse($editRow->password_update_timestamp);
                $now = Carbon::now();
                $oneDayLater = $passwordUpdateTimestamp->copy()->addDay();
                // 現在の日時が更新日時から24時間以内かどうかを判定
                if ($now->lessThanOrEqualTo($oneDayLater)) {
                    return redirect()->back()->withErrors(['login_password' => 'ログインパスワードの変更は24時間に1度までです']);
                }

                // パスワード履歴との一致チェック
                $passwordHistory = $editRow->password_history ? json_decode($editRow->password_history, true) : [];

                if (Hash::check($request['login_password'], $editRow->login_password)) {
                    return redirect()->back()->withErrors(['login_password' => 'ログインパスワードは以前に使用されています']);
                }

                foreach ($passwordHistory as $oldPassword) {
                    if (Hash::check($request['login_password'], $oldPassword['hash'])) {
                        return redirect()->back()->withErrors(['login_password' => 'ログインパスワードは以前に使用されています']);
                    }
                }
            }
        } catch (\App\Exceptions\ModuleValidationException $e) {
            return redirect()->back()->withErrors($e->getValidationErrors());
        } catch (Exception $e) {
            return redirect()->route('master.operators.edit', ['id' => $request->route('id')])->withInput($input);
        }

        $encodedParams = $this->esmSessionManager->setSessionKeyName(
            config('define.master.operators_register_request'),
            config('define.session_key_id'),
            $input + [
                'previousUrl' => route('master.operators.edit', ['id' => $editRow->m_operators_id]),
                'mode' => 'edit',
            ],
        );
        return redirect()->route('master.operators.notify', ['params' => $encodedParams])
            ->withInput($input);
    }

    /**
     * 社員マスタ確認画面
     *
     * @param Request $request
     * @param NotifyOperatorsInterface $notifyOperators
     * @return View
     */
    public function notify(
        Request $request,
        NotifyOperatorsInterface $notifyOperators,
        GetOperationAuthoritiesInterface $getOperationAuthorities,
        GetOperatorUserTypeInterface $operatorUserType
    ) {
        $previousInput = $this->esmSessionManager->getSessionKeyName(
            config('define.master.operators_register_request'),
            config('define.session_key_id'),
            $request->input('params')
        );

        // 前画面の入力情報が取得できない場合はリダイレクト
        if (empty($previousInput) && empty($request->old())) {
            return redirect()->route('master.operators.list');
        }

        try {
            $exFillData = [];
            $operator = $notifyOperators->execute($previousInput, $exFillData, $previousInput['m_operators_id'] ?? null);

            $operationAuthorities = $getOperationAuthorities->execute();

            $operatorUserType = $operatorUserType->execute();

            $compact = [
                'input' => $previousInput,
                'editRow' => $operator,
                'param' => $request->input('params'),
                'mode' => $previousInput['mode'] ?? null,
                'operationAuthorities' => $operationAuthorities,
                'operatorUserType' =>  $operatorUserType
            ];
        } catch (Exception $e) {
            Log::error('Database connection error dsfsfsfasfasfdasfasfasfwfvsdgvfsesfdc: ' . $e->getMessage());
            $this->checkErrorException('connectionError');

            $operator = [];

            $compact = [
                'input' => $previousInput,
                'editRow' => $operator,
                'param' => $request->input('params'),
                'mode' => $previousInput['mode'] ?? null,
                'operationAuthorities' => null,
                'operatorUserType' =>  null
            ];

            if (old('method') == self::NOTIFY_METHOD_NAME) {
                return account_view('master.operators.base.notify', $compact);
            }

            return redirect($previousInput['previousUrl'])
                ->withInput($previousInput);
        }

        return account_view('master.operators.base.notify', $compact);
    }

    /**
     * 社員マスタ登録処理
     *
     * @param NewNotifyOperatorsRequest $request
     * @param StoreOperatorsInterface $storeOperators
     * @return RedirectResponse
     */
    public function postNotify(
        NewNotifyOperatorsRequest $request,
        StoreOperatorsInterface $storeOperators
    ) {
        $input = $request->validated();
        $input['method'] = self::NOTIFY_METHOD_NAME;
        // キャンセルをクリックする
        $submit = $this->getSubmitName($request);
        if ($submit == self::CANCEL_BUTTON_CLICK) {
            return redirect()->route('master.operators.new')
                ->withInput($input);
        }

        try {
            $storeOperators->execute($input, [
                'm_account_id' => $this->esmSessionManager->getAccountId(),
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->route('master.operators.notify', ['params' => $request->input('params')])->withInput($input);
        }

        // セッション情報を削除する。
        $this->esmSessionManager->forgetSessionKeyName(
            config('define.master.operators_register_request'),
            config('define.session_key_id'),
            $request->input('params')
        );

        return redirect(route('master.operators.new'))->with([
            'messages.info' => ['message' => __('messages.info.create_completed', ['data' => '社員マスタ'])]
        ]);
    }

    /**
     * 社員マスタ更新処理
     *
     * @param Request $request
     * @param EditNotifyOperatorsRequest $editNotifyOperatorsReq
     * @param UpdateOperatorsInterface $updateOperators

     * @return RedirectResponse
     */
    public function putNotify(
        Request $request,
        EditNotifyOperatorsRequest $editNotifyOperatorsReq,
        UpdateOperatorsInterface $updateOperators
    ) {
        $input = $editNotifyOperatorsReq->validated();
        $input['method'] = self::NOTIFY_METHOD_NAME;

        // キャンセルをクリックする
        $submit = $this->getSubmitName($request);
        if ($submit == self::CANCEL_BUTTON_CLICK) {
            return redirect()->route('master.operators.edit', ['id' => $input['m_operators_id']])
                ->withInput($input);
        }

        try {
            $updateOperators->execute($input['m_operators_id'], $input, []);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->route('master.operators.notify', ['params' => $request->input('params')])->withInput($input);
        }

        // セッション情報を削除する。
        $this->esmSessionManager->forgetSessionKeyName(
            config('define.master.operators_register_request'),
            config('define.session_key_id'),
            $editNotifyOperatorsReq->input('params')
        );

        return redirect()->route('master.operators.edit', ['id' => $input['m_operators_id']])
            ->with([
                'editRow' => $input,
                'messages.info' => ['message' => __('messages.info.update_completed', ['data' => '社員マスタ'])]
            ]);
    }

    /**
     * submitボタン内容の取得
     */
    protected function getSubmitName($req)
    {
        $submitName = '';
        if (!empty($req['submit'])) {
            $submitName = $req['submit'];
        }
        return $submitName;
    }

    /**
     * show error message for connection error
     */
    public function checkErrorException($results = '', $message = '')
    {
        if ($results === 'connectionError') {
            session()->flash('messages.error', ['message' => __('messages.error.connection_error')]);
        } elseif ($message != '') {
            session()->flash('messages.error', ['message' => __($message)]);
        }
    }
}
