<?php

namespace App\Http\Controllers\Order;

use App\Http\Requests\Order\Base\EditOrderTagMasterRequest;
use App\Http\Requests\Order\Base\NewNotifyOrderTagMasterRequest;
use App\Http\Requests\Order\Base\NewOrderTagMasterRequest;
use App\Http\Requests\Order\Base\SearchOrderTagMasterRequest;
use App\Modules\Order\Base\Enums\AndOrEnumInterface;
use App\Modules\Order\Base\Enums\AutoTimmingEnumInterface;
use App\Modules\Order\Base\Enums\FontColorEnumInterface;
use App\Modules\Order\Base\Enums\OperatorEnumInterface;
use App\Modules\Order\Base\FindOrderTagMasterInterface;
use App\Modules\Order\Base\GetOrderTagColDictInterface;
use App\Modules\Order\Base\GetOrderTagTblDictInterface;
use App\Modules\Order\Base\NewOrderTagMasterInterface;
use App\Modules\Order\Base\NotifyOrderTagMasterInterface;
use App\Modules\Order\Base\SearchOrderTagMasterModuleInterface;
use App\Modules\Order\Base\StoreOrderTagMasterInterface;
use App\Modules\Order\Base\UpdateOrderTagMasterInterface;
use Config;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderTagController
{
    protected $viewExtendData;
    protected $autoTimming;
    protected $progressType;
    protected $fontColor;
    protected $andOr;
    protected $tableId;
    protected $columnId;
    protected $operator;
    public const ORDER_TAG_CONDITION_START = 1;
    public const ORDER_TAG_CONDITION_END = 10;
    public const NOT_SET_PRGRESS_TYPE_ARR = ['-1' => '(未設定)'];
    public const EMPTY_COUNT = 0;
    public const CANCEL_BUTTON_CLICK = 'cancel';
    public const NOTIFY_METHOD_NAME = 'postORput';

    /**
     * Constructor method
     *
     * This method initializes the class instance, setting up necessary data and configurations.
     * It maps values to labels for various enums (AutoTimming, ProgressType, FontColor, AndOr, Operator),
     * prepares pagination and sorting configurations, and retrieves required data for the view.
     *
     * @param \App\Services\EsmSessionManager $esmSessionManager The Esm session manager
     * @param Request $request The HTTP request object
     * @param GetOrderTagTblDictInterface $getOrderTagTblDict Interface to get the order tag table dictionary
     * @param GetOrderTagColDictInterface $getOrderTagColDict Interface to get the order tag column dictionary
     */
    public function __construct(
        private \App\Services\EsmSessionManager $esmSessionManager,
        Request $request,
    ) {
        // Initialize view data with pagination and sorting defaults.
        $this->viewExtendData = [
            'page_list_count' => Config::get('Common.const.disp_limits'), // Pagination limits configuration
        ];

        // Map auto timming to their labels
        $this->autoTimming = array_reduce(app(AutoTimmingEnumInterface::class)::cases(), function ($carry, $item) {
            $carry[$item->value] = $item->label(); // Associate value with its label.
            return $carry;
        }, []);

        // Map progress type to their labels
        $this->progressType = self::NOT_SET_PRGRESS_TYPE_ARR + array_reduce(\App\Enums\ProgressTypeEnum::cases(), function ($carry, $item) {
            $carry[$item->value] = $item->label(); // Associate value with its label.
            return $carry;
        }, []);

        // Map font color to their labels
        $this->fontColor = array_reduce(app(FontColorEnumInterface::class)::cases(), function ($carry, $item) {
            $carry[$item->value] = $item->label(); // Associate value with its label.
            return $carry;
        }, []);

        // Map font color to their labels
        $this->andOr = array_reduce(app(AndOrEnumInterface::class)::cases(), function ($carry, $item) {
            $carry[$item->value] = $item->label(); // Associate value with its label.
            return $carry;
        }, []);

        // Map operator to their labels
        $this->operator = array_reduce(app(OperatorEnumInterface::class)::cases(), function ($carry, $item) {
            $carry[$item->value] = $item->label(); // Associate value with its label.

            return $carry;
        }, []);

    }

    /**
     * 受注タグマスタ検索
     *
     * Displays the order tag list page.
     *
     * This method prepares the necessary data and renders the order tag list view.
     * It initializes search-related variables and passes them to the view.
     *
     * @param Request $request The HTTP request object, which may contain query parameters for filtering or sorting.
     * @return \Illuminate\View\View The view rendering the order tag list page with the provided data.
     */
    public function list(
        Request $request,
    ) {

        // Prepare the data array to pass to the view
        $data = [
            'searchResult' => [],
            'paginator' => null,
            'auto_timming' => $this->autoTimming, // 自動付与タイミング
            'progress_type' => $this->progressType, // 進捗停止区分
        ];

        // Render the view 'order.base.order-tag.list' with the prepared data
        return account_view('order.base.order-tag.list', $data);
    }

    /**
     * 受注タグマスタ検索
     *
     * Handles the search request for the order tag list.
     *
     * This method processes the search request, retrieves search conditions from the request,
     * executes the search, and returns the results to the order tag list view.
     *
     * @param SearchOrderTagMasterRequest $request The request object containing search parameters.
     * @param SearchOrderTagMasterModuleInterface $searchOrderTagMaster The search module handling the query execution.
     * @return \Illuminate\View\View The view displaying the search results.
     */
    public function postList(
        SearchOrderTagMasterRequest $request,
        SearchOrderTagMasterModuleInterface $searchOrderTagMaster,
    ) {

        // Retrieve input parameters from the request
        $input = $request->input();

        try {
            // Execute the search query using the provided interface
            $paginator = $searchOrderTagMaster->execute(
                $request->getSearchConditions(), // Pass the search conditions (filters)
                $request->getSearchOptions(), // Pass the search options (pagination and sorting)
            );

            // Get record counts
            $searchResult['search_record_count'] = $paginator->count(); // Count of records on the current page
            $searchResult['total_record_count'] = $paginator->total(); // Total count of records
        } catch (Exception $e) {
            // Prepare search results for the view
            // for sorting_column_name.blade.php
            $searchResult['search_record_count'] = self::EMPTY_COUNT; // Count of records on the current page
            $searchResult['total_record_count'] = self::EMPTY_COUNT; // Total count of records
            Log::error('Database connection error: ' . $e->getMessage());
            $this->checkErrorException('connectionError');
        }
        // to use in @include('common.elements.page_list_count')
        $searchRow['page_list_count'] = $request->page_list_count;
        $data = [
            'searchForm' => $input,
            'searchResult' => $searchResult ?? null, // Search results
            'paginator' => $paginator ?? null, // Pagination data
            'auto_timming' => $this->autoTimming, // 自動付与タイミング
            'progress_type' => $this->progressType, // 進捗停止区分
            'font_color' => $this->fontColor, // 文字色
            'viewExtendData' => $this->viewExtendData, // Additional data for the view
            'searchRow' => $searchRow ?? null,
        ];

        // Render the 受注タグ list view with the search results
        return account_view('order.base.order-tag.list', $data);
    }

    /**
     * 受注タグマスタ登録
     *
     * Displays the new order tag creation page.
     *
     * This method initializes the necessary data for creating a new order tag,
     * retrieves default values using the provided service, and passes them to the view.
     *
     * @param Request $request The HTTP request object.
     * @param NewOrderTagMasterInterface $newOrderTagMaster The service for handling new order tag creation.
     * @return \Illuminate\View\View The view for creating a new order tag.
     */
    public function new(
        Request $request,
        NewOrderTagMasterInterface $newOrderTagMaster,
        GetOrderTagTblDictInterface $getOrderTagTblDict,
        GetOrderTagColDictInterface $getOrderTagColDict,
    ) {
        // Prepare data to pass to the view
        $data = [
            'auto_timming' => $this->autoTimming, // 自動付与タイミング
            'progress_type' => $this->progressType, // 進捗停止区分
            'font_color' => $this->fontColor, // 文字色
            'operator' => $this->operator, // 演算子
            'and_or' => $this->andOr, // 各条件の結合
            'order_tag_condition_start' => self::ORDER_TAG_CONDITION_START,
            'order_tag_condition_end' => self::ORDER_TAG_CONDITION_END,
            'previousUrl' => url()->previous() // The URL of the previous page for redirection
        ];
        try {
            // Execute the service to initialize a new order tag record
            $orderTagMaster = $newOrderTagMaster->execute();
            $tableId = $getOrderTagTblDict->execute()->toArray();
            $columnId = $getOrderTagColDict->execute()->toArray();
            // Prepare data to pass to the view
            $data = $data + [
                'table_id' => $tableId, // 元データ
                'column_id' => $columnId, // 項目名
                'records' => $orderTagMaster ?? null, // The retrieved 受注タグマスタ data
            ];
        } catch (Exception $e) {
            // Prepare data to pass to the view
            $data = $data + [
                'table_id' => [], // 元データ
                'column_id' => [], // 項目名
                'records' => [], // The retrieved 受注タグマスタ data
            ];

            Log::error('Database connection error: ' . $e->getMessage());
            $this->checkErrorException('connectionError');
        }
        // Render the view for creating a new order tag and pass the data
        return account_view('order.base.order-tag.new', $data);
    }

    /**
     * Handles the submission of a new order tag.
     *
     * This method validates the input data, processes it, stores session data,
     * and redirects to the notification page.
     *
     * @param NewOrderTagMasterRequest $request The request containing the new order tag details.
     * @param NewOrderTagMasterInterface $newOrderTagMaster The service responsible for handling the new order tag.
     * @return \Illuminate\Http\RedirectResponse Redirects to the notification page with session data.
     */
    public function postNew(
        NewOrderTagMasterRequest $request,
        NewOrderTagMasterInterface $newOrderTagMaster,
    ) {

        // Validate and retrieve the input data
        $input = $request->validated();

        // Store the input data in the session
        $encodedParams = $this->esmSessionManager->setSessionKeyName(
            config('define.master.ordertag_request'), // Session key
            config('define.session_key_id'), // Session key ID
            $input + [
                'previousUrl' => route('order.order-tag.new'),  // Previous URL for redirection
                'mode' => $request->input('submit'), // Mode of the request (edit or new)
            ]
        );

        // Redirect to the notification page with the stored session data
        return redirect()->route('order.order-tag.notify', ['params' => $encodedParams])->withInput($input);
    }

    /**
     * Displays the edit page for an order tag.
     *
     * This method retrieves the order tag details based on the provided ID,
     * prepares the necessary data, and passes it to the edit view.
     *
     * @param Request $request The HTTP request object containing the order tag ID.
     * @param FindOrderTagMasterInterface $findOrderTagMaster The service used to find the order tag details.
     * @return \Illuminate\View\View The view for editing an order tag.
     */
    public function edit(
        Request $request,
        FindOrderTagMasterInterface $findOrderTagMaster,
        GetOrderTagColDictInterface $getOrderTagColDict,
        GetOrderTagTblDictInterface $getOrderTagTblDict,
    ) {
        // Prepare the data to pass to the view
        $data = [
            'auto_timming' => $this->autoTimming, // 自動付与タイミング
            'progress_type' => $this->progressType, // 進捗停止区分
            'font_color' => $this->fontColor, // 文字色
            'operator' => $this->operator, // 演算子
            'and_or' => $this->andOr, // 各条件の結合
            'order_tag_condition_start' => self::ORDER_TAG_CONDITION_START,
            'order_tag_condition_end' => self::ORDER_TAG_CONDITION_END,
            'previousUrl' => url()->previous() // Get the URL of the previous page for redirection purposes
        ];
        try {

            // Retrieve the order tag details using the provided ID from the route
            $orderTagMaster = $findOrderTagMaster->execute($request->route('id'));
            $tableId = $getOrderTagTblDict->execute()->toArray();
            $columnId = $getOrderTagColDict->execute($orderTagMaster->cond1_table_id);
            $data = $data + [
                'records' => $orderTagMaster, // The retrieved 受注タグマスタ data
                'column_id' => $columnId, // 項目名
                'table_id' => $tableId, // 元データ
            ];
        } catch (\App\Exceptions\DataNotFoundException $e) {
            // データが見つからなかった場合のエラーハンドリング
            Log::error('Data Not Found Error: ' .$e->getMessage());
            $this->checkErrorException('', $e->getMessage());
            return redirect()->route('order.order-tag.list');
        } catch (Exception $e) {
            $data = $data + [
                'records' => ['m_order_tag_id' => $request->route('id')], // The retrieved 受注タグマスタ data
                'column_id' => [], // 項目名
                'table_id' => [], // 元データ
            ];

            Log::error('Database connection error: ' . $e->getMessage());
            $this->checkErrorException('connectionError');
        }

        // Render the edit view and pass the prepared data
        return account_view('order.base.order-tag.edit', $data);
    }

    /**
     * Handles the submission of an edited order tag.
     *
     * This method validates the input data, retrieves the existing order tag details,
     * stores session data, and redirects to the notification page.
     *
     * @param EditOrderTagMasterRequest $request The request containing the edited order tag details.
     * @param FindOrderTagMasterInterface $findOrderTagMaster The service used to find the existing order tag.
     * @return \Illuminate\Http\RedirectResponse Redirects to the notification page with session data.
     */
    public function postEdit(
        EditOrderTagMasterRequest $request,
        FindOrderTagMasterInterface $findOrderTagMaster,
    ) {
        // Validate and retrieve the input data
        $input = $request->validated();
        try {
            // Retrieve the existing order tag details using the provided ID from the route
            $orderTagMaster = $findOrderTagMaster->execute($request->route('id'));

            // Store the input data in the session
            $encodedParams = $this->esmSessionManager->setSessionKeyName(
                config('define.master.ordertag_request'), // Session key
                config('define.session_key_id'), // Session key ID
                $input + [
                    'previousUrl' => route('order.order-tag.edit', ['id' => $orderTagMaster->m_order_tag_id]), //Previous URL for redirection
                    'm_order_tag_id' => $orderTagMaster->m_order_tag_id, // order tag ID
                    'mode' => $request->input('submit'), // Mode of request (new or edit)
                ],
            );
        } catch (\App\Exceptions\ModuleValidationException $e) {
            // If validation exception occurs, redirect back with validation errors
            return redirect()->back()->withErrors($e->getValidationErrors());
        } catch (Exception $e) {
            // Redirect to the notify route with the encoded parameters and retain the input data
            return redirect()->route('order.order-tag.edit', ['id' => $request->route('id')])->withInput($input);
        }

        // Redirect to the notification page with the stored session data
        return redirect()->route('order.order-tag.notify', ['params' => $encodedParams])->withInput($input);
    }

    /**
     * Handles the display of the notification screen for order tag processing.
     *
     * This method retrieves session data, verifies previous input, executes the notification logic,
     * and prepares data for the view.
     *
     * @param Request $request The request containing parameters for retrieving session data.
     * @param NotifyOrderTagMasterInterface $notifyOrderTagMaster The service that processes the order tag notification.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Returns the view for the notification page or redirects to the order tag list if no data is found.
     */
    public function notify(
        Request $request,
        NotifyOrderTagMasterInterface $notifyOrderTagMaster,
        GetOrderTagColDictInterface $getOrderTagColDict,
        GetOrderTagTblDictInterface $getOrderTagTblDict,
    ) {

        // Retrieve previous input from the session using the stored key name
        $previousInput = $this->esmSessionManager->getSessionKeyName(
            config('define.master.ordertag_request'), // Session key
            config('define.session_key_id'), // Session key ID
            $request->input('params') // Parameters from the request
        );

        // If there is no previous input and no old request data, redirect to the order tag list
        if (empty($previousInput) && empty($request->old())) {
            return redirect()->route('order.order-tag.list');
        }

        // Prepare data for rendering the notification view
        $data = [
            'auto_timming' => $this->autoTimming, // 自動付与タイミング
            'progress_type' => $this->progressType, // 進捗停止区分
            'font_color' => $this->fontColor, // 文字色
            'operator' => $this->operator, // 演算子
            'and_or' => $this->andOr, // 各条件の結合
            'mode' => $previousInput['mode'] ?? null, // Mode of the request (edit or new)
            'param' => $request->input('params'), // Request parameters
            'previousUrl' => url()->previous() // Previous URL for redirection
        ];
        try {
            $tableId = $getOrderTagTblDict->execute()->toArray();
            $columnId = $getOrderTagColDict->execute()->toArray();
            // Execute the order tag notification process
            $orderTagMaster = $notifyOrderTagMaster->execute(
                $previousInput, // from session
                [],
                $previousInput['m_order_tag_id'] ?? null // Order tag ID
            );

            $data = $data + [
                'table_id' => array_flip($tableId), // 元データ
                'column_id' => array_flip($columnId), // 項目名
                'records' => $orderTagMaster, // The retrieved 受注タグマスタ data
            ];
        } catch (Exception $e) {
            $data = $data + [
                'records' => [], // The retrieved 受注タグマスタ data
                'column_id' => [], // 項目名
                'table_id' => [], // 元データ
            ];
            Log::error('Database connection error: ' . $e->getMessage());
            $this->checkErrorException('connectionError');
            // Return the notify view with the required data
            if (old('method') === self::NOTIFY_METHOD_NAME) {
                return account_view('order.base.order-tag.notify', $data);
            }
            return redirect($previousInput['previousUrl'])
                ->withInput($previousInput);
        }

        // Render the notification page with the prepared data
        return account_view('order.base.order-tag.notify', $data);
    }

    /**
     * Handles the submission of the order tag notification form and stores the data.
     *
     * This method validates the request input, processes necessary data adjustments,
     * executes the order tag storage logic, and redirects to the order tag list page with a success message.
     *
     * @param NewNotifyOrderTagMasterRequest $request The request containing validated order tag data.
     * @param StoreOrderTagMasterInterface $storeOrderTagMaster The service that processes and stores the order tag information.
     * @return \Illuminate\Http\RedirectResponse Redirects to the order tag list with a success message.
     */
    public function postNotify(
        NewNotifyOrderTagMasterRequest $request,
        StoreOrderTagMasterInterface $storeOrderTagMaster
    ) {

        // Validate input data from the request
        $input = $request->validated();
        $input['method'] = self::NOTIFY_METHOD_NAME;
        // Remove the "#" symbol from the tag color value if present
        $input['tag_color'] = Str::replace('#', '', $input['tag_color']);

        // キャンセルをクリックする
        $submit = $this->getSubmitName($request);
        if ($submit == self::CANCEL_BUTTON_CLICK) {
            return redirect()->route('order.order-tag.new')
                ->withInput($input);
        }
        try {
            // Execute the order tag process
            $records = $storeOrderTagMaster->execute(
                $input + [
                    'entry_operator_id' => $this->esmSessionManager->getOperatorId(), // Operator ID
                ],
                [
                    'm_account_id' => $this->esmSessionManager->getAccountId(), // Account ID
                ]
            );
        } catch (Exception $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            // Return the notify view with the required data
            return redirect()->route('order.order-tag.notify', ['params' => $request->input('params')])->withInput($input);

        }
        // Redirect to the order tag list page with a success message
        return redirect()->route('order.order-tag.list')->with(['messages.info' => ['message' => __('messages.info.create_completed', ['data' => '受注タグ'])]]);
        ;
    }

    /**
     * Handles the update process for an existing order tag.
     *
     * This method validates the request input, processes necessary modifications,
     * updates the order tag details, clears session data, and redirects to the order tag list page with a success message.
     *
     * @param NewNotifyOrderTagMasterRequest $request The request containing validated order tag data.
     * @param UpdateOrderTagMasterInterface $updateOrderTagMaster The service that processes and updates the order tag information.
     * @return \Illuminate\Http\RedirectResponse Redirects to the order tag list with a success message.
     */
    public function putNotify(
        NewNotifyOrderTagMasterRequest $request,
        UpdateOrderTagMasterInterface $updateOrderTagMaster
    ) {

        // Validate input data from the request
        $input = $request->validated();

        // Remove the "#" symbol from the tag color value if present
        $input['tag_color'] = Str::replace('#', '', $input['tag_color']);
        $input['method'] = self::NOTIFY_METHOD_NAME;
        // キャンセルをクリックする
        $submit = $this->getSubmitName($request);
        if ($submit == self::CANCEL_BUTTON_CLICK) {
            return redirect()->route('order.order-tag.edit', ['id' => $input['m_order_tag_id']])
                ->withInput($input);
        }
        try {
            // Execute the order tag update process
            $records = $updateOrderTagMaster->execute(
                $input['m_order_tag_id'], // Order tag ID to be updated
                $input + [
                    'update_operator_id' => $this->esmSessionManager->getOperatorId(), // Operator ID
                    'update_timestamp' => \Carbon\Carbon::now()->format('Y-m-d H:i:s'), // Updated timestamp
                ],
                []
            );
        } catch (Exception $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            // Return the notify view with the required data
            return redirect()->route('order.order-tag.notify', ['params' => $request->input('params')])->withInput($input);

        }
        // Clear the session data related to the order tag request
        $this->esmSessionManager->forgetSessionKeyName(
            config('define.master.ordertag_request'),
            config('define.session_key_id'),
            $request->input('params')
        );

        // Redirect to the order tag list page with a success message
        return redirect()->route('order.order-tag.list')->with(['messages.info' => ['message' => __('messages.info.update_completed', ['data' => '受注タグ'])]]);
    }

    public function getOrderTagColDict(
        Request $request,
        GetOrderTagColDictInterface $getOrderTagColDict
    ) {
        $getOrderTagColDict = $getOrderTagColDict->execute($request->route('table_id'));

        if (!isset($getOrderTagColDict)) {
            return response()->json(['success' => false], 500);
        }

        if (isset($getOrderTagColDict)) {

            return response()->json($getOrderTagColDict->toArray());

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
