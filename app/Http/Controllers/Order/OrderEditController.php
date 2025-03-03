<?php

namespace App\Http\Controllers\Order;

use App\Modules\Order\Base\FindOrderInterface;
use App\Modules\Order\Base\GetExtendDataInterface;
use App\Modules\Customer\Base\SearchCustomerInterface;

use App\Http\Requests\Order\Base\EditOrderRequest;
use App\Modules\Order\Base\SetCalcSubTotalInterface;
use App\Modules\Order\Base\AddCampaignItemInterface;
use App\Modules\Order\Base\AddBillingTypeInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Exceptions\DataNotFoundException;

use App\Enums\ProgressTypeEnum;

class OrderEditController
{
    private $sessionKeyBase = 'order_edit_data_';
    private $sessionKeyId = 'data_key_id';

    /*
     * GET /order/new
     */
    public function new(
        Request $request,
        FindOrderInterface $findOrder,
        GetExtendDataInterface $getExtendData,
        SearchCustomerInterface $searchCustomer,
    ) {
        $editRow = $findOrder->execute(null);
        $sessionKeyId = $this->sessionKeyId;
        // viewExtendData の取得
        $viewExtendData = $getExtendData->execute('edit');

        $params = $this->getSessionFromParam($request);
        // $editRow の各プロパティに params の値をセット
        if (isset($params)) {
            foreach ($params as $key => $value) {
                $editRow->$key = $value;
            }
        }

        // $editRow[$sessionKeyId] が存在しなければ生成
        if (!isset($editRow[$sessionKeyId])) {
            $editRow[$sessionKeyId] = Str::random(32);
        }

        // 顧客検索
        if (isset($editRow['m_cust_id'])) {
            $customer = $searchCustomer->execute(['m_cust_id' => $editRow['m_cust_id']], [])->first();
        }
        
        // 顧客情報から不足している情報をセット
        $editRow = $this->getNewCustomerInfo($editRow, $customer);

        return account_view('order.base.edit', [
            'editRow' => $editRow,
            'customer' => $customer ?? null,
            'viewExtendData' => $viewExtendData,
        ]);
    }

    /*
     * GET /order/edit/{id}
     */
    public function edit(
        Request $request,
        FindOrderInterface $findOrder,
        GetExtendDataInterface $getExtendData,
        SearchCustomerInterface $searchCustomer,
    ) {
        $orderHdr = $findOrder->execute($request->route('id'));
        // $orderHdr が見つからない場合は list へリダイレクト
        if (!$orderHdr->t_order_hdr_id) {
            return redirect(route('order.order.list'))->with([
                'messages.error' => ['message' => __('messages.error.data_not_found', ['data' => '受注情報', 'id' => $request->route('id')])]
            ]);
        }
        $sessionKeyId = $this->sessionKeyId;

        // viewExtendData の取得
        $viewExtendData = $getExtendData->execute('edit');

        $params = $this->getSessionFromParam($request);

        $editRow = $this->setEditRow([], $orderHdr);
        // $editRow の各プロパティに params の値をセット
        if (isset($params)) {
            foreach ($params as $key => $value) {
                $editRow[$key] = $value;
            }
        }

        // $editRow[$sessionKeyId] が存在しなければ生成
        if (!isset($editRow[$sessionKeyId])) {
            $editRow[$sessionKeyId] = Str::random(32);
        }
        // 顧客検索
        if (isset($editRow['m_cust_id'])) {
            $customer = $searchCustomer->execute(['m_cust_id' => $editRow['m_cust_id']], [])->first();
        }

        return account_view('order.base.edit', [
            'editRow' => $editRow,
            'customer' => $customer ?? null,
            'viewExtendData' => $viewExtendData,
        ]);
    }

    /*
     * POST /order/new
     */
    public function postNew(
        EditOrderRequest $request,
        AddCampaignItemInterface $addCampaignItem,
        AddBillingTypeInterface $addBillingType,
        SetCalcSubTotalInterface $setCalcSubTotal,
    ) {
		$editRow = $request->all();

        // submit_cancel が送信されてきた場合
        if ($request->has('submit_cancel')) {
            if (isset($editRow['previous_url']) && $editRow['previous_url']) {
                // previous_subsys/previous_url にリダイレクト
                return redirect($request->input('previous_subsys').'/'.$request->input('previous_url'));
            } else {
                // デフォルトでは order.order.list にリダイレクト
                return redirect(route('order.order.list'));
            }
        }

        // $editRow に対する各種計算処理
        //$editRow = $setCalcSubTotal->execute($editRow);

        // キャンペーン商品追加
        $editRow = $addCampaignItem->execute($editRow);

        // t_order_hdr に請求書同梱送付先ID billing_destination_id を設定するための情報を追加
        $editRow = $addBillingType->execute($editRow);

        // 枝番の更新
        $editRow = $this->updateSequence($editRow);

		// セッションに入力内容を保持
        $sessionKey = $this->sessionKeyBase . $editRow[$this->sessionKeyId];
        $existingData = Session::get($sessionKey, []);
        $mergedData = array_merge($existingData, $editRow);
        Session::put($sessionKey, $mergedData);

        // ?params= に base64 エンコードした「'{"data_key_id":"$editRow['data_key_id']"}'」 を入れる
        $params = 'params=' . base64_encode('{"data_key_id":"'.$editRow[$this->sessionKeyId].'"}');
        // 確認画面へリダイレクト
        return redirect(route('order.order.notify', $params))->withInput($editRow);

    }

