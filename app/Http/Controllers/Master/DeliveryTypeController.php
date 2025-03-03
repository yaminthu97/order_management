<?php

namespace App\Http\Controllers\Master;

use App\Http\Requests\Master\Base\EditDeliveryTypeRequest;
use App\Http\Requests\Master\Base\NewDeliveryTypeRequest;
use App\Http\Requests\Master\Base\SearchDeliveryTypeRequest;
use App\Modules\Master\Base\DeleteYmstInterface;
use App\Modules\Master\Base\FindDeliveryTypeInterface;
use App\Modules\Master\Base\NewDeliveryTypeInterface;
use App\Modules\Master\Base\NotifyDeliveryTypeInterface;
use App\Modules\Master\Base\SaveDeliveryTypeInterface;
use App\Modules\Master\Base\SaveYmstpostInterface;
use App\Modules\Master\Base\SaveYmsttimeInterface;
use App\Modules\Master\Base\SearchDeliveryTypeInterface;
use App\Modules\Master\Base\UpdateDeliveryTypeInterface;
use App\Services\EsmSessionManager;
use Config;
use Exception;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * マスタ管理 配送方法マスタ コントローラ
 */
class DeliveryTypeController
{
    public const IN_USE_DELETE_FLAG = 0;
    public const DEFERRED_PAYMENT_DELIVERY_ID = 100;
    public const DELIVERY_TYPE_YAMATO = 100;
    public const TIME_DAT_FILENAME = 'YMSTTIME.DAT';
    public const POST_DAT_FILENAME = 'YMSTPOST.DAT';
    public const BATCH_SIZE = 1000;
    public const EXTRACTED_FOLDER_NAME = 'YTCMST';
    public const CANCEL_BUTTON_CLICK = 'cancel';
    public const NOTIFY_METHOD_NAME = 'postORput';

