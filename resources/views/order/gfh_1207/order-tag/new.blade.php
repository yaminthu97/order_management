{{-- NEOSM0242:受注タグマスタ登録・修正; --}}
{{-- 画面設定 --}}
@php
$ScreenCd='NEOSM0242';
@endphp

{{-- layout設定; --}}
@extends('common.layouts.default')

{{-- タイトル設定; --}}
@section('title', '受注タグマスタ登録・修正')

{{-- ぱんくず設定; --}}
@section('breadcrumb')
<li>受注タグマスタ登録・修正</li>
@endsection

@section('content')
<form method="POST" name="Form1" action="">
{{ csrf_field() }}
	<div>
		<table class="table table-bordered c-tbl c-tbl--full">
			<tr>
				<th class="must c-box--200">受注タグ名称</ht>
				<td colspan="2">
					<input class="form-control c-box--300" type="text" id="tag_name" name="tag_name" value="{{old('tag_name', $records['tag_name'] ?? '') }}" />
					@include('common.elements.error_tag', ['name' => 'tag_name'])
				</td>
			</tr>
			<tr>
				<th class="must">表示用名称</ht>
				<td colspan="2">
					<input class="form-control c-box--300" type="text" id="tag_display_name" name="tag_display_name" value="{{old('tag_display_name', $records['tag_display_name'] ?? '') }}" />
					@include('common.elements.error_tag', ['name' => 'tag_display_name'])
				</td>
			</tr>
			<tr>
				<th class="must">表示順</ht>
				<td colspan="2">
					<input class="form-control c-box--300" type="text" id="m_order_tag_sort" name="m_order_tag_sort" value="{{old('m_order_tag_sort', $records['m_order_tag_sort'] ?? '')}}" />
					@include('common.elements.error_tag', ['name' => 'm_order_tag_sort'])
				</td>
			</tr>
			<tr>
				<th class="must">自動付与タイミング</ht>
				<td colspan="2">
                    @foreach($auto_timming as $tableIdValue1 => $tableIdName1)
                        <div class="radio-inline"><label><input type="radio" name="auto_timming" value="{{$tableIdValue1}}" @checked(old('auto_timming', $records['auto_timming'] ?? \App\Modules\Order\Gfh1207\Enums\AutoTimmingEnum::NONE->value) == $tableIdValue1)> {{$tableIdName1}}</label></div>
                    @endforeach
					@include('common.elements.error_tag', ['name' => 'auto_timming'])
				</td>
			</tr>
			<tr>
				<th class="must">背景色</ht>
				<td colspan="2">
					<input class="form-control c-box--300" type="color" id="tag_color" name="tag_color" value="#{{str_replace('#', '', old('tag_color', $records['tag_color'] ?? \App\Modules\Order\Gfh1207\Enums\FontColorEnum::WHITE->value)); }}" />
					@include('common.elements.error_tag', ['name' => 'tag_color'])
				</td>
			</tr>
			<tr>
				<th class="must">文字色</ht>
				<td colspan="2">
                    @foreach($font_color as $tableIdValue1 => $tableIdName1 )
                    <div class="radio-inline"><label><input type="radio" name="font_color" value="{{$tableIdValue1}}"  @checked($tableIdValue1 == \App\Modules\Order\Gfh1207\Enums\FontColorEnum::BLACK->value)> {{$tableIdName1}}</label></div>
                    @endforeach
					@include('common.elements.error_tag', ['name' => 'font_color'])
				</td>
			</tr>
			<tr>
				<th>進捗停止区分</ht>
				<td colspan="2">
					<div>
					<select class="form-control c-box--300" name="deli_stop_flg">
						@foreach($progress_type as $keyId => $keyValue)
						<option value="{{$keyId}}" @selected(old('deli_stop_flg', $records['deli_stop_flg'] ?? '') == strval($keyId))>{{$keyValue}}</option>
						@endforeach
					</select>
					進捗区分を止めるタグは<u>下線付き</u>で表示されます。
					</div>
					@include('common.elements.error_tag', ['name' => 'deli_stop_flg'])
				</td>
			</tr>
			<tr>
				<th>説明</ht>
				<td colspan="2">
					<textarea class="form-control c-box--full" style="resize:none;" rows="5" id="tag_context" name="tag_context" >{{ old('tag_context', $records['tag_context'] ?? '' )}}</textarea>
					@include('common.elements.error_tag', ['name' => 'tag_context'])
				</td>
			</tr>
			<tr>
				<th class="must">各条件の結合</ht>
				<td colspan="2">
                   @foreach($and_or as $tableIdValue1 => $tableIdName1) 
                        <div class="radio-inline"><label><input type="radio" name="and_or" value="{{$tableIdValue1}}" @checked(old('and_or', $records['and_or'] ?? \App\Modules\Order\Gfh1207\Enums\AndOrEnum::AND->value ) == $tableIdValue1)> {{$tableIdName1}}</label></div>
                    @endforeach
					@include('common.elements.error_tag', ['name' => 'and_or'])
				</td>
			</tr>
			@for($i = $order_tag_condition_start; $i <= $order_tag_condition_end; $i++)
			<tr>
				<th colspan="3">自動付与条件{{mb_convert_kana($i, 'N')}}</ht>
			</tr>
			<tr>
				<th>元データ</ht>
				<td colspan="2">
					<select name="{{"cond{$i}_table_id"}}" class="form-control c-box--300 tableID" >
						<option value=""></option>
						@foreach($table_id as $tableIdName => $tableIdValue) 
						<option value="{{$tableIdValue}}" @selected(old("cond{$i}_table_id", $records["cond{$i}_table_id"]) == $tableIdValue) >{{$tableIdName}}</option>
						@endforeach
					</select>
					@include('common.elements.error_tag', ['name' => "cond{$i}_table_id"])
				</td>
			</tr>
			<tr>
				<th>項目名</ht>
				<td>
					<input type="hidden" id="{{"db_cond{$i}_column_id"}}" value="{{old("cond{$i}_column_id", $records["cond{$i}_column_id"] ?? '')}}">
					<select name="{{"cond{$i}_column_id"}}" class="form-control c-box--300 {{"cond{$i}_column_id"}}" >
						<option value=""></option>
						@foreach($column_id as $columnIdName => $columnIdValue)
						<option value="{{$columnIdValue}}" @selected(old("cond{$i}_column_id", $records["cond{$i}_column_id"]) == $columnIdValue) >{{$columnIdName}}</option>
						@endforeach
					</select>
					@include('common.elements.error_tag', ['name' => "cond{$i}_column_id"])
				</td>
				<td>
					<label class="checkbox-inline"><input type="checkbox" name="{{"cond{$i}_length_flg"}}" value="{{$records["cond{$i}_length_flg"] ?? '1'}}" @checked(old("cond{$i}_length_flg", $records["cond{$i}_length_flg"] ?? '') == '1')> 項目の文字数を条件とする</label>
					@include('common.elements.error_tag', ['name' => "cond{$i}_length_flg"])
				</td>
			</tr>
			<tr>
				<th>演算子</ht>
					
				<td colspan="2">
					<select name="{{"cond{$i}_operator"}}" class="form-control c-box--300" id="operators">
						<option value=""></option>
						@foreach($operator as $operatorValue => $operatorName )
						<option value="{{$operatorValue}}" @selected(old("cond{$i}_operator", $records["cond{$i}_operator"] ?? '') == $operatorValue)>{{$operatorName}}</option>
						@endforeach
					</select>
					@include('common.elements.error_tag', ['name' => "cond{$i}_operator"])
				</td>
			</tr>
			<tr>
				<th>値</ht>
				<td colspan="2">
					<input class="form-control c-box--300" type="text" id="{{"cond{$i}_value"}}" name="{{"cond{$i}_value"}}" value="{{old("cond{$i}_value", $records["cond{$i}_value"]?? '')}}" />
					@include('common.elements.error_tag', ['name' => "cond{$i}_value"])
				</td>
			</tr>
			@endfor
			
		</table>
		<input type="hidden" name="m_order_tag_id" id="m_order_tag_id" value="{{$records['m_order_tag_id']?? ''}}" />
		<input type="hidden" name="{{config('define.session_key_id')}}" value="{{$records[config('define.session_key_id')]?? ''}}">
		<button type="button" class="btn btn-default btn-lg u-mt--sm" onClick="location.href='{{ route("order.order-tag.list") }}';">キャンセル</button>&nbsp;
        <button class="btn btn-success btn-lg u-mt--sm" type="submit" name="submit" id="submit_register" value="new">確認</button>
        <input type="hidden" name="inOperator" id="inOperator" value="{{\App\Modules\Order\Gfh1207\Enums\OperatorEnum::IN->value}}">

	</div>
	@include('common.elements.on_enter_script', ['target_button_name' => 'submit_register'])
	@push('js')
        <script src="{{ esm_internal_asset('js/order/gfh_1207/NEOSM0242.js') }}"></script>
    @endpush
</form>
@endsection