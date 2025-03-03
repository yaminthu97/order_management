{{-- GFMSMB0010:キャンペーン --}}
{{-- 画面設定 --}}
@php
$ScreenCd='GFMSMB0010';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', 'キャンペーン')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>キャンペーン検索</li>
@endsection

@section('content')
<form method="POST" action="" name="Form1" id="Form1">
	{{ csrf_field() }}
	<div class="u-mt--xs">
		<table class="table table-bordered c-tbl c-tbl--1200">
			<tr>
				</td>
				<th class="c-box--140">キャンペーン名</th>
				<td class="c-box--500z">
					<input class="form-control" type="text" name="campaign_name" value="{{ old('campaign_name', $searchRow['campaign_name'] ?? '') }}" maxlength="256"/>
				</td>

				<th class="c-box--140">商品コード</th>
				<td class="c-box--500z">
					<input class="form-control" type="text" name="giving_page_cd" value="{{ old('giving_page_cd', $searchRow['giving_page_cd'] ?? '') }}" maxlength="50"/>
				</td>
			</tr>
			<tr>
				<th class="c-box--140">キャンペーン期間</th>
				<td>
					<div class="u-mt--xs d-table">
						<div class="c-box--218 d-table-cell">
							<div class="input-group date date-picker">
								<input type="text" class="form-control c-box--180" id="from_date" name="from_date" value="{{ old('from_date', $searchRow['from_date'] ?? '') }}">
								<span class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</span>
							</div>
						</div>
						<div class="d-table-cell">&nbsp;～&nbsp;</div>
						<div class="c-box--218 d-table-cell">
							<div class="input-group date date-picker">
								<input type="text" class="form-control c-box--180" id="to_date" name="to_date" value="{{ old('to_date', $searchRow['to_date'] ?? '') }}">
								<span class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</span>
							</div>
						</div>
					</div>
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
				<th class='m-box--350'>キャンペーン名称</th>
				<th class='c-box--160'>キャンペーン期間</th>
				<th class='c-box--30'>使用区分</th>
				<th class='c-box--40'>金額</th>
				<th class='c-box--40'>金額ごとに追加</th>
				<th class='c-box--40'>商品コード</th>
			</tr>

			@if(!empty($paginator->count()) > 0)
				@foreach($paginator as $elm)
				<tr>
					<td class="u-right"><a href="{{route('campaign.edit',$elm['m_campaign_id'])}}">{{$elm['m_campaign_id']}}</a></td>
					<td class='m-box--350'>{{$elm['campaign_name']}}</td>
					<td>
						{{ $elm['from_date'] . ' ~ ' . $elm['to_date'] }}
					</td>
					<td>{{ \App\Enums\DeleteFlg::tryfrom( $elm['delete_flg'] ) ? \App\Enums\DeleteFlg::tryfrom( $elm['delete_flg'] )->label() : '' }}</td>

					<td class="u-right">{{$elm['giving_condition_amount']}}</td>

					<td>{{ \App\Enums\GivingConditionEvery::tryfrom( $elm['giving_condition_every'] ) ? \App\Enums\GivingConditionEvery::tryfrom( $elm['giving_condition_every'] )->label() : '' }}</td>

					<td class="m-box--350">{{$elm['giving_page_cd']}}</td>
				</tr>
				@endforeach

			@else
				<tr>
					<td colspan="10">該当キャンペーンが見つかりません。</td>
				</tr>
			@endif

		</table>
		@include('common.elements.paginator_footer')
		<!-- ページネーションのコード -->
	</div>
	@endif
</form>

@push('css')
<link rel="stylesheet" href="{{ esm_internal_asset('css/master/gfh_1207/GFMSMB0010.css') }}">
@endpush
@endsection
