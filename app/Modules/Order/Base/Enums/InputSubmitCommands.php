<?php

namespace App\Modules\Order\Base\Enums;

/**
 * バッチ実行指示に登録するInput系処理
 */
enum InputSubmitCommands: string implements InputSubmitCommandsInterface
{
    case InputOrderCsv = 'input_order_csv';
    case InputPaymentAuthCsv = 'input_payment_auth_csv';
    case InputPaymentResultCsv = 'input_payment_result_csv';
    case InputDeliveryCsv = 'input_delivery_csv';
    case InputOrderUpdateCsv = 'input_order_update_csv';
    case InputEcOrderFile = 'input_ec_order_file';

    public function label(): string
    {
        return match($this) {
            self::InputOrderCsv => '受注取込',
            self::InputPaymentAuthCsv => '与信結果取込',
            self::InputPaymentResultCsv => '入金取込',
            self::InputDeliveryCsv => '出荷取込',
            self::InputOrderUpdateCsv => '受注一括編集データ取込',
            self::InputEcOrderFile => 'Amazon TSV受注取込',
        };
    }
}
