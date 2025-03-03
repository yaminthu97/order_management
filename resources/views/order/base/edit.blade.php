{{-- NEOSM0211:受注登録・修正 --}}
{{-- 画面設定 --}}
@php
$ScreenCd='NEOSM0211';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '受注登録・修正')

@section('csrf-token', "{{ csrf_token() }}")

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>受注登録・修正</li>
@endsection

@section('content')
<link rel="stylesheet" href="{{config('env.app_subsys_url.order')}}css/Order/v1_0/NEOSM0211.css">

<form method="post" name="Form1" action="">
	{{ csrf_field() }}
	<input type="hidden" name="previous_url" value="{{ $editRow['previous_url'] ?? '' }}">
	<input type="hidden" name="previous_subsys" value="{{ $editRow['previous_subsys'] ?? '' }}">
	<input type="hidden" name="previous_key" value="{{ $editRow['previous_key'] ?? '' }}">
	<input type="hidden" name="sell_find_index" id="sell_find_index" value="{{ $editRow['sell_find_index'] ?? '' }}" data-href="{{config('env.app_subsys_url.order')}}order/sales">
	<input type="hidden" name="progress_type" value="{{ $editRow['progress_type'] ?? '' }}">
	<input type="hidden" name="scroll_top" value="" class="st">
	<input type="hidden" name="sales_param" id="sales_param" value="{{ $editRow['sales_param'] ?? '' }}">
	<input type="hidden" name="return_flg" value="{{ $editRow['return_flg'] ?? '' }}">
	<div class="c-box--1200">
		@isset($editRow['message'])
		<div class="c-box--full">
			<span class="font-FF0000">{{$editRow['message'] ?? ''}}</span>
		</div>
		@endisset
		@include('common.elements.error_tag', ['name' => 'other_error'])
		@unless(empty($editRow['t_order_hdr_id']))
		<div id="line-01"></div>
		<div class="u-mt--ss">
			<p class="c-ttl--02">ステータス</p>
		</div>
		<div class="d-inline-block">
			進捗状況
			<ol class="stepBar step9">
				<li class="step @if($editRow['progress_type'] == 0){{'current'}}@endif">確認待</li>
				<li class="step @if($editRow['progress_type'] == 10){{'current'}}@endif">与信待</li>
				<li class="step @if($editRow['progress_type'] == 20){{'current'}}@endif">前払入金待</li>
				<li class="step @if($editRow['progress_type'] == 30){{'current'}}@endif">引当待</li>
				<li class="step @if($editRow['progress_type'] == 40){{'current'}}@endif">出荷待</li>
				<li class="step @if($editRow['progress_type'] == 50){{'current'}}@endif">出荷中</li>
				<li class="step @if($editRow['progress_type'] == 60){{'current'}}@endif">出荷済み</li>
				<li class="step @if($editRow['progress_type'] == 70){{'current'}}@endif">後払入金待</li>
				<li class="step @if($editRow['progress_type'] == 80){{'current'}}@endif">完了</li>
			</ol>
		</div>
		<div class="d-inline-block" style="margin-left: 10px;">
			<ol class="stepBar step2">
				<li class="step-nomal @if($editRow['progress_type'] == 90){{'current'}}@endif">キャンセル</li>
				<li class="step-nomal @if($editRow['progress_type'] == 100){{'current'}}@endif">返品</li>
			</ol>
		</div>

		@endunless
		<div id="line-02"></div>
		<div class="u-mt--sm">
			<p class="c-ttl--02">受注情報</p>
		</div>
		<div class="d-table">
			<div class="c-box--600Half">
				<table class="table c-tbl c-tbl--590">
					<tr>
						<th class="c-box--150">受注ID</th>
						<td>
							{{$editRow['t_order_hdr_id'] ?? ''}}
							<input type="hidden" name="t_order_hdr_id" value="{{ $editRow['t_order_hdr_id'] ?? '' }}">
							@include('common.elements.error_tag', ['name' => 't_order_hdr_id'])
						</td>
					</tr>
					<tr>
						<th class="must">受注日時</th>
						<td>
							<div class='c-box--218'>
								<div class='input-group date order-datetime-picker' id='datetimepicker_datetime'>
									<input type='text' name="order_datetime" id="order_datetime" class="form-control c-box--180" value="{{ $editRow['order_datetime'] ?? '' }}" />
									<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
								</div>
							</div>
							@include('common.elements.error_tag', ['name' => 'order_datetime'])
						</td>
					</tr>
					<tr>
						<th>受注担当者</th>
						<td>
							<select name="order_operator_id" id="order_operator_id" class="form-control c-box--300">
								@include('common.elements.NEOSM211_option_list', ['arrayName' => $viewExtendData['m_operators'], 'currentId' => ''])
							</select>
							@include('common.elements.error_tag', ['name' => 'order_operator_id'])
						</td>
					</tr>
					<tr>
						<th>受注方法</th>
						<td>
							<select name="order_type" id="order_type" class="form-control c-box--300">
								@include('common.elements.NEOSM211_option_list', ['arrayName' => $viewExtendData['m_ordertypes'], 'currentId' => ''])
							</select>
							@include('common.elements.error_tag', ['name' => 'order_type'])
						</td>
					</tr>
					<tr>
						<th class="must">ECサイト</th>
						<td>
							<select name="m_ecs_id" id="m_ecs_id" class="form-control c-box--300">
								@include('common.elements.NEOSM211_option_list', ['arrayName' => $viewExtendData['m_ecs'], 'currentId' => ''])
							</select>
							@include('common.elements.error_tag', ['name' => 'm_ecs_id'])
						</td>
					</tr>
					<tr>
						<th>ECサイト注文ID</th>
						<td>
							<a href="{{$viewExtendData['m_ecs_info']['m_ec_url'] ?? ''}}" target="_blank">{{$editRow['ec_order_num'] ?? ''}}</a>
							<input type="hidden" name="ec_order_num" value="{{ $editRow['ec_order_num'] ?? '' }}">
							@include('common.elements.error_tag', ['name' => 'ec_order_num'])
						</td>
					</tr>
					<tr>
						<th>領収証宛名</th>
						<td>
							<textarea class="form-control c-box--300" name="receipt_direction" id="receipt_direction" rows="6">{{$editRow['receipt_direction'] ?? ''}}</textarea>
							@include('common.elements.error_tag', ['name' => 'receipt_direction'])
						</td>
					</tr>
					<tr>
						<th>領収証但し書き</th>
						<td>
							<div class="c-box--300 d-flex" style="align-items: center; justify-content: space-between">
								<span>但し</span>
								<input type="text" name="receipt_proviso" id="receipt_proviso" class="form-control" value="{{ $editRow['receipt_proviso'] ?? '' }}">
								<span>代として</span>
							</div>
							@include('common.elements.error_tag', ['name' => 'receipt_proviso'])
						</td>
					</tr>
					<tr>
						<th>ギフトフラグ</th>
						<td>
							<select name="gift_flg" id="gift_flg" class="form-control u-input--small">
								<option value="" @if(!isset($editRow['gift_flg']) || $editRow['gift_flg']=='' ){{'selected'}}@endif>OFF</option>
								<option value="1" @if(isset($editRow['gift_flg']) && $editRow['gift_flg']=='1' ){{'selected'}}@endif>ON</option>
							</select>
							@include('common.elements.error_tag', ['name' => 'gift_flg'])
						</td>
					</tr>
				</table>
			</div>
			<div class="c-box--600Half">
				<table class="table c-tbl c-tbl--590">
					<tr>
						<th>備考</th>
						<td>
							<textarea name="order_comment" id="order_comment" class="form-control c-box--400" rows="5">{{$editRow['order_comment'] ?? ''}}</textarea>
							@include('common.elements.error_tag', ['name' => 'order_comment'])
						</td>
					</tr>
					<tr>
						<th>社内メモ</th>
						<td>
							<textarea name="operator_comment" id="operator_comment" class="form-control c-box--400" rows="5">{{$editRow['operator_comment'] ?? ''}}</textarea>
							@include('common.elements.error_tag', ['name' => 'operator_comment'])
						</td>
					</tr>
					<tr>
						<th>即日配送</th>
						<td>
							@unless(empty($editRow['t_order_hdr_id']))
							<input type="hidden" name="immediately_deli_flg" id="immediately_deli_flg" value="{{ $editRow['immediately_deli_flg'] ?? '' }}">
							<input type="checkbox" name="disabled_immediately_deli_flg" disabled='disabled' @if(isset($editRow['immediately_deli_flg']) && isset($editRow['immediately_deli_flg'])==1){{'checked'}}@endif>
							@endunless
							@include('common.elements.error_tag', ['name' => 'immediately_deli_flg'])
						</td>
					</tr>
					<tr>
						<th>楽天スーパーDEAL</th>
						<td>
							@unless(empty($editRow['t_order_hdr_id']))
							<input type="hidden" name="rakuten_super_deal_flg" id="rakuten_super_deal_flg" value="{{ $editRow['rakuten_super_deal_flg'] ?? '' }}">
							<input type="checkbox" name="disabled_rakuten_super_deal_flg" disabled='disabled' @if(isset($editRow['rakuten_super_deal_flg']) && isset($editRow['rakuten_super_deal_flg'])==1){{'checked'}}@endif>
							@endunless
							@include('common.elements.error_tag', ['name' => 'rakuten_super_deal_flg'])
						</td>
					</tr>
					<tr>
						<th>警告注文</th>
						<td>
							@unless(empty($editRow['t_order_hdr_id']))
							<input type="hidden" name="alert_order_flg" id="alert_order_flg" value="{{ $editRow['alert_order_flg'] ?? '' }}">
							<input type="checkbox" name="disabled_alert_order_flg" disabled='disabled' @if(isset($editRow['alert_order_flg']) && isset($editRow['alert_order_flg'])==1){{'checked'}}@endif>
							@endunless
							@include('common.elements.error_tag', ['name' => 'alert_order_flg'])
						</td>
					</tr>
				</table>
			</div>
		</div><!-- /.d-table -->

		<div id="line-03"></div>
		<p class="c-ttl--02">注文主情報</p>
		<div class="d-table">
			<div class="c-box--600Half">
				<table class="table c-tbl c-tbl--590">
					<tr>
						<th>顧客ID</th>
						<td>
							<input type="text" name="m_cust_id" id="m_cust_id" class="form-control u-input--mid" @if(isset($editRow['t_order_hdr_id']) && strlen($editRow['t_order_hdr_id'])> 0){{'readonly'}}@endif value="{{ $editRow['m_cust_id'] ?? '' }}">&nbsp;
							@empty($editRow['t_order_hdr_id'])
							<input type="button" class="btn btn-default" id="btn_search_cust" value="顧客を検索する" data-href="{{config('env.app_subsys_url.order')}}order/custlist">
							@endempty
							@include('common.elements.error_tag', ['name' => 'm_cust_id'])
						</td>
					</tr>
					<tr>
						<th class="must">電話番号</th>
						<td>
							<input type="text" name="order_tel1" id="order_tel1" class="form-control u-input--mid" value="{{ $editRow['order_tel1'] ?? '' }}">&nbsp;<input type="text" class="form-control u-input--mid" name="order_tel2" id="order_tel2" value="{{ $editRow['order_tel2'] ?? '' }}">
							@include('common.elements.error_tag', ['name' => 'order_tel1'])
							@include('common.elements.error_tag', ['name' => 'order_tel2'])
						</td>
					</tr>
					<tr>
						<th>FAX番号</th>
						<td>
							<input type="text" name="order_fax" id="order_fax" class="form-control u-input--mid" value="{{ $editRow['order_fax'] ?? '' }}">
							@include('common.elements.error_tag', ['name' => 'order_fax'])
						</td>
					</tr>
					<tr>
						<th>フリガナ</th>
						<td>
							<input type="text" name="order_name_kana" id="order_name_kana" class="form-control c-box--300" value="{{ $editRow['order_name_kana'] ?? '' }}">
							@include('common.elements.error_tag', ['name' => 'order_name_kana'])
						</td>
					</tr>
					<tr>
						<th class="must">名前</th>
						<td>
							<input type="text" name="order_name" id="order_name" class="form-control c-box--300" value="{{ $editRow['order_name'] ?? '' }}">
							@include('common.elements.error_tag', ['name' => 'order_name'])
						</td>
					</tr>
					<tr>
						<th>メールアドレス</th>
						<td>
							<input type="text" name="order_email1" id="order_email1" class="form-control c-box--300" value="{{ $editRow['order_email1'] ?? '' }}">
							@include('common.elements.error_tag', ['name' => 'order_email1'])
							<input type="text" name="order_email2" id="order_email2" class="form-control c-box--300" value="{{ $editRow['order_email2'] ?? '' }}">
							@include('common.elements.error_tag', ['name' => 'order_email2'])
						</td>
					</tr>
					<tr>
						<th>顧客ランク</th>
						<td>
							<input type="text" name="cust_rank_name" id="cust_rank_name" class="form-control u-input--mid" readonly value="{{$viewExtendData['m_cust']['cust_rank_name'] ?? ''}}">
						</td>
					</tr>
					<tr>
						<th>要注意区分</th>
						<td>
							<input type="hidden" name="alert_cust_type" value="{{$viewExtendData['m_cust']['alert_cust_type'] ?? ''}}">
							<div class="radio-inline">
								<label><input type="radio" name="disabled_alert_cust_type" value="0" disabled @if(isset($viewExtendData['m_cust']['alert_cust_type']) && ($viewExtendData['m_cust']['alert_cust_type']=='0' )){{'checked'}}@endif>通常</label>
							</div>
							<div class="radio-inline">
								<label><input type="radio" name="disabled_alert_cust_type" value="1" disabled @if(isset($viewExtendData['m_cust']['alert_cust_type']) && ($viewExtendData['m_cust']['alert_cust_type']=='1' )){{'checked'}}@endif>要確認</label>
							</div>
							<div class="radio-inline">
								<label><input type="radio" name="disabled_alert_cust_type" value="2" disabled @if(isset($viewExtendData['m_cust']['alert_cust_type']) && ($viewExtendData['m_cust']['alert_cust_type']=='2' )){{'checked'}}@endif>受注不可</label>
							</div>
						</td>
					</tr>
				</table>
			</div>
			<div class="c-box--600Half">
				<table class="table c-tbl c-tbl--590">
					<tr>
						<th class="must">住所</th>
						<td>
							<div class="d-table c-tbl--400">
								<div class="d-table-cell c-box--100">郵便番号</div>
								<div class="d-table-cell"><input type="text" name="order_postal" id="order_postal" class="form-control" maxlength="8" value="{{ $editRow['order_postal'] ?? '' }}" onKeyUp="AjaxZip3.zip2addr(this,'','order_address1','order_address2','dummy','order_address3');"></div>
							</div>
							@include('common.elements.error_tag', ['name' => 'order_postal'])
							<div class="d-table c-tbl--400 u-mt--xs">
								<div class="d-table-cell c-box--100">都道府県</div>
								<div class="d-table-cell">
									<select name="order_address1" id="order_address1" class="form-control c-box--200">
										@foreach($viewExtendData['m_prefectures'] as $keyId => $keyValue)
										<option value="{{$keyValue}}" @if (isset($editRow['order_address1']) && $editRow['order_address1']==$keyValue){{'selected'}}@endif>{{$keyValue}}</option>
										@endforeach
									</select>
								</div>
							</div>
							@include('common.elements.error_tag', ['name' => 'order_address1'])
							<div class="d-table c-tbl--400 u-mt--xs">
								<div class="d-table-cell c-box--100">市区町村</div>
								<div class="d-table-cell"><input type="text" name="order_address2" id="order_address2" class="form-control c-box--full" value="{{ $editRow['order_address2'] ?? '' }}"></div>
							</div>
							@include('common.elements.error_tag', ['name' => 'order_address2'])
							<div class="d-table c-tbl--400 u-mt--xs">
								<div class="d-table-cell c-box--100">番地</div>
								<div class="d-table-cell"><input type="text" name="order_address3" id="order_address3" class="form-control c-box--full" value="{{ $editRow['order_address3'] ?? '' }}"></div>
							</div>
							@include('common.elements.error_tag', ['name' => 'order_address3'])
							<div class="d-table c-tbl--400 u-mt--xs">
								<div class="d-table-cell c-box--100">建物名</div>
								<div class="d-table-cell"><input type="text" name="order_address4" id="order_address4" class="form-control c-box--full" value="{{ $editRow['order_address4'] ?? '' }}"></div>
							</div>
							@include('common.elements.error_tag', ['name' => 'order_address4'])
						</td>
					</tr>
					<tr>
						<th>法人名・団体名</th>
						<td>
							<input type="text" name="order_corporate_name" id="order_corporate_name" class="form-control c-box--full" value="{{ $editRow['order_corporate_name'] ?? '' }}">
							@include('common.elements.error_tag', ['name' => 'order_corporate_name'])
						</td>
					</tr>
					<tr>
						<th>部署名</th>
						<td>
							<input type="text" name="order_division_name" id="order_division_name" class="form-control c-box--full" value="{{ $editRow['order_division_name'] ?? '' }}">
							@include('common.elements.error_tag', ['name' => 'order_division_name'])
						</td>
					</tr>
					<tr>
						<th>勤務先電話番号</th>
						<td>
							<input type="text" name="corporate_tel" id="corporate_tel" class="form-control u-input--mid" readonly value="{{$viewExtendData['m_cust']['corporate_tel'] ?? ''}}">
						</td>
					</tr>
					<tr>
						<th>顧客備考</th>
						<td>
							<textarea name="cust_note" id="cust_note" class="form-control c-box--400" rows="5" readonly>{{$viewExtendData['m_cust']['note'] ?? ''}}</textarea>
						</td>
					</tr>
				</table>
			</div>
		</div><!-- /.d-table -->

		<div id="tabs">
			<div id="line-04"></div>
			<div class="c-box--full u-mt--xs">
				<ul>
					@isset($editRow['register_destination'])
					@php
					$destIndex = -1;
					@endphp
					@foreach($editRow['register_destination'] as $registerDestination)
					@php
					$destIndex++;
					@endphp
					<li><a id="dest_tab_{{$destIndex}}" data-delicomp="{{$destIndex}}" href="#tabs-{{$registerDestination['order_destination_seq']}}">{{$registerDestination['destination_tab_display_name'] ?? ''}}</a></li>
					@endforeach
					@endisset
				</ul>
			</div>
			<div class="tabs-inner">
				@isset($editRow['register_destination'])
				@php
				$destIndex = -1;
				@endphp
				@foreach($editRow['register_destination'] as $registerDestination)
				<!-- tabs-1ここから -->
				@php
				$destIndex++;
				@endphp
				<div id="tabs-{{$registerDestination['order_destination_seq']}}">
					<div class="c-box--1180">
						<p class="c-ttl--02">送付先情報</p>
					</div>
					<input type="hidden" name="register_destination[{{$destIndex}}][destination_tab_display_name]" value="{{$registerDestination['destination_tab_display_name'] ?? ''}}">
					<input type="hidden" name="register_destination[{{$destIndex}}][t_order_destination_id]" value="{{$registerDestination['t_order_destination_id'] ?? ''}}">
					<input type="hidden" name="register_destination[{{$destIndex}}][order_destination_seq]" value="{{$registerDestination['order_destination_seq'] ?? ''}}">
					<div class="d-table c-box--1180">
						<div class="c-box--590Half">
							<table class="table table-bordered c-tbl c-tbl--580">
								<tr>
									<th class="c-box--150 must">電話番号</th>
									<td>
										<input type="text" name="register_destination[{{$destIndex}}][destination_tel]" id="register_destination_{{$destIndex}}_destination_tel" class="form-control u-input--mid" value="{{$registerDestination['destination_tel'] ?? ''}}">
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.destination_tel'])
									</td>
								</tr>
								<tr>
									<th>フリガナ</th>
									<td>
										<input type="text" name="register_destination[{{$destIndex}}][destination_name_kana]" id="register_destination_{{$destIndex}}_destination_name_kana" class="form-control c-box--300" value="{{$registerDestination['destination_name_kana'] ?? ''}}">
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.destination_name_kana'])
									</td>
								</tr>
								<tr>
									<th class="must">名前</th>
									<td>
										<input type="text" name="register_destination[{{$destIndex}}][destination_name]" id="register_destination_{{$destIndex}}_destination_name" class="form-control c-box--300" value="{{$registerDestination['destination_name'] ?? ''}}">
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.destination_name'])
									</td>
								</tr>
								<tr>
									<th class="must">配送方法</th>
									<td>
										<select name="register_destination[{{$destIndex}}][m_delivery_type_id]" id="register_destination_{{$destIndex}}_delivery_name" class="form-control c-box--300" onchange="changeDeliveryType({{$destIndex}}, true)">
											@unless(empty($viewExtendData['m_delivery_type_list']))
											@foreach($viewExtendData['m_delivery_type_list'] as $deliveryType)
											<option value="{{$deliveryType['m_delivery_types_id']}}" data-delicomp="{{$deliveryType['delivery_type']}}" @if(!empty($registerDestination['m_delivery_type_id']) && $deliveryType['m_delivery_types_id']==$registerDestination['m_delivery_type_id']) selected @endif>{{$deliveryType['m_delivery_type_name']}}</option>
											@endforeach
											@endunless
										</select>
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.delivery_name'])
									</td>
								</tr>
								<tr>
									<th>配送希望日</th>
									<td>
										<div class='c-box--218'>
											<div class='input-group date date-picker' id='datetimepicker_date'>
												<input name="register_destination[{{$destIndex}}][deli_hope_date]" id="register_destination_{{$destIndex}}_deli_hope_date" type='text' class="form-control c-box--180" value="{{$registerDestination['deli_hope_date'] ?? ''}}" />
												<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
											</div>
										</div>
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.deli_hope_date'])
									</td>
								</tr>
								<tr>
									<th>配送希望時間帯</th>
									<td>
										<!--<select name="register_destination[{{$destIndex}}][delivery_time_hope_cd]" id="deli_hope_time_name" class="form-control c-box--300">-->
										<select name="register_destination[{{$destIndex}}][m_delivery_time_hope_id]" id="m_delivery_time_hope_id_{{$destIndex}}" class="form-control c-box--300">
											<option value=""></option>
											@foreach($viewExtendData['m_delivery_time_hope'] as $deliveryTimehope)
											@if(!empty($registerDestination['m_delivery_time_hope_id']) && $deliveryTimehope['m_delivery_time_hope_id'] == $registerDestination['m_delivery_time_hope_id'] && $deliveryTimehope['delivery_company_cd'] == $registerDestination['delivery_type'])

											<option value="{{$deliveryTimehope['m_delivery_time_hope_id']}}" data-delicomp="{{$deliveryTimehope['delivery_company_cd']}}" selected>{{$deliveryTimehope['delivery_company_time_hope_name']}}</option>
											@else
											<option value="{{$deliveryTimehope['m_delivery_time_hope_id']}}" data-delicomp="{{$deliveryTimehope['delivery_company_cd']}}">{{$deliveryTimehope['delivery_company_time_hope_name']}}</option>
											@endif
											@endforeach
										</select>
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.delivery_time_hope_name'])
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.m_delivery_time_hope_id'])
									</td>
								</tr>
								<tr>
									<th>出荷予定日</th>
									<td>
										<div class='c-box--218'>
											<div class='input-group date date-picker' id='datetimepicker_date'>
												<input type='text' name="register_destination[{{$destIndex}}][deli_plan_date]" id="register_destination_{{$destIndex}}_deli_plan_date" class="form-control c-box--180" value="{{$registerDestination['deli_plan_date'] ?? ''}}" />
												<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
											</div>
										</div>
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.deli_plan_date'])
									</td>
								</tr>
								<tr>
									<th>送り状コメント</th>
									<td>
										<input type="text" name="register_destination[{{$destIndex}}][invoice_comment]" id="register_destination_{{$destIndex}}_invoice_comment" class="form-control u-input--full" value="{{$registerDestination['invoice_comment'] ?? ''}}">
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.invoice_comment'])
									</td>
								</tr>
								<tr>
									<th>ピッキングコメント</th>
									<td>
										<input type="text" name="register_destination[{{$destIndex}}][picking_comment]" id="register_destination_{{$destIndex}}_picking_comment" class="form-control u-input--full" value="{{$registerDestination['picking_comment'] ?? ''}}">
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.picking_comment'])
									</td>
								</tr>
								<tr>
									<th>分割配送する</th>
									<td>
										<input type="checkbox" name="register_destination[{{$destIndex}}][partial_deli_flg]" id="register_destination_{{$destIndex}}_partial_deli_flg" value="1" @if(isset($registerDestination['partial_deli_flg']) && $registerDestination['partial_deli_flg']==1){{'checked'}}@endif>
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.partial_deli_flg'])
									</td>
								</tr>
							</table>
						</div>
						<div class="c-box--590Half">
							<table class="table table-bordered c-tbl c-tbl--580">
								<tr>
									<th class="must">住所</th>
									<td>
										<div class="d-table c-tbl--400">
											<div class="d-table-cell c-box--100">郵便番号</div>
											<div class="d-table-cell"><input type="text" name="register_destination[{{$destIndex}}][destination_postal]" id="register_destination_{{$destIndex}}_destination_postal" class="form-control" maxlength="8" value="{{$registerDestination['destination_postal'] ?? ''}}" onKeyUp="AjaxZip3.zip2addr(this,'','register_destination[{{$destIndex}}][destination_address1]','register_destination[{{$destIndex}}][destination_address2]','dummy','register_destination[{{$destIndex}}][destination_address3]');"></div>
										</div>
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.destination_postal'])
										<div class="d-table c-tbl--400 u-mt--xs">
											<div class="d-table-cell c-box--100">都道府県</div>
											<div class="d-table-cell">
												<select name="register_destination[{{$destIndex}}][destination_address1]" id="register_destination_{{$destIndex}}_destination_address1" class="form-control c-box--200">
													@foreach($viewExtendData['m_prefectures'] as $keyId => $keyValue)
													<option value="{{$keyValue}}" @if (isset($registerDestination['destination_address1']) && $registerDestination['destination_address1']==$keyValue){{'selected'}}@endif>{{$keyValue}}</option>
													@endforeach
												</select>
											</div>
										</div>
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.destination_address1'])
										<div class="d-table c-tbl--400 u-mt--xs">
											<div class="d-table-cell c-box--100">市区町村</div>
											<div class="d-table-cell"><input type="text" name="register_destination[{{$destIndex}}][destination_address2]" id="register_destination_{{$destIndex}}_destination_address2" class="form-control c-box--full" value="{{$registerDestination['destination_address2'] ?? ''}}"></div>
										</div>
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.destination_address2'])
										<div class="d-table c-tbl--400 u-mt--xs">
											<div class="d-table-cell c-box--100">番地</div>
											<div class="d-table-cell"><input type="text" name="register_destination[{{$destIndex}}][destination_address3]" id="register_destination_{{$destIndex}}_destination_address3" class="form-control c-box--full" value="{{$registerDestination['destination_address3'] ?? ''}}"></div>
										</div>
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.destination_address3'])
										<div class="d-table c-tbl--400 u-mt--xs">
											<div class="d-table-cell c-box--100">建物名</div>
											<div class="d-table-cell"><input type="text" name="register_destination[{{$destIndex}}][destination_address4]" id="register_destination_{{$destIndex}}_destination_address4" class="form-control c-box--full" value="{{$registerDestination['destination_address4'] ?? ''}}"></div>
										</div>
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.destination_address4'])
									</td>
								</tr>
								<tr>
									<th>法人名・団体名</th>
									<td>
										<input type="text" name="register_destination[{{$destIndex}}][destination_company_name]" id="register_destination_{{$destIndex}}_destination_company_name" class="form-control c-box--full" value="{{$registerDestination['destination_company_name'] ?? ''}}">
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.destination_company_name'])
									</td>
								</tr>
								<tr>
									<th>部署名</th>
									<td>
										<input type="text" name="register_destination[{{$destIndex}}][destination_division_name]" id="register_destination_{{$destIndex}}_destination_division_name" class="form-control c-box--full" value="{{$registerDestination['destination_division_name'] ?? ''}}">
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.destination_division_name'])
									</td>
								</tr>
								<tr>
									<th>ギフトメッセージ</th>
									<td>
										<input type="text" name="register_destination[{{$destIndex}}][gift_message]" id="register_destination_{{$destIndex}}_gift_message" class="form-control u-input--full" value="{{$registerDestination['gift_message'] ?? ''}}">
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.gift_message'])
									</td>
								</tr>
								<tr>
									<th>ギフト包装種類</th>
									<td>
										<input type="text" name="register_destination[{{$destIndex}}][gift_wrapping]" id="register_destination_{{$destIndex}}_gift_wrapping" class="form-control u-input--full" value="{{$registerDestination['gift_wrapping'] ?? ''}}">
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.gift_wrapping'])
									</td>
								</tr>
								<tr>
									<th>のしタイプ</th>
									<td>
										<input type="text" name="register_destination[{{$destIndex}}][nosi_type]" id="register_destination_{{$destIndex}}_nosi_type" class="form-control u-input--full" value="{{$registerDestination['nosi_type'] ?? ''}}">
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.nosi_type'])
									</td>
								</tr>
								<tr>
									<th>のし名前</th>
									<td>
										<input type="text" name="register_destination[{{$destIndex}}][nosi_name]" id="register_destination_{{$destIndex}}_nosi_name" class="form-control u-input--full" value="{{$registerDestination['nosi_name'] ?? ''}}">
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.nosi_name'])
									</td>
								</tr>
							</table>
						</div>
					</div><!--/.d-table-->
					<div class="u-mt--ss">
						@empty($registerDestination['t_order_destination_id'])
						<input type="submit" name="submit_del_dest[{{$destIndex}}]" class="btn btn-danger btn-lg" value="送付先を削除">
						@endempty
						@include('common.elements.error_tag', ['name' => 'submit_deldest.' . $destIndex])
					</div>
					<div id="line-05"></div>
					<div class="c-box--1180 u-mt--ss">
						<p class="c-ttl--02">受注明細情報</p>
					</div>
					<div class="d-table c-box--1160">
						<!--
							<div class="u-mt--xs">
								<input type="submit" name="submit_add_dtl[{{$destIndex}}]" class="btn btn-default btn-lg" value="明細追加">
							</div>
