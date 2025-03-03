<?php
namespace App\Modules\Order\Base;

use App\Exceptions\DataNotFoundException;
use App\Models\Order\Base\OrderHdrModel;
use App\Models\Order\Base\OrderDestinationModel;
use App\Models\Order\Base\OrderDtlModel;
use App\Models\Order\Base\OrderDtlSkuModel;
use App\Models\Order\Base\OrderDtlNoshiModel;
use App\Models\Order\Base\OrderDtlAttachmentItemModel;
use App\Models\Order\Base\OrderMemoModel;

use App\Models\Master\Base\OperatorModel;
use App\Models\Master\Base\EcsModel;
use App\Models\Master\Base\PaymentTypeModel;
use App\Models\Common\Base\DeliveryTimeHopeModel;

use App\Services\EsmSessionManager;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;



class UpdateOrderCheck implements UpdateOrderCheckInterface
{
    /**
     * ESMセッション管理クラス
     */
    protected $esmSessionManager;


    protected $beforeOrderData;
    protected $errorMessages = [];

    public function __construct(
        EsmSessionManager $esmSessionManager
    ) {
        $this->esmSessionManager = $esmSessionManager;
    }

    /**
     * 受注情報更新
     *
     * @param ?int $orderId 受注基本ID
     * @param array $params 更新情報
     */
    public function execute(?int $orderId, array $params)
    {
        try{
            //共通チェック処理
            $this->checkRegisterColumnsCommon($params);
        
            //登録チェック処理
            if ($orderId === null) {
                $this->checkRegisterColumnsInsert($params);
            } else {
                //変更前受注データ取得
                $this->beforeOrderData = OrderHdrModel::findOrFail($orderId);
                if (isset($params['cancel_timestamp']) && strlen($params['cancel_timestamp']) > 0) {
                    //削除チェック処理
                    $this->checkRegisterColumnsDelete($params);
                } else {
                    //更新チェック処理
                    $this->checkRegisterColumnsUpdate($params);
                }
            }
        }catch(ModelNotFoundException $e){
            throw new DataNotFoundException('受注情報が見つかりませんでした。');
        }

        // $errorMessages が1つ以上あればエラーを返す
        if (count($this->errorMessages) > 0) {
            throw new \App\Exceptions\ModuleValidationException('バリデーションエラー', 0, null, $this->errorMessages);
        }

        return $params;
    }

    // 共通チェック処理
    private function checkRegisterColumnsCommon($params)
    {
        // 受注担当者が存在しない場合はエラー
        if (isset($params['order_operator_id'])) {
            if (!OperatorModel::where('m_operators_id', $params['order_operator_id'])->exists()) {
                $this->setError('受注担当者が見つかりません', 'order_operator_id', 'exists');
            }
        }
        // ECサイトが存在しない場合はエラー
        if (isset($params['m_ecs_id'])) {
            if (!EcsModel::where('m_ecs_id', $params['m_ecs_id'])->exists()) {
                $this->setError('ECサイトが見つかりません', 'm_ecs_id', 'exists');
            }
        }
        // 支払い方法が存在しない場合はエラー
        if (isset($params['m_pay_type_id'])) {
            if (!PaymentTypeModel::where('m_payment_types_id', $params['m_pay_type_id'])->exists()) {
                $this->setError('支払方法が見つかりません', 'm_pay_type_id', 'exists');
            }
        }
        //配送先情報チェック
        foreach ($params['register_destination'] as $key => $destination) {
            // 配送時間帯IDが適正かチェック
            if (isset($destination['m_delivery_time_hope_id'])) {
                $deliHopeTime = DeliveryTimeHopeModel::where('m_delivery_time_hope_id', $destination['m_delivery_time_hope_id'])->first();
                if ($deliHopeTime === null) {
                    $this->setError('配送時間帯が見つかりません', 'm_pay_type_id', 'exists');
                } else {
                    //配送時間帯文字列設定
                    $params['register_destination'][$key]['deli_hope_time_name'] = $deliHopeTime->delivery_time_hope_name;
                }
            }
            //foreach ($params['register_destination'][$key][''] as $key => $destination) {
            //受注明細チェック
                //商品購入金額の計算
                //ECページ
                //受注明細SKU
                    // ギフトチェック
                //受注明細重複チェック用に連番退避
            //受注明細連番重複チェック
        }
        //有効明細件数、削除明細件数を取得する
        //削除受注の場合
            //(なにもしない)
        //有効受注の場合
            //金額関連の妥当性チェック（各種金額の合計）
    }

