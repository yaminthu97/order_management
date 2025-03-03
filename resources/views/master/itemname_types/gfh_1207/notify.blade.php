{{-- NEMSMF0030:項目名称マスタ確認 --}}
@php
    $ScreenCd='NEMSMF0030';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '項目名称マスタ確認')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>項目名称マスタ確認</li>
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
                <th class="c-box--300">項目名称マスタID</th>
               	<td>{{ $records->m_itemname_types_id ?? "自動" }}</td>
                <input type="hidden" name="m_itemname_types_id" value="{{ $records->m_itemname_types_id ?? null }}">
            </tr>

            <tr>
                <th class="c-box--150 must">使用区分</th>
                <td>{{ $deleteFlg[old('delete_flg')] ? $deleteFlg[old('delete_flg')] : $deleteFlg[$records->delete_flg] }}</td>
                <input type="hidden" name="delete_flg" value="{{ $records->delete_flg ?? "" }}">
            </tr>

            <tr>
                <th class="c-box--150 must">項目種類</th>
                <td>{{ $itemnameTypes[old('m_itemname_type') ?? $itemnameTypes[$records->m_itemname_type]] }}</td>
                <input type="hidden" name="m_itemname_type" value="{{ $records->m_itemname_type ?? "" }}">
            </tr>

            <tr>
                <th class="c-box--150 must">項目名</th>
                <td  style="word-wrap: break-word; word-break: break-all;">{{ old('m_itemname_type_name', $records->m_itemname_type_name ?? "") }}</td>
                <input type="hidden" name="m_itemname_type_name" value="{{ $records->m_itemname_type_name ?? "" }}">
            </tr>

            <tr>
                <th class="c-box--150">項目コード</th>
                <td style="word-wrap: break-word; word-break: break-all;">{{ old('m_itemname_type_code', $records->m_itemname_type_code ?? "") }}</td>
                <input type="hidden" name="m_itemname_type_code" value="{{ $records->m_itemname_type_code ?? "" }}">
            </tr>

            <tr>
                <th class="c-box--150 must">並び順</th>
                <td>{{ old('m_itemname_type_sort', $records->m_itemname_type_sort ?? "") }}</td>
                <input type="hidden" name="m_itemname_type_sort" value="{{ $records->m_itemname_type_sort ?? "" }}">
            </tr>
        </table>

        <div class="u-mt--sm">
            <button type="submit" name="submit" value="cancel" class="btn btn-default btn-lg u-mt--sm">キャンセル</button>     
            <button type="submit" name="submit" value="register" class="btn btn-success btn-lg u-mt--sm">{{(isset($records->m_itemname_types_id)) ? '更新' : '登録'}}</button>
        </div>
        {{-- hidden --}}
        <input type="hidden" name="{{config('define.session_key_id')}}" value="{{$records[config('define.session_key_id')] ?? ''}}">
    </form>
</div>
@endsection
