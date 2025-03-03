{{-- NECSM0111:顧客受付 --}}
@php
$ScreenCd='NEOSM0251';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '入金検索')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>入金検索</li>
@endsection
@section('content')
<form method="POST" action="" name="Form1" id="Form1" enctype="multipart/form-data">
{{ csrf_field() }}
<div>
    <table class="table c-tbl">
        <tbody>
            <tr>
                <th class="c-box--150">入金登録日</th>
                <td class="c-box--380">
                    <div class="u-mt--xs d-table">
                        <div>
                            <div class="input-group date date-picker">
                                <input type="text" class="form-control" id="payment_entry_date_from" name="payment_entry_date_from" value="{{ isset($searchRow['payment_entry_date_from']) ? $searchRow['payment_entry_date_from'] : '' }}">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                        </div>
                        <div>&nbsp;～&nbsp;</div>
                        <div>
                            <div class="input-group date date-picker">
                                <input type="text" class="form-control" id="payment_entry_date_to" name="payment_entry_date_to" value="{{ isset($searchRow['payment_entry_date_to']) ? $searchRow['payment_entry_date_to'] : '' }}">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </td>
                <th class="c-box--150">顧客入金日</th>
                <td class="c-box--380">
                    <div class="u-mt--xs d-table">
                        <div>
                            <div class="input-group date date-picker">
                                <input type="text" class="form-control" id="cust_payment_date_from" name="cust_payment_date_from" value="{{ isset($searchRow['cust_payment_date_from']) ? $searchRow['cust_payment_date_from'] : '' }}">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                        </div>
                        <div>&nbsp;～&nbsp;</div>
                        <div>
                            <div class="input-group date date-picker">
                                <input type="text" class="form-control" id="cust_payment_date_to" name="cust_payment_date_to" value="{{ isset($searchRow['cust_payment_date_to']) ? $searchRow['cust_payment_date_to'] : '' }}">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th class="c-box--150">口座入金日</th>
                <td class="c-box--380">
                    <div class="u-mt--xs d-table">
                        <div>
                            <div class="input-group date date-picker">
                                <input type="text" class="form-control" id="account_payment_date_from" name="account_payment_date_from" value="{{ isset($searchRow['account_payment_date_from']) ? $searchRow['account_payment_date_from'] : '' }}">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                        </div>
                        <div>&nbsp;～&nbsp;</div>
                        <div>
                            <div class="input-group date date-picker">
                                <input type="text" class="form-control" id="account_payment_date_to" name="account_payment_date_to" value="{{ isset($searchRow['account_payment_date_to']) ? $searchRow['account_payment_date_to'] : '' }}">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </td>
                <th class="c-box--150">入金科目</th>
                <td class="c-box--380">
                    <select class="form-control c-box--150" id="payment_subject" name="payment_subject">
                        <option value=""></option>
                        @foreach( $paymentSubjectList as $paymentSubject )
                            <option value="{{ $paymentSubject['m_itemname_types_id'] }}" @if( isset($searchRow['payment_subject']) && $searchRow['payment_subject'] == $paymentSubject['m_itemname_types_id'] ) selected @endif>{{ $paymentSubject['m_itemname_type_name'] }}</option>
                        @endforeach
                    </select>
                </td>
            </tr>
            <tr>
                <th class="c-box--150">支払方法</th>
                <td class="c-box--960 tag-box" colspan="3">
                    @foreach($mPayTypeList as $mPayType)
                        <label>
                            <input type="checkbox" name="m_payment_types_id[]" id="m_payment_types_id[]" value="{{$mPayType['m_payment_types_id']}}" @if( isset($searchRow['m_payment_types_id']) && in_array($mPayType['m_payment_types_id'], $searchRow['m_payment_types_id']) ) checked @endif>
                            {{ $mPayType['m_payment_types_name'] }}
                        </label>
                    @endforeach
                </td>
            </tr>
            <tr>
                <th class="c-box--150">ECサイト</th>
                <td class="c-box--960 tag-box" colspan="3">
                    @foreach($ecsList as $ecs)
                        <label>
                            <input type="checkbox" name="m_ecs_id[]" id="m_ecs_id[]" value="{{$ecs['m_ecs_id']}}" @if( isset($searchRow['m_ecs_id']) && in_array($ecs['m_ecs_id'], $searchRow['m_ecs_id']) ) checked @endif>
                            {{ $ecs['m_ec_name'] }}
                        </label>
                    @endforeach
                </td>
            </tr>
            <tr>
                <th class="c-box--150">出荷指示区分</th>
                <td class="c-box--960 tag-box" colspan="3">
                    @foreach($deliInstructType as $key => $value)
                        <label>
                            <input type="checkbox" name="deli_instruct_type[]" id="deli_instruct_type[]" value="{{$key}}" @if( isset($searchRow['deli_instruct_type']) && in_array($key, $searchRow['deli_instruct_type']) ) checked @endif>
                            {{ $value }}
                        </label>
                    @endforeach
                </td>
            </tr>
            <tr>
                <th class="c-box--150">出荷確定区分</th>
                <td class="c-box--960 tag-box" colspan="3">
                    @foreach($deliDecisionType as $key => $value)
                        <label>
                            <input type="checkbox" name="deli_decision_type[]" id="deli_decision_type[]" value="{{$key}}" @if( isset($searchRow['deli_decision_type']) && in_array($key, $searchRow['deli_decision_type']) ) checked @endif>
                            {{ $value }}
                        </label>
                    @endforeach
                </td>
            </tr>
            <tr>
                <th class="c-box--150">受注ID</th>
                <td class="c-box--380">
                    <input type="text" class="form-control c-box--250" name="t_order_hdr_id" placeholder="" value="{{ old('t_order_hdr_id', $searchRow['t_order_hdr_id'] ?? '') }}">
                </td>
                <th class="c-box--150">顧客ID</th>
                <td class="c-box--380">
                    <input type="text" class="form-control c-box--250" name="m_cust_id" placeholder="" value="{{ old('m_cust_id', $searchRow['m_cust_id'] ?? '') }}">
                </td>
            </tr>
            <tr>
                <th class="c-box--150">ECサイト受注ID</th>
                <td class="c-box--380">
                    <input type="text" class="form-control c-box--250" name="ec_order_num" placeholder="" value="{{ old('ec_order_num', $searchRow['ec_order_num'] ?? '') }}">
                </td>
                <th class="c-box--150">受注日</th>
                <td class="c-box--380">
                    <div class="u-mt--xs d-table">
                        <div>
                            <div class="input-group date date-picker">
                                <input type="text" class="form-control" id="order_date_from" name="order_date_from" value="{{ isset($searchRow['order_date_from']) ? $searchRow['order_date_from'] : '' }}">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                        </div>
                        <div>&nbsp;～&nbsp;</div>
                        <div>
                            <div class="input-group date date-picker">
                                <input type="text" class="form-control" id="order_date_to" name="order_date_to" value="{{ isset($searchRow['order_date_to']) ? $searchRow['order_date_to'] : '' }}">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th class="c-box--150">入金区分</th>
                <td class="c-box--380 tag-box">
                    @foreach($paymentType as $key => $value)
                        <label>
                            <input type="checkbox" name="payment_type[]" id="payment_type[]" value="{{$key}}" @if( isset($searchRow['payment_type']) && in_array($key, $searchRow['payment_type']) ) checked @endif>
                            {{ $value }}
                        </label>
                    @endforeach
                </td>
                <th class="c-box--150">注文者氏名・カナ</th>
                <td class="c-box--380">
                    <input type="text" class="form-control c-box--250" name="order_name" placeholder="" value="{{ old('order_name', $searchRow['order_name'] ?? '') }}">
                </td>
            </tr>
        </tbody>
    </table>
