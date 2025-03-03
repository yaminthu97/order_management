<?php

namespace App\Modules\Order\Base;

use App\Modules\Order\Base\GetExtendDataInterface;

use App\Enums\CheckType;
use App\Enums\ProgressTypeEnum;

use App\Modules\Master\Base\Enums\AttentionTypeInterface;

class GetExtendData implements GetExtendDataInterface
{

	/**
	 * 確認区分
	 */
	protected $checkTypeNames = [
		'0' => '未確認',
		'2' => '確認済み',
		'9' => '対象外',
	];

	/**
	 * 与信区分
	 */
	protected $creditTypeNames = [
		'0' => '未処理',
		'1' => '与信NG',
		'2' => '与信OK',
		'9' => '対象外',
	];

	/**
	 * 入金区分
	 */
	protected $paymentTypeNames = [
		'0' => '未入金',
		'1' => '一部入金',
		'2' => '入金済み',
		'9' => '対象外',
	];

	/**
	 * 在庫引当区分
	 */
	protected $reservationTypeNames = [
		'0' => '未引当',
		'1' => '一部引当',
		'2' => '引当済み',
		'9' => '対象外',
	];

	/**
	 * 出荷指示区分
	 */
	protected $deliInstructTypeNames = [
		'0' => '未指示',
		'1' => '一部指示',
		'2' => '指示済み',
		'9' => '対象外',
	];

	/**
	 * 出荷確定区分
	 */
	protected $deliDecisionTypeNames = [
		'0' => '未確定',
		'1' => '一部確定',
		'2' => '確定済み',
		'9' => '対象外',
	];

	/**
	 * 決済売上計上区分
	 */
	protected $settlementSalesTypeNames = [
		'0' => '未計上',
		'1' => '計上NG',
		'2' => '計上済み',
		'9' => '対象外',
	];

	/**
	 * 売上ステータス反映区分
	 */
	protected $salesStatusTypeNames = [
		'0' => '未計上',
		'1' => '計上NG',
		'2' => '計上済み',
		'9' => '対象外',
	];

	/**
	 * 後払い.com請求書送付種別
	 */
	protected $cbBilledTypeNames = [
		'0' => '同梱',
		'1' => '別送',
	];

	/**
	 * 後払い.com決済ステータス
	 */
	protected $cbCreditStatusNames = [
		'0'		=>	'未処理',
		'10'	=>	'与信待ち',
		'11'	=>	'与信中',
		'12'	=>	'与信完了',
		'19'	=>	'与信NG',
		'90'	=>	'与信取消待ち',
		'91'	=>	'キャンセル完了',
		'99'	=>	'キャンセルNG',
	];

	/**
	 * 後払い.com出荷ステータス
	 */
	protected $cbDeliStatusNames = [
		'0'		=> '未処理',
		'10'	=> '出荷連携待ち',
		'11'	=> '出荷連携完了',
		'19'	=> '出荷連携NG',
	];

	/**
	 * 後払い.com請求書送付ステータス
	 */
	protected $cbBilledStatusNames = [
		'0'		=>	'未処理',
		'11'	=>	'印刷キュー転送完了',
		'12'	=>	'印刷キュー転送NG',
		'21'	=>	'印字情報取得完了',
		'22'	=>	'印字情報取得NG',
		'31'	=>	'発行報告完了',
		'32'	=>	'発行報告NG',
		'40'	=>	'請求書別送',
	];

	/**
	 * 与信データ出力用一覧
	 */
	protected $outputPaymentAuthCsvNames = [
		'expcsv_np_credit_regist' => 'NP与信登録',
	];

	/**
	 * 与信結果取込用一覧
	 */
	protected $inputPaymentAuthCsvNames = [
		// 200番台 NP
		'impcsv_np_credit_result' => 'NP与信結果',
	];

	/**
	 * 出荷報告データ出力用一覧
	 */
	protected $outputPaymentDeliveryCsvNames = [
		'outcsv_np_delivery_result' => 'NP出荷報告',
	];

	/**
	 * 出力帳票の一覧
	 */
	protected $outputPdfNames = [
		'exppdf_total_picking' => 'トータルピッキングリスト',
		'exppdf_detail_picking' => '個別ピッキングリスト',
		'exppdf_submission' => '納品書',
		'exppdf_direct_delivery_order_placement' => '直送発注書',
		'exppdf_receipt'=>'領収書',
//		'exppdf_sagawa_yuumail' => '飛脚ゆうメール便ラベル',
	];

    /**
     * 確認する指示タイムスタンプ
     */
    protected $outputCheckInstructTimestamp = [
        'exppdf_total_picking' => 'total_pick_instruct_datetime',
		'exppdf_detail_picking' => 'order_pick_instruct_datetime',
		'exppdf_submission' => 'deliveryslip_instruct_datetime',
		'exppdf_direct_delivery_order_placement' => 'purchase_order_instruct_datetime',
		'exppdf_receipt'=>'invoice_create_datetime',
    ];

