<?php

namespace App\Http\Controllers\Master;

use App\Exceptions\DataNotFoundException;
use App\Http\Requests\Master\Base\EditNotifyPaymentTypesRequest;
use App\Http\Requests\Master\Base\EditPaymentTypesRequest;
use App\Http\Requests\Master\Base\NewNotifyPaymentTypesRequest;
use App\Http\Requests\Master\Base\NewPaymentTypesRequest;
use App\Modules\Master\Base\FindPaymentTypesInterface;
use App\Modules\Master\Base\NewPaymentTypesInterface;
use App\Modules\Master\Base\NotifyPaymentTypesInterface;
use App\Modules\Master\Base\SearchPaymentTypesInterface;
use App\Modules\Master\Base\StorePaymentTypesInterface;
use App\Modules\Master\Base\UpdatePaymentTypesInterface;
use App\Modules\Master\Gfh1207\Enums\CooperationType;
use App\Services\EsmSessionManager;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class PaymentTypesController
{
    public const CANCEL_BUTTON_CLICK = 'cancel';
    public const EMPTY_COUNT = 0;
    public const NOTIFY_METHOD_NAME = 'postORput';

    public function __construct(
        private EsmSessionManager $esmSessionManager
    ) {
    }

    /**
     * 支払方法マスタ検索画面表示
     *
     * @param Request $request
     * @param SearchPaymentTypesInterface $search
     * @return View
     */
    public function list(
        Request $request,
        SearchPaymentTypesInterface $search,
    ) {
        $viewExtendData =
            [
                'page_list_count' => Config::get('Common.const.disp_limits'),
                'list_sort' => [
                    'column_name' => 'm_payment_types_sort',
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
            $this->checkErrorException('connectionError');
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

        return account_view('master.payment_types.base.list', $compact);
    }

    /**
     * 支払方法マスタ検索画面 検索処理
     *
     * @param Request $request
     * @param SearchPaymentTypesInterface $search
     * @return View
     */
    public function postList(
        Request $request,
        SearchPaymentTypesInterface $search
    ) {
        $viewExtendData =
            [
                'page_list_count' => Config::get('Common.const.disp_limits'),
                'list_sort' => [
                    'column_name' => 'm_payment_types_sort',
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
            $this->checkErrorException('connectionError');
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

        return account_view('master.payment_types.base.list', $compact);
    }

    /**
     * 支払方法マスタ新規登録画面
     *
     * @param Request $request
     * @param NewPaymentTypesInterface $newPaymentTypes
     * @return View
     */
    public function new(
        NewPaymentTypesInterface $newPaymentTypes
    ) {

        $editRow = $newPaymentTypes->execute();

        return account_view('master.payment_types.base.edit', compact('editRow'));
    }

    /**
     * 支払方法マスタ新規登録確認処理
     *
     * @param Request $request
     * @param NewPaymentTypesRequest $paymentTypesRequest
     * @return RedirectResponse
     */
    public function postNew(
        NewPaymentTypesRequest $paymentTypesRequest
    ) {
        $input = $paymentTypesRequest->validated();

        $encodedParams = $this->esmSessionManager->setSessionKeyName(
            config('define.master.payment_types_register_request'),
            config('define.session_key_id'),
            $input + [
                'previousUrl' => route('master.payment_types.new'),
                'mode' => 'new',
            ]
        );

        return redirect()->route('master.payment_types.notify', ['params' => $encodedParams])
            ->withInput($input);
    }

    /**
     * 支払方法マスタ編集画面
     *
     * @param Request $request
     * @param FindPaymentTypesInterface $findPaymentTypes
     * @return View
     */
    public function edit(
        Request $request,
        FindPaymentTypesInterface $findPaymentTypes
    ) {
        $input = $request->input();

        try {
            // 編集対象のデータ取得
            $editRow = $findPaymentTypes->execute($request->route('id'));
        } catch (DataNotFoundException $e) {
            // データが見つからなかった場合のエラーハンドリング
            Log::error('Data Not Found Error: ' . $e->getMessage());
            $this->checkErrorException('', $e->getMessage());
            return redirect()->route('master.payment_types.list');
        } catch (Exception $e) {
            $editRow = [];
            $editRow['atobarai_com_cooperation_type'] = CooperationType::NO_COOPERATION->value;
            Log::error('Database connection error: ' . $e->getMessage());
            $this->checkErrorException('connectionError');
        }

        return account_view('master.payment_types.base.edit', compact('editRow'));
    }

    /**
     * 支払方法マスタ編集確認処理
     *
     * @param Request $request
     * @param EditPaymentTypesRequest $paymentTypesRequest
     * @param FindPaymentTypesInterface $findPaymentTypes
     * @return RedirectResponse
     */
    public function postEdit(
        Request $request,
        EditPaymentTypesRequest $paymentTypesRequest,
        FindPaymentTypesInterface $findPaymentTypes
    ) {
        $input = $paymentTypesRequest->validated();
        // 編集対象のデータ取得

        try {
            $editRow = $findPaymentTypes->execute($request->route('id'));
        } catch (\App\Exceptions\ModuleValidationException $e) {
            return redirect()->back()->withErrors($e->getValidationErrors());
        } catch (Exception $e) {
            return redirect()->route('master.payment_types.edit', ['id' => $request->route('id')])->withInput($input);
        }

        $encodedParams = $this->esmSessionManager->setSessionKeyName(
            config('define.master.payment_types_register_request'),
            config('define.session_key_id'),
            $input + [
                'previousUrl' => route('master.payment_types.edit', ['id' => $editRow->m_payment_types_id]),
                'mode' => 'edit',
            ],
        );
        return redirect()->route('master.payment_types.notify', ['params' => $encodedParams])
            ->withInput($input);
    }

    /**
     * 支払方法マスタ確認画面
     *
     * @param Request $request
     * @param NotifyPaymentTypesInterface $notifyPaymentTypes
     * @return View
     */
    public function notify(
        Request $request,
        NotifyPaymentTypesInterface $notifyPaymentTypes
    ) {
        $previousInput = $this->esmSessionManager->getSessionKeyName(
            config('define.master.payment_types_register_request'),
            config('define.session_key_id'),
            $request->input('params')
        );

        // 前画面の入力情報が取得できない場合はリダイレクト
        if (empty($previousInput) && empty($request->old())) {
            return redirect()->route('master.payment_types.list');
        }

        try {
            $exFillData = [];
            $paymentTypes = $notifyPaymentTypes->execute($previousInput, $exFillData, $previousInput['m_payment_types_id'] ?? null);
            $compact = [
                'input' => $previousInput,
                'editRow' => $paymentTypes,
                'param' => $request->input('params'),
                'mode' => $previousInput['mode'] ?? null
            ];
        } catch (Exception $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            $this->checkErrorException('connectionError');
            $paymentTypes = [];
            $paymentTypes['atobarai_com_cooperation_type'] = CooperationType::NO_COOPERATION->value;

            $compact = [
                'input' => $previousInput,
                'editRow' => $paymentTypes,
                'param' => $request->input('params'),
                'mode' => $previousInput['mode'] ?? null
            ];

            if (old('method') == self::NOTIFY_METHOD_NAME) {
                return account_view('master.payment_types.base.notify', $compact);
            }

            return redirect($previousInput['previousUrl'])
                ->withInput($previousInput);
        }

        return account_view('master.payment_types.base.notify', $compact);
    }

    /**
     * 支払方法マスタ登録処理
     *
     * @param NewNotifyPaymentTypesRequest $request
     * @param StorePaymentTypesInterface $storePaymentTypes
     * @return RedirectResponse
     */
    public function postNotify(
        NewNotifyPaymentTypesRequest $request,
        StorePaymentTypesInterface $storePaymentTypes
    ) {
        $input = $request->validated();
        $input['method'] = self::NOTIFY_METHOD_NAME;

        // キャンセルをクリックする
        $submit = $this->getSubmitName($request);
        if ($submit == self::CANCEL_BUTTON_CLICK) {
            return redirect()->route('master.payment_types.new')
                ->withInput($input);
        }

        try {
            $storePaymentTypes->execute($input, [
                'm_account_id' => $this->esmSessionManager->getAccountId(),
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->route('master.payment_types.notify', ['params' => $request->input('params')])->withInput($input);
        }

        // セッション情報を削除する。
        $this->esmSessionManager->forgetSessionKeyName(
            config('define.master.payment_types_register_request'),
            config('define.session_key_id'),
            $request->input('params')
        );

        return redirect(route('master.payment_types.new'))->with([
            'messages.info' => ['message' => __('messages.info.create_completed', ['data' => '支払方法マスタ'])]
        ]);
    }

    /**
     * 支払方法マスタ更新処理
     *
     * @param Request $request
     * @param EditNotifyPaymentTypesRequest $editNotifyPaymentTypesReq
     * @param UpdatePaymentTypesInterface $updatePaymentTypes

     * @return RedirectResponse
     */
    public function putNotify(
        Request $request,
        EditNotifyPaymentTypesRequest $editNotifyPaymentTypesReq,
        UpdatePaymentTypesInterface $updatePaymentTypes
    ) {
        $input = $editNotifyPaymentTypesReq->validated();
        $input['method'] = self::NOTIFY_METHOD_NAME;

        // キャンセルをクリックする
        $submit = $this->getSubmitName($request);
        if ($submit == self::CANCEL_BUTTON_CLICK) {
            return redirect()->route('master.payment_types.edit', ['id' => $input['m_payment_types_id']])
                ->withInput($input);
        }

        try {
            $updatePaymentTypes->execute($input['m_payment_types_id'], $input, []);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->route('master.payment_types.notify', ['params' => $request->input('params')])->withInput($input);
        }

        // セッション情報を削除する。
        $this->esmSessionManager->forgetSessionKeyName(
            config('define.master.payment_types_register_request'),
            config('define.session_key_id'),
            $editNotifyPaymentTypesReq->input('params')
        );

        return redirect()->route('master.payment_types.edit', ['id' => $input['m_payment_types_id']])
            ->with([
                'editRow' => $input,
                'messages.info' => ['message' => __('messages.info.update_completed', ['data' => '支払方法マスタ'])]
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
