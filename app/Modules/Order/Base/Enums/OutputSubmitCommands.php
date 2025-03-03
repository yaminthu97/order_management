<?php

namespace App\Modules\Order\Base\Enums;

/**
 * バッチ実行指示に登録するOutput系処理
 */
enum OutputSubmitCommands: string implements OutputSubmitCommandsInterface
{
    case ChangeProgress = 'change_progress';
    case SendTemplateMail = 'send_template_mail';
    case Payment = 'payment';
    case SendReciptMail = 'send_recipt_mail'; // 未使用?
    case NewSendReciptMail = 'new_send_recipt_mail'; // 未使用?
    case ReserveStock = 'reserve_stock';
    case ChangeDeliveryType = 'change_delivery_type';
    case ChangeDeliHopeDate = 'change_deli_hope_date';
    case ChangeDeliPlanDate = 'change_deli_plan_date';
    case ChangeDeliDecisionDate = 'change_deli_decision_date';
    case ChangeOperatorComment = 'change_operator_comment';
    case AddOrderTag = 'add_order_tag';
    case RemoveOrderTag = 'remove_order_tag';
    case OutputPaymentAuthCsv = 'output_payment_auth_csv';
    case OutputPdf = 'output_pdf'; // 未使用?
    case OutputDeliveryCsv = 'output_delivery_csv'; // 未使用?
    case OutputPaymentDeliveryResultCsv = 'output_payment_delivery_result_csv';
    case OutputDeliveryFile = 'output_delivery_file';
    case ReOutputDeliveryFile = 're_output_delivery_file';
    case OutputOrderFile = 'output_order_file';

    public function label(): string
    {
        return match($this) {
            self::ChangeProgress => '進捗区分変更',
            self::SendTemplateMail => 'メール送信',
            self::Payment => '入金',
            self::SendReciptMail => '領収書メール送信', // 未使用?
            self::NewSendReciptMail => '領収書メール再送信', // 未使用?
            self::ReserveStock => '在庫引当',
            self::ChangeDeliveryType => '配送方法',
            self::ChangeDeliHopeDate => '配送希望日',
            self::ChangeDeliPlanDate => '出荷予定日',
            self::ChangeDeliDecisionDate => '配送希望日',
            self::ChangeOperatorComment => '社内メモ',
            self::AddOrderTag => 'タグをつける',
            self::RemoveOrderTag => 'タグをはずす',
            self::OutputPaymentAuthCsv => '与信データ出力',
            self::OutputPdf => 'PDF出力', // 未使用?
            self::OutputDeliveryCsv => '配送CSV出力', // 未使用?
            self::OutputPaymentDeliveryResultCsv => '出荷報告データ出力',
            self::OutputDeliveryFile => '出荷帳票・データ出力（出力）',
            self::ReOutputDeliveryFile => '出荷帳票・データ出力（再出力）',
            self::OutputOrderFile => '受注データ出力',
        };
    }
}
