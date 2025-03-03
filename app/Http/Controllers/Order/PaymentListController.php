<?php

namespace App\Http\Controllers\Order;

use Illuminate\Http\Request;

use App\Modules\Master\Base\Enums\BatchListEnumInterface;
use App\Services\FileUploadManager;
use App\Modules\Common\Base\RegisterBatchExecuteInstructionInterface;
use Carbon\Carbon;
use Config;

use App\Services\EsmSessionManager;
use App\Modules\Master\Base\SearchItemNameTypesInterface;
use App\Modules\Master\Base\SearchPaymentTypesInterface;
use App\Modules\Master\Base\SearchEcsInterface;
use App\Modules\Order\Base\SearchPaymentInterface;
use App\Enums\ItemNameType;
use App\Enums\DeliInstructTypeEnum;
use App\Enums\DeliDecisionTypeEnum;
use App\Enums\PaymentTypeEnum;

class PaymentListController
{
    protected $batchListEnum;
    protected $esmSessionManager;
    protected $searchPayment;
    protected $searchItemNameTypes;
    protected $searchPaymentTypes;
    protected $searchEcs;

    public function __construct(
        EsmSessionManager $esmSessionManager,
        SearchPaymentInterface $searchPayment,
        SearchItemNameTypesInterface $searchItemNameTypes,
        SearchPaymentTypesInterface $searchPaymentTypes,
        SearchEcsInterface $searchEcs
    ) {
        $this->esmSessionManager = $esmSessionManager;
        $this->searchPayment = $searchPayment;
        $this->searchItemNameTypes = $searchItemNameTypes;
        $this->searchPaymentTypes = $searchPaymentTypes;
        $this->searchEcs = $searchEcs;
        
        $this->batchListEnum = app(BatchListEnumInterface::class);
    }

    public function list(
        Request $request,
    ) {
        $req = $request->all();

        $req['payment_entry_date_from'] = Carbon::now()->format('Y-m-d');

        return account_view('order.base.payment-list', [
            'searchRow' => $req,
            'paymentSubjectList' => $this->getPaymentSubjectList(),
            'mPayTypeList' => $this->getMPayTypeList(),
            'ecsList' => $this->getEcsList(),
            'deliInstructType' => $this->getDeliInstructType(),
            'deliDecisionType' => $this->getDeliDecisionType(),
            'paymentType' => $this->getPaymentType(),
            'paginator' => null,
        ]);
    }

