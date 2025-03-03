<?php

namespace App\Modules\Common\Base;

use App\Models\Common\Base\ExecuteBatchInstructionModel;
use App\Services\EsmSessionManager;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use App\Enums\BatchExecuteStatusEnum;
use Aws\Batch\BatchClient;
use Aws\Exception\AwsException;
use Aws\Exception\CredentialsException;
use Aws\Exception\UnresolvedEndpointException;

class RegisterBatchExecuteInstruction implements RegisterBatchExecuteInstructionInterface
{
    /**
     * ESMセッション管理クラス
     */
    protected $esmSessionManager;
    public function __construct(EsmSessionManager $esmSessionManager)
    {
        $this->esmSessionManager = $esmSessionManager;
    }

    public function execute(array $params)
    {
        try {
            // エラーハンドリング
            if (empty($params['execute_batch_type'])) {
                throw new \Exception('バッチタイプが指定されていません');
            }

            // insert クエリを作成
            $insertData = [
                'm_account_id' => $this->esmSessionManager->getAccountId(),
                'account_cd' => $this->esmSessionManager->getAccountCode(),
                'execute_batch_type' => $params['execute_batch_type'],
                'm_operators_id' => $this->esmSessionManager->getOperatorId(),
                'batchjob_create_datetime' => date('Y-m-d H:i:s'),
                'execute_conditions' => json_encode($params['execute_conditions']),
                'execute_status' => BatchExecuteStatusEnum::NOT_YET->value,
                'entry_timestamp' => date('Y-m-d H:i:s'),
                'update_timestamp' => date('Y-m-d H:i:s'),
            ];
            // ExecuteBatchInstructionModel テーブルにinsert
            try {
                $executeBatchInstructionModel = new ExecuteBatchInstructionModel();
                $executeBatchInstructionModel->fill($insertData);
                $executeBatchInstructionModel->save();

                $id = $executeBatchInstructionModel->t_execute_batch_instruction_id;

                $result = [
                    'result' => [
                        'status' => 0,
                        't_execute_batch_instruction_id' => $id,
                    ],
                ];

                // ローカル環境では、AWS Batchにジョブを送信しない
                if (config('services.batch.job_definition') == null || config('services.batch.job_queue') == null) {
                    return $result;
                }

                // AWS SDKの設定（credentialsの指定は不要）
                $client = new BatchClient([
                    'region' => config('services.batch.region'),
                    'version' => 'latest'
                ]);

                // ジョブのパラメータ
                $jobName = config('services.batch.job_name_prefix') . $params['execute_batch_type'] . '_' . $executeBatchInstructionModel->t_execute_batch_instruction_id;
                $jobQueue = config('services.batch.job_queue');
                $jobDefinition = config('services.batch.job_definition');
                $parameters = [
                    'command' => 'command:' . $params['execute_batch_type'],
                    'execute_id' => (string) $executeBatchInstructionModel->t_execute_batch_instruction_id,
                    'json_data' => json_encode($params['execute_conditions']),
                ];

                Log::info("Submit job[job name]: " . $jobName);
                Log::info("Submit job[job queue]: " . $jobQueue);
                Log::info("Submit job[job definition]: " . $jobDefinition);
                Log::info("Submit job[parameters]: " . json_encode($parameters));

                // ジョブの送信
                $jobResult = $client->submitJob([
                    'jobName' => $jobName,
                    'jobQueue' => $jobQueue,
                    'jobDefinition' => $jobDefinition,
                    'parameters' => $parameters,
                ]);

                // 結果の表示
                Log::info("Job submitted successfully. Job ID: " . $jobResult['jobId']);

                return $result;
            } catch (QueryException $e) {
                Log::error('Database connection error: ' . $e->getMessage());
                return [
                    'result' => [
                        'status' => 1,
                        'message' => 'Database connection error. Please try again later.',
                    ],
                ];
            } catch (AwsException $e) {
                // ジョブの送信処理で例外発生
                Log::error('AWS batch submitting job error: ' . $e->getMessage());
                return [
                    'result' => [
                        'status' => 1,
                        'message' => 'AWS batch submitting job error.',
                    ],
                ];
            }
        } catch (\Throwable $th) {
            $result = [
                'result' => [
                    'status' => 1,
                    'message' => $th->getMessage(),
                ],
            ];
            return $result;
        }
    }
}
