@php
    $ScreenCd = 'NEMSMJ0010';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '支払方法マスタ検索画面')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
    <li>支払方法マスタ検索画面</li>
@endsection
@section('content')
    @if (!empty($viewMessage))
        <div class="c-box--1700 c-tbl-border-all u-p--sm sy_notice u-mb--ss">
            @foreach ($viewMessage as $message)
                <p class="icon_sy_notice_03">{{ $message }}</p>
            @endforeach
        </div>
    @endif

    <div class="u-mt--xs">
        <form enctype="multipart/form-data" method="POST" action="" name="Form1" id="Form1">
            {{ csrf_field() }}

            {{-- 検索入力フォーム --}}
            <table class="table table-bordered c-tbl c-tbl--1100">
                <tr>
                    <th class="c-box--150">使用区分</th>
                    <td>
                        @foreach (\App\Enums\DeleteFlg::cases() as $deleteFlg)
                            <label class="checkbox-inline">
                                <input type="checkbox" name="delete_flg[]" value="{{ $deleteFlg->value }}"
                                    @checked(
                                        ($loop->first && empty($searchRow['delete_flg'])) ||
                                            (isset($searchRow['delete_flg']) && in_array($deleteFlg->value, $searchRow['delete_flg'])))>
                                {{ $deleteFlg->label() }}
                            </label>
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150">支払方法種類</th>
                    <td>
                        @foreach (\App\Enums\PaymentMethodTypeEnum::cases() as $payment_type)
                            <label class="checkbox-inline">
                                <input type="checkbox" name="payment_type[]" value="{{ $payment_type->value }}"
                                    @checked(isset($searchRow['payment_type']) && in_array($payment_type->value, $searchRow['payment_type']))>
                                {{ $payment_type->label() }}
                            </label>
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150">支払方法名</th>
                    <td>
                        <input type="text" class="form-control c-box--300" name="m_payment_types_name"
                            value="{{ $searchRow['m_payment_types_name'] ?? '' }}">
                        <label class="checkbox-inline"><input type="checkbox" name="m_payment_types_name_fuzzy_search_flg"
                                value="1" @if (!empty($searchRow['m_payment_types_name_fuzzy_search_flg'])) checked @endif>あいまい検索</label>
                    </td>
                </tr>
            </table>
            <div class="u-mt--sm">
                <button class="btn btn-success btn-lg u-mt--sm" type="submit" name="submit" id="submit_search"
                    value="search">検索</button>
                <button class="btn btn-default btn-lg u-mt--sm ml-20" type="button" name="submit"
                    id="submit_new"value="new"
                    onClick="location.href='{{ route('master.payment_types.new') }}'">新規登録</button>
            </div>
            {{-- 検索結果表示フォーム --}}
            <div class="u-mt--sm">
                @include('common.elements.paginator_header')
                @include('common.elements.page_list_count')
                <br>
                @include('common.elements.sorting_script')
                <table class="table table-bordered c-tbl link-style" name="searchResults">
                    <tr>
                        <th>使用区分</th>
                        <th>@include('common.elements.sorting_column_name', [
                            'columnName' => 'm_payment_types_id',
                            'columnViewName' => 'ID',
                        ])</th>
                        <th>支払方法種類</th>
                        <th>支払方法名</th>
                        <th>配送条件</th>
                        <th>@include('common.elements.sorting_column_name', [
                            'columnName' => 'm_payment_types_sort',
                            'columnViewName' => '並び順',
                        ])</th>
                    </tr>

                    @if(isset($paginator) && $paginator->count() > 0)
                    @foreach ($paginator as $paymentTypes)
                        <tr>
                            <td>
                                @if (isset($paymentTypes->delete_flg) && $paymentTypes->delete_flg == \App\Enums\DeleteFlg::Use->value)
                                    {{ \App\Enums\DeleteFlg::Use->label() }}
                                @else
                                    {{ \App\Enums\DeleteFlg::Notuse->label() }}
                                @endif
                            </td>
                            <td><a
                                    href="{{ route('master.payment_types.edit', ['id' => $paymentTypes->m_payment_types_id]) }}">{{ $paymentTypes['m_payment_types_id'] ?? null }}</a>
                            </td>
                            <td>
                                @foreach (\App\Enums\PaymentMethodTypeEnum::cases() as $paymentType)
                                    @if (isset($paymentTypes['payment_type']) && $paymentTypes['payment_type'] == $paymentType->value)
                                        {{ $paymentType->label() }}
                                    @endif
                                @endforeach
                            </td>
                            <td>
                                @if (isset($paymentTypes->m_payment_types_name) && mb_strlen($paymentTypes->m_payment_types_name) > 30)
                                    {{ mb_substr($paymentTypes->m_payment_types_name, 0, 30) . '…' }}
                                @else
                                    {{ $paymentTypes->m_payment_types_name ?? '' }}
                                @endif
                            </td>
                            <td>
                                @foreach (\App\Modules\Master\Gfh1207\Enums\DeliveryConditionEnum::cases() as $deliveryCondition)
                                    @if (isset($paymentTypes['delivery_condition']) && $paymentTypes['delivery_condition'] == $deliveryCondition->value)
                                        {{ $deliveryCondition->label() }}
                                    @endif
                                @endforeach
                            </td>
                            <td>{{ $paymentTypes->m_payment_types_sort ?? null }}</a></td>
                        </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="6">{{Config::get('Common.const.PageHeader.NoResultsMessage') }}</td>
                        <input type="hidden" name="page_list_count" value="{{config('esm.default_page_size.master')}}">
                    </tr>
                    @endif
                </table>
            </div>
            @include('common.elements.paginator_footer')
        </form>
    </div>
@endsection
