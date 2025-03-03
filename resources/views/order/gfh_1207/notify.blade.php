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

@push('css')
<link rel="stylesheet" href="{{ esm_internal_asset('css/order/gfh_1207/app.css') }}">
@endpush

@section('content')
@php
$esmSessionManager = new App\Services\EsmSessionManager();
@endphp
<link rel="stylesheet" href="{{config('env.app_subsys_url.order')}}css/Order/v1_0/NEOSM0212.css">
<style>
	.c-tbl--1580{width:1580px; box-sizing: border-box;}
	.noshi_label{color:lightgrey;}
	.attachment_item_detail_view_0{display:none;}
	.detail_item_thumb{
		display: flex;
		text-align: center;
		justify-content: center;
		width: 100px;
	}
	.detail_item_thumb img {
		object-fit: cover;
		width: 100%;
		height: 100%;
	}
	.detail_item_html {
		width:440px;
		height:100px;
		overflow:auto;
	}
	.nav-tabs > li.active > a, .nav-tabs > li.active > a:hover, .nav-tabs > li.active > a:focus {
		background-color:#337ab7;
		color:white;
	}
</style>
@include('order.base.image-modal')
<div id="frameWindow" style="display: none"><iframe id="iframeDiv"></iframe></div>
<form method="post" name="Form1" action="">
{{ csrf_field() }}
<div class="c-box--1600">
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
		<input type="hidden" id="t_order_hdr_id" value= "{{$editRow['t_order_hdr_id'] ?? ''}}">
		<div class="d-table">
			<div class="c-box--800Half">
			<table class="table c-tbl c-tbl--790">
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
					<th>販売窓口</th>
					<td>
						@foreach(\Arr::pluck($viewExtendData['m_sales_counter_list'], 'm_itemname_type_name','m_itemname_types_id') as $keyId => $keyValue)
							@if($editRow['sales_store'] == $keyId){{$keyValue}}@endif
						@endforeach
					</td>
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
		<div class="c-box--800Half">
			<table class="table c-tbl c-tbl--790">
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
			<tr>
				<th>見積</th>
				<td>
					{{ \App\Enums\EstimateFlgEnum::tryfrom( $editRow['estimate_flg'] ?? 0 )?->label() }}
				</td>
			</tr>
			<tr>
				<th>領収書</th>
				<td>
					{{ \App\Enums\ReceiptTypeEnum::tryfrom( $editRow['receipt_type'] ?? 0 )?->label() }}
				</td>
			</tr>
			<tr>
				<th>キャンペーン</th>
				<td>
					{{ \App\Enums\CampaignFlgEnum::tryfrom( $editRow['campaign_flg'] ?? \App\Enums\CampaignFlgEnum::EXCLUDE->value )?->label() }}
				</td>
			</tr>
			</table>
		</div>
	</div><!-- /.d-table -->

	<p class="c-ttl--02">注文主情報</p>
	<div class="d-table">
		<div class="c-box--800Half">
			<table class="table c-tbl c-tbl--790">
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
					<td colspan="2">
						@foreach($viewExtendData['cust_runk_list']??[] as $elm)
							@if(($editRow['order_cust_runk_id'] ?? '')==$elm['m_itemname_types_id'])
								{{$elm['m_itemname_type_name']}}
							@endif
						@endforeach
					</td>
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
		<div class="c-box--800Half">
			<table class="table c-tbl c-tbl--790">
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
	<p class="c-ttl--02">請求先情報</p>
	<div class="d-table">
		<div class="c-box--800Half">
			<table class="table c-tbl c-tbl--790">
				<tr>
					<th class="c-box--150">顧客ID</th>
					<td colspan="2">{{$editRow['m_cust_id_billing'] ?? ''}}</td>
				</tr>
				<tr>
					<th class="must">電話番号</th>
					<td>{{$editRow['billing_tel1'] ?? ''}}</td>
					<td>{{$editRow['billing_tel2'] ?? ''}}</td>
				</tr>
				<tr>
					<th>FAX番号</th>
					<td colspan="2">{{$editRow['billing_fax'] ?? ''}}</td>
				</tr>
				<tr>
					<th>フリガナ</th>
					<td colspan="2">{{$editRow['billing_name_kana'] ?? ''}}</td>
				</tr>
				<tr>
					<th class="must">名前</th>
					<td colspan="2">{{$editRow['billing_name'] ?? ''}}</td>
				</tr>
				<tr>
					<th>メールアドレス</th>
					<td colspan="2">
						{{$editRow['billing_email1'] ?? ''}}
						<br>
						{{$editRow['billing_email2'] ?? ''}}
					</td>
				</tr>
				<tr>
					<th>顧客ランク</th>
					<td colspan="2">
						@foreach($viewExtendData['cust_runk_list']??[] as $elm)
							@if(($editRow['billing_cust_runk_id'] ?? '')==$elm['m_itemname_types_id'])
								{{$elm['m_itemname_type_name']}}
							@endif
						@endforeach
					</td>
				</tr>
				<tr>
					<th>要注意区分</th>
					<td colspan="2">
						@if(array_key_exists('billing_alert_cust_type',$editRow))
						@if($editRow['billing_alert_cust_type']=='0') {{'通常'}} @endif
						@if($editRow['billing_alert_cust_type']=='1') {{'要確認'}} @endif
						@if($editRow['billing_alert_cust_type']=='2') {{'受注不可'}} @endif
						@endif
					</td>
				</tr>
				<tr>
					<th>請求書送付</th>
					<td colspan="2">
						{{$editRow['invoice_sending'] ?? ''}}
					</td>
				</tr>
				</table>
		</div>
		<div class="c-box--800Half">
			<table class="table c-tbl c-tbl--790">
				<tr>
					<th class="c-box--150 must">郵便番号</th>
					<td>{{$editRow['billing_postal'] ?? ''}}</td>
				</tr>
				<tr>
					<th class="must">都道府県</th>
					<td>{{$editRow['billing_address1'] ?? ''}}</td>
				</tr>
				<tr>
					<th class="must">市区町村</th>
					<td>{{$editRow['billing_address2'] ?? ''}}</td>
				</tr>
				<tr>
					<th>番地</th>
					<td>{{$editRow['billing_address3'] ?? ''}}</td>
				</tr>
				<tr>
					<th>建物名</th>
					<td>{{$editRow['billing_address4'] ?? ''}}</td>
				</tr>
				<tr>
					<th>法人名・団体名</th>
					<td>{{$editRow['billing_corporate_name'] ?? ''}}</td>
				</tr>
				<tr>
					<th>部署名</th>
					<td>{{$editRow['billing_division_name'] ?? ''}}</td>
				</tr>
				<tr>
					<th>勤務先電話番号</th>
					<td>{{$editRow['billing_corporate_tel'] ?? ''}}</td>
				</tr>
				<tr>
					<th>顧客備考</th>
					<td>{!! nl2br(e($editRow['billing_cust_note'])) !!}</td>
				</tr>
			</table>
		</div>
	</div><!-- /.d-table -->
	<div id="destination_area" class="c-box--1600">
		<ul class="nav nav-tabs">
			@php
				$destIndex = -1;
			@endphp
			@foreach($editRow['register_destination']??[] as $registerDestination)
				@php
				$destIndex++;
				@endphp
				<li class="destination_tab"><a id="dest_tab_{{$destIndex}}" href="#tabs-{{$destIndex}}" data-toggle="tab">{{$registerDestination['destination_tab_display_name'] ?? ''}}</a></li>
			@endforeach
		</ul>
		<div class="tab-content tabs-inner destination_tab_body" style="padding:5px;">
			@php
				$destIndex = -1;
			@endphp
			@foreach($editRow['register_destination']??[] as $registerDestination)
				@php
				$destIndex++;
				@endphp
				<!-- tabs-{{$destIndex}} start -->
				<div class="tab-pane destination_tab_data" id="tabs-{{$destIndex}}">
					<div class="c-box--full">
						<p class="c-ttl--02">送付先情報</p>
					</div>
					<div class="d-table c-box--full">
						<div class="c-box--800Half">
							<table class="table table-bordered c-tbl c-tbl--790">
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
										@foreach($viewExtendData['delivery_type_list'] as $deliveryType)
										@if(!empty($registerDestination['m_delivery_type_id']) && $deliveryType['m_delivery_types_id']==$registerDestination['m_delivery_type_id'])
										{{$deliveryType['m_delivery_type_name']}}
										@endif
										@endforeach
									</td>
								</tr>
								<tr>
									<th>配送希望日</th>
									<td>{{$registerDestination['deli_hope_date'] ?? ''}}</td>
								</tr>
								<tr>
									<th>配送業者別希望時間帯</th>
									<td>
										@foreach($viewExtendData['delivery_hope_timezone_list'] as $deliveryTimehope)
										@if(!empty($registerDestination['m_delivery_time_hope_id']) && $deliveryTimehope['m_delivery_time_hope_id'] == $registerDestination['m_delivery_time_hope_id'])
										{{$deliveryTimehope['delivery_company_time_hope_name']}}
										@endif
										@endforeach
									</td>
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
									<th>分割配送する</th>
									<td>@if($registerDestination['partial_deli_flg']??''== 1){{'する'}}@else{{'しない'}}@endif</td>
								</tr>
								<tr>
									<th>キャンペーン対象</th>
									<td>
										{{ \App\Enums\CampaignFlgEnum::tryfrom( $registerDestination['campaign_flg'] ?? null )?->label() }}
									</td>
								</tr>
								<tr>
									<th>出荷保留</th>
									<td>@if($registerDestination['pending_flg']??''== 1){{'あり'}}@else{{'なし'}}@endif</td>
								</tr>
								<tr>
									<th>送り主名</th>
									<td>{{$registerDestination['sender_name'] ?? ''}}</td>
								</tr>
								<tr>
									<th>配送種別</th>
									<td>
										@if($registerDestination['total_deli_flg']??''== 1)
										同梱配送　
										{{ \App\Enums\ThreeTemperatureZoneTypeEnum::tryfrom( $registerDestination['total_temperature_zone_type'] ?? null )?->label() }}
										@endif
									</td>
								</tr>
							</table>
						</div>
						<div class="c-box--800Half">
							<table class="table table-bordered c-tbl c-tbl--790">
								<tr>
									<th class="c-box--150 must">郵便番号</th>
									<td>{{$registerDestination['destination_postal'] ?? ''}}</td>
								</tr>
								<tr>
									<th class="c-box--150">フリガナ</th>
									<td>{{$registerDestination['destination_address1_kana'] ?? ''}}</td>
								</tr>
								<tr>
									<th class="c-box--150 must">都道府県</th>
									<td>{{$registerDestination['destination_address1'] ?? ''}}</td>
								</tr>
								<tr>
									<th class="c-box--150">フリガナ</th>
									<td>{{$registerDestination['destination_address2_kana'] ?? ''}}</td>
								</tr>
								<tr>
									<th class="c-box--150 must">市区町村</th>
									<td>{{$registerDestination['destination_address2'] ?? ''}}</td>
								</tr>
								<tr>
									<th class="c-box--150">番地</th>
									<td>{{$registerDestination['destination_address3'] ?? ''}}</td>
								</tr>
								<tr>
									<th class="c-box--150">建物名</th>
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
					</div>
					<div id="line-05"></div>
					<div class="c-box--full u-mt--ss">
						<p class="c-ttl--02">受注明細情報</p>
					</div>
					<div class="d-table c-box--full">
						<table class="table table-bordered c-tbl c-tbl--1580 u-mt--ss detail_sell_table">
							<tr class="nowrap">
								<th class="c-box--60"></th>
								<th class="c-box--220 must">販売コード</th>
								<th class="c-box--450 must">販売名</th>
								<th class="c-box--110">販売単価</th>
								<th class="c-box--110 must">数量</th>
								<th class="c-box--110">販売金額</th>
								<th class="c-box--130">在庫状態</th>
								<th class="c-box--130">クーポンID</th>
								<th class="c-box--110">クーポン金額</th>
								<th class="">種別</th>
							</tr>
							@php 
							$dtlIndex = -1;
							$register_destination_amount = 0;
							@endphp
							@foreach($registerDestination['register_detail']??[] as $registerDetail)
							@php 
								$dtlIndex++;
							@endphp
							<tr class="detail_area detail_row_{{$destIndex}}_{{$dtlIndex}}" data-index="{{$destIndex}}" data-detail_index="{{$dtlIndex}}">
								<td rowspan="3" class="u-center">
									@if(!empty($registerDetail['cancel_timestamp']) && str_starts_with($registerDetail['cancel_timestamp'],'0000-00-00') == false)
									<span class="u-center font-FF0000">削除済</span>
									@elseif($registerDetail['cancel_flg'] == '1')
									<span class="u-center font-FF0000">削除</span>
									@else
									@php $register_destination_amount += $registerDetail['order_sell_amount']??0 @endphp
									@endif
								</td>
								<td class="u-vam">
									{{$registerDetail['sell_cd']}}
								</td>
								<td class="u-vam">
									{{$registerDetail['sell_name']}}
								</td>
								<td class="u-vam u-right">
									{{number_format($registerDetail['order_sell_price']??'0')}}
								</td>
								<td class="u-vam u-right">
									{{number_format($registerDetail['order_sell_vol']??'0')}}
								</td>
								<td class="u-vam u-right">
									{{number_format($registerDetail['order_sell_amount']??'0')}}
								</td>
								<td class="u-vam">
									<a id="drawing_status_{{$destIndex}}-{{$dtlIndex}}" data-sellcd="{{$registerDetail['sell_cd']??''}}" data-rowid="{{$destIndex}}-{{$dtlIndex}}" style="cursor: pointer" data-href="{{config('env.app_subsys_url.order')}}order/stockinfo/id/{{$registerDetail['sell_cd'] ?? ''}}/variation/{{$registerDetail['variation_values'] or '__'}}/itemid/0">{{$registerDetail['drawing_status_name'] ?? ''}}</a>
								</td>
								<td class="u-vam">
									{{$registerDetail['order_dtl_coupon_id']}}
								</td>
								<td class="u-vam u-right">
									{{number_format($registerDetail['order_dtl_coupon_price']??'0')}}
								</td>
								<td class="u-vam">
									@foreach($viewExtendData['attachment_group']??[] as $val)
									@if($val['m_itemname_types_id'] == $registerDetail['attachment_item_group_id'])
									{{$val['m_itemname_type_name']}}
									@endif
									@endforeach
								</td>
							</tr>
							<tr class="detail_area2 detail_row_{{$destIndex}}_{{$dtlIndex}}">
								<td rowspan="2">
									<div class="detail_item_thumb">
										@if(($registerDetail['image_path']??'') != '')
											<div class='item-image-preview'>
												<img src="{{'/'.config('filesystems.resources_dir').'/'.$esmSessionManager->getAccountCode().'/image/page/'.$registerDetail['m_ami_page_id'].'/'.$registerDetail['image_path'] }}" class="action_item_zoom">
												<span class="item-image-preview-glass action_item_zoom">
													<span class="glyphicon glyphicon-zoom-in" aria-hidden="true"></span>
												</span>
											</div>
										@endif
									</div>
								</td>
								<td rowspan="2">
									<div class="detail_item_html">
										{!!$registerDetail['page_desc']??''!!}
									</div>
								</td>
								<th>熨斗</th>
								<td colspan="6">
									@if($registerDetail['order_dtl_noshi']['m_noshi_format_id']??'' != "")
									<span class="noshi_label">熨斗種類：</span>
									<span class="noshi_value">{{$registerDetail['order_dtl_noshi']['m_noshi_format_name']}}</span>
									<span class="noshi_label"> / </span>
									<span class="noshi_label">名入れパターン：</span>
									<span class="noshi_value">{{$registerDetail['order_dtl_noshi']['m_noshi_naming_pattern_name']}}</span>
									<span class="noshi_label"> / </span>
									<span class="noshi_label">貼付/同梱：</span>
									<span class="noshi_value">{{$registerDetail['order_dtl_noshi']['attach_flg'] == '1'?'同梱':'貼付'}}</span>
									<span class="noshi_label"> / </span>
									<br><span class="noshi_label">表書き：</span>
									<span class="noshi_value">{{$registerDetail['order_dtl_noshi']['omotegaki']}}</span><br>
									@if($registerDetail['order_dtl_noshi']['company_name_count']??0 > 0)
									<span class="noshi_label"> / </span>
									<span class="noshi_label">会社名：</span>
									@php
									$values = [];
									@endphp
									@for($idx=1;$idx<=$registerDetail['order_dtl_noshi']['company_name_count'];$idx++)
									@php
									$values[] = $registerDetail['order_dtl_noshi']['company_name'.$idx];
									@endphp
									@endfor
									<span class="noshi_value">{{implode('、',$values)}}</span>
									@endif
									@if($registerDetail['order_dtl_noshi']['section_name_count']??0 > 0)
									<span class="noshi_label"> / </span>
									<span class="noshi_label">部署名：</span>
									@php
									$values = [];
									@endphp
									@for($idx=1;$idx<=$registerDetail['order_dtl_noshi']['section_name_count'];$idx++)
									@php
									$values[] = $registerDetail['order_dtl_noshi']['section_name'.$idx];
									@endphp
									@endfor
									<span class="noshi_value">{{implode('、',$values)}}</span>
									@endif
									@if($registerDetail['order_dtl_noshi']['title_count']??0 > 0)
									<span class="noshi_label"> / </span>
									<span class="noshi_label">肩書：</span>
									@php
									$values = [];
									@endphp
									@for($idx=1;$idx<=$registerDetail['order_dtl_noshi']['title_count'];$idx++)
									@php
									$values[] = $registerDetail['order_dtl_noshi']['title'.$idx];
									@endphp
									@endfor
									<span class="noshi_value">{{implode('、',$values)}}</span>
									@endif
									@if($registerDetail['order_dtl_noshi']['f_name_count']??0 > 0)
									<span class="noshi_label"> / </span>
									<span class="noshi_label">苗字：</span>
									@php
									$values = [];
									@endphp
									@for($idx=1;$idx<=$registerDetail['order_dtl_noshi']['f_name_count'];$idx++)
									@php
									$values[] = $registerDetail['order_dtl_noshi']['firstname'.$idx];
									@endphp
									@endfor
									<span class="noshi_value">{{implode('、',$values)}}</span>
									@endif
									@if($registerDetail['order_dtl_noshi']['name_count']??0 > 0)
									<span class="noshi_label"> / </span>
									<span class="noshi_label">名前：</span>
									@php
									$values = [];
									@endphp
									@for($idx=1;$idx<=$registerDetail['order_dtl_noshi']['name_count'];$idx++)
									@php
									$values[] = $registerDetail['order_dtl_noshi']['name'.$idx];
									@endphp
									@endfor
									<span class="noshi_value">{{implode('、',$values)}}</span>
									@endif
									@if($registerDetail['order_dtl_noshi']['ruby_count']??0 > 0)
									<span class="noshi_label"> / </span>
									<span class="noshi_label">ルビ：</span>
									@php
									$values = [];
									@endphp
									@for($idx=1;$idx<=$registerDetail['order_dtl_noshi']['ruby_count'];$idx++)
									@php
									$values[] = $registerDetail['order_dtl_noshi']['ruby'.$idx];
									@endphp
									@endfor
									<span class="noshi_value">{{implode('、',$values)}}</span>
									@endif
									@endif
								</td>
							</tr>
							<tr class="detail_area3 detail_row_{{$destIndex}}_{{$dtlIndex}}">
								<th>付属品</th>
								<td colspan="6">
									@foreach($registerDetail['order_dtl_attachment_item']??[] as $attachmentItem)
									@if($attachmentItem['display_flg']??'' == 1)
									<span class="noshi_label">付属品コード：</span>
									<span class="noshi_value">{{$attachmentItem['attachment_item_cd']??''}}</span>
									<span class="noshi_label"> / </span>
									<span class="noshi_label">付属品名：</span>
									<span class="noshi_value">{{$attachmentItem['attachment_item_name']??''}}</span>
									<span class="noshi_label"> / </span>
									<span class="noshi_label">数量</span>
									<span class="noshi_value">{{$attachmentItem['attachment_vol']??''}}</span><br>
									@endif
									@endforeach
								</td>
							</tr>
							@endforeach
							<tr>
								<td colspan="9" class="u-vam u-right">小計</td>
								<td class="u-vam u-right">
									<span>{{number_format($register_destination_amount)}}</span>
								</td>
							</tr>
							<tr>
								<td colspan="9" class="u-vam u-right">送料</td>
								<td class="u-vam u-right">
									<span>{{number_format($registerDestination['shipping_fee'])}}</span>
								</td>
							</tr>
							<tr>
								<td colspan="9" class="u-vam u-right">手数料</td>
								<td class="u-vam u-right">
									<span>{{number_format($registerDestination['payment_fee'])}}</span>
								</td>
							</tr>
							<tr>
								<td colspan="9" class="u-vam u-right">包装料</td>
								<td class="u-vam u-right">
									<span>{{number_format($registerDestination['wrapping_fee'])}}</span>
								</td>
							</tr>					
						</table>
					</div>
				</div>
				<!-- tabs-{{$destIndex}} end -->
				@endforeach
		</div>
	</div>
	<div class="u-mt--ss c-box--1600"><p class="c-ttl--02">金額情報</p></div>
	<div class="u-mt--ss c-box--1600">
		<table class="table table-bordered c-tbl c-tbl--1580 u-mt--ss">
			<tr class="nowrap">
				<th class="c-box--120">商品金額計</th>
				<td class="u-right c-box--100">
					{{number_format($editRow['sell_total_price'])}}
				</td>
				<th class="c-box--120">消費税(8%)</th>
				<td class="u-right c-box--100">
					{{number_format($editRow['tax_price08'])}}
				</td>
				<th class="c-box--120">消費税(10%)</th>
				<td class="u-right c-box--100">
					{{number_format($editRow['tax_price10'])}}
				</td>
				<th class="c-box--120">送料</th>
				<td class="u-right c-box--100">
					{{number_format($editRow['shipping_fee'])}}
				</td>
				<th class="c-box--120">手数料</th>
				<td class="u-right c-box--100">
					{{number_format($editRow['payment_fee'])}}
				</td>
				<th class="c-box--120">包装料</th>
				<td class="u-right -box--100">
					{{number_format($editRow['package_fee'])}}
				</td>
				<th class="c-box--120">合計金額</th>
				<td class="u-right c-box--100">
					{{number_format($editRow['total_price'])}}
				</td>
			</tr>
			<tr>
				<th>割引金額</th>
				<td class="u-right font-FF0000">
					{{number_format($editRow['discount'])}}
				</td>
				<th>ストアクーポン</th>
				<td class="u-right font-FF0000">
					{{number_format($editRow['use_coupon_store'])}}
				</td>
				<th>モールクーポン</th>
				<td class="u-right font-FF0000">
					{{number_format($editRow['use_coupon_mall'])}}
				</td>
				<th>クーポン合計</th>
				<td class="u-right font-FF0000">
					{{number_format($editRow['total_use_coupon'])}}
				</td>
				<th>利用ポイント</th>
				<td class="u-right font-FF0000">
					{{number_format($editRow['use_point'])}}
				</td>
				<th colspan="2">請求金額</th>
				<td colspan="2" class="u-right">
					{{number_format($editRow['order_total_price'])}}
				</td>
			</tr>
		</table>
	</div>
	<div class="u-mt--ss">
		<p class="c-ttl--02">決済情報</p>
	</div>
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
			<th class="c-box--200">請求メモ</th>
			<td>
				{{$editRow['billing_comment'] ?? ''}}
			</td>
		</tr>
		<tr>
			<th class="c-box--200">後払い決済 取引ID</th>
			<td>
				{{$editRow['payment_transaction_id'] ?? ''}}
			</td>
		</tr>
		<tr>
			<th class="c-box--200">支払回数</th>
			<td>
				{{$editRow['card_pay_times'] ?? ''}}
			</td>
		</tr>
		<tr>
			<th class="c-box--200">後払い決済請求書送付方法</th>
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
@include('common.elements.datetime_picker_script')
@push('js')
<script src="{{ esm_internal_asset('js/order/gfh_1207/NEOSM0212.js') }}"></script>
@endpush
@endsection