{{-- GFISMA0020:付属品マスタ登録・修正 --}}
@php
$ScreenCd='GFISMA0020';
@endphp
{{-- layout設定 --}}
@extends('common.layouts.default')
{{-- タイトル設定 --}}
@section('title', '付属品マスタ登録・修正')
{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>付属品マスタ登録・修正</li>
@endsection
@section('content')
<form method="POST" action="{{ route('attachment_item.postNotify') }}" name="Form1" id="Form1">
{{ csrf_field() }}
<div>
    <table class="table table-bordered c-tbl c-tbl--800">
        <tr>
            <th class="">付属品マスタID
            </th>
            <td>
                <span>{{ !empty($editRow['m_ami_attachment_item_id']) ? $editRow['m_ami_attachment_item_id'] : '自動' }}</span>
                <input type="hidden" name="m_ami_attachment_item_id" value = "{{ old('m_ami_attachment_item_id', $editRow['m_ami_attachment_item_id'] ?? '')}}">
            </td>
        </tr>

        <tr>
            <th class="c-box--300 must">カテゴリ</th>
            <td>
                <select class="form-control" id="category_id" name="category_id">
                    <option value="" @if( old('category_id', $editRow['category_id'] ?? '' ) == 0 )selected @endif></option>
                    @if( isset( $viewExtendData['attachment_item_category_list'] ) )
                        @foreach( $viewExtendData['attachment_item_category_list'] as $group )
                            <option value="{{ $group['m_itemname_types_id'] }}" @if( old('category_id', $editRow['category_id'] ?? 0 ) == $group['m_itemname_types_id'] )selected @endif>
                                {{ $group['m_itemname_type_name'] }}
                            </option>
                        @endforeach
                    @endif
                </select>
                @include('common.elements.error_tag', ['name' => 'category_id'])
                </td>
        </tr>

        <tr>
            <th class="c-box--300 must">付属品コード</th>
            <td>
                <input class="form-control" type="text" name="attachment_item_cd" value="{{ old('attachment_item_cd', $editRow['attachment_item_cd'] ?? '')}}">
	            @include('common.elements.error_tag', ['name' => 'attachment_item_cd'])
            </td>
        </tr>
        <tr>
            <th class="c-box--300 must">付属品名称</th>
            <td>
                <input class="form-control" type="text" name="attachment_item_name" value="{{ old('attachment_item_name', $editRow['attachment_item_name'] ?? '')}}">
	            @include('common.elements.error_tag', ['name' => 'attachment_item_name'])
            </td>
        </tr>

        <tr>
            <th class="c-box--300 must">使用区分</th>
            <td>
                @foreach (\App\Enums\DeleteFlg::cases() as $target)
                <input class="form-check-input" type="radio" name="delete_flg" id="delete_flg_{{$target->value}}" value="{{$target->value}}" @if(old('delete_flg',$editRow['delete_flg'] ?? \App\Enums\DeleteFlg::Use->value)==$target->value) checked @endif>
                <label class="form-check-label" for="delete_flg_{{$target->value}}">{{$target->label()}}</label>
                @endforeach
	            @include('common.elements.error_tag', ['name' => 'delete_flg'])
            </td>
        </tr>
        <tr>
            <th class="c-box--300 must">受注画面表示</th>
            <td>
                @foreach (\App\Enums\DisplayFlg::cases() as $target)
                <input class="form-check-input" type="radio" name="display_flg" id="display_flg_{{$target->value}}" value="{{$target->value}}" @if(old('display_flg',$editRow['display_flg'] ?? \App\Enums\DisplayFlg::Use->value)==$target->value) checked @endif>
                <label class="form-check-label" for="display_flg_{{$target->value}}">{{$target->label()}}</label>
                @endforeach
	            @include('common.elements.error_tag', ['name' => 'display_flg'])
            </td>
        </tr>

        <tr>
            <th class="c-box--300 must">請求書記載</th>
            <td>
                @foreach (\App\Enums\InvoiceFlg::cases() as $target)
                <input class="form-check-input" type="radio" name="invoice_flg" id="invoice_flg_{{$target->value}}" value="{{$target->value}}" @if(old('invoice_flg',$editRow['invoice_flg'] ?? \App\Enums\InvoiceFlg::Use->value)==$target->value) checked @endif>
                <label class="form-check-label" for="invoice_flg_{{$target->value}}">{{$target->label()}}</label>
                @endforeach
	            @include('common.elements.error_tag', ['name' => 'invoice_flg'])
            </td>
        </tr>
        <tr>
            <th class="c-box--300">自由項目1</th>
            <td>
                <textarea class="form-control c-box--400" rows="5" name="reserve1" id="reserve1" >{{old('reserve1',$editRow['reserve1'] ?? '')}}</textarea>
	            @include('common.elements.error_tag', ['name' => 'reserve1'])
            </td>
        </tr>
        <tr>
            <th class="c-box--300">自由項目2</th>
            <td>
                <textarea class="form-control c-box--400" rows="5" name="reserve2" id="reserve2" >{{old('reserve2',$editRow['reserve2'] ?? '')}}</textarea>
	            @include('common.elements.error_tag', ['name' => 'reserve2'])
            </td>
        </tr>
        <tr>
            <th class="c-box--300">自由項目3</th>
            <td>
                <textarea class="form-control c-box--400" rows="5" name="reserve3" id="reserve3" >{{old('reserve3',$editRow['reserve3'] ?? '')}}</textarea>
	            @include('common.elements.error_tag', ['name' => 'reserve3'])
            </td>
        </tr>

    </table>
</div>
<div class="u-mt--ss">
    <input class="btn btn-default btn-lg u-mt--sm" type="button" name="cancel" value="キャンセル" onClick="location.href='{{route('attachment_item.list')}}';" />
	&nbsp;&nbsp;
    <input type="hidden" name="add" value="0" />
    <button class="btn btn-success btn-lg u-mt--sm" type="submit" name="submit" id="submit_notify" value="notify">確認</button>
	@include('common.elements.on_enter_script', ['target_button_name' => 'submit_register'])
</div>
</form>
@include('common.elements.datetime_picker_script')

@endsection