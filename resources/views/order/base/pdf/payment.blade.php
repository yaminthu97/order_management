@php
  use App\Modules\Payment\Base\CreateBillingData;
@endphp

<html>
<head>
    <title>Sample PDF</title>
    <style>
        html,body {
            margin: 0;
            padding:0;
            font-size:8pt;
            font-family: "mincho";
        }
        .text-center{
            text-align:center;
        }
        .text-right{
            text-align:right;
        }
        table {
            border-spacing:0;
        }
        /*　ヘッダー部 */
        .header {
            height: 70mm;
            width: 100%;
            position: relative;
            border: #000000 1px solid;
        }
        .header-title {
            position: absolute;
            right: 10mm;
            top: 3mm;
            font-size:14pt;
            text-align:center;
        }
        .header-print-date {
            position: absolute;
            right: 10mm;
            top: 9.5mm;
            font-size:9pt;
            text-align:right;
        }
        .header-postal {
            position: absolute;
            left: 24mm;
            top: 9.4mm;
            font-size:9pt;
        }
        .header-address1 {
            position: absolute;
            left: 44.5mm;
            top: 9.4mm;
            font-size:9pt;
        }
        .header-address2 {
            position: absolute;
            left: 24mm;
            top: 13.6mm;
            font-size:9pt;
        }
        .header-address3 {
            position: absolute;
            left: 24mm;
            top: 17.9mm;
            font-size:9pt;
        }
        .header-corporate_name {
            position: absolute;
            left: 24mm;
            top: 24.8mm;
            font-size:9pt;
        }
        .header-name {
            position: absolute;
            left: 27.2mm;
            top: 28.5mm;
            width:100mm;
            text-align: left;
            font-size:9pt;
        }
        .header-customer-code {
            position: absolute;
            left: 27.2mm;
            top: 39.1mm;
            font-size:8pt;
        }
        .header-comment1 {
            position: absolute;
            left: 9.3mm;
            top: 52.8mm;
            font-size:8pt;
        }
        .header-comment2 {
            position: absolute;
            left: 9.3mm;
            top: 56.6mm;
            font-size:8pt;
        }
        .header-comment3 {
            position: absolute;
            left: 9.3mm;
            top: 60.5mm;
            font-size:8pt;
        }
        .header-billing_no {
            position: absolute;
            left: 12mm;
            top: 69.0mm;
            font-size:8pt;
        }
        /* お買い上げ金額*/
        .header-product_price_area {
            position: absolute;
            left: 15mm;
            top: 73.5mm;
            font-size:8pt;
        }
        .header-tax_included_product_price_prefix {
            font-size:8pt;
            border-bottom: 1px solid #000;            
        }
        .header-tax_included_product_price {
            font-size:10pt;
            width:20mm;
            text-align:right;
            border-bottom: 1px solid #000;            
        }
        .header-tax_included_product_price_suffix {
            font-size:8pt;
            border-bottom: 1px solid #000;            
        }
        .header-tax_price_prefix,.header-standard_tax_price_prefix,.header-reduce_tax_price_prefix {
            font-size:8pt;
        }
        .header-tax_price,.header-standard_tax_price , .header-reduce_tax_price {
            font-size:8pt;
            text-align:right;
        }
        .header-tax_price_suffix,.header-standard_tax_price_suffix , .header-reduce_tax_price_suffix {
            font-size:8pt;
        }
        /* 支払い情報(右側) */
        .header-payment_area {
            position: absolute;
            right: 8mm;
            top: 66mm;
        }
        .header-payment_title {
            width:40mm;
            font-size:8pt;
            text-align:right;
        }
        .header-payment_value {
            width:40mm;
            font-size:8pt;
            text-align:right;
        }
        /* 支払い情報(右側) */
        .header-payment_area2 {
            position: absolute;
            right: 8mm;
            top: 74mm;
        }
        /** ボーダー */
        .border-top_left {
            border-left: 0.5px solid #000;
            border-top: 0.5px solid #000;
        }
        .border-top_right {
            border-right: 0.5px solid #000;
            border-top: 0.5px solid #000;
        }
        .border-top {
            border-top: 0.5px solid #000;
        }
        .border-top_bottom {
            border-top: 0.5px solid #000;
            border-bottom: 0.5px solid #000;
        }
        .border-top_left_bottom {
            border-top: 0.5px solid #000;
            border-left: 0.5px solid #000;
            border-bottom: 0.5px solid #000;
        }
        .border-top_right_bottom {
            border-top: 0.5px solid #000;
            border-right: 0.5px solid #000;
            border-bottom: 0.5px solid #000;
        }
        .border-top_left_right {
            border-top: 0.5px solid #000;
            border-left: 0.5px solid #000;
            border-right: 0.5px solid #000;
        }
        .border-top2_left {
            border-top: 0.5px dotted #000;
            border-left: 0.5px solid #000;
        }
        .border-top2_left_right {
            border-top: 0.5px dotted #000;
            border-left: 0.5px solid #000;
            border-right: 0.5px solid #000;
        }
        .header-payment2_title{
            width:25mm;
            font-size:8pt;
        }
        .header-payment2_value{
            width:25mm;
            font-size:8pt;
            text-align:right;
        }
        .header-payment2_yen{
            width:5mm;
            font-size:8pt;
            text-align:right;
        }
        .header-order_no {
            position: absolute;
            left: 8mm;
            top: 95mm;
        }
        /*　詳細 */
        .detail-area {
            position: absolute;
            left: 8mm;
            width:194mm;
            top: 100mm;
        }
        .detail-padding {
            padding-top:0px!important;
            padding-bottom:0px!important;
        }
        .detail-area tr td {
            border-top: 0.5px solid #000 !important;
        }
        .detail-deli_decision_date {
            width:20mm;
        }
        .detail-destination_name {
            width:87mm;
        }
        .detail-sender_name {
            width:87mm;
        }
        .detail-omotegaki {
            width:49mm;
            text-align:right;
        }
        .detail-quantity {
            width:12mm;
            text-align:right;
        }
        .detail-amount {
            width:12mm;
            text-align:right;
        }
        .detail-sum {
            width:14mm;
            text-align:right;
        }
        .detail_last td {
            border-bottom: 0.5px solid #000;
        }
        .detail_row1 .column1 {
            text-align:center;
            border-top: 0.5px solid #000;
            border-left: 0.5px solid #000;
        }
        .detail_row1 .column2 {
            border-top: 0.5px solid #000;
            border-left: 0.5px solid #000;
        }
        .detail_row1 .column3 {
            border-top: 0.5px solid #000;
            border-left: 0.5px solid #000;
            border-right: 0.5px solid #000;
        }
        .detail_row2 td {
            border-top: 0.5px dotted #000;
        }
        .detail_row2.detail_first td {
            border-top: 0.5px solid #000 !important;
        }
        .detail_row2 .column1 {
            border-left: 0.5px solid #000;
        }
        .detail_row2 .column2 {
            white-space: nowrap;
            border-left: 0.5px solid #000;
        }
        .detail_row2 .column3 {
            white-space: nowrap;
            border-left: 0.5px solid #000;
        }
        .detail_row2 .column4 {
            border-left: 0.5px solid #000;
            text-align:right;
        }
        .detail_row2 .column5 {
            border-left: 0.5px solid #000;
            text-align:right;
        }
        .detail_row2 .column6 {
            border-left: 0.5px solid #000;
            border-right: 0.5px solid #000;
            text-align:right;
        }

        /**  */
        .payment-sheet-notused {
            position: absolute;
            left: 38.8mm;
            top: 222.2mm;
            font-size: 32pt;
        }
        /* 本部控え */

        /** 金額 */
        .payment-sheet-price-on-honbu {
            position: absolute;
            right: 88.0mm;
            top: 196.1mm;
            font-family: ocrb;
            font-size: 10pt;
            text-align:right;
            letter-spacing: 2.9mm;
        }
        .payment-sheet-yuucho-ocr1 {
            position: absolute;
            right: 92.3mm;
            top:222mm;
            font-family: ocrb;
            font-size: 10pt;
            text-align:right;
            letter-spacing:0.42mm;
            width:100%;
        }
        .payment-sheet-yuucho-ocr2 {
            position: absolute;
            right: 92.3mm;
            top: 230.8mm;
            font-family: ocrb;
            font-size: 10pt;
            text-align:right;
            letter-spacing:0.42mm;
            width:100%;
        }
        .payment-sheet-xmark-on-store {
            position: absolute;
            left: 125.2mm;
            top:222mm;
            font-family: ocrb;
            font-size: 10pt;
        }

        /** */
        .payment-sheet-customer-name-on-honbu {
            position: absolute;
            left: 13.5mm;
            top: 242.6mm;
            font-size:10pt;
        }
        .payment-sheet-order-id-on-honbu {
            position: absolute;
            left: 25mm;
            top:246.6mm;
            font-size:8pt;
        }
        .payment-sheet-billing-id-on-honbu{
            position: absolute;
            left: 41.9mm;
            top:246.6mm;
            font-size:8pt;
        }
        .payment-sheet-customer-id-on-honbu{
            position: absolute;
            left: 56.0mm;
            top:246.6mm;
            font-size:8pt;
        }
        /** バーコード */
        .payment-sheet-barcode {
            position: absolute;
            left: 13.5mm;
            top: 252.4mm;
            width:48.8mm;
            height:10.8mm;
        }
        .barcode-img {
            width:100%;
            height:10.8mm;
        }
        .payment-sheet-barcode-value1 {
            position: absolute;
            left: 13.5mm;
            top: 263.0mm;
            font-size:8pt;
        }
        .payment-sheet-barcode-value2 {
            position: absolute;
            left: 13.5mm;
            top: 265.6mm;
            font-size:8pt;
        }
        .payment-sheet-hikae-label-on-honbu {
            position: absolute;
            left: 75mm;
            top: 264.5mm;
            font-size: 10pt;
            letter-spacing: 0;
        }
        .payment-sheet-year-on-honbu {
            position: absolute;
            left: 62.0mm;
            top:269.8mm;
            font-size: 8pt;
        }
        .payment-sheet-month-on-honbu {
            position: absolute;
            width:2rem;
            left: 73.6mm;
            top:269.8mm;
            font-size: 8pt;
        }
        .payment-sheet-day-on-honbu {
            position: absolute;
            width:2rem;
            left: 83.0mm;
            top:269.8mm;
            font-size: 8pt;
        }

        /* 店舗控え */
        /** X */
        /*
        .payment-sheet-xmark-on-store {
            position: absolute;
            left: 125.91375mm;
            top:225.0625mm;
            font-family: ocrb;
            font-size: 10pt;
            letter-spacing: 0.12em;
        }
        */
        /** 金額 */
        .payment-sheet-price-on-store {
            position: absolute;
            right: 29.5mm;
            top: 217.8mm;
            font-family: ocrb;
            font-size: 10pt;
            text-align:right;
            letter-spacing: 2.9mm;
        }
        /** 事業者 */
        .payment-sheet-publisher-on-store {
            position: absolute;
            width:16rem;
            left: 132.7mm;
            top: 225.4mm;
            font-size:8pt;
        }
        /** 氏名 */
        .payment-sheet-customer-name-on-store1 {
            position: absolute;
            left: 132.7mm;
            top: 240.4mm;
            font-size:8pt;
        }
        .payment-sheet-customer-name-on-store2 {
            position: absolute;
            width:16rem;
            left: 132.7mm;
            top: 243.4mm;
            font-size:8pt;
        }
        .payment-sheet-customer-name-on-store3 {
            position: absolute;
            width:16rem;
            left: 132.7mm;
            top: 246.4mm;
            font-size:8pt;
        }
        .payment-sheet-customer-name-on-store4 {
            position: absolute;
            width:16rem;
            left: 132.7mm;
            top: 249.4mm;
            font-size:8pt;
        }
        .payment-sheet-billing_no {
            position: absolute;
            width:16rem;
            right: 32.5mm;
            top: 252.4mm;
            text-align:right;
            font-size:8pt;
        }
        .payment-sheet-hikae-label-on-store {
            position: absolute;
            left: 166.4mm;
            top: 252.1mm;
            font-size:8pt;
        }

        /* お客様控え */
        .payment-sheet-customer-name-on-customer1 {
            position: absolute;
            left: 183.5mm;
            top: 196.6mm;
            font-size:6pt;
        }
        .payment-sheet-customer-name-on-customer2 {
            position: absolute;
            left: 183.5mm;
            top: 198.6mm;
            font-size:6pt;
        }
        .payment-sheet-customer-name-on-customer3 {
            position: absolute;
            left: 183.5mm;
            top: 200.6mm;
            font-size:6pt;
        }
        .payment-sheet-customer-name-on-customer4 {
            position: absolute;
            left: 183.5mm;
            top: 202.6mm;
            font-size:6pt;
        }
        .payment-sheet-customer-name-on-customer5 {
            position: absolute;
            left: 183.5mm;
            top: 204.6mm;
            font-size:6pt;
        }
        .payment-sheet-customer-id-on-customer {
            position: absolute;
            left: 183.5mm;
            top: 217.7mm;
            font-size:8pt;
        }
        .payment-sheet-price-on-customer {
            position: absolute;
            right: 5.4mm;
            top: 230mm;
            text-align:right;
            font-size:10pt;
        }
        .payment-sheet-tax-on-customer {
            position: absolute;
            right: 5.4mm;
            top: 235mm;
            text-align:right;
            font-size:5pt;
        }
        .payment-sheet-publisher-on-customer {
            position: absolute;
            left: 183.6mm;
            top: 246.0mm;
            font-size:6pt;
        }
    </style>