    public function execute()
    {
        $attentionType = app(AttentionTypeInterface::class);
        $attentionTypeArray = collect($attentionType::cases())->mapWithKeys(fn($type)=> [$type->value => $type->label()])->prepend('','');

        $progressType = app(ProgressType::class);
        $progressTypeArray = collect($progressType::cases())->mapWithKeys(fn($type)=> [$type->value => $type->label()])->prepend('','');

        // 進捗区分と受注タグ別の受注件数の一覧
        //$orderCounts = $this->getOrderCounts();
        // 各受注件数の取得
        //$progressCounts = $orderCounts['progress_type'];
        // 受注タグの一覧
        //$orderTags = $orderCounts['order_tag'];
        //$orderTagList = $this->searchOrderTagMaster();
        //$orderTagRows = [];
        // 配送の一覧
        //$viewExtendData['delivery_count'] = $orderCounts['delivery'];
        // 受注取込ECサイトの一覧
        //$viewExtendData['input_order_csv_type'] = $this->inputOrderCsvEcType;
        //$this->inputOrderCsvEcShop = $this->searchEcs($featureId, true, array('m_ec_type'=>7,'delete_flg'=>0));
        //$viewExtendData['input_order_csv_shop'] = $this->inputOrderCsvEcShop;

        // 受注方法の一覧
        $getOrderTypesInterface = app(GetOrderTypesInterface::class);
        $viewExtendData['order_type_list'] = $getOrderTypesInterface->execute();

        // ECサイトの一覧
        $getEcsDetailInterface = app(GetEcslInterface::class);
        $viewExtendData['ec_list'] = $getEcsDetailInterface->execute(1);

        // 支払方法の一覧
        $viewExtendData['m_paytype_list'] = ['支払1'=>'1','支払2'=>'2'];

        // 社員マスタの一覧/
        $getEcsDetailInterface = app(GetEcsDetailInterface::class);
        $viewExtendData['m_operator_list'] = $getEcsDetailInterface->execute(1);
        $viewExtendData['m_operators'] = $getEcsDetailInterface->execute(1);

        // 配送方法の一覧/
        $getEcsDetailInterface = app(GetEcsDetailInterface::class);
        $viewExtendData['delivery_type_list'] = $getEcsDetailInterface->execute(1);

        // 顧客ランクの一覧
        $getCustomerRank = app(GetCustomerRankInterface::class);
        $viewExtendData['cust_runk_list'] = $getCustomerRank->execute();

        // 進捗区分の一覧/
        $getCustomerRank = app(GetCustomerRankInterface::class);
        $viewExtendData['progress_type_list'] = $getCustomerRank->execute();

        // 後払い.com請求書送付種別
        $viewExtendData['cb_billed_type_list'] = $this->cbBilledTypeNames;

        // 後払い.com決済ステータス
        $viewExtendData['cb_credit_status_list'] = $this->cbCreditStatusNames;

        // 後払い.com出荷ステータス
        $viewExtendData['cb_deli_status_list'] = $this->cbDeliStatusNames;

        // 後払い.com請求書送付ステータス
        $viewExtendData['cb_billed_status_list'] = $this->cbBilledStatusNames;

        // 決済ステータス
        $viewExtendData['settlement_sales_type_list'] = $this->settlementSalesTypeNames;

        // 倉庫マスタの一覧/
        $getEcsDetailInterface = app(GetEcsDetailInterface::class);
        $viewExtendData['m_warehouse_list'] = $getCustomerRank->execute(1);

        // メールテンプレートの一覧
        //$viewExtendData['m_mail_template_list'] = $this->searchMailTemplateInfo($featureId, true);

        // 入金対象にする支払方法の一覧
        //$viewExtendData['payment_paytype_list'] = $this->searchItemNameTypes($featureId, true, ['m_itemname_type' => 5, 'delete_flg' => 0]);

        // 与信出力CSVの一覧
        $viewExtendData['output_payment_auth_csv_list'] = $this->outputPaymentAuthCsvNames;

        // 与信取込の一覧
        $viewExtendData['input_payment_auth_csv_list'] = $this->inputPaymentAuthCsvNames;

        // 出荷報告CSVデータの一覧
        $viewExtendData['output_payment_delivery_result_csv_list'] = $this->outputPaymentDeliveryCsvNames;

        // 出荷CSVの一覧
        //$deliveryCsvTypes = $this->searchInvoiceSystem($featureId);

        // 送り状出力CSVの一覧
        //$viewExtendData['output_delivery_csv_list'] = $deliveryCsvTypes;

        // 出荷実績CSVの一覧
        //$viewExtendData['input_delivery_csv_list'] = $deliveryCsvTypes;

        // 受注お気に入り検索の一覧
        $getOrderListConditions = app(GetOrderListConditionsInterface::class);
        $viewExtendData['order_cond_list'] = $getOrderListConditions->execute();

        // 出力PDFの一覧
        $viewExtendData['output_pdf_list'] = $this->outputPdfNames;

        // 都道府県の一覧
        $getPrefecture = app(GetPrefecturalInterface::class);
        $viewExtendData['prefectural_list'] = $getPrefecture->execute();

        // ショップ一覧/
        $getPrefecture = app(GetPrefecturalInterface::class);
        $viewExtendData['shop_list'] =  $getPrefecture->execute();

        // 受注出力の一覧
        //$viewExtendData['output_order_csv_list'] = $this->outputOrderFlieType;

        // 配送希望時間帯の一覧
        $getOperatorsInterface = app(GetDeliveryTimeHopeMapInterface::class);
        $viewExtendData['delivery_hope_timezone_list'] = $getOperatorsInterface->execute();

        // 入金データの一覧
        //$viewExtendData['input_payment_csv_filetype_list'] = $this->inputPaymentCsvFiles;

        // 受注検索表示列の一覧
        //$viewExtendData['order_disp'] = $this->getOrderList($featureId);

        // キャンセル理由の一覧
        $getCancelReasonInterface = app(GetCancelReasonInterface::class);
        $viewExtendData['cancel_reason'] = $getCancelReasonInterface->execute();

        return $viewExtendData;
    }
}