-->
						<table class="table table-bordered c-tbl c-tbl--1160 u-mt--ss">
							<tr class="nowrap">
								<th>コピー</th>
								<th class="must">販売コード</th>
								<th class="c-box--60"></th>
								<th class="must">販売名</th>
								<th class="must">販売単価</th>
								<th class="must">数量</th>
								<th>販売金額</th>
								<th>在庫状態
									<button type="submit" class="btn btn-default btn-xs" name="submit_stockinfo_update[{{$destIndex}}]">
										<span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
									</button>
								</th>
								<th>クーポンID</th>
								<th>クーポン金額</th>
								<th class="u-center"></th>
							</tr>
							@isset($registerDestination['register_detail'])
							@php
							$dtlIndex = -1;
							@endphp
							@foreach($registerDestination['register_detail'] as $registerDetail)
							@php
							$dtlIndex++;
							@endphp
							<tr>
								<td class="u-vam u-center">
									<label for="">
										<input type="checkbox" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][check_copy]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_check_copy" data-rowid="{{$destIndex}}-{{$dtlIndex}}" class="checkbox" value="{{$registerDetail['check_copy'] or '0'}}">
									</label>
								</td>
								<td class="u-vam">
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][t_order_dtl_id]" value="{{$registerDetail['t_order_dtl_id'] ?? ''}}">
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_seq]" value="{{$registerDetail['order_dtl_seq'] ?? ''}}">
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][t_deli_hdr_id]" value="{{$registerDetail['t_deli_hdr_id'] ?? ''}}">
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][cancel_timestamp]" value="{{$registerDetail['cancel_timestamp'] ?? ''}}">
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][cancel_flg]" value="{{$registerDetail['cancel_flg'] ?? ''}}">
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][reservation_date]" value="{{$registerDetail['reservation_date'] ?? ''}}">
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][variation_values]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_variation_values" value="{{$registerDetail['variation_values'] ?? ''}}">
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][sell_id]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_sell_id" value="{{$registerDetail['sell_id'] ?? ''}}">
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][sell_checked]" value="{{$registerDetail['sell_checked'] ?? ''}}">
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][sku_data]" value="{{$registerDetail['sku_data'] ?? ''}}">
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][tax_rate]" value="{{$registerDetail['tax_rate'] ?? ''}}">

									<input type="text" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][sell_cd]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_sell_cd" class="form-control u-input--mid" @if(!empty($registerDetail['t_order_dtl_id']) || !empty($registerDetail['sell_checked'])){{'readonly'}}@endif value="{{$registerDetail['sell_cd'] ?? ''}}">
									@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.register_detail.' . $dtlIndex . '.sell_cd'])
									@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.register_detail.' . $dtlIndex . '.t_order_dtl_id'])
								</td>
								<td class="u-vam u-center">
									@if(empty($registerDetail['t_order_dtl_id']) && empty($registerDetail['sell_checked']))
									<input type="button" name="btn_search_sell[{{$destIndex}}][{{$dtlIndex}}]" id="btn_search_sell_{{$destIndex}}_{{$dtlIndex}}" data-rowid="{{$destIndex}}-{{$dtlIndex}}" data-href="{{config('env.app_subsys_url.order')}}order/sales" class="btn btn-success btn_search_sell" value="検索">
									@endif
								</td>
								<td class="u-vam">
									<!--												<input type="text" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][sell_name]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_sell_name" class="form-control u-input--mid" {{$registerDetail['sell_name_readonly']}} value="{{$registerDetail['sell_name'] ?? ''}}">-->
									<textarea name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][sell_name]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_sell_name" rows="1" class="form-control u-input--mid c-box--full" {{$registerDetail['sell_name_readonly']}} style="resize: vertical">{{$registerDetail['sell_name'] ?? ''}}</textarea>

									@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.register_detail.' . $dtlIndex . '.sell_name'])
								</td>
								<td class="u-vam u-right">
									<input type="text" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_sell_price]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_order_sell_price" data-rowid="{{$destIndex}}-{{$dtlIndex}}" class="form-control u-input--small u-right" {{$registerDetail['order_sell_price_readonly']}} value="{{$registerDetail['order_sell_price'] ?? ''}}">
									@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.register_detail.' . $dtlIndex . '.order_sell_price'])
								</td>
								<td class="u-vam u-right">
									<input type="text" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_sell_vol]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_order_sell_vol" data-rowid="{{$destIndex}}-{{$dtlIndex}}" class="form-control u-input--small u-right c-box--60" {{$registerDetail['order_sell_vol_readonly']}} value="{{$registerDetail['order_sell_vol'] ?? ''}}">
									@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.register_detail.' . $dtlIndex . '.order_sell_vol'])
								</td>
								<td class="u-vam u-right">
									<span id="order_sell_amount_{{$destIndex}}-{{$dtlIndex}}">{{$registerDetail['order_sell_amount'] ?? ''}}</span>
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_sell_amount]" id="hdn_order_sell_amount_{{$destIndex}}-{{$dtlIndex}}">
								</td>
								<td class="u-vam u-center">
									<a id="drawing_status_{{$destIndex}}-{{$dtlIndex}}" data-sellcd="{{$registerDetail['sell_cd'] ?? ''}}" data-rowid="{{$destIndex}}-{{$dtlIndex}}" style="cursor: pointer" data-href="{{config('env.app_subsys_url.order')}}order/stockinfo/id/{{$registerDetail['sell_cd'] ?? ''}}/variation/{{$registerDetail['variation_values'] or '__'}}/itemid/0">{{$registerDetail['drawing_status_name'] ?? ''}}</a>
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][drawing_status_name]" value="{{$registerDetail['drawing_status_name'] ?? ''}}">
								</td>
								<td class="u-vam">
									{{$registerDetail['order_dtl_coupon_id'] ?? ''}}
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_coupon_id]" value="{{$registerDetail['order_dtl_coupon_id'] ?? ''}}">
									@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.register_detail.' . $dtlIndex . '.order_dtl_coupon_id'])
								</td>
								<td class="u-vam u-right">
									{{$registerDetail['order_dtl_coupon_price'] ?? ''}}
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_coupon_price]" value="{{$registerDetail['order_dtl_coupon_price'] ?? ''}}">
									@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.register_detail.' . $dtlIndex . '.order_dtl_coupon_price'])
								</td>
								<td class="u-vam u-center font-FF0000">
									{{$registerDetail['cancel_string'] ?? ''}}
									@if($registerDetail['btn_delete_visible'] == 1)
									<input type="button" name="btn_del_dtl" class="btn btn-danger" data-rowid="{{$destIndex}}-{{$dtlIndex}}" value="削除">
									@endif
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][btn_delete_visible]" value="{{$registerDetail['btn_delete_visible'] ?? ''}}">
									@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.register_detail.' . $dtlIndex . '.cancel_timestamp'])
								</td>
							</tr>
							@endforeach
							@endisset
							<tr>
								<td colspan="5" class="u-vam u-right">小計</td>
								<td class="u-vam u-right">
									<span id="sum_destination_sell_total_{{$destIndex}}">
										{{$registerDestination['sum_sell_total'] ?? ''}}
									</span>
								</td>
							</tr>
							<tr>
								<td colspan="5" class="u-vam u-right">送料</td>
								<td class="u-vam u-right">
									<input type="text" name="register_destination[{{$destIndex}}][shipping_fee]" id="register_destination_{{$destIndex}}_shipping_fee" class="form-control u-input--small u-right c-box--60" value="{{$registerDestination['shipping_fee'] ?? ''}}">
									@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.shipping_fee'])
								</td>
							</tr>
							<tr>
								<td colspan="5" class="u-vam u-right">手数料</td>
								<td class="u-vam u-right">
									<input type="text" name="register_destination[{{$destIndex}}][payment_fee]" id="register_destination_{{$destIndex}}_payment_fee" class="form-control u-input--small u-right c-box--60" value="{{$registerDestination['payment_fee'] ?? ''}}">
									@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.payment_fee'])
								</td>
							</tr>
							<tr>
								<td colspan="5" class="u-vam u-right">包装料</td>
								<td class="u-vam u-right">
									<input type="text" name="register_destination[{{$destIndex}}][wrapping_fee]" id="register_destination_{{$destIndex}}_wrapping_fee" class="form-control u-input--small u-right c-box--60" value="{{$registerDestination['wrapping_fee'] ?? ''}}">
									@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.wrapping_fee'])
								</td>
							</tr>
						</table>
						@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.t_order_destination_id'])
						@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.destination_alter_flg'])
						@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.ec_destination_num'])
						@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.register_detail'])
					</div><!--/.d-table-->
				</div><!-- tabs-1ここまで -->
				@endforeach
				@endisset
			</div><!-- /tabs-inner -->
		</div><!-- /tabs -->

		<div id="line-06">
			</dlv>
			<div class="u-mt--ss">
				<p class="c-ttl--02">金額情報</p>
			</div>
			<table class="table table-bordered c-tbl c-tbl--1200 u-mt--ss">
				<tr>
					<th class="c-box--110">商品金額計</th>
					<td class="u-right c-box--90">
						<span id="sell_total_price">{{$editRow['sell_total_price'] ?? ''}}</span>
						@include('common.elements.error_tag', ['name' => 'sell_total_price'])
					</td>
					<th class="c-box--110">消費税</th>
					<td class="c-box--90">
						<input type="text" name="tax_price" id="tax_price" class="form-control u-input--small u-right" value="{{ $editRow['tax_price'] ?? '' }}">
						@include('common.elements.error_tag', ['name' => 'tax_price'])
					</td>
					<th class="c-box--110">送料</th>
					<td class="u-right c-box--90">
						<span id="shipping_fee">{{$editRow['shipping_fee'] ?? ''}}</span>
						@include('common.elements.error_tag', ['name' => 'shipping_fee'])
					</td>
					<th class="c-box--110">手数料</th>
					<td class="u-right c-box--90">
						<span id="payment_fee">{{$editRow['payment_fee'] ?? ''}}</span>
						@include('common.elements.error_tag', ['name' => 'payment_fee'])
					</td>
					<th class="c-box--110">包装料</th>
					<td class="u-right c-box--90">
						<span id="package_fee">{{$editRow['package_fee'] ?? ''}}</span>
						@include('common.elements.error_tag', ['name' => 'package_fee'])
					</td>
					<th class="c-box--110">合計金額</th>
					<td class="u-right c-box--90">
						<span id="total_price">{{$editRow['total_price'] ?? ''}}</span>
					</td>
				</tr>
				<tr>
					<th>割引金額</th>
					<td class="u-right">
						<input type="text" name="discount" id="discount" class="form-control u-input--small u-right font-FF0000"" value=" {{$editRow['discount'] ?? ''}}">
						@include('common.elements.error_tag', ['name' => 'discount'])
					</td>
					<th>ストア<br>クーポン</th>
					<td class="u-right">
						<input type="text" name="use_coupon_store" id="use_coupon_store" class="form-control u-input--small u-right font-FF0000" value="{{ $editRow['use_coupon_store'] ?? '' }}">
						@include('common.elements.error_tag', ['name' => 'use_coupon_store'])
					</td>
					<th>モール<br>クーポン</th>
					<td class="u-right">
						<input type="text" name="use_coupon_mall" id="use_coupon_mall" class="form-control u-input--small u-right font-FF0000" value="{{ $editRow['use_coupon_mall'] ?? '' }}">
						@include('common.elements.error_tag', ['name' => 'use_coupon_mall'])
					</td>
					<th>クーポン<br>合計</th>
					<td class="u-right font-FF0000">
						<span id="total_use_coupon">{{$editRow['total_use_coupon'] ?? ''}}</span>
						@include('common.elements.error_tag', ['name' => 'total_use_coupon'])
					</td>
					<th>利用ポイント</th>
					<td class="u-right">
						<input type="text" name="use_point" id="use_point" class="form-control u-input--small u-right font-FF0000" value="{{ $editRow['use_point'] ?? '' }}">
						@include('common.elements.error_tag', ['name' => 'use_point'])
					</td>
					<th>請求金額</th>
					<td class="u-right">
						<span id="order_total_price"><b>{{$editRow['order_total_price'] ?? ''}}</b></span>
						@include('common.elements.error_tag', ['name' => 'order_total_price'])
					</td>
				</tr>
			</table>

			<div id="line-07"></div>
			<div class="u-mt--ss">
				<p class="c-ttl--02">決済情報</p>
			</div>
			<table class="table table-bordered c-tbl c-tbl--1200 u-mt--ss">
				<tr>
					<th class="c-box--200 must">支払い方法</th>
					<td>
						<select name="m_pay_type_id" id="pay_type_name" @if(!empty($editRow['paytype_readonly']) && $editRow['paytype_readonly']=='readonly' ){{'disabled'}}@endif class="form-control u-input--mid">
							@include('common.elements.NEOSM211_option_list', ['arrayName' => $viewExtendData['m_payment_types'], 'currentId' => (isset($editRow['m_pay_type_id']) ? $editRow['m_pay_type_id'] : '')])
						</select>
						@if(!empty($editRow['paytype_readonly']) && $editRow['paytype_readonly']=='readonly')
						<input type="hidden" name="m_pay_type_id" value="{{ $editRow['m_pay_type_id'] ?? '' }}">
						@endif
						@include('common.elements.error_tag', ['name' => 'pay_type_name'])
					</td>
				</tr>
				<tr>
					<th class="c-box--200">後払い決済 取引ID</th>
					<td>
						<input type="text" name="payment_transaction_id" id="payment_transaction_id" {{$editRow['paytype_readonly'] ?? ''}} class="form-control c-box--300" value="{{ $editRow['payment_transaction_id'] ?? '' }}">
						@include('common.elements.error_tag', ['name' => 'payment_transaction_id'])
					</td>
				</tr>
				<tr>
					<th class="c-box--200">支払回数</th>
					<td>
						<input type="text" name="card_pay_times" id="card_pay_times" {{$editRow['paytype_readonly'] ?? ''}} class="form-control u-input--small u-right" value="{{ $editRow['card_pay_times'] ?? '' }}">
						@include('common.elements.error_tag', ['name' => 'card_pay_times'])
					</td>
				</tr>
				<tr>
					<th class="c-box--200">後払い決済 請求書送付方法</th>
					<td>
						<div class="radio-inline">
							<label><input type="radio" name="cb_billed_type" value="0" @if(isset($editRow['cb_billed_type']) && ($editRow['cb_billed_type']=='0' )){{'checked'}}@endif>同梱</label>
						</div>
						<div class="radio-inline">
							<label><input type="radio" name="cb_billed_type" value="1" @if(isset($editRow['cb_billed_type']) && ($editRow['cb_billed_type']=='1' )){{'checked'}}@endif>別送</label>
						</div>
						@include('common.elements.error_tag', ['name' => 'cb_billed_type'])
					</td>
				</tr>
				<tr>
					<th class="c-box--200">販売コード</th>
					<td>
						<input type="text" name="sell_cd" id="sell_cd" {{$editRow['sell_cd'] ?? ''}} class="form-control c-box--300" value="{{ $editRow['sell_cd'] ?? '' }}">
						@include('common.elements.error_tag', ['name' => 'sell_cd'])
					</td>
				</tr>
			</table>
			@include('common.elements.error_tag', ['name' => 'card_company'])
			@include('common.elements.error_tag', ['name' => 'card_holder'])
			@include('common.elements.error_tag', ['name' => 'tax_rate'])
			@include('common.elements.error_tag', ['name' => 'operator_id'])
			@include('common.elements.error_tag', ['name' => 'order_dtl_coupon_id'])
			@include('common.elements.error_tag', ['name' => 'reservation_skip_flg'])
			@include('common.elements.error_tag', ['name' => 'credit_type'])
			@include('common.elements.error_tag', ['name' => 'payment_type'])
		</div><!-- /1200 -->
		<div class="u-mt--ss">
			<input type="submit" name="submit_cancel" class="btn btn-default btn-lg u-mr--xss" value="キャンセル">
			<input type="submit" name="submit_register" id="submit_register" class="btn btn-success btn-lg" style="margin-left: 10px;" value="確認">
		</div>
		<input type="submit" name="submit_search_cust" style="visibility:hidden">
		<input type="submit" name="submit_search_sell" style="visibility:hidden">
		@include('common.elements.on_enter_script', ['target_button_name' => 'submit_register'])
