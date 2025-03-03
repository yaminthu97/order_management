<?php
namespace App\Console\Commands\Goto;

use App\Enums\InvoiceClassEnum;
use App\Enums\BatchExecuteStatusEnum;

use App\Models\Master\Base\ShopModel;
use App\Models\Master\Base\ReportTemplateModel;
use App\Models\Order\Base\OrderHdrModel;
use App\Models\Order\Base\DeliHdrModel;

use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;
use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Master\Gfh1207\Enums\PaymentTypeEnum;
use App\Modules\Order\Base\CreateBillingOutput;

use App\Services\TenantDatabaseManager;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ShipmentScheduleDataOut extends Command
{
    private const EXPORT_DIR = "export";
    private const DISK_NAME = "gfh_mount";
    private const MAX_TEXT_1000 = 1000;
    private const MAX_TEXT_1024 = 1024;
    private const MAX_TEXT_2048 = 2048;
    private const SUBSCRIBER_NAME = ""; // 加入者名
    private const TRANSFER_BURDEN = "2"; // 企業負担

    private const BILLING_TEMPLATE_TYPE = "請求書1";

    private const PRIVATE_EXCEPTION = -1;
    private const REDUCED_TAX_RATE = 0.08;
    private const CSV_LN_CODE = "\r\n";
    private const FILENAME_SHIP_ORDER = "ship_order.csv";
    private const FILENAME_SHIP_SENDER = "ship_sender.csv";
    private const FILENAME_SHIP_NOSHI = "ship_noshi.csv";
    private const FILENAME_SHIP_PAGE = "ship_page.csv";
    private const FILENAME_SHIP_SKU = "ship_sku.csv";
    private const FILENAME_SHIP_PACKAGE = "ship_package.csv";
    private const HEARDER_SHIP_ORDER = [
        "受注ID",
        "受注日付",
        "注文方法",
        "注文者顧客ID",
        "注文者氏名",
        "注文者氏名カナ",
        "注文者郵便番号",
        "注文者都道府県",
        "注文者市区町村",
        "注文者番地",
        "注文者建物名",
        "注文者法人名・団体名",
        "注文者部署名",
        "注文者電話番号",
        "請求先顧客ID",
        "請求者氏名",
        "請求先氏名カナ",
        "請求先郵便番号",
        "請求先都道府県",
        "請求先市区町村",
        "請求先番地",
        "請求先建物名",
        "請求先法人名・団体名",
        "請求先部署名",
        "請求先電話番号",
        "商品購入金額",
        "商品消費税額",
        "手数料合計",
        "手数料（支払い方法）",
        "包装料",
        "割引金額",
        "割引金額（８％）",
        "割引金額（10％）",
        "クーポン利用額",
        "利用ポイント",
        "請求金額",
        "請求税額",
        "軽減税率合計金額",
        "軽減税率消費税",
        "標準税率合計金額", 
        "標準税率消費税",
        "支払期限",
        "備考",
        "社内メモ",
        "支払方法コード",
        "支払方法名",
        "請求書番号",
        "インボイス番号",
        "1段バーコード",
        "OCR1段目",
        "OCR2段目",
        "銀行口座 - 銀行コード",
        "銀行口座 - 支店コード",
        "銀行口座 - 銀行名",
        "銀行口座 - 支店名",
        "銀行口座 - 口座種別",
        "銀行口座 - 口座番号",
        "銀行口座 - 口座名義",
        "銀行口座 - 口座名義カナ",
        "ゆうちょ口座 - 加入者名",
        "ゆうちょ口座 - 口座番号",
        "ゆうちょ口座 - 払込負担区分"
    ];
    private const HEARDER_SHIP_SENDER = [
        "受注ID",
        "受注日付",
        "受注配送先ID",
        "出荷ID",
        "送付先氏名",
        "送付先氏名カナ",
        "商品購入金額",
        "配送方法コード",
        "送料",
        "手数料（配送方法）",
        "温度帯",
        "請求書区分",
        "汎用区分1",
        "汎用区分2",
        "汎用区分3",
        "汎用区分4",
        "汎用区分5",
        "送付先郵便番号",
        "送付先都道府県",
        "送付先市区町村",
        "送付先番地",
        "送付先建物名",
        "送付先法人名・団体名",
        "送付先部署名",
        "送付先電話番号",
        "送り主名",
        "送り状コメント",
        "ピッキングコメント",
        "出荷予定日",
        "配送希望日",
        "配送時間帯"
    ];
    private const HEARDER_SHIP_NOSHI = [
        "受注ID",
        "受注日付",
        "受注配送先ID",
        "出荷ID",
        "出荷明細ID",
        "熨斗タイプ",
        "熨斗種類",
        "付属品グループコード",
        "枚数",
        "貼付け・同梱",
        "熨斗ファイル名",
        "名入パターンID",
        "表書き（初期値）",
        "表書き",
        "会社名1",
        "会社名2",
        "会社名3",
        "会社名4",
        "会社名5",
        "部署名1",
        "部署名2",
        "部署名3",
        "部署名4",
        "部署名5",
        "肩書1",
        "肩書2",
        "肩書3",
        "肩書4",
        "肩書5",
        "苗字1",
        "苗字2",
        "苗字3",
        "苗字4",
        "苗字5",
        "名前1",
        "名前2",
        "名前3",
        "名前4",
        "名前5",
        "ルビ1",
        "ルビ2",
        "ルビ3",
        "ルビ4",
        "ルビ5"
    ];
    private const HEARDER_SHIP_PAGE = [
        "受注ID",
        "受注日付",
        "受注配送先ID",
        "出荷ID",
        "出荷明細ID",
        "明細番号",
        "熨斗区分",
        "商品ID",
        "商品コード",
        "販売種別",
        "商品名",
        "明細クーポンID",
        "明細クーポン金額",
        "販売単価",
        "受注数量",
        "受注金額",
        "軽減税率適用商品フラグ",
        "消費税額",
        "自由項目1",
        "自由項目2",
        "自由項目3",
        "自由項目4",
        "自由項目5"
    ];
    private const HEARDER_SHIP_SKU = [
        "受注ID",
        "受注日付",
        "受注配送先ID",
        "出荷ID",
        "出荷明細ID",
        "出荷明細SKUID",
        "明細番号",
        "SKUID",
        "SKUCD",
        "受注数量",
        "自由項目1",
        "自由項目2",
        "自由項目3",
        "自由項目4",
        "自由項目5"
    ];
    private const HEARDER_SHIP_PACKAGE = [
        "受注ID",
        "受注日付",
        "受注配送先ID",
        "出荷ID",
        "出荷明細ID",
        "出荷明細付属品ID",
        "明細番号",
        "付属品ID",
        "付属品グループコード",
        "付属品コード",
        "付属品カテゴリ",
        "付属品名",
        "数量",
        "請求書記載区分"
    ];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ShipmentScheduleDataOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '出荷予定データ作成';

    // 開始時処理
    protected $startBatchExecute;

    // 終了時処理
    protected $endBatchExecute;

    // 請求書履歴作成
    protected $createBillingOutput;

    // アカウントID
    protected $accountId;

    // 実行担当者ID
    protected $operatorId;

    // テンプレートID
    protected $templateId;

    public function __construct(
        StartBatchExecuteInstructionInterface $startBatchExecute,
        EndBatchExecuteInstructionInterface $endBatchExecute,
        CreateBillingOutput $createBillingOutput
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->createBillingOutput = $createBillingOutput;
        parent::__construct();
    }

    private function cutStrByte($str,$byte){
        // シフトJISでバイト区切りを行う
        $sjis = mb_convert_encoding($str,'sjis-win','utf-8');
        $ary = mb_str_split($sjis,1,'sjis-win');
        $lineByte = 0;
        $rv = [];
        $line = '';
        foreach($ary as $val){
            if(($lineByte + strlen($val)) > $byte){
                return mb_convert_encoding($line,'utf-8','sjis-win');
            } else {
                $line .= $val;
            }
            $lineByte += strlen($val);
        }
        if(strlen($line) > 0){
            return mb_convert_encoding($line,'utf-8','sjis-win');
        }
        return "";
    }
    /**
     * 出荷指示データ（受注）作成
     */
    private function getShipOrder($order,$invoiceRegistNumber){
        $billingOutputs = $this->createBillingOutput->execute($order->t_billing_hdr_id, $this->templateId, null,$this->accountId,$this->operatorId);
        if(empty($billingOutputs)){
            throw new Exception(__('messages.error.register_failed',['data'=>'請求書出力履歴']),self::PRIVATE_EXCEPTION);
        }
        $rv = [];
        $rv[] = $order->t_order_hdr_id; // 受注ID(必須)
        $rv[] = empty($order->order_datetime)?'':date('Y/m/d',strtotime($order->order_datetime)); // 受注日付(必須)
        $rv[] = $order->orderType?$order->orderType->m_itemname_type_name:''; // 注文方法(必須)
        $rv[] = $order->m_cust_id; // 注文主顧客ID(必須)
        $rv[] = $order->order_name; // 注文主氏名(必須)
        $rv[] = $order->order_name_kana; // 注文主氏名カナ(必須)
        $rv[] = empty($order->order_postal)?'':(substr($order->order_postal,0,3).'-'.substr($order->order_postal,3));// 注文主郵便番号(必須)
        $rv[] = $order->order_address1; // 注文主都道府県(必須)
        $rv[] = $order->order_address2; // 注文主市区町村(必須)
        $rv[] = $order->order_address3; // 注文主番地
        $rv[] = $order->order_address4; // 注文主建物名
        $rv[] = $order->order_corporate_name; // 注文主法人名・団体名
        $rv[] = $order->order_division_name; // 注文主部署名
        $rv[] = $order->order_tel1; // 注文主電話番号
        $rv[] = $order->m_cust_id_billing; // 請求先顧客ID(必須)
        $rv[] = $order->billing_name; // 請求者氏名(必須)
        $rv[] = $order->billing_name_kana; // 請求先氏名カナ(必須)
        $rv[] = empty($order->billing_postal)?'':(substr($order->billing_postal,0,3).'-'.substr($order->billing_postal,3)); // 請求先郵便番号(必須)
        $rv[] = $order->billing_address1; // 請求先都道府県(必須)
        $rv[] = $order->billing_address2; // 請求先市区町村(必須)
        $rv[] = $order->billing_address3; // 請求先番地
        $rv[] = $order->billing_address4; // 請求先建物名
        $rv[] = $order->billing_corporate_name; // 請求先法人名・団体名
        $rv[] = $order->billing_division_name; // 請求先部署名
        $rv[] = $order->billing_tel1; // 請求先電話番号
        $rv[] = (int)$order->sell_total_price; // 商品購入金額(必須)
        $rv[] = (int)$order->reduce_tax_price; // 商品消費税額(必須)
        $rv[] = (int)$order->payment_fee; // 手数料合計
        $rv[] = (int)$order->transfer_fee; // 手数料（支払方法）
        $rv[] = (int)$order->package_fee; // 包装料
        $rv[] = (int)$order->discount; // 割引金額
        $rv[] = (int)$order->reduce_discount; // 割引額(軽減税率)
        $rv[] = (int)$order->standard_discount; // 割引額(標準税率)
        $rv[] = (int)$order->total_use_coupon; // クーポン利用額
        $rv[] = (int)$order->use_point; // 利用ポイント
        $rv[] = (int)$order->order_total_price; // 請求金額
        $rv[] = (int)$order->tax_price; // 請求税額
        $rv[] = (int)$order->reduce_total_price; // 軽減税率合計金額
        $rv[] = (int)$order->reduce_tax_price; // 軽減税率消費税
        $rv[] = (int)$order->standard_total_price; // 標準税率合計金額
        $rv[] = (int)$order->standard_tax_price; // 標準税率消費税
        $rv[] = empty($billingOutputs->payment_due_date)?"":date('Y/m/d',strtotime($billingOutputs->payment_due_date)); // 支払期限(必須)
        $rv[] = $this->cutStrByte($order->order_comment,self::MAX_TEXT_2048); // 備考 2048byteでカット
        $rv[] = $this->cutStrByte($order->orderMemo?$order->orderMemo->operator_comment:"",self::MAX_TEXT_2048);// 社内メモ　2048byteでカット
        $rv[] = $order->paymentTypes?$order->paymentTypes->m_payment_types_code:""; // 支払方法コード(必須)
        $rv[] = $order->paymentTypes?$order->paymentTypes->m_payment_types_name:""; // 支払方法名(必須)
        $rv[] = $billingOutputs?$billingOutputs->billing_no:""; // 請求書番号
        $rv[] = $invoiceRegistNumber; // インボイス番号
        if($order->paymentTypes && (PaymentTypeEnum::PREPAYMENT_CONVENIENCE_POSTAL->value == $order->paymentTypes->m_payment_types_code || PaymentTypeEnum::CONVENIENCE_POSTAL->value == $order->paymentTypes->m_payment_types_code)){
            $order->paymentTypes->finance_code; // ファイナンスコード
            $order->paymentTypes->cvs_company_code; // 収納企業コード
            $order->paymentTypes->jp_account_num; // ゆうちょ口座番号
            // コンビニ・郵便振込
            $rv[] = $billingOutputs?$billingOutputs->cvs_barcode:''; // 1段バーコード
            $rv[] = $billingOutputs?$billingOutputs->jp_ocr_code_1:''; // OCR1段目
            $rv[] = $billingOutputs?$billingOutputs->jp_ocr_code_2:''; // OCR2段目
            $rv[] = ""; // 銀行口座 - 銀行コード
            $rv[] = ""; // 銀行口座 - 支店コード
            $rv[] = ""; // 銀行口座 - 銀行名
            $rv[] = ""; // 銀行口座 - 支店名
            $rv[] = ""; // 銀行口座 - 口座種別
            $rv[] = ""; // 銀行口座 - 口座番号
            $rv[] = ""; // 銀行口座 - 口座名義
            $rv[] = ""; // 銀行口座 - 口座名義カナ
            $rv[] = self::SUBSCRIBER_NAME; // ゆうちょ口座 - 加入者名
            $rv[] = $order->paymentTypes->jp_account_num; // ゆうちょ口座 - 口座番号
            $rv[] = self::TRANSFER_BURDEN; // ゆうちょ口座 - 払込負担区分
        } elseif($order->paymentTypes && PaymentTypeEnum::BANK->value == $order->paymentTypes->m_payment_types_code){
            // 銀行振込
            $rv[] = ""; // 1段バーコード
            $rv[] = ""; // OCR1段目
            $rv[] = ""; // OCR2段目
            $rv[] = $order->paymentTypes->bank_code; // 銀行口座 - 銀行コード
            $rv[] = $order->paymentTypes->bank_shop_code; // 銀行口座 - 支店コード
            $rv[] = $order->paymentTypes->bank_name; // 銀行口座 - 銀行名
            $rv[] = $order->paymentTypes->bank_shop_name; // 銀行口座 - 支店名
            $rv[] = $order->paymentTypes->bank_account_type; // 銀行口座 - 口座種別
            $rv[] = $order->paymentTypes->bank_account_num; // 銀行口座 - 口座番号
            $rv[] = $order->paymentTypes->bank_account_name; // 銀行口座 - 口座名義
            $rv[] = $order->paymentTypes->bank_account_name_kana; // 銀行口座 - 口座名義カナ
            $rv[] = ""; // ゆうちょ口座 - 加入者名
            $rv[] = ""; // ゆうちょ口座 - 口座番号
            $rv[] = ""; // ゆうちょ口座 - 払込負担区分
        } else {
            $rv[] = ""; // 1段バーコード
            $rv[] = ""; // OCR1段目
            $rv[] = ""; // OCR2段目
            $rv[] = ""; // 銀行口座 - 銀行コード
            $rv[] = ""; // 銀行口座 - 支店コード
            $rv[] = ""; // 銀行口座 - 銀行名
            $rv[] = ""; // 銀行口座 - 支店名
            $rv[] = ""; // 銀行口座 - 口座種別
            $rv[] = ""; // 銀行口座 - 口座番号
            $rv[] = ""; // 銀行口座 - 口座名義
            $rv[] = ""; // 銀行口座 - 口座名義カナ
            $rv[] = ""; // ゆうちょ口座 - 加入者名
            $rv[] = ""; // ゆうちょ口座 - 口座番号
            $rv[] = ""; // ゆうちょ口座 - 払込負担区分
        }
        return [ $rv ];
    }
    private function getBillingKbn($mPaymentTypesCode){
        $rv = null;
        switch($mPaymentTypesCode){
            case PaymentTypeEnum::BANK->value:
            case PaymentTypeEnum::CONVENIENCE_POSTAL->value:
                // 請求書
                $rv = InvoiceClassEnum::INVOICE_INCLUDED->value;
                break;
            case PaymentTypeEnum::CASH_ON_DELIVERY->value:
            case PaymentTypeEnum::CREDIT_CARD->value:
            case PaymentTypeEnum::PREPAYMENT_CONVENIENCE_POSTAL->value:
            case PaymentTypeEnum::PREPAYMENT_BANK->value:
                // 納品書
                $rv = InvoiceClassEnum::DELIVERY_SLIP->value;
                break;
            default:
                // ギフト明細書
                $rv = InvoiceClassEnum::GIFT_DETAILS->value;
                break;
        }
        return $rv;
    }
    /**
     * 出荷指示データ（受注）作成
     */
    private function getShipSender($deliHdr){
        $rv = [];
        $rv[] = $deliHdr->t_order_hdr_id; // 受注ID(必須)
        $rv[] = empty($deliHdr->order_datetime)?'':date('Y/m/d',strtotime($deliHdr->order_datetime)); // 受注日付(必須)
        $rv[] = $deliHdr->t_order_destination_id; // 受注配送先ID(必須)
        $rv[] = $deliHdr->t_deli_hdr_id; // 出荷ID(必須)
        $rv[] = $deliHdr->destination_name; // 送付先氏名(必須)
        $rv[] = $deliHdr->destination_name_kana; // 送付先氏名カナ(必須)
        $rv[] = (int)$deliHdr->sell_total_price; // 商品購入金額(必須)
        $rv[] = $deliHdr->deliType?$deliHdr->deliType->m_delivery_type_code:""; // 配送方法コード(必須)
        $rv[] = (int)$deliHdr->shipping_fee; // 送料
        $rv[] = (int)$deliHdr->payment_fee; // 手数料（配送方法）
        $rv[] = $deliHdr->temperature_zone; // 温度帯(必須)
        if(!empty($deliHdr->billing_type)){
            $rv[] = $this->getBillingKbn($deliHdr->paymentTypes->m_payment_types_code??''); // 請求書区分(必須)
        } else {
            $rv[] = InvoiceClassEnum::GIFT_DETAILS->value; // 請求書区分(必須)
        }
        $rv[] = $deliHdr->gp1_type; // 汎用区分1
        $rv[] = $deliHdr->gp2_type; // 汎用区分2
        $rv[] = $deliHdr->gp3_type; // 汎用区分3
        $rv[] = $deliHdr->gp4_type; // 汎用区分4
        $rv[] = $deliHdr->gp5_type; // 汎用区分5
        
        $rv[] = empty($deliHdr->destination_postal)?'':(substr($deliHdr->destination_postal,0,3).'-'.substr($deliHdr->destination_postal,3));// 送付先郵便番号
        $rv[] = $deliHdr->destination_address1; // 送付先都道府県
        $rv[] = $deliHdr->destination_address2; // 送付先市区町村
        $rv[] = $deliHdr->destination_address3; // 送付先番地
        $rv[] = $deliHdr->destination_address4; // 送付先建物名
        $rv[] = $deliHdr->destination_company_name; // 送付先法人名・団体名
        $rv[] = $deliHdr->destination_division_name; // 送付先部署名
        $rv[] = $deliHdr->destination_tel; // 送付先電話番号

        $rv[] = $deliHdr->sender_name; // 送り主名
        $rv[] =  $this->cutStrByte($deliHdr->deli_comment,self::MAX_TEXT_1024); // 送り状コメント
        $rv[] = $this->cutStrByte($deliHdr->picking_comment,self::MAX_TEXT_2048); // ピッキングコメント
        $rv[] = empty($deliHdr->deli_plan_date)?'':date('Y/m/d',strtotime($deliHdr->deli_plan_date)); // 出荷予定日(必須)
        $rv[] = empty($deliHdr->deli_hope_date)?'':date('Y/m/d',strtotime($deliHdr->deli_hope_date)); // 配送希望日
        $rv[] = $deliHdr->deli_hope_time_name; // 配送時間帯

        return [ $rv ];
    }
    /**
     * 出荷指示データ（熨斗）作成
     */
    private function getShipNoshi($deliHdr){
        $rv = [];
        foreach($deliHdr->deliveryDtl as $deliveryDtl){
            if(!empty($deliveryDtl->cancel_timestamp) && !str_starts_with($deliveryDtl->cancel_timestamp, '0')){
                continue;
            }
            if($deliveryDtl->orderDtlNoshi){
                $orderDtlNoshi = $deliveryDtl->orderDtlNoshi;
                if(!empty($orderDtlNoshi->cancel_timestamp) && !str_starts_with($orderDtlNoshi->cancel_timestamp, '0')){
                    continue;
                }
                $data = [];
                $data[] = $deliHdr->t_order_hdr_id; // 受注ID
                $data[] = empty($deliHdr->order_datetime)?'':date('Y/m/d',strtotime($deliHdr->order_datetime)); // 受注日付(必須)
                $data[] = $deliHdr->t_order_destination_id; // 受注配送先ID
                $data[] = $deliHdr->t_deli_hdr_id; // 出荷ID(必須);
                $data[] = $deliveryDtl->t_delivery_dtl_id; // 出荷明細ID
                $data[] = $orderDtlNoshi->noshi_type; // 熨斗タイプ
                $data[] = $orderDtlNoshi->noshiDetail->noshiFormat->noshi_format_name; // 熨斗種類
                $data[] = $orderDtlNoshi->itemGroup?$orderDtlNoshi->itemGroup->m_itemname_type_code:''; // 付属品グループコード
                $data[] = $orderDtlNoshi->count; // 枚数
                $data[] = $orderDtlNoshi->attach_flg; // 貼付け・同梱
                $data[] = $orderDtlNoshi->noshi_file_name; // 熨斗ファイル名
                $data[] = $orderDtlNoshi->m_noshi_naming_pattern_id; // 名入パターンID
                $data[] = $orderDtlNoshi->noshi->omotegaki; // 表書き（初期値）
                $data[] = $orderDtlNoshi->omotegaki; // 表書き
                $data[] = $orderDtlNoshi->company_name1; // 会社名1
                $data[] = $orderDtlNoshi->company_name2; // 会社名2
                $data[] = $orderDtlNoshi->company_name3; // 会社名3
                $data[] = $orderDtlNoshi->company_name4; // 会社名4
                $data[] = $orderDtlNoshi->company_name5; // 会社名5
                $data[] = $orderDtlNoshi->section_name1; // 部署名1
                $data[] = $orderDtlNoshi->section_name2; // 部署名2
                $data[] = $orderDtlNoshi->section_name3; // 部署名3
                $data[] = $orderDtlNoshi->section_name4; // 部署名4
                $data[] = $orderDtlNoshi->section_name5; // 部署名5
                $data[] = $orderDtlNoshi->title1; // 肩書1
                $data[] = $orderDtlNoshi->title2; // 肩書2
                $data[] = $orderDtlNoshi->title3; // 肩書3
                $data[] = $orderDtlNoshi->title4; // 肩書4
                $data[] = $orderDtlNoshi->title5; // 肩書5
                $data[] = $orderDtlNoshi->firstname1; // 苗字1
                $data[] = $orderDtlNoshi->firstname2; // 苗字2
                $data[] = $orderDtlNoshi->firstname3; // 苗字3
                $data[] = $orderDtlNoshi->firstname4; // 苗字4
                $data[] = $orderDtlNoshi->firstname5; // 苗字5
                $data[] = $orderDtlNoshi->name1; // 名前1
                $data[] = $orderDtlNoshi->name2; // 名前2
                $data[] = $orderDtlNoshi->name3; // 名前3
                $data[] = $orderDtlNoshi->name4; // 名前4
                $data[] = $orderDtlNoshi->name5; // 名前5
                $data[] = $orderDtlNoshi->ruby1; // ルビ1
                $data[] = $orderDtlNoshi->ruby2; // ルビ2
                $data[] = $orderDtlNoshi->ruby3; // ルビ3
                $data[] = $orderDtlNoshi->ruby4; // ルビ4
                $data[] = $orderDtlNoshi->ruby5; // ルビ5
                $rv[] = $data;
            }
        }
        return $rv;
    }
    /**
     * 出荷指示データ（明細）
     */
    private function getShipPage($deliHdr){
        $rv = [];
        foreach($deliHdr->deliveryDtl as $deliveryDtl){
            if(!empty($deliveryDtl->cancel_timestamp) && !str_starts_with($deliveryDtl->cancel_timestamp, '0')){
                continue;
            }
            $orderDtlNoshi = $deliveryDtl->orderDtlNoshi;
            $noshiKbn = 0;
            if(empty($orderDtlNoshi)){
                $noshiKbn = 0;// 0:熨斗なし
            } else {
                if(!empty($orderDtlNoshi->cancel_timestamp) && !str_starts_with($orderDtlNoshi->cancel_timestamp, '0')){
                    continue;
                }
                if(
                    empty($orderDtlNoshi->noshiNamingPattern->company_name_count) && 
                    empty($orderDtlNoshi->noshiNamingPattern->section_name_count) && 
                    empty($orderDtlNoshi->noshiNamingPattern->title_count) && 
                    empty($orderDtlNoshi->noshiNamingPattern->f_name_count) && 
                    empty($orderDtlNoshi->noshiNamingPattern->name_count) && 
                    empty($orderDtlNoshi->noshiNamingPattern->ruby_count)
                ){
                    // 全て0の場合
                    $noshiKbn = 2; // 2:熨斗あり（無地）
                } else {
                    $noshiKbn = 1; // 1:熨斗あり
                }
            }
            $data = [];
            $data[] = $deliHdr->t_order_hdr_id; // 受注ID
            $data[] = empty($deliHdr->order_datetime)?'':date('Y/m/d',strtotime($deliHdr->order_datetime)); // 受注日付(必須)
            $data[] = $deliHdr->t_order_destination_id; // 受注配送先ID
            $data[] = $deliHdr->t_deli_hdr_id; // 出荷ID(必須)
            $data[] = $deliveryDtl->t_delivery_dtl_id; // 出荷明細ID
            $data[] = $deliveryDtl->order_dtl_seq; // 明細番号
            $data[] = $noshiKbn; // 熨斗区分
            $data[] = $deliveryDtl->sell_id; // 商品ID
            $data[] = $deliveryDtl->sell_cd; // 商品コード
            $data[] = $deliveryDtl->amiEcPage->page->page_type; // 販売種別
            $data[] = $this->cutStrByte($deliveryDtl->sell_name,self::MAX_TEXT_1000); // 商品名 
            $data[] = $deliveryDtl->order_dtl_coupon_id; // 明細クーポンID
            $data[] = (int)$deliveryDtl->order_dtl_coupon_price; // 明細クーポン金額
            $data[] = (int)$deliveryDtl->order_sell_price; // 販売単価
            $data[] = $deliveryDtl->order_sell_vol; // 受注数量
            $data[] = (int)$deliveryDtl->order_sell_price * (int)$deliveryDtl->order_sell_vol; // 受注金額
            $data[] = self::REDUCED_TAX_RATE == $deliveryDtl->tax_rate?1:''; // 軽減税率適用商品フラグ
            $data[] = (int)$deliveryDtl->tax_price; // 消費税額
            $data[] = $deliveryDtl->amiEcPage->page->remarks1; // 自由項目1
            $data[] = $deliveryDtl->amiEcPage->page->remarks2; // 自由項目2
            $data[] = $deliveryDtl->amiEcPage->page->remarks3; // 自由項目3
            $data[] = $deliveryDtl->amiEcPage->page->remarks4; // 自由項目4
            $data[] = $deliveryDtl->amiEcPage->page->remarks5; // 自由項目5
            $rv[] = $data;
        }
        return $rv;
    }
    private function getShipSku($deliHdr){
        $rv = [];
        foreach($deliHdr->deliveryDtl as $deliveryDtl){
            if(!empty($deliveryDtl->cancel_timestamp) && !str_starts_with($deliveryDtl->cancel_timestamp, '0')){
                continue;
            }
            foreach($deliveryDtl->deliveryDtlSku as $deliveryDtlSku){
                if(!empty($deliveryDtlSku->cancel_timestamp) && !str_starts_with($deliveryDtlSku->cancel_timestamp, '0')){
                    continue;
                }
                $data = [];
                $data[] = $deliHdr->t_order_hdr_id; // 受注ID(必須)
                $data[] = empty($deliHdr->order_datetime)?'':date('Y/m/d',strtotime($deliHdr->order_datetime)); // 受注日付(必須)
                $data[] = $deliHdr->t_order_destination_id; // 受注配送先ID
                $data[] = $deliveryDtlSku->t_delivery_hdr_id; // 出荷ID(必須)
                $data[] = $deliveryDtlSku->t_delivery_dtl_id; // 出荷明細ID
                $data[] = $deliveryDtlSku->t_delivery_dtl_sku_id; // 出荷明細SKUID
                $data[] = $deliveryDtlSku->order_dtl_seq; // 明細番号
                $data[] = $deliveryDtlSku->item_id; // SKUID
                $data[] = $deliveryDtlSku->item_cd; // SKUCD
                $data[] = $deliveryDtlSku->order_sell_vol; // 受注数量
                $data[] = $deliveryDtlSku->amiSku->remarks1; // 自由項目1
                $data[] = $deliveryDtlSku->amiSku->remarks2; // 自由項目2
                $data[] = $deliveryDtlSku->amiSku->remarks3; // 自由項目3
                $data[] = $deliveryDtlSku->amiSku->remarks4; // 自由項目4
                $data[] = $deliveryDtlSku->amiSku->remarks5; // 自由項目5
                $rv[] = $data;
            }
        }
        return $rv;
    }
    private function getShipPackage($deliHdr){
        $rv = [];
        foreach($deliHdr->deliveryDtl as $deliveryDtl){
            if(!empty($deliveryDtl->cancel_timestamp) && !str_starts_with($deliveryDtl->cancel_timestamp, '0')){
                continue;
            }
            foreach($deliveryDtl->deliveryDtlAttachmentItems as $deliveryDtlAttachmentItem){
                if(!empty($deliveryDtlAttachmentItem->cancel_timestamp) && !str_starts_with($deliveryDtlAttachmentItem->cancel_timestamp, '0')){
                    continue;
                }
                $data = [];
                $data[] = $deliHdr->t_order_hdr_id; // 受注ID(必須)
                $data[] = empty($deliHdr->order_datetime)?'':date('Y/m/d',strtotime($deliHdr->order_datetime)); // 受注日付(必須)
                $data[] = $deliHdr->t_order_destination_id; // 受注配送先ID
                $data[] = $deliveryDtlAttachmentItem->t_delivery_hdr_id; // 出荷ID(必須)
                $data[] = $deliveryDtlAttachmentItem->t_delivery_dtl_id; // 出荷明細ID
                $data[] = $deliveryDtlAttachmentItem->t_delivery_dtl_attachment_item_id; // 出荷明細付属品ID
                $data[] = $deliveryDtlAttachmentItem->order_dtl_seq; // 明細番号
                $data[] = $deliveryDtlAttachmentItem->attachment_item_id; // 付属品ID
                $data[] = $deliveryDtlAttachmentItem->orderDtlAttachmentItem->group->m_itemname_type_code; // 付属品グループコード
                $data[] = $deliveryDtlAttachmentItem->attachment_item_cd; // 付属品コード
                $data[] = $deliveryDtlAttachmentItem->category->m_itemname_type_name; // 付属品カテゴリ
                $data[] = $deliveryDtlAttachmentItem->amiAttachmentItem->attachment_item_name; // 付属品名
                $data[] = $deliveryDtlAttachmentItem->attachment_item_vol; // 数量
                $data[] = $deliveryDtlAttachmentItem->invoice_flg; // 請求書記載区分
                $rv[] = $data;
            }
        }
        return $rv;
    }
    private function writeFile($filepath,$fields) {
        $fp = fopen('php://temp', 'r+b');
        foreach($fields as $field) {
            $row = array_map(function($value){
                $value = str_replace('"','""',$value);
                return '"'.$value.'"';
            },$field);
            fwrite($fp,implode(',',$row).self::CSV_LN_CODE);
        }
        rewind($fp);
        $tmp = stream_get_contents($fp);
        if(Storage::disk(self::DISK_NAME)->put($filepath,mb_convert_encoding($tmp, 'SJIS-win', 'UTF-8')) == false){
            // ファイル出力失敗
            throw new Exception(__('messages.error.putfile_failed',['diskname'=>self::DISK_NAME,'path'=>$filepath]),self::PRIVATE_EXCEPTION);
        }
    }
    public function handle()
    {
        try {
            $batchExecutionId = $this->argument('t_execute_batch_instruction_id');  // for batch execute id
            $batchExecute = $this->startBatchExecute->execute($batchExecutionId);

            $accountCode = $batchExecute->account_cd;     // for account cd
            $this->accountId = $batchExecute->m_account_id;   // for m account id
            $this->operatorId = $batchExecute->m_operators_id;

            if (app()->environment('testing')) {
                // テスト環境の場合
                TenantDatabaseManager::setTenantConnection($accountCode.'_db_testing');
            } else {
                TenantDatabaseManager::setTenantConnection($accountCode.'_db');
            }
        } catch (Exception $e) {
            // write the log error in laravel.log for error message
            Log::error('error_message : ' . $e->getMessage());
            return;
        }
        
        DB::beginTransaction();
        try {
            // パラメータの取得
            $json = json_decode($this->argument('json'),true);
            if(empty($json['order'])){
                throw new Exception(__('messages.error.invalid_parameter'),self::PRIVATE_EXCEPTION);
            }
            foreach($json['order'] as $elm){
                if(empty($elm['id'])){
                    throw new Exception(__('messages.error.invalid_parameter'),self::PRIVATE_EXCEPTION);
                }
            }

            $now = date("Ymd_His");
            $outputPath = DIRECTORY_SEPARATOR.self::EXPORT_DIR.DIRECTORY_SEPARATOR.$now.DIRECTORY_SEPARATOR;
            $instructionFile = DIRECTORY_SEPARATOR.self::EXPORT_DIR.DIRECTORY_SEPARATOR.$now.".flg";
            if(Storage::disk(self::DISK_NAME)->makeDirectory($outputPath) == false){
                // ディレクトリ作成失敗
                throw new Exception(__('messages.error.mkdir_failed',['diskname'=>self::DISK_NAME,'path'=>$outputPath]),self::PRIVATE_EXCEPTION);
            }
            $shipOrder = [];
            $shipSender = [];
            $shipNoshi = [];
            $shipPage = [];
            $shipSku = [];
            $shipPackage = [];

            // ヘッダー設定
            $shipOrder[] = self::HEARDER_SHIP_ORDER;
            $shipSender[] = self::HEARDER_SHIP_SENDER;
            $shipNoshi[] = self::HEARDER_SHIP_NOSHI;
            $shipPage[] = self::HEARDER_SHIP_PAGE;
            $shipSku[] = self::HEARDER_SHIP_SKU;
            $shipPackage[] = self::HEARDER_SHIP_PACKAGE;

            $invoiceRegistNumber = "";
            $shop = ShopModel::query()->first();
            if(!empty($shop)){
                $invoiceRegistNumber = $shop->invoice_regist_number;
            }
            $reportTemplate = ReportTemplateModel::query()->where('m_account_id',$this->accountId)->where('report_type',self::BILLING_TEMPLATE_TYPE)->first();
            if(empty($reportTemplate)){
                throw new Exception(__('messages.error.data_not_found',['data'=>"帳票種類",'id'=>self::BILLING_TEMPLATE_TYPE]),self::PRIVATE_EXCEPTION);
            }
            $this->templateId = $reportTemplate->m_report_template_id;

            foreach($json['order'] as $elm){
                $query = OrderHdrModel::query()->with(
                    [
                        'orderMemo',
                        'paymentTypes'
                    ]
                )->where('t_order_hdr_id',$elm['id']);
                $query->where(function ($q) {
                    $q->whereNull('cancel_timestamp')
                      ->orWhere('cancel_timestamp',0);
                });
                $order = $query->first();
                if(empty($order)){
                    throw new Exception(__('messages.error.data_not_found',['data'=>"受注基本ID",'id'=>$elm['id']]),self::PRIVATE_EXCEPTION);
                }
                // 出荷指示データ（受注）
                $shipOrder = array_merge($shipOrder,$this->getShipOrder($order,$invoiceRegistNumber));
                foreach($elm['delivery']??[] as $elm2){
                    $query = DeliHdrModel::query()->with([
                        'deliveryDtl',
                        'deliveryDtl.deliveryDtlSku',
                        'deliveryDtl.deliveryDtlSku.amiSku',
                        'deliveryDtl.deliveryDtlAttachmentItems',
                        'deliveryDtl.deliveryDtlAttachmentItems.orderDtlAttachmentItem',
                        'deliveryDtl.deliveryDtlAttachmentItems.orderDtlAttachmentItem.group',
                        'deliveryDtl.deliveryDtlAttachmentItems.category',
                        'deliveryDtl.deliveryDtlAttachmentItems.amiAttachmentItem',
                        'deliveryDtl.amiEcPage',
                        'deliveryDtl.amiEcPage.page',
                        'deliveryDtl.orderDtlNoshi',
                        'deliveryDtl.orderDtlNoshi.noshiDetail',
                        'deliveryDtl.orderDtlNoshi.noshiDetail.noshiFormat',
                        'deliveryDtl.orderDtlNoshi.noshiNamingPattern',
                        'deliType',
                        'paymentTypes'
                    ])->where('t_deli_hdr_id',$elm2['id']);
                    $query->where(function ($q) {
                        $q->whereNull('cancel_timestamp')
                          ->orWhere('cancel_timestamp',0);
                    });
                    $deliHdr = $query->first();
                    if(empty($deliHdr)){
                        throw new Exception(__('messages.error.data_not_found',['data'=>"出荷基本ID",'id'=>$elm2['id']]),self::PRIVATE_EXCEPTION);
                    }
                    // 出荷指示データ（送付先）
                    $shipSender = array_merge($shipSender,$this->getShipSender($deliHdr));
                    // 出荷指示データ（熨斗）
                    $shipNoshi = array_merge($shipNoshi,$this->getShipNoshi($deliHdr));
                    // 出荷指示データ（明細）
                    $shipPage = array_merge($shipPage,$this->getShipPage($deliHdr));
                    // 出荷指示データ（明細SKU）
                    $shipSku = array_merge($shipSku,$this->getShipSku($deliHdr));
                    // 出荷指示データ（付属品）
                    $shipPackage = array_merge($shipPackage,$this->getShipPackage($deliHdr));
                }
            }
            // ファイル出力
            self::writeFile($outputPath.self::FILENAME_SHIP_ORDER,$shipOrder);
            self::writeFile($outputPath.self::FILENAME_SHIP_SENDER,$shipSender);
            self::writeFile($outputPath.self::FILENAME_SHIP_NOSHI,$shipNoshi);
            self::writeFile($outputPath.self::FILENAME_SHIP_PAGE,$shipPage);
            self::writeFile($outputPath.self::FILENAME_SHIP_SKU,$shipSku);
            self::writeFile($outputPath.self::FILENAME_SHIP_PACKAGE,$shipPackage);
            if(Storage::disk(self::DISK_NAME)->put($instructionFile,"") == false){
                // ファイル出力失敗
                throw new Exception(__('messages.error.putfile_failed',['diskname'=>self::DISK_NAME,'path'=>$instructionFile]),self::PRIVATE_EXCEPTION);
            }
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => __('messages.info.csv_output'),
                'execute_status' => BatchExecuteStatusEnum::SUCCESS->value,
                'file_path' => $instructionFile // 指示ファイル
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            // default fail message
            $errorMessage = $e->getMessage();
            Log::error($errorMessage);
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => $errorMessage,
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value
            ]);
        }
    }
}