{{-- GFISMA0030:付属品マスタ確認登録・修正 --}}
@php
$ScreenCd='GFISMA0030';
@endphp
{{-- layout設定 --}}
@extends('common.layouts.default')
{{-- タイトル設定 --}}
@section('title', '付属品マスタ確認登録・修正')
{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>付属品マスタ確認登録・修正</li>
@endsection
@section('content')
<form method="POST" action="{{ route('attachment_item.update') }}" name="Form1" id="Form1">
{{ csrf_field() }}
<div>
    <table class="table table-bordered c-tbl c-tbl--800">
        <tr>
            <th class="">付属品マスタID</th>
            <td >
                <span>
                    {{ $editRow['m_ami_attachment_item_id'] }}
                </span>
            </td>
        </tr>

        <tr>
            <th class="must">カテゴリ</th>
            <td class="m-box--350">
                <span>
                    @foreach($viewExtendData['attachment_item_category_list'] as $category)
                    @if($editRow['category_id'] == $category['m_itemname_types_id'])
                        {{ $category['m_itemname_type_name'] }}
                    @endif
                    @endforeach
                </span>
                @include('common.elements.error_tag', ['name' => 'category_id'])
            </td>
        </tr>

        <tr>
            <th class="must">付属品コード</th>
            <td class="m-box--350">
                <span>{{ $editRow['attachment_item_cd'] }}</span>
                @include('common.elements.error_tag', ['name' => 'attachment_item_cd'])
            </td>
        </tr>
        <tr>
            <th class="must">付属品名称</th>
            <td class="m-box--350">
                <span>{{ $editRow['attachment_item_name'] }}</span>
                @include('common.elements.error_tag', ['name' => 'attachment_item_name'])
            </td>
        </tr>

        <tr>
            <th class="must">使用区分</th>
            <td>
                @if(isset($editRow['delete_flg']) && $editRow['delete_flg']=='0') {{'使用中'}} @else {{'使用停止'}} @endif
                @include('common.elements.error_tag', ['name' => 'delete_flg'])
            </td>
        </tr>

        <tr>
            <th class="must">受注画面表示</th>
            <td>
                @if(isset($editRow['display_flg']) && $editRow['display_flg']=='1') {{'表示'}} @else {{'非表示'}} @endif
                @include('common.elements.error_tag', ['name' => 'display_flg'])
            </td>
        </tr>

        <tr>
            <th class="must">請求書記載</th>
            <td>
                @if(isset($editRow['invoice_flg']) && $editRow['invoice_flg']=='1') {{'記載する'}} @else {{'記載しない'}} @endif
                @include('common.elements.error_tag', ['name' => 'invoice_flg'])
            </td>
        </tr>
        <tr>
            <th >自由項目1</th>
            <td class="m-box--350">
                <span>{!! nl2br($editRow['reserve1']) !!}</span>
                @include('common.elements.error_tag', ['name' => 'reserve1'])
            </td>
        </tr>
        <tr>
            <th >自由項目2</th>
            <td class="m-box--350">
                <span>{!! nl2br($editRow['reserve2']) !!}</span>
                @include('common.elements.error_tag', ['name' => 'reserve2'])
            </td>
        </tr>
        <tr>
            <th >自由項目3</th>
            <td class="m-box--350">
                <span>{!! nl2br($editRow['reserve3']) !!}</span>
                @include('common.elements.error_tag', ['name' => 'reserve3'])
            </td>
        </tr>


    </table>
</div>
<div class="u-mt--ss">
    <input type="hidden" name="cancel" value="0"> <!-- デフォルトの値 -->
    <button class="btn btn-default btn-lg u-mt--sm" type="submit" name="submit" id="submit_cancel" value="cancel">キャンセル</button>
    &nbsp;&nbsp;

    <input type="hidden" name="add" value="1"/>  
    <button class="btn btn-success btn-lg u-mt--sm" type="submit" name="submit" id="submit_register" value="register">登録</button>

    @include('common.elements.on_enter_script', ['target_button_name' => 'submit_notify'])
</div>
</form>
@push('css')
<link rel="stylesheet" href="{{ esm_internal_asset('css/ami/gfh_1207/GFISMA0030.css') }}">
@endpush

@endsection