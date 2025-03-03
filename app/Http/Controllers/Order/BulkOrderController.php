<?php

namespace App\Http\Controllers\Order;

use Illuminate\Http\Request;

use App\Modules\Master\Base\Enums\BatchListEnumInterface;
use App\Services\FileUploadManager;
use App\Modules\Common\Base\RegisterBatchExecuteInstructionInterface;
use Carbon\Carbon;

class BulkOrderController
{
    protected $batchListEnum;

    public function __construct() {
        $this->batchListEnum = app(BatchListEnumInterface::class);
    }

    public function list(
        Request $request,
    ) {
        $req = $request->all();

        return account_view('order.base.bulk_order', [
            'searchRow' => $req,
        ]);
    }

	public function postList(
        Request $request,
        RegisterBatchExecuteInstructionInterface $registerBatchExecute,
        FileUploadManager $fileUploadManager
    ) {
        $req = $request->all();

        try {
            $params = [
                'execute_batch_type' => $this->batchListEnum::IMPXLSX_BULK_ORDER->value,
                'execute_conditions' => [
                ],
            ];
            if (empty($req['bulk_order_file'])) {
                throw new \Exception(__('messages.error.order_search.select_name',['name'=>'受注/顧客データ']));
            }

            // ファイル拡張子確認
            $fileExtension = $request->file('bulk_order_file')->getClientOriginalExtension();
            if (!in_array($fileExtension, ['xlsx', 'xls'])) {
                throw new \Exception(__('messages.error.order_search.select_name',['name'=>'受注/顧客データ']));
            }

            // ファイルアップロード処理
            $nowTime = new Carbon();
            $originalFileName = $request->file('bulk_order_file')->getClientOriginalName();
            $uploadFileName = $nowTime->format('Ymdhis'). '_'. $originalFileName;

            $uploadSavePath = 'excel/import/' . $nowTime->format('Ymdhis');
            
            $fileUploadManager->upload($request->file('bulk_order_file'), $uploadSavePath, $uploadFileName);

            $params['execute_conditions']['upload_file_name'] = $uploadSavePath . '/' . $uploadFileName;
            $params['execute_conditions']['original_file_name'] = $uploadFileName;

            $batchExecute = $registerBatchExecute->execute($params);

            if (!isset($batchExecute['result']['t_execute_batch_instruction_id'])) {
                \Log::error("BulkOrderController error: " . print_r($batchExecute, true));
                throw new \Exception(__('messages.error.order_search.select_name',['name'=>'受注/顧客データ']));
            }

            session()->flash('messages.info', ['message' => __('messages.info.create_completed',['data'=>"大口注文バッチ"])]);
            session()->flash('messages.error', []);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            session()->flash('messages.error', ['message' => $message]);
            session()->flash('messages.info', []);
        }

        return account_view('order.base.bulk_order', [
            'searchRow' => $req,
        ]);
    }

}
