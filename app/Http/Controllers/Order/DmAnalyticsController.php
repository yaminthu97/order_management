<?php

namespace App\Http\Controllers\Order;

use Carbon\Carbon;

use Illuminate\Http\Request;

use App\Enums\DeleteFlg;
use App\Enums\ItemNameType;
use App\Modules\Master\Base\SearchItemNameTypesInterface;
use App\Modules\Master\Base\Enums\BatchListEnumInterface;
use App\Modules\Common\Base\RegisterBatchExecuteInstructionInterface;

use App\Services\FileUploadManager;

class DmAnalyticsController
{
    protected $batchListEnum;

    public function __construct() {
        $this->batchListEnum = app(BatchListEnumInterface::class);
    }

    public function new(
        Request $request,
        SearchItemNameTypesInterface $searchItemNameTypes,
    ) {
        $req = $request->all();
        
        try {
            $orderTypes = $searchItemNameTypes->execute([
                'm_itemname_type' => ItemNameType::ReceiptType,
                'delete_flg' => DeleteFlg::Use->value
            ]);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            session()->flash('messages.error', ['message' => $message]);
            session()->flash('messages.info', []);
        }

        return account_view('order.base.dm_analytics', [
            'searchRow' => $req,
            'orderTypes' => $orderTypes,
        ]);
    }

	public function output(
        Request $request,
        RegisterBatchExecuteInstructionInterface $registerBatchExecute,
        SearchItemNameTypesInterface $searchItemNameTypes,
        FileUploadManager $fileUploadManager
    ) {
        $req = $request->all();

        try {
            $orderTypes = $searchItemNameTypes->execute([
                'm_itemname_type' => ItemNameType::ReceiptType,
                'delete_flg' => DeleteFlg::Use->value
            ]);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            session()->flash('messages.error', ['message' => $message]);
            session()->flash('messages.info', []);
        }

        try {
            if (empty($req['customer_data'])) {
                throw new \Exception(__('messages.error.order_search.select_name', ['name' => '顧客データ']));
            }

            $fileExtension = $request->file('customer_data')->getClientOriginalExtension();

            if (!in_array($fileExtension, ['xlsx', 'xls'])) {
                throw new \Exception(__('messages.error.order_search.select_name', ['name' => '顧客データ']));
            }

            if (preg_match('/\A[0-9]{4}[\/-]?[0-9]{2}[\/-]?[0-9]{2}\z/i', $req['order_date_from']) < 1) {
                throw new \Exception(__('messages.error.order_search.select_name', ['name' => '受注日FROM']));
            }

            if (preg_match('/\A[0-9]{4}[\/-]?[0-9]{2}[\/-]?[0-9]{2}\z/i', $req['order_date_to']) < 1) {
                throw new \Exception(__('messages.error.order_search.select_name', ['name' => '受注日TO']));
            }

            if ($req['order_type'] != '' && !is_numeric($req['order_type'])) {
                throw new \Exception(__('messages.error.order_search.select_name', ['name' => '注文方法']));
            }

            foreach (range(1, 10) as $i) {
                $key = 'sell_cd_' . $i;
                $sellCd = $req[$key];

                if (is_null($sellCd)) {
                    unset($req[$key]);
                    continue;
                }

                if (!preg_match('/^[a-zA-Z0-9, ]+$/', $sellCd)) {
                    throw new \Exception(__('messages.error.order_search.select_name', ['name' => '商品コード' . $i]));
                }
            }

            $requestFile = $request->file('customer_data');
            unset($req['customer_data']);
            unset($req['submit']);
            unset($req['_token']);

            $params = [
                'execute_batch_type' => $this->batchListEnum::EXPXLSX_DM_ANALYTICS,
                'execute_conditions' => $req,
            ];

            $ret = $registerBatchExecute->execute($params);
            $executeBatchInstructionId = $ret['result']['t_execute_batch_instruction_id'];
            $this->fileUpload($fileUploadManager, $requestFile, $executeBatchInstructionId, $fileExtension);

            session()->flash('messages.info', ['message' => __('messages.info.create_completed', ['data' => 'DM集計バッチ'])]);
            session()->flash('messages.error', []);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            session()->flash('messages.error', ['message' => $message]);
            session()->flash('messages.info', []);
        }

        return account_view('order.base.dm_analytics', [
            'orderTypes' => $orderTypes,
            'searchRow' => $req,
        ]);
    }

    private function fileUpload($manager, $file, $executeBatchInstructionId, $fileExtension) {
        $uploadSavePath = 'excel' . DIRECTORY_SEPARATOR . 'import' . DIRECTORY_SEPARATOR . $executeBatchInstructionId;
        $uploadFileName = $executeBatchInstructionId . '.' . $fileExtension;
        $manager->upload($file, $uploadSavePath, $uploadFileName);
    }
}
