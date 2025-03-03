<?php

namespace App\Http\Controllers\Order;

use App\Modules\Common\Base\RegisterBatchExecuteInstructionInterface;
use App\Modules\Master\Base\Enums\BatchListEnumInterface;
use App\Modules\Order\Base\GetExtendDataInterface;
use App\Modules\Order\Base\SearchPaymentAccountingInterface;
use App\Services\EsmSessionManager;

use Illuminate\Http\Request;
use Config;

/**
 * 経理処理用情報照会コントローラ
 */
class PaymentAccountingController
{
    protected $batchListEnum;

    public function __construct() {
        $this->batchListEnum = app(BatchListEnumInterface::class);
    }

    /**
     * submitボタン内容の取得
     */
    protected function getSubmitName($req)
    {
        $submitName = '';
        if(!empty($req['submit'])) {
            $submitName = $req['submit'];
        }
        return $submitName;
    }

    /**
     * 画面表示
     */
    public function list(
        Request $request,
        EsmSessionManager $esmSessionManager,
        GetExtendDataInterface $getExtendData,
        SearchPaymentAccountingInterface $searchPaymentAccounting,
    ) {
        // 基本情報
        $viewExtendData = $getExtendData->execute('edit');
		$viewExtendData['page_list_count'] = Config::get('Common.const.disp_limits');
        $viewMessage = [];
        $paginator = [];
        $searchRow = [];

        $compact = [
            'viewExtendData',
            'viewMessage',
            'paginator',
            'searchRow'
        ];

        // 通常のview
        return account_view('order.payment-accounting.base.list', compact( $compact ));
    }

    /**
     * 検索・出力
     */
    public function search(
        Request $request,
        EsmSessionManager $esmSessionManager,
        GetExtendDataInterface $getExtendData,
        RegisterBatchExecuteInstructionInterface $registerBatchExecute,
        SearchPaymentAccountingInterface $searchPaymentAccounting,
    ) {
        session()->forget('messages.info');
        session()->forget('messages.error');

        // postパラメータの取得
        $req = $request->all();
		$submitName = $this->getSubmitName($req);
        $searchRow = $req ?? [];

        // 基本情報
        $viewExtendData = $getExtendData->execute('edit');
		$viewExtendData['page_list_count'] = Config::get('Common.const.disp_limits');
        $viewMessage = [];
        $paginator = [];

        // 一覧取得：POSTされた時点で確定で実行
        $searchRow['m_account_id'] = $esmSessionManager->getAccountId();

        // 検索の場合は表示ページを1ページ目に戻す
        if( $submitName == 'search' ){
            $searchRow['hidden_next_page_no'] = 1;
        }

        $options = [
            'should_paginate' => true,
            'limit' => $searchRow['page_list_count'] ?? 10,
            'page' => $searchRow['hidden_next_page_no'] ?? 1,
        ];
        $paginator = $searchPaymentAccounting->execute( $searchRow, $options );

        // CSV出力
        if( $submitName == 'output' ){
            if( count($paginator) == 0 ){
                session()->flash('messages.error', [
                    'message' => __('messages.info.display.no_data',
                    [ 'data' => '経理処理用情報' ])
                ]);
            }
            else{
                // バッチに不要なパラメータを削除
                $executeCondition = $this->formatParameter( $searchRow );
                unset( $executeCondition['_token'] );
                unset( $executeCondition['submit'] );
                unset( $executeCondition['hidden_next_page_no'] );
                unset( $executeCondition['page_list_count'] );
                $params = [
                    'execute_batch_type' => $this->batchListEnum::EXPXLSX_BILLING_PAYMENT,
                    'execute_conditions' => $executeCondition,
                ];
                $registerBatchExecute->execute($params);
                session()->flash('messages.info', [
                    'message' => __('messages.info.create_completed',
                    [ 'data' => $this->batchListEnum::EXPXLSX_BILLING_PAYMENT->label() . 'バッチ' ])
                ]);
            }
        }

        $compact = [
            'viewExtendData',
            'viewMessage',
            'paginator',
            'searchRow'
        ];

        // 通常のview
        return account_view('order.payment-accounting.base.list', compact( $compact ));
    }

    /**
     * パラメータ情報の整形
     */
    private function formatParameter( $searchRow )
    {
        $executeCondition = $searchRow;

        // Y/m/d -> Y-m-d
        $executeCondition['estimated_shipping_date_from'] = $this->converDateFormat( $executeCondition['estimated_shipping_date_from'] );
        $executeCondition['scheduled_ship_date_to'] = $this->converDateFormat( $executeCondition['scheduled_ship_date_to'] );
        $executeCondition['shipment_confirmation_date_from'] = $this->converDateFormat( $executeCondition['shipment_confirmation_date_from'] );
        $executeCondition['shipment_confirmation_date_to'] = $this->converDateFormat( $executeCondition['shipment_confirmation_date_to'] );
        $executeCondition['payment_registration_date_from'] = $this->converDateFormat( $executeCondition['payment_registration_date_from'] );
        $executeCondition['payment_registration_date_to'] = $this->converDateFormat( $executeCondition['payment_registration_date_to'] );
        $executeCondition['customer_payment_date_from'] = $this->converDateFormat( $executeCondition['customer_payment_date_from'] );
        $executeCondition['customer_payment_date_to'] = $this->converDateFormat( $executeCondition['customer_payment_date_to'] );
        $executeCondition['account_deposit_date_from'] = $this->converDateFormat( $executeCondition['account_deposit_date_from'] );
        $executeCondition['account_deposit_date_to'] = $this->converDateFormat( $executeCondition['account_deposit_date_to'] );

        return $executeCondition;
    }

    /**
     * 日付項目をY-m-d形式に変換
     */
    private function converDateFormat( $str )
    {
        $ret = null;
        if( strlen( $str ?? '' ) > 0 ){
            $ret = date('Y-m-d', strtotime( $str ) );
        }
        return $ret;
    }
}