    //登録チェック処理
    private function checkRegisterColumnsInsert($params)
    {
        //取消情報は設定不可
        if (isset($params['cancel_type']) && strlen($params['cancel_type']) > 0) {
            $this->setError('取消情報は設定できません', 'cancel_type', 'exists');
        }
		if (isset($params['cancel_note']) && strlen($params['cancel_note']) > 0) {
            $this->setError('取消情報は設定できません', 'cancel_note', 'exists');
        }
        foreach ($params['register_destination'] as $dkey => $destination) {
            //登録時は配送先IDは設定不可
            if (isset($destination['t_order_destination_id']) && strlen($destination['t_order_destination_id']) > 0) {
                $this->setError('配送先IDは設定できません', 't_order_destination_id', 'exists');
            }
            //明細が存在しない場合は次配送先へcontinue
            if (!isset($destination['register_detail'])) {
                continue;
            }
            foreach ($destination['register_detail'] as $dtlkey => $dtl) {
                //IDチェック
                if (isset($dtl['t_order_dtl_id']) && strlen($dtl['t_order_dtl_id']) > 0) {
                    $this->setError('受注明細IDは設定できません', 't_order_dtl_id', 'exists');
                }
                //取消明細チェック
                if (isset($dtl['cancel_timestamp']) && strlen($dtl['cancel_timestamp']) > 0) {
                    $this->setError('取消情報は設定できません', 'cancel_timestamp', 'exists');
                }
                //受注明細SKUが存在しない場合は次明細へcontinue
                if (!isset($dtl['register_detail_sku'])) {
                    continue;
                }
                $dtlSkuIndex = -1;
                // foreach(受注明細SKU)
                foreach ($dtl['register_detail_sku'] as $skukey => $sku) {
                    $dtlSkuIndex++;
                    if (isset($sku['t_order_dtl_sku_id']) && strlen($sku['t_order_dtl_sku_id']) > 0) {
                        $this->setError('受注明細SKU IDは設定できません', 't_order_dtl_sku_id', 'exists');
                    }
                }
            }
        }
    }

    // 削除チェック処理
    private function checkRegisterColumnsDelete($params)
    {
        //顧客IDは変更不可
        if ($params['t_order_hdr_id'] != $this->beforeOrderData['t_order_hdr_id']) {
            throw new \Exception(__('messages.error.invalid_parameter'));
        }
    }

    // 更新チェック処理
    private function checkRegisterColumnsUpdate($params)
    {
        //配送先データが取得できていなければエラーメッセージをセットして処理終了
        $beforeOrderDestination = $this->beforeOrderData->orderDestination()->get();
		if (!isset($beforeOrderDestination)) {
			$this->setError($this->errorMessages['orderDestinationNotFound'], 't_order_hdr_id', 'required');
            return;
        }
        //明細ID存在チェック
        // foreach(配送先)
            // foreach(受注明細)
                // 明細取り消しの場合
                    // 引き当て解除API
                //販売コード/数量変更データ
                    //引当済の場合は変更不可
                //在庫引当情報追加
        //顧客IDは変更不可
        //進捗区分チェック(与信)
            // 与信待ち(10)以降の場合、金額変更を伴う変更の場合エラー
        // foreach(配送先)
            // 出荷(40)以降の場合、配送方法や配送希望日変更の場合エラー
            // 与信待ち(10)以降の場合、金額変更を伴う変更の場合エラー
        //明細追加分在庫引当情報追加
        // foreach(配送先)
            // foreach(受注明細)
                // 明細ID未設定時
                    //在庫引当情報追加
    }

    private function setError($message, $column, $rule)
    {
        if (isset($this->errorMessages[$column])) {
            $this->errorMessages[$column][$rule] = $message;
        } else {
            $this->errorMessages[$column] = [];
            $this->errorMessages[$column][$rule] = $message;
        }
    }
}