</head>

<body>
    @for($idx=0;$idx < count($pages);$idx++)
    @if($idx!=0)
    <div style="page-break-after: always;"></div>
     @endif
    {{-- ヘッダー部 --}}
    <p class="header-title">{{$header['title']}} </p>
    @if($idx==0)
    <p class="header-print-date">{{$header['print_date']}}</p>
    <p class="header-postal">〒{{$header['postal']}}</p>
    <p class="header-address1">{{$header['address1']}}{{$header['address2']}}</p>
    <p class="header-address2">{{$header['address3']}}</p>
    <p class="header-address3">{{$header['address4']}}</p>
    <p class="header-corporate_name">{{$header['corporate_kanji']}}</p>
    <p class="header-name">{{$header['invoiced_customer_name_kanji']}}</p>
    <p class="header-customer-code">お客様ID:{{$header['invoiced_customer_id']}}</p>
    <p class="header-comment1">{{$header['comment1']}}</p>
    <p class="header-comment2">{{$header['comment2']}}</p>
    <p class="header-comment3">{{$header['comment3']}}</p>
    <p class="header-billing_no">御請求No:{{$header['billing_no']}}</p>
    @endif
    <p class="header-product_price_area">
        <table style="border-spacing:0;">
            <tr style="border-bottom: 1px solid #000;">
                <td class="header-tax_included_product_price_prefix">お買い上げ金額</td>
                <td class="header-tax_included_product_price">{{$idx!=0?"*******":$header['billing_amount']}}</td>
                <td class="header-tax_included_product_price_suffix">円&nbsp;</td>
            </tr>
            <tr>
                <td class="header-tax_price_prefix">(内消費税</td>
                <td class="header-tax_price">{{$idx!=0?"*******":$header['tax_price']}}</td>
                <td class="header-tax_price_suffix">円)</td>
            </tr>
            <tr>
                <td class="header-reduce_tax_price_prefix">(内8%消費税</td>
                <td class="header-reduce_tax_price">{{$idx!=0?"*******":$header['reduce_tax_price']}}</td>
                <td class="header-reduce_tax_price_suffix">円)</td>
            </tr>
            <tr>
                <td class="header-standard_tax_price_prefix">(内10%消費税</td>
                <td class="header-standard_tax_price">{{$idx!=0?"*******":$header['standard_tax_price']}}</td>
                <td class="header-standard_tax_price_suffix">円)</td>
            </tr>
        </table>
    </p>
    <p class="header-payment_area">
        <table style="border-spacing:0;">
            <tr>
                <td class="header-payment_title">支払い期限：</td>
                <td class="header-payment_value">{{$header['paymnet_due_date']}}</td>
            </tr>
            <tr>
                <td class="header-payment_title">支払い方法：</td>
                <td class="header-payment_value">{{$header['payment_method']}}</td>
            </tr>
        </table>
    </p>
    <p class="header-payment_area2">
        <table style="border-spacing:0;">
            <tr>
                <td class="header-payment2_title border-top_left">税抜商品金額</td>
                <td class="header-payment2_value border-top">{{$idx!=0?"*******":$header['tax_excluded_product_price']}}</td>
                <td class="header-payment2_yen border-top">円&nbsp;</td>
                <td class="header-payment2_title border-top_left">税抜金額</td>
                <td class="header-payment2_value border-top">{{$idx!=0?"*******":$header['tax_excluded_price']}}</td>
                <td class="header-payment2_yen border-top_right">円&nbsp;</td>
            </tr>
            <tr>
                <td class="header-payment2_title border-top_left">税抜送料</td>
                <td class="header-payment2_value border-top">{{$idx!=0?"*******":$header['tax_excluded_shipping_fee']}}</td>
                <td class="header-payment2_yen border-top">円&nbsp;</td>
                <td class="header-payment2_title border-top_left">　　(内8%対象</td>
                <td class="header-payment2_value border-top">{{$idx!=0?"*******":$header['reduce_tax_excluded_total_price']}}</td>
                <td class="header-payment2_yen border-top_right">円)</td>
            </tr>
            <tr>
                <td class="header-payment2_title border-top_left">税抜手数料</td>
                <td class="header-payment2_value border-top">{{$idx!=0?"*******":$header['tax_excluded_fee']}}</td>
                <td class="header-payment2_yen border-top">円&nbsp;</td>
                <td class="header-payment2_title border-top_left">　　(内10%対象</td>
                <td class="header-payment2_value border-top">{{$idx!=0?"*******":$header['standard_tax_excluded_total_price']}}</td>
                <td class="header-payment2_yen border-top_right">円)</td>
            </tr>
            @if(!empty($header['discount_amount']))
            <tr>
                <td class="header-payment2_title border-top"></td>
                <td class="header-payment2_value border-top"></td>
                <td class="header-payment2_yen border-top"></td>
                <td class="header-payment2_title border-top_left">割引金額</td>
                <td class="header-payment2_value border-top">{{$idx!=0?"*******":$header['discount_amount']}}</td>
                <td class="header-payment2_yen border-top_right">円&nbsp;</td>
            </tr>
            <tr>
                <td class="header-payment2_title"></td>
                <td class="header-payment2_value"></td>
                <td class="header-payment2_yen"></td>
                <td class="header-payment2_title border-top_left">　　(内8%対象</td>
                <td class="header-payment2_value border-top">{{$idx!=0?"*******":$header['reduce_discount']}}</td>
                <td class="header-payment2_yen border-top_right">円)</td>
            </tr>
            <tr>
                <td class="header-payment2_title"></td>
                <td class="header-payment2_value"></td>
                <td class="header-payment2_yen"></td>
                <td class="header-payment2_title border-top_left_bottom">　　(内10%対象</td>
                <td class="header-payment2_value border-top_bottom">{{$idx!=0?"*******":$header['standard_discount']}}</td>
                <td class="header-payment2_yen border-top_right_bottom">円)</td>
            </tr>

            @else
            <tr>
                <td class="header-payment2_title border-top"></td>
                <td class="header-payment2_value border-top"></td>
                <td class="header-payment2_yen border-top"></td>
                <td class="header-payment2_title border-top"></td>
                <td class="header-payment2_value border-top"></td>
                <td class="header-payment2_yen border-top"></td>
            </tr>
            @endif
        </table>
    </p>
    <p class="header-order_no">受注No：{{$header['order_no']}}</p>
    {{-- 詳細部 --}}
    <p class="detail-area">
        <table>
            <tr>
                <td class="detail-deli_decision_date text-center border-top_left">出荷日</td>
                <td class="detail-destination_name text-center border-top_left">届け先</td>
                <td class="detail-sender_name text-center border-top_left_right" colspan="4">送り主名</td>
            </tr>
            <tr>
                <td class="detail-deli_decision_date text-center border-top2_left">商品コード</td>
                <td class="detail-destination_name text-center border-top2_left">商品名　※印は軽減税率（8％）適用商品</td>
                <td class="detail-omotegaki text-center border-top2_left">表書き/名入れ</td>
                <td class="detail-quantity text-center border-top2_left">数量</td>
                <td class="detail-amount text-center border-top2_left">単価</td>
                <td class="detail-sum text-center border-top2_left_right">金額</td>
            </tr>
            @for($idx2=0;$idx2 < CreateBillingData::PER_PAGE;$idx2++)
            @if(isset($pages[$idx][$idx2]))
            @if($pages[$idx][$idx2]['type'] == CreateBillingData::DTL_ROW_TYPE1)
            <tr class="detail_row1 {{$idx2==(CreateBillingData::PER_PAGE-1)?'detail_last':''}}">
                <td class="detail-padding column1" style="width:20mm;">{{$pages[$idx][$idx2]['deli_decision_date']}}</td>
                <td class="detail-padding column2" style="width:87mm;">{{$pages[$idx][$idx2]['destination_name']}}</td>
                <td class="detail-padding column3" colspan="4">{{$pages[$idx][$idx2]['sender_name']}}</td>
            </tr>
            @elseif($pages[$idx][$idx2]['type'] == CreateBillingData::DTL_ROW_TYPE2)
            <tr class="detail_row2 {{$idx2==0?'detail_first':''}} {{$idx2==(CreateBillingData::PER_PAGE-1)?'detail_last':''}} ">
                <td class="detail-padding column1">{{$pages[$idx][$idx2]['display_code']??''}}</td>
                <td class="detail-padding column2">{{$pages[$idx][$idx2]['display_name']??''}}</td>
                <td class="detail-padding column3">{{$pages[$idx][$idx2]['noshi']??''}}</td>
                <td class="detail-padding column4">{{$pages[$idx][$idx2]['quantity']??''}}</td>
                <td class="detail-padding column5">{{$pages[$idx][$idx2]['unit_price']??''}}</td>
                <td class="detail-padding column6">{{$pages[$idx][$idx2]['amount']??''}}</td>
            </tr>
            @else
            <tr class="detail_row2 {{$idx2==(CreateBillingData::PER_PAGE-1)?'detail_last':''}} ">
                <td class="detail-padding column1">&nbsp;</td>
                <td class="detail-padding column2">&nbsp;</td>
                <td class="detail-padding column3">&nbsp;</td>
                <td class="detail-padding column4">&nbsp;</td>
                <td class="detail-padding column5">&nbsp;</td>
                <td class="detail-padding column6">&nbsp;</td>
            </tr>
            @endif
            @else
            <tr class="detail_row2 {{$idx2==(CreateBillingData::PER_PAGE-1)?'detail_last':''}} ">
                <td class="detail-padding column1">&nbsp;</td>
                <td class="detail-padding column2">&nbsp;</td>
                <td class="detail-padding column3">&nbsp;</td>
                <td class="detail-padding column4">&nbsp;</td>
                <td class="detail-padding column5">&nbsp;</td>
                <td class="detail-padding column6">&nbsp;</td>
            </tr>
            @endif
            @endfor
        </table>
    </p>

    {{-- 収納票部分 --}}
    {{-- 本部控え領域 --}}
    @if($idx==0 && $receipt['is_used'])
    <p class="payment-sheet-price-on-honbu">{{$receipt['price']}}</p>
    <p class="payment-sheet-yuucho-ocr1">{{$receipt['ocr1']}}</p>
    <p class="payment-sheet-yuucho-ocr2">{{$receipt['ocr2']}}</p>
    <p class="payment-sheet-customer-name-on-honbu">{{$receipt['customer_name']}}</p>
    <p class="payment-sheet-order-id-on-honbu">{{$receipt['order_no']}}</p>
    <p class="payment-sheet-billing-id-on-honbu">{{$receipt['billing_no']}}</p>
    <p class="payment-sheet-customer-id-on-honbu">{{$receipt['invoiced_customer_id']}}</p>
    <p class="payment-sheet-barcode"><img class="barcode-img" src="{{ $receipt['barcodeFileName'] }}" border="0"></p>
    <p class="payment-sheet-barcode-value1">{{ $receipt['barcodeDisplay1'] }}</p>
    <p class="payment-sheet-barcode-value2">{{ $receipt['barcodeDisplay2'] }}</p>
