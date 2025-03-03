{{-- NECSM0114:顧客照会 --}}
@php
    $ScreenCd = 'NECSM0114';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '顧客照会')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
    <li>顧客照会</li>
@endsection

@section('content')
    @session('messages.error.exception_message')
        <span class="font-FF0000">{{ $value }}</span>
    @endsession
    <form method="POST" action="" name="Form1" id="Form1">
        {{ csrf_field() }}
        <div class="d-table c-box--1200">
            <div class="c-box--600Half">
                <table class="table c-tbl c-tbl--590">
                    <tr>
                        <th class="c-box--200">使用区分</th>
                        <td>{{ $sample->display_delete_flg }}</td>
                    </tr>
                    <tr>
                        <th class="c-box--200">顧客ID</th>
                        <td>{{ $sample->m_cust_id }}</td>
                    </tr>
                    <tr>
                        <th class="c-box--200">フリガナ</th>
                        <td>
                            {{  $sample->name_kana }}
                        </td>
                    </tr>
                    <tr>
                        <th class="c-box--200">名前</th>
                        <td>
                            {{ $sample->name_kanji }}
                        </td>
                    </tr>
                    <tr>
                        <th class="c-box--200">郵便番号</th>
                        <td>{{ $sample->display_postal }}</td>
                    </tr>
                    <tr>
                        <th class="c-box--200">都道府県</th>
                        <td>{{ $sample->address1 }}</td>
                    </tr>
                    <tr>
                        <th>市区町村</th>
                        <td>{{  $sample->address2 }}</td>
                    </tr>
                    <tr>
                        <th>番地</th>
                        <td>{{ $sample->address3 }}</td>
                    </tr>
                    <tr>
                        <th>建物名</th>
                        <td>{{ $sample->address4 }}</td>
                    </tr>
                </table>
            </div>
            <div class="c-box--600Half">
                <table class="table c-tbl c-tbl--590">
                    <tr>
                        <td class="c-box--200" colspan="2">
                            <span class="font-FF0000">
                                {{ $sample->display_deleted_label }}
                            </span>&nbsp;
                        </td>
                    </tr>
                    <tr>
                        <th class="c-box--200">顧客コード</th>
                        <td>{{ $sample->cust_cd }}</td>
                    </tr>
                    <tr>
                        <th class="c-box--200">電話番号</th>
                        <td>
                            <ul>
                                @if (!empty($sample->tel1))
                                    <li class="u-input--small">{{ $sample->tel1 }}</li>
                                @endif
                                @if (!empty($sample->tel2))
                                    <li class="u-input--small">{{ $sample->tel2 }}</li>
                                @endif
                                @if (!empty($sample->tel3))
                                    <li class="u-input--small">{{ $sample->tel3 }}</li>
                                @endif
                                @if (!empty($sample->tel4))
                                    <li class="u-input--small">{{ $sample->tel4 }}</li>
                                @endif

                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <th>FAX番号</th>
                        <td>
                            {{ $sample->fax }}
                        </td>
                    </tr>
                    <tr>
                        <th class="c-box--200">フリガナ</th>
                        <td>{{ $sample->corporate_kana}}</td>
                    </tr>
                    <tr>
                        <th>法人名・団体名</th>
                        <td>{{ $sample->corporate_kanji}}</td>
                    </tr>
                    <tr>
                        <th>部署名</th>
                        <td>{{ $sample->division_name}}</td>
                    </tr>
                    <tr>
                        <th>勤務先電話番号</th>
                        <td>{{ $sample->corporate_tel}}</td>
                    </tr>
                    <tr>
                        <th class="c-box--200">顧客ランク</th>
                        <td>
                            {{ $sample->custRunk?->m_itemname_type_name }}
                        </td>
                    </tr>
                </table>
            </div>
        </div><!-- /.d-table -->
        <div class="c-box--600">
            <div class="c-box--600Half">
                <table class="table c-tbl c-tbl--590">
                    <tr>
                        <th class="c-box--200">メールアドレス</th>
                        <td>
                            {{ $sample->email1}}@if (!empty($sample->email1))
                                <br>
                            @endif
                            {{ $sample->email2}}@if (!empty($sample->email2))
                                <br>
                            @endif
                            {{ $sample->email3}}@if (!empty($sample->email3))
                                <br>
                            @endif
                            {{ $sample->email4}}@if (!empty($sample->email4))
                                <br>
                            @endif
                            {{ $sample->email5}}@if (!empty($sample->email5))
                                <br>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="c-box--200">備考</th>
                        <td>{!! nl2br(e($sample->note)) !!}</td>
                    </tr>
                </table>
            </div>
            <div class="c-box--600Half">
                <table class="table c-tbl c-tbl--590">
                    <tr>
                        <th class="c-box--200">顧客区分</th>
                        <td>
                            {{ $sample->customerCategory?->m_itemname_type_name }}
                        </td>
                    </tr>
                    <tr>
                        <th class="c-box--200">割引率</th>
                        <td>
                            {{ $sample->display_discount_rate }}
                        </td>
                    </tr>
                </table>
            </div>
        </div><!-- /.cbox1200 -->
        <div class="d-table c-box--1200">
            <div class="c-box--600Half">
                <table class="table c-tbl c-tbl--590">
                    <tr>
                        <th class="c-box--200">性別</th>
                        <td>
                            {{ $sample->display_sex_type }}
                        </td>
                    </tr>
                    <tr>
                        <th>誕生日</th>
                        <td>
                            {{ $sample->birthday}}
                        </td>
                    </tr>
                </table>
            </div><!-- /600Half -->

            <div class="c-box--600Half">
                <table class="table c-tbl c-tbl--590">
                    <tr>
                        <th class="c-box--200">DM配送方法 郵便</th>
                        <td>
                            {{ $sample->display_dm_send_letter_flg }}
                        </td>
                    </tr>
                    <tr>
                        <th class="c-box--200">DM配送方法 メール</th>
                        <td>
                            {{ $sample->display_dm_send_mail_flg }}
                        </td>
                    </tr>

                    <tr>
                        <th>要注意区分</th>
                        <td>
                            <span @class([
                                "font-FF0000" => $sample->alert_cust_type !== \App\Enums\AlertCustTypeEnum::NO_ALERT->value,
                            ])>
                            {{ $sample->display_alert_cust_type }}
                        </td>
                    </tr>

                    <tr>
                        <th>要注意コメント</th>
                        <td>
                            <span class="font-FF0000">
                            {!! nl2br(e($sample->alert_cust_comment)) !!}
                            </span>
                        </td>
                    </tr>
                </table>
            </div><!-- /600Half -->
        </div><!-- /.d-table -->


        <div class="c-box--1200">
            <div class="c-btn--02"><a data-toggle="collapse" href="#collapse-menu" class="collapsed">自由項目</a></div>
            <!-- 詳細アコーディオンここから -->
            <div class="collapse" id="collapse-menu">

                <div class="d-table c-box--1200 u-mt--xs">
                    <div class="c-box--600Half">
                        <table class="table c-tbl c-tbl--590">
                            <tr>
                                <th class="c-box--200"><label for="">ブラック理由</label></th>
                                <td>{{ $sample->reserve1}}</td>
                            </tr>
                            <tr>
                                <th class="c-box--200"><label for="">注意顧客理由</label></th>
                                <td>{{ $sample->reserve2}}</td>
                            </tr>
                            <tr>
                                <th class="c-box--200"><label for="">貸倒</label></th>
                                <td>{{ $sample->reserve3}}</td>
                            </tr>
                            <tr>
                                <th class="c-box--200"><label for="">お客様情報</label></th>
                                <td>{{ $sample->reserve4}}</td>
                            </tr>
                            <tr>
                                <th class="c-box--200"><label for="">問合せ連絡</label></th>
                                <td>{{ $sample->reserve5}}</td>
                            </tr>
                            <tr>
                                <th class="c-box--200"><label for="">備考</label></th>
                                <td>{{ $sample->reserve6}}</td>
                            </tr>
                            <tr>
                                <th class="c-box--200"><label for="">請求関連</label></th>
                                <td>{{ $sample->reserve7}}</td>
                            </tr>
                            <tr>
                                <th class="c-box--200"><label for="">督促</label></th>
                                <td>{{ $sample->reserve8}}</td>
                            </tr>
                            <tr>
                                <th class="c-box--200"><label for="">住所変更</label></th>
                                <td>{{ $sample->reserve9}}</td>
                            </tr>
                            <tr>
                                <th class="c-box--200"><label for="">Web会員番号</label></th>
                                <td>{{ $sample->reserve10}}</td>
                            </tr>
                        </table>
                    </div><!--/.cbox600Half-->


                    <div class="c-box--600Half">
                        <table class="table c-tbl c-tbl--590">
                            <tr>
                                <th class="c-box--200"><label for="">自由項目11</label></th>
                                <td>{{ $sample->reserve11}}</td>
                            </tr>
                            <tr>
                                <th class="c-box--200"><label for="">自由項目12</label></th>
                                <td>{{ $sample->reserve12}}</td>
                            </tr>
                            <tr>
                                <th class="c-box--200"><label for="">自由項目13</label></th>
                                <td>{{ $sample->reserve13}}</td>
                            </tr>
                            <tr>
                                <th class="c-box--200"><label for="">自由項目14</label></th>
                                <td>{{ $sample->reserve14}}</td>
                            </tr>
                            <tr>
                                <th class="c-box--200"><label for="">自由項目15</label></th>
                                <td>{{ $sample->reserve15}}</td>
                            </tr>
                            <tr>
                                <th class="c-box--200"><label for="">自由項目16</label></th>
                                <td>{{ $sample->reserve16}}</td>
                            </tr>
                            <tr>
                                <th class="c-box--200"><label for="">自由項目17</label></th>
                                <td>{{ $sample->reserve17}}</td>
                            </tr>
                            <tr>
                                <th class="c-box--200"><label for="">自由項目18</label></th>
                                <td>{{ $sample->reserve18}}</td>
                            </tr>
                            <tr>
                                <th class="c-box--200"><label for="">自由項目19</label></th>
                                <td>{{ $sample->reserve19}}</td>
                            </tr>
                            <tr>
                                <th class="c-box--200"><label for="">自由項目20</label></th>
                                <td>{{ $sample->reserve20}}</td>
                            </tr>
                        </table>
                    </div><!--/.cbox600Half-->
                </div><!--/.d-table-->
            </div><!--/.collapse-->
        </div>



        <div class="d-table c-box--1200 u-mt--ss">
            <div class="c-box--600Half">
                <table class="table c-tbl c-tbl--590">
                    <tr>
                        <th class="c-box--150">購入累計金額</th>
                        <td class="c-box--145 u-right">{{ $sample->custOrderSum?->display_total_order_money }}</td>
                    </tr>
                    <tr>
                        <th class="c-box--150">購入回数</th>
                        <td class="c-box--145 u-right">{{ $sample->custOrderSum?->total_order_count }}</td>
                    </tr>
                    <tr>
                        <th class="c-box--200">最新購入日</th>
                        <td>{{ $sample->custOrderSum?->newest_order_date }}</td>
                    </tr>
                    <tr>
                        <th class="c-box--150">初回購入店舗</th>
                        <td colspan="3">{{ $sample->custOrderSum?->firstEcs?->m_ec_name }}</td>
                    </tr>
                    <tr>
                        <th>最新購入店舗</th>
                        <td>{{ $sample->custOrderSum?->newestEcs?->m_ec_name }}</td>
                    </tr>
                </table>
            </div><!-- /600Half -->

            <div class="c-box--600Half">
                <table class="table c-tbl c-tbl--590">
                    <tr>
                        <th class="c-box--200">未請求金額</th>
                        <td class="c-box--145 u-right">{{ $sample->custOrderSum?->display_total_unbilled_money }}</td>
                    </tr>
                    <tr>
                        <th class="c-box--200">未入金金額</th>
                        <td class="c-box--145 u-right">{{ $sample->custOrderSum?->display_total_undeposited_money }}</td>
                    </tr>
                    <tr>
                        <th class="c-box--200">督促回数</th>
                        <td class="c-box--145 u-right">{{ $sample->custOrderSum?->total_remind_count }}</td>
                    </tr>
                    <tr>
                        <th class="c-box--200">返品回数</th>
                        <td class="c-box--145 u-right">{{ $sample->custOrderSum?->total_return_count }}</td>
                    </tr>

                </table>
            </div><!-- /600Half -->
        </div><!-- /.d-table -->

        <div class="u-mt--ss">
            <button class="btn btn-default btn-lg" type="submit" name="submit" formtarget="_blank" id="submit_customeredit" value="customeredit">顧客情報修正</button>
            <button class="btn btn-success btn-lg" type="submit" name="submit" formtarget="_blank" id="submit_customerhistorynew" value="customerhistorynew">新規対応履歴</button>
            <button class="btn btn-success btn-lg" type="submit" name="submit" formtarget="_blank" id="submit_mailnew" value="mailnew">メール送信</button>
            @if(!$sample->isDeleted())
                <button class="btn btn-success btn-lg" type="submit" name="submit" id="submit_ordernew" value="ordernew">新規注文</button>
            @endif

        </div>

        <div id="tabs" class="u-mt--sl">
            <div class="c-box--1200">
                <ul>
                    <li><a href="#tabs-1">注文履歴</a></li>
                    <li><a href="#tabs-2">対応履歴</a></li>
                    <li><a href="#tabs-3">メール送信履歴</a></li>
                </ul>
            </div>

            <div class="tabs-inner">
                <!-- tabs-1ここから -->
                <div id="tabs-1">
                    <div class="d-table c-box--1180">
                        <table class="table table-bordered c-tbl c-tbl--1180">
                            <tr>
                                <th class="u-center c-box--60">受注ID</th>
                                <th>受注編集</th>
                                <th>進捗区分</th>
                                <th class="c-box--200">受注日時</th>
                                <th>ECサイト</th>
                                <th>請求金額</th>
                                <th>支払方法</th>
                                <th>要注意</th>
                                <th>住所</th>
                                <th>指定日</th>
                                <th>与信</th>
                                <th>入金</th>
                                <th>引当</th>
                                <th>出荷予定日</th>
                                <th>出荷指示</th>
                                <th>出荷確定</th>
                            </tr>

                            @if (count($orders) > 0)
                                @foreach ($orders as $order)
                                    <tr @class([
                                        'c-states--02' => in_array($order->progress_type,[
                                            \App\Enums\ProgressTypeEnum::PendingConfirmation->value,
                                            \App\Enums\ProgressTypeEnum::PendingCredit->value,
                                            \App\Enums\ProgressTypeEnum::PendingPrepayment->value,
                                            \App\Enums\ProgressTypeEnum::PendingAllocation->value,
                                        ]),
                                        'c-states--03' => $order->progress_type === \App\Enums\ProgressTypeEnum::PendingShipment->value,
                                        'c-states--04' => $order->progress_type === \App\Enums\ProgressTypeEnum::Shipping->value,
                                        'c-states--05' => $order->progress_type === \App\Enums\ProgressTypeEnum::Shipped->value,
                                        'c-states--06' => $order->progress_type === \App\Enums\ProgressTypeEnum::PendingPostPayment->value,
                                        'c-states--07' => $order->progress_type === \App\Enums\ProgressTypeEnum::Completed->value,
                                        'c-states--08' => $order->progress_type === \App\Enums\ProgressTypeEnum::Cancelled->value,
                                        'c-states--09' => $order->progress_type === \App\Enums\ProgressTypeEnum::Returned->value,
                                    ])>
                                        <td><a href=""
                                                target="_blank">{{ $order->t_order_hdr_id }}&nbsp;<i
                                                    class="fas fa-external-link-alt"></i></a></td>
                                        <td class="u-center">
                                            @if($order->canEdit())
                                                <a href="">編集</a>
                                            @endif
                                        </td>
                                        <td>{{ $order->display_progress_type }}</td>
                                        <td>{{ $order->order_datetime }}</td>
                                        <td>{{ $order->ecs?->m_ec_name }}</td>
                                        <td class="u-right">{{ $order->display_order_total_price }}</td>
                                        <td class="">{{ $order->paymentTypes?->m_payment_types_name }}</td>
                                        <td class="u-center">
                                            <span @class([
                                                'glyphicon' => true,
                                                'glyphicon-remove' => $order->alert_cust_check_type === \App\Enums\AlertCustCheckTypeEnum::UNCONFIRMED->value,
                                                'glyphicon-ok-sign' => $order->alert_cust_check_type === \App\Enums\AlertCustCheckTypeEnum::CONFIRMED->value,
                                                'glyphicon-minus' => $order->alert_cust_check_type === \App\Enums\AlertCustCheckTypeEnum::EXCLUDED->value,
                                            ])></span>
                                        </td>
                                        <td class="u-center">
                                            <span @class([
                                                'glyphicon' => true,
                                                'glyphicon-remove' => $order->address_check_type === \App\Enums\AddressCheckTypeEnum::UNCONFIRMED->value,
                                                'glyphicon-ok-sign' => $order->address_check_type === \App\Enums\AddressCheckTypeEnum::CONFIRMED->value,
                                                'glyphicon-minus' => $order->address_check_type === \App\Enums\AddressCheckTypeEnum::EXCLUDED->value,
                                            ])></span>
                                        </td>
                                        <td class="u-center">
                                            <span @class([
                                                'glyphicon' => true,
                                                'glyphicon-remove' => $order->deli_hope_date_check_type === \App\Enums\DeliHopeDateCheckTypeEnum::UNCONFIRMED->value,
                                                'glyphicon-ok-sign' => $order->deli_hope_date_check_type === \App\Enums\DeliHopeDateCheckTypeEnum::CONFIRMED->value,
                                                'glyphicon-minus' => $order->deli_hope_date_check_type === \App\Enums\DeliHopeDateCheckTypeEnum::EXCLUDED->value,
                                            ])></span>
                                        </td>
                                        <td class="u-center">
                                            <span @class([
                                                'glyphicon' => true,
                                                'glyphicon-remove' => ($order->credit_type === \App\Enums\CreditTypeEnum::UNPROCESSED->value || $order->credit_type === \App\Enums\CreditTypeEnum::CREDIT_NG->value),
                                                'glyphicon-ok-sign' => $order->credit_type === \App\Enums\CreditTypeEnum::CREDIT_OK->value,
                                                'glyphicon-minus' => $order->credit_type === \App\Enums\CreditTypeEnum::EXCLUDED->value,
                                            ])></span>
                                        </td>
                                        <td class="u-center">
                                            <span @class([
                                                'glyphicon' => true,
                                                'glyphicon-remove' => $order->payment_type === \App\Enums\PaymentTypeEnum::NOT_PAID->value,
                                                'glyphicon-ok-circle' => $order->payment_type === \App\Enums\PaymentTypeEnum::PARTIALLY_PAID->value,
                                                'glyphicon-ok-sign' => $order->payment_type === \App\Enums\PaymentTypeEnum::PAID->value,
                                                'glyphicon-minus' => $order->payment_type === \App\Enums\PaymentTypeEnum::EXCLUDED->value,
                                            ])></span>
                                        </td>
                                        <td class="u-center">
                                            <span @class([
                                                'glyphicon' => true,
                                                'glyphicon-remove' => $order->reservation_type === \App\Enums\ReservationTypeEnum::NOT_RESERVED->value,
                                                'glyphicon-ok-circle' => $order->reservation_type === \App\Enums\ReservationTypeEnum::PARTIALLY_RESERVED->value,
                                                'glyphicon-ok-sign' => $order->reservation_type === \App\Enums\ReservationTypeEnum::RESERVED->value,
                                                'glyphicon-minus' => $order->reservation_type === \App\Enums\ReservationTypeEnum::EXCLUDED->value,
                                            ])></span>
                                        </td>
                                        <td>
                                            {{ $order->deli_plan_date }}
                                        </td>
                                        <td class="u-center">
                                            <span @class([
                                                'glyphicon' => true,
                                                'glyphicon-remove' => $order->deli_instruct_type === \App\Enums\DeliInstructTypeEnum::NOT_INSTRUCTED->value,
                                                'glyphicon-ok-circle' => $order->deli_instruct_type === \App\Enums\DeliInstructTypeEnum::PARTIALLY_INSTRUCTED->value,
                                                'glyphicon-ok-sign' => $order->deli_instruct_type === \App\Enums\DeliInstructTypeEnum::INSTRUCTED->value,
                                                'glyphicon-minus' => $order->deli_instruct_type === \App\Enums\DeliInstructTypeEnum::EXCLUDED->value,
                                            ])></span>
                                        </td>
                                        <td class="u-center">
                                            <span @class([
                                                'glyphicon' => true,
                                                'glyphicon-remove' => $order->deli_decision_type === \App\Enums\DeliDecisionTypeEnum::NOT_DECIDED->value,
                                                'glyphicon-ok-circle' => $order->deli_decision_type === \App\Enums\DeliDecisionTypeEnum::PARTIALLY_DECIDED->value,
                                                'glyphicon-ok-sign' => $order->deli_decision_type === \App\Enums\DeliDecisionTypeEnum::DECIDED->value,
                                                'glyphicon-minus' => $order->deli_decision_type === \App\Enums\DeliDecisionTypeEnum::EXCLUDED->value,
                                            ])></span>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="7">{{__('messages.info.display.no_data', ['data' => '注文履歴'])}}</td>
                                </tr>
                            @endif
                        </table>

                        <div>
                            @if(!$sample->isDeleted())
                                <button class="btn btn-default" type="submit" name="submit" id="submit_ordernew" value="ordernew">新規注文</button>
                            @endif
                            <button class="btn btn-default" type="submit" name="submit" id="submit_cccustomerorder" formtarget="_blank" value="cccustomerorder">もっと見る</button>
                        </div>
                    </div><!--/.d-table-->
                </div><!-- tabs-1ここまで -->

                <!-- tabs-2ここから -->
                <div id="tabs-2">
                    <div class="d-table c-box--1180">
                        <table class="table table-bordered c-tbl c-tbl--1180">
                            <tr>
                                <th class="c-box--200">最新受信日時</th>
                                <th class="c-box--200">初回受信日時</th>
                                <th>タイトル</th>
                                <th>ステータス</th>
                                <th>受信内容</th>
                                <th>受信者</th>
                                <th>回答内容</th>
                                <th>回答者</th>
                            </tr>

                            @if (count($custCommunications) > 0)
                                @foreach ($custCommunications as $custCommunication)
                                    <tr>
                                        <td>
                                            <a href="" target="_blank">
                                                {{ $custCommunication->latestCustCommunicationDtl?->receive_datetime }}&nbsp;
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="" target="_blank">
                                                {{ $custCommunication->oldestCustCommunicationDtl?->receive_datetime }}&nbsp;
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </td>
                                        <td>
                                            {{ $custCommunication->title }}
                                        </td>
                                        <td>
                                            {{ $custCommunication->custCommunicationStatus?->m_itemname_type_name }}
                                        </td>
                                        <td title="{{ $custCommunication->receive_detail_min }}">
                                            {{ $custCommunication->receive_detail }}
                                        </td>
                                        <td>
                                            {{ $custCommunication->receiveOperator?->m_operator_name }}
                                        </td>
                                        <td title="{{ $custCommunication->answer_detail_min }}">
                                            {{ $custCommunication->answer_detail }}
                                        </td>
                                        <td>
                                            {{ $custCommunication->answerOperator?->m_operator_name }}
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="7">{{__('messages.info.display.no_data', ['data' => '対応履歴'])}}</td>
                                </tr>
                            @endif
                        </table>
                        <div>
                            <button class="btn btn-default" type="submit" name="submit" id="submit_customerhistorynew" value="customerhistorynew">新規対応履歴</button>
                            <button class="btn btn-default" type="submit" name="submit" id="submit_customerhistorylist" formtarget="_blank" value="customerhistorylist">もっと見る</button>
                        </div>
                    </div><!--/.d-table-->
                </div><!-- tabs-2ここまで -->

                <!-- tabs-3ここから -->
                <div id="tabs-3">
                    <div class="d-table c-box--1180">
                        <table class="table table-bordered c-tbl c-tbl--1180">
                            <tr>
                                <th class="c-box--200">登録日時</th>
                                <th>テンプレート名</th>
                                <th>タイトル</th>
                                <th>受注ID</th>
                                <th>送信者</th>
                                <th>送信日時</th>
                                <th>送信状況</th>
                            </tr>

                            @if (count($mailSendHistories) > 0)
                                @foreach ($mailSendHistories as $mailSendHistory)
                                    <tr>
                                        <td>
                                            {{-- <a href="{{ config('env.app_subsys_url.cc') }}cc-customer-mail/info/{{ $mailSendHistory->t_mail_send_history_id }}" target="_blank"> --}}
                                            <a href="{{ esm_external_route('cc-customer-mail/info/{id}', ['id'=> $mailSendHistory->t_mail_send_history_id]) }}" target="_blank">
                                                {{ $mailSendHistory->entry_timestamp }}&nbsp;
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </td>
                                        <td>{{ $mailSendHistory->emailTemplates?->m_email_templates_name }}</td>
                                        <td>{{ $mailSendHistory->mail_title }}</td>
                                        <td>{{ $mailSendHistory->t_order_hdr_id }}</td>
                                        <td>{{ $mailSendHistory->entryOperator?->m_operator_name }}</td>
                                        <td>{{ $mailSendHistory->mail_send_timestamp }}</td>
                                        <td>{{ $mailSendHistory->mail_send_status_name }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    {{-- <td colspan="7">メール送信履歴が見つかりません。</td> --}}
                                    <td colspan="7">{{__('messages.info.display.no_data', ['data' => 'メール送信履歴'])}}</td>
                                </tr>
                            @endif

                        </table>

                        <div>
                            <button class="btn btn-default" type="submit" name="submit" id="submit_mailnew" formtarget="_blank" value="mailnew">メール送信</button>
                            <button class="btn btn-default" type="submit" name="submit" id="submit_cccustomermail" formtarget="_blank" value="cccustomermail">もっと見る</button>
                        </div>

                    </div><!--/.d-table-->
                </div>
                <!-- tabs-3ここまで -->





            </div><!-- tabs-inner -->
        </div>
        <!-- tabs -->

        <br>
    </form>
    <!-- タブcssここから -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <!-- タブcssここまで -->



    <!-- tabここから -->
    <script>
    $( function() {
    $( "#tabs" ).tabs();
    } );
    </script>
    <!-- tabここまで -->
@endsection
