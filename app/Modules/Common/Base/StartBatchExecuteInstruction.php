<?php

namespace App\Modules\Common\Base;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Common\Base\ExecuteBatchInstructionModel;
use App\Models\Master\Base\AccountModel;
use App\Enums\BatchExecuteStatusEnum;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class StartBatchExecuteInstruction implements StartBatchExecuteInstructionInterface
{
    // 現在時刻
    private $nowTime;

    public function execute(?int $batchId, ?array $options = [])
    {
        ModuleStarted::dispatch(__CLASS__, [$batchId, $options]);
        try {
            if (isset($_SERVER['argv'])) {
                $command = implode(' ', $_SERVER['argv']);
                Log::info("Batch command: " . $command);
            }

            $this->nowTime = Carbon::now();
            if (is_null($batchId)) {
                // バッチ実行指示作成
                $batchExecute = $this->createBatchJob($options);
            } else {
                // バッチ実行指示取得
                $batchExecute = $this->getBatchJob($batchId);
            }

            // 開始処理
            $batchExecute = $this->batchjobStart($batchExecute);

            ModuleCompleted::dispatch(__CLASS__, [$batchExecute->toArray()]);
            return $batchExecute;
        } catch (\Throwable $e) {
            ModuleFailed::dispatch(__CLASS__, [$batchId, $options], $e);
            throw new \Exception(__('messages.error.process_something_wrong', ['process' => 'StartBatchExecuteInstruction']), 0, $e);
        }
    }

    /**
     * バッチ実行指示作成
     */
    private function createBatchJob($options)
    {
        $m_account = AccountModel::find($options['m_account_id']);

        if (is_null($m_account)) {
            throw new \InvalidArgumentException(__('messages.error.invalid_parameter'));
        }

        // insert クエリを作成
        $insertData = $this->prepareInsertData($m_account, $options);
        // ExecuteBatchInstructionModel テーブルにinsert
        $batchExecute = new ExecuteBatchInstructionModel();
        $batchExecute->fill($insertData);
        $batchExecute->save();

        // 生成失敗
        if (is_null($batchExecute)) {
            throw new \InvalidArgumentException(__('messages.error.invalid_parameter'));
        }
        return $batchExecute;
    }

    /**
     * バッチ実行指示取得
     */
    private function getBatchJob(int $batchId): ExecuteBatchInstructionModel
    {
        // バッチ実行指示取得
        $batchExecute = ExecuteBatchInstructionModel::find($batchId);

        // 取得失敗
        if (is_null($batchExecute)) {
            throw new \InvalidArgumentException(__('messages.error.invalid_parameter'));
        }

        // 起動済
        if ($batchExecute->execute_status !== BatchExecuteStatusEnum::NOT_YET->value) {
            throw new \InvalidArgumentException(__('messages.error.batch_already_started', ['id' => $batchId]));
        }

        return $batchExecute;
    }

    /**
     * 開始処理
     */
    private function batchjobStart($batchExecute)
    {
        // 更新処理
        $batchExecute->batchjob_start_datetime = $this->nowTime->format('Y-m-d H:i:s');
        $batchExecute->update_timestamp = $this->nowTime->format('Y-m-d H:i:s.u');
        $batchExecute->execute_status = BatchExecuteStatusEnum::PROCESSING->value;

        if ($batchExecute->save()) {
            return $batchExecute;
        } else {
            throw new \Exception(__('messages.error.update_failed', ['data' => 'バッチ実行指示']));
        }
    }

    /**
     * バッチ実行指示のデータを準備する
     */
    private function prepareInsertData(AccountModel $m_account, array $options): array
    {
        return [
            'm_account_id' => $m_account->m_account_id,
            'account_cd' => $m_account->account_cd,
            'execute_batch_type' => $options['execute_batch_type'],
            'm_operators_id' => 0,
            'batchjob_create_datetime' => $this->nowTime->format('Y-m-d H:i:s'),
            'batchjob_start_datetime' => $this->nowTime->format('Y-m-d H:i:s'),
            'execute_conditions' => json_encode($options['execute_conditions'] ?? []),
            'execute_status' => BatchExecuteStatusEnum::STARTED->value,
            'entry_timestamp' => $this->nowTime->format('Y-m-d H:i:s'),
            'update_timestamp' => $this->nowTime->format('Y-m-d H:i:s'),
        ];
    }
}
