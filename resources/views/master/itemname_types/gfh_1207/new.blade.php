{{-- NEMSMA0020:項目名称マスタ登録・更新 --}}
@php
    $ScreenCd='NEMSMA0020';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '項目名称マスタ登録・更新')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>項目名称マスタ登録・更新</li>
@endsection

@section('content') 

<div class="u-mt--xs">
    <form enctype="multipart/form-data" method="POST" action="" name="Form1" id="Form1">
        {{ csrf_field() }}

        {{-- 検索入力フォーム --}}
        <table class="table table-bordered c-tbl c-tbl--800">
            <tr>
                <th class="c-box--300 must">項目名称マスタID</th>
                <td class="td-padding">
                    {{ $records['m_itemname_types_id'] ?? "自動" }}
                </td>
                <input type="hidden" name="m_itemname_types_id" value="{{ old('m_itemname_types_id', $records->m_itemname_types_id) ?? 0 }}">
                @include('common.elements.error_tag', ['name' => 'm_itemname_types_id'])
            </tr>

            <tr>
                <th class="c-box--150 must">使用区分</th>
                <td>
                    @foreach ($deleteFlg as $delete_value => $delete_label)
                    <label class="label-margin">
                        <input type="radio" name="delete_flg" value="{{ $delete_value }}" @checked(old('delete_flg', $records->delete_flg) == $delete_value)>
                        {{ $delete_label }}
                        <input type="hidden" name="delete_flg_label" value="{{ $delete_label }}">

                    </label>
                    @endforeach
                    @include('common.elements.error_tag', ['name' => 'delete_flg'])
                </td>
            </tr>

            <tr>
                <th class="c-box--150 must">項目種類</th>
                <td>
                    @foreach ($itemnameTypes as $itemname_type_value => $itemname_type_label)
                    <label class="label-margin">
                        <input type="radio" name="m_itemname_type" value="{{ $itemname_type_value }}" @checked(old('m_itemname_type', $records->m_itemname_type) == $itemname_type_value)>
                        {{ $itemname_type_label }}
                    </label>
                    @endforeach
                    @include('common.elements.error_tag', ['name' => 'm_itemname_type'])
                </td>
            </tr>

            <tr>
                <th class="c-box--150 must">項目名</th>
                <td>
                    <input type="text" class="form-control c-box--300 label-margin" name="m_itemname_type_name" value="{{ old('m_itemname_type_name', $records->m_itemname_type_name) }}">
                    @include('common.elements.error_tag', ['name' => 'm_itemname_type_name'])
                </td>
            </tr>

            <tr>
                <th class="c-box--150">項目コード</th>
                <td>
                    <input type="text" class="form-control c-box--300 label-margin" name="m_itemname_type_code" value="{{ old('m_itemname_type_code', $records->m_itemname_type_code) }}">
                    @include('common.elements.error_tag', ['name' => 'm_itemname_type_code'])
                </td>
            </tr>

           <tr>
                <th class="c-box--150 must">並び順</th>
                <td>
                    <input type="text" class="form-control c-box--300 label-margin" name="m_itemname_type_sort" value="{{ old('m_itemname_type_sort', $records->m_itemname_type_sort)?? '100'}}" >
                    @include('common.elements.error_tag', ['name' => 'm_itemname_type_sort'])
                </td>
            </tr>

        </table>

        <div class="u-mt--sm">
            <button type="button" class="btn btn-default btn-lg u-mt--sm" onClick="location.href='{{ route('master.itemname_types.list') }}';">キャンセル</button>&nbsp;
            <button class="btn btn-success btn-lg u-mt--sm" type="submit" name="submit" id="submit_register" value="new">確認</button>
        </div>
        {{-- hidden --}}
        <input type="hidden" name="{{config('define.session_key_id')}}" value="{{$records[config('define.session_key_id')] ?? ''}}">
    </form>
    @include('common.elements.on_enter_script', ['target_button_name' => 'submit_register'])
    @push('css')
    <link rel="stylesheet" href="{{ esm_internal_asset('css/master/gfh_1207/NEMSMA0020.css') }}">
    @endpush
</div>
@endsection
