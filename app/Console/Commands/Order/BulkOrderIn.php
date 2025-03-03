<?php

namespace App\Console\Commands\Order;

use Exception;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Enums\DeleteFlg;
use App\Enums\ItemNameType;
use App\Enums\ProgressTypeEnum;
use App\Enums\BatchExecuteStatusEnum;
use App\Enums\CampaignFlgEnum;

use App\Models\Ami\Base\AmiSkuModel;
use App\Models\Ami\Base\AmiPageModel;
use App\Models\Ami\Base\AmiEcPageModel;
use App\Models\Ami\Base\AmiPageSkuModel;
use App\Models\Ami\Base\AmiEcPageSkuModel;
use App\Models\Ami\Base\AmiPageAttachmentItemModel;

use App\Models\Cc\Base\CustOrderSumModel;
use App\Models\Master\Base\EcsModel;
use App\Models\Master\Base\NoshiModel;
use App\Models\Master\Base\NoshiDetailModel;
use App\Models\Master\Base\PostalCodeModel;
use App\Models\Master\Base\PrefecturalModel;
use App\Models\Master\Base\PaymentTypeModel;
use App\Models\Master\Base\DeliveryTypeModel;
use App\Models\Master\Base\ItemnameTypeModel;
use App\Models\Order\Base\OrderDtlNoshiModel;
use App\Models\Order\Base\OrderDtlAttachmentItemModel;

use App\Models\Warehouse\Base\WarehouseModel;

use App\Models\Common\Base\DeliveryCompanyModel;
use App\Models\Common\Base\DeliveryTimeHopeModel;
use App\Models\Common\Base\DeliveryCompanyTimeHopeModel;

use App\Models\Cc\Gfh1207\CustModel;
use App\Models\Order\Gfh1207\OrderHdrModel;
use App\Models\Order\Base\OrderMemoModel;
use App\Models\Order\Base\DestinationModel;
use App\Models\Order\Gfh1207\OrderDestinationModel;
use App\Models\Order\Gfh1207\OrderDetailModel;
use App\Models\Order\Gfh1207\OrderDetailSkuModel;

use App\Modules\Master\Gfh1207\Enums\BatchListEnum;
use App\Modules\Master\Gfh1207\Enums\StoreAggregationGroupEnum;

use App\Modules\Master\Base\GetYmstTimeInterface;
use App\Modules\Claim\Base\UpdateBillingHdrInterface;
use App\Modules\Customer\Base\SearchCustomerInterface;
use App\Modules\Order\Base\SearchDeliveryFeesInterface;
use App\Modules\Order\Base\RegisterOrderTagAutoInterface;

use App\Modules\Warehouse\Base\SearchWarehousesInterface;

use App\Modules\Common\Base\SearchNoshiDetailInterface;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;

use App\Modules\Master\Gfh1207\Enums\PaymentTypeEnum;

use App\Services\ExcelReportManager;
use App\Services\TenantDatabaseManager;


class BulkOrderIn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:BulkOrderIn {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    private const PRIVATE_THROW_ERR_CODE = -1;

    private const FROM_SHEET_NAME = '注文書【表紙】';
    private const FROM_CUSTOMER_ID_CELL = 'H4'; //②顧客ID
    private const FROM_BILLING_ADDRESS_CELL = 'H7'; //③送り主名(ご請求先）（全角16文字）
    private const FROM_BILLING_ADDRESS_KANA_CELL = 'H6'; //④送り主名(ご請求先）（かな）
    private const FROM_PHONE_NUMBER_CELL = 'AM7'; //⑤電話番号
    private const FROM_POSTAL_CODE_CELL = 'D10'; //⑥郵便番号
    private const FROM_PREFECTURES_CELL = 'P10'; //⑦都道府県（全角4文字）
    private const FROM_MUNICIPALITIES_CELL = 'AD10'; //⑧市区町村（全角12文字）
    private const FROM_HOUSE_NUMBER_CELL = 'D13'; //⑨番地（全角16文字）
    private const FROM_NAME_OF_BUILDING_COMPANY_DEPARTMENT_CELL = 'AC13'; //⑩建物名・会社名・部署名など（全角16文字）

    private const FROM_ITEM_NO1_CELL = 'C20'; //⑫品番1
    private const FROM_VOLUME1_CELL = 'AF20'; //⑬数量1
    private const FROM_NOSHI1_CELL = 'AN20'; //⑭熨斗紙1
    private const FROM_HANDBAG1_CELL = 'AR20'; //⑮手提げ1
    private const FROM_ITEM_NO2_CELL = 'C22'; //⑯品番2
    private const FROM_VOLUME2_CELL = 'AF22'; //⑰数量2
    private const FROM_NOSHI2_CELL = 'AN22'; //⑱熨斗紙2
    private const FROM_HANDBAG2_CELL = 'AR22'; //⑲手提げ2
    private const FROM_ITEM_NO3_CELL = 'C24'; //⑳品番3
    private const FROM_VOLUME3_CELL = 'AF24'; //㉑数量3
    private const FROM_NOSHI3_CELL = 'AN24'; //㉒熨斗紙3
    private const FROM_HANDBAG3_CELL = 'AR24'; //㉓手提げ3
    private const FROM_DESIRED_DELIVERY_DATE_CELL = 'AV20'; //㉔お届け希望日
    private const FROM_DESIRED_DELIVERY_TIME_CELL = 'AV24'; //㉕お届け希望時間
    private const FROM_NOSHI_ENVELOPE_CELL = 'C27'; //㉖のし表書き
    private const FROM_COMPANY_NAME_CELL = 'N27'; //㉗会社名
    private const FROM_JOB_TITLE_CELL = 'AD27'; //㉘肩書き
    private const FROM_NAME_CELL = 'AQ27'; //㉙名前
    private const FROM_PAYMENT_METHOD_CELL = 'A32'; //㉚お支払方法

    private const HANDBAG_WANT = '有';

    private const TO_SHEET_NAME = '注文書【配送先】';
    private const TO_NOSHI_ENVELOPE_CELL = 'B4'; //㊹のし表書き
    private const TO_COMPANY_NAME_CELL = 'E4'; //㊺会社名
    private const TO_JOB_TITLE_CELL = 'G4'; //㊻肩書き
    private const TO_NAME_CELL = 'H4'; //㊼名前
    private const TO_DESIRED_DELIVERY_DATE_CELL = 'I4'; //㊽お届け希望日
    private const TO_DESIRED_DELIVERY_TIME_CELL = 'J4'; //㊾お届け希望時間

    private const TO_ROW_INDEX = 8;
    private const TO_PHONE_NUMBER_COLUMN_POS = 'B'; //お届け先一覧[複数行] ㉜電話番号
    private const TO_POSTAL_CODE_COLUMN_POS = 'C'; //お届け先一覧[複数行] ㉝郵便番号
    private const TO_PREFECTURES_COLUMN_POS = 'D'; //お届け先一覧[複数行] ㉞都道府県
    private const TO_MUNICIPALITIES_COLUMN_POS = 'E'; //お届け先一覧[複数行] ㉟市区町村
    private const TO_HOUSE_NUMBER_COLUMN_POS = 'F'; //お届け先一覧[複数行] ㊱番地
    private const TO_BUILDING_NAME_COLUMN_POS = 'G'; //お届け先一覧[複数行] ㊲建物名
    private const TO_SHIPPING_ADDRESS1_COLUMN_POS = 'H'; //お届け先一覧[複数行] ㊳配送先名（会社名・部署名・役職・氏名）
    private const TO_SHIPPING_ADDRESS2_COLUMN_POS = 'I'; //お届け先一覧[複数行] ㊴配送先名が16文字以上の場合①
    private const TO_SHIPPING_ADDRESS3_COLUMN_POS = 'J'; //お届け先一覧[複数行] ㊵配送先名が16文字以上の場合②
    private const TO_ITEM_NO_COLUMN_POS = 'K'; //お届け先一覧[複数行] ㊶品番
    private const TO_VOLUME_COLUMN_POS = 'N'; //お届け先一覧[複数行] ㊷数量
    private const TO_NOSHI_COLUMN_POS = 'R'; //お届け先一覧[複数行] ㊸熨斗紙

