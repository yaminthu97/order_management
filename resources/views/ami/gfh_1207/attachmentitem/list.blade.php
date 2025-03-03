{{-- GFISMA0010:付属品マスタ --}}
{{-- 画面設定 --}}
@php
$ScreenCd='GFISMA0010';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '付属品マスタ')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>付属品マスタ検索</li>
@endsection

@section('content')
<form method="POST" action="" name="Form1" id="Form1">
	{{ csrf_field() }}
	<div class="u-mt--xs">
		<table class="table table-bordered c-tbl c-tbl--1200">

			<tr>
				<th class="c-box--140">カテゴリ</th>
				<td class="c-box--500z">
					<select class="form-control c-box--200" id="category_id" name="category_id">
						<option value=""></option>
						@foreach( $viewExtendData['attachment_item_category_list'] as $category )
							<option value="{{ $category['m_itemname_types_id'] }}" 
								{{ old('category_id', $searchRow['category_id'] ?? '') == $category['m_itemname_types_id'] ? 'selected' : '' }}>
								{{ $category['m_itemname_type_name'] }}
							</option>
						@endforeach
					</select>
				</td>
			</tr>
			<tr>
				<th class="c-box--140">付属品コード</th>
				<td class="c-box--500z">
					<input class="form-control" type="text" name="attachment_item_cd" value="{{ old('attachment_item_cd', $searchRow['attachment_item_cd'] ?? '') }}" maxlength="20"/>
				</td>
			</tr>
			<tr>
				<th class="c-box--140">付属品名称</th>
				<td class="c-box--500z">
					<input class="form-control" type="text" name="attachment_item_name" value="{{ old('attachment_item_name', $searchRow['attachment_item_name'] ?? '') }} " maxlength="100"/>
				</td>
			</tr>
			<tr>
            	<th class="c-box--200">使用区分</th>
				<td>
				@foreach (\App\Enums\DeleteFlg::cases() as $target)
					<label class="checkbox-inline">
						<input type="checkbox" name="delete_flg[]" value="{{ $target->value }}"
						@if(isset($searchRow['delete_flg']) && is_array($searchRow['delete_flg']) && in_array($target->value, $searchRow['delete_flg'])) checked @endif
						>{{ $target->label() }}
					</label>
				@endforeach
            	</td>
			<tr>
            	<th class="c-box--200">受注時表示</th>
				<td>
				@foreach (\App\Enums\DisplayFlg::cases() as $target)
					<label class="checkbox-inline">
						<input type="checkbox" name="display_flg[]" value="{{ $target->value }}"
						@if(isset($searchRow['display_flg']) && is_array($searchRow['display_flg']) && in_array($target->value, $searchRow['display_flg'])) checked @endif
						>{{ $target->label() }}
					</label>
				@endforeach
            </td>
			<tr>
            	<th class="c-box--200">請求書記載</th>
				<td>
				@foreach (\App\Enums\InvoiceFlg::cases() as $target)
					<label class="checkbox-inline">
						<input type="checkbox" name="invoice_flg[]" value="{{ $target->value }}"
						@if(isset($searchRow['invoice_flg']) && is_array($searchRow['invoice_flg']) && in_array($target->value, $searchRow['invoice_flg'])) checked @endif
						>{{ $target->label() }}
					</label>
				@endforeach
            	</td>
        	</tr>
			<tr>
				<th>自由項目1</th>
				<td>
					<input class="form-control c-box--400" type="text" id="reserve1" name="reserve1" value="{{ old('reserve1', $searchRow['reserve1'] ?? '') }}">
				</td>
			</tr>
			<tr>
				<th>自由項目2</th>
				<td>
					<input class="form-control c-box--400" type="text" id="reserve2" name="reserve2" value="{{ old('reserve2', $searchRow['reserve2'] ?? '') }}">
				</td>
			</tr>
			<tr>
				<th>自由項目3</th>
				<td>
					<input class="form-control c-box--400" type="text" id="reserve3" name="reserve3" value="{{ old('reserve3', $searchRow['reserve3'] ?? '') }}">
				</td>
			</tr>
		</table>
		<div class="u-mt--sm">
			<button class="btn btn-success btn-lg u-mt--sm" type="submit" name="submit_name" id="submit_search" value="search">検索</button>
     		&nbsp; 
			<input type="button" class="btn btn-default btn-lg u-mt--sm" name="new" value="新規登録" onClick="location.href='./new'">
    		<input type="hidden" name="{{config('define.session_key_id')}}" value="{{$searchRow[config('define.session_key_id')] ?? ''}}">
        </div>
	</div>

	<br>
	@if($paginator)
	<div>
	@include('common.elements.paginator_header')
	@include('common.elements.page_list_count')
	@include('common.elements.datetime_picker_script')
	<br>
		<table class="table table-bordered c-tbl link-style" name="searchResults">
			<tr>

				<th class='c-box--20'>ID</th>
				<th class='m-box--350'>カテゴリ</th>
				<th class='m-box--350'>付属品コード</th>
				<th class='c-box--40'>付属品名称</th>
				<th class='c-box--30'>使用区分</th>
				<th class='c-box--40'>受注画面</th>
				<th class='c-box--40'>請求書記載</th>
			</tr>

			@if(!empty($paginator->count()) > 0)
				@foreach($paginator as $elm)
				<tr>
					<td class="u-right">
						<a href="{{route('attachment_item.edit',$elm['m_ami_attachment_item_id'])}}">{{$elm['m_ami_attachment_item_id']}}</a>
					</td>
					<td class='m-box--350'>{{ $elm->category ? $elm->category->m_itemname_type_name : '' }} </td>
					<td class='m-box--350'>{{$elm['attachment_item_cd']}}</td>
					<td class='m-box--350'>{{$elm['attachment_item_name']}}</td>
					<td>{{ \App\Enums\DeleteFlg::tryfrom( $elm['delete_flg'] ) ? \App\Enums\DeleteFlg::tryfrom( $elm['delete_flg'] )->label() : '' }}</td>

					<td>{{ \App\Enums\DisplayFlg::tryfrom( $elm['display_flg'] ) ? \App\Enums\DisplayFlg::tryfrom( $elm['display_flg'] )->label() : '' }}</td>
					<td>{{ \App\Enums\InvoiceFlg::tryfrom( $elm['invoice_flg'] ) ? \App\Enums\InvoiceFlg::tryfrom( $elm['invoice_flg'] )->label() : '' }}</td>
				</tr>
				@endforeach

			@else
				<tr>
					<td colspan="10">該当付属品が見つかりません。</td>
				</tr>
			@endif

		</table>
		@include('common.elements.paginator_footer')
		<!-- ページネーションのコード -->
	</div>
	@endif
</form>
@push('css')
<link rel="stylesheet" href="{{ esm_internal_asset('css/ami/gfh_1207/GFISMA0010.css') }}">
@endpush
@endsection
