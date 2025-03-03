<?php

namespace App\Modules\Common\Base;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Common\Base\ExecuteBatchInstructionModel;
use Carbon\Carbon;

class EndBatchExecuteInstruction implements EndBatchExecuteInstructionInterface
{
    public function execute(ExecuteBatchInstructionModel $batchExecute, ?array $options = [])
    {
        ModuleStarted::dispatch(__CLASS__, [$batchExecute->toArray() ,$options]);
        try {
            if (is_null($batchExecute)) {
                throw new \InvalidArgumentException(__('messages.error.invalid_parameter'));
            }

            // 現在時刻を取得
            $nowTime = Carbon::now();

            // 更新処理
            $batchExecute->batchjob_end_datetime = $nowTime->format('Y-m-d H:i:s');
            $batchExecute->update_timestamp = $nowTime->format('Y-m-d H:i:s.u');
            // 実行結果 text
            if (isset($options['execute_result'])) {
                $batchExecute->execute_result = $options['execute_result'];
            }
            // 実行状態 0:正常, 1:異常
            if (isset($options['execute_status'])) {
                $batchExecute->execute_status = $options['execute_status'];
            }
            // 格納ファイルパス
            if (isset($options['file_path'])) {
                $batchExecute->file_path = $options['file_path'];
            }
            // エラーファイルパス
            if (isset($options['error_file_path'])) {
                $batchExecute->error_file_path = $options['error_file_path'];
            }

            if ($batchExecute->save()) {
                ModuleCompleted::dispatch(__CLASS__, [$batchExecute->toArray()]);
                return $batchExecute;
            } else {
                throw new \Exception(__('messages.error.update_failed', ['data' => 'バッチ実行指示']));
            }
        } catch (\Throwable $e) {
            ModuleFailed::dispatch(__CLASS__, [$batchExecute->toArray() ,$options], $e);
            throw new \Exception(__('messages.error.process_something_wrong', ['process' => 'EndBatchExecuteInstruction']), 0, $e);
        }
    }
}
