<?php

namespace App\Http\Controllers\Customer;

use App\Enums\ItemNameType;
use App\Modules\Common\Base\GetPrefecturalInterface;
use App\Modules\Customer\Base\CustomerCsvExpBatchExecuteInterface;
use App\Modules\Customer\Base\CustomerCsvImpBatchExecuteInterface;
use App\Modules\Customer\Base\SearchCustomerInterface;
use App\Modules\Master\Base\GetItemnameTypeInterface;
use App\Services\EsmSessionManager;
use Config;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Modules\Master\Base\Enums\BatchListEnumInterface;

class CustomerListController
{
    protected $inputBatchTypeName;
    protected $outputBatchTypeName;
    protected $outputCheckKeyName = 'csv_output_check_key_id';
    protected $outputCheckColumnName = 'm_cust_id';
    protected $searchSessionName = '';

    public function __construct() {
        $batchListEnum = app(BatchListEnumInterface::class);
        $this->inputBatchTypeName = $batchListEnum::IMPCSV_CUSTOMER->value;
        $this->outputBatchTypeName = $batchListEnum::EXPCSV_CUST->value;
    }

    /**
     * get method
     */
    public function list(
        GetPrefecturalInterface $GetPrefectural,
        GetItemnameTypeInterface $GetItemnameType,
    ) {

        $searchResult = [];
        $paginator = null;
        $viewExtendData = [];
        $searchRow = [];
        $getPrefectural = [];
        $getCustomerRank = [];
        $getCustomerType = [];

        try {

            $getPrefectural = $GetPrefectural->execute();
            $getCustomerRank =  $GetItemnameType->execute(ItemNameType::CustomerRank->value);
            $getCustomerType = $GetItemnameType->execute(ItemNameType::CustomerType->value);

            if (isset($getPrefectural['error']) || isset($getCustomerRank['error']) || isset($getCustomerType['error'])) {

                $getPrefectural = [];
                $getCustomerRank = [];
                $getCustomerType = [];

                throw new \Exception(__('messages.error.connection_error'));
            }

            // Prepare view extend data
            $viewExtendData = [
                'pref' => $getPrefectural,
                'contactWayTypes' => $getCustomerRank,
                'customerType' => $getCustomerType,
                'page_list_count' => Config::get('Common.const.disp_limits'),
                'output_check_key_name' => $this->outputCheckKeyName,
                'output_check_column_name' => $this->outputCheckColumnName,
                'outputBatchTypeName' => $this->outputBatchTypeName,
                'list_sort' => [
                    'column_name' => 'm_cust_id',
                    'sorting_shift' => 'asc',
                ],
            ];

            $searchResult['search_record_count'] = 0;
            $searchResult['total_record_count'] = 0;
            $req['tel_forward_match'] = '1';
            $req['fax_forward_match'] = '1';
            // view 向け項目初期値
            $paginator ??= null;
            $searchRow = $req ??= null;

        } catch (Exception $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            $this->checkErrorException('connectionError');
        }
        return account_view('customer.gfh_1207.list', compact(
            'searchResult',
            'paginator',
            'viewExtendData',
            'searchRow',
        ));

    }
    /**
     * search process
     */
    public function postList(
        Request $request,
        EsmSessionManager $EsmSessionManager,
        GetPrefecturalInterface $GetPrefectural,
        GetItemnameTypeInterface $GetItemnameType,
        SearchCustomerInterface $SearchCustomer,
    ) {

        $searchResult = [];
        $errorResult = [];
        $paginator = null;
        $viewExtendData = [];
        $searchRow = [];
        $getPrefectural = [];
        $getCustomerRank = [];
        $getCustomerType = [];
        $req = [];
        try {

            $getPrefectural = $GetPrefectural->execute();
            $getCustomerRank =  $GetItemnameType->execute(ItemNameType::CustomerRank->value);
            $getCustomerType = $GetItemnameType->execute(ItemNameType::CustomerType->value);

            if (isset($getPrefectural['error']) || isset($getCustomerRank['error']) || isset($getCustomerType['error'])) {

                $getPrefectural = [];
                $getCustomerRank = [];
                $getCustomerType = [];

                throw new \Exception(__('messages.error.connection_error'));
            }
            // Prepare view extend data
            $viewExtendData = [
                'pref' => $getPrefectural,
                'contactWayTypes' => $getCustomerRank,
                'customerType' => $getCustomerType,
                'page_list_count' => Config::get('Common.const.disp_limits'),
                'list_sort' => [
                    'column_name' => 'm_cust_id',
                    'sorting_shift' => 'asc',
                ],
                'output_check_key_name' => $this->outputCheckKeyName,
                'output_check_column_name' => $this->outputCheckColumnName,
                'outputBatchTypeName' => $this->outputBatchTypeName,
            ];

            $req = $request->all();

            $req['m_account_id'] = $EsmSessionManager->getAccountId();

            // Prepare sorting options
            $option = [
                'should_paginate' => true,
                'limit' => $req['page_list_count'] ?? 10,
                'page' => $req['hidden_next_page_no'] ?? 1,
            ];
            $req['page_list_count'] = $option['limit'];
            $req['hidden_next_page_no'] = $option['page'];

            if (!empty($req['sorting_column']) && !empty($req['sorting_shift'])) {
                $viewExtendData['list_sort'] = [
                    'column_name' => $req['sorting_column'],
                    'sorting_shift' => $req['sorting_shift'],
                ];
                $option['sorts'][$req['sorting_column']] = $req['sorting_shift'];
            } else {
                $req['sorting_column'] = 'm_cust_id';
                $req['sorting_shift'] = 'asc';
                $option['sorts'] = $req['sorting_column'];
            }

            // Handle search processing
            $paginator = $SearchCustomer->execute($req, $option);
            if ($paginator === 'connectionError') {
                $paginator = [];
                throw new \Exception(__('messages.error.connection_error'));
            } elseif (!empty($paginator)) {
                // write the seesion : paginator data
                $sessionName = config('define.cc.search_customer_list_session');
                $sessionKeyId = config('define.cc.session_key_id');
                $sessionSearch = [];
                // session manager only accept the array
                $sessionSearch['searchPaginator'] = $paginator;
                $sessionKey = $EsmSessionManager->setSessionKeyName($sessionName, $sessionKeyId, $sessionSearch);

                $req[$sessionKeyId] = $sessionKey;

                $searchResult['search_record_count'] = count($paginator);
                $searchResult['total_record_count'] = $paginator['total'];
            }
        } catch (Exception $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            $this->checkErrorException('connectionError');

        }

        // view 向け項目初期値
        $paginator ??= null;
        $searchRow = $req;

        return account_view('customer.gfh_1207.list', compact(
            'searchResult',
            'errorResult',
            'paginator',
            'viewExtendData',
            'searchRow',
        ));
    }

