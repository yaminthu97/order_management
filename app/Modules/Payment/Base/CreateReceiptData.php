<?php
namespace App\Modules\Payment\Base;

use App\Models\Claim\Gfh1207\ReceiptOutputModel;
use App\Modules\Payment\Base\CreateReceiptDataInterface;
use App\Services\ExcelReportManager;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Storage;

class CreateReceiptData implements CreateReceiptDataInterface
{
    private const PRIVATE_EXCEPTION = -1;

    private function makeExcelData($receiptOutput){
        $receiptHdr = $receiptOutput->receiptHdr;
        $order = $receiptHdr->order;
        $detail = json_decode($receiptHdr->detail_info,true);
        $rv['items'] = [
            '郵便番号',
            '住所1',
            '番地',
            '建物名',
            '顧客氏名',
            '顧客ID',
            '再発行',
            '領収書番号',
            '宛名',
            '顧客入金日',
            '入金年',
            '入金月',
            '入金日',
            '税込金額',
            '但し書き',
            '軽減税率金額',
            '標準税率金額',
            '軽減税額',
            '標準税額',
            '電話番号',
            '受注ID',
            '受注日',
            '請求金額',
            '支払方法',
            '請求番号',
            '社内メモ',
            '担当者氏名',
            '出力日時'
        ];
        $rv['data'] = [];
        if(!empty($detail['receipt_details'])){
            foreach($detail['receipt_details'] as $receipt_detail){
                $cust_payment_date = $receiptHdr->payment->cust_payment_date??'';
                $cust_payment_date_yyyy = "";
                $cust_payment_date_mm = "";
                $cust_payment_date_dd = "";
                if(!empty($cust_payment_date)){
                    $cust_payment_date_yyyy = date('Y',strtotime($cust_payment_date));
                    $cust_payment_date_mm = date('m',strtotime($cust_payment_date));
                    $cust_payment_date_dd = date('d',strtotime($cust_payment_date));
                }
                $rv['data'][] = [
                    empty($receiptHdr->postal)?"":(substr($receiptHdr->postal,0,3)."-".substr($receiptHdr->postal,3)), // 郵便番号
                    $receiptHdr->address1.$receiptHdr->address2, // 都道府県 + 市町村
                    $receiptHdr->address3, // 番地
                    $receiptHdr->address4, // 建物
                    $receiptHdr->customer_name_kanji, // 顧客氏名(様はフォーマットでつける)
                    $receiptHdr->m_cust_id, // 顧客ID
                    empty($receiptOutput->is_reprint)?'':'再発行', //再発行
                    $receiptOutput->receipt_no, // 領収書番号
                    $receipt_detail['addressee']??'', // 宛名
                    empty($cust_payment_date)?'':date('Y/m/d',strtotime($cust_payment_date)),
                    $cust_payment_date_yyyy,
                    $cust_payment_date_mm,
                    $cust_payment_date_dd,
                    $receipt_detail['tax_included_price'], // 領収金額
                    $receipt_detail['proviso']??'', // 但し書き
                    $receipt_detail['reduce_tax_excluded_price']??'0', // 8%分税抜金額
                    $receipt_detail['standard_tax_excluded_price']??'0', // 10%分税抜金額
                    $receipt_detail['reduce_tax_price']??'0', // 8%分税額
                    $receipt_detail['standard_tax_price']??'0', // 10%分税額
                    $receiptHdr->order_tel1, // 電話番号
                    $receiptHdr->t_order_hdr_id, // 受注ID
                    date('Y/m/d',strtotime($receiptHdr->order_datetime)), // 受注日
                    $receiptHdr->billing_amount, // 請求金額
                    $receiptHdr->paymentTypes->m_payment_types_name??'', // 支払方法
                    $receiptOutput->billing_no, // 請求ID
                    $order->orderMemo->operator_comment??'', // 社内メモ
                    $receiptHdr->entryOperator->m_operator_name??'', // 担当者氏名
                    date('Y/m/d H:i',strtotime($receiptOutput->output_at)), // 出力年月日
                ];
            }
        }
        return $rv;
    }    
    public function execute($receiptOutputId,$batchExecute){
        $accountCd = $batchExecute->account_cd;
        $accountId = $batchExecute->m_account_id;
        $executeBatchInstructionId = $batchExecute->t_execute_batch_instruction_id;
        // 領収書出力履歴の取得
        $receiptOutput = ReceiptOutputModel::find($receiptOutputId);
        if(empty($receiptOutput) || $receiptOutput->m_account_id != $accountId || $receiptOutput->is_available != 1){
            throw new Exception(__('messages.error.data_not_found',['data'=>"領収書出力履歴ID",'id'=>$receiptOutputId]),self::PRIVATE_EXCEPTION);
        }
        // 領収基本の取得
        if(empty($receiptOutput->receiptHdr)){
            throw new Exception(__('messages.error.data_not_found',['data'=>"領収基本ID",'id'=>$receiptOutput->t_receipt_hdr_id]),self::PRIVATE_EXCEPTION);
        }
        // 受注基本の取得
        if(empty($receiptOutput->order)){
            throw new Exception(__('messages.error.data_not_found',['data'=>"受注基本ID",'id'=>$receiptOutput->t_order_hdr_id]),self::PRIVATE_EXCEPTION);
        }
        // テンプレートの取得
        if(empty($receiptOutput->template)){
            throw new Exception(__('messages.error.data_not_found',['data'=>"テンプレートID",'id'=>$receiptOutput->template_id]),self::PRIVATE_EXCEPTION);
        }
        // ファイル有無チェック
        $templateFile = sprintf("/%s/template/%s/%s",$accountCd,$receiptOutput->template->report_type,$receiptOutput->template->template_file_name);
        if(Storage::disk(config('filesystems.default', 'local'))->exists($templateFile) == false){
            throw new Exception(__('messages.error.data_not_found',['data'=>"テンプレートファイル：",'id'=>$templateFile]),self::PRIVATE_EXCEPTION);
        }
        $manager = null;
        try{
            $manager = new ExcelReportManager($templateFile);
        } catch(Exception $e){
            throw new Exception(__('messages.error.template_format_error',['format'=>"Excel"]),self::PRIVATE_EXCEPTION);
        }
        $manager->setValues(null, $this->makeExcelData($receiptOutput));
                // 出力済みにする
        $receiptOutput->is_output = 1;
        $receiptOutput->update_operator_id = $batchExecute->m_operators_id;
        $receiptOutput->update_timestamp = Carbon::now();
        $receiptOutput->save();

        $excelExportPath = sprintf("/%s/excel/export/%s/",$accountCd,$executeBatchInstructionId);
        $excelFilename = sprintf("%s_%s.xlsx",$receiptOutput->receipt_no,date('YmdHis'));

        return [
            'manager'=>$manager,
            'filepath'=>$excelExportPath,
            'filename'=>$excelFilename
        ];
    }
}
