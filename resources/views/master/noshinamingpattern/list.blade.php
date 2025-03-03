{{-- GFMSMF0010:熨斗名入れパターンマスタ検索 --}}
@php
$ScreenCd='GFMSMF0010';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '熨斗名入れパターンマスタ検索')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>熨斗名入れパターンマスタ検索</li>
@endsection

@section('content')
<form method="POST" action="" name="Form1" id="Form1">
{{ csrf_field() }}
<div>
    <table class="table table-bordered c-tbl c-tbl--500">
        <tr>
            <th class="c-box--200">使用区分</th>
            <td>
            @foreach (\App\Enums\DeleteFlg::cases() as $target)
                <label class="checkbox-inline">
                    <input type="checkbox" name="delete_flg[]" value="{{ $target->value }}"
                     @if(isset($searchRow['delete_flg']) && in_array($target->value, $searchRow['delete_flg'])) checked @endif
                    >{{ $target->label() }}
                </label>
            @endforeach
            </td>
        </tr>
        <tr>
            <th class="c-box--200">名入れパターン名</th>
            <td>
                <input class="form-control" type="text" name="pattern_name" value="{{$searchRow['pattern_name'] ?? ''}}" maxlength="256">
            </td>
        </tr>
        <tr>
            <th>名入れパターンコード</th>
            <td>
                <input class="form-control" type="text" name="pattern_code" value="{{$searchRow['pattern_code'] ?? ''}}" maxlength="100">
            </td>
        </tr>
    </table>
    <input class="btn btn-success btn-lg" type="submit" name="submit_search" value="検索">
     &nbsp; <input type="button" class="btn btn-default btn-lg" name="new" value="新規登録" onClick="location.href='./new'">
    <input type="hidden" name="{{config('define.session_key_id')}}" value="{{$searchRow[config('define.session_key_id')] ?? ''}}">
</div>

<br>
@if($paginator)
<div>
@include('common.elements.paginator_header')
@include('common.elements.page_list_count')
<br>
<table class="table table-bordered c-tbl table-link">
	<tr>
		<th class="c-box--80">使用区分</th>
		<th class="c-box--60">ID</th>
		<th class="c-box--300">パターン名</th>
		<th class="c-box--300">パターンコード</th>
		<th class="c-box--100">並び順</th>
		<th class="c-box--60">会社名</th>
		<th class="c-box--60">部署名</th>
		<th class="c-box--60">肩書</th>
		<th class="c-box--60">苗字</th>
		<th class="c-box--60">名前</th>
		<th class="c-box--60">ルビ</th>
    </tr>
	@if(!empty($paginator->count()) > 0)
    @foreach($paginator as $elm)
    <tr>
        <td>{{\App\Enums\DeleteFlg::from($elm['delete_flg'])->label()}}</td>
        <td class="u-right"><a href="{{route('noshi.namingpattern.edit',$elm['m_noshi_naming_pattern_id'])}}">{{$elm['m_noshi_naming_pattern_id']}}</a></td>
        <td>{{$elm['pattern_name']}}</td>
        <td>{{$elm['pattern_code']}}</td>
        <td class="u-right">{{$elm['m_noshi_naming_pattern_sort']}}</td>
        <td class="u-right">{{$elm['company_name_count']}}</td>
        <td class="u-right">{{$elm['section_name_count']}}</td>
        <td class="u-right">{{$elm['title_count']}}</td>
        <td class="u-right">{{$elm['f_name_count']}}</td>
        <td class="u-right">{{$elm['name_count']}}</td>
        <td class="u-right">{{$elm['ruby_count']}}</td>
    </tr>
    @endforeach
    @else
		<tr>
			<td colspan="11">該当熨斗名入れパターンが見つかりません。</td>
		</tr>
	@endif
</table>
@include('common.elements.paginator_footer')
</div>
@endif
</form>
@endsection