    /**
     * import process
     */
    public function import(
        Request $request,
        CustomerCsvImpBatchExecuteInterface $CustomerCsvImpBatchExecute,
    ) {

        $viewMessage = [];
        $csvInputErrorResult = $CustomerCsvImpBatchExecute->execute($request, $this->inputBatchTypeName);
        if (empty($csvInputErrorResult)) {
            $viewMessage[] = __('messages.success.csv_input_success');
            return response()->json(['viewMessage' => $viewMessage, 'type' => 'success']);
        } elseif ($csvInputErrorResult === 'connectionError') {
            //for connection error
            $errorMessage[] = __('messages.error.connection_error');
            return response()->json(['viewMessage' => [$errorMessage], 'type' => 'error']);
        } else {
            //for validation error
            return response()->json(['importError' => $csvInputErrorResult, 'type' => 'importError']);
        }
    }

    /**
     * export process(normal/ bulk)
     */
    public function export(
        Request $request,
        CustomerCsvExpBatchExecuteInterface $CustomerCsvExpBatchExecute,
    ) {
        $viewMessage = [];
        $columnCheckArr = [];
        $columnCheckArr = [
            'output_check_key_name' => $this->outputCheckKeyName,
            'output_check_column_name' => $this->outputCheckColumnName,
            'outputBatchTypeName' => $this->outputBatchTypeName
        ];

        $submit = $this->getSubmitName($request->all());

        $csvOutputErrorResult = $CustomerCsvExpBatchExecute->execute($request, $submit, $columnCheckArr);

        if (empty($csvOutputErrorResult)) {
            $viewMessage[] = __('messages.success.csv_output_success');
            return response()->json(['viewMessage' => $viewMessage, 'type' => 'success']);
        } elseif ($csvOutputErrorResult === 'connectionError') {
            //for connection error
            $errorMessage[] = __('messages.error.connection_error');
            return response()->json(['viewMessage' => [$errorMessage], 'type' => 'error']);
        } else {
            //for validation error
            return response()->json(['exportError' => $csvOutputErrorResult, 'type' => 'exportError']);
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

    public function checkErrorException($results = '')
    {
        if ($results === 'connectionError') {
            session()->flash('messages.error', ['message' => __('messages.error.connection_error')]);

        }
    }
}