    private const REDUCE_TAX = 0.08;
    private const STANDARD_TAX = 0.1;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '大口注文入力・取込する';
    // テンプレートファイルパス
    protected $templateFilePath;
    // 開始時処理
    protected $startBatchExecute;
    // 終了時処理
    protected $endBatchExecute;
    // 検索モジュール
    protected $searchCustomer;

    protected $registerOrderTagAuto;

    protected $updateBillingHdr;

    /**
     * Create a new command instance.
     */
    public function __construct(
        StartBatchExecuteInstructionInterface $startBatchExecute,
        EndBatchExecuteInstructionInterface $endBatchExecute,
        GetYmstTimeInterface $getYmstTime,
        SearchCustomerInterface $searchCustomer,
        SearchNoshiDetailInterface $searchNoshiDetail,
        RegisterOrderTagAutoInterface $registerOrderTagAuto,
        UpdateBillingHdrInterface $updateBillingHdr,
        SearchWarehousesInterface $searchWarehouses,
        SearchDeliveryFeesInterface $searchDeliveryFees,
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->getYmstTime = $getYmstTime;
        $this->searchCustomer = $searchCustomer;
        $this->searchNoshiDetail = $searchNoshiDetail;
        $this->registerOrderTagAuto = $registerOrderTagAuto;
        $this->updateBillingHdr = $updateBillingHdr;
        $this->searchWarehouses = $searchWarehouses;
        $this->searchDeliveryFees = $searchDeliveryFees;
        parent::__construct();
    }

    private function setTenantConnection($accountCode)
    {
        $TenantConnectionValue = $accountCode . '_db';

        if (app()->environment('testing')) {
            // テスト環境の場合
            $TenantConnectionValue = $accountCode . '_db_testing';
        }

        TenantDatabaseManager::setTenantConnection($TenantConnectionValue);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $executeBatchInstructionId = $this->argument('t_execute_batch_instruction_id');
        $json = $this->argument('json');

        try {
            /**
             * [共通処理] 開始処理
             * バッチ実行指示テーブル（t_execute_batch_instruction ）に新規作成と開始処理
             * - バッチ開始時刻
             */
            $batchExecute = $this->startBatchExecute->execute($executeBatchInstructionId);

            if (!is_null($json)) {
                $jsonData = json_decode($json, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception(json_last_error_msg());
                }
            }
        } catch (Exception $e) {
            Log::error($e);

            $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => $e->getMessage(),
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value
            ]);

