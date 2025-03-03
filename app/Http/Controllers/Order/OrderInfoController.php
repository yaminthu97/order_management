<?php

namespace App\Http\Controllers\Order;

use App\Modules\Order\Base\SearchInterface;
use App\Services\EsmSessionManager;
use Illuminate\Http\Request;
use Symfony\Component\Routing\Exception\InvalidParameterException;

use App\Enums\ProgressTypeEnum;
use App\Enums\ItemNameType;
use App\Enums\OrderOutputPdfNamesEnum;
use App\Enums\Esm2SubSys;
use App\Modules\Master\Base\Enums\BatchListEnumInterface;

use App\Modules\Order\Base\GetExtendDataInterface;

use App\Modules\Order\Base\SearchOrderTagMasterInterface;
use App\Modules\Order\Base\SearchSettlementHistoryInterface;
use App\Modules\Order\Base\SerchMailSendHistoryInterface;
use App\Modules\Master\Base\SearchItemNameTypesInterface;
use App\Modules\Customer\Base\SearchCustCommunicationInterface;
use App\Modules\Order\Base\SearchOrderHdrLogInterface;
use App\Modules\Order\Base\SearchProgressUpdateHistoryInterface;
use App\Modules\Order\Base\SearchOrderTagInterface;
use App\Modules\Order\Base\SearchPaymentInterface;
use App\Modules\Order\Base\SearchReportOutputHistoryInterface;
use App\Modules\Common\Base\SearchInvoiceSystemInterface;
use App\Modules\Order\Base\SearchCooperationHistoryInterface;

use App\Modules\Order\Base\UpdateOrderTagInterface;
use App\Modules\Order\Base\UpdateApiOrderProgressInterface;
use App\Modules\Order\Base\UpdateOrderInfoInterface;
use App\Modules\Common\Base\RegisterBatchExecuteInstructionInterface;
use App\Modules\Master\Base\SearchOperatorsInterface;

use App\Models\Order\Base\PaymentModel;
use App\Enums\PaymentTypeEnum;

class OrderInfoController
{
    public function __construct(
        protected EsmSessionManager $esmSessionManager
    ){}