<!--    <p class="payment-sheet-hikae-label-on-honbu">本部控え</p>-->
    <p class="payment-sheet-year-on-honbu">{{$receipt['paymnet_due_date_yyyy']}}</p>
    <p class="payment-sheet-month-on-honbu">{{$receipt['paymnet_due_date_mm']}}</p>
    <p class="payment-sheet-day-on-honbu">{{$receipt['paymnet_due_date_dd']}}</p>
    {{-- 店舗控え領域 --}}
    <p class="payment-sheet-xmark-on-store">X</p>
    <p class="payment-sheet-price-on-store">{{$receipt['price']}}</p>
<!--    <p class="payment-sheet-publisher-on-store">事業者：{{$receipt['publisher']}}</p>-->
    <p class="payment-sheet-customer-name-on-store1">{{$receipt['invoiced_customer_name_kanji15_1']}}</p>
    <p class="payment-sheet-customer-name-on-store2">{{$receipt['invoiced_customer_name_kanji15_2']}}</p>
    <p class="payment-sheet-customer-name-on-store3">{{$receipt['invoiced_customer_name_kanji15_3']}}</p>
    <p class="payment-sheet-customer-name-on-store4">{{$receipt['invoiced_customer_name_kanji15_4']}}</p>
    <p class="payment-sheet-billing_no">{{$header['billing_no']}}</p>
