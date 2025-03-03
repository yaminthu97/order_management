<?php

namespace App\Modules\Order\Base;

use App\Modules\Common\Base\RegisterBatchExecuteInstructionInterface;
use App\Modules\Master\Base\SearchEcsInterface;
use App\Services\Esm2ApiManager;
use App\Services\EsmSessionManager;
use App\Services\FileUploadManager;
use Carbon\Carbon;

class SetInputBatchExecute implements SetInputBatchExecuteInterface
{
    /**
     * Input系バッチ実行指示に登録する際のファイル対象
     */
    protected $inputSubmitFileName = [
        'input_order_csv'				=> 'input_order_csv_file',
        'input_payment_result_csv'		=> 'input_payment_csv_file',
        'input_payment_auth_csv'		=> 'input_payment_auth_csv_file',
        'input_delivery_csv'			=> 'input_delivery_csv_file',
        'input_order_update_csv'		=> 'input_order_update_file',
        'input_ec_order_file'			=> 'input_ec_order_csv_file',
    ];

    /**
     * Input系バッチ実行指示に登録する際のバッチ実行種類
     */
    protected $inputBatchExecutingTypes = [
        'input_order_csv'				=> 'impcsv_order',
        'input_payment_result_csv'		=> 'impcsv_payment_result',
        'input_payment_auth_csv'		=> 'impcsv_payment_auth',
        'input_delivery_csv'			=> 'impcsv_delivery',
        'input_order_update_csv'		=> 'impcsv_order_update',
        'input_ec_order_file'			=> 'impcsv_ec_order',
    ];

    /**
     * Input系バッチ実行指示に登録する際のその他参照項目
     */
    protected $inputBatchTargetControl = [
        'impcsv_order'				=> ['input_order_csv_type'],
        'impcsv_payment_result'		=> ['input_payment_csv_filetype'],
        'impcsv_payment_auth'		=> ['input_payment_auth_csv_type'],
        'impcsv_delivery'			=> [],
        'impcsv_order_update'		=> [],
        'impcsv_ec_order'			=> ['input_ec_order_csv_type'],
        'impcsv_ec_order_amazon'	=> ['input_ec_order_csv_type'],
    ];

    /**
     * Input系バッチの銀行入金データのキュー名（input_payment_result_csv）変更用
     */
    protected $inputBatchExecutingTypesAdd = [
        1  => 'stdin',   // 標準形式(入金額+入金者)
        2  => 'stdin',   // 標準形式(入金額+備考の注文IDと氏名)
        3  => 'jnbin',   // ジャパンネット銀行（JNB）形式
    ];

    protected $registedBatchExecuteId;

    /**
     * ESM2.0 APIマネージャー
     */
    protected $esm2ApiManager;

    /**
     * ESMセッション管理クラス
     */
    protected $esmSessionManager;

    protected $registerBatchExecuteInstruction;
    protected $searchEcs;
    protected $fileUploadManager;

    public function __construct(
        Esm2ApiManager $esm2ApiManager,
        EsmSessionManager $esmSessionManager,
        RegisterBatchExecuteInstructionInterface $registerBatchExecuteInstruction,
        SearchEcsInterface $searchEcs,
        FileUploadManager $fileUploadManager
    ) {
        $this->esm2ApiManager = $esm2ApiManager;
        $this->esmSessionManager = $esmSessionManager;
        $this->registerBatchExecuteInstruction = $registerBatchExecuteInstruction;
        $this->searchEcs = $searchEcs;
        $this->fileUploadManager = $fileUploadManager;
    }

    /**
     * 取込系バッチの処理
     */
    public function execute($request, $submitName)
    {
        $upFile = $request->file($this->inputSubmitFileName[$submitName]);

        if(is_null($upFile)) {
            return __('messages.error.order_search.no_import_file');
        }

        $batchType = $this->inputBatchExecutingTypes[$submitName];

        $result = $this->setCsvInput($request->all(), $upFile, $batchType);

        return $result;
    }

