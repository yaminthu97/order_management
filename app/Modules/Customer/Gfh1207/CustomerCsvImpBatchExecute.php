<?php

namespace App\Modules\Customer\Gfh1207;

use App\Modules\Common\Base\RegisterBatchExecuteInstructionInterface;
use App\Modules\Customer\Base\CustomerCsvImpBatchExecuteInterface;
use App\Services\EsmSessionManager;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class CustomerCsvImpBatchExecute implements CustomerCsvImpBatchExecuteInterface
{
    protected $registedBatchExecuteId;

    public function __construct(
        protected EsmSessionManager $esmSessionManager,
        protected RegisterBatchExecuteInstructionInterface $registerBatchExecuteInstruction
    ) {
    }

    /**
     * Batch processing for CSV file input.
     */
    public function execute(Request $request, string $batchType): string
    {
        try {

            // check validation csv file
            $upFile = $this->validateCsvFile($request);
            // Call to process the CSV input, assuming it returns a string result
            $result = $this->setCsvInput($request, $upFile, $batchType);
            return $result;
        } catch (Exception $e) {
            $csv_error = strpos($e->getMessage(), 'errcsv_');

            if ($csv_error !== false) {
                $str_replace = str_replace('err', '', $e->getMessage());
                $err_msg = __('validation.'.$str_replace);
                Log::error('CSV validation error: ' . $err_msg);
                return $err_msg;
            } else {
                Log::error('Database connection error: ' . $e->getMessage());
                return 'connectionError';
            }
        }
    }


    private function validateCsvFile(Request $request)
    {
        // Fetch the uploaded file
        $upFile = $request['csv_input_file'];

        // Validate if a file was uploaded
        if (is_null($upFile)) {
            throw new \Exception('errcsv_empty');
        }

        // Validate the file extension
        if (strtolower($upFile->getClientOriginalExtension()) !== 'csv') {
            throw new \Exception('errcsv_file_ext');
        }

        // File content
        if ($upFile->getSize() <= 0) {
            throw new \Exception('errcsv_file_size');
        }

        return $upFile;
    }
    /**
     * Register CSV Import Queue
     */
    private function setCsvInput(Request $requestData, UploadedFile $csvFile, string $batchType)
    {
        // Get current time
        $nowTime = Carbon::now();

        // Get the original file name
        $originalFileName = $csvFile->getClientOriginalName();

        // Create the upload file name with timestamp
        $uploadFileName = $nowTime->format('Ymdhis') . '_' . $originalFileName;

        // Get operator id
        $operatorId = $this->esmSessionManager->getOperatorId();

        // Get account id
        $accountId = $this->esmSessionManager->getAccountId();

        // Define the upload save path
        $uploadSavePath = 'csv/'.config('env.subsys_name.cc');

        // Store the file in the defined path
        $csvFile->storeAs($uploadSavePath, $uploadFileName);

        // Prepare the CSV request data for the queue
        $csvRequestRow = [
            'original_file_name' => $originalFileName,
            'upload_file_name' => $uploadFileName,
        ];

        // Upload to S3
        $csvRequestRow['aws_s3_token'] = '';

        // Initialize registered batch execute ID
        $this->registedBatchExecuteId = 0;

        // Register the CSV request in the queue
        if ($this->setCsvQueue($csvRequestRow, $batchType, $requestData)) {
            return '';
        } else {
            throw new \Exception('errcsv_imp_failed');
        }
    }

    /**
     * キュー登録処理
     */
    private function setCsvQueue($data, $batchType, $requestData)
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
            return false;
        }
    }
}