	public function postList(
        Request $request,
        RegisterBatchExecuteInstructionInterface $registerBatchExecute,
        FileUploadManager $fileUploadManager
    ) {
        $req = $request->all();
        $searchConditions = $req;
        $options = [
            'should_paginate' => true,
            'limit' => $req['page_list_count'] ?? config('esm.default_page_size.order'),
            'page' => $req['hidden_next_page_no'] ?? 1,
            'sorts' => [
                $req['sorting_column'] ?? 't_order_hdr_id' => $req['sorting_shift'] ?? 'desc'
            ],
        ];
        $searchConditions['m_account_id'] = $this->esmSessionManager->getAccountId();
        // 検索処理
        $paginator = $this->searchPayment->execute($searchConditions, $options);

        $viewExtendData = [
            'page_list_count' => Config::get('Common.const.disp_limits'),
            'list_sort' => [
                'column_name'   => 't_order_hdr_id',
                'sorting_shift' => 'desc',
            ],
        ];
        if (isset($req['sorting_column']) && isset($req['sorting_shift'])) {
            $viewExtendData['list_sort'] = [
                'column_name'   => $searchConditions['sorting_column'],
                'sorting_shift' => $searchConditions['sorting_shift']
            ];
        }
        $searchResult = [
            'total_record_count' => $paginator->total(),
            'search_record_count' => $paginator->currentPage(),
        ];

        // 入金取込処理
        if (isset($req['submit_csv_input'])) {
            try {
                $execute_batch_type = null;
                $is_new_batch = false;
                switch ($req['input_payment_csv_filetype']) {
                    case '1':
                        $execute_batch_type = $this->batchListEnum::IMPCSV_PAYMENT_RESULT_STDIN;
                        break;
                    case '2':
                        $execute_batch_type = $this->batchListEnum::IMPCSV_PAYMENT_RESULT_STDIN;
                        break;
                    case '3':
                        $execute_batch_type = $this->batchListEnum::IMPCSV_PAYMENT_RESULT_JNBIN;
                        break;
                    case '4':
                        $execute_batch_type = $this->batchListEnum::IMPDAT_PAYMENT_CVS;
                        $is_new_batch = true;
                        break;
                    case '5':
                        $execute_batch_type = $this->batchListEnum::IMPDAT_PAYMENT_COLLECT;
                        $is_new_batch = true;
                        break;
                    case '6':
                        $execute_batch_type = $this->batchListEnum::IMPDAT_PAYMENT_CREDIT;
                        $is_new_batch = true;
                        break;
                    default:
                        break;
                }
                if ($execute_batch_type === null) {
                    throw new \Exception(__('messages.error.order_search.select_name',['name'=>'入金取込形式']));
                }
    
                // 新バッチなら cust_payment_date 確認
                if ($is_new_batch) {
                    if (empty($req['cust_payment_date'])) {
                        throw new \Exception(__('messages.error.order_search.select_name',['name'=>'入金日']));
                    }
                }
                // ファイルチェック
                if (empty($req['csv_input_file'])) {
                    throw new \Exception(__('messages.error.order_search.select_name',['name'=>'入金ファイル']));
                }
    
                // ファイルアップロード処理
                $uploadResult = $this->fileUpload($fileUploadManager, $request->file('csv_input_file'), $is_new_batch);
    
                if ($is_new_batch) {
                    $params = [
                        'execute_batch_type' => $execute_batch_type,
                        'execute_conditions' => [
                            "original_file_name" => $uploadResult['original_file_name'],
                            "csv_fullfile_path" => $uploadResult['upload_save_path']  . '/' . $uploadResult['upload_file_name'],
                            "payment_date" => $req['cust_payment_date'],
                        ],
                    ];
                } else {
                    $params = [
                        'execute_batch_type' => $execute_batch_type,
                        'execute_conditions' => [
                            "original_file_name" => $uploadResult['original_file_name'],
                            "upload_file_name" => $uploadResult['upload_file_name'],
                            "input_payment_csv_filetype" => $req['input_payment_csv_filetype'],
                            "csv_type" => "1",
                            "aws_s3_token" => null,
                        ],
                    ];
                }
    
                $registerBatchExecute->execute($params);
    
                return redirect(route('order.payment.list'))->with([
                    'messages.info'=>['message'=>__('messages.info.create_completed',['data'=>"入金取込"])]
                ]);
            } catch (\Exception $e) {
                $message = $e->getMessage();
                return redirect(route('order.payment.list'))->with([
                    'messages.error'=>['message'=> $message]
                ]);
            }
        }

        return account_view('order.base.payment-list', [
            'searchRow' => $req,
            'paymentSubjectList' => $this->getPaymentSubjectList(),
            'mPayTypeList' => $this->getMPayTypeList(),
            'ecsList' => $this->getEcsList(),
            'deliInstructType' => $this->getDeliInstructType(),
            'deliDecisionType' => $this->getDeliDecisionType(),
            'paymentType' => $this->getPaymentType(),
            'paginator' => $paginator,
            'searchResult' => $searchResult,
            'viewExtendData' => $viewExtendData,
        ]);
    }

    // ファイルアップロード処理
    protected function fileUpload($manager, $file, $is_new_batch) {
        if ($is_new_batch) {
            $uploadSavePath = 'text/payment';
        } else {
            $uploadSavePath = 'csv/Order';
        }
        $nowTime = new Carbon();
        $originalFileName = $file->getClientOriginalName();
        $uploadFileName = $nowTime->format('Ymdhis'). '_'. $originalFileName;
        $managerresult = $manager->upload($file, $uploadSavePath, $uploadFileName);
        if ($managerresult === false) {
            throw new \Exception(__('messages.error.upload_s3_failed'));
        }
        $result['upload_save_path'] = $uploadSavePath;
        $result['upload_file_name'] = $uploadFileName;
        $result['original_file_name'] = $originalFileName;
        return $result;
    }

    // 入金科目
    private function getPaymentSubjectList() {
        return $this->searchItemNameTypes->execute([
            'm_account_id' => $this->esmSessionManager->getAccountId(),
            'm_itemname_type'=> ItemNameType::Deposit->value
        ]);
    }

    // 支払方法
    private function getMPayTypeList() {
        return $this->searchPaymentTypes->execute([
            'm_account_id' => $this->esmSessionManager->getAccountId(),
        ]);
    }

    // ECサイト
    private function getEcsList() {
        return $this->searchEcs->execute([
            'm_account_id' => $this->esmSessionManager->getAccountId(),
        ]);
    }

    // 出荷指示区分
    private function getDeliInstructType() {
        return collect(DeliInstructTypeEnum::cases())->map(fn ($type) => $type->label());
    }
    
    // 出荷確定区分
    private function getDeliDecisionType() {
        return collect(DeliDecisionTypeEnum::cases())->map(fn ($type) => $type->label());
    }
    
    // 入金区分
    private function getPaymentType() {
        return collect(PaymentTypeEnum::cases())->map(fn ($type) => $type->label());
    }
}