    /*
     * POST /order/edit/{id}
     */
    public function postEdit(
        EditOrderRequest $request,
        AddCampaignItemInterface $addCampaignItem,
        AddBillingTypeInterface $addBillingType,
        SetCalcSubTotalInterface $setCalcSubTotal,
    ) {
		$editRow = $request->all();

        // submit_cancel が送信されてきた場合
        if ($request->has('submit_cancel')) {
            if (isset($editRow['previous_url']) && $editRow['previous_url']) {
                // previous_subsys/previous_url にリダイレクト
                return redirect($request->input('previous_subsys').'/'.$request->input('previous_url'));
            } else {
                // デフォルトでは order.order.list にリダイレクト
                return redirect(route('order.order.list'));
            }
        }

        // $editRow に対する各種計算処理
        //$editRow = $setCalcSubTotal->execute($editRow);

        // キャンペーン商品追加
        $editRow = $addCampaignItem->execute($editRow);

        // t_order_hdr に請求書同梱送付先ID billing_destination_id を設定するための情報を追加
        $editRow = $addBillingType->execute($editRow);

        // 枝番の更新
        $editRow = $this->updateSequence($editRow);

		// セッションに入力内容を保持
        $sessionKey = $this->sessionKeyBase . $editRow[$this->sessionKeyId];
        $existingData = Session::get($sessionKey, []);
        $mergedData = array_merge($existingData, $editRow);
        Session::put($sessionKey, $mergedData);

        // ?params= に base64 エンコードした「'{"data_key_id":"$editRow['data_key_id']"}'」 を入れる
        $params = 'params=' . base64_encode('{"data_key_id":"'.$editRow[$this->sessionKeyId].'"}');
        // 確認画面へリダイレクト
        return redirect(route('order.order.notify', $params))->withInput($editRow);
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
            } else {
                // 顧客系ページから画面遷移の場合パラメータの変換
                $editRow = $decodeParams;
                if (isset($editRow['tel'])) {
                    $editRow['order_tel1'] = $editRow['tel'];
                }
                if (isset($editRow['name_kanji'])) {
                    $editRow['order_name'] = $editRow['name_kanji'];
                }
                if (isset($editRow['order_name_kana'])) {
                    $editRow['order_name_kana'] = $editRow['order_name_kana'];
                }
                if (isset($editRow['postal'])) {
                    $editRow['order_postal'] = str_replace('-', '', $editRow['postal']);
                }
                if (isset($editRow['address1'])) {
                    $editRow['order_address1'] = $editRow['address1'];
                }
                if (isset($editRow['address2'])) {
                    $editRow['order_address2'] = $editRow['address2'];
                }
                if (isset($editRow['address3'])) {
                    $editRow['order_address3'] = $editRow['address3'];
                }
                if (isset($editRow['address4'])) {
                    $editRow['order_address4'] = $editRow['address4'];
                }
                if (isset($editRow['email'])) {
                    $editRow['order_email'] = $editRow['email'];
                }
            }
            return $editRow;
        } else {
            return [];
        }
    }

    // m_cust_id から新規登録時情報を取得
    protected function getNewCustomerInfo($editRow, $customer)
    {
        $editRowItems = [
            'order_tel1' => 'tel1',
            'order_tel2' => 'tel2',
            'order_fax' => 'fax',
            'order_name_kana' => 'name_kana',
            'order_name' => 'name_kanji',
            'order_email1' => 'email1',
            'order_email2' => 'email2',
            'order_cust_runk_id' => 'm_cust_runk_id',
            'alert_cust_type' => 'alert_cust_type',
            'order_postal' => 'postal',
            'order_address1' => 'address1',
            'order_address2' => 'address2',
            'order_address3' => 'address3',
            'order_address4' => 'address4',
            'order_corporate_name' => 'corporate_kanji',
            'order_division_name' => 'division_name',
            'corporate_tel' => 'corporate_tel',
            'cust_note' => 'note',

            'm_cust_id_billing' => 'm_cust_id',
            'billing_tel1' => 'tel1',
            'billing_tel2' => 'tel2',
            'billing_fax' => 'fax',
            'billing_name_kana' => 'name_kana',
            'billing_name' => 'name_kanji',
            'billing_email1' => 'email1',
            'billing_email2' => 'email2',
            'billing_cust_runk_id' => 'm_cust_runk_id',
            'billing_alert_cust_type' => 'alert_cust_type',
            'billing_postal' => 'postal',
            'billing_address1' => 'address1',
            'billing_address2' => 'address2',
            'billing_address3' => 'address3',
            'billing_address4' => 'address4',
            'billing_corporate_name' => 'corporate_kanji',
            'billing_division_name' => 'division_name',
            'billing_corporate_tel' => 'corporate_tel',
            'billing_cust_note' => 'note',
        ];
        foreach ($editRowItems as $key => $value) {
            if (!isset($editRow[$key]) && isset($customer->$value)) {
                $editRow[$key] = $customer->$value;
            }
        }

        return $editRow;
    }

    protected function updateSequence($editRow)
    {
        // 枝番の更新
        $sequence = 1;
        foreach ($editRow['register_destination'] as $key => $destination) {
            $order_dtl_seq = 1;
            $editRow['register_destination'][$key]['sequence'] = $sequence;
            $sequence++;
            foreach ($destination['register_detail'] as $dkey => $item) {
                $editRow['register_destination'][$key]['register_detail'][$dkey]['order_dtl_seq'] = $order_dtl_seq;
                $order_dtl_seq++;
            }
        }
        return $editRow;
    }

    // OrderHdrModel を $editRow にセットする
    protected function setEditRow($editRow, $orderHdr)
    {
        // 社内メモ
        $editRow['operator_comment'] = $orderHdr->orderMemo->operator_comment;
        $editRow['billing_comment'] = $orderHdr->orderMemo->billing_comment;

        // 注文主情報
        $editRow['order_cust_runk_id'] = $orderHdr->cust->m_cust_runk_id;
        $editRow['alert_cust_type'] = $orderHdr->cust->alert_cust_type;
        $editRow['corporate_tel'] = $orderHdr->cust->corporate_tel;
        $editRow['cust_note'] = $orderHdr->cust->note;

        // 請求先情報
        if ($orderHdr->billingCust) {
            $editRow['billing_cust_runk_id'] = $orderHdr->billingCust->m_cust_runk_id;
            $editRow['billing_alert_cust_type'] = $orderHdr->billingCust->alert_cust_type;
            $editRow['billing_corporate_tel'] = $orderHdr->billingCust->corporate_tel;
            $editRow['billing_cust_note'] = $orderHdr->billingCust->note;
        }

        $editRow['m_pay_type_id'] = $orderHdr->m_payment_types_id;

        // 引当待以降は支払方法を変更不可
        $editRow['paytype_readonly'] = '';
        if(isset($orderHdr->progress_type) && $orderHdr->progress_type >= ProgressTypeEnum::PendingAllocation->value)
        {
            $editRow['paytype_readonly'] = 'readonly';
        }
        //返品の場合、支払方法入力可能
        if(isset($editRow['returnFlag']) && $editRow['returnFlag']){
            $editRow['paytype_readonly'] = '';
        }

        // orderHdr モデルのキー一覧を取得
        foreach ($orderHdr->toArray() as $key => $value) {
            $editRow[$key] = $value;
        }

        // orderDeatination
        $destination_count = 0;
        $editRow['register_destination'] = [];
        foreach ($orderHdr->orderDestination as $destinationModel) {
            $destination_count++;
            $destination = $destinationModel->toArray();
            $destination['destination_tab_display_name'] = $destination['destination_name'];
            $destination['standard_fee'] = 0;
            $destination['frozen_fee'] = 0;
            $destination['chilled_fee'] = 0;
            $destination['campaign_target_flg'] = $destination['campaign_flg']; // 登録時とフィールド名を揃えるため詰め替える
            $editRow['register_destination'][$destination_count] = $destination;

            // orderDtl
            $dtl_count = 0;
            $editRow['register_destination'][$destination_count]['register_detail'] = [];
            foreach ($destinationModel->orderDtl as $detailModel) {
                $dtl_count++;
                $detail = $detailModel->toArray();

                // TODO: ダミー項目
                $detail['t_order_dtl_sku_id'] = 0;
                $detail['cancel_flg'] = 0;
                $detail['variation_values'] = '';
                $detail['sell_checked'] = 0;
                $detail['three_temperature_zone_type'] = 0;
                $detail['drawing_status_name'] = '';
                $detail['sku_data'] = '';

                // m_ami_page の情報を追加
                $detail['image_path'] = $detailModel->amiEcPage->page->image_path;
                $detail['m_ami_page_id'] = $detailModel->amiEcPage->page->m_ami_page_id;
                $detail['page_desc'] = $detailModel->amiEcPage->page->page_desc;

                $detail['order_dtl_sku'] = $detailModel->orderDtlSku->toArray();
                if (isset($detail['order_dtl_sku'][0]['temperature_type'])) {
                    $detail['three_temperature_zone_type'] = $detail['order_dtl_sku'][0]['temperature_type'];
                } else {
                    $detail['three_temperature_zone_type'] = 0;
                }
                $detail['order_dtl_noshi'] = $detailModel->orderDtlNoshi;
                $detail['order_dtl_attachment_item'] = $detailModel->orderDtlAttachmentItem->toArray();
                $editRow['register_destination'][$destination_count]['register_detail'][$dtl_count] = $detail;
            }
        }

        return $editRow;
    }
}
