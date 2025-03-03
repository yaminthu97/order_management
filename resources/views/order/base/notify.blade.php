{{-- NEOSM0212:受注登録・修正確認 --}}
{{-- 画面設定 --}}
@php
$ScreenCd='NEOSM0212';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '受注登録・修正確認')

@section('csrf-token', "{{ csrf_token() }}")

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>受注登録・修正確認</li>
@endsection

@section('content')
<link rel="stylesheet" href="{{config('env.app_subsys_url.order')}}css/Order/v1_0/NEOSM0212.css">
<form method="post" name="Form1" action="">
{{ csrf_field() }}
<div class="c-box--1200">
	@isset($editRow['message'])
	<div class="c-box--full">
		<span class="font-FF0000">{{$editRow['message'] ?? ''}}</span>
	</div>
	@endisset
	@unless(empty($editRow['t_order_hdr_id']))
		<div class="u-mt--ss"><p class="c-ttl--02">ステータス</p></div>
		<p>進捗状況</p>
		<div class="d-inline-block">
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
		<div class="tag-box c-tbl-border-all">
		@isset($viewExtendData['register_order_tag'])
			@foreach($viewExtendData['register_order_tag'] as $orderTag)
				<span class="ns-btn-like ns-orderTag-style" style="background:#{{$orderTag['tag_color']}};color:#{{$orderTag['font_color']}};">{{$orderTag['tag_display_name']}}</span>&nbsp
			@endforeach
		@endisset
		</div><!-- /.tag-box-->
	@endunless
	<div class="u-mt--sm"><p class="c-ttl--02">受注情報</p></div>
		<div class="d-table">
			<div class="c-box--600Half">
			<table class="table c-tbl c-tbl--590">
				<tr>
					<th class="c-box--150">受注ID</th>
					<td>{{$editRow['t_order_hdr_id'] ?? ''}}</td>
				</tr>
				<tr>
					<th class="must">受注日時</th>
					<td>{{$editRow['order_datetime'] ?? ''}}</td>
				</tr>
				<tr>
					<th>受注担当者</th>
					<td>
						@foreach($viewExtendData['m_operators'] as $keyId => $keyValue)
							@if ($editRow['order_operator_id'] == $keyId){{$keyValue}}@endif
						@endforeach
					</td>
				</tr>
				<tr>
					<th>受注方法</th>
					<td>
						@foreach($viewExtendData['m_ordertypes'] as $keyId => $keyValue)
							@if($editRow['order_type'] == $keyId){{$keyValue}}@endif
						@endforeach
					</td>
				</tr>
				<tr>
					<th class="must">ECサイト</th>
					<td>
						@foreach($viewExtendData['m_ecs'] as $keyId => $keyValue)
							@if($editRow['m_ecs_id'] == $keyId){{$keyValue}}@endif
						@endforeach
					</td>
				</tr>
				<tr>
					<th>ECサイト注文ID</th>
					<td>{{$editRow['ec_order_num'] ?? ''}}</td>
				</tr>
				<tr>
					<th>領収証宛名</th>
					<td>{{$editRow['receipt_direction'] ?? ''}}</td>
				</tr>
				<tr>
					<th>領収証但し書き</th>
					<td>{{$editRow['receipt_proviso'] ?? ''}}</td>
				</tr>
				<tr>
					<th>ギフトフラグ</th>
					<td>@if(isset($editRow['gift_flg']) && $editRow['gift_flg']=='1') {{'ON'}} @else {{'OFF'}} @endif</td>
				</tr>
			</table>
		</div>
		<div class="c-box--600Half">
			<table class="table c-tbl c-tbl--590">
			<tr>
				<th class="c-box--150">備考</th>
				<td>{!! nl2br(e($editRow['order_comment'])) !!}</td>
			</tr>
			<tr>
				<th>社内メモ</th>
				<td>{!! nl2br(e($editRow['operator_comment'])) !!}</td>
			</tr>
			<tr>
				<th>即日配送</th>
				<td>
					@unless(empty($editRow['t_order_hdr_id']))
						<input type="checkbox" name="disabled_immediately_deli_flg" disabled='disabled' @if(isset($editRow['immediately_deli_flg']) && isset($editRow['immediately_deli_flg']) == 1){{'checked'}}@endif>
					@endunless
				</td>
			</tr>
			<tr>
				<th>楽天スーパーDEAL</th>
				<td>
					@unless(empty($editRow['t_order_hdr_id']))
						<input type="checkbox" name="disabled_rakuten_super_deal_flg" disabled='disabled' @if(isset($editRow['rakuten_super_deal_flg']) && isset($editRow['rakuten_super_deal_flg']) == 1){{'checked'}}@endif>
					@endunless
				</td>
			</tr>
			<tr>
				<th>警告注文</th>
				<td>
					@unless(empty($editRow['t_order_hdr_id']))
						<input type="checkbox" name="disabled_alert_order_flg" disabled='disabled' @if(isset($editRow['alert_order_flg']) && isset($editRow['alert_order_flg']) == 1){{'checked'}}@endif>
					@endunless
				</td>
			</tr>
			</table>
		</div>
	</div><!-- /.d-table -->

	<p class="c-ttl--02">注文主情報</p>
	<div class="d-table">
		<div class="c-box--600Half">
			<table class="table c-tbl c-tbl--590">
				<tr>
					<th class="c-box--150">顧客ID</th>
					<td colspan="2">{{$editRow['m_cust_id'] ?? ''}}</td>
				</tr>
				<tr>
					<th class="must">電話番号</th>
					<td>{{$editRow['order_tel1'] ?? ''}}</td>
					<td>{{$editRow['order_tel2'] ?? ''}}</td>
				</tr>
				<tr>
					<th>FAX番号</th>
					<td colspan="2">{{$editRow['order_fax'] ?? ''}}</td>
				</tr>
				<tr>
					<th>フリガナ</th>
					<td colspan="2">{{$editRow['order_name_kana'] ?? ''}}</td>
				</tr>
				<tr>
					<th class="must">名前</th>
					<td colspan="2">{{$editRow['order_name'] ?? ''}}</td>
				</tr>
				<tr>
					<th>メールアドレス</th>
					<td colspan="2">
						{{$editRow['order_email1'] ?? ''}}
						<br>
						{{$editRow['order_email2'] ?? ''}}
					</td>
				</tr>
				<tr>
					<th>顧客ランク</th>
					<td colspan="2">{{$editRow['cust_rank_name'] ?? ''}}</td>
				</tr>
				<tr>
					<th>要注意区分</th>
					<td colspan="2">
						@if($editRow['alert_cust_type']=='0') {{'通常'}} @endif
						@if($editRow['alert_cust_type']=='1') {{'要確認'}} @endif
						@if($editRow['alert_cust_type']=='2') {{'受注不可'}} @endif
					</td>
				</tr>
			</table>
		</div>
		<div class="c-box--600Half">
			<table class="table c-tbl c-tbl--590">
				<tr>
					<th class="c-box--150 must">郵便番号</th>
					<td>{{$editRow['order_postal'] ?? ''}}</td>
				</tr>
				<tr>
					<th class="must">都道府県</th>
					<td>{{$editRow['order_address1'] ?? ''}}</td>
				</tr>
				<tr>
					<th class="must">市区町村</th>
					<td>{{$editRow['order_address2'] ?? ''}}</td>
				</tr>
				<tr>
					<th>番地</th>
					<td>{{$editRow['order_address3'] ?? ''}}</td>
				</tr>
				<tr>
					<th>建物名</th>
					<td>{{$editRow['order_address4'] ?? ''}}</td>
				</tr>
				<tr>
					<th>法人名・団体名</th>
					<td>{{$editRow['order_corporate_name'] ?? ''}}</td>
				</tr>
				<tr>
					<th>部署名</th>
					<td>{{$editRow['order_division_name'] ?? ''}}</td>
				</tr>
				<tr>
					<th>勤務先電話番号</th>
					<td>{{$editRow['corporate_tel'] ?? ''}}</td>
				</tr>
				<tr>
					<th>顧客備考</th>
					<td>{!! nl2br(e($editRow['cust_note'])) !!}</td>
				</tr>
			</table>
		</div>
	</div><!-- /.d-table -->

	<div id="tabs">
		<div class="c-box--full u-mt--xs">
			<ul>
				@foreach($editRow['register_destination'] as $registerDestination)
					<li><a href="#tabs-{{$registerDestination['order_destination_seq']}}" >@isset($registerDestination['destination_tab_display_name']){{$registerDestination['destination_tab_display_name']}}@endisset</a></li>
				@endforeach
			</ul>
		</div>
		<div class="tabs-inner">
			@foreach($editRow['register_destination'] as $registerDestination)
				<!-- tabs-nここから -->
				<div id="tabs-{{$registerDestination['order_destination_seq']}}">
					<div class="c-box--1180"><p class="c-ttl--02">送付先情報</p></div>
					<div class="d-table c-box--1180">
						<div class="c-box--590Half">
							<table class="table table-bordered c-tbl c-tbl--580">
								<tr>
									<th class="c-box--150 must">電話番号</th>
									<td>{{$registerDestination['destination_tel'] ?? ''}}</td>
								</tr>
								<tr>
									<th>フリガナ</th>
									<td>{{$registerDestination['destination_name_kana'] ?? ''}}</td>
								</tr>
								<tr>
									<th class="must">名前</th>
									<td>{{$registerDestination['destination_name'] ?? ''}}</td>
								</tr>
								<tr>
									<th class="must">配送方法</th>
									<td>
										@foreach($viewExtendData['m_delivery_types'] as $keyId => $keyValue)
											@if($registerDestination['m_delivery_type_id'] == $keyId){{$keyValue}}@endif</option>
										@endforeach
									</td>
								</tr>
								<tr>
									<th>配送希望日</th>
									<td>{{$registerDestination['deli_hope_date'] ?? ''}}</td>
								</tr>
								<tr>
									<th>配送希望時間帯</th>
									<td>{{$registerDestination['deli_hope_time_name'] ?? ''}}</td>
								</tr>
								<tr>
									<th>出荷予定日</th>
									<td>{{$registerDestination['deli_plan_date'] ?? ''}}</td>
								</tr>
								<tr>
									<th>送り状コメント</th>
									<td>{{$registerDestination['invoice_comment'] ?? ''}}</td>
								</tr>
								<tr>
									<th>ピッキングコメント</th>
									<td>{{$registerDestination['picking_comment'] ?? ''}}</td>
								</tr>
								<tr>
									<th>分割配送</th>
									<td>@if(isset($registerDestination['partial_deli_flg']) && $registerDestination['partial_deli_flg'] == 1){{'する'}}@else{{'しない'}}@endif</td>
								</tr>
							</table>
						</div>
						<div class="c-box--590Half">
							<table class="table table-bordered c-tbl c-tbl--580">
								<tr>
									<th class="c-box--150 must">郵便番号</th>
									<td>{{$registerDestination['destination_postal'] ?? ''}}</td>
								</tr>
								<tr>
									<th class="must">都道府県</th>
									<td>{{$registerDestination['destination_address1'] ?? ''}}</td>
								</tr>
								<tr>
									<th class="must">市区町村</th>
									<td>{{$registerDestination['destination_address2'] ?? ''}}</td>
								</tr>
								<tr>
									<th>番地</th>
									<td>{{$registerDestination['destination_address3'] ?? ''}}</td>
								</tr>
								<tr>
									<th>建物名</th>
									<td>{{$registerDestination['destination_address4'] ?? ''}}</td>
								</tr>
								<tr>
									<th>法人名・団体名</th>
									<td>{{$registerDestination['destination_company_name'] ?? ''}}</td>
								</tr>
								<tr>
									<th>部署名</th>
									<td>{{$registerDestination['destination_division_name'] ?? ''}}</td>
								</tr>
								<tr>
									<th>ギフトメッセージ</th>
									<td>{{$registerDestination['gift_message'] ?? ''}}</td>
								</tr>
								<tr>
									<th>ギフト包装種類</th>
									<td>{{$registerDestination['gift_wrapping'] ?? ''}}</td>
								</tr>
								<tr>
									<th>のしタイプ</th>
									<td>{{$registerDestination['nosi_type'] ?? ''}}</td>
								</tr>
								<tr>
									<th>のし名前</th>
									<td>{{$registerDestination['nosi_name'] ?? ''}}</td>
								</tr>
							</table>
						</div>
					</div><!--/.d-table-->
					<div class="c-box--1180 u-mt--ss"><p class="c-ttl--02">受注明細情報</p></div>
					<div class="d-table c-box--1160">
						<table class="table table-bordered c-tbl c-tbl--1160 u-mt--ss">
							<tr class="nowrap">
								<th class="c-box--150 must">販売コード</th>
								<th class="must">販売名</th>
								<th class="c-box--110 must">販売単価</th>
								<th class="c-box--100 must">数量</th>
								<th>販売金額</th>
								<th>在庫状態</th>
								<th>クーポンID</th>
								<th>クーポン金額</th>
								<th class="u-center"></th>
							</tr>
							@foreach($registerDestination['register_detail'] as $registerDetail)
								<tr>
									<td class="u-vam nowrap">{{$registerDetail['sell_cd'] ?? ''}}</td>
									<td class="u-vam">{{$registerDetail['sell_name'] ?? ''}}</td>
									<td class="u-vam u-right nowrap">{{$registerDetail['order_sell_price'] ?? ''}}</td>
									<td class="u-vam u-right">{{$registerDetail['order_sell_vol'] ?? ''}}</td>
									<td class="u-vam u-right">{{$registerDetail['order_sell_amount'] ?? ''}}</td>
									<td class="u-vam u-center nowrap">{{$registerDetail['drawing_status_name'] ?? ''}}</td>
									<td class="u-vam">{{$registerDetail['order_dtl_coupon_id'] ?? ''}}</td>
									<td class="u-vam u-right">{{$registerDetail['order_dtl_coupon_price'] ?? ''}}</td>
									<td class="u-vam u-center font-FF0000 nowrap">{{$registerDetail['cancel_string'] ?? ''}}</td>
								</tr>
							@endforeach
							<tr>
								<td colspan="4" class="u-vam u-right">小計</td>
								<td class="u-vam u-right nowrap">{{$registerDestination['sum_sell_total'] or '0'}}</td>
							</tr>
							<tr>
								<td colspan="4" class="u-vam u-right">送料</td>
								<td class="u-vam u-right">{{$registerDestination['shipping_fee'] or '0'}}</td>
							</tr>
							<tr>
								<td colspan="4" class="u-vam u-right">手数料</td>
								<td class="u-vam u-right">{{$registerDestination['payment_fee'] or '0'}}</td>
							</tr>
							<tr>
								<td colspan="4" class="u-vam u-right">包装料</td>
								<td class="u-vam u-right">{{$registerDestination['wrapping_fee'] or '0'}}</td>
							</tr>
						</table>
					</div><!--/.d-table-->
				</div><!-- tabs-nここまで -->
			@endforeach
		</div><!-- /tabs-inner -->
	</div><!-- /tabs -->

	<div class="u-mt--ss"><p class="c-ttl--02">金額情報</p></div>
	<table class="table table-bordered c-tbl c-tbl--1200 u-mt--ss">
		<tr>
			<th class="c-box--110">商品金額計</th>
			<td class="u-right c-box--90">{{$editRow['sell_total_price'] or '0'}}</td>
			<th class="c-box--110">消費税</th>
			<td class="u-right c-box--90">{{$editRow['tax_price'] ?? ''}}</td>
			<th class="c-box--110">送料</th>
			<td class="u-right c-box--90">{{$editRow['shipping_fee'] or '0'}}</td>
			<th class="c-box--110">手数料</th>
			<td class="u-right c-box--90">{{$editRow['payment_fee'] or '0'}}</td>
			<th class="c-box--110">包装料</th>
			<td class="u-right c-box--90">{{$editRow['package_fee'] or '0'}}</td>
			<th class="c-box--110">合計金額</th>
			<td class="u-right c-box--90">{{$editRow['total_price'] or '0'}}</td>
		</tr>
		<tr>
			<th>割引金額</th>
			<td class="u-right font-FF0000">{{$editRow['discount'] or '0'}}</td>
			<th>ストアクーポン</th>
			<td class="u-right font-FF0000">{{$editRow['use_coupon_store'] or '0'}}</td>
			<th>モールクーポン</th>
			<td class="u-right font-FF0000">{{$editRow['use_coupon_mall'] or '0'}}</td>
			<th>クーポン合計</th>
			<td class="u-right font-FF0000">{{$editRow['total_use_coupon'] or '0'}}</td>
			<th>利用ポイント</th>
			<td class="u-right font-FF0000">{{$editRow['use_point'] or '0'}}</td>
			<th>請求金額</th>
			<td class="u-right"><b>{{$editRow['order_total_price'] or '0'}}</b></td>
		</tr>
	</table>

	<div class="u-mt--ss"><p class="c-ttl--02">決済情報</p></div>
	<table class="table table-bordered c-tbl c-tbl--1200 u-mt--ss">
		<tr>
			<th class="c-box--200 must">支払い方法</th>
			<td>
				@foreach($viewExtendData['m_payment_types'] as $keyId => $keyValue)
					@if($editRow['m_pay_type_id'] == $keyId){{$keyValue}}@endif
				@endforeach
			</td>
		</tr>
		<tr>
			<th class="c-box--200">後払い決済 取引ID</th>
			<td>{{$editRow['payment_transaction_id'] ?? ''}}</td>
		</tr>
		<tr>
			<th class="c-box--200">支払回数</th>
			<td>{{$editRow['card_pay_times'] ?? ''}}</td>
		</tr>
		<tr>
			<th class="c-box--200">後払い決済 請求書送付方法</th>
			<td>
				@if(isset($editRow['cb_billed_type']) && ($editRow['cb_billed_type']=='0')) {{'同梱'}} @endif
				@if(isset($editRow['cb_billed_type']) && ($editRow['cb_billed_type']=='1')) {{'別送'}} @endif
			</td>
		</tr>
	</table>
</div><!-- /1200 -->
<div class="u-mt--ss"><input type="submit" name="submit_cancel" class="btn btn-default btn-lg" value="キャンセル"><input type="submit" name="submit_register" class="btn btn-success btn-lg" style="margin-left: 10px;" value="登録"></div>
<input type="hidden" name="{{config('define.session_key_id')}}" value="{{$editRow[config('define.session_key_id')] ?? ''}}">
</form>
<script type="text/javascript" src="{{config('env.app_subsys_url.order')}}js/Order/v1_0/NEOSM0212.js"></script>
@endsection