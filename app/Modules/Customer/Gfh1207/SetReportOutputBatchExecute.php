<?php

namespace App\Modules\Customer\Gfh1207;

use App\Modules\Common\Base\RegisterBatchExecuteInstructionInterface;
use App\Modules\Customer\Base\SetReportOutputBatchExecuteInterface;
use App\Modules\Master\Gfh1207\Enums\BatchListEnum;

class SetReportOutputBatchExecute implements SetReportOutputBatchExecuteInterface
{
    protected $registedBatchExecuteId;
    public function __construct(
        protected RegisterBatchExecuteInstructionInterface $registerBatchExecuteInstruction
    ) {
    }

    /**
     * CSV出力
     */
    public function execute($requestData)
    {
        $batchType = BatchListEnum::EXPXLSX_CUST_COMMUNICATION_DETAIL->value;
        $csvRequestRow = $this->setCsvOutputAll($requestData, $batchType);
        return $csvRequestRow;
    }

    /**
     * CSV出力キューに登録
     */
    private function setCsvOutputAll($requestData, $batchType)
    {

        $reqData = [];

        foreach ($requestData as $key => $row) {
            if (!is_null($row)) {
                $reqData[$key] = $row;
            }
        }

        $csvRequestRow = [
            'search_info' => $reqData
        ];

        // キューに登録する
        if ($this->setCsvQueue($csvRequestRow, $batchType, $requestData)) {
            return '';
        }

        return __('messages.error.order_search.failed_export_registration', ['extension' => 'CSV']);
    }


    /**
     * Register CSV Output Queue (Row-Specified)
     */
    protected function setCsvQueue(array $data, string $batchType, array $requestData): bool
    {
        // Initialize the batch execute ID
        $this->registedBatchExecuteId = 0;

        // Prepare the registration info
        $registerInfo = [
            'execute_batch_type' => $batchType,
            'execute_conditions' => $data,
        ];

        // Execute the batch instruction
        $response = $this->registerBatchExecuteInstruction->execute($registerInfo);

        // Return the result of the registration process
        if ($response['result']['status'] === 0) {
            $this->registedBatchExecuteId = $response['result']['t_execute_batch_instruction_id'];
            return true;
        }

        return false;
    }
}
