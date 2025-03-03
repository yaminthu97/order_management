<?php

namespace App\Modules\Customer\Gfh1207;

use App\Modules\Common\Base\RegisterBatchExecuteInstructionInterface;
use App\Modules\Customer\Base\CustomerCsvExpBatchExecuteInterface;
use App\Modules\Customer\Base\SearchCustomerInterface;
use App\Services\EsmSessionManager;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerCsvExpBatchExecute implements CustomerCsvExpBatchExecuteInterface
{
    protected $registedBatchExecuteId;
    public function __construct(
        protected EsmSessionManager $esmSessionManager,
        protected RegisterBatchExecuteInstructionInterface $registerBatchExecuteInstruction,
        protected SearchCustomerInterface $searchCustomer
    ) {
        $this->searchCustomer = $searchCustomer;

    }

    /**
     * Import Batch Processing
     */
    public function execute(Request $request, string $submitName, array $viewExtendData): string
    {
        $csvRequestRow = [];
        try {

            // Extract necessary data from the view extension
            $batchType = $viewExtendData['outputBatchTypeName'] ?? null;

            if ($submitName == 'csv_bulk_output') {
                $csvRequestRow = $this->setCsvOutputAll($request, $viewExtendData);
            }

            if ($submitName == 'csv_output') {
                $csvRequestRow = $this->setCsvOutputRows($request, $viewExtendData);
            }
            // Register the queue
            if (is_array($csvRequestRow) && $this->setCsvQueue($csvRequestRow, $batchType, $request)) {
                return ''; // Successfully registered.
            } else {
                return $csvRequestRow;
            }
        } catch (Exception $e) {
            $csv_error = strpos($e->getMessage(), 'row_');
            if ($csv_error !== false) {
                $err_msg = __('validation.'.$e->getMessage());
                Log::error('CSV validation error: ' . $csv_error);
                return $err_msg;
            } else {
                Log::error('Database connection error: ' . $e->getMessage());
                return 'connectionError';
            }
        }
    }

    private function setCsvOutputRows($request, $viewExtendData)
    {
        // Extract necessary data from the view extension
        $checkKeyName = $viewExtendData['output_check_key_name'] ?? null;
        $checkColName = $viewExtendData['output_check_column_name'] ?? null;

        // Fetch the rows to check(m_cust_id)
        $checkRows = $request[$checkKeyName] ?? null;
        $errors = [];
        if (is_null($checkRows)) {
            // No rows specified for output.
            throw new \Exception('row_specified');
        }

        // Prepare the CSV request data
        $csvRequestRow = [
            'search_info' => [$checkColName => $checkRows],
            'bulk_output_flg' => 0,
        ];

        return $csvRequestRow;
    }
    /**
     * Register CSV Output Queue (Bulk)
     */
    private function setCsvOutputAll($request, $viewExtendData)
    {
        $result = $this->searchCustomer->execute($request->all(), $viewExtendData);

        // Extract the m_cust_id from the search result
        $mCustIds = [];
        foreach ($result as $row) {
            $mCustIds[] = (string) $row['m_cust_id'];
        }

        // Prepare the CSV request row
        $csvRequestRow = [
            'search_info' => ['m_cust_id' => $mCustIds],
            'bulk_output_flg' => 1,
        ];

        return $csvRequestRow;
    }

    /**
     * Register CSV Output Queue (Row-Specified)
     */
    private function setCsvQueue($data, $batchType, $request)
    {
        $this->registedBatchExecuteId = 0;
        // バッチ登録の処理を行う
        $registerInfo = [
            'execute_batch_type' => $batchType,
            'execute_conditions' => $data,
        ];

        // saving batch
        $response = $this->registerBatchExecuteInstruction->execute($registerInfo);

        // 登録処理の結果を返す
        if ($response['result']['status'] == 0) {
            $this->registedBatchExecuteId = $response['result']['t_execute_batch_instruction_id'];
            return true;
        } elseif ($response['result']['status'] == 1) {
            throw new \Exception($response['result']['message']);
        }

        return false;
    }
}