</div>

<br>
<button class="btn btn-success btn-lg u-mt--sm" type="submit" name="submit" id="submit_search" value="search">検索</button>
</div>

<div class="u-mt--sl c-box c-tbl-border-all c-box--800">
    <table class="table table-bordered c-tbl c-tbl--800 nowrap">
        <tr>
            <th>入金取込</th>
        </tr>
    </table>

    <div  class="u-p--ss">
        <div>入金形式選択：</div>
        <div class="u-mt--sm  d-table-cell">
            <select class="form-control u-input--mid u-mr--xs" name="input_payment_csv_filetype" id="input_payment_csv_filetype">
                <option value="4">コンビニ・郵便振込取込</option>
                <option value="5">コレクト入金取込</option>
                <option value="6">クレジット入金取り込み</option>
                <option value="1">標準（入金額＋入金者）</option>
                <option value="2">標準（入金額＋備考の注文ID、氏名）</option>
                <option value="3">ジャパンネット銀行</option>
            </select>
        </div>
        <div class="d-table-cell">入金日：</div>
        <div class="c-box--218 d-table-cell">
            <div class="input-group date date-picker">
            <input type="text" class="form-control c-box--180" id="cust_payment_date" name="cust_payment_date" value="{{ isset($searchRow['cust_payment_date']) ? $searchRow['cust_payment_date'] : '' }}">
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
        </div>
        
    </div>
    <div class="u-p--ss">
        <input type="file" class="u-ib" name="csv_input_file" id="csv_input_file" form="Form1">
        <input type="submit" name="submit_csv_input" class="btn btn-default" value="入金ファイル取込">
        @include('common.elements.error_tag', ['name' => 'csv_input_error'])
    </div>
</div>

