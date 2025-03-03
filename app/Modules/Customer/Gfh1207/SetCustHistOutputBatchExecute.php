<?php

namespace App\Modules\Customer\Gfh1207;

use App\Modules\Common\Base\RegisterBatchExecuteInstructionInterface;
use App\Modules\Customer\Base\SetCustHistOutputBatchExecuteInterface;
use App\Modules\Master\Gfh1207\Enums\BatchListEnum;

class SetCustHistOutputBatchExecute implements SetCustHistOutputBatchExecuteInterface
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
        $batchType = BatchListEnum::CSV_OUTPUT_NECSM0120->value;
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

        unset($reqData['_token']);
        unset($reqData['hidden_next_page_no']);
        unset($reqData['page_list_count']);
        unset($reqData['submit_csv_bulk_output']);
        unset($reqData['sorting_shift']);

        $csvRequestRow = [
            'search_info' => $reqData,
            'bulk_output_flg' => 1
        ];

        // キューに登録する
        if ($this->setCsvQueue($csvRequestRow, $batchType, $requestData)) {
            return '';
        }

        return 'CSV出力処理の登録に失敗しました。';
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
