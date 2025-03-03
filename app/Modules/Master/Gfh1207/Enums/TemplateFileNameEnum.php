<?php

namespace App\Modules\Master\Gfh1207\Enums;

/**
 * 帳票テンプレートファイル名一覧
 */
enum TemplateFileNameEnum: string
{
    case EXPXLSX_ORDER_HDR = '受注一覧表';
    case EXPXLSX_ORDER_DESTINATION = '受注送付先一覧表';
    case EXPXLSX_ORDER_DTL = '受注明細一覧表';
    case EXPXLSX_ORDER_CONFIRMATION = 'ご注文承り確認書';
    case EXPXLSX_ESTIMATE = '見積書1';
    case EXPXLSX_ESTIMATE_2 = '見積書2';
    case EXPXLSX_ORDER_DAILY = '日別商品別受注一覧';
    case EXPXLSX_SHPPING_FAX = '出荷確定確認FAX送付状';
    case EXPXLSX_RETURN_FAX = '返送依頼FAX送付状';
    case EXPXLSX_SHIPMENT_REPORTS_CHECKLIST = '出荷検品チェックリスト';
    case EXPXLSX_SHIPPED_SEARCH_SKU = '出荷未出荷一覧_SKU別';
    case EXPXLSX_SHIPPED_SEARCH_DATE = '出荷未出荷一覧_出荷予定日別';
    case EXPXLSX_SHIPPED_SEARCH = '出荷未出荷一覧_商品別';
    case EXPXLSX_SHIPMENT_REPORTS_STATUS_PG = '出荷ステータスPG';
    case EXPXLSX_INSPECTION = '検品済一覧';
    case EXPXLSX_INSPECTION_DETAIL = '検品済一覧明細';
    case EXPXLSX_SHIPMENT_REPORTS_SHIPPED_BAG = '手提げ出荷未出荷一覧';
    case EXPXLSX_SHIPMENT_REPORTS_SCHEDULED_BAG = '出荷予定日別手提げ枚数';
    case EXPXLSX_SHIPMENT_REPORTS_CARDBOARD_WORK = '段ボール作業日別使用枚数一覧';
    case EXPXLSX_BILLING = '請求書1';
    case EXPXLSX_BILLING_2 = '請求書2';
    case EXPXLSX_BILLING_3 = '請求書3';
    case EXPXLSX_BILLING_4 = '請求書4';
    case EXPXLSX_BILLING_EXCEL = 'EXCEL請求書1';
    case EXPXLSX_BILLING_EXCEL_2 = 'EXCEL請求書2';
    case EXPXLSX_BILLING_EXCEL_3 = 'EXCEL納品書1';
    case EXPXLSX_BILLING_EXCEL_4 = 'EXCEL納品書2';
    case EXPXLSX_DELIVERY_NOTE = '納品書';
    case EXPXLSX_BILLING_REMIND_EXCEL = '督促請求書1';
    case EXPXLSX_BILLING_REMIND_EXCEL_2 = '督促請求書2';
    case EXPXLSX_BILLING_REMIND_EXCEL_3 = '督促請求書3';
    case EXPXLSX_BILLING_LIST = '請求一覧表';
    case EXPXLSX_RECEIPT = '領収書1';
    case EXPXLSX_RECEIPT_2 = '領収書2';
    case EXPXLSX_RECEIPT_3 = '領収書3';
    case EXPXLSX_RECEIPT_LIST = '領収一覧表';
    case EXPXLSX_DM_ANALYTICS = 'DM集計';
    case EXPXLSX_CUST_COMMUNICATION_DETAIL = '顧客対応照会履歴';
    case EXPXLSX_EXTERNAL_SALES = '外販申し送りファイル';
    case EXPXLSX_FURUSATO_TAX = 'ふるさと納税一覧';
    case SEND_EC_ORDER_XLSX = '通信販売売上・受注残';
    case EXPXLSX_INSPECTION_OUT = '帳票定義_検品済一覧';
    case EXPXLSX_INSPECTION_DETAIL_OUT = '帳票定義_検品済一覧明細';
    case EXPXLSX_ORDER_SHIPPING = '帳票定義_商品別受注数・出荷数';


    public function id(): int
    {
        return match($this) {
            self::EXPXLSX_ORDER_HDR => 100,
            self::EXPXLSX_ORDER_DESTINATION => 101,
            self::EXPXLSX_ORDER_DTL => 102,
            self::EXPXLSX_ORDER_CONFIRMATION => 103,
            self::EXPXLSX_ESTIMATE => 104,
            self::EXPXLSX_ESTIMATE_2 => 105,
            self::EXPXLSX_ORDER_DAILY => 106,
            self::EXPXLSX_SHPPING_FAX => 200,
            self::EXPXLSX_RETURN_FAX => 201,
            self::EXPXLSX_SHIPMENT_REPORTS_CHECKLIST => 202,
            self::EXPXLSX_SHIPPED_SEARCH_SKU => 203,
            self::EXPXLSX_SHIPPED_SEARCH_DATE => 204,
            self::EXPXLSX_SHIPPED_SEARCH => 205,
            self::EXPXLSX_SHIPMENT_REPORTS_STATUS_PG => 206,
            self::EXPXLSX_INSPECTION => 207,
            self::EXPXLSX_INSPECTION_DETAIL => 208,
            self::EXPXLSX_SHIPMENT_REPORTS_SHIPPED_BAG => 209,
            self::EXPXLSX_SHIPMENT_REPORTS_SCHEDULED_BAG => 210,
            self::EXPXLSX_SHIPMENT_REPORTS_CARDBOARD_WORK => 211,
            self::EXPXLSX_BILLING => 300,
            self::EXPXLSX_BILLING_2 => 301,
            self::EXPXLSX_BILLING_3 => 302,
            self::EXPXLSX_BILLING_4 => 303,
            self::EXPXLSX_BILLING_EXCEL => 310,
            self::EXPXLSX_BILLING_EXCEL_2 => 311,
            self::EXPXLSX_BILLING_EXCEL_3 => 320,
            self::EXPXLSX_BILLING_EXCEL_4 => 321,
            self::EXPXLSX_DELIVERY_NOTE => 322,
            self::EXPXLSX_BILLING_REMIND_EXCEL => 330,
            self::EXPXLSX_BILLING_REMIND_EXCEL_2 => 331,
            self::EXPXLSX_BILLING_REMIND_EXCEL_3 => 332,
            self::EXPXLSX_BILLING_LIST => 340,
            self::EXPXLSX_RECEIPT => 350,
            self::EXPXLSX_RECEIPT_2 => 351,
            self::EXPXLSX_RECEIPT_3 => 352,
            self::EXPXLSX_RECEIPT_LIST => 353,
            self::EXPXLSX_DM_ANALYTICS => 400,
            self::EXPXLSX_CUST_COMMUNICATION_DETAIL => 401,
            self::EXPXLSX_EXTERNAL_SALES => 402,
            self::EXPXLSX_FURUSATO_TAX => 403,
            self::SEND_EC_ORDER_XLSX => 404,
            self::EXPXLSX_INSPECTION_OUT => 500,
            self::EXPXLSX_INSPECTION_DETAIL_OUT => 501,
            self::EXPXLSX_ORDER_SHIPPING => 502,
        };
    }

}