<br>
@if ($paginator)
<div>
@include('common.elements.paginator_header')
@include('common.elements.page_list_count')
@include('common.elements.sorting_script')
<br>
<div class="c-box--full u-mt--sm">
<table class="table table-bordered c-tbl table-link nowrap">
    <tr>
        <th>@include('common.elements.sorting_column_name', ['columnName' => 't_order_hdr_id', 'columnViewName' => '受注ID']) </th>
        <th>@include('common.elements.sorting_column_name', ['columnName' => 'm_cust_id', 'columnViewName' => '顧客ID'])</th>
        <th>@include('common.elements.sorting_column_name', ['columnName' => 'payment_entry_date', 'columnViewName' => '入金登録日'])</th>
        <th>@include('common.elements.sorting_column_name', ['columnName' => 'cust_payment_date', 'columnViewName' => '顧客入金日'])</th>
        <th>@include('common.elements.sorting_column_name', ['columnName' => 'account_payment_date', 'columnViewName' => '口座入金日'])</th>
        <th>請求金額</th>
        <th>入金額</th>
        <th>入金科目</th>
        <th>入金区分</th>
        <th>ECサイト</th>
        <th>@include('common.elements.sorting_column_name', ['columnName' => 'name_kanji', 'columnViewName' => '受注日']) </th>
        <th>@include('common.elements.sorting_column_name', ['columnName' => 'name_kana', 'columnViewName' => '支払い方法名']) </th>
        <th>注文者法人名</th>
        <th>注文者氏名</th>
        <th>@include('common.elements.sorting_column_name', ['columnName' => 'address1', 'columnViewName' => '注文者カナ']) </th>
        <th>@include('common.elements.sorting_column_name', ['columnName' => 'address2', 'columnViewName' => '進捗区分'])</th>
        <th>出荷指示区分</th>
        <th>出荷確定区分</th>
    </tr>
    @if ($paginator->count() > 0)
        @foreach($paginator as $payment)
        <tr>
            <td>
                @if ($payment->t_order_hdr_id)
                <a href="{{ route('order.order.info', ['id' => $payment->t_order_hdr_id ]) }}"  target="_blank">{{ $payment->t_order_hdr_id }}<i class="fas fa-external-link-alt"></i></a>
                @endif
            </td>
            <td>
                @if (isset($payment->orderHdr->m_cust_id))
                <a href="{{ route('cc.cc-customer.info', ['id' => $payment->orderHdr->m_cust_id ]) }}"  target="_blank">{{ $payment->orderHdr->m_cust_id }}<i class="fas fa-external-link-alt"></i></a>
                @endif
            </td>
            <td>
                @if ($payment->payment_entry_date)
                <a href="{{esm_external_route('order/payment/info/{payment_id}', ['payment_id' => $payment->t_payment_id])}}" target="_blank">{{ $payment->payment_entry_date }}<i class="fas fa-external-link-alt"></i></a>
                @endif
            </td>
            <td>{{ $payment->cust_payment_date }}</td>
            <td>{{ $payment->account_payment_date }}</td>
            <td class="u-right">
                @if ($payment->orderHdr)
                {{ number_format($payment->orderHdr->order_total_price) }}
                @endif
            </td>
            <td class="u-right">
                @if ($payment->orderHdr)
                {{ number_format($payment->orderHdr->payment_price) }}
                @endif
            </td>
            <td>{{ $payment->paymentSubject->m_itemname_type_name }}</td>
            <td>
                @if ($payment->orderHdr)
                {{ \App\Enums\PaymentTypeEnum::tryfrom( $payment->orderHdr->payment_type )?->label() }}
                @endif
            </td>
            <td>
                @if ($payment->orderHdr)
                {{ $payment->orderHdr->ecs->m_ec_name }}
                @endif
            </td>
            <td>
                @if ($payment->orderHdr)
                {{ (new Carbon\Carbon($payment->orderHdr->order_datetime))->format('Y/m/d') }}
                @endif
            </td>
            <td>
                @isset( $payment->orderHdr->paymentTypes )
                {{ $payment->orderHdr->paymentTypes->m_payment_types_name ?? '' }}
                @endisset
            </td>
            <td>
                @if ($payment->orderHdr)
                {{ $payment->orderHdr->order_corporate_name }}
                @endif
            </td>
            <td>
                @if ($payment->orderHdr)
                {{ $payment->orderHdr->order_name }}
                @endif
            </td>
            <td>
                @if ($payment->orderHdr)
                {{ $payment->orderHdr->order_name_kana }}
                @endif
            </td>
            <td>
                @if ($payment->orderHdr)
                {{ \App\Enums\ProgressTypeEnum::tryfrom( $payment->orderHdr->progress_type )?->label() }}
                @endif
            </td>
            <td>
                @if ($payment->orderHdr)
                {{ \App\Enums\DeliInstructTypeEnum::tryfrom( $payment->orderHdr->deli_instruct_type )?->label() }}
                @endif
            </td>
            <td>
                @if ($payment->orderHdr)
                {{ \App\Enums\DeliDecisionTypeEnum::tryfrom( $payment->orderHdr->deli_decision_type )?->label() }}
                @endif
            </td>
        </tr>
        @endforeach
    @else
        <tr>
            <td colspan="18">該当入金情報が見つかりません。</td>
        </tr>
	@endif
</table>
</div>
@include('common.elements.paginator_footer')
</div>
</form>
@endif

@include('common.elements.datetime_picker_script')

@push('css')
<link rel="stylesheet" href="{{ esm_internal_asset('css/custCommunication/scroll/app.css') }}">
@endpush

@endsection