    public function info(
        Request $request,
        SearchInterface $searchOrder,
        GetExtendDataInterface $getExtendData,
        SearchOrderTagMasterInterface $searchOrderTagMaster,
        SearchSettlementHistoryInterface $searchSettlementHistory,
        SerchMailSendHistoryInterface $serchMailSendHistory,
        SearchItemNameTypesInterface $searchItemNameTypes,
        SearchCustCommunicationInterface $searchCustCommunication,
        SearchOrderHdrLogInterface $searchOrderHdrLog,
        SearchProgressUpdateHistoryInterface $searchProgressUpdateHistory,
        SearchOrderTagInterface $searchOrderTag,
        SearchPaymentInterface $searchPayment,
        SearchReportOutputHistoryInterface $searchReportOutputHistory,
        SearchInvoiceSystemInterface $searchInvoiceSystem,
        SearchCooperationHistoryInterface $searchCooperationHistory,
    )
    {
        $options = [
            'with' => [
                'ecs',
                'cust',
                'billingCust',
                'orderDestination',
                'orderDestination.orderDtls',
                'orderDestination.orderDtls.orderDtl',
                'orderDestination.orderDtls.amiEcPage',
                'orderDestination.orderDtls.orderDtlAttachmentItem',
                'orderDestination.orderDtls.orderDtlAttachmentItem.amiAttachmentItem',
                'orderDestination.orderDtls.orderDtlNoshi.noshiDetail',
                'orderDestination.orderDtls.orderDtlNoshi.noshiNamingPattern',
                'orderDestination.deliHdr',
                'orderDestination.deliHdr.shippingLabels',
                'orderTags',
                'orderMemo',
                'billingDestination',
                'billingHdr',
            ]
        ];
        $order = $searchOrder->execute(['t_order_hdr_id' => $request->route('id'), 'm_account_id' => $this->esmSessionManager->getAccountId()], $options)->first();
        
        // 見つからなければ404
        if (!$order) {
            throw new InvalidParameterException('Order not found');
        }
        
        // viewExtendData の取得
        $viewExtendData = $getExtendData->execute('info');

        // 進捗区分の一覧
        $progress_type_list = [];
        foreach (ProgressTypeEnum::cases() as $case) {
            $progress_type_list[$case->value] = $case->label();
        }

        // 受注タグマスタの一覧
        $order_tag_master_info = $searchOrderTagMaster->execute();

        // 決済履歴 t_settlement_history SettlementHistoryModel
        $settlement_history = $searchSettlementHistory->execute([
          'm_account_id' => $order->m_account_id,
          't_order_hdr_id' => $order->t_order_hdr_id,
        ]);

        // 取消理由の一覧 m_itemname_types 2
        $cancel_type_info = $searchItemNameTypes->execute([
            'm_itemname_type' => ItemNameType::CancelReason->value,
        ]);

        // 対応履歴
        $communication_history = $searchCustCommunication->execute([
            'm_account_id' => $order->m_account_id,
            't_order_hdr_id' => $order->t_order_hdr_id,
        ], [
            'should_paginate' => true,
            'limit' => 10,
            'page' => 1,
        ]);

        // 進捗区分変更履歴
        $progress_update_history = $searchProgressUpdateHistory->execute([
            'm_account_id' => $order->m_account_id,
            't_order_hdr_id' => $order->t_order_hdr_id,
        ]);
        $order_hdr_history = $progress_update_history;

        // タグ変更履歴
        $order_tag_history = $searchOrderTag->execute([
            'm_account_id' => $order->m_account_id,
            't_order_hdr_id' => $order->t_order_hdr_id,
        ]);

        // 入金履歴 t_payment
        $payment_history = $searchPayment->execute([
            'm_account_id' => $order->m_account_id,
            't_order_hdr_id' => $order->t_order_hdr_id,
        ]);

        // 帳票登録 t_report_output_history
        $report_history = $searchReportOutputHistory->execute([
            'm_account_id' => $order->m_account_id,
            't_order_hdr_id' => $order->t_order_hdr_id,
        ]);

        // 帳票出力 output_queue_report
        $output_queue_report = collect(OrderOutputPdfNamesEnum::cases())->mapWithKeys(fn($type) => [$type->value => $type->label()])->toArray();

        // 出荷データ形式 送り状システムマスタテーブル m_invoice_system
        // use_m_account_id が0か m_account_id のものを取得
        $output_queue_delivery = $searchInvoiceSystem->execute([
            'm_account_id' => $order->m_account_id,
        ]);

        // ECサイト 連携履歴テーブル t_cooperation_history
        $cooper_history = $searchCooperationHistory->execute([
            'm_account_id' => $order->m_account_id,
            't_order_hdr_id' => $order->t_order_hdr_id,
        ]);

        // メール送信履歴
        $mail_history = $serchMailSendHistory->execute([
            'm_account_id' => $order->m_account_id,
            't_order_hdr_id' => $order->t_order_hdr_id,
        ]);

        // 引当可能数の取得

        return account_view('order.base.info', [
            'order' => $order,
            'viewExtendData' => $viewExtendData,
            'progress_info' => $progress_type_list,
            'progress_type_list' => $progress_type_list,
            'order_tag_master_info' => $order_tag_master_info,
            'cancel_type_info' => $cancel_type_info,
            'output_queue_report' => $output_queue_report,
            'output_queue_delivery' => $output_queue_delivery,
            'communication_history' => $communication_history,
            'order_hdr_history' => $order_hdr_history,
            'order_tag_history' => $order_tag_history,
            'order_cust_info' => $order->cust,
            'settlement_history' => $settlement_history,
            'payment_history' => $payment_history,
            'report_history' => $report_history,
            'cooper_history' => $cooper_history,
            'mail_history' => $mail_history,
        ]);
    }

