<?php

namespace App\Modules\Order\Gfh1207;

use App\Enums\CbBilledStatusEnum;

use App\Enums\CbBilledTypeEnum;
use App\Enums\CbCreditStatusEnum;
use App\Enums\CbDeliStatusEnum;
use App\Enums\ProgressTypeEnum;
use App\Enums\SettlementSalesTypeEnum;
use App\Enums\EcSiteInfoEnum;
use App\Enums\ItemNameType;
use App\Modules\Common\Base\SearchCampaignsInterface;
use App\Modules\Common\Base\SearchDeliveryCompanyTimeHopeInterface;
use App\Modules\Common\Base\SearchInvoiceSystemInterface;
use App\Modules\Master\Base\SearchEmailTemplateInterface;
use App\Modules\Common\Base\SearchPrefecturalInterface;
use App\Modules\Master\Base\GetOrderListDispsInterface;
use App\Modules\Master\Base\SearchDeliveryTypesInterface;
use App\Modules\Master\Base\SearchEcsInterface;
use App\Modules\Master\Base\SearchItemNameTypesInterface;
use App\Modules\Master\Base\SearchOperatorsInterface;
use App\Modules\Master\Base\SearchPaymentTypesInterface;

use App\Modules\Master\Base\SearchShopsInterface;
use App\Modules\Master\Base\SearchWarehousesInterface;
use App\Modules\Order\Base\GetExtendDataInterface;
use App\Modules\Order\Base\GetOrderCountsInterface;
use App\Modules\Order\Base\GetOrderListConditionsInterface;
use App\Modules\Order\Base\SearchOrderTagMasterInterface;

use App\Services\EsmSessionManager;

