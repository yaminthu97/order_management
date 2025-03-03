<?php

namespace App\Http\Controllers\Customer;

use App\Enums\DeleteFlg;
use App\Enums\ItemNameType;
use App\Exceptions\DataNotFoundException;
use App\Http\Requests\Customer\Base\CustCommunicationRequest;
use App\Http\Requests\Customer\Base\NewNotifyCustCommRequest;
use App\Modules\Common\Base\GetPrefecturalInterface;
use App\Modules\Customer\Base\DeleteCustCommunicationDtlInterface;
use App\Modules\Customer\Base\FindCustCommunicationDtlInterface;
use App\Modules\Customer\Base\FindCustCommunicationInterface;
use App\Modules\Customer\Base\GetDeleteAuthorityInterface;
use App\Modules\Customer\Base\NewCustCommunicationInterface;
use App\Modules\Customer\Base\NotifyCustCommunicationInterface;
use App\Modules\Customer\Base\SearchCustCommunicationInterface;
use App\Modules\Customer\Base\SetCustHistOutputBatchExecuteInterface;
use App\Modules\Customer\Base\SetReportOutputBatchExecuteInterface;
use App\Modules\Customer\Base\StoreCustCommunicationDtlInterface;
use App\Modules\Customer\Base\StoreCustCommunicationInterface;
use App\Modules\Customer\Base\UpdateCustCommunicationInterface;
use App\Modules\Master\Base\GetItemnameTypeInterface;
use App\Modules\Master\Base\GetOperatorsInterface;
use App\Services\EsmSessionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class CustomerHistoryController
{
    protected $className = 'CustomerHistory';
    protected $namespace = 'CustomerHistory';
    protected $searchViewId = 'NECSM0120';
    protected $postAppList = ['mail-dealer'];
    protected $postAppKeyName = 'post_app';

    public const EMPTY_LENGTH_CHECK = 0;
    public const CANCEL_BUTTON_CLICK = 'cancel';

    public function __construct(
        protected EsmSessionManager $esmSessionManager
    ) {
    }

    public function index(
        Request $request,
        SetCustHistOutputBatchExecuteInterface $setOutputBatchExecute,
        GetItemnameTypeInterface $getItemnameType,
        GetOperatorsInterface $getOperator,
        GetPrefecturalInterface $getPrefecture
    ) {

        $statusList = $getItemnameType->execute(ItemNameType::CustomerSupportStatus->value);
        $contactWayTypes = $getItemnameType->execute(ItemNameType::CustomerContact->value);
        $categoryList = $getItemnameType->execute(ItemNameType::CustomerSupportType->value);
        $salesChannel = $getItemnameType->execute(ItemNameType::SalesContact->value, DeleteFlg::Use->value, true);
        $inquiryType = $getItemnameType->execute(ItemNameType::ContactType->value, DeleteFlg::Use->value, true);
        $resStatus = $getItemnameType->execute(ItemNameType::SupportResult->value, DeleteFlg::Use->value, true);
        $operatorNameList = $getOperator->execute();
        $prefectuals = $getPrefecture->execute();

        if (isset($statusList['error']) || isset($contactWayTypes['error']) || isset($categoryList['error']) || isset($salesChannel['error']) || isset($inquiryType['error']) || isset($resStatus['error']) || isset($operatorNameList['error']) || isset($prefectuals['error'])) {
            // view 向けデータ
            $viewExtendData = [
                'statusList' => [],
                'contactWayTypes' => [],
                'categoryList' => [],
                'salesChannel' => [],
                'inquiryType' => [],
                'resStatus' => [],
                'operatorNameList' => [],
                'prefectuals' => [],
            ];
            session()->flash('messages.error', ['message' => __('messages.error.connection_error')]);
        } else {
            $viewExtendData =
                [
                    'statusList' => $statusList,
                    'contactWayTypes' => $contactWayTypes,
                    'categoryList' => $categoryList,
                    'salesChannel' => $salesChannel,
                    'inquiryType' => $inquiryType,
                    'resStatus' => $resStatus,
                    'operatorNameList' => $operatorNameList,
                    'prefectuals' => $prefectuals,
                    'page_list_count' => Config::get('Common.const.disp_limits'),
                    'list_sort' => [
                        'column_name' => 't_cust_communication_id',
                        'sorting_shift' => 'desc',
                    ]
                ];
        }

        $viewExtendData ??= null;
        $paginator ??= null;
        $paginator = [];

        if ($paginator) {
            $searchResult['search_record_count'] = $paginator->count();
            $searchResult['total_record_count'] = $paginator->total();
        } else {
            $searchResult['search_record_count'] = 0;
            $searchResult['total_record_count'] = 0;
        }

        $req = $request->all();
        $searchRow = $req;
        $searchRow['tel_search_flag'] = '1';
        $sessionKeyId = null;

        $compact = [
            'viewExtendData',
            'paginator',
            'searchRow',
            'sessionKeyId',
            'searchResult'
        ];

        return account_view('customerHistory.gfh_1207.list', compact($compact));
    }

    public function list(
        Request $request,
        SetCustHistOutputBatchExecuteInterface $setOutputBatchExecute,
        SearchCustCommunicationInterface $search,
        GetItemnameTypeInterface $getItemnameType,
        GetOperatorsInterface $getOperator,
        GetPrefecturalInterface $getPrefecture
    ) {

        $statusList = $getItemnameType->execute(ItemNameType::CustomerSupportStatus->value);
        $contactWayTypes = $getItemnameType->execute(ItemNameType::CustomerContact->value);
        $categoryList = $getItemnameType->execute(ItemNameType::CustomerSupportType->value);
        $salesChannel = $getItemnameType->execute(ItemNameType::SalesContact->value, DeleteFlg::Use->value, true);
        $inquiryType = $getItemnameType->execute(ItemNameType::ContactType->value, DeleteFlg::Use->value, true);
        $resStatus = $getItemnameType->execute(ItemNameType::SupportResult->value, DeleteFlg::Use->value, true);
        $operatorNameList = $getOperator->execute();
        $prefectuals = $getPrefecture->execute();

        if (isset($statusList['error']) || isset($contactWayTypes['error']) || isset($categoryList['error']) || isset($salesChannel['error']) || isset($inquiryType['error']) || isset($resStatus['error']) || isset($operatorNameList['error']) || isset($prefectuals['error'])) {
            // view 向けデータ
            $viewExtendData = [
                'statusList' => [],
                'contactWayTypes' => [],
                'categoryList' => [],
                'salesChannel' => [],
                'inquiryType' => [],
                'resStatus' => [],
                'operatorNameList' => [],
                'prefectuals' => [],
            ];
            session()->flash('messages.error', ['message' => __('messages.error.connection_error')]);
        } else {
            $viewExtendData =
                [
                    'statusList' => $statusList,
                    'contactWayTypes' => $contactWayTypes,
                    'categoryList' => $categoryList,
                    'salesChannel' => $salesChannel,
                    'inquiryType' => $inquiryType,
                    'resStatus' => $resStatus,
                    'operatorNameList' => $operatorNameList,
                    'prefectuals' => $prefectuals,
                    'page_list_count' => Config::get('Common.const.disp_limits'),
                    'list_sort' => [
                        'column_name' => 't_cust_communication_id',
                        'sorting_shift' => 'desc',
                    ]
                ];
        }

        $req = $request->all();
        $submitName = $this->getSubmitName($req);
        $searchRow = $req;

        $searchResult = [
            'search_record_count' => 0,
            'total_record_count' => 0,
        ];

        $options = [
            'should_paginate' => true,
            'limit' => $req['page_list_count'] ?? Config::get('Common.const.page_limit'),
            'page' => $req['hidden_next_page_no'] ?? 1,
        ];

        if (!empty($req['sorting_column']) && !empty($req['sorting_shift'])) {
            $viewExtendData['list_sort'] = [
                'column_name' => $req['sorting_column'],
                'sorting_shift' => $req['sorting_shift'],
            ];
            $options['sorts'][$req['sorting_column']] = $req['sorting_shift'];
        }

        // リダイレクト時のセッションから呼び出される場合
        if (session()->exists('outside_post_redirect')) {
            $outsidePostRedirect = session()->get('outside_post_redirect');
            if (!empty($outsidePostRedirect[$this->postAppKeyName])) {
                $req[$this->postAppKeyName] = $outsidePostRedirect[$this->postAppKeyName];

                switch ($outsidePostRedirect[$this->postAppKeyName]) {
                    case 'mail-dealer':
                        //MailDealerからの呼び出し
                        $searchRow['email'] = $outsidePostRedirect['email'];
                        break;
                    default:
                        break;
                }
            }
            session()->forget('outside_post_redirect');
        }

        // 検索処理
        $paginator = $search->execute($req, $options);

        if (isset($paginator['error'])) {
            $paginator = null;
            session()->flash('messages.error', ['message' => __('messages.error.connection_error')]);
        } else {
            if ($paginator) {
                $searchResult['search_record_count'] = $paginator->count();
                $searchResult['total_record_count'] = $paginator->total();
            }

            $params = $paginator->toArray();
            $sessionKeyId = $this->esmSessionManager->setSessionKeyName(
                config('define.cc.search_custCommunication_session'),
                config('define.cc.session_key_id'),
                $params
            );

            $req['data_key_id'] = $sessionKeyId;

            if (empty($req['tel_search_flag'])) {
                $searchRow['tel_search_flag'] = '0';
            }
        }

        // view 向け項目初期値
        $searchResult ??= [];
        $dataList ??= null;
        $errorResult ??= null;
        $paginator ??= null;
        $viewName ??= null;
        $viewExtendData ??= null;
        $pageRows ??= null;
        $searchRow ??= $req;
        $sessionKeyId ??= null;

        $compact = [
            'searchResult',
            'dataList',
            'errorResult',
            'paginator',
            'viewName',
            'pageRows',
            'searchRow',
            'viewExtendData',
            'searchRow',
            'sessionKeyId'
        ];

        return account_view('customerHistory.gfh_1207.list', compact($compact));
    }

    public function csvOutput(
        Request $request,
        SetCustHistOutputBatchExecuteInterface $setOutputBatchExecute
    ) {
        $req = $request->all();

        $csvOutputErrorResult = $setOutputBatchExecute->execute($req);

        if (empty($csvOutputErrorResult)) {
            $viewMessage[] = __('messages.success.csv_output_success');
            return response()->json(['viewMessage' => $viewMessage, 'type' => 'success']);
        } else {
            $errorMessage[] = __('messages.error.connection_error');
            return response()->json(['viewMessage' => [$errorMessage], 'type' => 'error']);
        }
    }

    public function new(
        GetItemnameTypeInterface $getItemnameType,
        GetOperatorsInterface $getOperator,
        GetPrefecturalInterface $getPrefecture,
        NewCustCommunicationInterface $newCustCommunication
    ) {

        $statusList = $getItemnameType->execute(ItemNameType::CustomerSupportStatus->value);
        $contactWayTypes = $getItemnameType->execute(ItemNameType::CustomerContact->value);
        $categoryList = $getItemnameType->execute(ItemNameType::CustomerSupportType->value);
        $salesChannel = $getItemnameType->execute(ItemNameType::SalesContact->value, DeleteFlg::Use->value, true);
        $inquiryType = $getItemnameType->execute(ItemNameType::ContactType->value, DeleteFlg::Use->value, true);
        $resStatus = $getItemnameType->execute(ItemNameType::SupportResult->value, DeleteFlg::Use->value, true);
        $operatorNameList = $getOperator->execute();
        $prefectuals = $getPrefecture->execute();

        if (isset($statusList['error']) || isset($contactWayTypes['error']) || isset($categoryList['error']) || isset($salesChannel['error']) || isset($inquiryType['error']) || isset($resStatus['error']) || isset($operatorNameList['error']) || isset($prefectuals['error'])) {
            // view 向けデータ
            $viewExtendData = [
                'statusList' => [],
                'contactWayTypes' => [],
                'categoryList' => [],
                'salesChannel' => [],
                'inquiryType' => [],
                'resStatus' => [],
                'operatorNameList' => [],
                'prefectuals' => [],
            ];
            session()->flash('messages.error', ['message' => __('messages.error.connection_error')]);
        } else {
            $viewExtendData =
                [
                    'statusList' => $statusList,
                    'contactWayTypes' => $contactWayTypes,
                    'categoryList' => $categoryList,
                    'salesChannel' => $salesChannel,
                    'inquiryType' => $inquiryType,
                    'resStatus' => $resStatus,
                    'operatorNameList' => $operatorNameList,
                    'prefectuals' => $prefectuals,
                ];
        }

        $editRow = $newCustCommunication->execute();
        $viewExtendData ??= null;
        $viewMessage = session('viewMessage', []);
        $sessionKeyId ??= null;
        $previous_url = null;
        $isEdit = false;

        $old = session()->get('_old_input');
        if (isset($old['previous_url']) && strlen($old['previous_url']) > self::EMPTY_LENGTH_CHECK) {
            $previous_url = $old['previous_url'];
        }

        $compact = [
            'viewExtendData',
            'editRow',
            'viewMessage',
            'sessionKeyId',
            'previous_url',
            'isEdit'
        ];

        return account_view('customerHistory.gfh_1207.edit', compact($compact));
    }


    public function postNew(CustCommunicationRequest $request)
    {

        $input = $request->validated();
        $previous_url = $request->previous_url;

        $encodedParams = $this->esmSessionManager->setSessionKeyName(
            config('define.cc.custCommunication_register_request'),
            config('define.session_key_id'),
            $input + [
                'previous_url' => $previous_url,
                'mode' => 'new',
            ]
        );

        return redirect()->route('cc.customer-history.notify', ['params' => $encodedParams])->withInput($input);
    }


    public function edit(
        $id,
        Request $request,
        GetItemnameTypeInterface $getItemnameType,
        GetOperatorsInterface $getOperator,
        GetPrefecturalInterface $getPrefecture,
        FindCustCommunicationInterface $findCustCommunication,
        FindCustCommunicationDtlInterface $findCustCommunicationDtl,
        GetDeleteAuthorityInterface $getDeleteAuthority
    ) {

        $statusList = $getItemnameType->execute(ItemNameType::CustomerSupportStatus->value);
        $contactWayTypes = $getItemnameType->execute(ItemNameType::CustomerContact->value);
        $categoryList = $getItemnameType->execute(ItemNameType::CustomerSupportType->value);
        $salesChannel = $getItemnameType->execute(ItemNameType::SalesContact->value, DeleteFlg::Use->value, true);
        $inquiryType = $getItemnameType->execute(ItemNameType::ContactType->value, DeleteFlg::Use->value, true);
        $resStatus = $getItemnameType->execute(ItemNameType::SupportResult->value, DeleteFlg::Use->value, true);
        $operatorNameList = $getOperator->execute();
        $prefectuals = $getPrefecture->execute();

        if (isset($statusList['error']) || isset($contactWayTypes['error']) || isset($categoryList['error']) || isset($salesChannel['error']) || isset($inquiryType['error']) || isset($resStatus['error']) || isset($operatorNameList['error']) || isset($prefectuals['error'])) {
            // view 向けデータ
            $viewExtendData = [
                'statusList' => [],
                'contactWayTypes' => [],
                'categoryList' => [],
                'salesChannel' => [],
                'inquiryType' => [],
                'resStatus' => [],
                'operatorNameList' => [],
                'prefectuals' => [],
            ];
            session()->flash('messages.error', ['message' => __('messages.error.connection_error')]);
        } else {
            $viewExtendData =
                [
                    'statusList' => $statusList,
                    'contactWayTypes' => $contactWayTypes,
                    'categoryList' => $categoryList,
                    'salesChannel' => $salesChannel,
                    'inquiryType' => $inquiryType,
                    'resStatus' => $resStatus,
                    'operatorNameList' => $operatorNameList,
                    'prefectuals' => $prefectuals,
                ];
        }

        $editRow = [];

        try {
            $hasAuthority = $getDeleteAuthority->execute();

            $editRow = $findCustCommunication->execute($id);

            if (empty($editRow)) {
                return redirect()->back()->with([
                    'messages.error' => ['message' => __('messages.error.data_not_found', ['data' => '顧客対応履歴', 'id' => $id])]
                ]);
            }

            if (isset($editRow['error'])) {
                $custCommunicationDtl = null;
            } else {
                $custCommunicationDtl = $findCustCommunicationDtl->execute($editRow);
            }

            $viewExtendData ??= null;
            $viewMessage = session('viewMessage', []);
            $sessionKeyId ??= null;
            $custCommunicationDtl ??= null;
            $previous_url = null;
            $isEdit = true;

            $compact = [
                'viewExtendData',
                'editRow',
                'viewMessage',
                'sessionKeyId',
                'custCommunicationDtl',
                'hasAuthority',
                'previous_url',
                'isEdit',
            ];

            return account_view('customerHistory.gfh_1207.edit', compact($compact));
        } catch (DataNotFoundException $e) {
            // データが見つからなかった場合のエラーハンドリング
            return redirect()->back()->with([
                'messages.error' => ['message' => __('messages.error.data_not_found', ['data' => '顧客対応履歴', 'id' => $id])]
            ]);
        }
    }


    public function postEdit(
        $id,
        CustCommunicationRequest $request,
        FindCustCommunicationInterface $findCustCommunication
    ) {

        $input = $request->validated();

        try {
            $editRow = $findCustCommunication->execute($id);
            if (empty($editRow) || $id != $editRow->t_cust_communication_id) {
                return redirect(route('cc.customer-history.index'))->with([
                    'messages.error' => ['message' => __('messages.error.data_not_found', ['data' => '顧客対応履歴', 'id' => $id])]
                ]);
            }

            $encodedParams = $this->esmSessionManager->setSessionKeyName(
                config('define.cc.custCommunication_register_request'),
                config('define.session_key_id'),
                $input + [
                    't_cust_communication_id' => $editRow->t_cust_communication_id,
                    'mode' => 'edit',
                ],
            );
            return redirect()->route('cc.customer-history.notify', ['params' => $encodedParams])->withInput($input);
        } catch (DataNotFoundException $e) {
            return redirect(route('cc.customer-history.index'))->with([
                'messages.error' => ['message' => $e->getMessage()]
            ]);
        }
    }

    //顧客履歴確認画面
    public function notify(
        Request $request,
        GetItemnameTypeInterface $getItemnameType,
        GetOperatorsInterface $getOperator,
        GetPrefecturalInterface $getPrefecture,
        NotifyCustCommunicationInterface $notifyCustCommunication
    ) {

        $statusList = $getItemnameType->execute(ItemNameType::CustomerSupportStatus->value);
        $contactWayTypes = $getItemnameType->execute(ItemNameType::CustomerContact->value);
        $categoryList = $getItemnameType->execute(ItemNameType::CustomerSupportType->value);
        $salesChannel = $getItemnameType->execute(ItemNameType::SalesContact->value, DeleteFlg::Use->value, true);
        $inquiryType = $getItemnameType->execute(ItemNameType::ContactType->value, DeleteFlg::Use->value, true);
        $resStatus = $getItemnameType->execute(ItemNameType::SupportResult->value, DeleteFlg::Use->value, true);
        $operatorNameList = $getOperator->execute();
        $prefectuals = $getPrefecture->execute();

        if (isset($statusList['error']) || isset($contactWayTypes['error']) || isset($categoryList['error']) || isset($salesChannel['error']) || isset($inquiryType['error']) || isset($resStatus['error']) || isset($operatorNameList['error']) || isset($prefectuals['error'])) {
            // view 向けデータ
            $viewExtendData = [
                'statusList' => [],
                'contactWayTypes' => [],
                'categoryList' => [],
                'salesChannel' => [],
                'inquiryType' => [],
                'resStatus' => [],
                'operatorNameList' => [],
                'prefectuals' => [],
            ];
            session()->flash('messages.error', ['message' => __('messages.error.connection_error')]);
        } else {
            $viewExtendData =
                [
                    'statusList' => $statusList,
                    'contactWayTypes' => $contactWayTypes,
                    'categoryList' => $categoryList,
                    'salesChannel' => $salesChannel,
                    'inquiryType' => $inquiryType,
                    'resStatus' => $resStatus,
                    'operatorNameList' => $operatorNameList,
                    'prefectuals' => $prefectuals,
                ];
        }

        $viewExtendData ??= null;

        $previousInput = $this->esmSessionManager->getSessionKeyName(
            config('define.cc.custCommunication_register_request'),
            config('define.session_key_id'),
            $request->input('params')
        );

        if (empty($previousInput) && empty($request->old())) {
            return redirect()->route('cc.customer-history.index');
        }

        $exFillData = [];
        $custCommunication = $notifyCustCommunication->execute($previousInput, $exFillData, $previousInput['t_cust_communication_id'] ?? null);
        $editRow = $custCommunication;
        $input = $previousInput;
        $mode = $previousInput['mode'] ?? null;
        $param = $request->input('params');
        $previous_url = $previousInput['previous_url'] ?? route('cc.customer-history.index');

        $compact = [
            'viewExtendData',
            'editRow',
            'mode',
            'param',
            'previous_url',
            'input'
        ];

        return account_view('customerHistory.gfh_1207.notify', compact($compact));
    }

    // 編集画面からのPOSTリクエストを処理
    public function postNotify(
        NewNotifyCustCommRequest $request,
        StoreCustCommunicationInterface $storeCustCommunication,
        StoreCustCommunicationDtlInterface $storeCustCommunicationDtl
    ) {

        $req = $request->all();

        // キャンセルをクリックする
        $submit = $this->getSubmitName($request);
        if ($submit == self::CANCEL_BUTTON_CLICK) {
            return redirect()->route('cc.customer-history.new')
                ->withInput($req);
        }

        $input = $request->validated();

        $t_cust_communication_id = $storeCustCommunication->execute($input, [
            'm_account_id' => $this->esmSessionManager->getAccountId(),
        ]);

        if ($t_cust_communication_id) {
            $input = array_merge($input, ['t_cust_communication_id' => $t_cust_communication_id]);

            $storeCustCommunicationDtl->execute($input);
        }

        // セッション情報を削除する。
        $this->esmSessionManager->forgetSessionKeyName(
            config('define.cc.custCommunication_register_request'),
            config('define.session_key_id'),
            $request->input('params')
        );

        if (isset($req['previous_url']) && strlen($req['previous_url']) > 0) {
            return redirect(route('cc.cc-customer.info', ['id' => $req['m_cust_id']]))->with([
                'messages.info' => ['message' => __('messages.info.create_completed', ['data' => '顧客対応履歴'])]
            ]);
        } else {
            return redirect(route('cc.customer-history.new'))->with([
                'messages.info' => ['message' => __('messages.info.create_completed', ['data' => '顧客対応履歴'])]
            ]);
        }
    }


    public function putNotify(
        NewNotifyCustCommRequest $request,
        UpdateCustCommunicationInterface $updateCustCommunication,
        StoreCustCommunicationDtlInterface $storeCustCommunicationDtl
    ) {

        $req = $request->all();

        // キャンセルをクリックする
        $submit = $this->getSubmitName($request);
        if ($submit == self::CANCEL_BUTTON_CLICK) {
            if (isset($req['t_cust_communication_id']) && $req['t_cust_communication_id'] != 0) {
                return redirect()->route('cc.customer-history.edit', ['id' => $req['t_cust_communication_id']])
                    ->withInput($req);
            }
        }

        $input = $request->validated();

        $updateCustCommunication->execute($input['t_cust_communication_id'], $input, []);

        $storeCustCommunicationDtl->execute($input);

        // セッション情報を削除する。
        $this->esmSessionManager->forgetSessionKeyName(
            config('define.cc.custCommunication_register_request'),
            config('define.session_key_id'),
            $request->input('params')
        );
        return redirect()->route('cc.customer-history.edit', ['id' => $input['t_cust_communication_id']])
            ->with([
                'editRow' => $input,
                'messages.info' => ['message' => __('messages.info.update_completed', ['data' => '顧客対応履歴'])]
            ]);
    }

    public function postDelete(
        Request $request,
        DeleteCustCommunicationDtlInterface $deleteCustCommDtl,
        GetDeleteAuthorityInterface $getDeleteAuthority
    ) {

        $editRow = $request->all();

        $hasAuthority = $getDeleteAuthority->execute();
        //削除する権限がある
        if ($hasAuthority) {
            $deleteCustCommDtl->execute($editRow['t_cust_communication_dtl_id']);
            //削除しました。
            $viewMessage[] = __('messages.info.delete_completed', ['data' => $editRow['t_cust_communication_dtl_id'] . '顧客対応履歴内容']);
            return redirect()->back()->with('viewMessage', $viewMessage)->withInput($editRow);
        } //削除する権限がない
        else {
            //削除する権限がありません。
            session()->flash('messages.error', ['message' => __('messages.error.authority_error')]);
            return redirect()->back()->withInput($editRow);
        }
    }


    public function postReportOutput(
        Request $request,
        SetReportOutputBatchExecuteInterface $setReportOutputBatchExecute
    ) {

        $req = $request->only('t_cust_communication_id');

        $outputErrorResult = $setReportOutputBatchExecute->execute($req);

        if (empty($outputErrorResult)) {
            $viewMessage[] = __('messages.success.csv_output_success');
            return redirect()->back()->with('viewMessage', $viewMessage)->withInput();

        } else {
            return redirect()->back()->with([
                'messages.error' => ['message' => __('messages.error.connection_error')]
            ]);
        }

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
}
