<?php

namespace App\Http\Controllers\Master;

use App\Exceptions\DataNotFoundException;
use App\Http\Requests\Master\Base\EditDeliveryFeesRequest;
use App\Http\Requests\Master\Base\EditDeliveryReadtimeRequest;
use App\Http\Requests\Master\Base\EditNotifyDeliveryFeesRequest;
use App\Http\Requests\Master\Base\EditNotifyDeliveryReadtimeRequest;
use App\Http\Requests\Master\Base\EditNotifyWarehousesRequest;
use App\Http\Requests\Master\Base\EditWarehousesRequest;
use App\Http\Requests\Master\Base\NewDeliveryFeesRequest;
use App\Http\Requests\Master\Base\NewDeliveryReadtimeRequest;
use App\Http\Requests\Master\Base\NewNotifyDeliveryFeesRequest;
use App\Http\Requests\Master\Base\NewNotifyDeliveryReadtimeRequest;
use App\Http\Requests\Master\Base\NewNotifyWarehousesRequest;
use App\Http\Requests\Master\Base\NewWarehousesRequest;
use App\Http\Requests\Master\Base\WarehouseCalendarRequest;
use App\Modules\Common\Base\GetPrefecturalInterface;
use App\Modules\Master\Base\FindDeliveryFeesInterface;
use App\Modules\Master\Base\FindDeliveryReadtimeInterface;
use App\Modules\Master\Base\FindWarehousesInterface;
use App\Modules\Master\Base\GetDeliveryTypeInterface;
use App\Modules\Master\Base\NewDeliveryFeesInterface;
use App\Modules\Master\Base\NewDeliveryReadtimeInterface;
use App\Modules\Master\Base\NewWarehousesInterface;
use App\Modules\Master\Base\NotifyDeliveryFeesInterface;
use App\Modules\Master\Base\NotifyDeliveryReadtimeInterface;
use App\Modules\Master\Base\NotifyWarehousesInterface;
use App\Modules\Master\Base\SaveWarehouseCalendarInterface;
use App\Modules\Master\Base\SearchWarehouseCalendarInterface;
use App\Modules\Master\Base\SearchWarehousesInterface;
use App\Modules\Master\Base\StoreDeliveryFeesInterface;
use App\Modules\Master\Base\StoreDeliveryReadtimeInterface;
use App\Modules\Master\Base\StoreWarehousesInterface;
use App\Modules\Master\Base\UpdateDeliveryFeesInterface;
use App\Modules\Master\Base\UpdateDeliveryReadtimeInterface;
use App\Modules\Master\Base\UpdateWarehousesInterface;
use App\Services\EsmSessionManager;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class WarehousesController
{
    public const CANCEL_BUTTON_CLICK = 'cancel';
    public const EMPTY_COUNT = 0;
    public const DELETE_FLG_USE = 0;
    public const NOTIFY_METHOD_NAME = 'postORput';

    public function __construct(
        private EsmSessionManager $esmSessionManager
    ) {
    }

    /**
     * 倉庫マスタ検索画面表示
     *
     * @param Request $request
     * @param SearchWarehousesInterface $search
     * @return View
     */
    public function list(
        Request $request,
        SearchWarehousesInterface $search,
    ) {
        $viewExtendData =
            [
                'page_list_count' => Config::get('Common.const.disp_limits'),
                'list_sort' => [
                    'column_name' => 'm_warehouse_sort',
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
        $searchResult ??= [];
        $paginator ??= null;
        $viewExtendData ??= null;
        $searchRow ??= $req;

        $compact = [
            'searchResult',
            'paginator',
            'viewExtendData',
            'searchRow',
        ];
        return account_view('master.warehouses.base.list', compact($compact));
    }

    /**
     * 倉庫マスタ検索画面 検索処理
     *
     * @param Request $request
     * @param SearchWarehousesInterface $search
     * @return View
     */
    public function postList(
        Request $request,
        SearchWarehousesInterface $search
    ) {
        $viewExtendData =
            [
                'page_list_count' => Config::get('Common.const.disp_limits'),
                'list_sort' => [
                    'column_name' => 'm_warehouse_sort',
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
        $searchResult ??= [];
        $paginator ??= null;
        $viewExtendData ??= null;
        $searchRow ??= $req;

        $compact = [
            'searchResult',
            'paginator',
            'searchRow',
            'viewExtendData',
            'searchRow'
        ];

        return account_view('master.warehouses.base.list', compact($compact));
    }

    /**
     * 倉庫マスタ新規登録画面
     *
     * @param Request $request
     * @param NewWarehousesInterface $newWarehouses
     * @param NewDeliveryReadtimeInterface $newDeliveryReadtime
     * @param NewDeliveryFeesInterface $newDeliveryFees
     * @param GetPrefecturalInterface $getPrefecture
     * @param GetDeliveryTypeInterface $getDeliveryType
     * @return View
     */
    public function new(
        Request $request,
        NewWarehousesInterface $newWarehouses,
        NewDeliveryReadtimeInterface $newDeliveryReadtime,
        NewDeliveryFeesInterface $newDeliveryFees,
        GetPrefecturalInterface $getPrefecture,
        GetDeliveryTypeInterface $getDeliveryType
    ) {

        $editRow = $newWarehouses->execute();
        $editDeliveryReadtime = $newDeliveryReadtime->execute();
        $editDeliveryFees = $newDeliveryFees->execute();
        $prefecturals = $getPrefecture->execute();
        $delivery_types = $getDeliveryType->execute();

        if (isset($prefectuals['error']) || isset($delivery_types['error'])) {
            $editRow = [];
            $editDeliveryReadtime = [];
            $editDeliveryFees = [];
            $prefecturals = [];
            $delivery_types = [];
            $this->checkErrorException('connectionError');
        }

        $notify ??= 0;
        $submitValue = '確認';

        $compact = [
            'editRow',
            'editDeliveryReadtime',
            'editDeliveryFees',
            'notify',
            'prefecturals',
            'delivery_types',
            'submitValue'
        ];

        return account_view('master.warehouses.base.edit', compact($compact));
    }

    /**
     * 倉庫マスタ新規登録確認処理
     *
     * @param Request $request
     * @param NewWarehousesRequest $warehousesRequest
     * @param NewDeliveryReadtimeRequest $deliveryReadtimeRequest
     * @param NewDeliveryFeesRequest $deliveryFeesRequest
     * @return RedirectResponse
     */
    public function postNew(
        Request $request,
        NewWarehousesRequest $warehousesRequest,
        NewDeliveryReadtimeRequest $deliveryReadtimeRequest,
        NewDeliveryFeesRequest $deliveryFeesRequest
    ) {
        $inputWarehouse = $warehousesRequest->validated();
        $inputDeliveryReadtime = $deliveryReadtimeRequest->validated();
        $inputDeliveryFees = $deliveryFeesRequest->validated();
        $holidays = $request->holidays;

        $input = array_merge($inputWarehouse, $inputDeliveryReadtime, $inputDeliveryFees);

        $encodedParams = $this->esmSessionManager->setSessionKeyName(
            config('define.master.warehouses_register_request'),
            config('define.session_key_id'),
            $input + [
                'previousUrl' => route('master.warehouses.new'),
                'mode' => 'new',
                'holidays' => $holidays,
                'year' => $request->year
            ]
        );

        return redirect()->route('master.warehouses.notify', ['params' => $encodedParams])
            ->withInput($input);
    }

    /**
     * 倉庫マスタ編集画面
     *
     * @param Request $request
     * @param FindWarehousesInterface $findWarehouses
     * @param FindDeliveryReadtimeInterface $findDeliveryReadtime
     * @param FindDeliveryFeesInterface $findDeliveryFees
     * @param GetPrefecturalInterface $getPrefecture
     * @param GetDeliveryTypeInterface $getDeliveryType
     * @return View
     */
    public function edit(
        Request $request,
        FindWarehousesInterface $findWarehouses,
        FindDeliveryReadtimeInterface $findDeliveryReadtime,
        FindDeliveryFeesInterface $findDeliveryFees,
        GetPrefecturalInterface $getPrefecture,
        GetDeliveryTypeInterface $getDeliveryType
    ) {
        $input = $request->input();
        // 編集対象のデータ取得

        $prefecturals = $getPrefecture->execute();
        $delivery_types = $getDeliveryType->execute();
        $holidays = $request->holidays;

        try {
            $editRow = $findWarehouses->execute($request->route('id'));
            $editDeliveryReadtime = $findDeliveryReadtime->execute($request->route('id'));
            $editDeliveryFees = $findDeliveryFees->execute($request->route('id'));
        } catch (DataNotFoundException $e) {
            Log::error('Data Not Found Error: ' . $e->getMessage());
            $this->checkErrorException('', $e->getMessage());
            return redirect()->route('master.warehouses.list');
        } catch (\Exception $e) {
            $editRow = [];
            $editDeliveryReadtime = [];
            $editDeliveryFees = [];
            $prefecturals = [];
            $delivery_types = [];
            Log::error('Database connection error: ' . $e->getMessage());
            $this->checkErrorException('connectionError');
        }

        if (isset($prefectuals['error']) || isset($delivery_types['error'])) {
            $editRow = [];
            $editDeliveryReadtime = [];
            $editDeliveryFees = [];
            $prefecturals = [];
            $delivery_types = [];
            $this->checkErrorException('connectionError');
        }

        $notify ??= 0;
        $submitValue = '確認';

        $compact = [
            'editRow',
            'editDeliveryReadtime',
            'editDeliveryFees',
            'notify',
            'prefecturals',
            'delivery_types',
            'submitValue',
            'holidays'
        ];
        return account_view('master.warehouses.base.edit', compact($compact));
    }

    /**
     * 倉庫マスタ編集確認処理
     *
     * @param Request $request
     * @param EditWarehousesRequest $warehousesRequest
     * @param EditDeliveryReadtimeRequest $deliveryReadtimeRequest
     * @param EditDeliveryFeesRequest $deliveryFeesRequest
     * @param FindWarehousesInterface $findWarehouses
     * @return RedirectResponse
     */
    public function postEdit(
        Request $request,
        EditWarehousesRequest $warehousesRequest,
        EditDeliveryReadtimeRequest $deliveryReadtimeRequest,
        EditDeliveryFeesRequest $deliveryFeesRequest,
        FindWarehousesInterface $findWarehouses
    ) {
        $inputWarehouse = $warehousesRequest->validated();
        $inputDeliveryReadtime = $deliveryReadtimeRequest->validated();
        $inputDeliveryFees = $deliveryFeesRequest->validated();
        $holidays = $request->holidays;

        $input = array_merge($inputWarehouse, $inputDeliveryReadtime, $inputDeliveryFees);

        try {
            // 編集対象のデータ取得
            $editRow = $findWarehouses->execute($request->route('id'));
        } catch (\App\Exceptions\ModuleValidationException $e) {
            return redirect()->back()->withErrors($e->getValidationErrors());
        } catch (Exception $e) {
            return redirect()->route('master.warehouses.edit', ['id' => $request->route('id')])->withInput($input);
        }

        $encodedParams = $this->esmSessionManager->setSessionKeyName(
            config('define.master.warehouses_register_request'),
            config('define.session_key_id'),
            $input + [
                'previousUrl' => route('master.warehouses.edit', ['id' => $editRow->m_warehouses_id]),
                'mode' => 'edit',
                'holidays' => $holidays,
                'year' => $request->year
            ],
        );
        return redirect()->route('master.warehouses.notify', ['params' => $encodedParams])
            ->withInput($input);
    }

    /**
     * 倉庫マスタ確認画面
     *
     * @param Request $request
     * @param NotifyWarehousesInterface $notifyWarehouses
     * @param NotifyDeliveryReadtimeInterface $notifyDeliveryReadtime
     * @param NotifyDeliveryFeesInterface $notifyDeliveryFees
     * @param GetPrefecturalInterface $getPrefecture
     * @param GetDeliveryTypeInterface $getDeliveryType
     * @return View
     */
    public function notify(
        Request $request,
        NotifyWarehousesInterface $notifyWarehouses,
        NotifyDeliveryReadtimeInterface $notifyDeliveryReadtime,
        NotifyDeliveryFeesInterface $notifyDeliveryFees,
        GetPrefecturalInterface $getPrefecture,
        GetDeliveryTypeInterface $getDeliveryType
    ) {

        $prefecturals = $getPrefecture->execute();
        $delivery_types = $getDeliveryType->execute();


        $previousInput = $this->esmSessionManager->getSessionKeyName(
            config('define.master.warehouses_register_request'),
            config('define.session_key_id'),
            $request->input('params')
        );

        // 前画面の入力情報が取得できない場合はリダイレクト
        if (empty($previousInput) && empty($request->old())) {
            return redirect()->route('master.warehouses.list');
        }

        if ($previousInput['mode'] == 'new') {
            $submitValue = '新規登録';
        } else {
            $submitValue = '登録';
        }

        try {
            $exFillData = [];
            $warehouses = $notifyWarehouses->execute($previousInput, $exFillData, $previousInput['m_warehouses__m_warehouses_id'] ?? null);
            $deliveryReadtime = $notifyDeliveryReadtime->execute($previousInput, $exFillData, $previousInput['m_delivery_readtime_id'] ?? null);
            $deliveryFees = $notifyDeliveryFees->execute($previousInput, $exFillData, $previousInput['m_delivery_readtime_id'] ?? null);

            $editRow = $warehouses;
            $holidays = $previousInput['holidays'] ?? [];
            $year = $previousInput['year'] ?? [];
            $editDeliveryReadtime = $deliveryReadtime;
            $editDeliveryFees = $deliveryFees;
            $input = $previousInput;
            $param = $request->input('params');
            $prefecturals;
            $delivery_types;
            $notify = true;
            $mode = $previousInput['mode'] ?? null;

            $compact = [
                'input',
                'editRow',
                'holidays',
                'year',
                'editDeliveryReadtime',
                'editDeliveryFees',
                'param',
                'prefecturals',
                'delivery_types',
                'notify',
                'submitValue',
                'mode'
            ];

        } catch (Exception $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            $this->checkErrorException('connectionError');

            $editRow = [];
            $holidays = $previousInput['holidays'] ?? [];
            $year = $previousInput['year'] ?? [];
            $editDeliveryReadtime = [];
            $editDeliveryFees = [];
            $input = $previousInput;
            $param = $request->input('params');
            $prefecturals = [];
            $delivery_types = [];
            $notify = true;
            $mode = $previousInput['mode'] ?? null;

            $compact = [
                'input',
                'editRow',
                'holidays',
                'year',
                'editDeliveryReadtime',
                'editDeliveryFees',
                'param',
                'prefecturals',
                'delivery_types',
                'notify',
                'submitValue',
                'mode'
            ];

            if (old('method') == self::NOTIFY_METHOD_NAME) {
                return account_view('master.warehouses.base.edit', compact($compact));
            }

            return redirect($previousInput['previousUrl'])
                ->withInput($previousInput);
        }

        return account_view('master.warehouses.base.edit', compact($compact));
    }

    /**
     * 倉庫マスタ登録処理
     *
     * @param Request $request
     * @param NewNotifyWarehousesRequest $newNotifyWarehousesReq
     * @param NewNotifyDeliveryReadtimeRequest $newNotifyDeliveryReadtimeReq
     * @param NewNotifyDeliveryFeesRequest $newNotifyDeliveryFeesReq
     * @param WarehouseCalendarRequest $warehouseCalendarReq
     * @param StoreWarehousesInterface $storeWarehouses
     * @param StoreDeliveryReadtimeInterface $storeDeliveryReadtime
     * @param StoreDeliveryFeesInterface $storeDeliveryFees
     * @param SaveWarehouseCalendarInterface $saveWarehouseCalendar
     * @return RedirectResponse
     */
    public function postNotify(
        Request $request,
        NewNotifyWarehousesRequest $newNotifyWarehousesReq,
        NewNotifyDeliveryReadtimeRequest $newNotifyDeliveryReadtimeReq,
        NewNotifyDeliveryFeesRequest $newNotifyDeliveryFeesReq,
        WarehouseCalendarRequest $warehouseCalendarReq,
        StoreWarehousesInterface $storeWarehouses,
        StoreDeliveryReadtimeInterface $storeDeliveryReadtime,
        StoreDeliveryFeesInterface $storeDeliveryFees,
        SaveWarehouseCalendarInterface $saveWarehouseCalendar
    ) {
        $inputWarehouse = $newNotifyWarehousesReq->validated();
        $inputDeliveryReadtime = $newNotifyDeliveryReadtimeReq->validated();
        $inputDeliveryFees = $newNotifyDeliveryFeesReq->validated();
        $calendarReq = $warehouseCalendarReq->validated();

        $input = array_merge($inputWarehouse, $inputDeliveryReadtime, $inputDeliveryFees,$calendarReq);
        $input['method'] = self::NOTIFY_METHOD_NAME;

        // キャンセルをクリックする
        $submit = $this->getSubmitName($request);
        if ($submit == self::CANCEL_BUTTON_CLICK) {
            return redirect()->route('master.warehouses.new')
                ->withInput($input);
        }

        try {
            $m_warehouses_id = $storeWarehouses->execute($inputWarehouse, [
                'm_account_id' => $this->esmSessionManager->getAccountId(),
            ]);

            if ($m_warehouses_id) {
                $inputDeliveryReadtime = array_merge($inputDeliveryReadtime, ['m_warehouses_id' => $m_warehouses_id]);
                $storeDeliveryReadtime->execute($inputDeliveryReadtime);

                $inputDeliveryFees = array_merge($inputDeliveryFees, ['m_warehouses_id' => $m_warehouses_id]);
                $storeDeliveryFees->execute($inputDeliveryFees);

                $update_operator_id = $this->esmSessionManager->getOperatorId();
                $saveWarehouseCalendar->execute($calendarReq, $m_warehouses_id, $update_operator_id);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->route('master.warehouses.notify', ['params' => $request->input('params')])->withInput($input);
        }

        // セッション情報を削除する。
        $this->esmSessionManager->forgetSessionKeyName(
            config('define.master.warehouses_register_request'),
            config('define.session_key_id'),
            $newNotifyWarehousesReq->input('params')
        );

        return redirect(route('master.warehouses.new'))->with([
            'messages.info' => ['message' => __('messages.info.create_completed', ['data' => '倉庫マスタ'])]
        ]);
    }

    /**
     * 倉庫マスタ更新処理
     *
     * @param Request $request
     * @param EditNotifyWarehousesRequest $editNotifyWarehousesReq
     * @param EditNotifyDeliveryReadtimeRequest $editNotifyDeliveryReadtimeReq
     * @param EditNotifyDeliveryFeesRequest $editNotifyDeliveryFeesReq
     * @param WarehouseCalendarRequest $warehouseCalendarReq
     * @param UpdateWarehousesInterface $updateWarehouses
     * @param UpdateDeliveryReadtimeInterface $updateDeliveryReadtime
     * @param UpdateDeliveryFeesInterface $updateDeliveryFees
     * @param SaveWarehouseCalendarInterface $saveWarehouseCalendar
     * @return RedirectResponse
     */
    public function putNotify(
        Request $request,
        EditNotifyWarehousesRequest $editNotifyWarehousesReq,
        EditNotifyDeliveryReadtimeRequest $editNotifyDeliveryReadtimeReq,
        EditNotifyDeliveryFeesRequest $editNotifyDeliveryFeesReq,
        WarehouseCalendarRequest $warehouseCalendarReq,
        UpdateWarehousesInterface $updateWarehouses,
        UpdateDeliveryReadtimeInterface $updateDeliveryReadtime,
        UpdateDeliveryFeesInterface $updateDeliveryFees,
        SaveWarehouseCalendarInterface $saveWarehouseCalendar
    ) {
        $inputWarehouse = $editNotifyWarehousesReq->validated();
        $inputDeliveryReadtime = $editNotifyDeliveryReadtimeReq->validated();
        $inputDeliveryFees = $editNotifyDeliveryFeesReq->validated();
        $calendarReq = $warehouseCalendarReq->validated();

        $input = array_merge($inputWarehouse, $inputDeliveryReadtime, $inputDeliveryFees,$calendarReq);
        $input['method'] = self::NOTIFY_METHOD_NAME;

        // キャンセルをクリックする
        $submit = $this->getSubmitName($request);
        if ($submit == self::CANCEL_BUTTON_CLICK) {
            return redirect()->route('master.warehouses.edit', ['id' => $input['m_warehouses_id']])
                ->withInput($input);
        }

        try {
            $updateWarehouses->execute($input['m_warehouses_id'], $inputWarehouse, []);

            $updateDeliveryReadtime->execute($inputWarehouse['m_warehouses_id'], $inputDeliveryReadtime, []);

            $updateDeliveryFees->execute($inputWarehouse['m_warehouses_id'], $inputDeliveryFees, []);

            $update_operator_id = $this->esmSessionManager->getOperatorId();
            $saveWarehouseCalendar->execute($calendarReq, $inputWarehouse['m_warehouses_id'], $update_operator_id);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->route('master.warehouses.notify', ['params' => $request->input('params')])->withInput($input);
        }

        // セッション情報を削除する。
        $this->esmSessionManager->forgetSessionKeyName(
            config('define.master.warehouses_register_request'),
            config('define.session_key_id'),
            $editNotifyWarehousesReq->input('params')
        );

        return redirect()->route('master.warehouses.edit', ['id' => $input['m_warehouses_id']])
            ->with([
                'editRow' => $input,
                'messages.info' => ['message' => __('messages.info.update_completed', ['data' => '倉庫マスタ'])]
            ]);
    }

    /**
     * 特定の倉庫と年のカレンダーを取得
     *
     * @param Request $request
     * @param [type] $m_warehouses_id
     * @param [type] $year
     * @param integer $notify
     * @return View
     */
    public function getCalendar(Request $request, $m_warehouses_id, $year, $notify = 0)
    {

        $calendar_arr = $this->getCalendarArr($year);

        $m_warehouses_id = $m_warehouses_id;
        $notify = $notify;
        $choices = $this->getChoices($m_warehouses_id, $year, $request->all());

        return account_view("master.warehouses.base.calendar", compact('m_warehouses_id', 'year', 'notify', 'calendar_arr', 'choices'));
    }


    /**
     * 指定された年のカレンダー配列を生成
     *
     * @param [type] $year
     */
    public function getCalendarArr($year)
    {
        $calendar_arr = [];
        for ($m = 1; $m <= 12; ++$m) {
            $calendar_arr[$m] = array_fill(0, 42, '');
            $lastday = date('t', mktime(0, 0, 0, $m, 1, $year));
            $i = date('w', mktime(0, 0, 0, $m, 1, $year));
            for ($d = 1; $d <= $lastday; $i++, $d++) {
                $calendar_arr[$m][$i] = $d;
            }
        }

        return $calendar_arr;
    }

    /**
     * 休日を含むカレンダーの日付の選択肢を取得
     *
     * @param [type] $m_warehouses_id
     * @param [type] $year
     * @param [type] $request
     * @return array
     */
    public function getChoices($m_warehouses_id, $year, $request)
    {
        $searchWarehouseCalendar = app(SearchWarehouseCalendarInterface::class);

        $where = [
            'm_warehouses_id' => $m_warehouses_id,
            'calendar_year' => $year,
            'delete_flg' => self::DELETE_FLG_USE,
        ];

        [$total_record_count, $warehouseCalendar] = $searchWarehouseCalendar->execute($where);

        $choices = [];
        foreach ($warehouseCalendar as $val) {
            $choices[$val] = 1; // 各カレンダーの日付に1を割り当てる
        }

        // リクエストに休日が存在するかどうかを確認し、選択肢に追加
        if (!empty($request['holidays'])) {
            foreach ($request['holidays'] as $holiday) {
                $choices[$holiday] = 1; // 休日も選択肢に含める
            }
        }
        return $choices;
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