class GetExtendData implements GetExtendDataInterface
{
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
        'exppdf_receipt' => '領収書',
//        'exppdf_sagawa_yuumail' => '飛脚ゆうメール便ラベル',
    ];

    /**
     * 確認する指示タイムスタンプ
     */
    protected $outputCheckInstructTimestamp = [
        'exppdf_total_picking' => 'total_pick_instruct_datetime',
        'exppdf_detail_picking' => 'order_pick_instruct_datetime',
        'exppdf_submission' => 'deliveryslip_instruct_datetime',
        'exppdf_direct_delivery_order_placement' => 'purchase_order_instruct_datetime',
        'exppdf_receipt' => 'invoice_create_datetime',
    ];

    /**
     * 受注取込のECサイト種類
     */
    protected $inputOrderCsvEcType = [
        // 1	=> 'Yahooショッピング',
        // 2	=> 'Yahooオークション',
        // 3	=> '楽天市場',
        // 4	=> 'アマゾン',
        // 5	=> 'Wowma!',
        1 => '標準',
        7 => 'FutureShop',
    ];

    /**
     * 受注データ出力の一覧
     */
    protected $outputOrderFlieType = [
        'expcsv_order_detail'	=> '受注明細',
        'expcsv_order_sku'		=> '受注商品',
        'expcsv_order_update'	=> '受注一括編集',
    ];


    /**
     * 入金データ取込形式
     */
    protected $inputPaymentCsvFiles = [
        1 => '標準（入金額＋入金者）',
        2 => '標準（入金額＋備考の注文ID、氏名）',
        3 => 'ジャパンネット銀行',
    ];

    /**
     * Input系バッチの銀行入金データのキュー名（input_payment_result_csv）変更用
     */
    protected $inputBatchExecutingTypesAdd = [
        1  => 'stdin',   // 標準形式(入金額+入金者)
        2  => 'stdin',   // 標準形式(入金額+備考の注文IDと氏名)
        3  => 'jnbin',   // ジャパンネット銀行（JNB）形式
    ];

    protected $inputOrderCsvEcShop;

    protected $getOrderCounts;
    protected $searchOrderTagMaster;
    protected $searchEcs;
    protected $searchItemNameTypes;
    protected $searchPaymentTypes;
    protected $searchOperators;
    protected $searchDeliveryTypes;
    protected $searchWarehouses;
    protected $searchEmailTemplate;
    protected $searchInvoiceSystem;
    protected $getOrderListConditions;
    protected $searchPrefectual;
    protected $searchShops;
    protected $searchDeliveryCompanyTimeHope;
    protected $searchCampaigns;
    protected $getOrderList;
    protected $esmSessionManager;

    public function __construct(
        GetOrderCountsInterface $getOrderCounts,
        SearchOrderTagMasterInterface $searchOrderTagMaster,
        SearchEcsInterface $searchEcs,
        SearchItemNameTypesInterface $searchItemNameTypes,
        SearchPaymentTypesInterface $searchPaymentTypes,
        SearchOperatorsInterface $searchOperators,
        SearchDeliveryTypesInterface $searchDeliveryTypes,
        SearchWarehousesInterface $searchWarehouses,
        SearchEmailTemplateInterface $searchEmailTemplate,
        SearchInvoiceSystemInterface $searchInvoiceSystem,
        GetOrderListConditionsInterface $getOrderListConditions,
        SearchPrefecturalInterface $searchPrefectual,
        SearchShopsInterface $searchShops,
        SearchDeliveryCompanyTimeHopeInterface $searchDeliveryCompanyTimeHope,
        SearchCampaignsInterface $searchCampaigns,
        GetOrderListDispsInterface $getOrderList,
        EsmSessionManager $esmSessionManager
    ) {
        $this->getOrderCounts = $getOrderCounts;
        $this->searchOrderTagMaster = $searchOrderTagMaster;
        $this->searchEcs = $searchEcs;
        $this->searchItemNameTypes = $searchItemNameTypes;
        $this->searchPaymentTypes = $searchPaymentTypes;
        $this->searchOperators = $searchOperators;
        $this->searchDeliveryTypes = $searchDeliveryTypes;
        $this->searchWarehouses = $searchWarehouses;
        $this->searchEmailTemplate = $searchEmailTemplate;
        $this->searchInvoiceSystem = $searchInvoiceSystem;
        $this->getOrderListConditions = $getOrderListConditions;
        $this->searchPrefectual = $searchPrefectual;
        $this->searchShops = $searchShops;
        $this->searchDeliveryCompanyTimeHope = $searchDeliveryCompanyTimeHope;
        $this->searchCampaigns = $searchCampaigns;
        $this->getOrderList = $getOrderList;
        $this->esmSessionManager = $esmSessionManager;
    }

    public function execute($type = 'list')
    {
        if ($type == 'list') {
            return $this->getListExtendData();
        } elseif ($type == 'edit') {
            return $this->getEditExtendData();
        } elseif ($type == 'info') {
            return $this->getInfoExtendData();
        }
    }

    public function getListExtendData()
    {
        $viewExtendData = [];


        /*
         * モジュールにより取得
         **/

        // 進捗区分と受注タグ別の受注件数の一覧
        $orderCounts = $this->getOrderCounts->execute(['m_account_id' => $this->esmSessionManager->getAccountId()]);

        // 各受注件数の取得
        $progressTypes = collect(ProgressTypeEnum::cases())->map(fn ($type) => $type->value);
        $progressCounts = $orderCounts['progress_type'];
        foreach($progressTypes as $progressType) {
            $viewExtendData['order_progress_count'][$progressType] = [
                'total' => $progressCounts[$progressType]['total'],
                'today' => $progressCounts[$progressType]['today'],
            ];
        }

        // 受注タグの一覧
        $orderTags = $orderCounts['order_tag'];
        $orderTagList = $this->searchOrderTagMaster->execute();
        $orderTagRows = [];
        foreach($orderTagList as $orderTag) {
            $orderTagRow = $orderTag;
            // 受注件数の取得
            $orderTagOrderCount = 0;
            if(isset($orderTags[$orderTag['m_order_tag_id']])) {
                $orderTagOrderCount = $orderTags[$orderTag['m_order_tag_id']];
            }
            $orderTagRow['order_count'] = $orderTagOrderCount;
            // タグの色コードは念のため小文字にしておく
            $orderTagRow['tag_color'] = strtolower($orderTag['tag_color']);
            $orderTagRows[] = $orderTagRow;
        }
        $viewExtendData['m_tag_list'] = $orderTagRows;

        // 配送の一覧
        $viewExtendData['delivery_count'] = $orderCounts['delivery'];

        // 受注取込ECサイトの一覧
        $viewExtendData['input_order_csv_type'] = $this->inputOrderCsvEcType;
        $this->inputOrderCsvEcShop = $this->searchEcs->execute(['m_account_id' => $this->esmSessionManager->getAccountId(), 'm_ec_type' => EcSiteInfoEnum::FUTURESHOP->value], ['should_selection' => true]);
        $viewExtendData['input_order_csv_shop'] = $this->inputOrderCsvEcShop;

        // 受注方法の一覧
        $viewExtendData['order_type_list'] = $this->searchItemNameTypes->execute(['m_itemname_type' => ItemNameType::ReceiptType->value,'m_account_id' => $this->esmSessionManager->getAccountId()]);

        // ECサイトの一覧（[m_ecs_id=>value, m_ec_name=>value, m_ec_type=>value]）
        $m_ecs = $this->searchEcs->execute(['m_account_id' => $this->esmSessionManager->getAccountId()]);
        $viewExtendData['ec_list'] = [];
        foreach ($m_ecs as $ecs) {
            $viewExtendData['ec_list'][] = ['m_ecs_id' => $ecs['m_ecs_id'], 'm_ec_name' => $ecs['m_ec_name'], 'm_ec_type' => $ecs['m_ec_type']];
        }

        // ECサイトの一覧（key=>value）
        $viewExtendData['m_ecs'] = $this->searchEcs->execute(['m_account_id' => $this->esmSessionManager->getAccountId()], ['should_selection' => true]);

        // 支払方法の一覧
        $viewExtendData['m_paytype_list'] = $this->searchPaymentTypes->execute(['m_account_id' => $this->esmSessionManager->getAccountId()]);

        // 社員マスタの一覧
        $viewExtendData['m_operator_list'] = $this->searchOperators->execute(['m_account_id' => $this->esmSessionManager->getAccountId()]);

        // 配送方法の一覧
        $viewExtendData['delivery_type_list'] = $this->searchDeliveryTypes->execute(['m_account_id' => $this->esmSessionManager->getAccountId()]);

        // 顧客ランクの一覧
        $viewExtendData['cust_runk_list'] = $this->searchItemNameTypes->execute(['m_itemname_type' => ItemNameType::CustomerRank->value, 'm_account_id' => $this->esmSessionManager->getAccountId()]);

        // 倉庫マスタの一覧
        $viewExtendData['m_warehouse_list'] = $this->searchWarehouses->execute(['m_account_id' => $this->esmSessionManager->getAccountId()]);

        // メールテンプレートの一覧
        $viewExtendData['m_mail_template_list'] = $this->searchEmailTemplate->execute(['m_account_id' => $this->esmSessionManager->getAccountId()]);

        // 入金対象にする支払方法の一覧
        $viewExtendData['payment_paytype_list'] = $this->searchItemNameTypes->execute(['m_itemname_type' => ItemNameType::Deposit->value, 'm_account_id' => $this->esmSessionManager->getAccountId()]);

        // 出荷CSVの一覧
        $deliveryCsvTypes = $this->searchInvoiceSystem->execute(['m_account_id' => $this->esmSessionManager->getAccountId()]);

        // 受注お気に入り検索の一覧
        $viewExtendData['order_cond_list'] = $this->getOrderListConditions->execute([]);

        // 都道府県の一覧
        $viewExtendData['prefectural_list'] = $this->searchPrefectual->execute();

        // ショップ一覧
        $viewExtendData['shop_list'] = $this->searchShops->execute(['m_account_id' => $this->esmSessionManager->getAccountId()]);

        // 配送希望時間帯の一覧
        $viewExtendData['delivery_hope_timezone_list'] = $this->searchDeliveryCompanyTimeHope->execute();

        // キャンペーンの一覧
        $viewExtendData['m_campaign_list'] =  $this->searchCampaigns->execute(['m_account_id' => $this->esmSessionManager->getAccountId()]);

        // 販売窓口
        $viewExtendData['m_sales_counter_list'] = $this->searchItemNameTypes->execute(['m_itemname_type' => ItemNameType::SalesContact->value, 'm_account_id' => $this->esmSessionManager->getAccountId()]);

        // 受注検索表示列の一覧
        $featureId = '/order/order/list';
        $viewExtendData['order_disp'] = $this->getOrderList->execute($featureId);

        // 付属品カテゴリ
        $viewExtendData['attachment_category'] = $this->searchItemNameTypes->execute(['m_itemname_type' => ItemNameType::AttachmentCategory->value, 'm_account_id' => $this->esmSessionManager->getAccountId()]);

        // 付属品グループ
        $viewExtendData['attachment_group'] = $this->searchItemNameTypes->execute(['m_itemname_type' => ItemNameType::AttachmentGroup->value, 'm_account_id' => $this->esmSessionManager->getAccountId()]);

        /*
         * Enums
         **/
        // 進捗区分の一覧
        $viewExtendData['progress_type_list'] = [];
        foreach (ProgressTypeEnum::cases() as $case) {
            $viewExtendData['progress_type_list'][$case->value] = $case->label();
        }

        // 後払い.com請求書送付種別
        $viewExtendData['cb_billed_type_list'] = collect(CbBilledTypeEnum::cases())->map(fn ($type) => $type->label());

        // 後払い.com決済ステータス
        $viewExtendData['cb_credit_status_list'] = collect(CbCreditStatusEnum::cases())->map(fn ($type) => $type->label());

        // 後払い.com出荷ステータス
        $viewExtendData['cb_deli_status_list'] = collect(CbDeliStatusEnum::cases())->map(fn ($type) => $type->label());

        // 後払い.com請求書送付ステータス
        $viewExtendData['cb_billed_status_list'] = collect(CbBilledStatusEnum::cases())->map(fn ($type) => $type->label());

        // 決済ステータス
        $viewExtendData['settlement_sales_type_list'] = collect(SettlementSalesTypeEnum::cases())->map(fn ($type) => $type->label());

        //$attentionType = app(AttentionTypeInterface::class);
        //$attentionTypeArray = collect($attentionType::cases())->mapWithKeys(fn($type)=> [$type->value => $type->label()])->prepend('','');

        /*
         * プロパティ
         **/
        // 与信出力CSVの一覧
        $viewExtendData['output_payment_auth_csv_list'] = $this->outputPaymentAuthCsvNames;

        // 与信取込の一覧
        $viewExtendData['input_payment_auth_csv_list'] = $this->inputPaymentAuthCsvNames;

        // 出荷報告CSVデータの一覧
        $viewExtendData['output_payment_delivery_result_csv_list'] = $this->outputPaymentDeliveryCsvNames;

        // 送り状出力CSVの一覧
        $viewExtendData['output_delivery_csv_list'] = $deliveryCsvTypes;

        // 出荷実績CSVの一覧
        $viewExtendData['input_delivery_csv_list'] = $deliveryCsvTypes;

        // 出力PDFの一覧
        $viewExtendData['output_pdf_list'] = $this->outputPdfNames;

        // 受注出力の一覧
        $viewExtendData['output_order_csv_list'] = $this->outputOrderFlieType;

        // 入金データの一覧
        $viewExtendData['input_payment_csv_filetype_list'] = $this->inputPaymentCsvFiles;

        // 認証情報
        $viewExtendData['m_operators_id'] = $this->esmSessionManager->getOperatorId();

        // 配送リードタイム
        $viewExtendData['delivery_readtime'] = 1; // TODO GFH_DEV-4 取得処理を追加

        return $viewExtendData;
    }

    public function getEditExtendData()
    {
        $viewExtendData = $this->getListExtendData();
        // 社員マスタの変換
        $viewExtendData['m_operators'] = collect($viewExtendData['m_operator_list'])->mapWithKeys(fn ($operator) => [$operator['m_operators_id'] => $operator['m_operator_name']]);

        // 受注方法の変換
        $viewExtendData['m_ordertypes'] = collect($viewExtendData['order_type_list'])->mapWithKeys(fn ($operator) => [$operator['m_itemname_types_id'] => $operator['m_itemname_type_name']]);

        // 支払方法の変換
        $viewExtendData['m_payment_types'] = collect($viewExtendData['m_paytype_list'])->mapWithKeys(fn ($operator) => [$operator['m_payment_types_id'] => $operator['m_payment_types_name']]);

        // 都道府県の変換
        $viewExtendData['m_prefectures'] = collect($viewExtendData['prefectural_list'])->mapWithKeys(fn ($operator) => [$operator['m_prefectural_id'] => $operator['prefectual_name']]);

        $viewExtendData['m_delivery_time_hope'] = [];

        return $viewExtendData;
    }

    public function getInfoExtendData()
    {
        $viewExtendData = $this->getListExtendData();

        // 付属品カテゴリ
        $viewExtendData['attachment_category'] = $this->searchItemNameTypes->execute(['m_itemname_type' => ItemNameType::AttachmentCategory->value, 'm_account_id' => $this->esmSessionManager->getAccountId()], ['should_selection' => true]);
        
        // 付属品グループ
        $viewExtendData['attachment_group'] = $this->searchItemNameTypes->execute(['m_itemname_type' => ItemNameType::AttachmentGroup->value, 'm_account_id' => $this->esmSessionManager->getAccountId()], ['should_selection' => true]);

        // 熨斗サイズ
        $viewExtendData['noshi_size'] = $this->searchItemNameTypes->execute(['m_itemname_type' => ItemNameType::NoshiSize->value, 'm_account_id' => $this->esmSessionManager->getAccountId()], ['should_selection' => true]);

        // 顧客対応ステータス
        $viewExtendData['customer_support'] = $this->searchItemNameTypes->execute(['m_itemname_type' => ItemNameType::CustomerSupportStatus->value, 'm_account_id' => $this->esmSessionManager->getAccountId()], ['should_selection' => true]);

        // 顧客対応連絡方法
        $viewExtendData['customer_contact'] = $this->searchItemNameTypes->execute(['m_itemname_type' => ItemNameType::CustomerContact->value, 'm_account_id' => $this->esmSessionManager->getAccountId()], ['should_selection' => true]);

        // 顧客対応分類
        $viewExtendData['customer_support_type'] = $this->searchItemNameTypes->execute(['m_itemname_type' => ItemNameType::CustomerSupportType->value, 'm_account_id' => $this->esmSessionManager->getAccountId()], ['should_selection' => true]);
        return $viewExtendData;

    }
}
