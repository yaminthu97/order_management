<?php
namespace App\Modules\Payment\Gfh1207;

use Exception;
use Carbon\Carbon;
use App\Services\ExcelReportManager;
use App\Enums\BillingDetailTypeEnum;
use App\Models\Claim\Gfh1207\BillingOutputModel;
use App\Models\Claim\Gfh1207\BillingHdrModel;
use App\Modules\Payment\Base\ExportBillingDataInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExportBillingData implements ExportBillingDataInterface
{
    // エラーコード用
    private const PRIVATE_THROW_ERR_CODE = -1;


    /**
     * 請求書を出力する。
     * @param int (出力ID)
     * @param string (テンプレートファイルパス)
     * @param int (保存先ファイルパス)
     * @param int (アカウントID)
     * @return int;
    */
    public function execute($id,$templateFilePath,$savePath,$accountId){
        $dataList = $this->getData($id);

        $values = $dataList['tableHeaders'];
        $continuousValues = $dataList['tableData'];

        // データをExcelに書き込む
        $erm = new ExcelReportManager($templateFilePath);
        $erm->setValues($values, $continuousValues);
        $result = $erm->save($savePath);
        $totalCount = count($dataList);
        if ($result) {
            return $totalCount;
        } else {
            // 保存に失敗した場合は例外をスロー
            throw new Exception(__('messages.error.upload_s3_failed'), self::PRIVATE_THROW_ERR_CODE);
        }
    }

    /**
    * 検索パラメータに関連するデータを取得する
    *
    * @param array $param 検索パラメータ
    * @return array (必要なすべての結果データ)
    */
    private function getData($param)
    {
        // 必要なデータ
        $billData = $this->fetchBillingData($param);
        // Excelヘッダー用
        $tableHeaders = $this->getValues($billData);
        // Excelボディのデータを整形して計算
        $billBody = $this->formatBillingBody($billData);
        // Excelボディ用
        $tableDatas = $this->getContinuousValues($billBody);
        $result = [];
        $result = [
            'tableHeaders' => $tableHeaders,
            'tableData'    => $tableDatas
        ];
        return $result;
    }

    /**
    * リレーションを含むデータを取得する
    *
    * @param array $param tbillingOutputId
    * @return array (Excelバインディング用の必要なデータ)
    */
    private function fetchBillingData($param)
    {
        $idKey = isset($param['t_billing_outputs_id']) ? 't_billing_outputs_id' : 't_order_hdr_id';
        $id = $param[$idKey][0] ?? null;

        if (is_null($id)) {
            Log::error("パラメータにIDが不足しています。");
            throw new Exception(__('messages.error.bill_data_not_found'), self::PRIVATE_THROW_ERR_CODE);
        }
        // 「EXCEL請求書」か「見積書・納品書」かを判定
        $isExcelInvoice = isset($param['t_billing_outputs_id']);
        if ($isExcelInvoice) {
            // 「EXCEL請求書」の場合、「請求書出力履歴」と「請求基本」を取得
           $billingData = BillingOutputModel::where('t_billing_outputs_id', $id)
                ->with('billingHdr') // 請求基本情報を一緒に取得
                ->first();
        } else {
            // 「見積書・納品書」の場合、「請求基本」のみを取得
           $billingData = BillingHdrModel::where('t_order_hdr_id', $id)->first();
        }
         // 共通のバリデーション
        if (!$billingData) {
            $this->logAndThrowError("請求データが取得できませんでした", $id);
        }

        if (!$billingData->is_available) {
            $this->logAndThrowError("請求データが無効になっています", $id);
        }

        // 「EXCEL請求書」の場合、billingHdrの追加バリデーション
        if ($isExcelInvoice) {
            if (!$billingData->billingHdr || !$billingData->billingHdr->is_available) {
                $this->logAndThrowError("請求基本レコードが取得できないか、無効になっています", $id);
            }
        }
        return $billingData->toArray();
    }

    /**
     * ログを出力し、エラーメッセージとともに例外をスローします。
     *
     * @param string $message エラーメッセージの内容
     * @param int $id エラーが発生した対象のID
     * @throws Exception 例外をスロー
    */
    private function logAndThrowError($message, $id)
    {
        // エラーメッセージとIDをログに記録
        Log::error("{$message} (ID: {$id})");

        // エラーメッセージを含む例外をスロー
        throw new Exception(__('messages.error.bill_data_not_found'), self::PRIVATE_THROW_ERR_CODE);
    }


    /**
    * Excelヘッダー用データを準備する
    *
    * @param array $param billHeader
    * @return array (Excelテンプレートのヘッダー)
    **/
    private function getValues($billHeader)
    {

        $billingHdr = $billHeader['billing_hdr'] ?? $billHeader;

        $billingHdr['payment_due_date'] = isset($billHeader['payment_due_date'])
                                            ? date('Y/m/d', strtotime($billHeader['payment_due_date']))
                                            : null;

        // 税抜金額の合計
        $taxExcPrice   = intval($billingHdr['tax_excluded_price'] ?? 0);
        $taxExcShipFee = intval($billingHdr['tax_excluded_shipping_fee'] ?? 0);
        $taxExcFee     = intval($billingHdr['tax_excluded_fee'] ?? 0);
        $billingHdr['total_tax_exc'] =$taxExcPrice+$taxExcShipFee+$taxExcFee;
        // 受注基本IDをゼロ埋めした請求書番号
        $invoiceNumber = sprintf('%011d', $billingHdr['t_order_hdr_id'] ?? 0);
         return[
            'items' => [
                        '郵便番号','都道府県','市区町村','番地','建物名',
                        '請求先法人名','請求先顧客氏名','請求先顧客ID', '請求番号','発行日',
                        '支払期限日','請求金額', '消費税額', '軽減税率消費税', '標準税率消費税',
                        '税抜商品金額','税抜送料','税抜手数料','税抜金額',
                        '軽減税率対象金額','標準税率対象金額',
                        '割引金額','軽減税率対象割引','標準税率対象割引','受注ID','請求書番号'
                    ],
            'data'  => [
                $billingHdr['postal'] ?? null,//郵便番号
                $billingHdr['address1']?? null,//都道府県
                $billingHdr['address2']?? null,//市区町村
                $billingHdr['address3']?? null,//番地
                $billingHdr['address4']?? null,//建物名
                $billingHdr['corporate_kanji']?? null,//請求先法人名
                $billingHdr['invoiced_customer_name_kanji']?? null,//請求先顧客氏名
                $billingHdr['invoiced_customer_id']?? null,//請求先顧客ID
                intval($billHeader['billing_no']?? null),//請求番号
                date('Y/m/d'),//発行日
                $billingHdr['payment_due_date'],//支払期限日(上で null チェック済み )
                intval($billingHdr['billing_amount'] ?? 0),    // 請求金額
                intval($billingHdr['tax_price'] ?? 0),         // 消費税額
                intval($billingHdr['reduce_tax_price'] ?? 0),  // 軽減税率消費税
                intval($billingHdr['standard_tax_price'] ?? 0),// 標準税率消費税
                $taxExcPrice,//税抜商品金額
                $taxExcShipFee,//税抜送料
                $taxExcFee,//税抜手数料
                $billingHdr['total_tax_exc'],//税抜金額 (上で null チェック済み )
                intval($billingHdr['reduce_tax_excluded_total_price'] ?? 0),//軽減税率対象金額
                intval($billingHdr['standard_tax_excluded_total_price'] ?? 0),//標準税率対象金額
                intval($billingHdr['discount_amount'] ?? 0),//割引金額
                intval($billingHdr['reduce_discount'] ?? 0),//軽減税率対象割引
                intval($billingHdr['standard_discount'] ?? 0),//標準税率対象割引
                $billingHdr['t_order_hdr_id']?? null,//受注ID
                $invoiceNumber //請求書番号

            ]
        ];
    }

    /**
    * データを整形する（計算ロジックと検証）
    *
    * @param array $param billData
    * @return array (Excelボディデータ)
    **/
    private function formatBillingBody($billData)
    {
        $billHdr = $billData['billing_hdr'] ?? $billData ;
        $detailInfo = $billHdr['detail_info'] ?? null;

        if (empty($detailInfo)) {
            Log::error('請求データ内に詳細情報が存在しません。');
            throw new Exception(__('messages.error.bill_data_not_found'),self::PRIVATE_THROW_ERR_CODE);
        }

        $detailData = json_decode($detailInfo, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('detail_infoのJSONデコードに失敗しました。', ['error' => json_last_error_msg()]);
           throw new Exception(__('messages.error.bill_data_not_found'),self::PRIVATE_THROW_ERR_CODE);
        }

        $billingDestinations = $detailData['destinations'] ?? [];
        $orderDatas = [];

        foreach ($billingDestinations as $item) {
            $billDetails = $item['billing_details'] ?? [];

            $productType   = BillingDetailTypeEnum::PRODUCT_DTL->value;
            $shippingType  = BillingDetailTypeEnum::SHIPPING_FEE->value;
            $paymentType   = BillingDetailTypeEnum::PAYMENT_FEE->value;

            foreach ($billDetails as $data) {
                // 明細種別を確認
                $detailType = $data['detail_type'] ?? null;
                if (in_array($detailType,[$productType, $shippingType, $paymentType],true)) {
                    $orderDatas[] = [
                        "display_code" => $data['display_code'] ?? null,
                        "display_name" => $data['display_name'] ?? null,
                        "quantity"     => $data['quantity'] ?? null,
                        "unit_price"   => $data['unit_price'] ?? 0,
                        "amount"       => $data['amount'] ?? 0,
                        "mark"         => ($detailType === $productType) ? '個' : '件',
                        "disId"        => $data['order_destination_id'] ?? null,
                    ];
                }
            }
        }
        return $orderDatas;
    }

    /**
    * Excelボディ用データを準備する
    *
    * @param array $param billData
    * @return array (Excelボディデータ)
    **/
    private function getContinuousValues($orderDatas)
    {
        foreach ($orderDatas as $item) {
            $data[]= [
                $item['display_code'],//商品コード
                $item['display_name'],//商品名
                $item['quantity'],//数量
                $item['mark'],//単位
                $item['unit_price'],//単価
                $item['amount'],//金額
                $item['disId'],//配送先番号
            ];
        }
        $tableDatas = [
            'items' => ['商品コード', '商品名', '数量', '単位', '単価', '金額','配送先番号'],
            'data'  => $data,
        ];
        return $tableDatas;
    }
}
