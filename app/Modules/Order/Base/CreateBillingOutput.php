<?php

namespace App\Modules\Order\Base;

use App\Exceptions\DataNotFoundException;
use App\Models\Claim\Gfh1207\BillingOutputModel; //請求書出力履歴のモデル
use App\Models\Master\Base\NumberAllocateModel; //採番テーブルのモデル
use App\Models\Master\Base\ShopGfhModel;
use App\Modules\Master\Gfh1207\Enums\PaymentTypeEnum;

use App\Modules\Order\Base\CreateBillingOutputInterface;

use App\Models\Claim\Gfh1207\BillingHdrModel;
use Carbon\Carbon;

use Config;
use DB;

/**
 * 請求書出力履歴更新およびバッチ実行指示テーブル更新
 */
class CreateBillingOutput implements CreateBillingOutputInterface
{
    private $accountId;

    public function __construct()
    {
    }

    private function getBillingNo(){
        // 検索処理
        $query = NumberAllocateModel::query();

        // 企業アカウントIDを追加
        $query->where('m_account_id', $this->accountId);
        $query->where('number_type', 1);
        
        // 採番番号を取得
        $numberAllocate = $query->first();
        
        // 採番番号が見つからない場合
        if (!$numberAllocate) {
            throw new DataNotFoundException(__('messages.error.data_not_found',['data'=>"採番テーブル",'id'=>'請求書']));
        }
        
        // 現在のnumberを取得
        $currentNumber = $numberAllocate->number;
        
        // numberを+1して新しい番号を設定
        $newNumber = $currentNumber + 1;
        
        // 採番テーブルの更新
        $numberAllocate->number = $newNumber;
        $numberAllocate->save();
        
        // 更新前のnumberを返す
        return $currentNumber;
    }
    public function execute($billing_id, $template_id, $output_at,$account_id,$operator_id){
        $resultModel = null;
        $this->accountId = $account_id;
        try {
            //トランザクション追加
            return DB::transaction(function () use ($billing_id, $template_id, $output_at,$operator_id) {
                $shopGfh = ShopGfhModel::query()->first();
                if (empty($shopGfh)) {
                    throw new DataNotFoundException(__('messages.error.data_not_found',['data'=>"基本設定マスタ(gfh)",'id'=>'データ']));
                }
                $conditions = [
                    't_billing_hdr_id' => $billing_id, // 必要に応じてキー名を変更
                ];
        
                //2. 請求詳細テーブルから該当レコードの取得
                $billingHdr = BillingHdrModel::find($billing_id);

                if (empty($billingHdr)) {
                    throw new DataNotFoundException(__('messages.error.data_not_found',['data'=>"請求基本",'id'=>$billing_id]));
                }
                // 支払期限
                $dueDate = new Carbon('+'.$shopGfh->payment_due_dates.' day');
                if(!empty($output_at)){
                    $dueDate = new Carbon($output_at . ' +'.$shopGfh->payment_due_dates.' day');
                }
                $barcode = "";
                $ocr1 = "";
                $ocr2 = "";

                if($billingHdr->order->paymentTypes && (PaymentTypeEnum::PREPAYMENT_CONVENIENCE_POSTAL->value == $billingHdr->order->paymentTypes->m_payment_types_code || PaymentTypeEnum::CONVENIENCE_POSTAL->value == $billingHdr->order->paymentTypes->m_payment_types_code)){
                    // コンビニ
                    $stampFlag = $billingHdr['billing_amount'] >= 50000 ? '1' : '0';
                    $amount = str_pad(floor($billingHdr['billing_amount']), 6, '0', STR_PAD_LEFT);

                    //チェックディジットおよびOCR文字列生成
                    $code = 
                        '91' . 
                        '9' . 
                        $billingHdr['finance_code'] . // ファイナンスコード(5桁)
                        $billingHdr['cvs_company_code'] . // 収納企業コード(5桁)
                        str_pad($billingHdr['t_order_hdr_id'],11,'0',STR_PAD_LEFT) . // 受注基本ID(11桁)
                        '000000' .
                        $dueDate->format('ymd') .
                        $stampFlag .
                        $amount; // 請求金額
                    $checkdigit = $this->calculateBarcodeCd($code);
                    // 最終バーコード文字列の生成
                    $barcode = $code . $checkdigit;
            
                    // 4. ゆうちょ用OCR（1段目）の文字列を生成
                    $financeCodeOcr = $billingHdr['finance_code'];
                    $billingAmountOcr = str_pad(floor($billingHdr['billing_amount']), 11, '0', STR_PAD_LEFT); // 金額を11桁に
            
                    $yuchoOcr = $billingHdr['jp_account_num'];
            
                    // 1段目 OCR 文字列生成
                    $ocr1Data = $yuchoOcr. $billingAmountOcr. '2'.'00000'. '91'. '9'.$financeCodeOcr. '0'; // CD2用
                    $ocr1Cd2 = $this->calculateCd2($ocr1Data, json_decode(config('define.ocr.OCR1TOKEN2')));
                    $ocr1Cd1 = $this->calculateCd1($ocr1Cd2.$ocr1Data, json_decode(config('define.ocr.OCR1TOKEN1')));
                    $ocr1 = $ocr1Cd1 . $ocr1Cd2 . $ocr1Data;
            
                    // 2段目 OCR 文字列生成
                    $ocr2Data2 = substr($barcode,8).'00000'.'2'; //CD1用
                    $ocr2Cd2 = $this->calculateCd2($ocr2Data2,json_decode(config('define.ocr.OCR2TOKEN2')));
                    $ocr2Cd1 = $this->calculateCd1($ocr2Cd2.$ocr2Data2, json_decode(config('define.ocr.OCR2TOKEN1')));
                    $ocr2 = $ocr2Cd1 . $ocr2Cd2 . $ocr2Data2;
                }
        
                // 5. 請求書番号発行
                $modelNumAllocateN = $this->getBillingNo();
        
                // 新規保存
                $model = new BillingOutputModel();
                $model->m_account_id            = $this->accountId;
                $model->entry_operator_id       = $operator_id;
        
                // 必要なデータを保存
                $model->t_order_hdr_id          = $billingHdr['t_order_hdr_id'];
                $model->t_billing_hdr_id        = $billingHdr['t_billing_hdr_id'];
                $model->billing_no              = $modelNumAllocateN;
                $model->cvs_barcode             = $barcode;
                $model->jp_ocr_code_1           = $ocr1;
                $model->jp_ocr_code_2           = $ocr2;
                $model->is_available            = 1;
                $model->payment_due_date        = $dueDate->format('Y-m-d'); //支払期限日
        
                // 出力日時、フラグ設定
                $model->output_at               = $output_at;
                $model->is_output               = !empty($output_at) ? 1 : 0;   //出力フラグ
                $model->template_id             = $template_id;
        
                $model->is_reprint = 0;
                $model->is_remind  = 0;
                $model->save();
                
                BillingOutputModel::where('t_billing_hdr_id', $billing_id)
                    ->where('is_available', 1) // 有効フラグが1のもの
                    ->where('t_billing_outputs_id', '!=', $model->t_billing_outputs_id) // 自分以外
                    ->update(['is_available' => 0]); // 有効フラグを0に更新
        
                return $model;
            });
        }catch (DataNotFoundException $e) {
            \Log::error($e->getMessage());
            throw $e;
        }
    }
    /**
     * バーコードのチェックデジットを取得する
     */
    private function calculateBarcodeCd(string $code): string
    {
        $reverse = strrev($code);
        $even = 0;
        $odd = 0;
        for ($idx = 0; $idx < strlen($reverse); $idx++) {
            if ($idx % 2 == 0) {
                $even += (int)$reverse[$idx];
            } else {
                $odd += (int)$reverse[$idx];
            }
        }
        $even *= 3;
        $c = (string)($even + $odd);
        return substr($c, -1); // チェックディジットを取得
    }
    /**
     * CD2を取得する
     */
    private function calculateCd2(string $data, array $weights): string
    {
        $sum = 0;
        foreach (str_split($data) as $index => $digit) {
            $sum += (int) $digit * $weights[$index];
        }
        return (string) ($sum % 10); // 下1桁
    }

    /**
     * CD2を取得する
     */
    private function calculateCd1(string $data, array $weights): string
    {
        $sum = 0;
        foreach (str_split($data) as $index => $digit) {
            $sum += (int) $digit * $weights[$index];
        }
        return (string) ($sum % 11); // 余りを引いて算出
    }
}
