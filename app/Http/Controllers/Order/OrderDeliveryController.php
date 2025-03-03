<?php

namespace App\Http\Controllers\Order;

use App\Enums\ThreeTemperatureZoneTypeEnum;
use App\Http\Requests\Order\Base\UpdateOrderDeliveryRequest;
use App\Http\Requests\Order\Base\UpdateOrderDeliveryRequestInterface;
use App\Models\Master\Base\DeliveryTypeModel;
use App\Modules\Common\Base\SearchInvoiceSystemInterface;
use App\Modules\Master\Base\GetDeliveryTypesInterface;
use App\Modules\Master\Base\GetEcsDetail;
use App\Modules\Master\Base\GetSkusInterface;
use App\Modules\Master\Base\SearchEmailTemplateInterface;
use App\Modules\Master\Base\SearchShopsInterface;
use App\Modules\Order\Base\CheckOperatorAuthInterface;
use App\Modules\Order\Base\FindOrderDeliveryInterface;
use App\Modules\Order\Base\RetrieveDeliveryInfoInterface;
use App\Modules\Order\Base\UpdateOrderDelivery;
use App\Modules\Order\Base\UpdateOrderDeliveryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderDeliveryController
{
    public const GET_PARAM_KEY = 'params';

    /**
     * Show the delivery information of the order.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    // public function info(
    //     Request $request,
    //     RetrieveDeliveryInfoInterface $retrieveDeliveryInfo,
    //     GetDeliveryTypesInterface $getDeliveryTypes,
    //     GetSkusInterface $getSkus,
    //     GetEcsDetail $getEcsDetail,
    //     CheckOperatorAuthInterface $checkOperatorAuth,
    //     $id = 0,
    // ) {
    //     $editRow = [];

    //     $infoRow = $retrieveDeliveryInfo->execute((int)$id);

    //     $infoRow = array_merge($editRow, $infoRow);
    //     Log::info('infoRow: '. print_r($infoRow, true));

    //     $viewExtendData = [];
    //     $deliveryType = $getDeliveryTypes->execute($infoRow['m_deli_type_id']);
    //     Log::info('deliveryType: '. print_r($deliveryType, true));
    //     if (isset($deliveryType) && is_array($deliveryType) && count($deliveryType) > 0) {
    //         // 配送方法名
    //         $viewExtendData['m_delivery_type_name'] = $deliveryType[0]['m_delivery_type_name'];
    //         // 配送追跡確認URL
    //         $viewExtendData['delivery_tracking_url'] = $deliveryType[0]['delivery_tracking_url'];
    //     }

    //     // 送り状番号
    //     for ($i = 1; $i <= 5; $i++) {
    //         if (isset($infoRow['invoice_num' . $i]) && strlen($infoRow['invoice_num' . $i]) > 0) {
    //             $viewExtendData['invoice_info'][] = $infoRow['invoice_num' . $i];
    //         }
    //     }

    //     // 商品名
    //     $itemNames = [];
    //     if (isset($infoRow['register_detail_info01']) && count($infoRow['register_detail_info01']) > 0) {
    //         foreach ($infoRow['register_detail_info01'] as $sell) {
    //             if (!isset($sell['register_detail_info02']) || count($sell['register_detail_info02']) === 0) {
    //                 continue;
    //             }

    //             foreach ($sell['register_detail_info02'] as $item) {
    //                 // SKUマスタ情報取得
    //                 $responseRows = $getSkus->execute($infoRow['m_deli_type_id'], $item['item_id']);

    //                 if (!isset($responseRows) || !is_array($responseRows) || count($responseRows) === 0) {
    //                     continue;
    //                 }

    //                 $itemNames[$item['item_id']] = $responseRows[0]['sku_name'];
    //             }
    //         }
    //     }
    //     if (isset($itemNames) && count($itemNames) > 0) {
    //         $viewExtendData['item_names'] = $itemNames;
    //     }
    //     // ECサイトマスタのタイプ判定用
    //     $responseRows = $getEcsDetail->execute($infoRow['m_deli_type_id']);
    //     $ecsRows = [];
    //     if (isset($responseRows) && is_array($responseRows) && count($responseRows) > 0) {
    //         foreach ($responseRows as $work) {
    //             $ecsRows[$work['m_ecs_id']]['m_ec_type'] = $work['m_ec_type'];
    //             $ecsRows[$work['m_ecs_id']]['ec_type_uri'] = $work['ec_type_uri'];
    //         }
    //     }
    //     $viewExtendData['ecsRows'] = $ecsRows;
    //     // 商品コードのリンク設定
    //     $viewExtendData['authItem'] = $checkOperatorAuth->execute('30');
    //     // 販売コードのリンク設定
    //     $viewExtendData['authPage'] = $checkOperatorAuth->execute('40');

    //     //出荷確定日
    //     if (isset($infoRow["deli_decision_date"])) {
    //         $editRow["deli_decision_date"] = str_replace('-', '/', $infoRow['deli_decision_date']);
    //     }
    //     // 送り状番号
    //     for ($i = 1; $i <= 5; $i++) {
    //         if (isset($infoRow['invoice_num' . $i])) {
    //             $editRow['invoice_num' . $i] = $infoRow['invoice_num' . $i];
    //         }
    //     }
    //     // 個口数
    //     if (isset($infoRow["deli_package_vol"])) {
    //         $editRow['deli_package_vol'] = $infoRow['deli_package_vol'];
    //     }
    //     $errorResult = [];
    //     return view('order.order-delivery-info', compact('infoRow', 'viewExtendData', 'errorResult', 'editRow'));
    // }


    public function info(
        Request $request,
        FindOrderDeliveryInterface $findOrderDelivery,
        CheckOperatorAuthInterface $checkOperatorAuth,
    )
    {
        $delivery = $findOrderDelivery->execute($request->route('id'));

        $authItem = $checkOperatorAuth->execute('30');
        $authPage = $checkOperatorAuth->execute('40');

        return account_view('order.base.order-delivery-info', [
            'delivery' => $delivery,
            'authItem' => $authItem,
            'authPage' => $authPage,
        ]);
    }

    public function update(
        UpdateOrderDeliveryRequest $request,
        UpdateOrderDeliveryInterface $updateOrderDelivery
    )
    {
        $input = $request->validated();
        $orderDeliveryId = $request->route('id');

        $result = $updateOrderDelivery->execute($orderDeliveryId, $input);

        // info画面にリダイレクト
        session()->flash('messages.info', ['message' => __('messages.info.update_completed', ['data' => '出荷情報'])]);
        return redirect()->route('order.order-delivery.info', ['id' => $orderDeliveryId]);
    }

    // public function register(Request $request, $id = 0)
    // {
    //     $this->getSubmitName($request);

    //     $editRow = [];

    //     if(!empty($request->all())) {
    //         $editRow = $request->all();
    //         $editRow = $this->decodeGetParameter($editRow);
    //     }

    //     $editRow = $this->setNewPresetData($editRow, $request);

    //     $manager = $this->getManager($this->className);

    //     // 出荷済にする
    //     if($this->submitName == 'decision') {
    //         $editRow = $request->all();
    //         $editRow = $this->decodeGetParameter($editRow);

    //         // 出荷済登録API名実行
    //         $manager->setCurrentApiKey('registerDeliveryStatus');
    //         $resultRow = $manager->registerData($editRow);
    //         $manager->setCurrentApiKey('');

    //         if($resultRow['response']['result']['status'] > 0) {
    //             $errorResult = (array)$resultRow['response']['error'];
    //             $editRow['register_message'] = $resultRow['response']['error']['message'];
    //         } else {
    //             $editRow['register_message'] = '出荷済に更新しました。';
    //         }
    //     }

    //     $manager->setCurrentApiKey('searchDeliveryInfo');
    //     $infoRow = $manager->getInfoData((int)$id);
    //     $manager->setCurrentApiKey('');

    //     $infoRow = array_merge($editRow, $infoRow);

    //     $viewExtendData = $manager->setInfoExtendData($infoRow, $id);

    //     $editRow = $manager->editDisplayData($editRow, $infoRow);

    //     return view('order.order-delivery-info', compact('infoRow', 'viewExtendData', 'errorResult', 'editRow'));
    // }

    // public function update(Request $request, $id = 0)
    // {
    //     $this->getSubmitName($request);

    //     $editRow = [];

    //     if(!empty($request->all())) {
    //         $editRow = $request->all();
    //         $editRow = $this->decodeGetParameter($editRow);
    //     }

    //     $editRow = $this->setNewPresetData($editRow, $request);

    //     $manager = $this->getManager($this->className);

    //     // 個口数と送り状番号を更新する
    //     if($this->submitName == 'update') {
    //         $editRow = $request->all();
    //         $editRow = $this->decodeGetParameter($editRow);

    //         // 出荷済登録API名実行
    //         $manager->setCurrentApiKey('registerDeliveryStatus');
    //         $resultRow = $manager->registerData($editRow);
    //         $manager->setCurrentApiKey('');

    //         if($resultRow['response']['result']['status'] > 0) {
    //             $errorResult = (array)$resultRow['response']['error'];
    //             $editRow['register_message'] = $resultRow['response']['error']['message'];
    //         } else {
    //             $editRow['register_message'] = '出荷情報を更新しました。';
    //         }
    //     }


    //     $manager->setCurrentApiKey('searchDeliveryInfo');
    //     $infoRow = $manager->getInfoData((int)$id);
    //     $manager->setCurrentApiKey('');

    //     $infoRow = array_merge($editRow, $infoRow);

    //     $viewExtendData = $manager->setInfoExtendData($infoRow, $id);

    //     $editRow = $manager->editDisplayData($editRow, $infoRow);
    //     return view('order.order-delivery-info', compact('infoRow', 'viewExtendData', 'errorResult', 'editRow'));
    // }

    // public function cancel(Request $request, $id = 0)
    // {
    //     $this->getSubmitName($request);

    //     $editRow = [];

    //     if(!empty($request->all())) {
    //         $editRow = $request->all();
    //         $editRow = $this->decodeGetParameter($editRow);
    //     }

    //     $editRow = $this->setNewPresetData($editRow, $request);

    //     $manager = $this->getManager($this->className);

    //     // 出荷取消する
    //     if($this->submitName == 'cancel') {
    //         $editRow = $request->all();
    //         $editRow = $this->decodeGetParameter($editRow);

    //         // 受注出荷取消API実行
    //         $manager->setCurrentApiKey('registerOrderDeliveryCancel');
    //         $resultRow = $manager->registerData($editRow);
    //         $manager->setCurrentApiKey('');

    //         if($resultRow['response']['result']['status'] > 0) {
    //             $errorResult = (array)$resultRow['response']['error'];
    //             $editRow['register_message'] = $resultRow['response']['error']['message'];
    //         } else {
    //             // 出荷待に戻す
    //             $progressResult = $manager->registerOrderProgress($editRow['t_order_hdr_id'], 40, 'order/order-delivery/info/'. $id);

    //             if($progressResult['response']['result']['status'] > 0) {
    //                 $editRow['register_message'] = '出荷取消は完了しましたが、進捗区分の更新に失敗しました。';
    //             } else {
    //                 $editRow['register_message'] = '出荷を取り消しました。';
    //             }
    //         }
    //     }

    //     $manager->setCurrentApiKey('searchDeliveryInfo');
    //     $infoRow = $manager->getInfoData((int)$id);
    //     $manager->setCurrentApiKey('');

    //     $infoRow = array_merge($editRow, $infoRow);

    //     $viewExtendData = $manager->setInfoExtendData($infoRow, $id);

    //     $editRow = $manager->editDisplayData($editRow, $infoRow);
    //     return view('order.order-delivery-info', compact('infoRow', 'viewExtendData', 'errorResult', 'editRow'));
    // }

    /**
     * GETパラメータを複合する
     */
    protected function decodeGetParameter($requestRow)
    {
        if (isset($requestRow[$this::GET_PARAM_KEY]) && strlen($requestRow[$this::GET_PARAM_KEY]) > 0) {
            $b64Param = $requestRow[$this::GET_PARAM_KEY];
            $jsonParam = base64_decode($b64Param);
            $requestRow = array_merge($requestRow, (array)json_decode($jsonParam));
        }

        return $requestRow;
    }

    /**
     * 新規登録時の初期データを用意する
     */
    protected function setNewPresetData($editRow, $request = null)
    {
        return $editRow;
    }
}
