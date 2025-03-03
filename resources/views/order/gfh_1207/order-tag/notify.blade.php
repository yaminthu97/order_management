{{-- NEOSM0243:受注タグマスタ登録・修正確認 --}}
{{-- 画面設定 --}}
@php
$ScreenCd='NEOSM0243';
@endphp

{{-- layout設定; --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '受注タグマスタ登録・修正確認')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>受注タグマスタ登録・修正確認</li>
@endsection

@section('content')
<form method="POST" name="Form1" action="">
@csrf
@if($mode == 'edit')
    @method('PUT')
@endif
	<div>	
		<table class="table table-bordered c-tbl c-tbl--full">
			<tr>
				<th class="must c-box--200">受注タグ名称</th>
				<td colspan="2">{{ old('tag_name', $records['tag_name'] ?? '') }}
					<input type="hidden" id="tag_name" name="tag_name" value="{{ old('tag_name', $records['tag_name'] ?? '') }}" />
					@include('common.elements.error_tag', ['name' => 'tag_name'])
				</td>
			</tr>
			<tr>
				<th class="must">表示用名称</th>
				<td colspan="2">{{ old('tag_display_name', $records['tag_display_name'] ?? '') }}
					<input type="hidden" id="tag_display_name" name="tag_display_name" value="{{old('tag_display_name', $records['tag_display_name'] ?? '')}}" />
					@include('common.elements.error_tag', ['name' => 'tag_display_name'])
				</td>
			</tr>
			<tr>
				<th class="must">表示順</th>
				<td colspan="2">{{  old('m_order_tag_sort', $records['m_order_tag_sort'] ?? '')}}
					<input type="hidden" id="m_order_tag_sort" name="m_order_tag_sort" value="{{ old('m_order_tag_sort', $records['m_order_tag_sort'] ?? '')}}" />
					@include('common.elements.error_tag', ['name' => 'm_order_tag_sort'])
				</td>
			</tr>
			<tr>
				<th class="must">自動付与タイミング</th>
				<td colspan="2">{{ $auto_timming[old('auto_timming', $records['auto_timming'] ?? '')] }}
                    <input type="hidden" id="auto_timming" name="auto_timming" value="{{ old('auto_timming', $records['auto_timming'] ?? '')}}" />
					@include('common.elements.error_tag', ['name' => 'auto_timming'])
				</td>
			</tr>
			<tr>
				<th class="must">背景色</th>
				<td colspan="2" style="background: linear-gradient(to right, #{{old('tag_color', $records['tag_color'] ?? '') ?? \App\Modules\Order\Gfh1207\Enums\FontColorEnum::WHITE->value}} 95%, transparent 5%);">
					<input type="hidden" id="tag_color" name="tag_color" value="{{ $records['tag_color'] ?? ''}}" />
					@include('common.elements.error_tag', ['name' => 'tag_color'])
				</td>
			</tr>
			<tr>
				<th class="must">文字色</th>
				<td colspan="2">{{ $font_color[old('font_color', $records['font_color'] ?? '')]}}
					<input type="hidden" id="font_color" name="font_color" value="{{old('font_color', $records['font_color'] ?? '')}}" />
					@include('common.elements.error_tag', ['name' => 'font_color'])
				</td>
			</tr>
			<tr>
				<th>進捗停止区分</th>
				<td colspan="2">{{ $progress_type[old('deli_stop_flg', $records['deli_stop_flg'] ?? '')]}}
					<input type="hidden" id="deli_stop_flg" name="deli_stop_flg" value="{{old('deli_stop_flg', $records['deli_stop_flg'] ?? '')}}" />
					@include('common.elements.error_tag', ['name' => 'deli_stop_flg'])
				</td>
			</tr>
			<tr>
				<th>説明</th>
				<td colspan="2">{{old('tag_context', $records['tag_context'] ?? '')}}
					<input type="hidden" id="tag_context" name="tag_context" value="{{old('tag_context', $records['tag_context'] ?? '')}}" />
					@include('common.elements.error_tag', ['name' => 'tag_context'])
				</td>
			</tr>
			<tr>
				<th class="must">各条件の結合</th>
				<td colspan="2">{{ $and_or[old('and_or', $records['and_or'] ?? '')]}}
					<input type="hidden" id="and_or" name="and_or" value="{{old('and_or', $records['and_or'] ?? '')}}" />
					@include('common.elements.error_tag', ['name' => 'and_or'])
				</td>
			</tr>
			@for($i = 1; $i <= 10; $i++)
			<tr>
				<th colspan="3">自動付与条件{{mb_convert_kana($i, 'N')}}</th>
			</tr>
			<tr>
				<th>元データ</th>
				<td colspan="2">{{ $table_id[old("cond{$i}_table_id", $records["cond{$i}_table_id"] ?? '')] ?? '' }}
					<input type="hidden" name="{{"cond{$i}_table_id"}}" value='{{old("cond{$i}_table_id", $records["cond{$i}_table_id"] ?? '')}}' />
					@include('common.elements.error_tag', ['name' => "cond{$i}_table_id"])
				</td>
			</tr>
			<tr>
				<th>項目名</th>
				<td>{{ $column_id[old("cond{$i}_column_id", $records["cond{$i}_column_id"] ?? '')] ?? '' }}
					<input type="hidden" name="{{"cond{$i}_column_id"}}" value='{{old("cond{$i}_column_id", $records["cond{$i}_column_id"] ?? '')}}' />
					@include('common.elements.error_tag', ['name' => "cond{$i}_column_id"])
				</td>
				<td>
					@if (isset($records["cond{$i}_length_flg"]) && $records["cond{$i}_length_flg"] == '1') 項目の文字数を条件とする @endif
				</td>
			</tr>		
			<tr>
				<th>演算子</th>
				<td>{{ old("cond{$i}_operator", $operator[$records["cond{$i}_operator"] ?? ''] ?? '')}}
					<input type="hidden" name="{{"cond{$i}_operator"}}" value='{{old("cond{$i}_operator", $records["cond{$i}_operator"] ?? '')}}' />
					@include('common.elements.error_tag', ['name' => "cond{$i}_column_id"])
				</td>
			</tr>
			<tr>
				<th>値</th>
				<td colspan="2">{{old("cond{$i}_value", $records["cond{$i}_value"] ?? '')}}
					<input type="hidden" id="{{"cond{$i}_value"}}" name="{{"cond{$i}_value"}}" value="{{old("cond{$i}_value", $records["cond{$i}_value"] ?? '')}}" />
					@include('common.elements.error_tag', ['name' => "cond{$i}_value"])
				</td>
			</tr>
			@endfor
			
		</table>
		<input type="hidden" name="m_order_tag_id" id="m_order_tag_id" value="{{$records['m_order_tag_id'] ?? ''}}" />
		<input type="hidden" name="{{config('define.session_key_id')}}" value="{{$param}}">
		<button type="submit" name="submit" value="cancel" class="btn btn-default btn-lg u-mt--sm">キャンセル</button>     
        <button type="submit" name="submit" value="register" class="btn btn-success btn-lg u-mt--sm">{{isset($records->m_order_tag_id) ? '更新' : '登録'}}</button>
	</div>
@include('common.elements.on_enter_script', ['target_button_name' => 'submit_register'])
</form>
@endsection