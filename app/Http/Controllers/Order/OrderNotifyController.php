<?php

namespace App\Http\Controllers\Order;

use App\Modules\Order\Base\FindOrderInterface;
use App\Modules\Order\Base\GetExtendDataInterface;
use App\Http\Requests\Order\Base\UpdateOrderRequest;
use App\Modules\Order\Base\UpdateOrderInterface;
use App\Modules\Order\Base\UpdateOrderCheckInterface;

use App\Modules\Order\Base\RegisterOrderDrawingInterface;
use App\Modules\Order\Base\RegisterOrderTagAutoInterface;
use App\Modules\Order\Base\RegisterOrderProgressInterface;
use App\Modules\Order\Base\RegisterSendmailInterface;
use App\Modules\Order\Base\RegisterMailSendHistoryInterface;
use App\Modules\Claim\Base\UpdateBillingHdrInterface;
use App\Models\Order\Base\OrderDrlSkuModel;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class OrderNotifyController
{
    private $sessionKeyBase = 'order_edit_data_';
    private $sessionKeyId = 'data_key_id';

    //private $findOrder;
    //private $getExtendData;
    //private $registerOrder;

    public function notify(
        Request $request,
        FindOrderInterface $findOrder,
        GetExtendDataInterface $getExtendData,
    ) {
        $editRow = $this->getSessionFromParam($request);

        $viewExtendData = $getExtendData->execute('edit');
        $viewExtendData['m_delivery_types'] = [];

        return account_view('order.base.notify', [
            'editRow' => $editRow,
            'viewExtendData' => $viewExtendData,
        ]);
    }

	public function postNotify(
        UpdateOrderRequest $request,
        UpdateOrderInterface $updateOrder,
        UpdateOrderCheckInterface $updateOrderCheck,
        RegisterOrderDrawingInterface $registerOrderDrawing,
        RegisterOrderTagAutoInterface $registerOrderTagAuto,
        RegisterOrderProgressInterface $registerOrderProgress,
        RegisterSendmailInterface $registerSendmail,
        RegisterMailSendHistoryInterface $registerMailSendHistory,
        UpdateBillingHdrInterface $updateBillingHdr,
    ) {
        $editRow = $this->getSessionFromParam($request);
        if ($request->has('submit_register')) {

            // チェック処理
            try{
                $editRow = $updateOrderCheck->execute($editRow['t_order_hdr_id'], $editRow);
            } catch (\App\Exceptions\ModuleValidationException $e){
                return redirect()->back()->withErrors($e->getValidationErrors());
            }

            // キャンセルの場合
            if (!empty($editRow['cancel_timestamp'])) {
                $result = $registerOrderProgress->execute([
                    't_order_hdr_id' => $editRow['t_order_hdr_id'],
                    'progress_type' => 90, // キャンセル
                    'cancel_type' => $editRow['cancel_type'],
                    'cancel_note' => $editRow['cancel_note'],
                ]);
                if (!isset($result['response']['result']['status']) || $result['response']['result']['status'] == 1) {
                    //エラー時はException
                    throw new \Exception('キャンセル処理に失敗しました。');
                }
            }

            // 登録処理
            $resultRow = $updateOrder->execute($editRow['t_order_hdr_id'], $editRow);
            
            // トランザクション後の処理
            // 新規登録時処理
            if (!isset($editRow['t_order_hdr_id'])) {
                //返品時は処理しない return
                if (isset($editRow['return_flg']) && $editRow['return_flg'] == 1) {
                    return redirect(route('order.order.list'));
                }
                // $resultRow から 受注明細SKU を取得
                $orderDtls = $resultRow->orderDtl;
                foreach ($orderDtls as $orderDtl) {
                    //在庫引当API(仮引当)
                    $drawingParam = [
                        'process_type' => 1, //仮引当
                        'm_ecs_id' => $editRow['m_ecs_id'] ?? null,
                        'ec_order_id' => $editRow['ec_order_id'] ?? null,
                        'order_id' => $resultRow['t_order_hdr_id'] ?? null,
                        'forced_delivery' => 0,
                        'detail_info' => [
                            'detail_number' => $orderDtl['order_dtl_seq'] ?? null,
                            'item_cd' => $orderDtl['sell_cd'] ?? null,
                            'vol' => $orderDtl['order_sell_vol'] ?? null,
                            //'drawing_result' => 0
                        ]
                    ];
                    $drawingResult = $registerOrderDrawing->execute($drawingParam);
                    if (isset($drawingResult['result']['status']) && $drawingResult['result']['status'] == 0) {
                        // 在庫引当APIの結果により受注明細SKU更新処理
                        foreach ($drawingResult['register_result']['detail_info'] as $stockRow) {
                            $orderSkuDb = OrderDrlSkuModel::where('t_order_hdr_id', '=', $resultRow['t_order_hdr_id'])
                                ->where('order_dtl_seq', '=', $stockRow['detail_number'])
                                ->where('item_cd', '=', $stockRow['item_cd']);
                            $skuData = [
                                'update_operator_id'		=>	$resultRow['operator_id'],
                                'update_timestamp'			=>	Carbon::now(),
                                'm_warehouse_id'			=>	$stockRow['warehouse_id'],
                                'temp_reservation_flg'		=>	($stockRow['drawing_result'] == 0) ? null : $stockRow['drawing_result']
                                ];
                            $orderSkuDb->update($skuData);
                        }
                    }
                }

                //注文登録受付API(請求金額がマイナスでない場合)
                //受注タグ付与判定API 新規作成
                $registerOrderTagAuto->execute($resultRow['t_order_hdr_id'], 1);
                //前日以降のEC注文受注、かつ、メールアドレス設定時
                $checkDate = new Carbon(Carbon::yesterday()->format('Y/m/d 00:00:00'));
                $orderDate = new Carbon($editRow['order_datetime']);
                $mailTempaleId = null;
                if ($orderDate->gte($checkDate) &&
                    isset($editRow['ec_order_num']) &&
                    strlen($editRow['ec_order_num']) > 0 &&
                    ((isset($editRow['order_email1']) && strlen($editRow['order_email1']) > 0) ||
                        (isset($editRow['order_email2']) && strlen($editRow['order_email2']) > 0))
                    )
                {
                    // メール送信処理
                    // ecs から smtp_info, from_address, bcc_address を設定
                    $registerSendmail->execute($resultRow['t_order_hdr_id'], 1);
                    $registerMailSendHistory->execute($resultRow['t_order_hdr_id'], 1);
                }
                
                // 請求基本テーブル追加
                $billingHdr = $updateBillingHdr->execute($resultRow['t_order_hdr_id']);

            } else {
                //在庫引当API(仮引当)
                $drawingParam = [
                    'process_type' => 1, //仮引当
                    'm_ecs_id' => $editRow['m_ecs_id'] ?? null,
                    'ec_order_id' => $editRow['ec_order_id'] ?? null,
                    'order_id' => $resultRow['t_order_hdr_id'] ?? null,
                    'forced_delivery' => 0,
                    'detail_info' => [
                        'detail_number' => $orderDtl['order_dtl_seq'] ?? null,
                        'item_cd' => $orderDtl['sell_cd'] ?? null,
                        'vol' => $orderDtl['order_sell_vol'] ?? null,
                        //'drawing_result' => 0
                    ]
                ];
                $drawingResult = $registerOrderDrawing->execute($drawingParam);
                if (isset($drawingResult['result']['status']) && $drawingResult['result']['status'] == 0) {
                    // 在庫引当APIの結果により受注明細SKU更新処理
                    foreach ($drawingResult['register_result']['detail_info'] as $stockRow) {
                        $orderSkuDb = OrderDrlSkuModel::where('t_order_hdr_id', '=', $resultRow['t_order_hdr_id'])
                            ->where('order_dtl_seq', '=', $stockRow['detail_number'])
                            ->where('item_cd', '=', $stockRow['item_cd']);
                        $skuData = [
                            'update_operator_id'		=>	$resultRow['operator_id'],
                            'update_timestamp'			=>	Carbon::now(),
                            'm_warehouse_id'			=>	$stockRow['warehouse_id'],
                            'temp_reservation_flg'		=>	($stockRow['drawing_result'] == 0) ? null : $stockRow['drawing_result']
                            ];
                        $orderSkuDb->update($skuData);
                    }
                }
                //受注タグ付与判定API 更新
                $registerOrderTagAuto->execute($resultRow['t_order_hdr_id'], 2);

                // 請求基本テーブル追加
                $billingHdr = $updateBillingHdr->execute($resultRow['t_order_hdr_id']);
            }

            // order.order.info にリダイレクト
            return redirect(route('order.order.info', ['id' => $resultRow['t_order_hdr_id']]));
        } elseif ($request->has('submit_cancel')) {
            // t_order_hdr_id の有無で新規登録画面か編集画面にリダイレクト
            $params = '?params=' . $request->input('params');
            if (isset($editRow['t_order_hdr_id'])) {
                return redirect(route('order.order.edit', ['id' => $editRow['t_order_hdr_id']]) . $params);
            } else {
                $params = '?params=' . $request->input('params');
                return redirect(route('order.order.new') . $params);
            }
        }
	}

    protected function getSessionFromParam($request)
    {
        if ($request->query->has('params')) {
            // params を復元
            $base64decodeParams = base64_decode($request->query('params'));
            $decodeParams = json_decode($base64decodeParams, true);

            if (isset($decodeParams[$this->sessionKeyId])) {
                // $decodeParams に $sessionKeyId が含まれている場合、セッションから取得
                $editRow = Session::get($this->sessionKeyBase . $decodeParams[$this->sessionKeyId]);
            }
            return $editRow;
        } else {
            return [];
        }
    }
}
