{{-- GFOSMJ0010:見積書・納品書・請求書出力 --}}
{{-- 画面設定 --}}
@php
$ScreenCd='GFOSMJ0010';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '見積書・納品書・請求書出力')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>見積書・納品書・請求書出力</li>
@endsection

@section('content')
	@if( !empty($errors) && count($errors) > 0 )
		<div class="c-box--1800 c-tbl-border-all u-p--sm sy_notice u-mb--ss">
			<p class="icon_sy_notice_01">＜異常＞入力にエラーがあります。</p>
		</div>
	@endif
	<link rel="stylesheet" href="{{config('env.app_subsys_url.order')}}css/Order/v1_0/NEOSM0210.css">
	<form method="POST" action="" name="Form1" id="Form1" enctype="multipart/form-data">
		{{ csrf_field() }}
		<div class="c-box--1600">
			<p class="c-ttl--02">検索条件</p>
			<table class="table table-bordered c-tbl">
				<tbody>
                    <tr>
						<th class="c-box--180">受注ID</th>
						<td class="c-box--420">
							<input type="text" name="t_order_hdr_id" id="t_order_hdr_id" class="form-control u-input--full" value="{{ $searchRow['t_order_hdr_id'] ?? '' }}">
						</td>
						<th class="c-box--180">受注日</th>
						<td class="c-box--420">
							<div class="u-mt--xs d-table">
								<div class="c-box--218 d-table-cell">
									<div class='input-group date date-picker'>
										<input type="text" class="form-control c-box--180" name="order_datetime_from" id="order_datetime_from" value="{{ $searchRow['order_datetime_from'] ?? '' }}">
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar"></span>
										</span>
									</div>
								</div>
								<div class="d-table-cell">&nbsp;～&nbsp;</div>
								<div class='c-box--218 d-table-cell'>
									<div class='input-group date date-picker'>
										<input type="text" class="form-control c-box--180" name="order_datetime_to" id="order_datetime_to" value="{{ $searchRow['order_datetime_to'] ?? '' }}">
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar"></span>
										</span>
									</div>
								</div>
							</div>
						</td>
					</tr>
                    <tr>
						<th class="c-box--180">注文主ID</th>
						<td class="c-box--420">
							<input type="text" name="m_cust_id" id="m_cust_id" class="form-control u-input--full" value="{{ $searchRow['m_cust_id'] ?? '' }}">
						</td>
						<th class="c-box--180">請求先ID</th>
						<td class="c-box--420">
							<input type="text" name="m_cust_id_billing" id="m_cust_id_billing" class="form-control u-input--full" value="{{ $searchRow['m_cust_id_billing'] ?? '' }}">
						</td>
					</tr>
				</tbody>
			</table>
			<button class="btn btn-success btn-lg u-mt--sm u-mr--xs" type="submit" name="submit" value="search" id="button_search">検索</button>
		</div>

		@if($paginator)
			<div class="u-mt--sl c-box--1600">
				<p class="c-ttl--02">受注一覧</p>
				@include('common.elements.paginator_header')
				@include('common.elements.page_list_count')
				<br>
				@include('common.elements.error_tag', ['name' => 't_order_destination_id'])
				<table class="table table-bordered c-tbl table-link">
					<thead>
						<tr>
							<th class="c-box--120 text-center">受注ID</th>
							<th class="c-box--80 text-center">枝番</th>
							<th class="c-box--180 text-center">受注日</th>
							<th class="c-box--120 text-center">注文主ID</th>
							<th class="c-box--200 text-center">注文主</th>
							<th class="c-box--200 text-center">送付先</th>
							<th class="c-box--120 text-center">出荷予定日</th>
							<th class="c-box--120 text-center">配送希望日</th>
							<th class="c-box--100 text-center">進捗区分</th>
							<th class="c-box--200 text-center">支払方法</th>
						</tr>
					</thead>
					<tbody>
						@if( $paginator->count() > 0)
							@foreach($paginator as $record)
								<tr>
									<td>
										<label style="width:100%">
											<input 
												type="radio" name="t_order_destination_id" value="{{ $record->t_order_destination_id }}"
												@checked( ( $searchRow['t_order_destination_id'] ?? '' ) == $record->t_order_destination_id ) 
											>
											<div class="pull-right">
												{{ $record->t_order_hdr_id }}
											</div>
										</label>
									</td>
									<td class="text-right">
										{{ $record->order_destination_seq }}
									</td>
									<td>
										@if( !empty( $record->orderHdr->order_datetime ) )
											{{ date('Y/m/d', strtotime($record->orderHdr->order_datetime) ) }}
										@endif
									</td>
									<td class="text-right">
										{{ $record->orderHdr->m_cust_id }}
									</td>
									<td>
										{{ $record->orderHdr->cust?->name_kanji }}
									</td>
									<td>
										{{ $record->destination_name }}
									</td>
									<td>
										@if( !empty( $record->deli_plan_date ) )
											{{ date('Y/m/d', strtotime($record->deli_plan_date) ) }}
										@endif
									</td>
									<td>
										@if( !empty( $record->deli_hope_date ) )
											{{ date('Y/m/d', strtotime($record->deli_hope_date) ) }}
										@endif
									</td>
									<td>
										{{ \App\Enums\ProgressTypeEnum::tryfrom( $record->orderHdr->progress_type )?->label() }}
									</td>
									<td>
										{{ $record->orderHdr->paymentTypes?->m_payment_types_name }}
									</td>
								</tr>
							@endforeach
						@else
							<tr>
								<td colspan="10">
									該当する受注情報が見つかりません。
								</td>
							</tr>
						@endif
					</tbody>
				</table>
				@include('common.elements.paginator_footer')
			</div>

			@if( $paginator->count() > 0)
				<div class="u-mt--sl c-box--1600">
					<p class="c-ttl--02">帳票出力</p>
					<table class="table c-tbl c-tbl--1200">
						<tbody>
							<tr>
								<th class="c-box--200">出力単位</th>
								<td>
									@foreach(\App\Enums\ExcelReportOutputUnitEnum::cases() as $outputUnit)
										<div class="radio-inline">
											<label>
												<input 
													type="radio" name="output_unit" value="{{ $outputUnit->value }}"
													@checked( ( $searchRow['output_unit'] ?? '' ) == $outputUnit->value )
												>
												{{ $outputUnit->label() }}
											</label>
										</div>
									@endforeach
									@include('common.elements.error_tag', ['name' => 'output_unit'])
								</td>
							</tr>
							<tr>
								<th class="c-box--200">テンプレート</th>
								<td>
									<select class="form-control u-input--long u-mr--xs" id="m_report_template_id" name="m_report_template_id">
										@foreach ( $templateList as $template )
											<option 
												value="{{ $template->m_report_template_id }}"
												@selected( ( $searchRow['m_report_template_id'] ?? '' ) == $template->m_report_template_id )
											>
												{{ $template->report_name }}
											</option>
										@endforeach
									</select>
									@include('common.elements.error_tag', ['name' => 'm_report_template_id'])
								</td>
							</tr>
						</tbody>
					</table>
					<button class="btn btn-success btn-lg u-mt--sm u-mr--xs" type="submit" name="submit" value="output" id="button_output">出力</button>
				</div>
			@endif
		@endif
	</form>
	@push('css')
		<link rel="stylesheet" href="{{ esm_internal_asset('css/order/base/app.css') }}">
	@endpush
	@include('common.elements.datetime_picker_script')
@endsection