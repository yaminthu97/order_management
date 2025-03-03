<?php
namespace App\Modules\Order\Base;

use App\Models\Order\Base\OrderHdrModel;
use App\Models\Order\Base\DrawingInfoModel;
use Illuminate\Support\Facades\DB;

use App\Enums\CommentCheckTypeEnum;
use App\Enums\AlertCustCheckTypeEnum;
use App\Enums\AddressCheckTypeEnum;
use App\Enums\DeliHopeDateCheckTypeEnum;
use App\Enums\CreditTypeEnum;
use App\Services\EsmSessionManager;

use App\Exceptions\DataNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UpdateOrderInfo implements UpdateOrderInfoInterface
{
    /**
     * ESMセッション管理クラス
     */
    protected $esmSessionManager;

    public function __construct(EsmSessionManager $esmSessionManager)
    {
        $this->esmSessionManager = $esmSessionManager;
    }

    /**
     * 受注照会更新
     *
     * @param array $params 更新情報
     */
    public function execute(array $params)
    {
        try{
            $order = OrderHdrModel::findOrFail($params['t_order_hdr_id']);
            // トランザクション開始
            $order = DB::transaction(function () use ($order, $params) {
                $nowDateTime = date("Y-m-d H:i:s");
                $order->update_operator_id = $this->esmSessionManager->getOperatorId();

                // 社内メモ更新
                if ($params['submit']  == 'update_operator_comment') {
                    $order->orderMemo->operator_comment = $params['operator_comment'];
                    $order->orderMemo->save();
                    $order->save();
                }

                // コメント確認済み
                if ($params['submit']  == 'comment_check') {
                    $order->comment_check_type = CommentCheckTypeEnum::CONFIRMED->value;
                    $order->comment_check_datetime = $nowDateTime;
                    $order->save();
                }
                
                // 領収書宛名、但し書き登録
                if ($params['submit']  == 'receipt_direction_and_proviso') {
                    $order->receipt_direction = $params['receipt_direction'];
                    $order->receipt_proviso = $params['receipt_proviso'];
                    $order->save();
                }

                // 要注意顧客確認済み
                if ($params['submit']  == 'alert_cust_check') {
                    $order->alert_cust_check_type = AlertCustCheckTypeEnum::CONFIRMED->value;
                    $order->alert_cust_check_datetime = $nowDateTime;
                    $order->save();
                }
                
                // 住所確認済み
                if ($params['submit']  == 'address_check') {
                    $order->address_check_type = AddressCheckTypeEnum::CONFIRMED->value;
                    $order->address_check_datetime = $nowDateTime;
                    $order->save();
                }
                
                // 指定配送日確認済み
                if ($params['submit']  == 'deli_hope_date_check') {
                    $order->deli_hope_date_check_type = DeliHopeDateCheckTypeEnum::CONFIRMED->value;
                    $order->deli_hope_date_check_datetime = $nowDateTime;
                    $order->save();
                }

                // 与信区分変更（与信OKにする）
                if ($params['submit']  == 'credit_check') {
                    $order->credit_type = CreditTypeEnum::CREDIT_OK->value;
                    $order->credit_datetime = $nowDateTime;
                    $order->save();
                }

                // 強制出荷
                if ($params['submit']  == 'forced_deli') {
                    // 引当 t_drawing_info を取得
                    $drawingInfo = DrawingInfoModel::where('order_id', $order->t_order_hdr_id);
                    // すべての引き当ての forced_delivery を 1 にする
                    $drawingInfo->update(['forced_delivery' => 1]);
                }

                return $order;
            });
        }catch(ModelNotFoundException $e){
            throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => '受注照会', 'id' => $params['t_order_hdr_id'] ?? '']), 0, $e);
        }

        return $order;
    }
}