<!--    <p class="payment-sheet-hikae-label-on-store">店舗控え</p>-->
    {{-- お客様控え --}}
    <p class="payment-sheet-customer-name-on-customer1">{{$receipt['invoiced_customer_name_kanji10_1']}}</p>
    <p class="payment-sheet-customer-name-on-customer2">{{$receipt['invoiced_customer_name_kanji10_2']}}</p>
    <p class="payment-sheet-customer-name-on-customer3">{{$receipt['invoiced_customer_name_kanji10_3']}}</p>
    <p class="payment-sheet-customer-name-on-customer4">{{$receipt['invoiced_customer_name_kanji10_4']}}</p>
    <p class="payment-sheet-customer-name-on-customer5">{{$receipt['invoiced_customer_name_kanji10_5']}}</p>
    <p class="payment-sheet-customer-id-on-customer">{{$receipt['invoiced_customer_id']}}</p>
    <p class="payment-sheet-price-on-customer">{{ $receipt['price_f'] }}</p>
    <p class="payment-sheet-tax-on-customer">(内消費税額&nbsp;{{ $header['tax_price'] }}円)</p>
<!--    <p class="payment-sheet-publisher-on-customer">{{$receipt['publisher']}}</p> -->
    @else
    <p class="payment-sheet-price-on-honbu">********</p>
    <p class="payment-sheet-price-on-store">********</p>
    <p class="payment-sheet-price-on-customer">********</p>
    <p class="payment-sheet-notused">この用紙は不要です</p>
    @endif
    @endfor
</body>
</html>