            return;
        }

        if (BatchListEnum::IMPXLSX_BULK_ORDER->value !== $batchExecute->execute_batch_type) {
            $errorMessage = __('messages.error.invalid_parameter');
            Log::error($errorMessage);

            $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => $errorMessage,
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value
            ]);

            return;
        }

        $accountCode = $batchExecute->account_cd;
        $this->setTenantConnection($accountCode);

        $executeCondition = json_decode($batchExecute->execute_conditions, true);
        $keyname = 'upload_file_name';

        if (!isset($executeCondition[$keyname])) {
            $errorMessage = __('messages.error.invalid_parameter');
            Log::error($errorMessage);

            $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => $errorMessage,
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value
            ]);

            return;
        }

        $accountId = $batchExecute->m_account_id;

        try {
            $this->templateFilePath = $this->getTemplateFileName(
                $executeBatchInstructionId,
                $accountCode,
                $executeCondition[$keyname]
            );

            $erm = new ExcelReportManager($this->templateFilePath);
            $spreadSheet = $erm->getSpreadSheet();

            $spreadSheetData = $this->getSpreadSheetValues($spreadSheet);

            $fromSpreadSheetData = $this->fromSpreadSheetValidity($accountId, $spreadSheetData);
            $spreadSheetData = $fromSpreadSheetData['spread_sheet'];
            $errorMessageList = $fromSpreadSheetData['error_message_list'];

            $toErrorMessageList = $this->toSpreadSheetValidity($accountId, $spreadSheetData['to']);

            $errorMessageList = array_merge($errorMessageList, $toErrorMessageList);

            if (0 < count($errorMessageList)) {
                throw new Exception(implode(PHP_EOL, $errorMessageList));
            }
        } catch (Exception $e) {
            Log::error($e);

            $errorMessage = $e->getMessage();

            $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => $errorMessage,
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value
            ]);

            return;
        }

        $custId = '';

        if (isset($spreadSheetData['from']['base']['m_cust_id'])) {
            $custId = $spreadSheetData['from']['base']['m_cust_id'];
        }

        $spreadSheetData['from']['base']['m_account_id'] = $accountId;

        $operatorsId = $batchExecute->m_operators_id;
        $spreadSheetData['from']['base']['entry_operator_id'] = $operatorsId;
        $spreadSheetData['from']['base']['update_operator_id'] = $operatorsId;

        $executeResultStrAry = [];

        DB::beginTransaction();

        try {
            $customer = $this->getCustomer($accountId, $custId);

            if (!$this->existCustomerId($custId) && $this->existCustomer($customer) === false) {
                DB::rollBack();

                $errorMessage = __('messages.error.data_not_found', ['data' => 'customer', 'id' => $custId]);
                Log::error($errorMessage);

                $this->endBatchExecute->execute($batchExecute, [
                    'execute_result' => $errorMessage,
                    'execute_status' => BatchExecuteStatusEnum::FAILURE->value
                ]);

                return;
            }

            $customerInsertOrUpdateValue = $this->getCustomerCreateOrUpdateValue($custId);

            $ecsId = $this->getEcsId($accountId);

            $paymentMethod = $spreadSheetData['from']['base']['paymentMethod'];
            unset($spreadSheetData['from']['base']['paymentMethod']);
            $paymentTypesId = $this->getPaymentTypesId($accountId, $paymentMethod);
            $receiptItemnameTypesId = $this->getitemnameTypesId($accountId, ItemNameType::ReceiptType);
            $salesitemnameTypesId = $this->getitemnameTypesId($accountId, ItemNameType::SalesContact);

            $tmp = $this->saveCustomer($customer, $operatorsId, $customerInsertOrUpdateValue, $spreadSheetData['from']['base']);

            $custOrderSum = CustOrderSumModel::where(['m_account_id' => $accountId, 'm_cust_id' => $tmp['instance']->m_cust_id])->first();
            $orderCount = 1;

            if (!is_null($custOrderSum)) {
                $orderCount += $custOrderSum->total_order_count;
            }

            $discountRate = $tmp['instance']->discount_rate;
            $executeResultStrAry[] = $tmp['message'];
            $retOrderHdr = $this->saveOrderHdr($tmp['instance'], $ecsId, $receiptItemnameTypesId, $salesitemnameTypesId, $paymentTypesId, $orderCount);
            $executeResultStrAry[] = $retOrderHdr['message'];
            $orderHdr = $retOrderHdr['instance'];
            $tmp = $this->saveOrderDestination($orderHdr, $spreadSheetData, $discountRate);
            $executeResultStrAry[] = $tmp['message'][0];
            $executeResultStrAry[] = $tmp['message'][1];

            $orderHdrId = $orderHdr->t_order_hdr_id;
            $new = 1;
            //注文登録受付API(請求金額がマイナスでない場合)
            //受注タグ付与判定API 新規作成
            $this->registerOrderTagAuto->execute($orderHdrId, $new);
            $this->updateBillingHdr->execute($orderHdrId);

            /**
             * [共通処理] 終了処理
             * バッチ実行指示テーブルの該当バッチに以下の情報を書き込む
             * - バッチ終了時刻
             * - (実行状態)
             * - (実行結果)
             * - (格納ファイルパス)
             */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => implode(' ', $executeResultStrAry),
                'execute_status' => BatchExecuteStatusEnum::SUCCESS->value,
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            Log::error($e);
            // default fail message
            $errorMessage = BatchExecuteStatusEnum::FAILURE->label();

            // If there is an Exception error message, write it to the log
            if ($e->getCode() == self::PRIVATE_THROW_ERR_CODE) {
                $errorMessage = $e->getMessage();
            }

            /**
             * [共通処理] 終了処理
             * バッチ実行指示テーブルの該当バッチに以下の情報を書き込む
             * - バッチ終了時刻
             * - (実行状態)
             * - (実行結果)
             */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => $errorMessage,
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value
            ]);
        }
    }

    private function saveOrderDtlSku($dtlData, $volume, $sku, $itemVol, $warehouse)
    {
        $v = $dtlData;
        $v += [
            'order_sell_vol' => $volume,
            'item_id' => $sku->m_ami_sku_id,
            'item_cd' => $sku->sku_cd,
            'item_vol' => $itemVol,
            'm_supplier_id' => $sku->m_suppliers_id,
            'temperature_type' => $sku->three_temperature_zone_type,
            'order_bundle_type' => $sku->including_package_flg,
            'direct_delivery_type' => $sku->direct_delivery_flg,
            'gift_type' => $sku->gift_flg,
            'item_cost' => $sku->item_cost,
            'm_warehouse_id' => $warehouse->m_warehouses_id,
        ];

        OrderDetailSkuModel::create($v);
    }

    private function saveOrderDtlAttachmentItems($dtlData, $volume, $sellId, $attachmentItemGroupId, $handbag)
    {
        $attachmentList = AmiPageAttachmentItemModel::with(['attachmentItem', 'category'])
            ->where(['m_ami_page_id' => $sellId, 'group_id' => $attachmentItemGroupId])->get();

        foreach ($attachmentList as $val) {
            if ($val['attachmentItem']->attachment_item_cd == 1 && self::HANDBAG_WANT != $handbag) {    //1 : 手提げ Enum 定義待ち
                continue;
            }

            $v = $dtlData;
            $v += [
                'order_sell_vol' => $volume,
                'attachment_item_id' => $val['attachmentItem']->m_ami_attachment_item_id,
                'attachment_item_cd' => $val['attachmentItem']->attachment_item_cd,
                'attachment_item_name' => $val['attachmentItem']->attachment_item_name,
                'attachment_vol' => $val->item_vol,
                'group_id' => $val->group_id,
                'category_id' => $val->category_id,
                'display_flg' => $val['attachmentItem']->display_flg,
                'invoice_flg' => $val['attachmentItem']->invoice_flg,
            ];

            OrderDtlAttachmentItemModel::create($v);
        }
    }

    private function saveOrderDtlNoshi($dtlData, $volume, $noshiDetailId, $noshiSheetData)
    {
        $accountId = $dtlData['m_account_id'];
        $noshi = null;
        $v = [];

        if ($noshiDetailId != '') {
            $noshiDetail = NoshiDetailModel::where([
                'm_account_id' => $accountId,
                'delete_flg' => DeleteFlg::Use->value,
                'm_noshi_detail_id' => $noshiDetailId
            ])->first();

            if (is_null($noshiDetail)) {
                throw new Exception('熨斗詳細 ID error.');
            }

            $noshiId = $noshiDetail->m_noshi_id;

            $noshi = NoshiModel::where([
                'm_account_id' => $accountId,
                'delete_flg' => DeleteFlg::Use->value,
                'm_noshi_id' => $noshiId
            ])->first();

            if (is_null($noshi)) {
                return;
            }

            $v = [
                'noshi_id' => $noshiId,
                'noshi_detail_id' => $noshiDetailId,
                'noshi_type' => $noshi->noshi_type,
                'attachment_item_group_id' => $noshi->attachment_item_group_id,
                'm_noshi_naming_pattern_id' => $noshiDetail->m_noshi_naming_pattern_id
            ];
        }

        if (is_null($noshi)) {
            return;
        }

        $v += $dtlData;
        $v += $noshiSheetData;
        $v['count'] = $volume;
        $v['attach_flg'] = 1; //固定で 1 を入れる。

        OrderDtlNoshiModel::create($v);
    }

    private function saveOrderDtl($orderDestination, $ec, $sku, $orderDtlSeq, $ecsId, $itemNo, $volume, $attachmentItemGroupId, $noshiDetailId, $noshiSheetData, $handbag)
    {
        $salesPrice = $ec->sales_price;
        $taxRate = $ec->tax_rate;
        $sellId = $ec->m_ami_ec_page_id;

        $v = [
            'm_account_id' => $orderDestination->m_account_id,
            't_order_hdr_id' => $orderDestination->t_order_hdr_id,
            't_order_destination_id' => $orderDestination->t_order_destination_id,
            'order_destination_seq' => $orderDestination->order_destination_seq,
            'order_dtl_seq' => $orderDtlSeq,
            'ecs_id' => $ecsId,
            'sell_id' => $sellId,
            'sell_cd' => $itemNo,
            'sell_name' => $ec->ec_page_title,
            'order_sell_price' => $salesPrice,
            'order_cost' => $sku->item_cost * $volume,
            'order_time_sell_vol' => $volume,
            'order_sell_vol' => $volume,
            'tax_rate' => $taxRate,
            'tax_price' => $salesPrice * $taxRate,
            'attachment_item_group_id' => $attachmentItemGroupId,
            'entry_operator_id' => $orderDestination->update_operator_id,
            'update_operator_id' => $orderDestination->update_operator_id
        ];

        $oDtlObj = OrderDetailModel::create($v);

        $dtlData = [
            'm_account_id' => $oDtlObj->m_account_id,
            't_order_hdr_id' => $oDtlObj->t_order_hdr_id,
            't_order_destination_id' => $oDtlObj->t_order_destination_id,
            'order_destination_seq' => $oDtlObj->order_destination_seq,
            't_order_dtl_id' => $oDtlObj->t_order_dtl_id,
            'order_dtl_seq' => $oDtlObj->order_dtl_seq,
            'ecs_id' => $oDtlObj->ecs_id,
            'sell_cd' => $oDtlObj->sell_cd,
            'entry_operator_id' => $oDtlObj->update_operator_id,
            'update_operator_id' => $oDtlObj->update_operator_id
        ];

        $page = AmiPageModel::where(['m_ami_page_id' => $ec->m_ami_page_id])->first();
        $pageSku = AmiPageSkuModel::where(['m_ami_page_id' => $page->m_ami_page_id])->first();
        $itemVol = $pageSku->item_vol;

        $this->saveOrderDtlNoshi($dtlData, $volume, $noshiDetailId, $noshiSheetData);
        $this->saveOrderDtlAttachmentItems($dtlData, $volume, $sellId, $attachmentItemGroupId, $handbag);
        
        // m_warehouse_priority が一番小さい倉庫1件を取得
        $warehouse = WarehouseModel::orderBy('m_warehouse_priority')->first();
        $this->saveOrderDtlSku($dtlData, $volume, $sku, $itemVol, $warehouse);

        return $oDtlObj;
    }

    private function calcTotalPrice($ec, $sku, $deliveryType, $volume, $result)
    {
        $result['sell_total_price'] += $ec->sales_price * $volume;

        if ($ec->tax_rate == self::REDUCE_TAX) {
            $result['reduce_total_price'] += $ec->sales_price * $volume;
        }
        elseif ($ec->tax_rate == self::STANDARD_TAX) {
            $result['standard_total_price'] += $ec->sales_price * $volume;
        }

        $temperatureZoneType = $sku->three_temperature_zone_type; //0:常温 1:冷凍 2:冷蔵
        $standard = 0;
        $frozen = 1;
        $chilled = 2;

        $feeList = [
            $standard => $deliveryType->standard_fee,
            $frozen => $deliveryType->frozen_fee,
            $chilled => $deliveryType->chilled_fee,
        ];

        $fee = $deliveryType->standard_fee;

        if (isset($feeList[$temperatureZoneType])) {
            $fee = $feeList[$temperatureZoneType];
        }

        $result['delivery_type_fee'] = $fee;

        return $result;
    }

    private function createOrderDestination($accountId, $orderHdr, $deliveryType, $row, $orderDestinationSeq, $excelHopeDate, $desiredDeliveryTime, $shippingFee)
    {
        $destination = $this->createDestination($accountId, $orderHdr, $row);

        $deliveryCompany = DeliveryCompanyModel::where('delivery_company_cd', $deliveryType->delivery_type)->first();
        $deliveryCompanyTimeHope = DeliveryCompanyTimeHopeModel::where([
            'delivery_company_time_hope_name' => $desiredDeliveryTime,
            'm_delivery_company_id' => $deliveryCompany->m_delivery_company_id,
        ])->first();
        $deliveryTimeHope = DeliveryTimeHopeModel::where('m_delivery_time_hope_id', $deliveryCompanyTimeHope->m_delivery_time_hope_id)->first();

        $warehouse = WarehouseModel::oldest('m_warehouse_priority')->first();
        $ymstTime = $this->getYmstTime->execute($warehouse->m_warehouses_id, $row['destination_postal']);
        $deliveryDays = $ymstTime->delivery_days;

        $hopeDate = $this->getDesiredDeliveryDate($excelHopeDate)->format('Y-m-d');
        $deliPlanDate = Carbon::parse($hopeDate)->subDays($deliveryDays)->format('Y-m-d');

        $v = $row;
        $v += [
            'm_account_id' => $accountId,
            'destination_id' => $destination->m_destination_id,
            't_order_hdr_id' => $orderHdr->t_order_hdr_id,
            'order_destination_seq' => $orderDestinationSeq,
            'deli_hope_date' => $hopeDate,
            'deli_hope_time_name' => $desiredDeliveryTime,
            'm_delivery_time_hope_id' => $deliveryTimeHope->m_delivery_time_hope_id,
            'deli_hope_time_cd' =>  $deliveryTimeHope->deli_hope_time_cd,
            'm_delivery_type_id' => $deliveryType->m_delivery_type_id,
            'shipping_fee' => $shippingFee,
            'deli_plan_date' => $deliPlanDate,
            'entry_operator_id' => $orderHdr->update_operator_id,
            'update_operator_id' => $orderHdr->update_operator_id
        ];

        $oDObj = OrderDestinationModel::create($v);
        return $oDObj;
    }

    private function createDestination($accountId, $orderHdr, $row)
    {
        $destination = DestinationModel::firstOrNew([
            'cust_id' => $orderHdr->m_cust_id,
            'destination_name' => $row['destination_name'],
            'destination_tel' => $row['destination_tel']
        ]);
        if (!$destination->entry_operator_id) {
            $destination->entry_operator_id = $orderHdr->update_operator_id;
        }

        $v = [
            'm_account_id' => $accountId,
            'cust_id' => $orderHdr->m_cust_id,
            'destination_name' => $row['destination_name'],
            'destination_tel' => $row['destination_tel'],
            'destination_postal' => $row['destination_postal'] ?? null,
            'destination_address1' => $row['destination_address1'] ?? null,
            'destination_address2' => $row['destination_address2'] ?? null,
            'destination_address3' => $row['destination_address3'] ?? null,
            'destination_address4' => $row['destination_address4'] ?? null,
            'destination_company_name' => $row['destination_company_name'] ?? null,
            'destination_division_name' => $row['destination_division_name'] ?? null,
            'update_operator_id' => $orderHdr->update_operator_id
        ];

        $destination->fill($v);
        $destination->save();

        return $destination;
    }

    private function getNoshiAttachmentItemGroupId($accountId, $noshiDetailId)
    {
        $noshiDetail = NoshiDetailModel::where([
            'm_account_id' => $accountId,
            'delete_flg' => DeleteFlg::Use->value,
            'm_noshi_detail_id' => $noshiDetailId
        ])->first();

        if (is_null($noshiDetail)) {
            return null;
        }

        $noshi = NoshiModel::where([
            'm_account_id' => $accountId,
            'delete_flg' => DeleteFlg::Use->value,
            'm_noshi_id' => $noshiDetail->m_noshi_id
        ])->first();

        if (is_null($noshi)) {
            return null;
        }

        return $noshi->attachment_item_group_id;
    }

    private function getAttachmentGroupItemnameTypeId($accountId)
    {
        $itemnameTypeId = ItemnameTypeModel::where([
            'm_account_id' => $accountId,
            'delete_flg' => DeleteFlg::Use->value,
            'm_itemname_type' => ItemNameType::AttachmentGroup->value,
        ])->orderBy('m_itemname_type_sort', 'ASC')->first()->m_itemname_types_id;

        return $itemnameTypeId;
    }

    private function saveOrderDestination($orderHdr, $spreadSheetData, $discountRate)
    {
        $dtlCnt = count($spreadSheetData['from']['orderDtl']['itemNo']);
        $accountId = $orderHdr->m_account_id;

        $deliveryTypeYamato = 100;
        $deliveryType = DeliveryTypeModel::where([
            'm_account_id' => $accountId,
            'delete_flg' => DeleteFlg::Use->value,
            'delivery_type' => $deliveryTypeYamato
        ])->first();

        $shippingFeeTotal = 0;
        $orderDestinationSeq = 1;

        if (0 < $dtlCnt) {
            $fromHopeDate = $this->getDesiredDeliveryDate($spreadSheetData['from']['orderDestination']['desiredDeliveryDate'])->format('Y-m-d');
            $fromDesiredDeliveryTime = $spreadSheetData['from']['orderDestination']['desiredDeliveryTime'];

            $shippingFee = $this->getShippingFee($accountId, $deliveryType, $orderHdr->order_address1);
            $shippingFeeTotal += $shippingFee;

            $row = [
                'destination_tel' => $orderHdr->order_tel1,
                'destination_postal' => $orderHdr->order_postal,
                'destination_address1' => $orderHdr->order_address1,
                'destination_address2' => $orderHdr->order_address2,
                'destination_address3' => $orderHdr->order_address3,
                'destination_address4' => $orderHdr->order_address4,
                'destination_name' => $orderHdr->order_name,
                'campaign_flg' => CampaignFlgEnum::SUBJECT->value,
            ];
 
            $oDObj = $this->createOrderDestination($accountId, $orderHdr, $deliveryType, $row, $orderDestinationSeq, $fromHopeDate, $fromDesiredDeliveryTime, $shippingFee);
            ++$orderDestinationSeq;
        }

        $totalPriceRow = [
            'sell_total_price' => 0,
            'reduce_total_price' => 0,
            'standard_total_price' => 0,
        ];

        $orderDtlSeq = 1;
        $fromDeliveryTypeFee = 0;
        $deliveryTypeFeeTotal = 0;

        foreach ($spreadSheetData['from']['orderDtl']['itemNo'] as $i => $itemNo) {
            $volume = $spreadSheetData['from']['orderDtl']['volume'][$i];
            $noshiDetailId = $spreadSheetData['from']['orderDtl']['noshi'][$i];

            $ec = AmiEcPageModel::where(['m_account_id' => $accountId, 'm_ecs_id' => $orderHdr->m_ecs_id, 'ec_page_cd' => $itemNo])->first();

            if (is_null($ec)) {
                throw new Exception('AmiEcPage error.');
            }

            $ecSku = AmiEcPageSkuModel::where('m_ami_ec_page_id', $ec->m_ami_ec_page_id)->first();
            $sku = AmiSkuModel::where('m_ami_sku_id', $ecSku->m_ami_sku_id)->first();

            $totalPriceRow = $this->calcTotalPrice($ec, $sku, $deliveryType, $volume, $totalPriceRow);

            //温度帯別手数、一つ目の商品の金額を入れる。
            if ($fromDeliveryTypeFee == 0 && isset($totalPriceRow['delivery_type_fee'])) {
                $fromDeliveryTypeFee = $totalPriceRow['delivery_type_fee'];
                $deliveryTypeFeeTotal += $fromDeliveryTypeFee;
                unset($totalPriceRow['delivery_type_fee']);
            }

            $attachmentItemGroupId = $this->getNoshiAttachmentItemGroupId($accountId, $noshiDetailId);

            if (is_null($attachmentItemGroupId)) {
                $attachmentItemGroupId = $this->getAttachmentGroupItemnameTypeId($accountId);
            }

            $handbag = $spreadSheetData['from']['orderDtl']['handbag'][$i];
            $this->saveOrderDtl($oDObj, $ec, $sku, $orderDtlSeq, $orderHdr->m_ecs_id, $itemNo, $volume, $attachmentItemGroupId, $noshiDetailId, $spreadSheetData['from']['noshi'], $handbag);
            ++$orderDtlSeq;
        }

        $instance = [];

        if (isset($oDObj)) {
            $oDObj->payment_fee = $fromDeliveryTypeFee;
            $oDObj->save();
            $instance[] = $oDObj;
        }

        $orderDestinationList = $spreadSheetData['to']['orderDestinationList'];
        $toDesiredDeliveryDate = $spreadSheetData['to']['orderDestination']['desiredDeliveryDate'];
        $toDesiredDeliveryTime = $spreadSheetData['to']['orderDestination']['desiredDeliveryTime'];

        $toHopeDate = $this->getDesiredDeliveryDate($toDesiredDeliveryDate)->format('Y-m-d');

        $toDeliveryTypeFee = 0;

        foreach ($orderDestinationList as $row) {
            $row['destination_name'] = $row['shippingAddress1'];
            $row['destination_company_name'] = $row['shippingAddress2'];
            $row['destination_division_name'] = $row['shippingAddress3'];
            unset($row['shippingAddress1']);
            unset($row['shippingAddress2']);
            unset($row['shippingAddress3']);

            $itemNo = $row['itemNo'];
            $volume = $row['volume'];
            unset($row['itemNo']);
            unset($row['volume']);

            $noshiDetailId = $row['noshi'];
            unset($row['noshi']);

            $shippingFee = $this->getShippingFee($accountId, $deliveryType, $row['destination_address1']);
            $shippingFeeTotal += $shippingFee;

            if ($volume == 1) { //一品一葉対応
                $row['gp1_type'] = 1;
            }
            
            $row['campaign_flg'] = CampaignFlgEnum::EXCLUDED->value;

            $oDObj = $this->createOrderDestination($accountId, $orderHdr, $deliveryType, $row, $orderDestinationSeq, $toDesiredDeliveryDate, $toDesiredDeliveryTime, $shippingFee);
            ++$orderDestinationSeq;

            $ec = AmiEcPageModel::where(['m_account_id' => $accountId, 'm_ecs_id' => $orderHdr->m_ecs_id, 'ec_page_cd' => $itemNo])->first();

            if (is_null($ec)) {
                throw new Exception('AmiEcPage error.');
            }

            $ecSku = AmiEcPageSkuModel::where('m_ami_ec_page_id', $ec->m_ami_ec_page_id)->first();
            $sku = AmiSkuModel::where('m_ami_sku_id', $ecSku->m_ami_sku_id)->first();

            $attachmentItemGroupId = $this->getNoshiAttachmentItemGroupId($accountId, $noshiDetailId);

            if (is_null($attachmentItemGroupId)) {
                $attachmentItemGroupId = $this->getAttachmentGroupItemnameTypeId($accountId);
            }

            $this->saveOrderDtl($oDObj, $ec, $sku, $orderDtlSeq, $orderHdr->m_ecs_id, $itemNo, $volume, $attachmentItemGroupId, $noshiDetailId, $spreadSheetData['to']['noshi'], self::HANDBAG_WANT);
            ++$orderDtlSeq;
            ++$dtlCnt;

            $totalPriceRow = $this->calcTotalPrice($ec, $sku, $deliveryType, $volume, $totalPriceRow);

            //温度帯別手数、一つ目の商品の金額を入れる。
            if ($toDeliveryTypeFee == 0 && isset($totalPriceRow['delivery_type_fee'])) {
                $toDeliveryTypeFee = $totalPriceRow['delivery_type_fee'];
                $deliveryTypeFeeTotal += $toDeliveryTypeFee;
                unset($totalPriceRow['delivery_type_fee']);
            }

            if (isset($oDObj)) {
                $oDObj->payment_fee = $toDeliveryTypeFee;
                $oDObj->save();
            }   

            $instance[] = $oDObj;
        }

        $orderHdr->sell_total_price = $totalPriceRow['sell_total_price'];

        $orderHdr->shipping_fee = $shippingFeeTotal;
        $orderHdr->delivery_type_fee = $deliveryTypeFeeTotal;
        $paymentFee = $deliveryTypeFeeTotal;
        $orderHdr->payment_fee = $paymentFee;
        $standardTotalPrice = $totalPriceRow['standard_total_price'] + $shippingFeeTotal + $paymentFee;
        $orderHdr->standard_total_price = $standardTotalPrice;

        $standardDiscount = $standardTotalPrice * $discountRate;
        $orderHdr->standard_discount = $standardDiscount;

        $standardTaxPrice = floor(($standardTotalPrice - $standardDiscount) * self::STANDARD_TAX);
        $orderHdr->standard_tax_price = $standardTaxPrice;

        $reduceTotalPrice = $totalPriceRow['reduce_total_price'];
        $orderHdr->reduce_total_price = $reduceTotalPrice;

        $reduceDiscount = $reduceTotalPrice * $discountRate;
        $orderHdr->reduce_discount = $reduceDiscount;

        $reduceTaxPrice = floor(($reduceTotalPrice - $reduceDiscount) * self::REDUCE_TAX);
        $orderHdr->reduce_tax_price = $reduceTaxPrice;

        $discount = ceil($standardDiscount + $reduceDiscount);
        $orderHdr->discount = $discount;
        $orderHdr->tax_price = $standardTaxPrice + $reduceTaxPrice;

        $orderHdr->order_total_price = $standardTotalPrice + $reduceTotalPrice + $standardTaxPrice + $reduceTaxPrice - $discount;

        $orderHdr->save();

        $message = __('messages.info.create_completed', ['data' => '受注詳細' . $dtlCnt . '件']);
        $cnt = count($instance);

        return [
            'instance' => $instance,
            'message' => [__('messages.info.create_completed', ['data' => '送付先' . $cnt . '件']), $message]
        ];
    }

    private function getShippingFee($accountId, $deliveryType, $prefectualName)
    {
        $prefectural = PrefecturalModel::where('prefectual_name', $prefectualName)->first();
        $prefectureId = $prefectural->m_prefectural_id;

        $warehouseId = $this->searchWarehouses->execute([
            'm_account_id' => $accountId,
            'delete_flg' => DeleteFlg::Use->value], [
            'sorts' => ['m_warehouse_priority' => 'ASC'],
        ])->first()->m_warehouses_id;

        $deliveryFees = $this->searchDeliveryFees->execute([
            'm_account_id' => $accountId,
            'm_warehouse_id' => $warehouseId,
            'm_prefecture_id' => $prefectureId,
            'm_delivery_type_id' => $deliveryType->m_delivery_type_id
        ])->first();

        return $deliveryFees->delivery_fee;
    }

    private function saveOrderHdr($customer, $ecsId, $orderType, $salesStore, $paymentTypesId, $orderCount)
    {
        $row = [
            'm_account_id' => $customer->m_account_id,
            'order_operator_id' => $customer->update_operator_id,
            'order_type' => $orderType,
            'sales_store' => $salesStore,
            'm_ecs_id' => $ecsId,
            'm_cust_id' => $customer->m_cust_id,
            'order_tel1' => $customer->tel1,
            'order_tel2' => $customer->tel2,
            'order_fax' => $customer->fax,
            'order_email1' => $customer->email1,
            'order_email2' => $customer->email2,
            'order_postal' => $customer->postal,
            'order_address1' => $customer->address1,
            'order_address2' => $customer->address2,
            'order_address3' => $customer->address3,
            'order_address4' => $customer->address4,
            'order_corporate_name' => $customer->corporate_name,
            'order_division_name' => $customer->division_name,
            'order_name' => $customer->name_kanji,
            'order_name_kana' => $customer->name_kana,
            'm_cust_id_billing' => $customer->m_cust_id,
            'billing_tel1' => $customer->tel1,
            'billing_tel2' => $customer->tel2,
            'billing_fax' => $customer->fax,
            'billing_email1' => $customer->email1,
            'billing_email2' => $customer->email2,
            'billing_postal' => $customer->postal,
            'billing_address1' => $customer->address1,
            'billing_address2' => $customer->address2,
            'billing_address3' => $customer->address3,
            'billing_address4' => $customer->address4,
            'billing_corporate_name' => $customer->corporate_kanji,
            'billing_division_name' => $customer->division_name,
            'billing_name' => $customer->name_kanji,
            'billing_name_kana' => $customer->name_kana,
            'campaign_flg' => CampaignFlgEnum::SUBJECT->value,
            'm_payment_types_id' => $paymentTypesId,
            'progress_type' => ProgressTypeEnum::PendingConfirmation->value,
            'order_datetime' => Carbon::now()->format('Y-m-d H:i:s'),
            'order_count' => $orderCount,
            'entry_operator_id' => $customer->update_operator_id,
            'update_operator_id' => $customer->update_operator_id
        ];

        $instance = OrderHdrModel::create($row);

        $this->saveOrderMemo($instance, $orderCount);

        return [
            'instance' => $instance,
            'message' => __('messages.info.create_completed', ['data' => '受注基本1件']),
        ];
    }

    private function saveOrderMemo($orderHdr, $orderCount)
    {
        $row = [
            'm_account_id' => $orderHdr->m_account_id,
            't_order_hdr_id' => $orderHdr->t_order_hdr_id,
            'entry_operator_id' => $orderHdr->order_operator_id,
            'update_operator_id' => $orderHdr->order_operator_id
        ];

        $instance = OrderMemoModel::create($row);
    }

    private function getitemnameTypesId($accountId, $itemnameType)
    {
        $row = ItemnameTypeModel::where([
            'm_account_id' => $accountId,
            'delete_flg' => DeleteFlg::Use->value,
            'm_itemname_type' => $itemnameType,
            'm_itemname_type_code' => 'EC'
        ])
        ->orderBy('m_itemname_types_id', 'ASC')->first();

        if (is_null($row)) {
            $row = ItemnameTypeModel::where([
                'm_account_id' => $accountId,
                'delete_flg' => DeleteFlg::Use->value,
                'm_itemname_type' => $itemnameType
            ])
            ->orderBy('m_itemname_type_sort', 'ASC')->first();
        }

        return $row->m_itemname_types_id;
    }

    private function getEcsId($accountId)
    {
        $row = EcsModel::where(['m_account_id' => $accountId, 'delete_flg' => DeleteFlg::Use->value])
            ->orderByRaw('m_ecs_sort ASC, m_ecs_id ASC')->first();

        return $row->m_ecs_id;
    }

    private function saveCustomer($customer, $operatorsId, $customerCreateOrUpdateValue, $row)
    {
        $ret = false;

        if ($customerCreateOrUpdateValue == 'update') {
            unset($row['entry_operator_id']);
        }

        if ($customerCreateOrUpdateValue == 'create') {
            $customer = CustModel::create($row);
        }
        elseif (!$customer->fill($row)->save()) {
            throw new Exception('customer save error.');
        }

        $messageKey = $customerCreateOrUpdateValue;

        return [
            'instance' => $customer,
            'message' => __('messages.info.' . $messageKey . '_completed', ['data' => '顧客1件']),
        ];
    }

    private function fromSpreadSheetValidity($accountId, $spreadSheetData)
    {
        $data = $spreadSheetData['from'];
        $errorMessageList = [];

        if ($data['base']['name_kanji'] == '') {
            $errorMessageList[] = self::FROM_SHEET_NAME . ' ' . self::FROM_BILLING_ADDRESS_CELL . ' billing address format error.';
        }

        $phoneNumber = $data['base']['tel1'];

        if (!$this->isPhoneNumberFormat($phoneNumber)) {
            $errorMessageList[] = self::FROM_SHEET_NAME . ' ' . self::FROM_PHONE_NUMBER_CELL . ' phone number format error.';
        }

        $postalCode = $data['base']['postal'];

        if (!$this->isPostalCodeFormat($postalCode)) {
            $errorMessageList[] = self::FROM_SHEET_NAME . ' ' . self::FROM_POSTAL_CODE_CELL . ' postal code format error.';
        }

        if (!$this->existPostalCode($postalCode)) {
            $errorMessageList[] = self::FROM_SHEET_NAME . ' ' . self::FROM_POSTAL_CODE_CELL . ' postal code not exist error.';
        }

        $prefectures = $data['base']['address1'];

        if (!$this->isPrefecturesValidity($prefectures)) {
            $errorMessageList[] = self::FROM_SHEET_NAME . ' ' . self::FROM_PREFECTURES_CELL . ' prefectures validity error.';
        }

        $municipalities = $data['base']['address2'];

        if (!$this->isMunicipalitiesValidity($municipalities)) {
            $errorMessageList[] = self::FROM_SHEET_NAME . ' ' . self::FROM_MUNICIPALITIES_CELL . ' municipalities validity error.';
        }

        $houseNumber = $data['base']['address3'];

        if (!$this->isHouseNumberValidity($houseNumber)) {
            $errorMessageList[] = self::FROM_SHEET_NAME . ' ' . self::FROM_HOUSE_NUMBER_CELL . ' house number validity error.';
        }

        foreach ($data['orderDtl']['itemNo'] as $i => $itemNo) {
            if ($itemNo == '' && $data['orderDtl']['volume'][$i] == '' && $data['orderDtl']['noshi'][$i] == '') {
                unset($data['orderDtl']['itemNo'][$i]);
                continue;
            }

            if (!$this->existItemNo($accountId, $itemNo)) {
                $errorMessageList[] = self::FROM_SHEET_NAME . ' item No. format error.';
            }

            if (!$this->isVolumeFormat($data['orderDtl']['volume'][$i])) {
                $errorMessageList[] = self::FROM_SHEET_NAME . ' volume format error.';
            }

            $noshiDetailId = $data['orderDtl']['noshi'][$i];

            if (!$this->isNoshiValidity($accountId, $noshiDetailId)) {
                $errorMessageList[] = self::FROM_SHEET_NAME . ' noshi format error.';
            }
        }

        $desiredDeliveryDate = $data['orderDestination']['desiredDeliveryDate'];

        if (!$this->isDesiredDeliveryDateFormat($desiredDeliveryDate)) {
            $errorMessageList[] = self::FROM_SHEET_NAME . ' ' . self::FROM_DESIRED_DELIVERY_DATE_CELL . ' desired delivery date format error.';
        }

        $desiredDeliveryTime = $data['orderDestination']['desiredDeliveryTime'];

        if (!$this->isDesiredDeliveryTimeFormat($desiredDeliveryTime)) {
            $errorMessageList[] = self::FROM_SHEET_NAME . ' ' . self::FROM_DESIRED_DELIVERY_TIME_CELL . ' desired delivery time format error.';
        }

        $paymentMethod = $data['base']['paymentMethod'];

        if (!$this->isPaymentMethodFormat($paymentMethod)) {
            $errorMessageList[] = self::FROM_SHEET_NAME . ' ' . self::FROM_PAYMENT_METHOD_CELL . ' payment method format error.';
        }

        $spreadSheetData['from'] = $data;

        return [
            'spread_sheet' => $spreadSheetData,
            'error_message_list' => $errorMessageList,
        ];
    }

    private function toSpreadSheetValidity($accountId, $data)
    {
        $errorMessageList = [];
        $index = self::TO_ROW_INDEX;

        foreach ($data['orderDestinationList'] as $row) {
            $phoneNumber = $row['destination_tel'];

            if (!$this->isPhoneNumberFormat($phoneNumber)) {
                $errorMessageList[] = self::TO_SHEET_NAME . ' ' . self::TO_PHONE_NUMBER_COLUMN_POS . $index . ' phone number format error.';
            }

            $postalCode = $row['destination_postal'];

            if (!$this->isPostalCodeFormat($postalCode)) {
                $errorMessageList[] = self::TO_SHEET_NAME . ' ' . self::TO_POSTAL_CODE_COLUMN_POS . $index . ' postal code format error.';
            }

            if (!$this->existPostalCode($postalCode)) {
                $errorMessageList[] = self::TO_SHEET_NAME . ' ' . self::TO_POSTAL_CODE_COLUMN_POS . $index . ' postal code not exist error.';
            }

            $prefectures = $row['destination_address1'];

            if (!$this->isPrefecturesValidity($prefectures)) {
                $errorMessageList[] = self::TO_SHEET_NAME . ' ' . self::TO_PREFECTURES_COLUMN_POS . $index . ' prefectures validity error.';
            }

            $municipalities = $row['destination_address2'];

            if (!$this->isMunicipalitiesValidity($municipalities)) {
                $errorMessageList[] = self::TO_SHEET_NAME . ' ' . self::TO_MUNICIPALITIES_COLUMN_POS . $index . ' municipalities validity error.';
            }

            $houseNumber = $row['destination_address3'];

            if (!$this->isHouseNumberValidity($houseNumber)) {
                $errorMessageList[] = self::TO_SHEET_NAME . ' ' . self::TO_HOUSE_NUMBER_COLUMN_POS . $index . ' house number validity error.';
            }

            $shippingAddress = $row['shippingAddress1'];

            if (!$this->isShippingAddressValidity($shippingAddress)) {
                $errorMessageList[] = self::TO_SHEET_NAME . ' ' . self::TO_SHIPPING_ADDRESS1_COLUMN_POS . $index . ' shipping address validity error.';
            }

            $itemNo = $row['itemNo'];
            $volume = $row['volume'];

            if (!$this->existItemNo($accountId, $itemNo)) {
                $errorMessageList[] = self::TO_SHEET_NAME . ' ' . self::TO_ITEM_NO_COLUMN_POS . $index . ' item No. format error.';
            }

            if (!$this->isVolumeFormat($volume)) {
                $errorMessageList[] = self::TO_SHEET_NAME . ' ' . self::TO_VOLUME_COLUMN_POS . $index . ' volume format error.';
            }

            ++$index;
        }

        $toDesiredDeliveryDate = $data['orderDestination']['desiredDeliveryDate'];

        if (!$this->isDesiredDeliveryDateFormat($toDesiredDeliveryDate)) {
            $errorMessageList[] = self::TO_SHEET_NAME . ' ' . self::TO_DESIRED_DELIVERY_DATE_CELL . ' desired delivery date format error.';
        }

        $toDesiredDeliveryTime = $data['orderDestination']['desiredDeliveryTime'];

        if (!$this->isDesiredDeliveryTimeFormat($toDesiredDeliveryTime)) {
            $errorMessageList[] = self::TO_SHEET_NAME . ' ' . self::TO_DESIRED_DELIVERY_TIME_CELL . ' desired delivery time format error.';
        }

        return $errorMessageList;
    }

    private function existCustomerId($custId)
    {
        return $custId !== '';
    }

    private function getCustomerCreateOrUpdateValue($custId)
    {
        return $this->existCustomerId($custId) ? 'update' : 'create';
    }

    private function existCustomer($customer)
    {
        return !is_null($customer);
    }

    private function getCustomer($accountId, $custId)
    {
        $customer = $this->searchCustomer->execute([
            'm_account_id' => $accountId,
            'm_cust_id' => $custId,
        ]);

        // to check excel data have or not condition
        if (count($customer) < 1) {
            return null;
        }

        return $customer->first();
    }

    //電話番号フォーマットでないならば異常
    private function isPhoneNumberFormat($phoneNumber)
    {
        $matchNum = 1;
        return preg_match('/^[0-9\-+]+$/u', $phoneNumber) === $matchNum;
    }

    //郵便番号フォーマットなら true
    private function isPostalCodeFormat($postalCode)
    {
        $matchNum = 1;
        return preg_match('/^[0-9]{7}$/u', $postalCode) === $matchNum;
    }

    //郵便番号マスタに存在しないならば異常
    private function existPostalCode($postalCode)
    {
        return !is_null(PostalCodeModel::where('postal_code', $postalCode)->first());
    }

    private function isPrefecturesValidity($prefectures)
    {
        return $prefectures !== '';
    }

    private function isMunicipalitiesValidity($municipalities)
    {
        return $municipalities !== '';
    }

    private function isHouseNumberValidity($houseNumber)
    {
        return $houseNumber !== '';
    }

    private function isShippingAddressValidity($shippingAddress)
    {
        return $shippingAddress !== '';
    }

    private function existItemNo($accountId, $itemNo)
    {
        if ($itemNo === '') {
            return false;
        }

        //該当の品番が ページマスタ.ページコード に存在しないならば異常
        return !is_null(AmiPageModel::where(['m_account_id' => $accountId, 'page_cd' => $itemNo])->first());
    }

    private function isVolumeFormat($volume)
    {
        if (is_numeric($volume) && 0 < $volume) {
            return true;
        }

        return false;
    }

    private function isNoshiValidity($accountId, $noshiDetailId)
    {
        if ($noshiDetailId === '') {
            return true;
        }

        return !is_null($this->searchNoshiDetail->execute(['m_account_id' => $accountId, 'm_noshi_detail_id' => $noshiDetailId])->first());
    }

    private function getDesiredDeliveryDate($string)
    {
        $dt = null;

        if (preg_match('/\A[0-9]{5}\z/i', $string)) { //Excel serial値
            $dt = Date::excelToDateTimeObject($string);
        }
        elseif (preg_match('/\A[0-9]{4}[\/-]?[0-9]{2}[\/-]?[0-9]{2}\z/i', $string)) {
            $dt = Carbon::parse($string);
        }

        return $dt;
    }

    //お届け希望日
    private function isDesiredDeliveryDateFormat($date)
    {
        if (empty($date)) {
            return false;
        }

        $dt = $this->getDesiredDeliveryDate($date);

        if (is_null($dt)) {
            //日付フォーマットでないならば異常
            return false;
        }

        //日付が過去日付ならば異常
        if ($dt->format('Ymd') < Carbon::now()->format('Ymd')) {
            return false;
        }

        return true;
    }

    private function getDeliveryTimeHope($timeHope)
    {
        //受注配送先.配送時間帯ID に変換できないならば異常
        return DeliveryTimeHopeModel::where('delivery_time_hope_name', $timeHope)->first();
    }

    //お届け希望時間
    private function isDesiredDeliveryTimeFormat($timeHope)
    {
        //受注配送先.配送時間帯ID に変換できないならば異常
        return !is_null($this->getDeliveryTimeHope($timeHope));
    }

    private function getPaymentTypesId($accountId, $paymentMethod)
    {
        $keywordList = $this->_getPaymentMethodValueCandidate($paymentMethod);
        $paymentTypesCode = $keywordList[0];

        $row = PaymentTypeModel::where(['m_account_id' => $accountId, 'm_payment_types_code' => $paymentTypesCode])->first();
        return $row->m_payment_types_id;
    }

    //お支払方法フォーマットなら true
    private function isPaymentMethodFormat($paymentMethod)
    {
        $keywordList = $this->_getPaymentMethodValueCandidate($paymentMethod);
        $one = 1;

        if (count($keywordList) != $one) {
            return false;
        }

        return true;
    }

    //お支払方法候補を array に入れてそれを返却。
    private function _getPaymentMethodValueCandidate($paymentMethod)
    {
        $keywordList = [];
        //文字列内に「コンビニ」もしくは「銀行振込」が含まれていないならば異常
        //文字列内に「コンビニ」と「銀行振込」の両方が含まれている場合は異常
        if (strpos($paymentMethod, 'コンビニ') !== false) {
            $keywordList[] = PaymentTypeEnum::CONVENIENCE_POSTAL->value;
        }

        if (strpos($paymentMethod, '銀行振込') !== false) {
            $keywordList[] = PaymentTypeEnum::BANK->value;
        }

        return $keywordList;
    }

    private function existOrderDestination($row)
    {
        if ($row['destination_tel'] != ''
        || $row['destination_postal'] != ''
        || $row['destination_address1'] != ''
        || $row['destination_address2'] != ''
        || $row['destination_address3'] != ''
        || $row['shippingAddress1'] != ''
        || $row['itemNo'] != ''
        || $row['volume'] != '') {
            return true;
        }

        return false;
    }

    /**
     * Data get
     *
     * @return array ( excel table data )
     */
    private function getSpreadSheetValues($spreadSheet)
    {
        $fromSheet = $spreadSheet->getSheetByName(self::FROM_SHEET_NAME);

        if (is_null($fromSheet)) {
            throw new Exception('not ' . self::FROM_SHEET_NAME . ' Sheet.');
        }

        $data = [];
        $id = $fromSheet->getCell(self::FROM_CUSTOMER_ID_CELL)->getFormattedValue(); //②顧客ID 無ければ新規作成

        if ($id !== '') {
            $tmp['base']['m_cust_id'] = $id;
        }

        $tmp['base']['name_kanji'] = $fromSheet->getCell(self::FROM_BILLING_ADDRESS_CELL)->getFormattedValue(); //③送り主名(ご請求先）（全角16文字）
        $tmp['base']['name_kana'] = $fromSheet->getCell(self::FROM_BILLING_ADDRESS_KANA_CELL)->getFormattedValue(); //④送り主名(ご請求先）（かな）
        $tmp['base']['tel1'] = $fromSheet->getCell(self::FROM_PHONE_NUMBER_CELL)->getFormattedValue(); //⑤電話番号
        $tmp['base']['postal'] = $fromSheet->getCell(self::FROM_POSTAL_CODE_CELL)->getFormattedValue(); //⑥郵便番号
        $tmp['base']['address1'] = $fromSheet->getCell(self::FROM_PREFECTURES_CELL)->getFormattedValue(); //⑦都道府県（全角4文字）
        $tmp['base']['address2'] = $fromSheet->getCell(self::FROM_MUNICIPALITIES_CELL)->getFormattedValue(); //⑧市区町村（全角12文字）
        $tmp['base']['address3'] = $fromSheet->getCell(self::FROM_HOUSE_NUMBER_CELL)->getFormattedValue(); //⑨番地（全角16文字）
        $tmp['base']['address4'] = $fromSheet->getCell(self::FROM_NAME_OF_BUILDING_COMPANY_DEPARTMENT_CELL)->getFormattedValue(); //⑩建物名・会社名・部署名など（全角16文字）

        $tmp['orderDtl']['itemNo'][] = $fromSheet->getCell(self::FROM_ITEM_NO1_CELL)->getFormattedValue(); //⑫品番1
        $tmp['orderDtl']['itemNo'][] = $fromSheet->getCell(self::FROM_ITEM_NO2_CELL)->getFormattedValue(); //⑯品番2
        $tmp['orderDtl']['itemNo'][] = $fromSheet->getCell(self::FROM_ITEM_NO3_CELL)->getFormattedValue(); //⑳品番3
        $tmp['orderDtl']['volume'][] = $fromSheet->getCell(self::FROM_VOLUME1_CELL)->getFormattedValue(); //⑬数量1
        $tmp['orderDtl']['volume'][] = $fromSheet->getCell(self::FROM_VOLUME2_CELL)->getFormattedValue(); //⑰数量2
        $tmp['orderDtl']['volume'][] = $fromSheet->getCell(self::FROM_VOLUME3_CELL)->getFormattedValue(); //㉑数量3
        $tmp['orderDtl']['noshi'][] = $fromSheet->getCell(self::FROM_NOSHI1_CELL)->getFormattedValue(); //⑭熨斗紙1
        $tmp['orderDtl']['noshi'][] = $fromSheet->getCell(self::FROM_NOSHI2_CELL)->getFormattedValue(); //⑱熨斗紙2
        $tmp['orderDtl']['noshi'][] = $fromSheet->getCell(self::FROM_NOSHI3_CELL)->getFormattedValue(); //㉒熨斗紙3
        $tmp['orderDtl']['handbag'][] = $fromSheet->getCell(self::FROM_HANDBAG1_CELL)->getFormattedValue(); //⑮手提げ1
        $tmp['orderDtl']['handbag'][] = $fromSheet->getCell(self::FROM_HANDBAG2_CELL)->getFormattedValue(); //⑲手提げ2
        $tmp['orderDtl']['handbag'][] = $fromSheet->getCell(self::FROM_HANDBAG3_CELL)->getFormattedValue(); //㉓手提げ3

        $tmp['orderDestination']['desiredDeliveryDate'] = $fromSheet->getCell(self::FROM_DESIRED_DELIVERY_DATE_CELL)->getValue(); //㉔お届け希望日
        $tmp['orderDestination']['desiredDeliveryTime'] = $fromSheet->getCell(self::FROM_DESIRED_DELIVERY_TIME_CELL)->getFormattedValue(); //㉕お届け希望時間
        $tmp['noshi']['omotegaki'] = $fromSheet->getCell(self::FROM_NOSHI_ENVELOPE_CELL)->getFormattedValue(); //㉖のし表書き
        $tmp['noshi']['company_name1'] = $fromSheet->getCell(self::FROM_COMPANY_NAME_CELL)->getFormattedValue(); //㉗会社名
        $tmp['noshi']['title1'] = $fromSheet->getCell(self::FROM_JOB_TITLE_CELL)->getFormattedValue(); //㉘肩書き
        $tmp['noshi']['name1'] = $fromSheet->getCell(self::FROM_NAME_CELL)->getFormattedValue(); //㉙名前

        $tmp['base']['paymentMethod'] = $fromSheet->getCell(self::FROM_PAYMENT_METHOD_CELL)->getFormattedValue(); //㉚お支払方法

        $data['from'] = $tmp;

        $toSheet = $spreadSheet->getSheetByName(self::TO_SHEET_NAME);

        if (is_null($toSheet)) {
            throw new Exception('not ' . self::TO_SHEET_NAME . ' Sheet.');
        }

        $index = self::TO_ROW_INDEX;
        $data['to']['orderDestinationList'] = [];

        for (;;) {
            $tmp = [];
            $tmp['destination_tel'] = $toSheet->getCell(self::TO_PHONE_NUMBER_COLUMN_POS . $index)->getFormattedValue(); //お届け先一覧[複数行] ㉜電話番号
            $tmp['destination_postal'] = $toSheet->getCell(self::TO_POSTAL_CODE_COLUMN_POS . $index)->getFormattedValue(); //お届け先一覧[複数行] ㉝郵便番号
            $tmp['destination_address1'] = $toSheet->getCell(self::TO_PREFECTURES_COLUMN_POS . $index)->getFormattedValue(); //お届け先一覧[複数行] ㉞都道府県
            $tmp['destination_address2'] = $toSheet->getCell(self::TO_MUNICIPALITIES_COLUMN_POS . $index)->getFormattedValue(); //お届け先一覧[複数行] ㉟市区町村
            $tmp['destination_address3'] = $toSheet->getCell(self::TO_HOUSE_NUMBER_COLUMN_POS . $index)->getFormattedValue(); //お届け先一覧[複数行] ㊱番地
            $tmp['destination_address4'] = $toSheet->getCell(self::TO_BUILDING_NAME_COLUMN_POS . $index)->getFormattedValue(); //お届け先一覧[複数行] ㊲建物名
            $tmp['shippingAddress1'] = $toSheet->getCell(self::TO_SHIPPING_ADDRESS1_COLUMN_POS . $index)->getFormattedValue(); //お届け先一覧[複数行] ㊳配送先名（会社名・部署名・役職・氏名）
            $tmp['shippingAddress2'] = $toSheet->getCell(self::TO_SHIPPING_ADDRESS2_COLUMN_POS . $index)->getFormattedValue(); //お届け先一覧[複数行] ㊴配送先名が16文字以上の場合①
            $tmp['shippingAddress3'] = $toSheet->getCell(self::TO_SHIPPING_ADDRESS3_COLUMN_POS . $index)->getFormattedValue(); //お届け先一覧[複数行] ㊵配送先名が16文字以上の場合②
            $tmp['itemNo'] = $toSheet->getCell(self::TO_ITEM_NO_COLUMN_POS . $index)->getFormattedValue(); //お届け先一覧[複数行] ㊶品番
            $tmp['volume'] = $toSheet->getCell(self::TO_VOLUME_COLUMN_POS . $index)->getFormattedValue(); //お届け先一覧[複数行] ㊷数量
            $tmp['noshi'] = $toSheet->getCell(self::TO_NOSHI_COLUMN_POS . $index)->getFormattedValue(); //お届け先一覧[複数行] ㊸熨斗紙

            if (!$this->existOrderDestination($tmp)) {
                break;
            }

            $data['to']['orderDestinationList'][] = $tmp;

            ++$index;
        }

        $data['to']['noshi']['omotegaki'] = $toSheet->getCell(self::TO_NOSHI_ENVELOPE_CELL)->getFormattedValue(); //㊹のし表書き
        $data['to']['noshi']['company_name1'] = $toSheet->getCell(self::TO_COMPANY_NAME_CELL)->getFormattedValue(); //㊺会社名
        $data['to']['noshi']['title1'] = $toSheet->getCell(self::TO_JOB_TITLE_CELL)->getFormattedValue(); //㊻肩書き
        $data['to']['noshi']['name1'] = $toSheet->getCell(self::TO_NAME_CELL)->getFormattedValue(); //㊼名前
        $data['to']['orderDestination']['desiredDeliveryDate'] = $toSheet->getCell(self::TO_DESIRED_DELIVERY_DATE_CELL)->getValue(); //㊽お届け希望日
        $data['to']['orderDestination']['desiredDeliveryTime'] = $toSheet->getCell(self::TO_DESIRED_DELIVERY_TIME_CELL)->getFormattedValue(); //㊾お届け希望時間

        return $data;
    }

    private function getTemplateFileName(int $executeBatchInstructionId, string $accountCd, string $fileName)
    {
        return $accountCd . '/' . $fileName;
    }
}