    /**
     * CSV取込キューに登録
     */
    private function setCsvInput($requestData, $csvFile, $batchType)
    {
        if(empty($csvFile)) {
            return __('messages.error.order_search.no_import_file');
        }

        if($batchType != 'impcsv_ec_order' && strtolower($csvFile->getClientOriginalExtension()) != 'csv') {
            return __('messages.error.order_search.specify_import_file', ['extension' => 'CSV']);
        }

        $nowTime = new Carbon();

        $originalFileName = $csvFile->getClientOriginalName();

        $uploadFileName = $nowTime->format('Ymdhis'). '_'. $originalFileName;

        $uploadSavePath = 'csv/order';

        $csvFile->storeAs($uploadSavePath, $uploadFileName);

        $csvRequestRow = [
            'original_file_name' => $originalFileName,
            'upload_file_name' => $uploadFileName,
        ];

        // ECサイト受注取込の場合、ECサイト別で判断する
        if($batchType == 'impcsv_ec_order') {
            $uploadEcId = $requestData['input_ec_order_csv_type'];

            $ecRows = $this->searchEcs->execute(['delete_flg' => 0]);

            $targetEc = collect($ecRows)->filter(function ($value) use ($uploadEcId) {
                return $value['m_ecs_id'] == $uploadEcId;
            })->first();

            $batchType = config("define.input_ec_order_csv_batch.{$targetEc['m_ec_type']}");
        }

        $csvRequestExtend = $this->inputBatchTargetControl[$batchType];

        if(!empty($csvRequestExtend)) {
            foreach($csvRequestExtend as $extendColumn) {
                $csvRequestRow[$extendColumn] = $requestData[$extendColumn];
            }
        }

        $csvRequestRow['aws_s3_token'] = '';

        $this->registedBatchExecuteId = 0;

        if($batchType == 'impcsv_delivery') {
            $batchType =  'impcsv_delivery_'. $requestData['input_queue_delivery'];
        }

        // キューへの登録直前で、実行バッチ種類（execute_batch_type）=impcsv_payment_resultの場合には、
        // 銀行の形式（標準 又は ジャパンネット銀行）によって、
        // impcsv_payment_result_stdin 又は impcsv_payment_result_jnbin に変更するようした
        if($batchType == 'impcsv_payment_result') {
            $batchType = $batchType . '_' . $this->inputBatchExecutingTypesAdd[$requestData[$extendColumn]];
            $csvRequestRow['csv_type'] = $requestData[$extendColumn];
        }


        if($batchType == 'impcsv_order' && ($requestData['input_order_csv_type'] == 7)) {
            $batchType = $batchType.'_futureshop';
            $csvRequestRow['m_ec_id'] = $requestData['input_order_csv_shop'];
        }
        // キューに登録する
        if($this->setCsvQueue($csvRequestRow, $batchType, $requestData)) {
            logger('registedBatchExecuteId:'. $this->registedBatchExecuteId);
            if($this->registedBatchExecuteId > 0) {
                // $this->fileUploadManager を利用したファイルアップロード処理
                $this->fileUploadManager->upload($csvFile, $uploadSavePath, $uploadFileName);
            }

            return '';
        }

        return __('messages.error.order_search.failed_import_registration', ['extension' => 'CSV']);
    }

    /**
     * キュー登録処理
     */
    protected function setCsvQueue($data, $batchType, $requestData)
    {
        $this->registedBatchExecuteId = 0;

        $nowTime = new Carbon();

        // バッチ登録の処理を行う
        $registerInfo = [
            'execute_batch_type' => $batchType,
            'batchjob_create_datetime' => $nowTime->format('Y-m-d H:i:s'),
            'execute_conditions' => $data,
            '_token' => $requestData['_token'],
        ];

        $response = $this->registerBatchExecuteInstruction->execute($registerInfo);

        // 登録処理の結果を返す
        if($response['result']['status'] == 0) {
            $this->registedBatchExecuteId = $response['result']['t_execute_batch_instruction_id'];
            return true;
        }

        return false;
    }
}
