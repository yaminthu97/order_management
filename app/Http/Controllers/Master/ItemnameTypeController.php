<?php

namespace App\Http\Controllers\Master;

use App\Http\Requests\Master\Base\EditItemnameTypeRequest;
use App\Http\Requests\Master\Base\EditNotifyItemnameTypeRequest;
use App\Http\Requests\Master\Base\NewItemnameTypeRequest;
use App\Http\Requests\Master\Base\NewNotifyItemnameTypeRequest;
use App\Http\Requests\Master\Base\SearchItemnameTypeRequest;
use App\Modules\Master\Base\FindItemnameTypeInterface;
use App\Modules\Master\Base\NewItemnameTypeInterface;
use App\Modules\Master\Base\NotifyItemnameTypeInterface;
use App\Modules\Master\Base\SearchItemnameTypeInterface;
use App\Modules\Master\Base\StoreItemnameTypeInterface;
use App\Modules\Master\Base\UpdateItemnameTypeInterface;
use Config;
use Exception;
use Illuminate\Http\Request;
use Log;
use App\Exceptions\DataNotFoundException;

class ItemnameTypeController
{
    protected $viewExtendData;
    protected $deleteFlg;
    protected $itemnameTypes;

    public const IN_USE_DELETE_FLAG = 0;
    public const ITEMNAME_TYPE_SORT = 100;
    public const CANCEL_BUTTON_CLICK = 'cancel';
    public const EMPTY_COUNT = 0;
    public const NOTIFY_METHOD_NAME = 'postORput';

    /**
     * Constructor for initializing EsmSessionManager dependencies and request-based configuration.
     *
     *
     * @param \App\Services\EsmSessionManager $esmSessionManager The session manager service for Esm.
     * @param \Illuminate\Http\Request $request The current HTTP request object.
     */
    public function __construct(
        private \App\Services\EsmSessionManager $esmSessionManager,
        Request $request,
    ) {
        // Initialize view data with pagination and sorting defaults.
        $this->viewExtendData = [
            'page_list_count' => Config::get('Common.const.disp_limits'), // Pagination limits configuration.
            'list_sort' => [
                // Determine sorting column and shift from the request; fall back to defaults.
                'column_name' => (isset($request->input()['sorting_column'])) ? $request->input()['sorting_column'] : 'm_itemname_type',
                'sorting_shift' => (isset($request->input()['sorting_shift'])) ? $request->input()['sorting_shift'] : 'asc',
            ]
        ];

        // Map delete flags to their labels
        $this->deleteFlg = array_reduce(\App\Enums\DeleteFlg::cases(), function ($carry, $item) {
            $carry[$item->value] = $item->label(); // Associate value with its label.
            return $carry;
        }, []);

        // Map item name types to their labels
        $this->itemnameTypes = array_reduce(\App\Enums\ItemNameType::cases(), function ($carry, $item) {
            $carry[$item->value] = $item->label(); // Associate value with its label.
            return $carry;
        }, []);
    }

