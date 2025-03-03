{{-- NEMSMF0020:配送方法マスタ登録・更新 --}}
@php
    $ScreenCd='NEMSMF0020';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '配送方法マスタ登録・更新')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>配送方法マスタ登録・更新</li>
@endsection

@section('content') 

@if( isset($fileErrMsg) )
<div class="c-box--1800 c-tbl-border-all u-p--sm sy_notice u-mb--ss">
    <p class="icon_sy_notice_01">＜異常＞入力にエラーがあります。</p>
</div>
@endif
<div class="u-mt--xs">
    <form enctype="multipart/form-data" method="POST" action="" name="Form1" id="Form1">
        {{ csrf_field() }}

        {{-- 検索入力フォーム --}}
        <table class="table table-bordered c-tbl c-tbl--800">
            <tr>
                <th class="c-box--300">配送方法マスタID</th>
                <td class="td-inline">
                    {{ $records['m_delivery_types_id'] ?? "自動" }}
                </td>
                <input type="hidden" name="m_delivery_types_id" value="{{ $records['m_delivery_types_id'] ?? null }}">
                @include('common.elements.error_tag', ['name' => 'm_delivery_types_id'])
            </tr>

            <tr>
                <th class="c-box--150 must">使用区分</th>
                <td>
                @foreach ($deleteFlg as $delete_value => $delete_label)
                    <label class="radio-inline">
                      <input type="radio" name="delete_flg" value="{{ $delete_value }}" {{ old('delete_flg', $records['delete_flg'] ?? '0') == $delete_value ? 'checked' : '' }}>
                        {{ $delete_label }}
                        <input type="hidden" name="delete_flg_label" value="{{ $delete_label }}">
                    </label>
                    @endforeach
                    @include('common.elements.error_tag', ['name' => 'delete_flg'])
                </td>
            </tr>

            <tr>
                <th class="c-box--150 must">並び順</th>
                <td>
                    <input type="text" class="form-control c-box--300" name="m_delivery_sort" value="{{old('m_delivery_sort', isset($records['m_delivery_sort'])? $records['m_delivery_sort'] : '100')}}">
                    @include('common.elements.error_tag', ['name' => 'm_delivery_sort'])
                </td>
            </tr>

            <tr>
                <th class="c-box--150 must">配送方法名</th>
                <td>
                    <input type="text" class="form-control c-box--300" name="m_delivery_type_name" value="{{old('m_delivery_type_name', isset($records['m_delivery_type_name'])? $records['m_delivery_type_name'] : '')}}">
                    @include('common.elements.error_tag', ['name' => 'm_delivery_type_name'])
                </td>
            </tr>

            <tr>
                <th class="c-box--150">配送方法コード</th>
                <td>
                    <input type="text" class="form-control c-box--300" name="m_delivery_type_code" value="{{old('m_delivery_type_code', isset($records['m_delivery_type_code'])? $records['m_delivery_type_code'] : '')}}">
                    @include('common.elements.error_tag', ['name' => 'm_delivery_type_code'])
                </td>
            </tr>

            <tr>
                <th class="c-box--150 must">配送方法種類</th>
                <td id="delivery_type" >
                    @foreach ($deliveryTypes as $delivery_type_value => $delivery_type_label)
                    <label class="radio-inline">
                        <input id="deli_type" type="radio" name="delivery_type" value="{{ $delivery_type_value }}"
                        {{ old('delivery_type', isset($records['delivery_type']) ? $records['delivery_type'] : '0') == $delivery_type_value ? 'checked' : '' }} >
                        {{ $delivery_type_label }}
                    </label>
                    @endforeach
                    @include('common.elements.error_tag', ['name' => 'delivery_type'])
                </td>
            </tr>

            <tr>
                <th class="c-box--150">温度帯別手数料（常温）</th>
                <td>
                    <input type="text" class="form-control c-box--300" name="standard_fee" value="{{old('standard_fee', isset($records['standard_fee'])? $records['standard_fee'] : '')}}">
                    @include('common.elements.error_tag', ['name' => 'standard_fee'])
                </td>
            </tr>

            <tr>
                <th class="c-box--150">温度帯別手数料（冷蔵）</th>
                <td>
                    <input type="text" class="form-control c-box--300" name="chilled_fee" value="{{old('chilled_fee', isset($records['chilled_fee'])? $records['chilled_fee'] : '')}}">
                    @include('common.elements.error_tag', ['name' => 'chilled_fee'])
                </td>
            </tr>

            <tr>
                <th class="c-box--150">温度帯別手数料（冷凍）</th>
                <td>
                    <input type="text" class="form-control c-box--300" name="frozen_fee" value="{{old('frozen_fee', isset($records['frozen_fee'])? $records['frozen_fee'] : '')}}">
                    @include('common.elements.error_tag', ['name' => 'frozen_fee'])
                </td>
            </tr>

            {{-- 選択配送方法が西濃運輸の場合 --}} 
            <tr class="only_seino">
                <th class="c-box--150">荷送人コード（西濃のみ有効）</th>
                <td>
                    <input type="text" class="form-control c-box--300" name="shipper_cd" value="{{old('shipper_cd', isset($records['shipper_cd'])? $records['shipper_cd'] : '')}}">
                    @include('common.elements.error_tag', ['name' => 'shipper_cd'])
                </td>
            </tr>

            {{-- 選択配送方法がヤマト運輸の場合 --}} 
            <tr class="only_yamoto">
                <th class="c-box--150">コレクトお客様情報（ヤマトののみ有効）</th>
                <td>
                    <input type="text" class="form-control c-box--300" name="correct_info" value="{{old('correct_info', isset($records['correct_info'])? $records['correct_info'] : '')}}">
                    @include('common.elements.error_tag', ['name' => 'correct_info'])
                </td>
            </tr>

            <tr class="only_yamoto">
                <th class="c-box--150">ヤマトマスタパック（ヤマトのみ有効）</th>
                <td rowspan="2">
                    {{-- 最終取込日時 --}}
                    @if(isset($records['masterpack_import_datetime']))                    
                    <label>最終取込日時: {{ $records['masterpack_import_datetime']}}</label>
                    @endif
                    <input type="hidden" name="masterpack_import_datetime"  value="{{old('masterpack_import_datetime', isset($records['masterpack_import_datetime'])? $records['masterpack_import_datetime'] : '')}}">
                    {{-- ファイル選択 --}}
                    <div class="file-upload">
                        <label for="file" class="custom-file-label" >ファイルを選択</label>
                        <input type="file" id="file" name="file" accept=".zip" value="{{ old('file', isset($records['file']) ? $records['file'] : '') }}">
                        <span id="file-name"></span>
                        @if(isset($fileErrMsg)) <div class="error u-mt--xs">{{$fileErrMsg}}</div> @endif
                        @include('common.elements.error_tag', ['name' => 'file'])
                    </div>
                </td>
            </tr>
        </table>

        <div class="u-mt--sm">
            <button type="button" class="btn btn-default btn-lg u-mt--sm" onClick="location.href='{{ route('master.delivery_types.list') }}';">キャンセル</button>&nbsp;
            @if(isset($records['m_delivery_types_id']))
            <button class="btn btn-success btn-lg u-mt--sm" type="submit" name="submit" id="submit_update" value="edit">確認</button>
            @else
            <button class="btn btn-success btn-lg u-mt--sm" type="submit" name="submit" id="submit_register" value="new">確認</button>
            @endif
        </div>
        {{-- hidden --}}
        <input type="hidden" name="{{config('define.session_key_id')}}" value="{{$records[config('define.session_key_id')] ?? ''}}">
        <input type="hidden" name="deferred_payment_delivery_id" value="{{$records['deferred_payment_delivery_id'] ?? ''}}">
        <input type="hidden" name="m_delivery_unique_setting_seino_id" value="{{$records['m_delivery_unique_setting_seino_id'] ?? ''}}">
        <input type="hidden" name="m_delivery_unique_setting_yamato_id" value="{{$records['m_delivery_unique_setting_yamato_id'] ?? ''}}">
    </form>
    @include('common.elements.on_enter_script', ['target_button_name' => 'submit_register'])

    @push('css')
    <link rel="stylesheet" href="{{ esm_internal_asset('css/master/gfh_1207/NEMSMF0020.css') }}">
    @endpush

    @push('js')
        <script src="{{ esm_internal_asset('js/master/gfh_1207/NEMSMF0020.js') }}"></script>
    @endpush
</div>
@endsection