    protected $viewExtendData;
    protected $deleteFlg;
    protected $deliveryTypes;

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
                'column_name' => (isset($request->input()['sorting_column'])) ? $request->input()['sorting_column'] : 'm_delivery_sort',
                'sorting_shift' => (isset($request->input()['sorting_shift'])) ? $request->input()['sorting_shift'] : 'asc',
            ]
        ];

        // Map delete flags to their labels
        $this->deleteFlg = array_reduce(\App\Enums\DeleteFlg::cases(), function ($carry, $item) {
            $carry[$item->value] = $item->label();
            return $carry;
        }, []);

        // Map delivery types to their labels
        $this->deliveryTypes =  array_reduce(app(\App\Modules\Master\Base\Enums\DeliveryCompanyEnumInterface::class)::cases(), function ($carry, $item) {
            $carry[$item->value] = $item->label();
            return $carry;
        }, []);

    }
    /**
     * List Delivery Types with Pagination and Sorting
     *
     * This function retrieves a paginated and sorted list of delivery types
     * using the provided SearchDeliveryTypeInterface implementation. It applies
     * filtering based on the delete flag to exclude inactive records.
     *
     * @param Request $request The HTTP request object.
     * @param SearchDeliveryTypeInterface $searchDeliveryType The search interface for querying delivery types.
     *
     * @return \Illuminate\View\View The rendered view displaying the list of delivery types.
     *
     * @throws \Exception Logs errors if the search query fails.
     */
    public function list(
        Request $request,
        SearchDeliveryTypeInterface $searchDeliveryType,
    ) {
        $searchResult = [];
        try {
            // Execute the search query using the provided interface
            $paginator = $searchDeliveryType->execute(
                [
                    'delete_flg' => [
                        self::IN_USE_DELETE_FLAG // Filter for active records
                    ],
                ],
                [
                    'should_paginate' => true, // Enable pagination
                    'limit' => config('esm.default_page_size.master'), // Default page size
                ]
            );

            $searchResult['search_record_count'] = $paginator->count();
            $searchResult['total_record_count'] = $paginator->total();
        } catch (Exception $e) {
            // Log any errors for debugging
            Log::error('Database connection error: ' . $e->getMessage());
            $this->checkErrorException('connectionError');
        }
        $data = [
            'searchResult' => $searchResult,
            'searchRow' => [
                'delete_flg' => [self::IN_USE_DELETE_FLAG], // Filter for active records
                'page_list_count' => config('esm.default_page_size.master') // Default page size
            ],
            'deleteFlg' => $this->deleteFlg, // Delete flag mapping
            'deliveryTypes' => $this->deliveryTypes, // Delivery type mapping
            'viewExtendData' => $this->viewExtendData, // View-related configurations
            'paginator' => $paginator ?? null, // Pagination object for rendering in the view
        ];
        // Render the view with the prepared data
        return account_view('master.deliverytypes.base.list', $data);
    }

    /**
     * Handles the delivery type search request and returns a paginated list.
     *
     * This function processes search input from the request, executes a filtered
     * and paginated query using the SearchDeliveryTypeInterface, and renders
     * the results in a view.
     *
     * @param SearchDeliveryTypeRequest $request The request containing search conditions and options.
     * @param SearchDeliveryTypeInterface $searchDeliveryType The interface for executing the search query.
     *
     * @return \Illuminate\View\View The rendered view displaying the filtered list of delivery types.
     *
     * @throws \Exception Logs any errors encountered during the search operation.
     */
    public function postList(
        SearchDeliveryTypeRequest $request,
        SearchDeliveryTypeInterface $searchDeliveryType,
    ) {
        $searchResult = [];
        // Retrieve all input data from the request
        $input = $request->input();
        try {
            // Execute the search query using the provided search conditions and options
            $paginator = $searchDeliveryType->execute(
                $request->getSearchConditions(), // Retrieve search filters from request
                $request->getSearchOptions(), // Retrieve search options (pagination and sorting)
            );

            $searchResult['search_record_count'] = $paginator->count();
            $searchResult['total_record_count'] = $paginator->total();
        } catch (Exception $e) {
            // Log any errors for debugging
            Log::error('Database connection error: ' . $e->getMessage());
            $this->checkErrorException('connectionError');
        }

        $data = [
            'searchResult' => $searchResult,
            'searchRow' => $input, // Processed search results
            'deleteFlg' => $this->deleteFlg, // Delete flag mapping
            'deliveryTypes' => $this->deliveryTypes, // Delivery type mapping
            'viewExtendData' => $this->viewExtendData, // View-related configurations
            'paginator' => $paginator ?? null, // Pagination object for rendering in the view
        ];

        // Render the view with the retrieved data
        return account_view('master.deliverytypes.base.list', $data);
    }


    /**
     * Displays the form for creating a new delivery type.
     *
     * This function prepares the necessary data for rendering the "new" delivery type form,
     * including default values, mapping information, and the previous page URL for redirection.
     *
     * @param Request $request The HTTP request object.
     * @param NewDeliveryTypeInterface $newDeliveryType The interface for handling new delivery type creation.
     *
     * @return \Illuminate\View\View The rendered view for the new delivery type form.
     */
    public function new(
        Request $request
    ) {
        $data = [
            'deleteFlg' => $this->deleteFlg, // Delete flag mapping
            'deliveryTypes' => $this->deliveryTypes, // Delivery type mapping
            'records' => [
                'deferred_payment_delivery_id' => self::DEFERRED_PAYMENT_DELIVERY_ID, // Default values for a new delivery type record
            ],
            'previousUrl' => url()->previous() // Get the URL of the previous page for redirection purposes
        ];

        // Render the view with the necessary data for creating a new delivery type
        return account_view('master.deliverytypes.base.edit', $data);
    }


    /**
     * Handles the submission of a new delivery type form.
     *
     * This function processes the form data, including handling file uploads,
     * storing the file, and encoding the parameters into a session for further processing.
     * It then redirects to a notification route with the necessary data.
     *
     * @param NewDeliveryTypeRequest $request The validated request object containing the form input.
     *
     * @return \Illuminate\Http\RedirectResponse Redirect to the notify route with encoded parameters and form input.
     *
     * @throws \App\Exceptions\ModuleValidationException If validation fails, the user is redirected back to the form with error messages.
     */
    public function postNew(
        NewDeliveryTypeRequest $request
    ) {

        // Retrieve validated data from the request
        $input = $request->validated();

        try {
            // Check if a file was uploaded
            if ($request->hasFile('file')) {
                // Retrieve the uploaded file
                $file = $input['file'];
                unset($input['file']);// Remove file from input as it's not stored directly in the session

                // Store the file in the 'public/uploads' directory
                $filePath = $file->storeAs('uploads', $file->getClientOriginalName(), 'public');

                // Get the full absolute path to the uploaded file
                $fullPath = storage_path('app/public/' . $filePath);

                if (!$this->getListFilesInZip($fullPath)) {
                    $data = [
                        'records' => $input,
                        'deleteFlg' => $this->deleteFlg, // Delete flag mapping
                        'deliveryTypes' => $this->deliveryTypes, // Delivery type mapping
                        'previousUrl' => url()->previous(), // Get the URL of the previous page for redirection purposes
                        'fileErrMsg' => __('validation.in', ['attribute' => 'ファイル']),
                    ];

                    return account_view('master.deliverytypes.base.edit', $data);
                }

                // Update the input data with the file path and other file-related information
                $input['file'] = $fullPath;
                $input['file_name'] = $file->getClientOriginalName(); // set file original name
                $input['masterpack_import_datetime'] = \Carbon\Carbon::now()->format("Y/m/d H:i:s"); // set uploaded datetime
            }

            // Encode parameters and set them in the session
            $encodedParams = $this->esmSessionManager->setSessionKeyName(
                config('define.master.deliverytype_update_request'), // Session key name
                config('define.session_key_id'), // Session key ID
                $input + [
                    'previousUrl' => route('master.delivery_types.new'), // Redirect URL in case of navigation
                    'mode' => $request->input('submit'), // Operation mode (e.g., new or edit)
                ]
            );

            // Redirect to the notify route with encoded parameters and form input
            return redirect()->route('master.delivery_types.notify', ['params' => $encodedParams])
                ->withInput($input);
        } catch (\App\Exceptions\ModuleValidationException $e) {
            // Handle validation errors by redirecting back to the form with error messages
            return redirect()->back()->withErrors($e->getValidationErrors());
        }
    }


    /**
     * Displays the edit form for a specific delivery type.
     *
     * This function retrieves the delivery type data by ID, including associated
     * Seino and Yamato settings. It also ensures proper data formatting,
     * such as converting decimal values to integers and formatting timestamps.
     *
     * @param FindDeliveryTypeInterface $findDeliveryType The interface for finding delivery type records.
     * @param Request $request The HTTP request object containing the delivery type ID.
     *
     * @return \Illuminate\View\View The rendered view for editing a delivery type.
     *
     * @throws \Exception Logs the error and rethrows the exception for further handling.
     */
    public function edit(
        FindDeliveryTypeInterface $findDeliveryType,
        Request $request,
    ) {

        $data = [
            'deleteFlg' => $this->deleteFlg, // Delete flag mapping
            'deliveryTypes' => $this->deliveryTypes, // Delivery type mapping
        ];
        try {
            // Specify relationships to include during data retrieval
            $option['with'] = [
                'deliveryUniqueSettingSeino', // 配送会社固有設定-西濃運輸
                'deliveryUniqueSettingYamato' // 配送会社固有設定-ヤマト運輸
            ];

            // Retrieve the data for the specified 配送方法マスタID
            $records = $findDeliveryType->execute($request->route('id'), $option)->toArray();

            // Convert decimal values to integers for consistency
            $records['standard_fee'] = (isset($records['standard_fee'])) ? (int)$records['standard_fee'] : null; // 手数料（常温）
            $records['frozen_fee'] = (isset($records['frozen_fee'])) ? (int)$records['frozen_fee'] : null; // 手数料（冷凍）
            $records['chilled_fee'] = (isset($records['chilled_fee'])) ? (int)$records['chilled_fee'] : null; // 手数料（冷蔵）

            // Map 西濃運輸 or set default values if values is empty
            $records['m_delivery_unique_setting_seino_id'] = $records['delivery_unique_setting_seino']['m_delivery_unique_setting_seino_id'] ?? null; // 配送会社固有設定-西濃運輸ID
            $records['shipper_cd'] = $records['delivery_unique_setting_seino']['shipper_cd'] ?? null; // Retrieve 荷送人コード

            // Map ヤマト運輸 or set default values if values is empty
            $records['m_delivery_unique_setting_yamato_id'] = $records['delivery_unique_setting_yamato']['m_delivery_unique_setting_yamato_id'] ?? null; // 配送会社固有設定-ヤマト運輸ID
            $records['correct_info'] = $records['delivery_unique_setting_yamato']['correct_info'] ?? null; // Retrieve コレクトお客様情報
            $records['masterpack_import_datetime'] = (isset($records['delivery_unique_setting_yamato']['masterpack_import_datetime'])) ? \Carbon\Carbon::parse(
                $records['delivery_unique_setting_yamato']['masterpack_import_datetime']
            )->format("Y/m/d H:i:s") : null; // マスターパック取込日時

            $data = $data + [
                'records' => $records, // Delivery type data
            ];

        } catch (\App\Exceptions\DataNotFoundException $e) {
            // データが見つからなかった場合のエラーハンドリング
            Log::error('Data Not Found Error: ' .$e->getMessage());
            $this->checkErrorException('', $e->getMessage());
            return redirect()->route('master.delivery_types.list');
        } catch (Exception $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            $this->checkErrorException('connectionError');

            $data = $data + [
                'records' => ['m_delivery_types_id' => $request->route('id')],
            ];
        }
        // Redirect to the notify route with the encoded parameters and retain the input data
        return account_view('master.deliverytypes.base.edit', $data);
    }

    /**
     * Handles the submission of the edit form for a delivery type.
     *
     * This function processes the updated delivery type data, including handling file uploads,
     * retrieving the existing delivery type record, and storing the necessary data in the session.
     * It then redirects to the notify route with the encoded parameters.
     *
     * @param FindDeliveryTypeInterface $findDeliveryType Interface for retrieving the delivery type data.
     * @param EditDeliveryTypeRequest $request The validated request object containing the edited form data.
     *
     * @return \Illuminate\Http\RedirectResponse Redirect to the notify route with encoded parameters and form input.
     *
     * @throws \App\Exceptions\ModuleValidationException If validation fails, the user is redirected back to the form with error messages.
     */
    public function postEdit(
        FindDeliveryTypeInterface $findDeliveryType,
        EditDeliveryTypeRequest $request,
    ) {
        // Retrieve validated data from the request
        $input = $request->validated();

        try {
            // Check if a file was uploaded
            if ($request->hasFile('file')) {
                // Retrieve the uploaded file
                $file = $input['file'];
                unset($input['file']);// Remove file from input as it's not stored directly in the session

                // Store the file in the 'public/uploads' directory
                $filePath = $file->storeAs('uploads', $file->getClientOriginalName(), 'public');

                // Get the full absolute path to the uploaded file
                $fullPath = storage_path('app/public/' . $filePath); // Get the absolute path to the uploaded file

                if (!$this->getListFilesInZip($fullPath)) {
                    $data = [
                        'records' => $input,
                        'deleteFlg' => $this->deleteFlg, // Delete flag mapping
                        'deliveryTypes' => $this->deliveryTypes, // Delivery type mapping
                        'previousUrl' => url()->previous(), // Get the URL of the previous page for redirection purposes
                        'fileErrMsg' => __('validation.in', ['attribute' => 'ファイル']),
                    ];

                    return account_view('master.deliverytypes.base.edit', $data);
                }

                // Update the input data with the file path and other file-related information
                $input['file'] = $fullPath;
                $input['file_name'] = $file->getClientOriginalName(); // set file original name
                $input['masterpack_import_datetime'] = \Carbon\Carbon::now()->format("Y/m/d H:i:s"); // set uploaded datetime
            }

            // Specify relationships to include during data retrieval
            $option['with'] = [
                'deliveryUniqueSettingSeino', // 配送会社固有設定-西濃運輸
                'deliveryUniqueSettingYamato' // 配送会社固有設定-ヤマト運輸
            ];

            // Retrieve the data for the specified 配送方法マスタID
            $deliveryTypeData = $findDeliveryType->execute($request->route('id'), $option);

        } catch (\App\Exceptions\ModuleValidationException $e) {

            // Handle validation errors by redirecting back to the form with error messages
            return redirect()->back()->withErrors($e->getValidationErrors());
        } catch (Exception $e) {
            // Redirect to the notify route with the encoded parameters and retain the input data
            return redirect()->route('master.delivery_types.edit', ['id' => $request->route('id')])->withInput($input);
        }

        // Encode parameters and set them in the session
        $encodedParams = $this->esmSessionManager->setSessionKeyName(
            config('define.master.deliverytype_update_request'), // Session key name
            config('define.session_key_id'), // Session key ID
            $input + [
                'previousUrl' => route('master.delivery_types.edit', ['id' => $deliveryTypeData->m_delivery_types_id]), // Redirect URL
                'm_delivery_types_id' => $deliveryTypeData->m_delivery_types_id, // Current delivery type ID
                'mode' => $request->input('submit'), // Operation mode (e.g., new or edit)
            ]
        );

        // Redirect to the notify route with encoded parameters and form input
        return redirect()->route('master.delivery_types.notify', ['params' => $encodedParams])
            ->withInput($input);
    }


    /**
     * Displays the confirmation screen for delivery type changes.
     *
     * This function retrieves session data for a delivery type update, processes the
     * notification data, and prepares it for rendering in the confirmation view.
     * If no session data is found, it redirects back to the list page.
     *
     * @param Request $request The HTTP request containing session parameters.
     * @param NotifyDeliveryTypeInterface $notifyDeliveryType Interface to process the delivery type notification.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Renders the confirmation view or redirects to the list.
     */
    public function notify(
        Request $request,
        NotifyDeliveryTypeInterface $notifyDeliveryType,
    ) {

        // Retrieve session data for the previous input using session manager
        $previousInput = $this->esmSessionManager->getSessionKeyName(
            config('define.master.deliverytype_update_request'), // Session key name
            config('define.session_key_id'), // Session key ID
            $request->input('params') // Encoded session parameters from the request
        );

        // Redirect to the list view if no previous input or old input exists
        if (empty($previousInput) && empty($request->old())) {
            return redirect()->route('master.delivery_types.list');
        }

        $data = [
            'input' => $previousInput, // User input from the previous screen
            'deleteFlg' => $this->deleteFlg, // Delete flag mapping
            'deliveryTypes' => $this->deliveryTypes, // Delivery type mapping
            'param' => $request->input('params'), // Encoded session parameters
            'previousUrl' => $previousInput['previousUrl'] ?? route('sample.sample.list'), // Previous URL for navigation
            'mode' => $previousInput['mode'] ?? null, // Operation mode (e.g., new, edit)
        ];

        try {
            // 確認画面のデータ設定
            // 必要に応じてfillable外のデータを移す。
            // fillableに定義されていない項目はエラーとなる。
            $exFillData = [];

            // Process delivery type notification data
            $deliveryTypeNotifyData = $notifyDeliveryType->execute(
                $previousInput, // User input from session
                $exFillData, // Additional non-fillable data
                $previousInput['m_delivery_types_id'] ?? null // Delivery type ID
            );

            // Combine all notification data (delivery type, Seino, and Yamato settings)
            $records = $deliveryTypeNotifyData['delivery_type']
                     + $deliveryTypeNotifyData['seino']
                     + $deliveryTypeNotifyData['yamato'];

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
                return account_view('master.deliverytypes.base.notify', $data);
            }

            return redirect($previousInput['previousUrl'])
                ->withInput($previousInput);
        }

        // Render the notification view with the prepared data
        return account_view('master.deliverytypes.base.notify', $data);
    }


    /**
     * Handles the final confirmation and saving process for a new delivery type.
     *
     * This function validates the request data, retrieves session data,
     * saves delivery type details, processes Yamato-specific files if needed,
     * and clears session data before redirecting.
     *
     * @param NewDeliveryTypeRequest $request The validated request containing delivery type data.
     * @param SaveDeliveryTypeInterface $saveDeliveryType Interface to save delivery type details.
     * @param SaveYmsttimeInterface $saveYmsttime Interface to save Yamato-specific time settings.
     * @param DeleteYmstInterface $deleteYmst Interface to delete Yamato-specific time settings.
     * @param SaveYmstpostInterface $saveYmstpost Interface to save Yamato-specific post settings.
     *      *
     * @return \Illuminate\Http\RedirectResponse Redirects to the delivery types list with a success message.
    */
    public function postNotify(
        NewDeliveryTypeRequest $request,
        SaveDeliveryTypeInterface $saveDeliveryType,
        DeleteYmstInterface $deleteYmst,
        SaveYmsttimeInterface $saveYmsttime,
        SaveYmstpostInterface $saveYmstpost,
    ) {
        // Retrieve validated input data
        $input = $request->validated();
        $input['method'] = self::NOTIFY_METHOD_NAME;
        // Retrieve previous input from session
        $previousInput = $this->esmSessionManager->getSessionKeyName(
            config('define.master.deliverytype_update_request'), // Session key name
            config('define.session_key_id'), // Session key ID
            $request->input('params') // Encoded session parameters
        );

        // キャンセルをクリックする
        $submit = $this->getSubmitName($request);
        if ($submit == self::CANCEL_BUTTON_CLICK) {
            return redirect()->route('master.delivery_types.new')
                ->withInput($input);
        }

        // Retrieve the uploaded file(filepath) from the session
        $file = $previousInput['file'] ?? null;
        try {
            // Save to the DeliveryTypeModel, DeliveryUniqueSettingSeinoModel, and DeliveryUniqueSettingYamatoModel
            $saveDeliveryType->execute(
                $previousInput,
                [
                    'm_account_id' => $this->esmSessionManager->getAccountId(),
                    'm_operator_id' => $this->esmSessionManager->getOperatorId(),
                ]
            );

            // If the delivery type is Yamato and a file was uploaded, process the ZIP file
            if ($previousInput['delivery_type'] == self::DELIVERY_TYPE_YAMATO && $file !== null) {
                $this->extractYamatoZipFile($file, $saveYmsttime, $saveYmstpost, $deleteYmst);

            }
        } catch (Exception $e) {
            $this->checkErrorException('', __('messages.error.register_failed', ['data' => '配送方法']));
            Log::error('Database connection error: ' . $e->getMessage());
            // Return the notify view with the required data
            return redirect()->route('master.delivery_types.notify', ['params' => $request->input('params')])->withInput($input);

        }
        // Clear session data related to this operation
        $this->esmSessionManager->forgetSessionKeyName(
            config('define.cc.customer_register_request'), // Session key name
            config('define.session_key_id'), // Session key ID
            $request->input('params') // Encoded session parameters
        );

        // Redirect to the delivery types new form with a success message
        return redirect()->route('master.delivery_types.new')->with(['messages.info' => ['message' => __('messages.info.create_completed', ['data' => '配送方法'])]]);

    }

    /**
     * Handles the update process for an existing delivery type.
     *
     * This function validates the request data, retrieves session data,
     * updates the delivery type details, processes Yamato-specific files if needed,
     * and clears session data before redirecting.
     *
     * @param EditDeliveryTypeRequest $request The validated request containing updated delivery type data.
     * @param UpdateDeliveryTypeInterface $updateDeliveryType Interface to update delivery type details.
     * @param SaveYmsttimeInterface $saveYmsttime Interface to save Yamato-specific time settings.
     * @param DeleteYmsttimeInterface $deleteYmsttime Interface to delete Yamato-specific time settings.
     * @param SaveYmstpostInterface $saveYmstpost Interface to save Yamato-specific post settings.
     * @param DeleteYmstpostInterface $deleteYmstpost Interface to delete Yamato-specific post settings.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects to the edit page of the updated delivery type with a success message.
     */
    public function putNotify(
        EditDeliveryTypeRequest $request,
        UpdateDeliveryTypeInterface $updateDeliveryType,
        SaveYmsttimeInterface $saveYmsttime,
        DeleteYmstInterface $deleteYmst,
        SaveYmstpostInterface $saveYmstpost,
    ) {
        // Retrieve validated input data from the request
        $input = $request->validated();
        $input['method'] = self::NOTIFY_METHOD_NAME;
        // Retrieve previous input from session
        $previousInput = $this->esmSessionManager->getSessionKeyName(
            config('define.master.deliverytype_update_request'), // Session key name
            config('define.session_key_id'), // Session key ID
            $request->input('params') // Encoded session parameters
        );

        // キャンセルをクリックする
        $submit = $this->getSubmitName($request);
        if ($submit == self::CANCEL_BUTTON_CLICK) {
            return redirect()->route('master.delivery_types.edit', ['id' => $input['m_delivery_types_id']])
               ->withInput($input);
        }

        // Retrieve the uploaded file (filepath) from the session
        $file = $previousInput['file'] ?? null;
        try {
            // If the delivery type is Yamato and a file was uploaded, process the ZIP file
            if ($previousInput['delivery_type'] == self::DELIVERY_TYPE_YAMATO && $file !== null) {
                $this->extractYamatoZipFile($file, $saveYmsttime, $saveYmstpost, $deleteYmst);

            }

            // Update to the DeliveryTypeModel, DeliveryUniqueSettingSeinoModel, and DeliveryUniqueSettingYamatoModel
            $updateDeliveryType->execute(
                $previousInput,
                [
                    'm_account_id' => $this->esmSessionManager->getAccountId(),
                    'm_operator_id' => $this->esmSessionManager->getOperatorId(),
                ]
            );
        } catch (Exception $e) {
            $this->checkErrorException('', __('messages.error.update_failed', ['data' => '配送方法']));
            Log::error('Database connection error: ' . $e->getMessage());
            // Return the notify view with the required data
            return redirect()->route('master.delivery_types.notify', ['params' => $request->input('params')])->withInput($input);
        }

        // Clear session data related to this operation
        $this->esmSessionManager->forgetSessionKeyName(
            config('define.cc.customer_register_request'),
            config('define.session_key_id'),
            $request->input('params')
        );
        // Redirect to the edit form with a success message
        return redirect()->route('master.delivery_types.edit', $previousInput['m_delivery_types_id'])->with(['messages.info' => ['message' => __('messages.info.update_completed', ['data' => '配送方法'])]]);
    }

    /**
     * Extracts and processes data from a Yamato ZIP file.
     *
     * This function extracts the contents of the ZIP file, processes specific
     * data files ('YMSTTIME.DAT' and 'YMSTPOST.DAT'), and deletes the extracted files afterward.
     *
     * @param string $fullPath The absolute path to the ZIP file.
     * @param SaveYmsttimeInterface $saveYmsttime Interface for saving Yamato time data.
     * @param DeleteYmsttimeInterface $deleteYmsttime Interface for deleting Yamato time data.
     * @param SaveYmstpostInterface $saveYmstpost Interface for saving Yamato post data.
     * @param DeleteYmstpostInterface $deleteYmstpost Interface for deleting Yamato post data.
     *
     * @return bool Returns true if extraction and processing were successful, false otherwise.
     */
    public function extractYamatoZipFile($fullPath, $saveYmsttime, $saveYmstpost, $deleteYmst)
    {
        // Define the path where the contents of the ZIP file will be extracted.
        $extractPath = storage_path('app/public/uploads/extracted');

        // Create a new ZipArchive instance to handle the extraction.
        $zip = new \ZipArchive();

        // Attempt to open the ZIP file
        if ($zip->open($fullPath) === true) {
            // Extract the contents to the specified directory.
            $zip->extractTo($extractPath);

            // Close the ZIP file after extraction
            $zip->close();

            // Define paths for the 'YMSTTIME.DAT' and 'YMSTPOST.DAT' files in the extracted folder.
            $postFilePath = $extractPath . '/' . self::EXTRACTED_FOLDER_NAME . '/' . self::POST_DAT_FILENAME;
            $timeFilePath = $extractPath . '/' . self::EXTRACTED_FOLDER_NAME . '/' . self::TIME_DAT_FILENAME;

            try {
                // Execute the delete module to remove old data before inserting new records.
                $deleteYmst->execute([]);

                // If the time file exists, import the data using the provided importData functions.
                if (file_exists($timeFilePath)) {
                    // Import the time-related data.
                    $this->importData($timeFilePath, $saveYmsttime);
                }

                // If the post file exists, import the data using the provided importData functions.
                if (file_exists($postFilePath)) {
                    // Import the post-related data.
                    $this->importData($postFilePath, $saveYmstpost);
                }

                // Delete the extracted files and directories.
                Storage::disk('public')->deleteDirectory('uploads'); // Delete extracted files

                return true; // Return true to indicate that the process was successful.
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                throw $e;
            }
        } else {
            return false; // Return false if the ZIP file could not be opened.
        }
    }

    /**
     * Imports data from a specified file by processing it in batches.
     *
     * This function reads the file line by line, processes each entry based on the file type,
     * and saves the parsed data in batches to improve efficiency. Before inserting new data,
     * it deletes any existing records using the provided delete module.
     *
     * @param string $filePath The absolute path to the data file.
     * @param object $saveModule The module responsible for saving processed data.
     *
     * @return void
     */
    private function importData($filePath, $saveModule)
    {
        // Prepare the data to be used by the delete module
        $fillData = [];

        // Open the file for reading.
        $handle = fopen($filePath, 'r');

        // Check file opend or not
        if (!$handle) {
            throw new \RuntimeException("Failed to open file: $filePath");
        }

        // Define batch size for bulk inserts to optimize performance.
        $batchSize = self::BATCH_SIZE; // 一回に1000件

        // Initialize an array to collect records for batch insertion.
        $data = [];

        // Read and process the file line by line.
        while (($line = fgets($handle)) !== false) {

            // Determine the appropriate parsing method based on the file name.
            $fileType = (basename($filePath) === self::TIME_DAT_FILENAME) ? self::TIME_DAT_FILENAME : self::POST_DAT_FILENAME;

            // Parse the line accordingly.
            $data[] = $this->parseLine($line, $fileType);

            // If batch size is reached, save the batch and reset the array.
            if (count($data) >= $batchSize) {
                $saveModule->execute($data); // Save the current batch.
                $data = []; // Reset the batch storage for next batch.
            }
        }

        // If there is any remaining data after the loop, execute the save module to insert it.
        if (!empty($data)) {
            $saveModule->execute($data); // Save
        }

        // Close the file handle after reading.
        fclose($handle);
    }

    /**
     * Parses a line of data from a fixed-width text file.
     *
     * This function handles two file types:
     * - `YMSTTIME.DAT`: Contains delivery time information.
     * - `YMSTPOST.DAT`: Contains postal sorting codes.
     *
     * @param string $line A single line from the data file.
     * @param string $fileName The name of the file being processed.
     * @return array Parsed key-value pairs from the given line.
     */
    private function parseLine($line, $fileName)
    {
        // Check if the file is 'YMSTTIME.DAT' and parse the line
        if ($fileName == self::TIME_DAT_FILENAME) {
            return [
                'from_base' => substr($line, 0, 3),          // 0-3 characters (発ベース№)
                'cls_code1' => substr($line, 3, 5),        // 3-8 characters (仕分けコード)
                'reserve1' => substr($line, 8, 2),             // 8-10 characters (予約1 - 空白が2文字)
                'delivery_days' => (int) substr($line, 10, 2),    // 10-12 characters (配達日数 - converted to int)
                'delivery_time' => substr($line, 12, 2),     // 12-14 characters (配達可能時間帯)
                'apply_date' => substr($line, 14, 8),      // 14-22 characters (適用開始日付)
                'time_type' => substr($line, 22, 2),        // 22-24 characters (時間帯区分)
                'reserve2' => substr($line, 24, 8),             // 24-32 characters (予約2 - 空白が8文字)
                'update_date' => substr($line, 32, 8),            // 32-40 characters (更新日)
            ];
        } else {
            // Parse the line if the file is 'YMSTPOST.DAT'.
            return [
                'reserve1' => substr($line, 0, 1), //0-1 characters (予約1('1'が固定で入っている）)
                'zip_code' => substr($line, 1, 7), //1-7 characters (郵便番号)
                'reserve2' => substr($line, 8, 4), //8-12 characters (予約2（空白が4文字）)
                'cls_code1' => substr($line, 12, 7), //12-19 characters (仕分けコード（宅急便用）)
                'cls_code2' => substr($line, 19, 7), //19-26 characters (仕分けコード（メール便用）)
                'apply_date' => substr($line, 26, 8), //26-34 characters (適用開始日付)
                'post_type' => substr($line, 34, 2), //34-36 characters (郵便番号区分)
                'reserve3' => substr($line, 36, 6), //36-42 characters (予約3（空白が6文字）)
                'update_date' => substr($line, 42, 8), //42-50 characters (更新日)
            ];
        }
    }

    /**
     * Checks if specific files (defined in `ymstFileArray`) exist inside the ZIP file.
     *
     * This method opens the ZIP file, loops through its contents, and checks if the
     * files `TIME_DAT_FILENAME` and `POST_DAT_FILENAME` are present. If these files
     * are found, the method returns `true`. If none of these files are found, it will
     * delete extracted files from the 'uploads' directory and log an error message.
     *
     * @param string $zipPath The path to the ZIP file to be opened and checked.
     * @return bool Returns `true` if the required files are found, `false` otherwise.
     *
     * @throws \Exception Throws an exception if the ZIP file cannot be opened.
     */
    private function getListFilesInZip($zipPath)
    {
        $zip = new \ZipArchive();

        // ZIPファイルを開く
        if ($zip->open($zipPath) === true) {
            $fileNames = [];
            $ymstFileCount = 0;
            $numFiles = $zip->numFiles;
            $ymstFileArray = [
                self::TIME_DAT_FILENAME,
                self::POST_DAT_FILENAME
            ];

            // ループしてファイル名を収集する
            for ($i = 0; $i < $numFiles; $i++) {

                if (in_array(basename($zip->getNameIndex($i)), $ymstFileArray)) {
                    $ymstFileCount++;
                }
            }

            $zip->close();

            if (!($ymstFileCount > 0)) {
                // Delete the extracted files and directories.
                Storage::disk('public')->deleteDirectory('uploads'); // Delete extracted files
                Log::error(__('validation.in', ['attribute' => 'ファイル']));
                return false;
            }
        } else {
            return false; // Return false if the ZIP file could not be opened.
        }
        return true;
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
