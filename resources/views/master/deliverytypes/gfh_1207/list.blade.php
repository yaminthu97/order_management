@php
    $ScreenCd = 'NEMSMF0010';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '配送方法マスタ検索画面')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>配送方法マスタ検索画面</li>
@endsection

@section('content') 
@if( !empty($viewMessage) )
    <div class="c-box--1700 c-tbl-border-all u-p--sm sy_notice u-mb--ss">
        @foreach($viewMessage as $message)
            <p class="icon_sy_notice_03">{{$message}}</p>
        @endforeach
    </div>
@endif

<div class="u-mt--xs">
    <form enctype="multipart/form-data" method="POST" action="" name="Form1" id="Form1">
        {{ csrf_field() }}

        {{-- 検索入力フォーム --}}
        <table class="table table-bordered c-tbl c-tbl--600">
            <tr>
                <th class="c-box--150">使用区分</th>
                <td>
                    @foreach ($deleteFlg as $delete_value => $delete_label)
                    <label class="checkbox-inline">
                        <input type="checkbox" name="delete_flg[]" value="{{ $delete_value }}"
                        @checked(isset($searchRow['delete_flg']) && in_array($delete_value, $searchRow['delete_flg']))>
                        {{ $delete_label }}
                    </label>
                    @endforeach
                </td>
            </tr>

            <tr>
                <th class="c-box--150">配送方法</th>
                <td>
                    @foreach ($deliveryTypes as $delivery_type_value => $delivery_type_label)
                    <label class="checkbox-inline">
                        <input type="checkbox" name="delivery_type[]" value="{{ $delivery_type_value }}"
                        @checked(isset($searchRow['delivery_type']) && in_array($delivery_type_value, $searchRow['delivery_type']))>
                        {{ $delivery_type_label }}
                    </label>
                    @endforeach
                </td>
            </tr>

            <tr>
                <th class="c-box--150">配送方法名</th>
                <td>
                    <input type="text" class="form-control c-box--300" name="m_delivery_type_name" value="{{ $searchRow['m_delivery_type_name'] ?? "" }}">
                    <label class="checkbox-inline"><input type="checkbox" name="m_delivery_type_name_fuzzy_search_flg" value="1" @if(!empty($searchRow['m_delivery_type_name_fuzzy_search_flg'])) checked @endif>あいまい検索</label>
                </td>
            </tr>
        </table>

        <div class="u-mt--sm">
            <button class="btn btn-success btn-lg u-mt--sm js_disabled_button" type="submit" name="submit" id="submit_search" value="search">検索</button>&nbsp;<button type="button" class="btn btn-default btn-lg u-mt--sm" onClick="location.href='./new'">新規登録</button>
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
            <th>@include('common.elements.sorting_column_name', ['columnName' => 'm_delivery_types_id', 'columnViewName' => 'ID'])</th>
            <th>配送方法名</th>
            <th>配送方法種類</th>
            <th>@include('common.elements.sorting_column_name', ['columnName' => 'm_delivery_sort', 'columnViewName' => '並び順'])</th>
        </tr>
        @if(isset($paginator) && $paginator->count() > 0) 
        @foreach($paginator as $deliveryTypes) 
            <tr>
                <td>{{ $deliveryTypes->displayDeleteFlg ?? null }}</td>
                <td><a href='./edit/{{$deliveryTypes->m_delivery_types_id}}'>{{$deliveryTypes['m_delivery_types_id'] ?? null}}</a></td>
                <td>{{ $deliveryTypes->m_delivery_type_name ?? null}}</td>
                <td>{{ $deliveryTypes->displayDeliverType ?? null }}</td>
                <td>{{ $deliveryTypes->m_delivery_sort ?? null }}</a></td>
            </tr>
        @endforeach
        @else 
            <tr>
                <td colspan="5">{{ Config::get('Common.const.PageHeader.NoResultsMessage') }}</td>
                <input type="hidden" name="page_list_count" value="{{config('esm.default_page_size.master')}}">
            </tr>
        @endif
    </table>
    </div>
    @include('common.elements.paginator_footer')
    </form>
    @push('css')
        <link rel="stylesheet" href="{{ esm_internal_asset('css/master/gfh_1207/NEMSMF0010.css') }}">
    @endpush
</div>
@endsection
