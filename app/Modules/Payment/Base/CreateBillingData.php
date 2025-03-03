<?php
namespace App\Modules\Payment\Base;

use App\Enums\BillingDetailTypeEnum;
use App\Models\Claim\Gfh1207\BillingOutputModel;
use App\Models\Order\Gfh1207\DeliHdrModel;
use App\Modules\Master\Gfh1207\Enums\PaymentTypeEnum;
use App\Modules\Payment\Base\CreateBillingDataInterface;
use App\Services\BarcodeGenerator;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Storage;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class CreateBillingData implements CreateBillingDataInterface
{
    private const PRIVATE_EXCEPTION = -1;

    public const PER_PAGE = 19;
    public const DTL_ROW_TYPE1 = "1"; // 配達単位１行目(届け先、送り主名)
    public const DTL_ROW_TYPE2 = "2"; // 配達単位２行目以降(商品、送料、手数料)
    public const DTL_ROW_TYPE3 = "3"; // ブランク

    private $barcodeGenerator;

    public function __construct(BarcodeGenerator $barcodeGenerator){
        $this->barcodeGenerator = $barcodeGenerator;
    }

    private function separateByte($str,$byte){
        // シフトJISでバイト区切りを行う
        $sjis = mb_convert_encoding($str,'sjis-win','utf-8');
        $ary = mb_str_split($sjis,1,'sjis-win');
        $lineByte = 0;
        $rv = [];
        $line = '';
        foreach($ary as $val){
            if(($lineByte + strlen($val)) > $byte){
                $rv[] = $line;
                $line = $val;
                $lineByte = 0;
            } else {
                $line .= $val;
            }
            $lineByte += strlen($val);
        }
        if(strlen($line) > 0){
            $rv[] = $line;
        }
        return mb_convert_encoding($rv,'utf-8','sjis-win');
    }
    private function makePdfData($templateData,$billingOutput){
        $billingHdr = $billingOutput->billingHdr;
        $order_no = sprintf("%010d",$billingOutput->t_order_hdr_id);
        $header = [
            "title"=>array_key_exists("title",$templateData)?$templateData['title']:"", // タイトル
            "print_date"=>empty($billingOutput->output_at)?"":date('Y/m/d',strtotime($billingOutput->output_at)), // 発行日
            "comment1"=>array_key_exists("salutation1",$templateData)?$templateData["salutation1"]:"", // あいさつ文1
            "comment2"=>array_key_exists("salutation2",$templateData)?$templateData["salutation2"]:"", // あいさつ文2
            "comment3"=>array_key_exists("salutation3",$templateData)?$templateData["salutation3"]:"", // あいさつ文3
            "postal" =>empty($billingHdr->postal)?"":(substr($billingHdr->postal,0,3)."-".substr($billingHdr->postal,3)), // 郵便番号
            "address1"=>$billingHdr->address1, // 都道府県
            "address2"=>$billingHdr->address2, // 市区町村
            "address3"=>$billingHdr->address3, // 番地
            "address4"=>$billingHdr->address4, // 建物
            "corporate_kanji"=>$billingHdr->corporate_kanji, // 企業名
            "invoiced_customer_name_kanji"=>$billingHdr->invoiced_customer_name_kanji.'　様', // 請求先顧客氏名
            "invoiced_customer_id"=>$billingHdr->invoiced_customer_id, // お客様コード
            "billing_no"=>$billingOutput->billing_no, // 請求番号
            
            "paymnet_due_date"=>empty($billingOutput->payment_due_date)?"":date('Y/m/d',strtotime($billingOutput->payment_due_date)), // 支払期日
            "payment_method"=>$billingHdr->order->paymentTypes ? PaymentTypeEnum::from($billingHdr->order->paymentTypes->m_payment_types_code)->billingout_label():'',// 支払い方法

            'billing_amount'=>number_format($billingHdr->billing_amount), //お買上げ金額
            "tax_price"=>number_format($billingHdr->tax_price), //内消費税
            "reduce_tax_price"=>number_format($billingHdr->reduce_tax_price), // 内8%消費税
            "standard_tax_price"=>number_format($billingHdr->standard_tax_price), // 内10%消費税
            
            "tax_excluded_product_price"=>number_format($billingHdr->tax_excluded_price), // 税抜商品金額
            "tax_excluded_shipping_fee"=>number_format($billingHdr->tax_excluded_shipping_fee), // 税抜送料
            "tax_excluded_fee"=>number_format($billingHdr->tax_excluded_fee), // 税抜手数料

            "tax_excluded_price"=>number_format($billingHdr->reduce_tax_excluded_total_price + $billingHdr->standard_tax_excluded_total_price), // 税抜合計金額
            "reduce_tax_excluded_total_price"=>number_format($billingHdr->reduce_tax_excluded_total_price),// 8%分税抜金額
            "standard_tax_excluded_total_price"=>number_format($billingHdr->standard_tax_excluded_total_price), // 10%分税抜金額

            "discount_amount"=>number_format($billingHdr->discount_amount), // 割引金額
            "reduce_discount"=>number_format($billingHdr->reduce_discount), //割引額(軽減税率)
            "standard_discount"=>number_format($billingHdr->standard_discount),// 割引額(標準税率)

            'order_no'=>$order_no,
        ];
        $invoiced_customer_name_kanji15 = $this->separateByte($billingHdr->invoiced_customer_name_kanji."　様",30); // 全角15文字
        $invoiced_customer_name_kanji10 = $this->separateByte($billingHdr->invoiced_customer_name_kanji."　様",20); // 全角10文字
        $receipt = [
            'is_used'=>false
        ];
        if($billingHdr->order->paymentTypes && (
            PaymentTypeEnum::PREPAYMENT_CONVENIENCE_POSTAL->value == $billingHdr->order->paymentTypes->m_payment_types_code || 
            PaymentTypeEnum::CONVENIENCE_POSTAL->value == $billingHdr->order->paymentTypes->m_payment_types_code)
        ){
            $receipt = [
                "price"=>floor($billingHdr->billing_amount),
                "price_f"=>number_format($billingHdr->billing_amount),
                "ocr1"=>$billingOutput->jp_ocr_code_1,
                "ocr2"=>$billingOutput->jp_ocr_code_2,
                "customer_name"=>$billingHdr->invoiced_customer_name_kanji."　様",
                "order_no"=>$order_no,
                "billing_no"=>$billingOutput->billing_no, // 請求番号
                "invoiced_customer_id"=>$billingHdr->invoiced_customer_id,
                "paymnet_due_date_yyyy"=>empty($billingOutput->payment_due_date)?"":date('Y',strtotime($billingOutput->payment_due_date)),
                "paymnet_due_date_mm"=>empty($billingOutput->payment_due_date)?"":date('m',strtotime($billingOutput->payment_due_date)),
                "paymnet_due_date_dd"=>empty($billingOutput->payment_due_date)?"":date('d',strtotime($billingOutput->payment_due_date)),
                "invoiced_customer_name_kanji15_1"=>$invoiced_customer_name_kanji15[0]??'',
                "invoiced_customer_name_kanji15_2"=>$invoiced_customer_name_kanji15[1]??'',
                "invoiced_customer_name_kanji15_3"=>$invoiced_customer_name_kanji15[2]??'',
                "invoiced_customer_name_kanji15_4"=>$invoiced_customer_name_kanji15[3]??'',
                "invoiced_customer_name_kanji10_1"=>$invoiced_customer_name_kanji10[0]??'',
                "invoiced_customer_name_kanji10_2"=>$invoiced_customer_name_kanji10[1]??'',
                "invoiced_customer_name_kanji10_3"=>$invoiced_customer_name_kanji10[2]??'',
                "invoiced_customer_name_kanji10_4"=>$invoiced_customer_name_kanji10[3]??'',
                "invoiced_customer_name_kanji10_5"=>$invoiced_customer_name_kanji10[4]??'',
                'is_used'=>true,
            ];
        }
        $detail_data = json_decode($billingHdr->detail_info,true);
        $detail = [];
        foreach($detail_data['destinations']??[] as $e){
            // 出荷完了日はjsonに含まれないためDeliHdrから取得する
            $query = DeliHdrModel::query()->where('t_order_destination_id',$e['order_destination_id']);
            $query->where(function ($q) {
                $q->whereNull('cancel_timestamp')
                  ->orWhere('cancel_timestamp',0);
            });
            $deliHdr = $query->first();
            if((count($detail) % self::PER_PAGE) != 0 && (count($detail) % self::PER_PAGE) != (self::PER_PAGE - 1)){
                $detail[] = [
                    'type'=>self::DTL_ROW_TYPE3,
                ];
            }
            $destination_name = $e['destination_company_name']??'';
            if(strlen($destination_name) > 0){
                $destination_name .= ' ';
            }
            $destination_name .= $e['destination_name']."様";
            $detail[] = [
                    'type'=>self::DTL_ROW_TYPE1,
                    'deli_decision_date'=>(!empty($deliHdr) && !empty($deliHdr->deli_decision_date))?date('Y/m/d',strtotime($deliHdr->deli_decision_date)):'',
                    'destination_name'=>$destination_name,
                    'sender_name'=>$e['sender_name'],
            ];
            foreach($e['billing_details']??[] as $e2){
                if(empty($e2['display_flag'])){
                    continue;
                }
                $display_code = $this->separateByte($e2['display_code']??'',13); // 半角13文字
                $display_name = $this->separateByte($e2['display_name']??'',60); // 全角30文字
                if($e2['detail_type'] == BillingDetailTypeEnum::SHIPPING_FEE->value || $e2['detail_type'] == BillingDetailTypeEnum::PAYMENT_FEE->value){
                    // 送料、手数料
                    $d =  [
                        'type'=>self::DTL_ROW_TYPE2,
                        'display_name'=>$display_name[0],
                        'amount'=>number_format($e2['amount']??'0')
                    ];
                    if(isset($e2['quantity'])){
                        $d['quantity'] = number_format($e2['quantity']??'0');
                    }
                    if(isset($e2['unit_price'])){
                        $d['unit_price'] = number_format($e2['unit_price']??'0');
                    }
                    $detail[] = $d;
                } elseif($e2['detail_type'] == BillingDetailTypeEnum::ATTACHMENT_ITEM->value){
                    // 付属品
                    $d =  [
                        'type'=>self::DTL_ROW_TYPE2,
                        'display_code'=>$display_code[0],
                        'display_name'=>$display_name[0]
                    ];
                    if(isset($e2['quantity'])){
                        $d['quantity'] = number_format($e2['quantity']??'0');
                    }
                    $detail[] = $d;
                } else {
                    $noshi = "";
                    if(($e2['noshi_omotegaki']??'') != '' || ($e2['noshi_addressee']??'') != ''){
                        // どちらかの設定があったら出力する
                        $n = ($e2['noshi_omotegaki']??'').'/'.($e2['noshi_addressee']??'');
                        $noshiAry = $this->separateByte($n,32); // 全角16文字
                        $noshi = $noshiAry[0];           
                    }
                    $detail[] = [
                        'type'=>self::DTL_ROW_TYPE2,
                        'display_code'=>$display_code[0],
                        'display_name'=>$display_name[0],
                        'noshi'=>$noshi,
                        'quantity'=>number_format($e2['quantity']??'0'),
                        'unit_price'=>number_format($e2['unit_price']??'0'),
                        'amount'=>number_format($e2['amount']??'0'),
                    ];
                }
            }
        }
        $data['header'] = $header;
        $data['receipt'] = $receipt;
        $data['pages'] = array_chunk($detail,self::PER_PAGE);
        return $data;
    }
    private function getTemplateValue($templateFile){
        $rv = [];
        try {
            $fileContents = Storage::disk(config('filesystems.default', 'local'))->get($templateFile);
            $tempFilePath = tempnam(sys_get_temp_dir(), 'xlsx');
            file_put_contents($tempFilePath, $fileContents);
            $reader = new Xlsx();
            $reader->setReadDataOnly(false);
            $spreadSheet = $reader->load($tempFilePath);
            unlink($tempFilePath);
            $sheet = $spreadSheet->getSheet(0);
            foreach ($sheet->getRowIterator() as $row) {
                $rv[$sheet->getCell("A".$row->getRowIndex())->getValue()] = $sheet->getCell("B".$row->getRowIndex())->getValue();
            }
            return $rv;
        } catch (Exception $ex) {
            throw new Exception(__('messages.error.template_format_error'),self::PRIVATE_EXCEPTION);
        }
    }

    public function execute($billingOutputId,$batchExecute,$tempPdfFilePath){
        $accountCd = $batchExecute->account_cd;
        $accountId = $batchExecute->m_account_id;
        $executeBatchInstructionId = $batchExecute->t_execute_batch_instruction_id;

        $billingOutput = BillingOutputModel::query()->find($billingOutputId);
        if(empty($billingOutput) || $billingOutput->m_account_id != $accountId || $billingOutput->is_available != 1){
            throw new Exception(__('messages.error.data_not_found',['data'=>"請求書出力履歴ID",'id'=>$billingOutputId]),self::PRIVATE_EXCEPTION);
        }
        // 請求基本の取得
        if(empty($billingOutput->billingHdr)){
            throw new Exception(__('messages.error.data_not_found',['data'=>"請求基本ID",'id'=>$billingOutput->t_billing_hdr_id]),self::PRIVATE_EXCEPTION);
        }
        // 受注基本の取得
        if(empty($billingOutput->order)){
            throw new Exception(__('messages.error.data_not_found',['data'=>"受注基本ID",'id'=>$billingOutput->t_order_hdr_id]),self::PRIVATE_EXCEPTION);
        }
        // テンプレートの取得
        if(empty($billingOutput->template)){
            throw new Exception(__('messages.error.data_not_found',['data'=>"テンプレートID",'id'=>$billingOutput->template_id]),self::PRIVATE_EXCEPTION);
        }
        // ファイル有無チェック
        $templateFile = sprintf("/%s/template/%s/%s",$accountCd,$billingOutput->template->report_type,$billingOutput->template->template_file_name);
        if(Storage::disk(config('filesystems.default', 'local'))->exists($templateFile) == false){
            throw new Exception(__('messages.error.data_not_found',['data'=>"テンプレートファイル：",'id'=>$templateFile]),self::PRIVATE_EXCEPTION);
        }
        $templateValue = $this->getTemplateValue($templateFile);
        // PDF用データ作成
        $data = $this->makePdfData($templateValue,$billingOutput);
        $data["receipt"]['publisher'] = $batchExecute->account->account_name; // 事業者

        if($data["receipt"]['is_used']){
            // バーコードデータ作成
            $barcodeData = $this->barcodeGenerator->generateBarcodeImage($billingOutput->cvs_barcode??'');
            $data["receipt"] = array_merge($data['receipt'],$barcodeData);
        }

        $pdf = LaravelMpdf::loadView('order.base.pdf.payment', $data);
        $pdf->save($tempPdfFilePath);
        if($data["receipt"]['is_used']){
            $this->barcodeGenerator->removeBarcodeImageFile($billingOutput->cvs_barcode??'');
        }
        
        // 出力済みにする
        $billingOutput->is_output = 1;
        $billingOutput->update_operator_id = $batchExecute->m_operators_id;
        $billingOutput->update_timestamp = Carbon::now();
        $billingOutput->save();

        $pdfExportPath = sprintf("/%s/pdf/export/%s/",$accountCd,$executeBatchInstructionId);
        $pdfFilename = sprintf("%s_%s.pdf",$billingOutput->billing_no,date('YmdHis'));

        return [
            'pdf'=>$pdf,
            'filepath'=>$pdfExportPath,
            'filename'=>$pdfFilename
        ];
    }
}