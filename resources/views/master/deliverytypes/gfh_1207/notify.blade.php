{{-- NEMSMF0030:配送方法マスタ確認 --}}
@php
    $ScreenCd='NEMSMF0030';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '配送方法マスタ確認')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>配送方法マスタ確認</li>
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
    <form method="POST" action="">
        @csrf
        @if($mode == 'edit')
            @method('PUT')
        @endif

        {{-- 検索入力フォーム --}}
        <table class="table table-bordered c-tbl c-tbl--800">
            <tr>
                <th class="c-box--300">配送方法マスタID</th>
               	<td>{{ $records['m_delivery_types_id'] ?? "自動" }}</td>
                <input type="hidden" name="m_delivery_types_id" value="{{ $records['m_delivery_types_id'] ?? null }}">
            </tr>

            <tr> 
                <th class="c-box--150 must">使用区分</th>
                <td>{{ $deleteFlg[old('delete_flg', $records['delete_flg'] ?? 0)] }}</td>
                <input type="hidden" name="delete_flg" value="{{ old('delete_flg', $records['delete_flg'] ?? 0) }}">
            </tr>

            <tr>
                <th class="c-box--150 must">並び順</th>
                <td>{{ old('m_delivery_sort', $records['m_delivery_sort'] ?? "") }}</td>
                <input type="hidden" name="m_delivery_sort" value="{{ old('m_delivery_sort', $records['m_delivery_sort'] ?? "") }}">
            </tr>

            <tr>
                <th class="c-box--150 must">配送方法名</th>
                <td>{{ old('m_delivery_type_name', $records['m_delivery_type_name'] ?? "") }}</td>
                <input type="hidden" name="m_delivery_type_name" value="{{ old('m_delivery_type_name', $records['m_delivery_type_name'] ?? "") }}">
            </tr>

            <tr>
                <th class="c-box--150 must">配送方法種類</th>
                <td>{{ $deliveryTypes[old('delivery_type', $records['delivery_type'] ?? "100")] }}</td>
                <input type="hidden" name="delivery_type" value="{{ old('delivery_type', $records['delivery_type'] ?? "100") }}">
            </tr>

            <tr>
                <th class="c-box--150">温度帯別手数料（常温）</th>
                <td>{{ old('standard_fee', $records['standard_fee'] ?? "") }}</td>
                <input type="hidden" name="standard_fee" value="{{ old('standard_fee', $records['standard_fee'] ?? "") }}">
            </tr>

            <tr>
                <th class="c-box--150">温度帯別手数料（冷蔵）</th>
                <td>{{ old('chilled_fee', $records['chilled_fee'] ?? "") }}</td>
                <input type="hidden" name="chilled_fee" value="{{ old('chilled_fee', $records['chilled_fee'] ?? "") }}">
            </tr>

            <tr>
                <th class="c-box--150">温度帯別手数料（冷凍）</th>
                <td>{{ old('frozen_fee', $records['frozen_fee'] ?? "") }}</td>
                <input type="hidden" name="frozen_fee" value="{{ old('frozen_fee', $records['frozen_fee'] ?? "") }}">
            </tr>
            
            <tr class="only_seino">
                <th class="c-box--150">荷送人コード（西濃のみ有効）</th>
                <td>{{ old('shipper_cd', $records['shipper_cd'] ?? "") }}</td>
                <input type="hidden" name="shipper_cd" value="{{ old('shipper_cd', $records['shipper_cd'] ?? "") }}">
            </tr>

            <tr class="only_yamato">
                <th class="c-box--150">コレクトお客様情報（ヤマトのみ有効）</th>
                <td>{{ old('correct_info', $records['correct_info'] ?? "") }}</td>
                <input type="hidden" name="correct_info" value="{{ old('correct_info', $records['correct_info'] ?? "") }}">
            </tr>

            <tr class="only_yamato">
                <th class="c-box--150">ヤマトマスタパックファイル（ヤマトのみ有効）</th>
                <td>{{ old('file_name', $input['file_name'] ?? "") }}</td>
                <input type="hidden" name="file_name" value="{{ old('file_name', $input['file_name'] ?? "") }}">
            </tr>
        </table>

        <div class="u-mt--sm">
            <button type="submit" name="submit" value="cancel" class="btn btn-default btn-lg u-mt--sm">キャンセル</button>     
            <button type="submit" name="submit" value="register" class="btn btn-success btn-lg u-mt--sm">{{ (isset($records['m_delivery_types_id'])) ? '更新' :  '新規登録'}}</button>
           
        </div>
        {{-- hidden --}}
        <input type="hidden" name="{{config('define.session_key_id')}}" value="{{$records[config('define.session_key_id')] ?? ''}}">
        <input type="hidden" name="deferred_payment_delivery_id" value="{{$records['deferred_payment_delivery_id'] ?? ''}}">
        <input type="hidden" name="m_delivery_type_code" value="{{ $records['m_delivery_type_code'] ?? "" }}">
        <input type="hidden" name="correct_info" value="{{ $records['correct_info'] ?? "" }}">
    </form>
    @push('js')
        <script src="{{ esm_internal_asset('js/master/gfh_1207/NEMSMF0030.js') }}"></script>
    @endpush
</div>
@endsection