    /**
     * 項目名称検索画面表示
     *
     * Handles listing item name types.
     *
     * This method executes a search query using the `SearchItemnameTypeInterface`
     * and prepares the results to be rendered in the specified view. Sorting and filtering are
     * based on predefined configuration. Errors during execution are logged for debugging purposes.
     *
     * @param \Illuminate\Http\Request $request The current HTTP request object containing user input.
     * @param SearchItemnameTypeInterface $searchItemnameType Interface to perform the search query.
     * @return \Illuminate\Contracts\View\View The rendered view with search results and related data.
     */
    public function list(
        Request $request,
        SearchItemnameTypeInterface $searchItemnameType,
    ) {

        try {
            // Define options for the search query
            $option = [
                'should_paginate' => true, // Enable pagination
                'limit' => $options['limit'] ?? config('esm.default_page_size.master'), // Default page size
            ];

            // Execute the search query using the provided interface
            $paginator = $searchItemnameType->execute(
                [
                    'delete_flg' => [
                        self::IN_USE_DELETE_FLAG // Filter for active records
                    ],
                ],
                $option,
            );

            // Prepare search results for the view
            // for sorting_column_name.blade.php
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

        $searchRow['page_list_count'] = config('esm.default_page_size.master'); // Default page size
        $data = [
            'searchForm' => [
                'delete_flg' => [self::IN_USE_DELETE_FLAG], // Filter for active records
            ],
            'searchRow' => $searchRow ?? null,
            'searchResult' => $searchResult ?? null, // Processed search results
            'deleteFlg' => $this->deleteFlg, // Delete flag mapping
            'itemnameTypes' => $this->itemnameTypes, // Item name type mapping
            'viewExtendData' => $this->viewExtendData, // View-related configurations
            'paginator' => $paginator ?? null, // Pagination object for rendering in the view
        ];
        // Render the view with the prepared data
        return account_view('master.itemname_types.base.list', $data);
    }

    /**
     * 項目名称検索画面 検索処理
     *
     * Handles the post request for listing item name types.
     *
     * This method accepts a search request and executes the search query using
     * the `SearchItemnameTypeInterface` to retrieve matching item name types.
     * If any errors occur during the search execution, they are caught and logged.
     *
     * @param SearchItemnameTypeRequest $request - The request object containing search criteria and options.
     * @param SearchItemnameTypeInterface $searchItemnameType - The interface to execute the search query.
     *
     * @return \Illuminate\View\View - The view for displaying the list of item name types with pagination and search results.
     */
    public function postList(
        SearchItemnameTypeRequest $request,
        SearchItemnameTypeInterface $searchItemnameType,
    ) {
        // Retrieve all input data from the request
        $input = $request->input();
        try {
            // Execute the search query using the provided interface
            $paginator = $searchItemnameType->execute(
                $request->getSearchConditions(), // Pass the search conditions (filters)
                $request->getSearchOptions(), // Pass the search options (pagination, sorting, ...)
            );
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
            'searchForm' => $input,  // Pass the search form input to the UI
            'deleteFlg' => $this->deleteFlg, // Delete flag mapping
            'itemnameTypes' => $this->itemnameTypes, // Item name type mapping
            'viewExtendData' => $this->viewExtendData, // View-related configurations
            'paginator' => $paginator ?? null, // Pagination object for rendering in the view
            'searchRow' => $searchRow ?? null,
            'searchResult' => $searchResult ?? null
        ];
        // Render the view with the prepared data
        return account_view('master.itemname_types.base.list', $data);
    }

    /**
     * 項目名称新規登録画面
     *
     * Handles the request to display the form for creating a new item name type.
     *
     * This method retrieves necessary data to display the form, such as existing records,
     * and returns the view for creating a new item name type. If an exception occurs,
     * it logs the error and aborts with a 500 server error.
     *
     * @param Request $request - The incoming request object.
     * @param NewItemnameTypeInterface $newItemnameType - The interface to fetch required data for the form.
     *
     * @return \Illuminate\View\View - The view for displaying the new item name type creation form.
     */
    public function new(
        Request $request,
        NewItemnameTypeInterface $newItemnameType,
    ) {

        try {
            // 画面表示のためのデータ取得
            $records = $newItemnameType->execute();

            $data = [
                'deleteFlg' => $this->deleteFlg,
                'itemnameTypes' => $this->itemnameTypes,
                'records' => $records,
                'previousUrl' => url()->previous() // Get the URL of the previous page for redirection purposes
            ];
            // Render the view with the prepared data
            return account_view('master.itemname_types.base.new', $data);
        } catch (\Exception $e) {

            // Log the exception for debugging (if required) or handle gracefully
            Log::error($e->getMessage());

            // Abort the process and return a 500 error with a descriptive message
            return abort(500, 'An error occurred while loading the page.');
        }
    }

    /**
     * 項目名称新規登録確認処理
     *
     * Handles the post request for creating a new item name type.
     *
     * This method validates the incoming request data, prepares session parameters,
     * and redirects to a notify page with the encoded parameters.
     *
     * @param NewItemnameTypeRequest $request - The validated request object containing the submitted form data.
     * @return \Illuminate\Http\RedirectResponse - Redirects to the notification page with the necessary parameters.
     */
    public function postNew(
        NewItemnameTypeRequest $request,
    ) {
        // Validate the request and get the validated input data
        $input = $request->validated();

        // Set session parameters with the input data and required fields
        $encodedParams = $this->esmSessionManager->setSessionKeyName(
            config('define.master.itemnametype_request'),
            config('define.session_key_id'),
            $input + [
                'previousUrl' => route('master.itemname_types.new'),
                'mode' => 'new',
            ]
        );

        // Redirect to the notify route with the encoded parameters, passing the input data
        return redirect()->route('master.itemname_types.notify', ['params' => $encodedParams])->withInput($input);
    }

    /**
     * 項目名称編集画面
     *
     * Handles the request to display the form for editing an existing item name type.
     *
     * This method retrieves the record to be edited based on the ID from the route,
     * and returns the view with the necessary data to fill in the edit form.
     * If any errors occur during the search execution, they are caught and logged the error.
     *
     * @param Request $request - The incoming request object containing the route parameters.
     * @param FindItemnameTypeInterface $findItemnameType - The interface for fetching the item name type to be edited.
     *
     * @return \Illuminate\View\View - The view for editing an existing item name type with pre-populated data.
     */
    public function edit(
        Request $request,
        FindItemnameTypeInterface $findItemnameType,
    ) {

        $data = [
            'deleteFlg' => $this->deleteFlg,
            'itemnameTypes' => $this->itemnameTypes,
            'previousUrl' => url()->previous(),
        ];
        try {
            // 編集対象のデータ取得
            $records = $findItemnameType->execute($request->route('id'));

            $data = $data + [
                'records' => $records,
            ];
        } catch(DataNotFoundException $e) {
            // データが見つからなかった場合のエラーハンドリング
            Log::error('Data Not Found Error: ' .$e->getMessage());
            $this->checkErrorException('', $e->getMessage());
            return redirect()->route('master.itemname_types.list');
        } catch (Exception $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            $this->checkErrorException('connectionError');

            $data = $data + [
                'records' => ['m_itemname_types_id' => $request->route('id')],
            ]; 
        }
        // Redirect to the notify route with the encoded parameters and retain the input data
        return account_view('master.itemname_types.base.edit', $data);
    }

    /**
     * 項目名称編集確認処理
     *
     * Handles the post request for editing an existing item name type.
     *
     * This method validates the incoming request data, retrieves the data of the item name type
     * to be edited based on the ID from the route, and prepares session parameters before redirecting
     * to a notify page. In case of validation errors, it redirects back to the edit form with
     * the validation errors displayed.
     *
     * @param EditItemnameTypeRequest $request - The validated request object containing the edited data.
     * @param FindItemnameTypeInterface $findItemnameType - The interface to retrieve the item name type for editing.
     *
     * @return \Illuminate\Http\RedirectResponse - Redirects to the notify page with the encoded parameters and form input.
     */
    public function postEdit(
        EditItemnameTypeRequest $request,
        FindItemnameTypeInterface $findItemnameType,
    ) {

        // Validate the incoming request and retrieve the validated input data
        $input = $request->validated();

        try {
            // 編集対象のデータ取得
            $itemTypeRecords = $findItemnameType->execute($request->route('id'));

        } catch (\App\Exceptions\ModuleValidationException $e) {
            // If validation exception occurs, redirect back with validation errors
            return redirect()->back()->withErrors($e->getValidationErrors());
        } catch (Exception $e) {
            // Redirect to the notify route with the encoded parameters and retain the input data
            return redirect()->route('master.itemname_types.edit', ['id' => $request->route('id')])->withInput($input);
        }

        // Set session parameters with the input data and required fields
        $encodedParams = $this->esmSessionManager->setSessionKeyName(
            config('define.master.itemnametype_request'),
            config('define.session_key_id'),
            $input + [
                'previousUrl' => route('master.itemname_types.edit', ['id' => $itemTypeRecords->m_itemname_types_id]),
                'm_itemname_types_id' => $itemTypeRecords->m_itemname_types_id,
                'mode' => $request->input('submit') ? 'edit' : '',
            ],
        );

        // Redirect to the notify route with the encoded parameters and retain the input data for repopulation if needed
        return redirect()->route('master.itemname_types.notify', ['params' => $encodedParams])->withInput($input);
    }

    /**
     * 項目名称確認画面
     *
     * Handles the display of the notify page after editing/creating an item name type.
     *
     * This method retrieves session data for the previous input (submitted form data) and validates
     * whether the required input is available. If not, it redirects to the item name types list page.
     * The function then prepares the data for the confirmation view and displays the notification page
     * where the user can confirm their edits.
     *
     * @param Request $request - The incoming request object containing the parameters and session data.
     * @param NotifyItemnameTypeInterface $notifyItemnameType - The interface used to retrieve the data for the notification view.
     *
     * @return \Illuminate\View\View - The view for displaying the notification page with the provided data.
     */
    public function notify(
        Request $request,
        NotifyItemnameTypeInterface $notifyItemnameType,
    ) {
        // Retrieve the previous input data from the session using the encoded parameters from the request
        $previousInput = $this->esmSessionManager->getSessionKeyName(
            config('define.master.itemnametype_request'),
            config('define.session_key_id'),
            $request->input('params')
        );

        // 前画面の入力情報が取得できない場合はリダイレクト
        if (empty($previousInput) && empty($request->old())) {
            return redirect()->route('master.itemname_types.list');
        }

        $data = [
            'input' => $previousInput,
            'mode' => $previousInput['mode'] ?? null,
            'deleteFlg' => $this->deleteFlg,
            'itemnameTypes' => $this->itemnameTypes,
            'param' => $request->input('params'),
            'previousUrl' => $previousInput['previousUrl'] ?? route('master.itemname_types.list'),
        ];

        try {
            // 確認画面のデータ設定
            // 必要に応じてfillable外のデータを移す。
            // fillableに定義されていない項目はエラーとなる。
            $exFillData = [];
            $records = $notifyItemnameType->execute($previousInput, $exFillData, $previousInput['m_itemname_types_id'] ?? null);
            $data = $data + [
                'records' => $records ?? null,
            ];
        } catch (Exception $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            $this->checkErrorException('connectionError');

            $data = $data + [
                'records' => null,
            ];
            // Return the notify view with the required data
            if (old('method') == self::NOTIFY_METHOD_NAME) {
                return account_view('master.itemname_types.base.notify', $data);
            }

            return redirect($previousInput['previousUrl'])
                ->withInput($previousInput);
        }
        // Return the notify view with the required data
        return account_view('master.itemname_types.base.notify', $data);
    }

    /**
     * 項目名称登録処理
     *
     * Handles the form submission after reviewing the notification for creating a new item name type.
     *
     * This method validates the incoming request, stores the new item name type in the database,
     * and then deletes the session data related to the request. Finally, it redirects the user
     * to the "new" page with a success message indicating that the item name type was successfully created.
     *
     * @param NewNotifyItemnameTypeRequest $request - The validated request object containing the data to be stored.
     * @param StoreItemnameTypeInterface $storeItemnameType - The interface used to store the new item name type in the database.
     *
     * @return \Illuminate\Http\RedirectResponse - Redirects to the new item name types page with a success message.
     */
    public function postNotify(
        NewNotifyItemnameTypeRequest $request,
        StoreItemnameTypeInterface $storeItemnameType
    ) {
        // Validate the incoming request and retrieve the validated input data
        $input = $request->validated();
        $input['method'] = self::NOTIFY_METHOD_NAME;
        // キャンセルをクリックする
        $submit = $this->getSubmitName($request);
        if ($submit == self::CANCEL_BUTTON_CLICK) {
            return redirect()->route('master.itemname_types.new')
                ->withInput($input);
        }
        try {
            // Store the new item name type in the database, passing additional required data
            $records = $storeItemnameType->execute(
                $input + [
                    'entry_operator_id' => $this->esmSessionManager->getOperatorId(),
                ],
                [
                'm_account_id' => $this->esmSessionManager->getAccountId(),
            ]
            );
        } catch (Exception $e) {

            Log::error($e->getMessage());
            
            // Return the notify view with the required data
            return redirect()->route('master.itemname_types.notify', ['params' => $request->input('params')])->withInput($input);
        }
        // セッション情報を削除する。
        $this->esmSessionManager->forgetSessionKeyName(
            config('define.master.itemnametype_request'),
            config('define.session_key_id'),
            $request->input('params')
        );

        // Redirect the user to the "new" page with a success message indicating the creation was completed
        return redirect()->route('master.itemname_types.new')->with(['messages.info' => ['message' => __('messages.info.create_completed', ['data' => '項目名称'])]]);
    }

    /**
     * 項目名称更新処理
     *
     * Handles the form submission after reviewing the notification for updating an existing item name type.
     *
     * This method validates the incoming request, updates the item name type in the database,
     * deletes the session data related to the request, and then redirects the user to the edit page
     * of the updated item name type with a success message.
     *
     * @param EditNotifyItemnameTypeRequest $request - The validated request object containing the updated data.
     * @param UpdateItemnameTypeInterface $updateItemnameType - The interface used to update the item name type in the database.
     *
     * @return \Illuminate\Http\RedirectResponse - Redirects to the edit page of the updated item name type with a success message.
     */
    public function putNotify(
        EditNotifyItemnameTypeRequest $request,
        UpdateItemnameTypeInterface $updateItemnameType,
    ) {
        // Validate the incoming request and retrieve the validated input data
        $input = $request->validated();
        $input['method'] = self::NOTIFY_METHOD_NAME;
        // キャンセルをクリックする
        $submit = $this->getSubmitName($request);
        if ($submit == self::CANCEL_BUTTON_CLICK) {
            return redirect()->route('master.itemname_types.edit', ['id' => $input['m_itemname_types_id']])
                ->withInput($input);
        }
        try {
            // Update the existing item name type in the database using the validated input
            $records = $updateItemnameType->execute(
                $input['m_itemname_types_id'],
                $input + [
                    'update_operator_id' => $this->esmSessionManager->getOperatorId(),
                    'update_timestamp' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')
                ],
                []
            );
        } catch (Exception $e) {

            Log::error($e->getMessage());
           
            // Return the notify view with the required data
            return redirect()->route('master.itemname_types.notify', ['params' => $request->input('params')])->withInput($input);
        }

        // セッション情報を削除する。
        $this->esmSessionManager->forgetSessionKeyName(
            config('define.master.itemnametype_request'),
            config('define.session_key_id'),
            $request->input('params')
        );

        // Redirect the user to the edit page of the updated item name type with a success message
        return redirect()->route('master.itemname_types.edit', ['id' => $records['m_itemname_types_id']])->with(['messages.info' => ['message' => __('messages.info.update_completed', ['data' => '項目名称'])]]);
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
        }else if ($message != '') {
            session()->flash('messages.error', ['message' => __($message)]);
        }
    }
}