</form>
<div id="frameWindow" style="display: none"><iframe id="iframeDiv"></iframe></div>

<script type="text/javascript">
	$(document).ready(function() {
		const maxRows = 6;
		const charsPerRow = 20;
		
		$('#receipt_direction').css('resize', 'none');
		$('#receipt_direction').on('input', function() {
			let text = $(this).val();
			let lines = text.split('\n');

			// Restrict each line to 20 characters
			const truncatedLines = lines.map(line => line.slice(0, charsPerRow));

			// Combine the lines and keep at most 6 rows
			const combinedText = truncatedLines.join('\n');
			const rows = Math.min(truncatedLines.length, maxRows);
			const truncatedText = combinedText.split('\n').slice(0, rows).join('\n');

			// Update the textarea content
			$(this).val(truncatedText);
		});
	});
</script>

<script type="text/javascript">
	$(document).ready(function() {
		//スクロール位置
		@if(isset($editRow['scroll_top']) && strlen($editRow['scroll_top']) > 0)
		$(window).scrollTop({
			{
				$editRow['scroll_top']
			}
		});
		@endif
		//送料変更メッセージ
		@unless(empty($editRow['alertMessage']))
		alert("{{$editRow['alertMessage']}}");
		@endunless
		@php
		$destIndex = 0;
		@endphp
		@foreach($editRow['register_destination'] as $registerDestination)
		changeDeliveryType({
			{
				$destIndex
			}
		}, false);
		@php
		$destIndex++;
		@endphp
		@endforeach
		//エラー時にハッシュを削除
		@if(!empty($errorResult))
		location.hash = "";
		@endif
	});
</script>
@include('common.elements.datetime_picker_script')
<script type="text/javascript" src="{{config('env.app_subsys_url.order')}}js/Order/v1_0/NEOSM0211.js"></script>
@endsection