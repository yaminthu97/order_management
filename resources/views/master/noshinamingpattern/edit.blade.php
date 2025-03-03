{{-- GFMSME0020:熨斗名入れパターン登録・修正 --}}
@php
$ScreenCd='GFMSME0020';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '熨斗名入れパターン登録・修正')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>熨斗名入れパターン登録・修正</li>
@endsection

@section('content')
<form  method="POST" action="" name="Form1" id="Form1">
{{ csrf_field() }}
<div>
    <table class="table table-bordered c-tbl c-tbl--800">
        <tr>
            <th class="c-box--200">名入れパターンID</th>
            <td>
                <input type="hidden" name="m_noshi_naming_pattern_id" value="{{ old('m_noshi_naming_pattern_id',$editRow['m_noshi_naming_pattern_id'] ?? '') }}">
                {{ $editRow['m_noshi_naming_pattern_id'] ?? '' }}
	            @include('common.elements.error_tag', ['name' => 'm_noshi_naming_pattern_id'])
            </td>
        </tr>
        <tr>
            <th class="c-box--200 must">使用区分</th>
            <td>
                @foreach (\App\Enums\DeleteFlg::cases() as $target)
                <input class="form-check-input" type="radio" name="delete_flg" id="radio{{$target->value}}" value="{{$target->value}}" @if(old('delete_flg',$editRow['delete_flg'] ?? \App\Enums\DeleteFlg::Use->value)==$target->value) checked @endif>
                <label class="form-check-label" for="radio{{$target->value}}">{{$target->label()}}</label>
                @endforeach
	            @include('common.elements.error_tag', ['name' => 'delete_flg'])
            </td>
        </tr>
        <tr>
            <th class="must">名入れパターン名</th>
            <td>
                <input class="form-control c-box--full" type="text" name="pattern_name" value="{{old('pattern_name',$editRow['pattern_name'] ?? '')}}">
	            @include('common.elements.error_tag', ['name' => 'pattern_name'])
            </td>
        </tr>
        <tr>
            <th>名入れパターンコード</th>
            <td>
                <input class="form-control c-box--full" type="text" name="pattern_code" value="{{old('pattern_code',$editRow['pattern_code'] ?? '')}}">
	            @include('common.elements.error_tag', ['name' => 'pattern_code'])
            </td>
        </tr>
        <tr>
            <th>並び順</th>
            <td>
                <input class="form-control" type="text" name="m_noshi_naming_pattern_sort" value="{{old('m_noshi_naming_pattern_sort',$editRow['m_noshi_naming_pattern_sort'] ?? '')}}">
	            @include('common.elements.error_tag', ['name' => 'm_noshi_naming_pattern_sort'])
            </td>
        </tr>
        <tr>
            <th>会社名</th>
            <td>
            <select name="company_name_count" id="company_name_count" class="form-control c-box--60">
            @for($idx=0;$idx<=5;$idx++)
                <option value="{{$idx}}" @if(old('company_name_count',$editRow['company_name_count']??0) == $idx) {{'selected'}} @endif >{{$idx}}</option>
            @endfor
            </select>
            @include('common.elements.error_tag', ['name' => 'company_name_count'])
            </td>
        </tr>
        <tr>
            <th>部署名</th>
            <td>
            <select name="section_name_count" id="section_name_count" class="form-control c-box--60">
            @for($idx=0;$idx<=5;$idx++)
                <option value="{{$idx}}" @if(old('section_name_count',$editRow['section_name_count']??0) == $idx) {{'selected'}} @endif >{{$idx}}</option>
            @endfor
            </select>
            @include('common.elements.error_tag', ['name' => 'section_name_count'])
            </td>
        </tr>
        <tr>
            <th>肩書
            </th>
            <td>
            <select name="title_count" id="title_count" class="form-control c-box--60">
            @for($idx=0;$idx<=5;$idx++)
                <option value="{{$idx}}" @if(old('title_count',$editRow['title_count']??0) == $idx) {{'selected'}} @endif >{{$idx}}</option>
            @endfor
            </select>
            @include('common.elements.error_tag', ['name' => 'title_count'])
            </td>
        </tr>
        <tr>
            <th>苗字</th>
            <td>
            <select name="f_name_count" id="f_name_count" class="form-control c-box--60">
            @for($idx=0;$idx<=5;$idx++)
                <option value="{{$idx}}" @if(old('f_name_count',$editRow['f_name_count']??0) == $idx) {{'selected'}} @endif >{{$idx}}</option>
            @endfor
            </select>
            @include('common.elements.error_tag', ['name' => 'f_name_count'])
            </td>
        </tr>
        <tr>
            <th>名前</th>
            <td>
            <select name="name_count" id="name_count" class="form-control c-box--60">
            @for($idx=0;$idx<=5;$idx++)
                <option value="{{$idx}}" @if(old('name_count',$editRow['name_count']??0) == $idx) {{'selected'}} @endif >{{$idx}}</option>
            @endfor
            </select>
            @include('common.elements.error_tag', ['name' => 'name_count'])
            </td>
        </tr>
        <tr>
            <th>ルビ</th>
            <td>
            <select name="ruby_count" id="ruby_count" class="form-control c-box--60">
            @for($idx=0;$idx<=5;$idx++)
                <option value="{{$idx}}" @if(old('ruby_count',$editRow['ruby_count']??0) == $idx) {{'selected'}} @endif >{{$idx}}</option>
            @endfor
            </select>
            @include('common.elements.error_tag', ['name' => 'ruby_count'])
            </td>
        </tr>
    </table>
</div>
<div class="u-mt--ss">
    <input class="btn btn-default btn-lg u-mt--sm" type="button" name="cancel" value="キャンセル" onClick="location.href='{{route('noshi.namingpattern.list')}}';" />
	&nbsp;&nbsp;
	<input type="submit" name="submit_register" id="submit_register" class="btn btn-success btn-lg u-mt--sm" value="登録"> 
	@include('common.elements.on_enter_script', ['target_button_name' => 'submit_register'])
</div>
</form>
@endsection