    public function postInfo(
        Request $request,
        SearchInterface $searchOrder,
        UpdateOrderTagInterface $updateOrderTag,
        UpdateApiOrderProgressInterface $updateApiOrderProgress,
        UpdateOrderInfoInterface $updateOrderInfo,
        RegisterBatchExecuteInstructionInterface $registerBatchExecute,
    )
    {
        $batchListEnum = app(BatchListEnumInterface::class);
        $options = [
            'with' => [
                'ecs',
                'cust',
                'billingCust',
                'orderDestination',
                'orderDestination.orderDtls',
                'orderDestination.orderDtls.orderDtl',
                'orderDestination.orderDtls.amiEcPage',
                'orderTags',
                'deliHdr',
                'orderMemo',
                'billingDestination',
                'billingHdr',
            ]
        ];
        $order = $searchOrder->execute(['t_order_hdr_id' => $request->route('id'), 'm_account_id' => $this->esmSessionManager->getAccountId()], $options)->first();

        $req = $request->all();
        // 見つからなければ404
        if (!$order) {
            throw new InvalidParameterException('Order not found');
        }
        // 各種処理
        $submit = $request->input('submit');

        $previous_subsys = Esm2SubSys::ORDER->value;
        $previous_url = request()->path();
        $redirectParams = base64_encode(json_encode([
            'previous_subsys' => $previous_subsys,
            'previous_url' => $previous_url,
            'previous_key' => $order->t_cust_id . ',' . $order->t_order_hdr_id,
        ]));

        switch ($submit) {
            // 進捗区分変更
            case 'status_progress_edit':
                $result = $updateApiOrderProgress->execute([
                    't_order_hdr_id' => $order->t_order_hdr_id,
                    'progress_type' => $req['status_progress_type'],
                    'update_operator_id' => $this->esmSessionManager->getOperatorId(),
                ]);
                if(isset($result['result']['status']) && $result['result']['status'] == 0) {
                    // flush にメッセージ追加
                    session()->flash('messages.info', ['message' => __('messages.info.update_completed', ['data' => '進捗区分'])]);
                } else {
                    // flush にメッセージ追加
                    if (isset($result['result']['error']['message'])) {
                        $messages = explode("\r\n", $result['result']['error']['message']);
                        session()->flash('messages.error', ['message' => $messages]);
                    } else {
                        session()->flash('messages.error', ['message' => __('messages.error.connection_error')]);
                    }
                }
                break;
            // 受注タグ追加
            case 'status_regist_tag':
                $result = $updateOrderTag->execute($order->t_order_hdr_id, $req['status_regist_tags'][0], []);
                // flush にメッセージ追加
                session()->flash('messages.info', ['message' => __('messages.info.update_completed', ['data' => '受注タグ'])]);
                break;
            // 受注タグ削除
            case 'status_delete_tag':
                if (isset($req['status_delete_tags'])) {
                    foreach($req['status_delete_tags'] as $tag) {
                        $result = $updateOrderTag->execute($order->t_order_hdr_id, $tag, ['cancel_flg' => 1]);
                    }
                }
                // flush にメッセージ追加
                session()->flash('messages.info', ['message' => __('messages.info.update_completed', ['data' => '受注タグ'])]);

                break;
            // 社内メモ更新
            case 'update_operator_comment':
                $result = $updateOrderInfo->execute([
                    't_order_hdr_id' => $order->t_order_hdr_id,
                    'submit' => $req['submit'],
                    'operator_comment' => $req['operator_comment'],
                ]);
                // flush にメッセージ追加
                session()->flash('messages.info', ['message' => __('messages.info.update_completed', ['data' => '社内メモ'])]);
                break;
            // 備考確認済み
            case 'comment_check':
                $result = $updateOrderInfo->execute([
                    't_order_hdr_id' => $order->t_order_hdr_id,
                    'submit' => $req['submit'],
                ]);
                // flush にメッセージ追加
                session()->flash('messages.info', ['message' => __('messages.info.update_completed', ['data' => '備考確認済み'])]);
                break;
            // 領収書宛名、但し書き登録
            case 'receipt_direction_and_proviso':
                $result = $updateOrderInfo->execute([
                    't_order_hdr_id' => $order->t_order_hdr_id,
                    'submit' => $req['submit'],
                    'receipt_direction' => $req['receipt_direction'],
                    'receipt_proviso' => $req['receipt_proviso'],
                ]);
                // flush にメッセージ追加
                session()->flash('messages.info', ['message' => __('messages.info.update_completed', ['data' => '領収書宛名、但し書き'])]);

                break;
            // 要注意顧客確認済み
            case 'alert_cust_check':
                $result = $updateOrderInfo->execute([
                    't_order_hdr_id' => $order->t_order_hdr_id,
                    'submit' => $req['submit'],
                ]);
                // flush にメッセージ追加
                session()->flash('messages.info', ['message' => __('messages.info.update_completed', ['data' => '要注意顧客確認済み'])]);
                break;
            // 住所確認済み
            case 'address_check':
                $result = $updateOrderInfo->execute([
                    't_order_hdr_id' => $order->t_order_hdr_id,
                    'submit' => $req['submit'],
                ]);
                // flush にメッセージ追加
                session()->flash('messages.info', ['message' => __('messages.info.update_completed', ['data' => '住所確認済み'])]);
                break;
            // 指定配送日確認済み
            case 'deli_hope_date_check':
                $result = $updateOrderInfo->execute([
                    't_order_hdr_id' => $order->t_order_hdr_id,
                    'submit' => $req['submit'],
                ]);
                // flush にメッセージ追加
                session()->flash('messages.info', ['message' => __('messages.info.update_completed', ['data' => '指定配送日確認済み'])]);
                break;

            // 在庫引き当て
            case 'entry_drawing':
                // TODO: 受注登録の実装を使用する
                // flush にメッセージ追加
                session()->flash('messages.info', ['message' => '在庫引当を行いました']);
                break;
            // 強制出荷
            case 'forced_deli':
                $result = $updateOrderInfo->execute([
                    't_order_hdr_id' => $order->t_order_hdr_id,
                    'submit' => $req['submit'],
                ]);
                // flush にメッセージ追加
                session()->flash('messages.info', ['message' => '強制出荷を行いました']);
                break;
            // 他注文を同梱する
            case 'order_bundle':
                return redirect(esm_external_route('order/order-bundle/list', ['params' => $redirectParams]));
                break;

            //タブ各種
            // 対応履歴の登録
            case 'customer_history_new':
                return redirect()->route('cc.customer-history.new', ['params' => $redirectParams]);
                break;
            // 進捗区分変更履歴
            case 'history_progress_edit':
                $result = $updateApiOrderProgress->execute([
                    't_order_hdr_id' => $order->t_order_hdr_id,
                    'progress_type' => $req['history_progress_type'],
                    'update_operator_id' => $this->esmSessionManager->getOperatorId(),
                ]);
                if(isset($result['result']['status']) && $result['result']['status'] === 0) {
                    // flush にメッセージ追加
                    session()->flash('messages.info', ['message' => __('messages.info.update_completed', ['data' => '進捗区分'])]);
                } else {
                    // flush にメッセージ追加
                    if (isset($result['result']['error']['message'])) {
                        $messages = explode("\r\n", $result['result']['error']['message']);
                        session()->flash('messages.error', ['message' => $messages]);
                    } else {
                        session()->flash('messages.error', ['message' => __('messages.error.connection_error')]);
                    }
                }
                break;
            // タグ追加
            case 'history_regist_tag':
                $result = $updateOrderTag->execute($order->t_order_hdr_id, $req['history_regist_tags'][0], []);
                // flush にメッセージ追加
                session()->flash('messages.info', ['message' => __('messages.info.update_completed', ['data' => '受注タグ'])]);
                break;
            // タグ削除
            case 'history_delete_tag':
                if (isset($req['history_delete_tags'])) {
                    foreach($req['history_delete_tags'] as $tag) {
                        $result = $updateOrderTag->execute($order->t_order_hdr_id, $tag, ['cancel_flg' => 1]);
                    }
                }
                // flush にメッセージ追加
                session()->flash('messages.info', ['message' => __('messages.info.update_completed', ['data' => '受注タグ'])]);
                break;
            // 決済履歴（与信OKにする）
            case 'credit_check':
                $result = $updateOrderInfo->execute([
                    't_order_hdr_id' => $order->t_order_hdr_id,
                    'submit' => $req['submit'],
                ]);
                // flush にメッセージ追加
                session()->flash('messages.info', ['message' => __('messages.info.update_completed', ['data' => '決済履歴'])]);
                break;
            // 入金履歴、入金登録
            case 'payment':
                // $req に必要な情報が入っているか確認
                if (!isset($req['payment_subject_id']) || !isset($req['cust_payment_date']) || !isset($req['account_payment_date']) || !isset($req['payment_price'])) {
                    session()->flash('messages.error', ['message' => __('messages.error.invalid_parameter')]);
                    break;
                }
                if ($req['payment_price'] <= 0) {
                    session()->flash('messages.error', ['message' => __('messages.error.invalid_parameter')]);
                    break;
                }
                // $req に入っている情報を PaymentModel にセット
                $payment = new PaymentModel();
                $payment->m_account_id = $order->m_account_id;
                $payment->t_order_hdr_id = $order->t_order_hdr_id;
                $payment->delete_flg = 0;
                $payment->payment_entry_date = date('Y-m-d');
                $payment->payment_subject = $req['payment_subject_id'];
                $payment->cust_payment_date = $req['cust_payment_date'];
                $payment->account_payment_date = $req['account_payment_date'];
                $payment->payment_price = $req['payment_price'];
                $payment->entry_operator_id = $this->esmSessionManager->getOperatorId();
                $payment->update_operator_id = $this->esmSessionManager->getOperatorId();
                $payment->save();

                $oldPaymentType = $order->payment_type;
                $order->payment_date = $req['account_payment_date'];
                $order->payment_price += $req['payment_price'];
                if ($order->payment_type != PaymentTypeEnum::EXCLUDED->value) {
                    if ($order->payment_price == $order->order_total_price) {
                        $order->payment_type = PaymentTypeEnum::PAID->value;
                    } else if ($order->payment_price > 0) {
                        $order->payment_type = PaymentTypeEnum::PARTIALLY_PAID->value;
                    } else {
                        $order->payment_type = PaymentTypeEnum::NOT_PAID->value;
                    }
                }
                if ($oldPaymentType !== $order->payment_type) {
                    $order->payment_datetime = date('Y-m-d H:i:s');
                }
                $order->save();

                // flush にメッセージ追加
                session()->flash('messages.info', ['message' => __('messages.info.create_completed', ['data' => '入金'])]);

                break;
            // 帳票出力
            case 'queue_report':
                $params = [
                    'execute_batch_type' =>  $req['output_queue_report'],
                    'execute_conditions' => ['t_order_hdr_id' => [$order->t_order_hdr_id]],
                ];
                $result = $registerBatchExecute->execute($params);
                if(isset($result['result']['status']) && $result['result']['status'] === 0) {
                    // flush にメッセージ追加
                    session()->flash('messages.info', ['message' => __('messages.info.create_completed', ['data' => '帳票出力バッチ'])]);
                } else {
                    // flush にメッセージ追加
                    if (isset($result['result']['error']['message'])) {
                        session()->flash('messages.error', ['message' => $result['result']['error']['message']]);
                    } else {
                        session()->flash('messages.error', ['message' => __('messages.error.connection_error')]);
                    }
                }
                break;
            // データ出力
            case 'queue_delivery':
                $params = [
                    'execute_batch_type' =>  'expcsv_delivery_' . $req['output_queue_delivery'], // TODO validation
                    'execute_conditions' => ['t_order_hdr_id' => [$order->t_order_hdr_id]],
                ];
                $result = $registerBatchExecute->execute($params);
                if(isset($result['result']['status']) && $result['result']['status'] === 0) {
                    // flush にメッセージ追加
                    session()->flash('messages.info', ['message' => __('messages.info.create_completed', ['data' => 'データ出力バッチ'])]);
                } else {
                    // flush にメッセージ追加
                    if (isset($result['result']['error']['message'])) {
                        session()->flash('messages.error', ['message' => $result['result']['error']['message']]);
                    } else {
                        session()->flash('messages.error', ['message' => __('messages.error.connection_error')]);
                    }
                }
                break;
            // メール送信
            case 'mail_send':
                $params = [
                    't_order_hdr_id' => $order->t_order_hdr_id,
                ];
                $encodedParams = $this->esmSessionManager->setSessionKeyName(
                    config('define.order.send_mail_parameter_session'),
                    config('define.order.session_key_id'),
                    $params
                );
                return redirect(esm_external_route('order/mail-send/new', ['params' => $encodedParams]));
                break;
            // 受注キャンセル
            case 'order_cancel':
                if (!isset($req['cancel_type']) || !isset($req['cancel_note'])) {
                    session()->flash('messages.error', ['message' => __('messages.error.invalid_parameter')]);
                    break;
                }
                $result = $updateApiOrderProgress->execute([
                    't_order_hdr_id' => $order->t_order_hdr_id,
                    'progress_type' => ProgressTypeEnum::Cancelled->value,
                    'update_operator_id' => $this->esmSessionManager->getOperatorId(),
                    'cancel_type' => (int)$req['cancel_type'],
                    'cancel_note' => $req['cancel_note'],
                ]);
                if(isset($result['result']['status']) && $result['result']['status'] == 0) {
                    // flush にメッセージ追加
                    session()->flash('messages.info', ['message' => __('messages.info.update_completed', ['data' => '進捗区分'])]);
                } else {
                    // flush にメッセージ追加
                    if (isset($result['result']['error']['message'])) {
                        $messages = explode("\r\n", $result['result']['error']['message']);
                        session()->flash('messages.error', ['message' => $messages]);
                    } else {
                        session()->flash('messages.error', ['message' => __('messages.error.connection_error')]);
                    }
                }
                break;
            // 返品
            case 'order_return':
                
            default:
                throw new InvalidParameterException('不正なリクエストです');
        }
        return redirect()->route('order.order.info', array_merge(['id' => $order->t_order_hdr_id]));
    }
}
