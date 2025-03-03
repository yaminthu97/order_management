{{-- NEOSM0210:受注検索 --}}
{{-- 画面設定 --}}
@php
$ScreenCd='NEOSM0210';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '受注検索')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>受注検索</li>
@endsection

@section('content')

@if( !empty($viewMessage) )
	<div class="c-box--1600 c-tbl-border-all u-p--sm sy_notice u-mb--ss">
		@foreach($viewMessage as $message)
			<p class="icon_sy_notice_03">{{$message}}</p>
		@endforeach
	</div><!--/sy_notice-->
@endif

<link rel="stylesheet" href="{{config('env.app_subsys_url.order')}}css/Order/v1_0/NEOSM0210.css">
<form method="POST" action="" name="Form1" id="Form1" enctype="multipart/form-data">
{{ csrf_field() }}
<div>
	<div class="c-box--1600" style='text-align: right;'>
		<a href='{{config('env.app_subsys_url.cc')}}cc-customer/list' style='color: #337ab7;'>
			<i class="fas fa-arrows-alt-h"></i>&nbsp;
			<u style='text-decoration: underline;'>顧客受付へ</u>
		</a>
	</div>
	<div class="c-box--1600 u-mt--xs">
		<div id="line-01"></div>
		<p class="c-ttl--02">検索条件</p>
		<table class="table c-tbl c-tbl--1600">
			<tr>
				<th class="c-box--180">検索条件</th>
				<td>
					<select class="form-control" id="m_order_list_cond_id" name="m_order_list_cond_id">
						@foreach($viewExtendData['order_cond_list'] as $orderCond)
							<option value="{{$orderCond['m_order_list_cond_id']}}"  @if( isset($searchRow['m_order_list_cond_id']) && $searchRow['m_order_list_cond_id'] == $orderCond['m_order_list_cond_id'] ) selected @endif>{{ $orderCond['order_list_cond_name'] }}</option>
						@endforeach
					</select>
					@include('common.elements.error_tag', ['name' => 'order_list_cond_error'])
				</td>
				<td>
					<button class="btn btn-default" type="submit" name="submit" value="read_order_list_cond">読み出し</button>&nbsp;&nbsp;
					<button class="btn btn-default" type="submit" name="submit" value="modify_order_list_cond">変更</button>&nbsp;&nbsp;
					<button class="btn btn-default" type="submit" name="submit" value="delete_order_list_cond">削除</button>
				</td>
				<td>
					<input class="form-control u-input--full" type="text" placeholder="検索条件名を入力してください" id="order_list_cond_name" name="order_list_cond_name" value="{{ isset($searchRow['order_list_cond_name']) ? $searchRow['order_list_cond_name'] : '' }}">
				</td>
				<td>
					<label class="checkbox-inline"><input type="checkbox" name="public_flg" id="public_flg" value="1" {{ isset($viewExtendData['public_flg'][1]) ? $viewExtendData['public_flg'][1] : ''}}>公開</label>&nbsp;&nbsp;
					<button class="btn btn-default" type="submit" name="submit" value="add_order_list_cond">追加</button>
				</td>
			</tr>
		</table>
	</div>

	<div>
		<table class="table c-tbl">
			<tr>
				<th class="c-box--180">進捗区分検索</th>
				<td>
					<label class="checkbox-inline"><input type="checkbox" name="progress_type_auto_self[]" id="progress_type_auto_self[]" value="0" @if( isset($searchRow['progress_type_auto_self']) && in_array(0, $searchRow['progress_type_auto_self']) ) checked @endif>自動</label>
					<label class="checkbox-inline"><input type="checkbox" name="progress_type_auto_self[]" id="progress_type_auto_self[]" value="1" @if( isset($searchRow['progress_type_auto_self']) && in_array(1, $searchRow['progress_type_auto_self']) ) checked @endif>手動</label>
				</td>
			</tr>
		</table>
		<table class="table c-tbl c-tbl--1600 c-tbl-border-left c-tbl-border-bottom">
			<tr>
				<td class="c-tbl-border-right c-states--02 u-center c-box--150">
					<button class="btn btn-default c-box--100 c-button-height--57 button_search_progress" type="submit" name="submit" value="{{ \App\Enums\ProgressTypeEnum::PendingConfirmation->value }}">
						{{ isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingConfirmation->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingConfirmation->value] : '確認待' }}<br>
						<span class="badge">
							{{isset($viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::PendingConfirmation->value]['total']) ? $viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::PendingConfirmation->value]['total'] : 0}}
						</span>
					</button>
					<div class="checkbox th-checkbox">
						<label class="u-pl--25" for="">
							<input type="checkbox" id="progress_type[]" name="progress_type[]" value="{{ \App\Enums\ProgressTypeEnum::PendingConfirmation->value }}" @if( isset( $searchRow['progress_type'] ) && in_array( \App\Enums\ProgressTypeEnum::PendingConfirmation->value, $searchRow['progress_type'] ) ) checked @endif>
						</label>
					</div>
				</td>
				<td class="c-tbl-border-right c-states--02 u-center c-box--150">
					<button class="btn btn-default c-box--100 c-button-height--57 button_search_progress" type="submit" name="submit" value="{{ \App\Enums\ProgressTypeEnum::PendingCredit->value }}">
						{{isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingCredit->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingCredit->value] : '与信待'}}<br>
						<span class="badge">
							{{isset($viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::PendingCredit->value]['total']) ? $viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::PendingCredit->value]['total'] : '0'}}
						</span>
					</button>
					<div class="checkbox th-checkbox">
						<label class="u-pl--25" for="">
							<input type="checkbox" id="progress_type[]" name="progress_type[]" value="{{ \App\Enums\ProgressTypeEnum::PendingCredit->value }}" @if( isset( $searchRow['progress_type'] ) && in_array( \App\Enums\ProgressTypeEnum::PendingCredit->value, $searchRow['progress_type'] ) ) checked @endif>
						</label>
					</div>
				</td>
				<td class="c-tbl-border-right c-states--02 u-center c-box--150">
					<button class="btn btn-default c-box--100 c-button-height--57 button_search_progress" type="submit" name="submit" value="{{ \App\Enums\ProgressTypeEnum::PendingPrepayment->value }}">
						{{isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingPrepayment->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingPrepayment->value] : '前払入金待'}}<br>
						<span class="badge">
							{{isset($viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::PendingPrepayment->value]['total']) ? $viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::PendingPrepayment->value]['total'] : '0'}}
						</span>
					</button>
					<div class="checkbox th-checkbox">
						<label class="u-pl--25" for="">
							<input type="checkbox" id="progress_type[]" name="progress_type[]" value="{{ \App\Enums\ProgressTypeEnum::PendingPrepayment->value }}" @if( isset( $searchRow['progress_type'] ) && in_array( \App\Enums\ProgressTypeEnum::PendingPrepayment->value, $searchRow['progress_type'] ) ) checked @endif>
						</label>
					</div>
				</td>
				<td class="c-tbl-border-right c-states--02 u-center c-box--150">
					<button class="btn btn-default c-box--100 c-button-height--57 button_search_progress" type="submit" name="submit" value="{{ \App\Enums\ProgressTypeEnum::PendingAllocation->value }}">
						{{isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingAllocation->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingAllocation->value] : '引当待'}}<br>
						<span class="badge">
							{{isset($viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::PendingAllocation->value]['total']) ? $viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::PendingAllocation->value]['total'] : '0'}}
						</span>
					</button>
					<div class="checkbox th-checkbox">
						<label class="u-pl--25" for="">
							<input type="checkbox" id="progress_type[]" name="progress_type[]" value="{{ \App\Enums\ProgressTypeEnum::PendingAllocation->value }}" @if( isset( $searchRow['progress_type'] ) && in_array( \App\Enums\ProgressTypeEnum::PendingAllocation->value, $searchRow['progress_type'] ) ) checked @endif>
						</label>
					</div>
				</td>
				<td class="c-tbl-border-right c-states--03 u-center c-box--150">
					<button class="btn btn-default c-box--100 c-button-height--57 button_search_progress" type="submit" name="submit" value="{{ \App\Enums\ProgressTypeEnum::PendingShipment->value }}">
						{{isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingShipment->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingShipment->value] : '出荷待'}}<br>
						<span class="badge">
							{{isset($viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::PendingShipment->value]['total']) ? $viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::PendingShipment->value]['total'] : '0'}}
						</span>
					</button>
					<div class="checkbox th-checkbox">
						<label class="u-pl--25" for="">
							<input type="checkbox" id="progress_type[]" name="progress_type[]" value="{{ \App\Enums\ProgressTypeEnum::PendingShipment->value }}" @if( isset( $searchRow['progress_type'] ) && in_array( \App\Enums\ProgressTypeEnum::PendingShipment->value, $searchRow['progress_type'] ) ) checked @endif>
						</label>
					</div>
				</td>
				<td class="c-tbl-border-right c-states--04 u-center c-box--150">
					<button class="btn btn-default c-box--100 c-button-height--57 button_search_progress" type="submit" name="submit" value="{{ \App\Enums\ProgressTypeEnum::Shipping->value }}">
						{{isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::Shipping->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::Shipping->value] : '出荷中'}}<br>
						<span class="badge">
							{{isset($viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::Shipping->value]['total']) ? $viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::Shipping->value]['total'] : '0'}}
						</span>
					</button>
					<div class="checkbox th-checkbox">
						<label class="u-pl--25" for="">
							<input type="checkbox" id="progress_type[]" name="progress_type[]" value="{{ \App\Enums\ProgressTypeEnum::Shipping->value }}" @if( isset( $searchRow['progress_type'] ) && in_array( \App\Enums\ProgressTypeEnum::Shipping->value, $searchRow['progress_type'] ) ) checked @endif>
						</label>
					</div>
				</td>
				<td class="c-tbl-border-right c-states--05 u-center c-box--150">
					<button class="btn btn-default c-box--100 c-button-height--57 button_search_progress" type="submit" name="submit" value="{{ \App\Enums\ProgressTypeEnum::Shipped->value }}">
						{{isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::Shipped->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::Shipped->value] : '出荷済'}}<br>
						<span class="badge">
							{{isset($viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::Shipped->value]['total']) ? $viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::Shipped->value]['total'] : '0'}}
						</span>
					</button>
					<div class="checkbox th-checkbox">
						<label class="u-pl--25" for="">
							<input type="checkbox" id="progress_type[]" name="progress_type[]" value="{{ \App\Enums\ProgressTypeEnum::Shipped->value }}" @if( isset( $searchRow['progress_type'] ) && in_array( \App\Enums\ProgressTypeEnum::Shipped->value, $searchRow['progress_type'] ) ) checked @endif>
						</label>
					</div>
				</td>
				<td class="c-tbl-border-right c-states--06 u-center c-box--150">
					<button class="btn btn-default c-box--100 c-button-height--57 button_search_progress" type="submit" name="submit" value="{{ \App\Enums\ProgressTypeEnum::PendingPostPayment->value }}">
						{{isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingPostPayment->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingPostPayment->value] : '後払入金待'}}<br>
						<span class="badge">
							{{isset($viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::PendingPostPayment->value]['total']) ? $viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::PendingPostPayment->value]['total'] : '0'}}
						</span>
					</button>
					<div class="checkbox th-checkbox">
						<label class="u-pl--25" for="">
							<input type="checkbox" id="progress_type[]" name="progress_type[]" value="{{ \App\Enums\ProgressTypeEnum::PendingPostPayment->value }}" @if( isset( $searchRow['progress_type'] ) && in_array( \App\Enums\ProgressTypeEnum::PendingPostPayment->value, $searchRow['progress_type'] ) ) checked @endif>
						</label>
					</div>
				</td>
				<td class="c-tbl-border-right c-states--07 u-center c-box--150">
					<button class="btn btn-default c-box--100 c-button-height--57 button_search_progress" type="submit" name="submit" value="{{ \App\Enums\ProgressTypeEnum::Completed->value }}">
						{{isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::Completed->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::Completed->value] : '完了'}}<br>
						<span class="badge"></span>
					</button>
					<div class="checkbox th-checkbox">
						<label class="u-pl--25" for="">
							<input type="checkbox" id="progress_type[]" name="progress_type[]" value="{{ \App\Enums\ProgressTypeEnum::Completed->value }}" @if( isset( $searchRow['progress_type'] ) && in_array( \App\Enums\ProgressTypeEnum::Completed->value, $searchRow['progress_type'] ) ) checked @endif>
						</label>
					</div>
				</td>
				<td class="c-tbl-border-right c-states--08 u-center c-box--150">
					<button class="btn btn-default c-box--100 c-button-height--57 button_search_progress" type="submit" name="submit" value="{{ \App\Enums\ProgressTypeEnum::Cancelled->value }}">
						{{isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::Cancelled->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::Cancelled->value] : 'キャンセル'}}
					</button>
					<div class="checkbox th-checkbox">
						<label class="u-pl--25" for="">
							<input type="checkbox" id="progress_type[]" name="progress_type[]" value="{{ \App\Enums\ProgressTypeEnum::Cancelled->value }}" @if( isset( $searchRow['progress_type'] ) && in_array( \App\Enums\ProgressTypeEnum::Cancelled->value, $searchRow['progress_type'] ) ) checked @endif>
						</label>
					</div>
				</td>
				<td class="c-tbl-border-right c-states--09 u-center c-box--150">
					<button class="btn btn-default c-box--100 c-button-height--57 button_search_progress" type="submit" name="submit" value="{{ \App\Enums\ProgressTypeEnum::Returned->value }}">
						{{isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::Returned->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::Returned->value] : '返品'}}
					</button>
					<div class="checkbox th-checkbox">
						<label class="u-pl--25" for="">
							<input type="checkbox" id="progress_type[]" name="progress_type[]" value="{{ \App\Enums\ProgressTypeEnum::Returned->value }}" @if( isset( $searchRow['progress_type'] ) && in_array( \App\Enums\ProgressTypeEnum::Returned->value, $searchRow['progress_type'] ) ) checked @endif>
						</label>
					</div>
				</td>
				<td class="c-tbl-border-right c-states--10 u-center u-vat c-box--150">
					<button class="btn btn-default c-box--100 c-button-height--57 button_search_progress" type="submit" name="submit" value="">
						全て
					</button>
				</td>
			</tr>
			<tr>
				<td class="c-tbl-border-right">
					<p>本日　{{isset($viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::PendingConfirmation->value]['today']) ? $viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::PendingConfirmation->value]['today'] : '0'}}件</p>
				</td>
				<td class="c-tbl-border-right">
					<p>本日　{{isset($viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::PendingCredit->value]['today']) ? $viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::PendingCredit->value]['today'] : '0'}}件</p>
				</td>
				<td class="c-tbl-border-right">
					<p>本日　{{isset($viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::PendingPrepayment->value]['today']) ? $viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::PendingPrepayment->value]['today'] : '0'}}件</p>
				</td>
				<td class="c-tbl-border-right">
					<p>本日　{{isset($viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::PendingAllocation->value]['today']) ? $viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::PendingAllocation->value]['today'] : '0'}}件</p>
				</td>
				<td class="c-tbl-border-right">
					<p>本日　{{isset($viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::PendingShipment->value]['today']) ? $viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::PendingShipment->value]['today'] : '0'}}件</p>
				</td>
				<td class="c-tbl-border-right">
					<p>本日　{{isset($viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::Shipping->value]['today']) ? $viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::Shipping->value]['today'] : '0'}}件</p>
				</td>
				<td class="c-tbl-border-right">
					<p>本日　{{isset($viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::Shipped->value]['today']) ? $viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::Shipped->value]['today'] : '0'}}件</p>
				</td>
				<td class="c-tbl-border-right">
					<p>本日　{{isset($viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::PendingPostPayment->value]['today']) ? $viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::PendingPostPayment->value]['today'] : '0'}}件</p>
				</td>
				<td class="c-tbl-border-right">
					<p>本日　{{isset($viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::Completed->value]['today']) ? $viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::Completed->value]['today'] : '0'}}件</p>
				</td>
				<td class="c-tbl-border-right">
					<p>本日　{{isset($viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::Cancelled->value]['today']) ? $viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::Cancelled->value]['today'] : '0'}}件</p>
				</td>
				<td class="c-tbl-border-right">
					<p>本日　{{isset($viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::Returned->value]['today']) ? $viewExtendData['order_progress_count'][\App\Enums\ProgressTypeEnum::Returned->value]['today'] : '0'}}件</p>
				</td>
				<td class="c-tbl-border-right">
				</td>
			</tr>
		</table>
	</div>

	<div class="c-box--800Half">
		<table class="table c-tbl c-tbl--790">
			<tbody>
				<tr>
					<th class="c-box--200">ECサイト</th>
					<td class="tag-box">
						@foreach($viewExtendData['ec_list'] as $ec)
							<label class="checkbox-inline u-ma--5-10-5-5">
								<input type="checkbox" name="m_ecs_id[]" id="m_ecs_id[]" value="{{ $ec['m_ecs_id'] }}" @if( isset($searchRow['m_ecs_id']) && in_array($ec['m_ecs_id'], $searchRow['m_ecs_id']) ) checked @endif>{{ $ec['m_ec_name'] }}
							</label>
						@endforeach
					</td>
				</tr>
				<tr>
					<th class="c-box--200">ECサイト注文ID</th>
					<td><input class="form-control" type="text" id="ec_order_num" name="ec_order_num" value="{{ isset($searchRow['ec_order_num']) ? $searchRow['ec_order_num'] : '' }}"></td>
				</tr>
				<tr>
					<th class="c-box--200">受注ID</th>
					<td>
						<textarea class="form-control c-box--200" rows="5" name="t_order_hdr_id">{{ isset($searchRow['t_order_hdr_id']) ? $searchRow['t_order_hdr_id'] : '' }}</textarea>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<div class="c-box--800Half">
		<table class="table c-tbl c-tbl--790">
			<tbody>
				<tr>
					<th class="c-box--200">注文者氏名・カナ氏名</th>
					<td>
						<input class="form-control c-box--300" type="text" id="order_name" name="order_name" value="{{ isset($searchRow['order_name']) ? $searchRow['order_name'] : '' }}">
					</td>
				</tr>
				<tr>
					<th class="c-box--200">電話番号・FAX</th>
					<td><input class="form-control c-box--300" type="text" id="tel_fax" name="tel_fax" value="{{ isset($searchRow['tel_fax']) ? $searchRow['tel_fax'] : '' }}"></td>
				</tr>
				<tr>
					<th class="c-box--200">メールアドレス</th>
					<td><input class="form-control c-box--300" type="text" id="order_email" name="order_email" value="{{ isset($searchRow['order_email']) ? $searchRow['order_email'] : '' }}"></td>
				</tr>
				<tr>
					<th class="c-box--200">受注日時</th>
					<td>
						<div class="u-mt--xs d-table">
							<div class="c-box--218 d-table-cell">
								<div class='input-group date datetime-picker'>
									<input type="text" class="form-control c-box--180" name="order_datetime_from" id="order_datetime_from" value="{{ isset($searchRow['order_datetime_from']) ? $searchRow['order_datetime_from'] : '' }}">
									<span class="input-group-addon">
										<span class="glyphicon glyphicon-calendar"></span>
									</span>
								</div>
							</div>
							<div class="d-table-cell">&nbsp;～&nbsp;</div>
							<div class='c-box--218 d-table-cell'>
								<div class='input-group date datetime-picker'>
									<input type="text" class="form-control c-box--180" name="order_datetime_to" id="order_datetime_to" value="{{ isset($searchRow['order_datetime_to']) ? $searchRow['order_datetime_to'] : '' }}">
									<span class="input-group-addon">
										<span class="glyphicon glyphicon-calendar"></span>
									</span>
								</div>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<th class="c-box--200">出荷予定日</th>
					<td>
						<div class="u-mt--xs d-table">
							<div class="c-box--218 d-table-cell">
								<div class='input-group date datetime-picker'>
									<input type="text" class="form-control c-box--180" id="deli_plan_date_from" name="deli_plan_date_from" value="{{ isset($searchRow['deli_plan_date_from']) ? $searchRow['deli_plan_date_from'] : '' }}">
									<span class="input-group-addon">
										<span class="glyphicon glyphicon-calendar"></span>
									</span>
								</div>
							</div>
							<div class="d-table-cell">&nbsp;～&nbsp;</div>
							<div class='c-box--218 d-table-cell'>
								<div class='input-group date datetime-picker'>
									<input type="text" class="form-control c-box--180" id="deli_plan_date_to" name="deli_plan_date_to" value="{{ isset($searchRow['deli_plan_date_to']) ? $searchRow['deli_plan_date_to'] : '' }}">
									<span class="input-group-addon">
										<span class="glyphicon glyphicon-calendar"></span>
									</span>
								</div>
							</div>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	@if(!empty($viewExtendData['m_tag_list']))
	<div class="c-box--1600">
		<table class="table c-tbl c-tbl--1600 c-tbl-border-all">
			<tr>
				<th class="c-box--200 u-vam">検索に含めるタグ</th>
				<td class="u-vam">
					<div class="tag-box">
						@foreach($viewExtendData['m_tag_list'] as $mTagRow)
							<label>
								<p data-toggle="tooltip" class="c-p--220 nowrap" data-placement="top" title="{{ isset($mTagRow['tag_context']) ? $mTagRow['tag_context'] : '' }}">
									<input type="checkbox" id="order_tags_include[]" name="order_tags_include[]" value="{{ $mTagRow['m_order_tag_id'] }}" @if( isset($searchRow['order_tags_include']) && in_array( $mTagRow['m_order_tag_id'], $searchRow['order_tags_include']) ) checked @endif>&nbsp;
									<a class="btn ns-orderTag-style c-a--0-207 nowrap-normal" style="background-color:#{{ isset($mTagRow['tag_color']) ? $mTagRow['tag_color'] : '000000'}};" type="button">
										<span style="color:#{{ isset($mTagRow['font_color']) ? $mTagRow['font_color'] : '000000'}};">
											@if( blank($mTagRow['deli_stop_flg']) || $mTagRow['deli_stop_flg'] < 0 )
												{{ isset($mTagRow['tag_display_name']) ? $mTagRow['tag_display_name'] :'' }} {{ isset($mTagRow['order_count']) ? $mTagRow['order_count'] : '0' }}件
											@else
												<u>{{ isset($mTagRow['tag_display_name']) ? $mTagRow['tag_display_name'] :'' }} {{ isset($mTagRow['order_count']) ? $mTagRow['order_count'] : '0' }}件</u>
											@endif
										</span>
									</a>
								</p>
							</label>
						@endforeach
					</div><!-- /.tag-box-->
				</td>
			</tr>
			<tr>
				<th class="c-box--200 u-vam exclude_tag_row">
					<a data-toggle="collapse" href="#collapse-tag-box" aria-expanded="false" id="exclude_tag_title" class="@if( count( ( $searchRow['order_tags_exclude'] ?? [] ) ) == 0 ) collapsed @endif">
						検索に含まれないタグ
					</a>
				</th>
				<td class="u-vam exclude_tag_row">
					<div class="@if( count( ( $searchRow['order_tags_exclude'] ?? [] ) ) == 0 ) collapse @endif" id="collapse-tag-box">
						<div class="tag-box">
							@foreach($viewExtendData['m_tag_list'] as $mTagRow)
								<label>
									<p data-toggle="tooltip" class="c-p--220 nowrap" data-placement="top" title="{{ isset($mTagRow['tag_context']) ? $mTagRow['tag_context'] : '' }}">
										<input type="checkbox" id="order_tags_exclude[]" name="order_tags_exclude[]" value="{{ $mTagRow['m_order_tag_id'] }}" @if( isset($searchRow['order_tags_exclude']) && in_array( $mTagRow['m_order_tag_id'], $searchRow['order_tags_exclude']) ) checked @endif>&nbsp;
										<a class="btn ns-orderTag-style c-a--0-207 nowrap-normal" style="background-color:#{{ isset($mTagRow['tag_color']) ? $mTagRow['tag_color'] : '000000'}};" type="button">
											<span style="color:#{{ isset($mTagRow['font_color']) ? $mTagRow['font_color'] : '000000' }};">
												@if(blank($mTagRow['deli_stop_flg']) || $mTagRow['deli_stop_flg'] < 0)
													{{ isset($mTagRow['tag_display_name']) ? $mTagRow['tag_display_name'] : '' }} {{ isset($mTagRow['order_count']) ? $mTagRow['order_count'] : '0' }}件
												@else
												<u>{{ isset($mTagRow['tag_display_name']) ? $mTagRow['tag_display_name'] : '' }} {{ isset($mTagRow['order_count']) ? $mTagRow['order_count'] : '0' }}件</u>
												@endif
											</span>
										</a>
									</p>
								</label>
							@endforeach
						</div>
					</div><!-- /.tag-box-->
				</td>
			</tr>
		</table>
	</div>
	@endif	

	<div class="c-box--1600">
		<div id="line-02"></div>
		@php 
			$detailAcordion = false; // 詳細検索
			$progressAcordion = false; // 詳細検索:進捗区分
			$baseAcordion = false; // 詳細検索:受注基本情報
			$orderAcordion = false; // 詳細検索:注文主
			$pageAcordion = false; // 詳細検索:購入商品
			$paymentAcordion = false; // 詳細検索:決済
			$deliveryAcordion = false; // 詳細検索:配送情報

			// 詳細検索
			$countDetailCond = 0;
			$countDetailCond += ( isset($searchRow['display_period']) && !is_null($searchRow['display_period']) ) ? 1 : 0;
			$countDetailCond += ( isset($searchRow['order_time_from']) && !is_null($searchRow['order_time_from']) ) ? 1 : 0;
			if( $countDetailCond > 0 ){
				$detailAcordion = true;
			}
			// 進捗区分
			$countProgressCond = 0;
			$countProgressCond += ( isset($searchRow['alert_cust_check_type']) && !is_null($searchRow['alert_cust_check_type']) ) ? 1 : 0;
			$countProgressCond += ( isset($searchRow['address_check_type']) && !is_null($searchRow['address_check_type']) ) ? 1 : 0;
			$countProgressCond += ( isset($searchRow['deli_hope_date_check_type']) && !is_null($searchRow['deli_hope_date_check_type']) ) ? 1 : 0;
			$countProgressCond += ( isset($searchRow['credit_type']) && !is_null($searchRow['credit_type']) ) ? 1 : 0;
			$countProgressCond += ( isset($searchRow['payment_type_mae']) && !is_null($searchRow['payment_type_mae']) ) ? 1 : 0;
			$countProgressCond += ( isset($searchRow['reservation_type']) && !is_null($searchRow['reservation_type']) ) ? 1 : 0;
			$countProgressCond += ( isset($searchRow['deli_instruct_type']) && !is_null($searchRow['deli_instruct_type']) ) ? 1 : 0;
			$countProgressCond += ( isset($searchRow['deli_decision_type']) && !is_null($searchRow['deli_decision_type']) ) ? 1 : 0;
			$countProgressCond += ( isset($searchRow['settlement_sales_type']) && !is_null($searchRow['settlement_sales_type']) ) ? 1 : 0;
			$countProgressCond += ( isset($searchRow['sales_status_type']) && !is_null($searchRow['sales_status_type']) ) ? 1 : 0;
			$countProgressCond += ( isset($searchRow['payment_type_ato']) && !is_null($searchRow['payment_type_ato']) ) ? 1 : 0;
			if( $countProgressCond > 0 ){
				$detailAcordion = true;
				$progressAcordion = true;
			}
			// 受注基本情報
			$countBaseCond = 0;
			$countBaseCond += ( isset($searchRow['m_payment_types_id']) && !is_null($searchRow['m_payment_types_id']) ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['immediately_deli_flg']) && !is_null($searchRow['immediately_deli_flg']) ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['rakuten_super_deal_flg']) && !is_null($searchRow['rakuten_super_deal_flg']) ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['bundle']) && !is_null($searchRow['bundle']) ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['repeat_order']) && !is_null($searchRow['repeat_order']) ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['order_operator_id']) && !is_null($searchRow['order_operator_id']) && $searchRow['order_operator_id'] != '' ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['update_operator_id']) && !is_null($searchRow['update_operator_id']) && $searchRow['update_operator_id'] != '' ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['order_type']) && !is_null($searchRow['order_type']) ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['gift_flg']) && !is_null($searchRow['gift_flg']) ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['alert_order_flg']) && !is_null($searchRow['alert_order_flg']) ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['total_deli_flg']) && !is_null($searchRow['total_deli_flg']) ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['total_temperature_zone_type']) && !is_null($searchRow['total_temperature_zone_type']) && $searchRow['total_temperature_zone_type'] != '' ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['total_price_from']) && !is_null($searchRow['total_price_from']) && $searchRow['total_price_from'] != '' ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['total_price_to']) && !is_null($searchRow['total_price_to']) && $searchRow['total_price_to'] != '' ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['order_total_price_from']) && !is_null($searchRow['order_total_price_from']) && $searchRow['order_total_price_from'] != '' ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['order_total_price_to']) && !is_null($searchRow['order_total_price_to']) && $searchRow['order_total_price_to'] != '' ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['shipping_fee_from']) && !is_null($searchRow['shipping_fee_from']) && $searchRow['shipping_fee_from'] != '' ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['shipping_fee_to']) && !is_null($searchRow['shipping_fee_to']) && $searchRow['shipping_fee_to'] != '' ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['noshi_flg']) && !is_null($searchRow['noshi_flg']) ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['order_comment_flg']) && !is_null($searchRow['order_comment_flg']) ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['order_comment']) && !is_null($searchRow['order_comment']) && $searchRow['order_comment'] != '' ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['operator_comment_flg']) && !is_null($searchRow['operator_comment_flg']) ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['operator_comment']) && !is_null($searchRow['operator_comment']) && $searchRow['operator_comment'] != '' ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['cancel_date_from']) && !is_null($searchRow['cancel_date_from']) && $searchRow['cancel_date_from'] != '' ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['cancel_date_to']) && !is_null($searchRow['cancel_date_to']) && $searchRow['cancel_date_to'] != '' ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['last_receipt_date_from']) && !is_null($searchRow['last_receipt_date_from']) && $searchRow['last_receipt_date_from'] != '' ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['last_receipt_date_to']) && !is_null($searchRow['last_receipt_date_to']) && $searchRow['last_receipt_date_to'] != '' ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['m_email_templates_id']) && !is_null($searchRow['m_email_templates_id']) ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['estimate_flg']) && !is_null($searchRow['estimate_flg']) ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['receipt_flg']) && !is_null($searchRow['receipt_flg']) ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['campaign_flg']) && !is_null($searchRow['campaign_flg']) ) ? 1 : 0;
			$countBaseCond += ( isset($searchRow['sales_store']) && !is_null($searchRow['sales_store']) ) ? 1 : 0;
			if( $countBaseCond > 0 ){
				$detailAcordion = true;
				$baseAcordion = true;
			}
			// 注文主
			$countOrderCond = 0;
			$countOrderCond += ( isset($searchRow['m_cust_runk_id']) && !is_null($searchRow['m_cust_runk_id']) ) ? 1 : 0;
			$countOrderCond += ( isset($searchRow['m_cust_id']) && !is_null($searchRow['m_cust_id']) && $searchRow['m_cust_id'] != '' ) ? 1 : 0;
			$countOrderCond += ( isset($searchRow['cust_cd']) && !is_null($searchRow['cust_cd']) && $searchRow['cust_cd'] != '' ) ? 1 : 0;
			$countOrderCond += ( isset($searchRow['reserve10']) && !is_null($searchRow['reserve10']) && $searchRow['reserve10'] != '' ) ? 1 : 0;
			$countOrderCond += ( isset($searchRow['alert_cust_type']) && !is_null($searchRow['alert_cust_type']) ) ? 1 : 0;
			if( $countOrderCond > 0 ){
				$detailAcordion = true;
				$orderAcordion = true;
			}
			// 購入商品
			$countPageCond = 0;
			$countPageCond += ( isset($searchRow['sell_cd']) && !is_null($searchRow['sell_cd']) && $searchRow['sell_cd'] != '' ) ? 1 : 0;
			$countPageCond += ( isset($searchRow['sell_name']) && !is_null($searchRow['sell_name']) && $searchRow['sell_name'] != '' ) ? 1 : 0;
			$countPageCond += ( isset($searchRow['sell_option']) && !is_null($searchRow['sell_option']) && $searchRow['sell_option'] != '' ) ? 1 : 0;
			$countPageCond += ( isset($searchRow['m_suppliers_id']) && !is_null($searchRow['m_suppliers_id']) && $searchRow['m_suppliers_id'] != '' ) ? 1 : 0;
			$countPageCond += ( isset($searchRow['item_cd']) && !is_null($searchRow['item_cd']) && $searchRow['item_cd'] != '' ) ? 1 : 0;
			if( $countPageCond > 0 ){
				$detailAcordion = true;
				$pageAcordion = true;
			}
			// 決済
			$countPaymentCond = 0;
			$countPaymentCond += ( isset($searchRow['disp_settlement_sales_type']) && !is_null($searchRow['disp_settlement_sales_type']) ) ? 1 : 0;
			$countPaymentCond += ( isset($searchRow['payment_diff_flg']) && !is_null($searchRow['payment_diff_flg']) ) ? 1 : 0;
			$countPaymentCond += ( isset($searchRow['payment_date_from']) && !is_null($searchRow['payment_date_from']) && $searchRow['payment_date_from'] != '' ) ? 1 : 0;
			$countPaymentCond += ( isset($searchRow['payment_date_to']) && !is_null($searchRow['payment_date_to']) && $searchRow['payment_date_to'] != '' ) ? 1 : 0;
			$countPaymentCond += ( isset($searchRow['payment_price_from']) && !is_null($searchRow['payment_price_from']) && $searchRow['payment_price_from'] != '' ) ? 1 : 0;
			$countPaymentCond += ( isset($searchRow['payment_price_to']) && !is_null($searchRow['payment_price_to']) && $searchRow['payment_price_to'] != '' ) ? 1 : 0;
			$countPaymentCond += ( isset($searchRow['payment_transaction_id']) && !is_null($searchRow['payment_transaction_id']) && $searchRow['payment_transaction_id'] != '' ) ? 1 : 0;
			$countPaymentCond += ( isset($searchRow['cb_credit_status']) && !is_null($searchRow['cb_credit_status']) ) ? 1 : 0;
			$countPaymentCond += ( isset($searchRow['cb_deli_status']) && !is_null($searchRow['cb_deli_status']) ) ? 1 : 0;
			$countPaymentCond += ( isset($searchRow['cb_billed_status']) && !is_null($searchRow['cb_billed_status']) ) ? 1 : 0;
			$countPaymentCond += ( isset($searchRow['cb_billed_type']) && !is_null($searchRow['cb_billed_type']) ) ? 1 : 0;
			if( $countPaymentCond > 0 ){
				$detailAcordion = true;
				$paymentAcordion = true;
			}
			// 配送情報
			$countDeliveryCond = 0;
			$countDeliveryCond += ( isset($searchRow['t_deli_hdr_id']) && !is_null($searchRow['t_deli_hdr_id']) && $searchRow['t_deli_hdr_id'] != '' ) ? 1 : 0;
			$countDeliveryCond += ( isset($searchRow['m_delivery_types_id']) && !is_null($searchRow['m_delivery_types_id']) ) ? 1 : 0;
			$countDeliveryCond += ( isset($searchRow['destination_alter_flg']) && !is_null($searchRow['destination_alter_flg']) ) ? 1 : 0;
			$countDeliveryCond += ( isset($searchRow['destination_address1']) && !is_null($searchRow['destination_address1']) ) ? 1 : 0;
			$countDeliveryCond += ( isset($searchRow['multiple_deli_flg']) && !is_null($searchRow['multiple_deli_flg']) ) ? 1 : 0;
			$countDeliveryCond += ( isset($searchRow['invoice_num1']) && !is_null($searchRow['invoice_num1']) && $searchRow['invoice_num1'] != '' ) ? 1 : 0;
			$countDeliveryCond += ( isset($searchRow['deli_decision_date_flg']) && !is_null($searchRow['deli_decision_date_flg']) ) ? 1 : 0;
			$countDeliveryCond += ( isset($searchRow['deli_decision_date_from']) && !is_null($searchRow['deli_decision_date_from']) && $searchRow['deli_decision_date_from'] != '' ) ? 1 : 0;
			$countDeliveryCond += ( isset($searchRow['deli_decision_date_to']) && !is_null($searchRow['deli_decision_date_to']) && $searchRow['deli_decision_date_to'] != '' ) ? 1 : 0;
			$countDeliveryCond += ( isset($searchRow['deli_hope_date_from']) && !is_null($searchRow['deli_hope_date_from']) && $searchRow['deli_hope_date_from'] != '' ) ? 1 : 0;
			$countDeliveryCond += ( isset($searchRow['deli_hope_date_to']) && !is_null($searchRow['deli_hope_date_to']) && $searchRow['deli_hope_date_to'] != '' ) ? 1 : 0;
			$countDeliveryCond += ( isset($searchRow['deli_hope_date_nothing_flg']) && !is_null($searchRow['deli_hope_date_nothing_flg']) ) ? 1 : 0;
			$countDeliveryCond += ( isset($searchRow['deli_hope_time_cd']) && !is_null($searchRow['deli_hope_time_cd']) && $searchRow['deli_hope_time_cd'] != '' ) ? 1 : 0;
			$countDeliveryCond += ( isset($searchRow['deli_plan_date_nothing_flg']) && !is_null($searchRow['deli_plan_date_nothing_flg']) ) ? 1 : 0;
			$countDeliveryCond += ( isset($searchRow['destination_name']) && !is_null($searchRow['destination_name']) && $searchRow['destination_name'] != '' ) ? 1 : 0;
			$countDeliveryCond += ( isset($searchRow['invoice_comment_flg']) && !is_null($searchRow['invoice_comment_flg']) ) ? 1 : 0;
			$countDeliveryCond += ( isset($searchRow['invoice_comment']) && !is_null($searchRow['invoice_comment']) && $searchRow['invoice_comment'] != '' ) ? 1 : 0;
			$countDeliveryCond += ( isset($searchRow['picking_comment']) && !is_null($searchRow['picking_comment']) && $searchRow['picking_comment'] != '' ) ? 1 : 0;
			$countDeliveryCond += ( isset($searchRow['picking_comment_flg']) && !is_null($searchRow['picking_comment_flg']) ) ? 1 : 0;
			$countDeliveryCond += ( isset($searchRow['m_warehouse_id']) && !is_null($searchRow['m_warehouse_id']) ) ? 1 : 0;
			$countDeliveryCond += ( isset($searchRow['multi_warehouse_flg']) && !is_null($searchRow['multi_warehouse_flg']) ) ? 1 : 0;
			$countDeliveryCond += ( isset($searchRow['temperature_zone']) && !is_null($searchRow['temperature_zone']) ) ? 1 : 0;
			$countDeliveryCond += ( isset($searchRow['direct_deli_flg']) && !is_null($searchRow['direct_deli_flg']) ) ? 1 : 0;
			if( $countDeliveryCond > 0 ){
				$detailAcordion = true;
				$deliveryAcordion = true;
			}
		@endphp
		<!-- 詳細検索 -->
		@if( $detailAcordion )
		<div class="c-btn--03 u-mt--sl"><a data-toggle="collapse" href="#collapse-menu" aria-expanded="true">詳細検索</a></div>
		<div class="collapse in" id="collapse-menu" aria-expanded="true" style="">
		@else
		<div class="c-btn--03 u-mt--sl"><a class="collapsed" data-toggle="collapse" href="#collapse-menu" aria-expanded="false">詳細検索</a></div>
		<div class="collapse" id="collapse-menu" aria-expanded="false" style="height: 0px;">
		@endif
			<div class="c-box--1600">
				<table class="table c-tbl c-tbl--1590">
					<tbody>
						<tr>
							<th class="c-box--180">表示期間</th>
							<td class="list-radio-box">
								@foreach( \App\Enums\DisplayPeriodEnum::cases() as $displayPeriod )
									<label class="radio-inline"><input type="radio" name="display_period" value="{{ $displayPeriod->value }}" @if( isset($searchRow['display_period']) && $searchRow['display_period'] == $displayPeriod->value ) checked @endif>{{ $displayPeriod->label() }}</label>
								@endforeach
							</td>
						</tr>
						<tr>
							<th class="c-box--180">表示開始時刻</th>
							<td>
								<div class="c-box--218">
									<div class="input-group date time-picker">
										<input type="text" class="form-control c-box--180" name="order_time_from" id="order_time_from" value="{{ isset($searchRow['order_time_from']) ? $searchRow['order_time_from'] : '' }}">
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-time"></span>
										</span>
									</div>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="c-box-inner">
				<!-- 進捗区分 -->
				@if( $progressAcordion )
				<div class="c-btn--03"><a data-toggle="collapse" href="#collapse-sub-menu-1" aria-expanded="true">進捗区分</a></div>
				<div class="collapse in" id="collapse-sub-menu-1" aria-expanded="true" style="">
				@else
				<div class="c-btn--03"><a class="collapsed" data-toggle="collapse" href="#collapse-sub-menu-1" aria-expanded="false">進捗区分</a></div>
				<div class="collapse" id="collapse-sub-menu-1" aria-expanded="false" style="height: 0px;">
				@endif
					<table class="table c-tbl c-tbl-border-left c-tbl-border-bottom">
						<tr>
							<td class="c-tbl-border-right c-states--02 u-center">
								{{ isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingConfirmation->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingConfirmation->value] : '確認待' }}
							</td>
							<td class="c-tbl-border-right c-states--02 u-center">
								{{ isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingCredit->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingCredit->value] : '与信待' }}
							</td>
							<td class="c-tbl-border-right c-states--02 u-center">
								{{ isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingPrepayment->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingPrepayment->value] : '前払入金待' }}
							</td>
							<td class="c-tbl-border-right c-states--02 u-center">
								{{ isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingAllocation->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingAllocation->value] : '引当待' }}
							</td>
							<td class="c-tbl-border-right c-states--03 u-center">
								{{ isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingShipment->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingShipment->value] : '出荷待' }}
							</td>
							<td class="c-tbl-border-right c-states--04 u-center">
								{{ isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::Shipping->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::Shipping->value] : '出荷中' }}
							</td>
							<td class="c-tbl-border-right c-states--05 u-center">
								{{ isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::Shipped->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::Shipped->value] : '出荷済' }}
							</td>
							<td class="c-tbl-border-right c-states--06 u-center">
								{{ isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingPostPayment->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingPostPayment->value] : '後払入金待' }}
							</td>
						</tr>
						<tr>
							<td class="c-tbl-border-right u-center u-vat">
								<table class="c-box--full">
									<tbody>
										<tr>
											<td class="u-left c-box--140">
												<p class="list-title">要注意顧客</p>
												@foreach( \App\Enums\AlertCustCheckTypeEnum::cases() as $alertCustCheckType )
													<div class="checkbox font-13"><label><input type="checkbox" name="alert_cust_check_type[]" id="alert_cust_check_type[]" value="{{ $alertCustCheckType->value }}" @if( isset($searchRow['alert_cust_check_type']) && in_array($alertCustCheckType->value, $searchRow['alert_cust_check_type']) ) checked @endif>{{ $alertCustCheckType->label() }}</label></div>
												@endforeach
											<td class="u-left c-box--140">
												<p class="list-title">住所エラー</p>
												@foreach( \App\Enums\AddressCheckTypeEnum::cases() as $addressCheckType )
													<div class="checkbox font-13"><label><input type="checkbox" name="address_check_type[]" id="address_check_type[]" value="{{ $addressCheckType->value }}" @if( isset($searchRow['address_check_type']) && in_array($addressCheckType->value, $searchRow['address_check_type']) ) checked @endif>{{ $addressCheckType->label() }}</label></div>
												@endforeach
											</td>
											<td class="u-left c-box--140">
												<p class="list-title">配達指定エラー</p>
												@foreach( \App\Enums\DeliHopeDateCheckTypeEnum::cases() as $deliHopeDateCheckType )
													<div class="checkbox font-13"><label><input type="checkbox" name="deli_hope_date_check_type[]" id="deli_hope_date_check_type[]" value="{{ $deliHopeDateCheckType->value }}" @if( isset($searchRow['deli_hope_date_check_type']) && in_array($deliHopeDateCheckType->value, $searchRow['deli_hope_date_check_type']) ) checked @endif>{{ $deliHopeDateCheckType->label() }}</label></div>
												@endforeach
											</td>
										</tr>
									</tbody>
								</table>
							</td>
							<td class="c-tbl-border-right u-center u-vat">
								<table class="c-box--full">
									<tbody>
										<tr>
											<td class="u-left c-box--140">
												<p class="list-title">与信区分</p>
												@foreach( \App\Enums\CreditTypeEnum::cases() as $creditType )
													<div class="checkbox font-13"><label><input type="checkbox" name="credit_type[]" id="credit_type[]" value="{{ $creditType->value }}" @if( isset($searchRow['credit_type']) && in_array($creditType->value, $searchRow['credit_type']) ) checked @endif>{{ $creditType->label() }}</label></div>
												@endforeach
											</td>
										</tr>
									</tbody>
								</table>
							</td>
							<td class="c-tbl-border-right u-center u-vat">
								<table class="c-box--full">
									<tbody>
										<tr>
											<td class="u-left c-box--140">
												<p class="list-title">入金区分</p>
												@foreach( \App\Enums\PaymentTypeEnum::cases() as $paymentType )
													<div class="checkbox font-13"><label><input type="checkbox" name="payment_type_mae[]" id="payment_type_mae[]" value="{{ $paymentType->value }}" @if( isset($searchRow['payment_type_mae']) && in_array($paymentType->value, $searchRow['payment_type_mae']) ) checked @endif>{{ $paymentType->label() }}</label></div>
												@endforeach
											</td>
										</tr>
									</tbody>
								</table>
							</td>
							<td class="c-tbl-border-right u-center u-vat">
								<table class="c-box--full">
									<tbody>
										<tr>
											<td class="u-left c-box--140">
												<p class="list-title">引当区分</p>
												@foreach( \App\Enums\ReservationTypeEnum::cases() as $reservationType )
													<div class="checkbox font-13"><label><input type="checkbox" name="reservation_type[]" id="reservation_type[]" value="{{ $reservationType->value }}" @if( isset($searchRow['reservation_type']) && in_array($reservationType->value, $searchRow['reservation_type']) ) checked @endif>{{ $reservationType->label() }}</label></div>
												@endforeach
											</td>
										</tr>
									</tbody>
								</table>
							</td>
							<td class="c-tbl-border-right u-center u-vat">
								<table class="c-box--full">
									<tbody>
										<tr>
											<td class="u-left c-box--140">
												<p class="list-title">出荷指示区分</p>
												@foreach( \App\Enums\DeliInstructTypeEnum::cases() as $deliInstructType )
													<div class="checkbox font-13"><label><input type="checkbox" name="deli_instruct_type[]" id="deli_instruct_type[]" value="{{ $deliInstructType->value }}" @if( isset($searchRow['deli_instruct_type']) && in_array($deliInstructType->value, $searchRow['deli_instruct_type']) ) checked @endif>{{ $deliInstructType->label() }}</label></div>
												@endforeach
											</td>
										</tr>
									</tbody>
								</table>
							</td>
							<td class="c-tbl-border-right u-center u-vat">
								<table class="c-box--full">
									<tbody>
										<tr>
											<td class="u-left c-box--140">
												<p class="list-title">出荷確定区分</p>
												@foreach( \App\Enums\DeliDecisionTypeEnum::cases() as $deliDecisionType )
													<div class="checkbox font-13"><label><input type="checkbox" name="deli_decision_type[]" id="deli_decision_type[]" value="{{ $deliDecisionType->value }}" @if( isset($searchRow['deli_decision_type']) && in_array($deliDecisionType->value, $searchRow['deli_decision_type']) ) checked @endif>{{ $deliDecisionType->label() }}</label></div>
												@endforeach
											</td>
										</tr>
									</tbody>
								</table>
							</td>
							<td class="c-tbl-border-right u-center u-vat">
								<table class="c-box--full">
									<tbody>
										<tr>
											<td class="u-left c-box--140">
												<p class="list-title">決済売上区分</p>
												@foreach( \App\Enums\SettlementSalesTypeEnum::cases() as $settlementSalesType )
													<div class="checkbox font-13"><label><input type="checkbox" name="settlement_sales_type[]" id="settlement_sales_type[]" value="{{ $settlementSalesType->value }}" @if( isset($searchRow['settlement_sales_type']) && in_array($settlementSalesType->value, $searchRow['settlement_sales_type']) ) checked @endif>{{ $settlementSalesType->label() }}</label></div>
												@endforeach
											</td>
											<td class="u-left c-box--140">
												<p class="list-title">ECステータス区分</p>
												@foreach( \App\Enums\SalesStatusTypeEnum::cases() as $salesStatusType )
													<div class="checkbox font-13"><label><input type="checkbox" name="sales_status_type[]" id="sales_status_type[]" value="{{ $salesStatusType->value }}" @if( isset($searchRow['sales_status_type']) && in_array($salesStatusType->value, $searchRow['sales_status_type']) ) checked @endif>{{ $salesStatusType->label() }}</label></div>
												@endforeach
											</td>
										</tr>
									</tbody>
								</table>
							</td>
							<td class="c-tbl-border-right u-center u-vat">
								<table class="c-box--full">
									<tbody>
										<tr>
											<td class="u-left c-box--140">
												<p class="list-title">入金区分</p>
												@foreach( \App\Enums\PaymentTypeEnum::cases() as $paymentType )
													<div class="checkbox font-13"><label><input type="checkbox" name="payment_type_ato[]" id="payment_type_ato[]" value="{{ $paymentType->value }}" @if( isset($searchRow['payment_type_ato']) && in_array($paymentType->value, $searchRow['payment_type_ato']) ) checked @endif>{{ $paymentType->label() }}</label></div>
												@endforeach
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</table>
				</div>

				<!-- 受注基本情報 -->
				@if( $baseAcordion )
				<div class="c-btn--03"><a data-toggle="collapse" href="#collapse-sub-menu-2" aria-expanded="true">受注基本情報</a></div>
				<div class="collapse in" id="collapse-sub-menu-2" aria-expanded="true" style="">
				@else
				<div class="c-btn--03"><a class="collapsed" data-toggle="collapse" href="#collapse-sub-menu-2" aria-expanded="false">受注基本情報</a></div>
				<div class="collapse" id="collapse-sub-menu-2" aria-expanded="false" style="height: 0px;">
				@endif
					<div class="c-box--800Half">
						<table class="table c-tbl c-tbl--790">
							<tbody>
								<tr>
									<th class="c-box--180">支払方法</th>
									<td class="tag-box">
										@foreach($viewExtendData['m_paytype_list'] as $mPayType)
											<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="m_payment_types_id[]" id="m_payment_types_id[]" value="{{$mPayType['m_payment_types_id']}}" @if( isset($searchRow['m_payment_types_id']) && in_array($mPayType['m_payment_types_id'], $searchRow['m_payment_types_id']) ) checked @endif>{{ $mPayType['m_payment_types_name'] }}</label>
										@endforeach
									</td>
								</tr>
								<tr>
									<th>即日配送</th>
									<td class="tag-box">
										<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="immediately_deli_flg[]" id="immediately_deli_flg[]" value="1" @if( isset($searchRow['immediately_deli_flg']) && in_array(1, $searchRow['immediately_deli_flg']) ) checked @endif>対象</label>
										<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="immediately_deli_flg[]" id="immediately_deli_flg[]" value="0" @if( isset($searchRow['immediately_deli_flg']) && in_array(0, $searchRow['immediately_deli_flg']) ) checked @endif>非対象</label>
									</td>
								</tr>
								<tr>
									<th>楽天スーパーDEAL</th>
									<td class="tag-box">
										<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="rakuten_super_deal_flg[]" id="rakuten_super_deal_flg[]" value="1" @if( isset($searchRow['rakuten_super_deal_flg']) && in_array(1, $searchRow['rakuten_super_deal_flg']) ) checked @endif>対象</label>
										<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="rakuten_super_deal_flg[]" id="rakuten_super_deal_flg[]" value="0" @if( isset($searchRow['rakuten_super_deal_flg']) && in_array(0, $searchRow['rakuten_super_deal_flg']) ) checked @endif>非対象</label>
									</td>
								</tr>
								<tr>
									<th>同梱</th>
									<td class="tag-box">
										<label for=""><input type="checkbox" id="bundle" name="bundle" value="1" @if( isset($searchRow['bundle']) && $searchRow['bundle'] == 1 ) checked @endif></label>
									</td>
								</tr>
								<tr>
									<th>リピート注文</th>
									<td class="tag-box">
										<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="repeat_order[]" id="repeat_order[]" value="1" @if( isset($searchRow['repeat_order']) && in_array(1, $searchRow['repeat_order']) ) checked @endif>リピート注文</label>
										<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="repeat_order[]" id="repeat_order[]" value="0" @if( isset($searchRow['repeat_order']) && in_array(0, $searchRow['repeat_order']) ) checked @endif>新規注文</label>
									</td>
								</tr>
								<tr>
									<th>受注担当者</th>
									<td>
										<select class="form-control c-box--200" id="order_operator_id" name="order_operator_id">
											<option value=""></option>
											@foreach( $viewExtendData['m_operator_list'] as $operator )
												<option value="{{ $operator['m_operators_id'] }}" @if( isset($searchRow['order_operator_id']) && $searchRow['order_operator_id'] == $operator['m_operators_id'] ) selected @endif>{{ $operator['m_operator_name'] }}</option>
											@endforeach
										</select>
									</td>
								</tr>
								<tr>
									<th>更新担当者</th>
									<td>
										<select class="form-control c-box--200" id="update_operator_id" name="update_operator_id">
											<option value=""></option>
											@foreach( $viewExtendData['m_operator_list'] as $operator )
												<option value="{{ $operator['m_operators_id'] }}" @if( isset($searchRow['update_operator_id']) && $searchRow['update_operator_id'] == $operator['m_operators_id'] ) selected @endif>{{ $operator['m_operator_name'] }}</option>
											@endforeach
										</select>
									</td>
								</tr>
								<tr>
									<th>受注方法</th>
									<td class="tag-box">
										@foreach( $viewExtendData['order_type_list'] as $orderType )
											<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="order_type[]" id="order_type[]" value="{{$orderType['m_itemname_types_id']}}" @if( isset($searchRow['order_type']) && in_array( $orderType['m_itemname_types_id'], $searchRow['order_type'] ) ) checked @endif>{{ $orderType['m_itemname_type_name'] }}</label>
										@endforeach
									</td>
								</tr>
								<tr>
									<th>ギフトフラグ</th>
									<td class="tag-box">
										<label><input type="checkbox" name="gift_flg" id="gift_flg" value="1" @if( isset($searchRow['gift_flg']) && $searchRow['gift_flg'] == 1) checked @endif></label>
									</td>
								</tr>
								<tr>
									<th>警告注文</th>
									<td class="tag-box">
										<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="alert_order_flg[]" id="alert_order_flg[]" value="1" @if( isset($searchRow['alert_order_flg']) && in_array(1, $searchRow['alert_order_flg']) ) checked @endif>有</label>
										<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="alert_order_flg[]" id="alert_order_flg[]" value="0" @if( isset($searchRow['alert_order_flg']) && in_array(1, $searchRow['alert_order_flg']) ) checked @endif>無</label>
									</td>
								</tr>
								<tr>
									<th>配送種別</th>
									<td class="tag-box">
										<div class="d-table">
											<div class="d-table-cell">
												<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="total_deli_flg" id="total_deli_flg" value="1" @if( isset($searchRow['total_deli_flg']) && $searchRow['total_deli_flg'] == 1 ) checked @endif>同梱配送</label>
											</div>
											<div class="d-table-cell">&nbsp;&nbsp;</div>
											<div class="d-table-cell">
												<select class="form-control c-box--200" id="total_temperature_zone_type" name="total_temperature_zone_type">
													<option value=""></option>
													@foreach( $viewExtendData['delivery_type_list'] as $deliveryType )
														<option value="{{ $deliveryType['m_delivery_types_id'] }}" @if( isset($searchRow['total_temperature_zone_type']) && $searchRow['total_temperature_zone_type'] == $deliveryType['m_delivery_types_id'] ) selected @endif>{{ $deliveryType['m_delivery_type_name'] }}</option>
													@endforeach
												</select>
											</div>
										</div>
									</td>
								</tr>
								<tr>
									<th>合計金額</th>
									<td>
										<input class="form-control u-input--mid" type="text" id="total_price_from" name="total_price_from" value="{{ isset($searchRow['total_price_from']) ? $searchRow['total_price_from'] : '' }}">
										&nbsp;〜&nbsp;
										<input class="form-control u-input--mid" type="text" id="total_price_to" name="total_price_to" value="{{ isset($searchRow['total_price_to']) ? $searchRow['total_price_to'] : '' }}">
									</td>
								</tr>
								<tr>
									<th>請求金額</th>
									<td>
										<input class="form-control u-input--mid" type="text" id="order_total_price_from" name="order_total_price_from" value="{{ isset($searchRow['order_total_price_from']) ? $searchRow['order_total_price_from'] : '' }}">
										&nbsp;〜&nbsp;
										<input class="form-control u-input--mid" type="text" id="order_total_price_to" name="order_total_price_to" value="{{ isset($searchRow['order_total_price_to']) ? $searchRow['order_total_price_to'] : ''}}">
									</td>
								</tr>
								<tr>
									<th>送料</th>
									<td>
										<input class="form-control u-input--mid" type="text"id="shipping_fee_from" name="shipping_fee_from" value="{{ isset($searchRow['shipping_fee_from']) ? $searchRow['shipping_fee_from'] : ''}}">
										&nbsp;〜&nbsp;
										<input class="form-control u-input--mid" type="text"id="shipping_fee_to" name="shipping_fee_to" value="{{ isset($searchRow['shipping_fee_to']) ? $searchRow['shipping_fee_to'] : ''}}">
									</td>
								</tr>
								<tr>
									<th>熨斗設定</th>
									<td class="tag-box">
										<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="noshi_flg[]" id="noshi_flg[]" value="1"  @if( isset($searchRow['noshi_flg']) && in_array(1, $searchRow['noshi_flg']) ) checked @endif>有</label>
										<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="noshi_flg[]" id="noshi_flg[]" value="0"  @if( isset($searchRow['noshi_flg']) && in_array(0, $searchRow['noshi_flg']) ) checked @endif>無</label>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="c-box--800Half">
						<table class="table c-tbl c-tbl--790">
							<tbody>
								<tr>
									<th class="c-box--180">備考の有無</th>
									<td class="tag-box">
										<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="order_comment_flg[]" id="order_comment_flg[]" value="1"  @if( isset($searchRow['order_comment_flg']) && in_array(1, $searchRow['order_comment_flg']) ) checked @endif>有</label>
										<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="order_comment_flg[]" id="order_comment_flg[]" value="0"  @if( isset($searchRow['order_comment_flg']) && in_array(0, $searchRow['order_comment_flg']) ) checked @endif>無</label>
									</td>
								</tr>
								<tr>
									<th>備考</th>
									<td><textarea name="order_comment" id="order_comment" cols="20" rows="5" class="form-control c-box--400">{{ isset($searchRow['order_comment']) ? $searchRow['order_comment'] : '' }}</textarea></td>
								</tr>
								<tr>
									<th>社内メモの有無</th>
									<td class="tag-box">
										<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="operator_comment_flg[]" id="operator_comment_flg[]" value="1"  @if( isset($searchRow['operator_comment_flg']) && in_array(1, $searchRow['operator_comment_flg']) ) checked @endif>有</label>
										<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="operator_comment_flg[]" id="operator_comment_flg[]" value="0"  @if( isset($searchRow['operator_comment_flg']) && in_array(0, $searchRow['operator_comment_flg']) ) checked @endif>無</label>
									</td>
								</tr>
								<tr>
									<th>社内メモ</th>
									<td>
										<textarea name="operator_comment" id="operator_comment" cols="20" rows="5" class="form-control c-box--400">{{ isset($searchRow['operator_comment']) ? $searchRow['operator_comment'] : '' }}</textarea>
									</td>
								</tr>
								<tr>
									<th>受注キャンセル日</th>
									<td>
										<div class="u-mt--xs d-table">
											<div class="c-box--218 d-table-cell">
												<div class="input-group date date-picker">
													<input type="text" class="form-control c-box--180" id="cancel_date_from" name="cancel_date_from" value="{{ isset($searchRow['cancel_date_from']) ? $searchRow['cancel_date_from'] : '' }}">
													<span class="input-group-addon">
														<span class="glyphicon glyphicon-calendar"></span>
													</span>
												</div>
											</div>
											<div class="d-table-cell">&nbsp;～&nbsp;</div>
											<div class="c-box--218 d-table-cell">
												<div class="input-group date date-picker">
													<input type="text" class="form-control c-box--180" id="cancel_date_to" name="cancel_date_to" value="{{ isset($searchRow['cancel_date_to']) ? $searchRow['cancel_date_to'] : '' }}">
													<span class="input-group-addon">
														<span class="glyphicon glyphicon-calendar"></span>
													</span>
												</div>
											</div>
										</div>
									</td>
								</tr>
								<tr>
								<th>領収書最終出力日</th>
									<td>
										<div class="u-mt--xs d-table">
											<div class="c-box--218 d-table-cell">
												<div class="input-group date date-picker">
													<input type="text" class="form-control c-box--180" id="last_receipt_date_from" name="last_receipt_date_from" value="{{ isset($searchRow['last_receipt_date_from']) ? $searchRow['last_receipt_date_from'] : '' }}">
													<span class="input-group-addon">
														<span class="glyphicon glyphicon-calendar"></span>
													</span>
												</div>
											</div>
											<div class="d-table-cell">&nbsp;～&nbsp;</div>
											<div class="c-box--218 d-table-cell">
												<div class="input-group date date-picker">
													<input type="text" class="form-control c-box--180" id="last_receipt_date_to" name="last_receipt_date_to" value="{{ isset($searchRow['last_receipt_date_to']) ? $searchRow['last_receipt_date_to'] : '' }}">
													<span class="input-group-addon">
														<span class="glyphicon glyphicon-calendar"></span>
													</span>
												</div>
											</div>
										</div>
									</td>
								</tr>
								<tr>
									<th class="c-box--150">メール未送信</th>
									<td class="tag-box">
										@foreach( $viewExtendData['m_mail_template_list'] as $mMailTemplate )
											<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="not_send_m_email_templates_id[]" id="not_send_m_email_templates_id[]" value="{{ $mMailTemplate['m_email_templates_id'] }}" @if( isset($searchRow['not_send_m_email_templates_id']) && in_array($mMailTemplate['m_email_templates_id'], $searchRow['not_send_m_email_templates_id']) ) checked @endif>{{ $mMailTemplate['m_email_templates_name'] }}</label>
										@endforeach
									</td>
								</tr>
								<tr>
									<th>見積</th>
									<td class="tag-box">
										<label><input type="checkbox" name="estimate_flg" id="estimate_flg" value="1" @if( isset($searchRow['estimate_flg']) && $searchRow['estimate_flg'] == 1 ) checked @endif></label>
									</td>
								</tr>
								<tr>
									<th>領収書</th>
									<td class="tag-box">
										@foreach( \App\Enums\ReceiptTypeEnum::cases() as $receiptFlg )
											<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="receipt_flg[]" id="receipt_flg[]" value="{{ $receiptFlg->value }}" @if( isset($searchRow['receipt_flg']) && in_array($receiptFlg->value, $searchRow['receipt_flg']) ) checked @endif>{{ $receiptFlg->label() }}</label>
										@endforeach
									</td>
								</tr>
								<tr>
									<th>キャンペーン</th>
									<td>
										<select class="form-control c-box--200" id="campaign_flg" name="campaign_flg">
											<option value=""></option>
											@foreach( $viewExtendData['m_campaign_list'] as $campaign )
												<option value="{{ $campaign['m_campaign_id'] }}" @if( isset($searchRow['campaign_flg']) && $searchRow['campaign_flg'] == $campaign['m_campaign_id'] ) selected @endif>{{ $campaign['campaign_name'] }}</option>
											@endforeach
										</select>
									</td>
								</tr>
								<tr>
									<th>販売窓口</th>
									<td>
										<select class="form-control c-box--200" id="sales_store" name="sales_store">
											<option value=""></option>
											@foreach( $viewExtendData['m_sales_counter_list'] as $sales_counter )
												<option value="{{ $sales_counter['m_itemname_types_id'] }}" @if( isset($searchRow['sales_store']) && $searchRow['sales_store'] == $sales_counter['m_itemname_types_id'] ) selected @endif>{{ $sales_counter['m_itemname_type_name'] }}</option>
											@endforeach
										</select>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<!-- 注文主 -->
				@if( $orderAcordion )
				<div class="c-btn--03"><a data-toggle="collapse" href="#collapse-sub-menu-3" aria-expanded="true">注文主</a></div>
				<div class="collapse in" id="collapse-sub-menu-3" aria-expanded="true" style="">
				@else
				<div class="c-btn--03"><a class="collapsed" data-toggle="collapse" href="#collapse-sub-menu-3" aria-expanded="false">注文主</a></div>
				<div class="collapse" id="collapse-sub-menu-3" aria-expanded="false" style="height: 0px;">
				@endif
					<div class="c-box--1600">
						<table class="table c-tbl c-tbl--1590">
							<tbody>
								<tr>
									<th class="c-box--180">顧客ランク</th>
									<td class="tag-box">
										@foreach($viewExtendData['cust_runk_list'] as $custRunk)
											<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="m_cust_runk_id[]" id="m_cust_runk_id[]" value="{{ $custRunk['m_itemname_types_id'] }}" @if( isset($searchRow['m_cust_runk_id']) && in_array($custRunk['m_itemname_types_id'], $searchRow['m_cust_runk_id']) ) checked @endif>{{ $custRunk['m_itemname_type_name'] }}</label>
										@endforeach
									</td>
								</tr>
								<tr>
									<th>顧客ID</th>
									<td>
										<input class="form-control c-box--200" type="text" id="m_cust_id" name="m_cust_id" value="{{ isset($searchRow['m_cust_id']) ? $searchRow['m_cust_id'] : '' }}">
									</td>
								</tr>
								<tr>
									<th>顧客コード</th>
									<td>
										<input class="form-control c-box--200" type="text" id="cust_cd" name="cust_cd" value="{{ isset($searchRow['cust_cd']) ? $searchRow['cust_cd'] : '' }}">
									</td>
								</tr>
								<tr>
									<th>Web会員番号</th>
									<td>
										<input class="form-control c-box--200" type="text" id="reserve10" name="reserve10" value="{{ isset($searchRow['reserve10']) ? $searchRow['reserve10'] : '' }}">
									</td>
								</tr>
								<tr>
									<th>要注意顧客区分</th>
									<td class="tag-box">
										@foreach( \App\Enums\AlertCustTypeEnum::cases() as $alertCustType )
											<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="alert_cust_type[]" id="alert_cust_type[]" value="{{ $alertCustType->value }}" @if( isset($searchRow['alert_cust_type']) && in_array($alertCustType->value, $searchRow['alert_cust_type']) ) checked @endif>{{ $alertCustType->label() }}</label>
										@endforeach
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<!-- 購入商品 -->
				@if( $pageAcordion )
				<div class="c-btn--03"><a data-toggle="collapse" href="#collapse-sub-menu-4" aria-expanded="true">購入商品</a></div>
				<div class="collapse in" id="collapse-sub-menu-4" aria-expanded="true" style="">
				@else
				<div class="c-btn--03"><a class="collapsed" data-toggle="collapse" href="#collapse-sub-menu-4" aria-expanded="false">購入商品</a></div>
				<div class="collapse" id="collapse-sub-menu-4" aria-expanded="false" style="height: 0px;">
				@endif
					<div class="c-box--1600">
						<table class="table c-tbl c-tbl--1590">
							<tbody>
								<tr>
									<th class="c-box--180">販売コード</th>
									<td><input class="form-control c-box--200" type="text" name="sell_cd" id="sell_cd" value="{{ isset($searchRow['sell_cd']) ? $searchRow['sell_cd'] : '' }}"></td>
								</tr>
								<tr>
									<th>販売名</th>
									<td><input class="form-control c-box--200" type="text" name="sell_name" id="sell_name" value="{{ isset($searchRow['sell_name']) ? $searchRow['sell_name'] : '' }}"></td>
								</tr>
								<tr>
									<th>項目選択肢</th>
									<td><input class="form-control c-box--200" type="text" name="sell_option" id="sell_option" value="{{ isset($searchRow['sell_option']) ? $searchRow['sell_option'] : '' }}"></td>
								</tr>
								<tr>
									<th>仕入先コード</th>
									<td><input class="form-control c-box--200" type="text" name="m_suppliers_id" id="m_suppliers_id" value="{{ isset($searchRow['m_suppliers_id']) ? $searchRow['m_suppliers_id'] : '' }}"></td>
								</tr>
								<tr>
									<th>商品コード</th>
									<td><input class="form-control c-box--200" type="text" name="item_cd" id="item_cd" value="{{ isset($searchRow['item_cd']) ? $searchRow['item_cd'] : '' }}"></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<!-- 決済 -->
				@if( $paymentAcordion )
				<div class="c-btn--03"><a data-toggle="collapse" href="#collapse-sub-menu-5" aria-expanded="true">決済</a></div>
				<div class="collapse in" id="collapse-sub-menu-5" aria-expanded="true" style="">
				@else
				<div class="c-btn--03"><a class="collapsed" data-toggle="collapse" href="#collapse-sub-menu-5" aria-expanded="false">決済</a></div>
				<div class="collapse" id="collapse-sub-menu-5" aria-expanded="false" style="height: 0px;">
				@endif
					<div class="c-box--800Half">
						<table class="table c-tbl c-tbl--790">
							<tbody>
								<tr>
									<th class="c-box--180">決済売上計上区分</th>
									<td class="tag-box">
										@foreach( $viewExtendData['settlement_sales_type_list'] as $settlementSalesTypeCode => $settlementSalesTypeName )
											<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="disp_settlement_sales_type[]" id="disp_settlement_sales_type[]" value="{{ $settlementSalesTypeCode }}" @if( isset($searchRow['disp_settlement_sales_type']) && in_array($settlementSalesTypeCode, $searchRow['disp_settlement_sales_type']) ) checked @endif>{{ $settlementSalesTypeName }}</label>
										@endforeach
									</td>
								</tr>
								<tr>
									<th>決済金額差異</th>
									<td class="tag-box">
										<label><input type="checkbox" id="payment_diff_flg" name="payment_diff_flg" value="1" @if( isset($searchRow['payment_diff_flg']) && $searchRow['payment_diff_flg'] == 1 ) checked @endif></label>
									</td>
								</tr>
								<tr>
									<th>入金日</th>
									<td>
										<div class="u-mt--xs d-table">
											<div class="c-box--218 d-table-cell">
												<div class="input-group date date-picker">
													<input type="text" class="form-control c-box--180" id="payment_date_from" name="payment_date_from" value="{{ isset($searchRow['payment_date_from']) ? $searchRow['payment_date_from'] : '' }}">
													<span class="input-group-addon">
														<span class="glyphicon glyphicon-calendar"></span>
													</span>
												</div>
											</div>
											<div class="d-table-cell">&nbsp;～&nbsp;</div>
											<div class="c-box--218 d-table-cell">
												<div class="input-group date date-picker">
													<input type="text" class="form-control c-box--180" id="payment_date_to" name="payment_date_to" value="{{ isset($searchRow['payment_date_to']) ? $searchRow['payment_date_to'] : '' }}">
													<span class="input-group-addon">
														<span class="glyphicon glyphicon-calendar"></span>
													</span>
												</div>
											</div>
										</div>
									</td>
								</tr>
								<tr>
									<th>入金金額</th>
									<td>
										<input class="form-control u-input--mid" type="text" id="payment_price_from" name="payment_price_from" value="{{ isset($searchRow['payment_price_from']) ? $searchRow['payment_price_from'] : '' }}">
										&nbsp;〜&nbsp;
										<input class="form-control u-input--mid" type="text" id="payment_price_to" name="payment_price_to" value="{{ isset($searchRow['payment_price_to']) ? $searchRow['payment_price_to'] : '' }}">
									</td>
								</tr>								
							</tbody>
						</table>
					</div>
					<div class="c-box--800Half">
						<table class="table c-tbl c-tbl--790">
							<tbody>
								<tr>
									<th class="c-box--180">後払い決済<br>取引ID</th>
									<td>
										<input class="form-control c-box--200" type="text" name="payment_transaction_id" id="payment_transaction_id" value="{{ isset($searchRow['payment_transaction_id']) ? $searchRow['payment_transaction_id'] : '' }}">
									</td>
								</tr>
								<tr>
									<th>
										後払い決済<br>決済ステータス
									</th>
									<td class="tag-box">
										@foreach( $viewExtendData['cb_credit_status_list'] as $cbCreditStatusCode => $cbCreditStatusName )
											<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="cb_credit_status[]" id="cb_credit_status[]" value="{{ $cbCreditStatusCode }}" @if( isset($searchRow['cb_credit_status']) && in_array($cbCreditStatusCode, $searchRow['cb_credit_status']) ) checked @endif>{{ $cbCreditStatusName }}</label>
										@endforeach
									</td>
								</tr>
								<tr>
									<th>
										後払い決済<br>出荷ステータス
									</th>
									<td class="tag-box">
										@foreach( $viewExtendData['cb_deli_status_list'] as $cbDeliStatusCode => $cbDeliStatusName )
											<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="cb_deli_status[]" id="cb_deli_status[]" value="{{ $cbDeliStatusCode }}" @if( isset($searchRow['cb_deli_status']) && in_array($cbDeliStatusCode, $searchRow['cb_deli_status']) ) checked @endif>{{ $cbDeliStatusName }}</label>
										@endforeach
									</td>
								</tr>
								<tr>
									<th>
										後払い決済<br>請求書送付ステータス
									</th>
									<td class="tag-box">
										@foreach( $viewExtendData['cb_billed_status_list'] as $cbBilledStatusCode => $cbBilledStatusName )
											<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="cb_billed_status[]" id="cb_billed_status[]" value="{{ $cbBilledStatusCode }}" @if( isset($searchRow['cb_billed_status']) && in_array($cbBilledStatusCode, $searchRow['cb_billed_status']) ) checked @endif>{{ $cbBilledStatusName }}</label>
										@endforeach
									</td>
								</tr>
								<tr>
									<th>後払い決済<br>請求書送付種別</th>
									<td class="tag-box">
										@foreach( $viewExtendData['cb_billed_type_list'] as $cbBilledTypeCode => $cbBilledTypeName )
											<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="cb_billed_type[]" id="cb_billed_type[]" value="{{ $cbBilledTypeCode }}" @if( isset($searchRow['cb_billed_type']) && in_array($cbBilledTypeCode, $searchRow['cb_billed_type']) ) checked @endif>{{ $cbBilledTypeName }}</label>
										@endforeach
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<!-- 配送情報 -->
				@if( $deliveryAcordion )
				<div class="c-btn--03"><a data-toggle="collapse" href="#collapse-sub-menu-6" aria-expanded="true">配送情報</a></div>
				<div class="collapse in" id="collapse-sub-menu-6" aria-expanded="true" style="">
				@else
				<div class="c-btn--03"><a class="collapsed" data-toggle="collapse" href="#collapse-sub-menu-6" aria-expanded="false">配送情報</a></div>
				<div class="collapse" id="collapse-sub-menu-6" aria-expanded="false" style="height: 0px;">
				@endif
					<div class="c-box--800Half">
						<table class="table c-tbl c-tbl--790">
							<tbody>
								<tr>
									<th class="c-box--180">配送ID</th>
									<td>
										<input class="form-control" type="text" name="t_deli_hdr_id" id="t_deli_hdr_id" value="{{ isset($searchRow['t_deli_hdr_id']) ? $searchRow['t_deli_hdr_id'] : '' }}">
									</td>
								</tr>
								<tr>
									<th>配送方法</th>
									<td class="tag-box">
										@foreach( $viewExtendData['delivery_type_list'] as $deliveryType )
											<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="m_delivery_types_id[]" id="m_delivery_types_id[]" value="{{ $deliveryType['m_delivery_types_id'] }}" @if( isset($searchRow['m_delivery_types_id']) && in_array($deliveryType['m_delivery_types_id'], $searchRow['m_delivery_types_id']) ) checked @endif>{{ $deliveryType['m_delivery_type_name'] }}</label>
										@endforeach
									</td>
								</tr>
								<tr>
									<th>注文・送付先不一致</th>
									<td class="tag-box"><label><input type="checkbox" id="destination_alter_flg" name="destination_alter_flg" value="1" @if( isset($searchRow['destination_alter_flg']) && $searchRow['destination_alter_flg'] == 1 ) checked @endif></label></td>
								</tr>
								<tr>
									<th>送付先都道府県</th>
									<td class="tag-box">
										@foreach( $viewExtendData['prefectural_list'] as $prefectural )
											<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="destination_address1[]" id="destination_address1[]" value="{{$prefectural['prefectual_name']}}" @if( isset($searchRow['destination_address1']) && in_array($prefectural['prefectual_name'], $searchRow['destination_address1']) ) checked @endif>{{ $prefectural['prefectual_name'] }}</label>
										@endforeach
									</td>
								</tr>
								<tr>
									<th>複数配送先</th>
									<td class="tag-box">
										<label><input type="checkbox" id="multiple_deli_flg" name="multiple_deli_flg" value="1" @if( isset($searchRow['multiple_deli_flg']) && $searchRow['multiple_deli_flg'] == 1 ) checked @endif></label>
									</td>
								</tr>
								<tr>
									<th>送り状番号</th>
									<td>
										<input class="form-control" type="text" id="invoice_num1" name="invoice_num1" value="{{ isset($searchRow['invoice_num1']) ? $searchRow['invoice_num1'] : '' }}">
									</td>
								</tr>
								<tr>
									<th>配送日の有無</th>
									<td class="tag-box">
										<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" value="1" id="deli_decision_date_flg[]" name="deli_decision_date_flg[]" @if( isset($searchRow['deli_decision_date_flg']) && in_array(1, $searchRow['deli_decision_date_flg']) ) checked @endif>有</label>
										<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" value="0" id="deli_decision_date_flg[]" name="deli_decision_date_flg[]" @if( isset($searchRow['deli_decision_date_flg']) && in_array(0, $searchRow['deli_decision_date_flg']) ) checked @endif>無</label>
									</td>
								</tr>
								<tr>
									<th>配送日</th>
									<td>
										<div class="u-mt--xs d-table">
											<div class="c-box--218 d-table-cell">
												<div class="input-group date date-picker">
													<input type="text" class="form-control c-box--180" id="deli_decision_date_from" name="deli_decision_date_from" value="{{ isset($searchRow['deli_decision_date_from']) ? $searchRow['deli_decision_date_from'] : '' }}">
													<span class="input-group-addon">
														<span class="glyphicon glyphicon-calendar"></span>
													</span>
												</div>
											</div>
											<div class="d-table-cell">&nbsp;～&nbsp;</div>
											<div class="c-box--218 d-table-cell">
												<div class="input-group date date-picker">
													<input type="text" class="form-control c-box--180" id="deli_decision_date_to" name="deli_decision_date_to" value="{{ isset($searchRow['deli_decision_date_to']) ? $searchRow['deli_decision_date_to'] : '' }}">
													<span class="input-group-addon">
														<span class="glyphicon glyphicon-calendar"></span>
													</span>
												</div>
											</div>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="c-box--800Half">
						<table class="table c-tbl c-tbl--790">
							<tbody>
								<tr>
									<th class="c-box--180">配送希望日</th>
									<td>
										<div class="u-mt--xs d-table">
											<div class="c-box--218 d-table-cell">
												<div class="input-group date date-picker">
													<input type="text" class="form-control c-box--180" id="deli_hope_date_from" name="deli_hope_date_from" value="{{ isset($searchRow['deli_hope_date_from']) ? $searchRow['deli_hope_date_from'] : '' }}">
													<span class="input-group-addon">
														<span class="glyphicon glyphicon-calendar"></span>
													</span>
												</div>
											</div>
											<div class="d-table-cell">&nbsp;～&nbsp;</div>
											<div class="c-box--218 d-table-cell">
												<div class="input-group date date-picker">
													<input type="text" class="form-control c-box--180" id="deli_hope_date_to" name="deli_hope_date_to" value="{{ isset($searchRow['deli_hope_date_to']) ? $searchRow['deli_hope_date_to'] : '' }}">
													<span class="input-group-addon">
														<span class="glyphicon glyphicon-calendar"></span>
													</span>
												</div>
											</div>
										</div>
									</td>								
								</tr>
								<tr>
									<th>配送希望日なし</th>
									<td class="tag-box">
										<label><input type="checkbox" id="deli_hope_date_nothing_flg" name="deli_hope_date_nothing_flg" value="1" @if( isset($searchRow['deli_hope_date_nothing_flg']) && $searchRow['deli_hope_date_nothing_flg'] == 1 ) checked @endif></label>
									</td>
								</tr>
								<tr>
									<th>配送希望時間帯</th>
									<td class="tag-box">
										<select class="form-control c-box--200" id="deli_hope_time_cd" name="deli_hope_time_cd">
											<option value=""></option>
											@foreach($viewExtendData['delivery_hope_timezone_list'] as $deliHopeTimezone)
												<option value="{{ $deliHopeTimezone['delivery_time_hope_cd'] }}" @if( isset($searchRow['deli_hope_time_cd']) && $searchRow['deli_hope_time_cd'] == $deliHopeTimezone['delivery_time_hope_cd'] ) selected @endif>{{ $deliHopeTimezone['delivery_time_hope_name'] }}</option>
											@endforeach
										</select>
									</td>
								</tr>
								<tr>
									<th>出荷予定日なし</th>
									<td class="tag-box">
										<label><input type="checkbox" id="deli_plan_date_nothing_flg" name="deli_plan_date_nothing_flg" value="1" @if( isset($searchRow['deli_plan_date_nothing_flg']) && $searchRow['deli_plan_date_nothing_flg'] == 1 ) checked @endif></label>
									</td>
								</tr>
								<tr>
									<th>配送先氏名・カナ氏名</th>
									<td class="tag-box">
										<input class="form-control" type="text" id="destination_name" name="destination_name" value="{{ isset($searchRow['destination_name']) ? $searchRow['destination_name'] : '' }}">
									</td>
								</tr>
								<tr>
									<th>送り状コメントの有無</th>
									<td class="tag-box">
										<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" id="invoice_comment_flg[]" name="invoice_comment_flg[]" value="1" @if( isset($searchRow['invoice_comment_flg']) && in_array(1, $searchRow['invoice_comment_flg']) ) checked @endif>有</label>
										<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" id="invoice_comment_flg[]" name="invoice_comment_flg[]" value="0" @if( isset($searchRow['invoice_comment_flg']) && in_array(0, $searchRow['invoice_comment_flg']) ) checked @endif>無</label>
									</td>
								</tr>
								<tr>
									<th>送り状コメント</th>
									<td>
										<textarea name="invoice_comment" id="invoice_comment" cols="20" rows="3" class="form-control c-box--400">{{ isset($searchRow['invoice_comment']) ? $searchRow['invoice_comment'] : '' }}</textarea>
									</td>
								</tr>
								<tr>
									<th>ピッキングコメント</th>
									<td>
										<textarea name="picking_comment" id="picking_comment" cols="20" rows="3" class="form-control c-box--400">{{ isset($searchRow['picking_comment']) ? $searchRow['picking_comment'] : '' }}</textarea>
									</td>
								</tr>
								<tr>
									<th>ピッキングコメントの有無</th>
									<td class="tag-box">
										<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" id="picking_comment_flg[]" name="picking_comment_flg[]" value="1" @if( isset($searchRow['picking_comment_flg']) && in_array(1, $searchRow['picking_comment_flg']) ) checked @endif>有</label>
										<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" id="picking_comment_flg[]" name="picking_comment_flg[]" value="0" @if( isset($searchRow['picking_comment_flg']) && in_array(0, $searchRow['picking_comment_flg']) ) checked @endif>無</label>
									</td>
								</tr>
								<tr>
									<th>配送倉庫</th>
									<td class="tag-box">
										@foreach( $viewExtendData['m_warehouse_list'] as $mWarehouse )
											<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="m_warehouse_id[]" id="m_warehouse_id[]" value="{{ $mWarehouse['m_warehouses_id'] }}" @if( isset($searchRow['m_warehouse_id']) && in_array($mWarehouse['m_warehouses_id'], $searchRow['m_warehouse_id']) ) checked @endif>{{ $mWarehouse['m_warehouse_name'] }}</label>
										@endforeach
									</td>
								</tr>
								<tr>
									<th>倉庫引当区分</th>
									<td class="tag-box">
										<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" id="multi_warehouse_flg[]" name="multi_warehouse_flg[]" value="0" @if( isset($searchRow['multi_warehouse_flg']) && in_array(0, $searchRow['multi_warehouse_flg']) ) checked @endif>単一倉庫引当</label>
										<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" id="multi_warehouse_flg[]" name="multi_warehouse_flg[]" value="1" @if( isset($searchRow['multi_warehouse_flg']) && in_array(1, $searchRow['multi_warehouse_flg']) ) checked @endif>複数倉庫引当</label>
									</td>
								</tr>
								<tr>
									<th>温度帯</th>
									<td class="tag-box">
										@foreach( \App\Enums\ThreeTemperatureZoneTypeEnum::cases() as $temperatureZoneType )
											<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" name="temperature_zone[]" id="temperature_zone[]" value="{{ $temperatureZoneType->value }}" @if( isset($searchRow['temperature_zone']) && in_array($temperatureZoneType->value, $searchRow['temperature_zone']) ) checked @endif>{{ $temperatureZoneType->label() }}</label>
										@endforeach
									</td>
								</tr>
								<tr>
									<th>メーカー直送</th>
									<td class="tag-box">
										<label><input type="checkbox" name="direct_deli_flg" id="direct_deli_flg" value="1" @if( isset($searchRow['direct_deli_flg']) && $searchRow['direct_deli_flg'] == 1 ) checked @endif></label>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="c-box--1600 u-mt--xs">
		<div id="line-01"></div>
		<p class="c-ttl--02">検索と新規受注</p>

		<table class="table table-bordered c-tbl c-tbl--1600 nowrap">
			<tbody>
				<tr>
					<th>受注取込</th>
				</tr>
				<tr>
					<td>
						<div class="u-mt--sm">
							形式選択：<select class="form-control u-input--mid u-mr--xs" name="input_order_csv_type" id="input_order_csv_type">
								@foreach( $viewExtendData['input_order_csv_type'] as $ecTypeValue => $ecTypeName )
									<option value="{{ $ecTypeValue }}">{{ $ecTypeName }}</option>
								@endforeach
							</select>
							<select class="form-control u-input--mid u-mr--xs" name="input_order_csv_shop" id="input_order_csv_shop" style="display: none">
								@foreach( $viewExtendData['input_order_csv_shop'] as $m_ecs_id => $m_ec_name )
									<option value="{{ $m_ecs_id ?? '' }}">{{ $m_ec_name ?? '' }}</option>
								@endforeach
							</select>
						</div>
						<div class="u-mt--sm">
							<input type="file" class="u-ib" name="input_order_csv_file" name="input_order_csv_file" form="Form1">
							<button class="btn btn-default" type="submit" name="submit" id="submit_input_order_csv" value="input_order_csv">CSV取込</button>
						</div>
						@include('order.list_input_error_tag', ['name' => 'input_order_csv'])
					</td>
				</tr>
			</tbody>
		</table>
		<button class="btn btn-success btn-lg u-mt--sm u-mr--xs" type="submit" name="submit" value="search" id="button_search">検索</button>
		{{-- <button class="btn btn-default btn-lg u-mt--sm u-mr--xs" type="button" name="new" onClick="location.href='./../new'">新規受注登録</button> --}}
		<button class="btn btn-default btn-lg u-mt--sm u-mr--xs" type="submit" name="submit" value="search_clear" id="button_search_clear">検索条件クリア</button>
		<button class="btn btn-default btn-lg u-mt--sm u-mr--xs" type="button" name="disp" onClick="window.open('/master/order_list_disps/edit/{{ session()->get('OperatorInfo')['m_operators_id'] }}');">表示項目設定</button>
	</div>

	<div id="search_results">
		@if($paginator)
			@include('order.gfh_1207.list_search')
		@endif
	</div>

	<div class="u-mt--sl c-box--1600">
		<div id="line-04"></div>
		<p class="c-ttl--02">各種操作</p>
		<table class="table c-tbl c-tbl--1200">
			<tbody>
				<tr>
					<th class="c-box--200">操作対象</th>
					<td>
						<label class="radio-inline"><input type="radio" id="bulk_target_type" name="bulk_target_type" value="1" @if( !isset($searchRow['bulk_target_type']) || empty($searchRow['bulk_target_type']) || $searchRow['bulk_target_type'] == 1 ) checked @endif> 選択データを対象</label>
						<label class="radio-inline"><input type="radio" id="bulk_target_type" name="bulk_target_type" value="2" @if( isset($searchRow['bulk_target_type']) && $searchRow['bulk_target_type'] == 2 ) checked @endif> 検索データを対象</label>
					</td>
				</tr>
				<tr>
					<th class="c-box--200">進捗区分変更</th>
					<td>
						<select class="form-control u-input--long u-mr--xs" id="set_change_progress_type" name="set_change_progress_type">
							@foreach (\App\Enums\ProgressTypeEnum::cases() as $target)
								@if( $target->value != \App\Enums\ProgressTypeEnum::Cancelled->value && $target->value != \App\Enums\ProgressTypeEnum::Returned->value )
									<option value="{{ $target->value }}">{{ $target->label() }}</option>
								@endif
			                @endforeach
						</select>
						<button class="btn btn-default" type="submit" name="submit" value="change_progress">変更</button>
						@include('order.list_output_error_tag', ['name' => 'change_progress'])
					</td>
				</tr>
				@if( !empty($viewExtendData['m_mail_template_list']) )
					<tr>
						<th class="c-box--200">メール送信</th>
						<td>
							<div class="d-inline-block u-pr--ms">
								<div class="d-table u-mt--ss">
									<div class="d-table-cell">
										<select class="form-control u-mr--xs" id="send_email_templates_id" name="send_email_templates_id">
											@foreach( $viewExtendData['m_mail_template_list'] as $sendMailTemplate )
												<option value="{{ $sendMailTemplate['m_email_templates_id'] }}" @if( isset($searchRow['send_email_templates_id']) && $searchRow['send_email_templates_id'] == $sendMailTemplate['m_email_templates_id'] ) selected @endif>{{ $sendMailTemplate['m_email_templates_name'] }}</option>
											@endforeach
										</select>
									</div>
									<div class="d-table-cell">
										<button class="btn btn-default u-mr--xs" type="submit" name="submit" value="send_template_mail">送信</button>
									</div>
									@if( ( isset($viewExtendData['shop_list'][0]['receipt_batch_tramission']) ? $viewExtendData['shop_list'][0]['receipt_batch_tramission'] : 0 ) == 1 )
										<div class="d-table-cell">
											<button class="btn btn-default u-mr--xs" type="submit" name="submit" value="new_send_recipt_mail">領収書メール送信</button>
											&nbsp;&nbsp;&nbsp;&nbsp;再発行
										</div>
										<div class="d-table-cell">
											<input type="checkbox" name="reissue"  value="1" id="reissue">
										</div>
									@endif
								</div>
							</div><!-- /d-inline-block -->
							@include('order.list_output_error_tag', ['name' => 'send_template_mail'])
							@include('order.list_output_error_tag', ['name' => 'new_send_recipt_mail'])
						</td>
					</tr>
				@endif
			
				@if( !empty($viewExtendData['payment_paytype_list']) )
					<tr>
						<th class="c-box--200">入金</th>
						<td>
							<div>
								<select class="form-control u-input--long u-mr--xs" id="payment_paytype_id" name="payment_paytype_id">
									@foreach( $viewExtendData['payment_paytype_list'] as $paymentType )
										<option value="{{ $paymentType['m_itemname_types_id'] }}" @if( isset($searchRow['payment_paytype_id']) && $searchRow['payment_paytype_id'] == $paymentType['m_itemname_types_id'] ) selected @endif>{{ $paymentType['m_itemname_type_name'] }}</option>
									@endforeach
								</select>
							</div>
							<div class="d-inline-block u-pr--ms">
								<div class="d-table u-mt--ss">
									<span class="u-pr--ss d-table-cell u-vam">顧客入金日</span>
									<div class="c-box--218">
										<div class="input-group date date-picker">
											<input type="text" class="form-control c-box--180" id="set_cust_payment_date" name="set_cust_payment_date">
											<span class="input-group-addon">
												<span class="glyphicon glyphicon-calendar"></span>
											</span>
										</div>
									</div>
								</div><!-- /d-table -->
							</div><!-- /d-inline-block -->
							<div class="d-inline-block">
								<div class="d-table">
									<span class="u-pr--ss d-table-cell u-vam">口座入金日</span>
									<div class="c-box--218">
										<div class="input-group date date-picker">
											<input type="text" class="form-control c-box--180" id="set_account_payment_date" name="set_account_payment_date">
											<span class="input-group-addon">
												<span class="glyphicon glyphicon-calendar"></span>
											</span>
										</div>
									</div>
								</div><!-- /d-table -->
							</div><!-- /d-inline-block -->
							<div>
								<button class="btn btn-default" type="submit" name="submit" value="payment">全額入金</button>
							</div>
							@include('order.list_output_error_tag', ['name' => 'payment'])
						</td>
					</tr>
				@endif				
				<tr>
					<th class="c-box--200">在庫引当</th>
					<td>
						@include('order.list_output_error_tag', ['name' => 'reserve_stock'])
						<button class="btn btn-default" type="submit" name="submit" value="reserve_stock">引当する</button>
					</td>
				</tr>
				<tr>
					<th class="c-box--200">配送方法</th>
					<td>
						<select class="form-control u-input--long u-mr--xs" id="set_change_delivery_type" name="set_change_delivery_type">
							@foreach( $viewExtendData['delivery_type_list'] as $deliveryType )
								<option value="{{ $deliveryType['m_delivery_types_id'] }}">{{ $deliveryType['m_delivery_type_name'] }}</option>
							@endforeach
						</select>
						<button class="btn btn-default" type="submit" name="submit" value="change_delivery_type">変更</button>
						@include('order.list_output_error_tag', ['name' => 'change_delivery_type'])
					</td>
				</tr>
				<tr>
					<th class="c-box--200">配送希望日</th>
					<td>
						<div class="d-table">
							<div class="c-box--218 d-table-cell u-pr--ss">
								<div class="input-group date date-picker">
									<input type="text" class="form-control c-box--180" id="set_deli_hope_date" name="set_deli_hope_date">
									<span class="input-group-addon">
										<span class="glyphicon glyphicon-calendar"></span>
									</span>
								</div>
							</div>
							<button class="btn btn-default" type="submit" name="submit" value="change_deli_hope_date">変更</button>
						</div>
						@include('order.list_output_error_tag', ['name' => 'change_deli_hope_date'])
					</td>
				</tr>
				<tr>
					<th class="c-box--200">出荷予定日</th>
					<td>
						<div class="d-table">
							<div class="c-box--218 d-table-cell u-pr--ss">
								<div class="input-group date date-picker">
									<input type="text" class="form-control c-box--180" id="set_deli_plan_date" name="set_deli_plan_date">
									<span class="input-group-addon">
										<span class="glyphicon glyphicon-calendar"></span>
									</span>
								</div>
							</div>
							<button class="btn btn-default" type="submit" name="submit" value="change_deli_plan_date">変更</button>
						</div>
						@include('order.list_output_error_tag', ['name' => 'change_deli_plan_date'])
					</td>
				</tr>
			</tbody>
		</table>

		<table class="table c-tbl c-tbl--1600">
			<tbody>
				<tr>
					<th class="c-box--200">社内メモ</th>
					<td>
						<div><textarea name="set_operator_comment" id="set_operator_comment" cols="30" rows="3" class="form-control c-box--300"></textarea></div>
						<div class="u-mt--ss">
							<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" id="add_operator_comment_flg" name="add_operator_comment_flg" value="1">追記</label>
							<button class="btn btn-default" type="submit" name="submit" value="change_operator_comment">変更</button>
						</div>
						@include('order.list_output_error_tag', ['name' => 'change_operator_comment'])
					</td>
				</tr>
			</tbody>
		</table>

		<table class="table c-tbl c-tbl--1200">
			<tbody>
				<tr>
					<th class="c-box--200">タグをつける</th>
					<td>
						<select class="form-control u-input--long u-mr--xs" id="set_add_tag_id" name="set_add_tag_id">
							@foreach($viewExtendData['m_tag_list'] as $masterTag)
								<option value="{{ $masterTag['m_order_tag_id'] }}">{{ $masterTag['tag_display_name'] }}</option>
							@endforeach
						</select>
						<button class="btn btn-default" type="submit" name="submit" value="add_order_tag">つける</button>
						@include('order.list_output_error_tag', ['name' => 'add_order_tag'])
					</td>
				</tr>
				<tr>
					<th class="c-box--200">タグをはずす</th>
					<td>
						<select class="form-control u-input--long u-mr--xs" id="set_remove_tag_id" name="set_remove_tag_id">
							@foreach($viewExtendData['m_tag_list'] as $masterTag)
								<option value="{{ $masterTag['m_order_tag_id'] }}">{{ $masterTag['tag_display_name'] }}</option>
							@endforeach
						</select>
						<button class="btn btn-default" type="submit" name="submit" value="remove_order_tag">はずす</button>
						@include('order.list_output_error_tag', ['name' => 'remove_order_tag'])
					</td>
				</tr>
			</tbody>
		</table>

		<table class="table c-tbl c-tbl--1600">
			<tbody>
				<tr>
					<th class="c-box--200">与信データ出力</th>
					<td>
						<select class="form-control u-input--long u-mr--xs" id="output_payment_auth_csv_type" name="output_payment_auth_csv_type">
							@foreach( $viewExtendData['output_payment_auth_csv_list'] as $outputPaymentAuthCsvCode => $outputPaymentAuthCsvName )
								<option value="{{ $outputPaymentAuthCsvCode }}">{{ $outputPaymentAuthCsvName }}</option>
							@endforeach
						</select>
						<button class="btn btn-default" type="submit" name="submit" value="output_payment_auth_csv">出力</button>
						@include('order.list_output_error_tag', ['name' => 'output_payment_auth_csv'])
					</td>
				</tr>
				<tr>
					<th class="c-box--200">与信結果取込</th>
					<td>
						<select class="form-control u-input--long u-mr--xs" id="input_payment_auth_csv_type" name="input_payment_auth_csv_type">
							@foreach( $viewExtendData['input_payment_auth_csv_list'] as $inputPaymentAuthCsvCode => $inputPaymentAuthCsvName )
								<option value="{{ $inputPaymentAuthCsvCode }}">{{ $inputPaymentAuthCsvName }}</option>
							@endforeach
						</select>
						<input type="file" class="u-ib" id="input_payment_csv_file" name="input_payment_auth_csv_file" form="Form1">
						<button class="btn btn-default" type="submit" name="submit" value="input_payment_auth_csv">CSV取込</button>
						@include('order.list_input_error_tag', ['name' => 'input_payment_auth_csv'])
					</td>
				</tr>
				<tr>
				<th class="c-box--200">出荷報告データ出力</th>
					<td>
						<select class="form-control u-input--long u-mr--xs" id="output_payment_delivery_result_csv_type" name="output_payment_delivery_result_csv_type">
							@foreach( $viewExtendData['output_payment_delivery_result_csv_list'] as $outputPaymentDeliveryCsvCode => $outputPaymentDeliveryCsvName )
								<option value="{{ $outputPaymentDeliveryCsvCode }}">{{ $outputPaymentDeliveryCsvName }}</option>
							@endforeach
						</select>
						<button class="btn btn-default" type="submit" name="submit" value="output_payment_delivery_result_csv">出力</button>
						@include('order.list_output_error_tag', ['name' => 'output_payment_delivery_result_csv'])
					</td>
				</tr>
			</tbody>
		</table>

		<table class="table c-tbl c-tbl--1200">
			<tbody>
				<tr>
					<th class="c-box--200">入金取込</th>
					<td>
						<div>
							<select class="form-control u-input--long u-mr--xs" id="input_payment_csv_filetype" name="input_payment_csv_filetype">
								@foreach( $viewExtendData['input_payment_csv_filetype_list'] as $inputPaymentCsvCode => $inputPaymentCsvName )
									<option value="{{ $inputPaymentCsvCode }}">{{ $inputPaymentCsvName }}</option>
								@endforeach
							</select>
							<input type="file" class="u-ib" name="input_payment_csv_file" id="input_payment_csv_file" form="Form1">
							<button class="btn btn-default" type="submit" name="submit" value="input_payment_result_csv">CSV取込</button>
							@include('order.list_input_error_tag', ['name' => 'input_payment_result_csv'])
						</div>
					</td>
				</tr>
			</tbody>
		</table>

		<table class="table c-tbl c-tbl--1200">
			<tbody>
				<tr>
					<th class="c-box--200">出荷帳票・データ出力</th>
					<td>
						<table>
							<tr>
								<td class="c-box--130 u-p--xs">出力対象倉庫</td>
								<td class="u-p--xs">
									<select class="form-control u-input--long u-mr--xs" name="output_warehouse_id" id="output_warehouse_id">
										@foreach( $viewExtendData['m_warehouse_list'] as $mWarehouse2 )
											<option value="{{ $mWarehouse2['m_warehouses_id'] }}" {{ isset($viewExtendData['output_warehouse_id'][$mWarehouse['m_warehouses_id']]) ? $viewExtendData['output_warehouse_id'][$mWarehouse['m_warehouses_id']] : '' }}>
												{{ $mWarehouse2['m_warehouse_name'] }}@if( $mWarehouse2['m_warehouse_type'] == 3 )(L-Spark倉庫)@endif
											</option>
										@endforeach
									</select>
								</td>
							</tr>
							<tr>
								<td class="c-box--130 u-p--xs">出力PDF種類</td>
								<td class="u-p--xs">
									@foreach($viewExtendData['output_pdf_list'] as $outputPdfType => $outputPdfName)
									<label class="checkbox-inline u-ma--5-10-5-5"><input type="checkbox" class="checkbox-inline" name="output_queue_report[]" id="output_queue_report[]" value="{{$outputPdfType}}">{{$outputPdfName}}</label>
									@endforeach
								</td>
							</tr>
							<tr>
								<td class="c-box--130 u-p--xs">送り状データ種類</td>
								<td class="u-p--xs">
									<select class="form-control u-input--long u-mr--xs" name="output_queue_delivery" id="output_queue_delivery">
										<option value=""></option>
										@foreach( $viewExtendData['output_delivery_csv_list'] as $outputDeliveryCsv )
											<option value="{{ $outputDeliveryCsv['invoice_system_cd'] }}">{{ $outputDeliveryCsv['invoice_system_name'] }}</option>
										@endforeach
									</select>
								</td>
							</tr>
							<tr>
								<td class="u-p--xs" colspan="2">
									<button class="btn btn-default" type="submit" name="submit" value="output_delivery_file">出力</button>
									<button class="btn btn-default" type="submit" name="submit" value="re_output_delivery_file">再出力</button>
								</td>
							</tr>
						</table>
						@include('order.list_output_error_tag', ['name' => 'output_delivery_csv'])
						@include('order.list_output_error_tag', ['name' => 'output_pdf'])
						@include('order.list_output_error_tag', ['name' => 'output_delivery_file'])
						@include('order.list_output_error_tag', ['name' => 're_output_delivery_file'])
					</td>
				</tr>
			</tbody>
		</table>

		<table class="table c-tbl c-tbl--1200">
			<tbody>
				<tr>
					<th class="c-box--200">出荷取込</th>
					<td>
						<select class="form-control u-input--long u-mr--xs" name="input_queue_delivery" id="input_queue_delivery">
							@foreach( $viewExtendData['input_delivery_csv_list'] as $inputDeliveryCsv )
								<option value="{{ $inputDeliveryCsv['invoice_system_cd'] }}">{{ $inputDeliveryCsv['invoice_system_name'] }}</option>
							@endforeach
						</select>
						<input type="file" class="u-ib" name="input_delivery_csv_file" id="input_delivery_csv_file" form="Form1">
						<button class="btn btn-default" type="submit" name="submit" value="input_delivery_csv">CSV取込</button>
						@include('order.list_input_error_tag', ['name' => 'input_delivery_csv'])
					</td>
				</tr>
			</tbody>
		</table>

		<table class="table c-tbl c-tbl--1200">
			<tbody>
				<tr>
					<th class="c-box--200">Amazon TSV受注取込</th>
					<td>
						<select class="form-control u-input--long u-mr--xs" name="input_ec_order_csv_type" id="input_ec_order_csv_type">
							@foreach( $viewExtendData['ec_list'] as $ec2 )
								@if( in_array( $ec2['m_ec_type'], config('define.input_ec_order_csv_type') ) )
									<option value="{{$ec2['m_ecs_id']}}">{{ $ec2['m_ec_name'] }}</option>
								@endif
							@endforeach
						</select>
						<input type="file" class="u-ib" name="input_ec_order_csv_file" id="input_ec_order_csv_file" form="Form1">
						<button class="btn btn-default" type="submit" name="submit" value="input_ec_order_file">ファイル取込</button>
						@include('order.list_output_error_tag', ['name' => 'input_ec_order_file'])
					</td>
				</tr>
				<tr>
					<th class="c-box--200">受注データ出力</th>
					<td>
						<select class="form-control u-input--long u-mr--xs" name="output_order_csv_type" id="output_order_csv_type">
							@foreach($viewExtendData['output_order_csv_list'] as $outputOrderCsvValue => $outputOrderCsvName)
							<option value={{$outputOrderCsvValue}}>{{$outputOrderCsvName}}</option>
							@endforeach
						</select>
						<button class="btn btn-default" type="submit" name="submit" value="output_order_file">出力</button>
						@include('order.list_output_error_tag', ['name' => 'output_order_file'])
					</td>
				</tr>
				<tr>
					<th class="c-box--200">受注一括編集データ取込</th>
					<td>
						<input type="file" class="u-ib" name="input_order_update_file" id="input_order_update_file" form="Form1">
						<button class="btn btn-default" type="submit" name="submit" value="input_order_update_csv">CSV取込</button>
						@include('order.list_input_error_tag', ['name' => 'input_order_update_csv'])
					</td>
				</tr>
			</tbody>
		</table>		
	</div>

	@include('common.elements.sorting_script')
	@include('common.elements.datetime_picker_script')
	@include('common.elements.on_enter_script', ['target_button_name' => 'button_search'])
</div>
<p id="float_search_wrapper" class="footer-button-p" style="right:70px;"><a id="float_search" class="footer-button-a" style="color: #fff;">検索</a></p>
<p id="float_clear_wrapper" class="footer-button-p" style="right:155px;"><a id="float_clear" class="footer-button-a" style="color: #fff;">条件クリア</a></p>
</form>

@push('css')
<link rel="stylesheet" href="{{ esm_internal_asset('css/order/gfh_1207/app.css') }}">
@endpush

@push('js')
<script src="{{ esm_internal_asset('js/order/gfh_1207/app.js') }}"></script>
@endpush
@endsection