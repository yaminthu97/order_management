{{-- GFOSME0010:経理処理用情報照会 --}}
{{-- 画面設定 --}}
@php
$ScreenCd='GFOSME0010';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '経理処理用情報照会')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>経理処理用情報照会</li>
@endsection

@section('content')
	<link rel="stylesheet" href="{{config('env.app_subsys_url.order')}}css/Order/v1_0/NEOSM0210.css">
	<form method="POST" action="" name="Form1" id="Form1" enctype="multipart/form-data">
		{{ csrf_field() }}
		<div>
			<table class="table table-bordered c-tbl">
				<tbody>
					<tr>
						<th class="c-box--180">進捗区分</th>
						<td class="c-box--420">
							<select class="form-control u-input--mid u-mr--xs" name='progress_classification'>
								<option value=""></option>
								@foreach( \App\Enums\ProgressTypeEnum::cases() as $progressType )
									<option value="{{ $progressType->value }}" @selected( ( $searchRow['progress_classification'] ?? '' ) == $progressType->value )>
										{{ $progressType->label() }}
									</option>
								@endforeach
							</select>
						</td>
						<th class="c-box--180">入金区分</th>
						<td class="c-box--420">
							<select class="form-control u-input--mid u-mr--xs" name='payment_classification'>
								<option value=""></option>
								@foreach( \App\Enums\PaymentTypeEnum::cases() as $paymentType )
									<option value="{{ $paymentType->value }}" @selected( ( $searchRow['payment_classification'] ?? '' ) == $paymentType->value )>
										{{ $paymentType->label() }}
									</option>
								@endforeach
							</select>
						</td>
					</tr>
					<tr>
						<th class="c-box--180">出荷予定日</th>
						<td class="c-box--420">
							<div class="u-mt--xs d-table">
								<div class="c-box--218 d-table-cell">
									<div class='input-group date date-picker'>
										<input type="text" class="form-control c-box--180" name="estimated_shipping_date_from" id="estimated_shipping_date_from" value="{{ $searchRow['estimated_shipping_date_from'] ?? '' }}">
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar"></span>
										</span>
									</div>
								</div>
								<div class="d-table-cell">&nbsp;～&nbsp;</div>
								<div class='c-box--218 d-table-cell'>
									<div class='input-group date date-picker'>
										<input type="text" class="form-control c-box--180" name="scheduled_ship_date_to" id="scheduled_ship_date_to" value="{{ $searchRow['scheduled_ship_date_to'] ?? '' }}">
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar"></span>
										</span>
									</div>
								</div>
							</div>
						</td>
						<th class="c-box--180">出荷確定日</th>
						<td class="c-box--420">
							<div class="u-mt--xs d-table">
								<div class="c-box--218 d-table-cell">
									<div class='input-group date date-picker'>
										<input type="text" class="form-control c-box--180" name="shipment_confirmation_date_from" id="shipment_confirmation_date_from" value="{{ $searchRow['shipment_confirmation_date_from'] ?? '' }}">
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar"></span>
										</span>
									</div>
								</div>
								<div class="d-table-cell">&nbsp;～&nbsp;</div>
								<div class='c-box--218 d-table-cell'>
									<div class='input-group date date-picker'>
										<input type="text" class="form-control c-box--180" name="shipment_confirmation_date_to" id="shipment_confirmation_date_to" value="{{ $searchRow['shipment_confirmation_date_to'] ?? '' }}">
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar"></span>
										</span>
									</div>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<th class="c-box--180">入金登録日</th>
						<td class="c-box--420">
							<div class="u-mt--xs d-table">
								<div class="c-box--218 d-table-cell">
									<div class='input-group date date-picker'>
										<input type="text" class="form-control c-box--180" name="payment_registration_date_from" id="payment_registration_date_from" value="{{ $searchRow['payment_registration_date_from'] ?? '' }}">
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar"></span>
										</span>
									</div>
								</div>
								<div class="d-table-cell">&nbsp;～&nbsp;</div>
								<div class='c-box--218 d-table-cell'>
									<div class='input-group date date-picker'>
										<input type="text" class="form-control c-box--180" name="payment_registration_date_to" id="payment_registration_date_to" value="{{ $searchRow['payment_registration_date_to'] ?? '' }}">
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar"></span>
										</span>
									</div>
								</div>
							</div>
						</td>
						<th class="c-box--180">顧客入金日</th>
						<td class="c-box--420">
							<div class="c-box--218 d-table-cell">
								<div class='input-group date date-picker'>
									<input type="text" class="form-control c-box--180" name="customer_payment_date_from" id="customer_payment_date_from" value="{{ $searchRow['customer_payment_date_from'] ?? '' }}">
									<span class="input-group-addon">
										<span class="glyphicon glyphicon-calendar"></span>
									</span>
								</div>
							</div>
							<div class="d-table-cell">&nbsp;～&nbsp;</div>
							<div class='c-box--218 d-table-cell'>
								<div class='input-group date date-picker'>
									<input type="text" class="form-control c-box--180" name="customer_payment_date_to" id="customer_payment_date_to" value="{{ $searchRow['customer_payment_date_to'] ?? '' }}">
									<span class="input-group-addon">
										<span class="glyphicon glyphicon-calendar"></span>
									</span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<th class="c-box--180">口座入金日</th>
						<td class="c-box--420">
							<div class="u-mt--xs d-table">
								<div class="c-box--218 d-table-cell">
									<div class='input-group date date-picker'>
										<input type="text" class="form-control c-box--180" name="account_deposit_date_from" id="account_deposit_date_from" value="{{ $searchRow['account_deposit_date_from'] ?? '' }}">
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar"></span>
										</span>
									</div>
								</div>
								<div class="d-table-cell">&nbsp;～&nbsp;</div>
								<div class='c-box--218 d-table-cell'>
									<div class='input-group date date-picker'>
										<input type="text" class="form-control c-box--180" name="account_deposit_date_to" id="account_deposit_date_to" value="{{ $searchRow['account_deposit_date_to'] ?? '' }}">
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar"></span>
										</span>
									</div>
								</div>
							</div>
						</td>
						<th class="c-box--180">入金科目</th>
						<td class="c-box--420">
							<select class="form-control u-input--mid u-mr--xs" name='deposit_account'>
								<option value=""></option>
								@foreach( $viewExtendData['payment_paytype_list'] as $mItemNameTypes )
									<option value="{{ $mItemNameTypes->m_itemname_types_id }}" @selected( ( $searchRow['deposit_account'] ?? '' ) == $mItemNameTypes->m_itemname_types_id )>
										{{ $mItemNameTypes->m_itemname_type_name }}
									</option>
								@endforeach
							</select>
						</td>
					</tr>
					<tr>
						<th class="c-box--180">支払方法</th>
						<td class="c-box--420">
							<select class="form-control u-input--mid u-mr--xs" name='payment_method'>
								<option value=""></option>
								@foreach( $viewExtendData['m_paytype_list'] as $mPayType )
									<option value="{{ $mPayType['m_payment_types_id'] }}" @selected( ( $searchRow['payment_method'] ?? '' ) == $mPayType['m_payment_types_id'] )>
										{{ $mPayType['m_payment_types_name'] }}
									</option>
								@endforeach
							</select>
						</td>
						<th class="c-box--180">ECサイト</th>
						<td class="c-box--420">
							<select class="form-control u-input--mid u-mr--xs" name='ec_site'>
								<option value=""></option>
								@foreach( $viewExtendData['m_ecs'] as $mEcsId => $mEcsName )
									<option value="{{ $mEcsId }}" @selected( ( $searchRow['ec_site'] ?? '' ) == $mEcsId )>
										{{ $mEcsName }}
									</option>
								@endforeach
							</select>
						</td>
					</tr>
					<tr>
						<th class="c-box--180">受注方法</th>
						<td class="c-box--420">
							<select class="form-control u-input--mid u-mr--xs" name='order_method'>
								<option value=""></option>
								@foreach( $viewExtendData['m_ordertypes'] as $orderTypeId => $orderTypeName )
									<option value="{{ $orderTypeId }}" @selected( ( $searchRow['order_method'] ?? '' ) == $orderTypeId )>
										{{ $orderTypeName }}
									</option>
								@endforeach
							</select>
						</td>
						<th class="c-box--180">社内メモ</th>
						<td class="c-box--420">
							<input type="text" name="internal_memo" id="internal_memo" class="form-control u-input--full" value="{{ $searchRow['internal_memo'] ?? '' }}">
						</td>
					</tr>
					<tr>
						<th class="c-box--180">出荷指示区分</th>
						<td class="c-box--420">
							<select class="form-control u-input--mid u-mr--xs" name='shipping_instruction_category'>
								<option value=""></option>
								@foreach( \App\Enums\DeliInstructTypeEnum::cases() as $deliIntructType )
									<option value="{{ $deliIntructType->value }}" @selected( ( $searchRow['shipping_instruction_category'] ?? '' ) == $deliIntructType->value )>
										{{ $deliIntructType->label() }}
									</option>
								@endforeach
							</select>
						</td>
						<th class="c-box--180">出荷確定区分</th>
						<td class="c-box--420">
							<select class="form-control u-input--mid u-mr--xs" name='shipping_confirmation_category'>
								<option value=""></option>
								@foreach( \App\Enums\DeliDecisionTypeEnum::cases() as $deliDecisionType )
									<option value="{{ $deliDecisionType->value }}" @selected( ( $searchRow['shipping_confirmation_category'] ?? '' ) == $deliDecisionType->value )>
										{{ $deliDecisionType->label() }}
									</option>
								@endforeach
							</select>
						</td>
					</tr>
					<tr>
						<th class="c-box--180">顧客ID</th>
						<td class="c-box--420">
							<input type="text" name="m_cust_id_billing" id="m_cust_id_billing" class="form-control u-input--full" value="{{ $searchRow['m_cust_id_billing'] ?? '' }}">
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<button class="btn btn-success btn-lg u-mt--sm u-mr--xs" type="submit" name="submit" value="search" id="button_search">検索</button>
		<button class="btn btn-default btn-lg u-mt--sm u-mr--xs" type="submit" name="submit" value="output" id="button_output">請求入金一覧CSV出力</button>

		<div class="c-box--1600">
			@if($paginator)
				@include('common.elements.paginator_header')
				@include('common.elements.page_list_count')
				<br>
				<table class="table table-bordered c-tbl table-link">
					<thead>
						<tr>
							<th class="c-box--70 text-center">受注ID</th>
							<th class="c-box--100 text-center">進捗区分<br>出荷指示区分<br>出荷確定区分</th>
							<th class="c-box--140 text-center">受注方法<br>支払方法</th>
							<th class="c-box--240 text-center">顧客ID<br>顧客氏名</th>
							<th class="c-box--100 text-center">出荷予定日<br>出荷確定日</th>
							<th class="c-box--110 text-center">請求金額</th>
							<th class="c-box--110 text-center">消費税額(8%)</th>
							<th class="c-box--110 text-center">消費税額(10%)</th>
							<th class="c-box--70 text-center">入金No</th>
							<th class="c-box--140 text-center">入金科目</th>
							<th class="c-box--110 text-center">入金額</th>
							<th class="c-box--100 text-center">顧客入金日</th>
							<th class="c-box--100 text-center">口座入金日</th>
							<th class="c-box--100 text-center">入金登録日</th>
						</tr>
					</thead>
					<tbody>
						@if( $paginator->count() > 0)
							@foreach($paginator as $record)
								<tr>
									<td class="text-right">
										{{ $record->t_order_hdr_id }}
									</td>
									<td>
										{{ $record->orderHdr->displayProgressType }}<br>
										{{ $record->orderHdr->displayDeliInstructType }}<br>
										{{ $record->orderHdr->displayDeliDecisionType }}
									</td>
									<td>
										{{ $record->orderHdr->orderType->m_itemname_type_name }}<br>
										{{ $record->orderHdr->paymentTypes?->m_payment_types_name }}
									</td>
									<td>
										{{ $record->orderHdr->m_cust_id_billing }}<br>
										{{ $record->orderHdr->billingCust?->name_kanji }}
									</td>
									<td class="text-right">
										@if( !empty( $record->deli_plan_date ) )
											{{ date('Y/m/d', strtotime($record->deli_plan_date) ) }}
										@endif
										<br>
										@if( !empty( $record->deli_decision_date ) )
											{{ date('Y/m/d', strtotime($record->deli_decision_date) ) }}
										@endif
									</td>
									<td class="text-right">
										{{ empty( $record->orderHdr->order_total_price ) ? '' : number_format( $record->orderHdr->order_total_price ) }}
									</td>
									<td class="text-right">
										{{ empty( $record->orderHdr->reduce_tax_price ) ? '' : number_format( $record->orderHdr->reduce_tax_price ) }}
									</td>
									<td class="text-right">
										{{ empty( $record->orderHdr->standard_tax_price ) ? '' : number_format( $record->orderHdr->standard_tax_price ) }}
									</td>
									<td class="text-right">
										{{ $record->t_payment_id }}
									</td>
									<td>
										{{ $record->itemnameType?->m_itemname_type_name }}
									</td>
									<td class="text-right">
										{{ empty( $record->payment_price ) ? '' : number_format( $record->payment_price ) }}
									</td>
									<td class="text-right">
										@if( !empty( $record->cust_payment_date ) )
											{{ date('Y/m/d', strtotime($record->cust_payment_date) ) }}
										@endif
									</td>
									<td class="text-right">
										@if( !empty( $record->account_payment_date ) )
											{{ date('Y/m/d', strtotime($record->account_payment_date) ) }}
										@endif
									</td>
									<td class="text-right">
										@if( !empty( $record->payment_entry_date ) )
											{{ date('Y/m/d', strtotime($record->payment_entry_date) ) }}
										@endif
									</td>
								</tr>
							@endforeach
						@else
							<tr>
								<td colspan="14">
									該当する経理処理用情報が見つかりません。
								</td>
							</tr>
						@endif
					</tbody>
				</table>
				@include('common.elements.paginator_footer')
			@endif
		</div>
	</form>
	@push('css')
		<link rel="stylesheet" href="{{ esm_internal_asset('css/order/base/app.css') }}">
	@endpush
	@include('common.elements.datetime_picker_script')
@endsection