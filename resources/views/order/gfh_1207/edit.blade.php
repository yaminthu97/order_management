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

@push('css')
<link rel="stylesheet" href="{{ esm_internal_asset('css/common/gfh_1207/check_textbyte.css') }}">
<link rel="stylesheet" href="{{ esm_internal_asset('css/order/gfh_1207/app.css') }}">
@endpush

@section('content')
@php
$esmSessionManager = new App\Services\EsmSessionManager();
@endphp
<link rel="stylesheet" href="{{config('env.app_subsys_url.order')}}css/Order/v1_0/NEOSM0211.css">
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
	.item_image_src {
		width:600px;
	}
	.nav-tabs > li > a, .nav-tabs > li > a:hover, .nav-tabs > li > a:focus {
		background-color:#F8F8F8;
		border: 1px solid #ccc;
	}
	.nav-tabs > li > a:hover {
		background-color:#ddd;
	}
	.nav-tabs > li.active > a, .nav-tabs > li.active > a:hover, .nav-tabs > li.active > a:focus {
		background-color:#337ab7;
		color:white;
	}
	.btn_circle {
		width:33px;
		height:33px;
		border-radius:50%;
		padding:0;
	}
	label:has(input:disabled){
		color:lightgray;
	}
	.button_margin {
		margin-right	:10px;
	}
</style>
<div id="dialogWindow" style="display: none"><div class="dialog_body"></div></div>
@include('order.base.image-modal')
<div id="dialogAttachmentItemWindow" style="display: none">
	<input type="hidden" class="index">
	<input type="hidden" class="detail_index">
	<input type="hidden" class="ami_ec_page_id">
	<input type="hidden" class="t_order_dtl_id">
	<input type="hidden" class="group_id">
	<div class="dialog_body">
		<table class="table table-bordered c-tbl c-tbl--1000">
			<thead>
				<tr>
					<th class="c-box--200">付属品コード</th>
					<th>付属品名</th>
					<th class="c-box--80">数量</th>
					<th class="c-box--80">&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<tr class="item_detail_first">
					<td class="u-vam">
						<input type="text" class="form-control u-input--small search_item_cd" value="">
						<input type="button" class="btn btn-success action_attachment_item_search" value="検索">
					</td>
					<td><input type="text" name="item_name" class="form-control u-input--full item_name"></td>
					<td><input type="text" name="vol" class="form-control u-input--small u-right vol"></td>
					<td>&nbsp;</td>
				</tr>
			</tbody>
		</table>
		<button type="button" class="btn btn-default u-mt--ss button_margin action_attachment_item_setup_cancel">キャンセル</button>
		<button type="button" class="btn btn-default u-mt--ss button_margin action_attachment_item_setup_default">デフォルトに戻す</button>
		<button type="button" class="btn btn-success u-mt--ss action_attachment_item_setup">登録</button>
	</div>
</div>
<div id="dialogNoshiWindow" style="display: none">
	<input type="hidden" class="index">
	<input type="hidden" class="detail_index">
	<input type="hidden" class="ami_ec_page_id">
	<input type="hidden" class="attach_flg">
	<input type="hidden" class="noshi_id" value="">
	<input type="hidden" class="noshi_detail_id" value="">
	<input type="hidden" class="company_name_count" value="0">
	<input type="hidden" class="section_name_count" value="0">
	<input type="hidden" class="title_count" value="0">
	<input type="hidden" class="f_name_count" value="0">
	<input type="hidden" class="name_count" value="0">
	<input type="hidden" class="ruby_count" value="0">
	<div class="dialog_body">
		<table class="table table-bordered c-tbl c-tbl--1000">
			<tbody>
				<tr>
					<th class="c-box--200">熨斗種類</th>
					<td class="c-box--300">
						<select name="m_noshi_format_id" class="form-control u-input--full m_noshi_format_id">
						</select>
					</td>
					<th class="c-box--200">貼付/同梱</th>
					<td class="c-box--300">
						<select name="attach_flg" class="form-control u-input--full attach_flg">
							<option value="0">貼付</option>
							<option value="1">同梱</option>
						</select>
					</td>
				</tr>
				<tr>
					<th>名入れパターン</th>
					<td>
						<select name="m_noshi_naming_pattern_id" class="form-control u-input--full m_noshi_naming_pattern_id">
						</select>
					</td>
					<th class="c-box--100">表書き</th>
					<td class="c-box--400">
						<input type="text" name="omotegaki" class="form-control u-input--full omotegaki">
					</td>
				</tr>
				<tr class="company_name company_name1">
					<th>会社名</th>
					<td colspan="3">
						<input type="text" name="company_name1" class="form-control u-input--full company_name1">
					</td>
				</tr>
				<tr class="company_name company_name2">
					<th>会社名2</th>
					<td colspan="3">
						<input type="text" name="company_name2" class="form-control u-input--full company_name2">
					</td>
				</tr>
				<tr class="company_name company_name3">
					<th>会社名3</th>
					<td colspan="3">
						<input type="text" name="company_name3" class="form-control u-input--full company_name3">
					</td>
				</tr>
				<tr class="company_name company_name4">
					<th>会社名4</th>
					<td colspan="3">
						<input type="text" name="company_name4" class="form-control u-input--full company_name4">
					</td>
				</tr>
				<tr class="company_name company_name5">
					<th>会社名5</th>
					<td colspan="3">
						<input type="text" name="company_name5" class="form-control u-input--full company_name5">
					</td>
				</tr>
				<tr class="section_name section_name1">
					<th>部署名</th>
					<td colspan="3">
						<input type="text" name="section_name1" class="form-control u-input--full section_name1">
					</td>
				</tr>
				<tr class="section_name section_name2">
					<th>部署名2</th>
					<td colspan="3">
						<input type="text" name="section_name2" class="form-control u-input--full section_name2">
					</td>
				</tr>
				<tr class="section_name section_name3">
					<th>部署名3</th>
					<td colspan="3">
						<input type="text" name="section_name3" class="form-control u-input--full section_name3">
					</td>
				</tr>
				<tr class="section_name section_name4">
					<th>部署名4</th>
					<td colspan="3">
						<input type="text" name="section_name4" class="form-control u-input--full section_name4">
					</td>
				</tr>
				<tr class="section_name section_name5">
					<th>部署名5</th>
					<td colspan="3">
						<input type="text" name="section_name5" class="form-control u-input--full section_name5">
					</td>
				</tr>
				<tr class="title title1">
					<th>肩書</th>
					<td colspan="3">
						<input type="text" name="title1" class="form-control u-input--full title1">
					</td>
				</tr>
				<tr class="title title2">
					<th>肩書2</th>
					<td colspan="3">
						<input type="text" name="title2" class="form-control u-input--full title2">
					</td>
				</tr>
				<tr class="title title3">
					<th>肩書3</th>
					<td colspan="3">
						<input type="text" name="title3" class="form-control u-input--full title3">
					</td>
				</tr>
				<tr class="title title4">
					<th>肩書4</th>
					<td colspan="3">
						<input type="text" name="title4" class="form-control u-input--full title4">
					</td>
				</tr>
				<tr class="title title5">
					<th>肩書5</th>
					<td colspan="3">
						<input type="text" name="title5" class="form-control u-input--full title5">
					</td>
				</tr>
				<tr class="firstname firstname1">
					<th>苗字</th>
					<td colspan="3">
						<input type="text" name="firstname1" class="form-control u-input--full firstname1">
					</td>
				</tr>
				<tr class="firstname firstname2">
					<th>苗字2</th>
					<td colspan="3">
						<input type="text" name="firstname2" class="form-control u-input--full firstname2">
					</td>
				</tr>
				<tr class="firstname firstname3">
					<th>苗字3</th>
					<td colspan="3">
						<input type="text" name="firstname3" class="form-control u-input--full firstname3">
					</td>
				</tr>
				<tr class="firstname firstname4">
					<th>苗字4</th>
					<td colspan="3">
						<input type="text" name="firstname4" class="form-control u-input--full firstname4">
					</td>
				</tr>
				<tr class="firstname firstname5">
					<th>苗字5</th>
					<td colspan="3">
						<input type="text" name="firstname5" class="form-control u-input--full firstname5">
					</td>
				</tr>
				<tr class="name name1">
					<th>名前</th>
					<td colspan="3">
						<input type="text" name="name1" class="form-control u-input--full name1">
					</td>
				</tr>
				<tr class="name name2">
					<th>名前2</th>
					<td colspan="3">
						<input type="text" name="name2" class="form-control u-input--full name2">
					</td>
				</tr>
				<tr class="name name3">
					<th>名前3</th>
					<td colspan="3">
						<input type="text" name="name3" class="form-control u-input--full name3">
					</td>
				</tr>
				<tr class="name name4">
					<th>名前4</th>
					<td colspan="3">
						<input type="text" name="name4" class="form-control u-input--full name4">
					</td>
				</tr>
				<tr class="name name5">
					<th>名前5</th>
					<td colspan="3">
						<input type="text" name="name5" class="form-control u-input--full name5">
					</td>
				</tr>
				<tr class="ruby ruby1">
					<th>ルビ</th>
					<td colspan="3">
						<input type="text" name="ruby1" class="form-control u-input--full ruby1">
					</td>
				</tr>
				<tr class="ruby ruby2">
					<th>ルビ2</th>
					<td colspan="3">
						<input type="text" name="ruby2" class="form-control u-input--full ruby2">
					</td>
				</tr>
				<tr class="ruby ruby3">
					<th>ルビ3</th>
					<td colspan="3">
						<input type="text" name="ruby3" class="form-control u-input--full ruby3">
					</td>
				</tr>
				<tr class="ruby ruby4">
					<th>ルビ4</th>
					<td colspan="3">
						<input type="text" name="ruby4" class="form-control u-input--full ruby4">
					</td>
				</tr>
				<tr class="ruby ruby5">
					<th>ルビ5</th>
					<td colspan="3">
						<input type="text" name="ruby5" class="form-control u-input--full ruby5">
					</td>
				</tr>
			</tbody>
		</table>
		<button type="button" class="btn btn-default u-mt--ss button_margin action_noshi_setup_cancel">キャンセル</button>
		<button type="button" class="btn btn-success u-mt--ss action_noshi_setup">登録</button>
	</div>
</div>
<input type="hidden" id="m_prefectures" value="{{json_encode($viewExtendData['m_prefectures'])}}">
<form method="post" name="Form1" action="">
<!--
@foreach ($errors->keys() as $k)
  <li>{{$k}}:{{$errors->first($k)}}</li>
@endforeach	
-->
	{{ csrf_field() }}
<!--
	<input type="hidden" name="item_price_for_free_delivery_fee" id="item_price_for_free_delivery_fee" value="{{ old('item_price_for_free_delivery_fee', $viewExtendData['shop_list'][0]['item_price_for_free_delivery_fee'] ?? '0') }}">
	<input type="hidden" name="base_delivery_fee" id="base_delivery_fee" value="{{ old('base_delivery_fee', $viewExtendData['shop_list'][0]['base_delivery_fee'] ?? '0') }}">
-->

	<input type="hidden" name="delivery_readtime" id="delivery_readtime" value="{{ $viewExtendData['delivery_readtime'] }}">
	<input type="hidden" name="previous_url" value="{{ old('previous_url', $editRow['previous_url'] ?? '') }}">
	<input type="hidden" name="previous_subsys" value="{{ old('previous_subsys', $editRow['previous_subsys'] ?? '') }}">
	<input type="hidden" name="previous_key" value="{{ old('previous_key', $editRow['previous_key'] ?? '') }}">
	<input type="hidden" name="sell_find_index" id="sell_find_index" value="{{ old('sell_find_index', $editRow['sell_find_index'] ?? '') }}" data-href="{{config('env.app_subsys_url.order')}}order/sales">
	<input type="hidden" name="progress_type" value="{{ old('progress_type', $editRow['progress_type'] ?? '') }}">
	<input type="hidden" name="scroll_top" value="" class="st">
	<input type="hidden" name="sales_param" id="sales_param" value="{{ old('sales_param', $editRow['sales_param'] ?? '') }}">
	<input type="hidden" name="return_flg" value="{{ old('return_flg', $editRow['return_flg'] ?? '') }}">
	<input type="hidden" name="data_key_id" value="{{ old('data_key_id', $editRow['data_key_id'] ?? '') }}">
	<input type="hidden" name="discount_rate" id="discount_rate" class="form-control u-input--mid" readonly value="{{ old('discount_rate',  $customer['discount_rate'] ?? '0.0') }}">&nbsp;
	<div class="c-box--1600 u-mt--xs">
		@isset($editRow['message'])
		<div class="c-box--full">
			<span class="font-FF0000">{{ old('message', $editRow['message'] ?? '') }}</span>
		</div>
		@endisset
		@include('common.elements.error_tag', ['name' => 'other_error'])
		@unless(empty(old('t_order_hdr_id',$editRow['t_order_hdr_id']??'')))
		<div id="line-01"></div>
		<div class="u-mt--ss">
			<p class="c-ttl--02">ステータス</p>
		</div>
		<div class="d-inline-block">
			進捗状況
			<ol class="stepBar step9">
				<li class="step @if(old('progress_type',$editRow['progress_type']??'') == 0){{'current'}}@endif">確認待</li>
				<li class="step @if(old('progress_type',$editRow['progress_type']??'') == 10){{'current'}}@endif">与信待</li>
				<li class="step @if(old('progress_type',$editRow['progress_type']??'') == 20){{'current'}}@endif">前払入金待</li>
				<li class="step @if(old('progress_type',$editRow['progress_type']??'') == 30){{'current'}}@endif">引当待</li>
				<li class="step @if(old('progress_type',$editRow['progress_type']??'') == 40){{'current'}}@endif">出荷待</li>
				<li class="step @if(old('progress_type',$editRow['progress_type']??'') == 50){{'current'}}@endif">出荷中</li>
				<li class="step @if(old('progress_type',$editRow['progress_type']??'') == 60){{'current'}}@endif">出荷済み</li>
				<li class="step @if(old('progress_type',$editRow['progress_type']??'') == 70){{'current'}}@endif">後払入金待</li>
				<li class="step @if(old('progress_type',$editRow['progress_type']??'') == 80){{'current'}}@endif">完了</li>
			</ol>
		</div>
		<div class="d-inline-block" style="margin-left: 10px;">
			<ol class="stepBar step2">
				<li class="step-nomal @if(old('progress_type',$editRow['progress_type']??'') == 90){{'current'}}@endif">キャンセル</li>
				<li class="step-nomal @if(old('progress_type',$editRow['progress_type']??'') == 100){{'current'}}@endif">返品</li>
			</ol>
		</div>
		<div class="tag-box c-tbl-border-all">
		</div><!-- /.tag-box-->
		タグ追加
		<select name="m_order_tag_id" id="m_order_tag_id" class="form-control u-input--mid u-mr--xs u-mt--xs" style="margin-left: 10px;">
			@foreach($viewExtendData['m_tag_list']??[] as $mOrderTag)
			<option value="{{$mOrderTag['m_order_tag_id']}}" @if (isset($editRow['m_order_tag_id']) && $editRow['m_order_tag_id']==$mOrderTag['m_order_tag_id']){{'selected'}}@endif>{{$mOrderTag['tag_display_name']}}</option>
			@endforeach
		</select>
		<button type="button" class="btn btn-success u-mr--xs action_append_tag">タグ追加</button>
		<button type="button" class="btn btn-danger action_remove_tag">チェックしたタグを削除</button>

		@endunless
		<div id="line-02"></div>
		<div class="u-mt--sm">
			<p class="c-ttl--02">受注情報</p>
		</div>
		<div class="d-table">
			<div class="c-box--800Half">
				<table class="table c-tbl c-tbl--790">
					<tr>
						<th class="c-box--150">受注ID</th>
						<td>
							{{ old('t_order_hdr_id', $editRow['t_order_hdr_id'] ?? '') }}
							<input type="hidden" id="t_order_hdr_id" name="t_order_hdr_id" value="{{ old('t_order_hdr_id', $editRow['t_order_hdr_id'] ?? '') }}">
							@include('common.elements.error_tag', ['name' => 't_order_hdr_id'])
						</td>
					</tr>
					<tr>
						<th class="must">受注日時</th>
						<td>
							<div class='c-box--218'>
								<div class='input-group date order-datetime-picker' id='datetimepicker_datetime'>
									<input type='text' name="order_datetime" id="order_datetime" class="form-control c-box--180" value="{{ old('order_datetime', $editRow['order_datetime'] ?? date('Y/m/d H:i')) }}" />
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
								<option></option>
								
								@include('common.elements.NEOSM211_option_list', ['arrayName' => $viewExtendData['m_operators']??[], 'currentId' => old('order_operator_id',$editRow['order_operator_id']??$viewExtendData['m_operators_id']) ])
							</select>
							@include('common.elements.error_tag', ['name' => 'order_operator_id'])
						</td>
					</tr>
					<tr>
						<th>受注方法</th>
						<td>
							<select name="order_type" id="order_type" class="form-control c-box--300">
								@include('common.elements.NEOSM211_option_list', ['arrayName' => $viewExtendData['m_ordertypes']??[], 'currentId' => old('order_type',$editRow['order_type']??'')])
							</select>
							@include('common.elements.error_tag', ['name' => 'order_type'])
						</td>
					</tr>
					<tr>
						<th class="must">ECサイト</th>
						<td>
							<select name="m_ecs_id" id="m_ecs_id" class="form-control c-box--300">
								@include('common.elements.NEOSM211_option_list', ['arrayName' => $viewExtendData['m_ecs']??[], 'currentId' => old('m_ecs_id',$editRow['m_ecs_id']??'')])
							</select>
							@include('common.elements.error_tag', ['name' => 'm_ecs_id'])
						</td>
					</tr>
					<tr>
						<th>ECサイト注文ID</th>
						<td>
							<a href="{{$viewExtendData['m_ecs_info']['m_ec_url'] ?? ''}}" target="_blank">{{ old('ec_order_num', $editRow['ec_order_num'] ?? '') }}</a>
							<input type="hidden" name="ec_order_num" value="{{ old('ec_order_num', $editRow['ec_order_num'] ?? '') }}">
							@include('common.elements.error_tag', ['name' => 'ec_order_num'])
						</td>
					</tr>
					<tr>
						<th>販売窓口</th>
						<td>
							<select name="sales_store" id="sales_store" class="form-control c-box--300">
								@include('common.elements.NEOSM211_option_list', ['arrayName' => \Arr::pluck($viewExtendData['m_sales_counter_list'], 'm_itemname_type_name','m_itemname_types_id'), 'currentId' => old('sales_store',$editRow['sales_store']??'')])
							</select>
							@include('common.elements.error_tag', ['name' => 'sales_store'])
						</td>
					</tr>
					<tr>
						<th>領収証宛名</th>
						<td>
							<textarea class="form-control c-box--300" name="receipt_direction" id="receipt_direction" rows="6">{{ old('receipt_direction', $editRow['receipt_direction'] ?? '') }}</textarea>
							@include('common.elements.error_tag', ['name' => 'receipt_direction'])
						</td>
					</tr>
					<tr>
						<th>領収証但し書き</th>
						<td>
							<div class="c-box--300 d-flex" style="align-items: center; justify-content: space-between">
								<span>但し</span>
								<input type="text" name="receipt_proviso" id="receipt_proviso" class="form-control" value="{{ old('receipt_proviso', $editRow['receipt_proviso'] ?? '') }}">
								<span>代として</span>
							</div>
							@include('common.elements.error_tag', ['name' => 'receipt_proviso'])
						</td>
					</tr>
					<tr>
						<th>ギフトフラグ</th>
						<td>
							<select name="gift_flg" id="gift_flg" class="form-control u-input--small">
								<option value="" @if(old('gift_flg',$editRow['gift_flg']??'') == '' ){{'selected'}}@endif>OFF</option>
								<option value="1" @if(old('gift_flg',$editRow['gift_flg']??'') == '1' ){{'selected'}}@endif>ON</option>
							</select>
							@include('common.elements.error_tag', ['name' => 'gift_flg'])
						</td>
					</tr>
				</table>
			</div>
			<div class="cc-box--800Half">
				<table class="table c-tbl c-tbl--790">
					<tr>
						<th class="c-box--150">備考</th>
						<td>
							<textarea name="order_comment" id="order_comment" class="form-control c-box--400" rows="5">{{ old('order_comment', $editRow['order_comment'] ?? '') }}</textarea>
							@include('common.elements.error_tag', ['name' => 'order_comment'])
						</td>
					</tr>
					<tr>
						<th>社内メモ</th>
						<td>
							<textarea name="operator_comment" id="operator_comment" class="form-control c-box--400" rows="5">{{ old('operator_comment', $editRow['operator_comment'] ?? '') }}</textarea>
							@include('common.elements.error_tag', ['name' => 'operator_comment'])
						</td>
					</tr>
					<tr>
						<th>即日配送</th>
						<td>
						
							@unless(empty(old('t_order_hdr_id', $editRow['t_order_hdr_id'] ?? '')))
							<input type="hidden" name="immediately_deli_flg" id="immediately_deli_flg" value="{{ old('immediately_deli_flg', $editRow['immediately_deli_flg'] ?? '') }}">
							<input type="checkbox" name="disabled_immediately_deli_flg" disabled='disabled' @if(old('immediately_deli_flg', $editRow['immediately_deli_flg'] ?? '')==1){{'checked'}}@endif>
							@endunless
							@include('common.elements.error_tag', ['name' => 'immediately_deli_flg'])
						</td>
					</tr>
					<tr>
						<th>楽天スーパーDEAL</th>
						<td>
							@unless(empty(old('t_order_hdr_id', $editRow['t_order_hdr_id'] ?? '')))
							<input type="hidden" name="rakuten_super_deal_flg" id="rakuten_super_deal_flg" value="{{ old('rakuten_super_deal_flg', $editRow['rakuten_super_deal_flg'] ?? '') }}">
							<input type="checkbox" name="disabled_rakuten_super_deal_flg" disabled='disabled' @if(old('rakuten_super_deal_flg',$editRow['rakuten_super_deal_flg']??'')==1){{'checked'}}@endif>
							@endunless
							@include('common.elements.error_tag', ['name' => 'rakuten_super_deal_flg'])
						</td>
					</tr>
					<tr>
						<th>警告注文</th>
						<td>
							@unless(empty(old('t_order_hdr_id', $editRow['t_order_hdr_id'] ?? '')))
							<input type="hidden" name="alert_order_flg" id="alert_order_flg" value="{{ old('alert_order_flg', $editRow['alert_order_flg'] ?? '') }}">
							<input type="checkbox" name="disabled_alert_order_flg" disabled='disabled' @if(old('alert_order_flg',$editRow['alert_order_flg']??'')==1){{'checked'}}@endif>
							@endunless
							@include('common.elements.error_tag', ['name' => 'alert_order_flg'])
						</td>
					</tr>
					<tr>
						<th>見積</th>
						<td>
							<input type="checkbox" name="estimate_flg" value="1" @if(old('estimate_flg',$editRow['estimate_flg']??'')==1){{'checked'}}@endif>
							@include('common.elements.error_tag', ['name' => 'estimate_flg'])
						</td>
					</tr>
					<tr>
						<th>領収書</th>
						<td>
							<div class="radio-inline">
								<label><input type="radio" name="receipt_type" value="0" @if(old('receipt_type',$editRow['receipt_type']??'0')=='0'){{'checked'}}@endif>不要</label>
							</div>
							<div class="radio-inline">
								<label><input type="radio" name="receipt_type" value="1" @if(old('receipt_type',$editRow['receipt_type']??'0')=='1'){{'checked'}}@endif>一括</label>
							</div>
							<div class="radio-inline">
								<label><input type="radio" name="receipt_type" value="2" @if(old('receipt_type',$editRow['receipt_type']??'0')=='2'){{'checked'}}@endif>分割</label>
							</div>
							@include('common.elements.error_tag', ['name' => 'receipt_type'])
						</td>
					</tr>
					<tr>
						<th>キャンペーン</th>
						<td>
							<select name="campaign_flg" id="campaign_flg" class="form-control u-input--small">
								@foreach(\App\Enums\CampaignFlgEnum::cases() as $CampaignFlg)
									<option value="{{ $CampaignFlg->value }}" @if(old('campaign_flg', $editRow['campaign_flg'] ?? \App\Enums\CampaignFlgEnum::SUBJECT->value) == $CampaignFlg->value) selected @endif>{{ $CampaignFlg->label() }}</option>
								@endforeach
							</select>
							@include('common.elements.error_tag', ['name' => 'campaign_flg'])
						</td>
					</tr>
				</table>
			</div>
		</div><!-- /.d-table -->
		<div id="line-03"></div>
		<p class="c-ttl--02">注文主情報</p>
		<div class="d-table">
			<div class="c-box--800Half">
				<table class="table c-tbl c-tbl--790">
					<tr>
						<th>顧客ID</th>
						<td>
							<input type="text" name="m_cust_id" id="m_cust_id" class="form-control u-input--mid" readonly value="{{ old('m_cust_id', $editRow['m_cust_id'] ?? '') }}">&nbsp;
							@include('common.elements.error_tag', ['name' => 'm_cust_id'])
						</td>
					</tr>
					<tr>
						<th class="must">電話番号</th>
						<td>
							<input type="text" name="order_tel1" id="order_tel1" class="form-control u-input--mid" readonly value="{{ old('order_tel1', $editRow['order_tel1'] ?? '') }}">&nbsp;<input type="text" class="form-control u-input--mid" name="order_tel2" id="order_tel2" readonly value="{{ old('order_tel2', $editRow['order_tel2'] ?? '') }}">
							@include('common.elements.error_tag', ['name' => 'order_tel1'])
							@include('common.elements.error_tag', ['name' => 'order_tel2'])
						</td>
					</tr>
					<tr>
						<th>FAX番号</th>
						<td>
							<input type="text" name="order_fax" id="order_fax" class="form-control u-input--mid" readonly value="{{ old('order_fax', $editRow['order_fax'] ?? '') }}">
							@include('common.elements.error_tag', ['name' => 'order_fax'])
						</td>
					</tr>
					<tr>
						<th>フリガナ</th>
						<td>
							<input type="text" name="order_name_kana" id="order_name_kana" class="form-control c-box--300" readonly value="{{ old('order_name_kana', $editRow['order_name_kana'] ?? '') }}">
							@include('common.elements.error_tag', ['name' => 'order_name_kana'])
						</td>
					</tr>
					<tr>
						<th class="must">名前</th>
						<td>
							<input type="text" name="order_name" id="order_name" class="form-control c-box--300" readonly value="{{ old('order_name', $editRow['order_name'] ?? '') }}">
							@include('common.elements.error_tag', ['name' => 'order_name'])
						</td>
					</tr>
					<tr>
						<th>メールアドレス</th>
						<td>
							<input type="text" name="order_email1" id="order_email1" class="form-control c-box--300" readonly value="{{ old('order_email1', $editRow['order_email1'] ?? '') }}">
							@include('common.elements.error_tag', ['name' => 'order_email1'])
							<input type="text" name="order_email2" id="order_email2" class="form-control c-box--300 u-mt--xs" readonly value="{{ old('order_email2', $editRow['order_email2'] ?? '') }}">
							@include('common.elements.error_tag', ['name' => 'order_email2'])
						</td>
					</tr>
					<tr>
						<th>顧客ランク</th>
						<td> 
							<select name="order_cust_runk_id" id="order_cust_runk_id" readonly class="form-control c-box--200">
								@foreach($viewExtendData['cust_runk_list']??[] as $elm)
								<option value="{{$elm['m_itemname_types_id']}}" disabled @if (old('order_cust_runk_id', $editRow['order_cust_runk_id'] ?? '')==$elm['m_itemname_types_id']){{'selected'}}@endif>{{$elm['m_itemname_type_name']}}</option>
								@endforeach
							</select>
							@include('common.elements.error_tag', ['name' => 'order_cust_runk_id'])
						</td>
					</tr>
					<tr>
						<th>要注意区分</th>
						<td>
							<input type="hidden" name="alert_cust_type" id="alert_cust_type" value="{{ old('alert_cust_type', $editRow['alert_cust_type'] ?? '' ) }}">
							<div class="radio-inline">
								<label><input type="radio" name="disabled_alert_cust_type" value="0" disabled @if(old('alert_cust_type', $editRow['alert_cust_type'] ?? '') =='0' ){{'checked'}}@endif>通常</label>
							</div>
							<div class="radio-inline">
								<label><input type="radio" name="disabled_alert_cust_type" value="1" disabled @if(old('alert_cust_type', $editRow['alert_cust_type'] ?? '') =='1' ){{'checked'}}@endif>要確認</label>
							</div>
							<div class="radio-inline">
								<label><input type="radio" name="disabled_alert_cust_type" value="2" disabled @if(old('alert_cust_type', $editRow['alert_cust_type'] ?? '') =='2' ){{'checked'}}@endif>受注不可</label>
							</div>
						</td>
					</tr>
				</table>
			</div>
			<div class="c-box--800Half">
				<table class="table c-tbl c-tbl--790">
					<tr>
						<th class="must">住所</th>
						<td>
							<div class="d-table c-tbl--400">
								<div class="d-table-cell c-box--100">郵便番号</div>
								<div class="d-table-cell"><input type="text" name="order_postal" id="order_postal" class="form-control order_postal" maxlength="8" readonly value="{{ old('order_postal', $editRow['order_postal'] ?? '') }}" onKeyUp="AjaxZip3.zip2addr(this,'','order_address1','order_address2','dummy','order_address3');"></div>
							</div>
							@include('common.elements.error_tag', ['name' => 'order_postal'])
							<div class="d-table c-tbl--400 u-mt--xs">
								<div class="d-table-cell c-box--100">フリガナ</div>
								<div class="d-table-cell"><input type="text" name="order_address1_kana" id="order_address1_kana" class="form-control c-box--full" readonly value="{{ old('order_address1_kana',$editRow['order_address1_kana'] ?? '') }}"></div>
							</div>
							@include('common.elements.error_tag', ['name' => 'order_address1_kana'])
							<div class="d-table c-tbl--400 u-mt--xs">
								<div class="d-table-cell c-box--100">都道府県</div>
								<div class="d-table-cell">
									<select name="order_address1" id="order_address1" readonly class="form-control c-box--200">
										<option></option>
										@foreach($viewExtendData['m_prefectures']??[] as $keyId => $keyValue)
										<option value="{{$keyValue}}" disabled @if (old('order_address1',$editRow['order_address1']??'') == $keyValue){{'selected'}}@endif>{{$keyValue}}</option>
										@endforeach
									</select>
								</div>
							</div>
							@include('common.elements.error_tag', ['name' => 'order_address1'])
							<div class="d-table c-tbl--400 u-mt--xs">
								<div class="d-table-cell c-box--100">フリガナ</div>
								<div class="d-table-cell"><input type="text" name="order_address2_kana" id="order_address2_kana" class="form-control c-box--full" readonly value="{{ old('order_address2_kana',$editRow['order_address2_kana'] ?? '') }}"></div>
							</div>
							@include('common.elements.error_tag', ['name' => 'order_address2_kana'])
							<div class="d-table c-tbl--400 u-mt--xs">
								<div class="d-table-cell c-box--100">市区町村</div>
								<div class="d-table-cell"><input type="text" name="order_address2" id="order_address2" class="form-control c-box--full" readonly value="{{ old('order_address2', $editRow['order_address2'] ?? '') }}"></div>
							</div>
							@include('common.elements.error_tag', ['name' => 'order_address2'])
							<div class="d-table c-tbl--400 u-mt--xs">
								<div class="d-table-cell c-box--100">番地</div>
								<div class="d-table-cell"><input type="text" name="order_address3" id="order_address3" class="form-control c-box--full" readonly value="{{ old('order_address3', $editRow['order_address3'] ?? '') }}"></div>
							</div>
							@include('common.elements.error_tag', ['name' => 'order_address3'])
							<div class="d-table c-tbl--400 u-mt--xs">
								<div class="d-table-cell c-box--100">建物名</div>
								<div class="d-table-cell"><input type="text" name="order_address4" id="order_address4" class="form-control c-box--full" readonly value="{{ old('order_address4', $editRow['order_address4'] ?? '') }}"></div>
							</div>
							@include('common.elements.error_tag', ['name' => 'order_address4'])
						</td>
					</tr>
					<tr>
						<th>法人名・団体名</th>
						<td>
							<input type="text" name="order_corporate_name" id="order_corporate_name" class="form-control c-box--full" readonly value="{{ old('order_corporate_name', $editRow['order_corporate_name'] ?? '') }}">
							@include('common.elements.error_tag', ['name' => 'order_corporate_name'])
						</td>
					</tr>
					<tr>
						<th>部署名</th>
						<td>
							<input type="text" name="order_division_name" id="order_division_name" class="form-control c-box--full" readonly value="{{ old('order_division_name', $editRow['order_division_name'] ?? '') }}">
							@include('common.elements.error_tag', ['name' => 'order_division_name'])
						</td>
					</tr>
					<tr>
						<th>勤務先電話番号</th>
						<td>
							<input type="text" name="corporate_tel" id="corporate_tel" class="form-control u-input--mid" readonly value="{{ old('corporate_tel',$editRow['corporate_tel'] ?? '') }}">
						</td>
					</tr>
					<tr>
						<th>顧客備考</th>
						<td>
							<textarea name="cust_note" id="cust_note" class="form-control c-box--400" rows="5" readonly>{{old('cust_note',$editRow['cust_note'] ?? '')}}</textarea>
						</td>
					</tr>
				</table>
			</div>
		</div><!-- /.d-table -->
		<div id="line-08"></div>
		<p class="c-ttl--02">請求先情報</p>
		<div class="d-table" id="billing_area">
			<div class="c-box--800Half">
				<table class="table c-tbl c-tbl--790">
					<tr>
						<th>顧客ID</th>
						<td>
							<input type="text" name="m_cust_id_billing" id="m_cust_id_billing" class="form-control u-input--mid" readonly value="{{ old('m_cust_id_billing',$editRow['m_cust_id_billing'] ?? '') }}">&nbsp;
							<button class="btn btn-default action_billing_search" type="button">顧客を検索する</button><br>
							@include('common.elements.error_tag', ['name' => 'm_cust_id_billing'])
							<div class="u-mt--ss">
							<button class="btn btn-default button_margin action_billing_new" type="button">表示内容を編集し新規登録</button>
							<button class="btn btn-default button_margin action_billing_clear_new" type="button">情報をクリアし新規登録</button>
							<button class="btn btn-default button_margin action_billing_copy_customer" type="button">注文主情報を自動入力</button>
							</div>
						</td>
					</tr>
					<tr>
						<th class="must">電話番号</th>
						<td>
							<input type="text" name="billing_tel1" id="billing_tel1" class="form-control u-input--mid" readonly value="{{ old('billing_tel1',$editRow['billing_tel1'] ?? '') }}">&nbsp;<input type="text" class="form-control u-input--mid" name="billing_tel2" id="billing_tel2" readonly value="{{ old('billing_tel2',$editRow['billing_tel2'] ?? '') }}">
							@include('common.elements.error_tag', ['name' => 'billing_tel1'])
							@include('common.elements.error_tag', ['name' => 'billing_tel2'])
						</td>
					</tr>
					<tr>
						<th>FAX番号</th>
						<td>
							<input type="text" name="billing_fax" id="billing_fax" class="form-control u-input--mid" readonly value="{{ old('billing_fax',$editRow['billing_fax'] ?? '') }}">
							@include('common.elements.error_tag', ['name' => 'billing_fax'])
						</td>
					</tr>
					<tr>
						<th>フリガナ</th>
						<td>
							<input type="text" name="billing_name_kana" id="billing_name_kana" class="form-control c-box--300" readonly value="{{ old('billing_name_kana',$editRow['billing_name_kana'] ?? '') }}">
							@include('common.elements.error_tag', ['name' => 'billing_name_kana'])
						</td>
					</tr>
					<tr>
						<th class="must">名前</th>
						<td>
							<input type="text" name="billing_name" id="billing_name" class="form-control c-box--300 check_textbyte" data-item_name='名前' data-max_byte="32" readonly value="{{ old('billing_name',$editRow['billing_name'] ?? '') }}">
							@include('common.elements.error_tag', ['name' => 'billing_name'])
						</td>
					</tr>
					<tr>
						<th>メールアドレス</th>
						<td>
							<input type="text" name="billing_email1" id="billing_email1" class="form-control c-box--300" readonly value="{{ old('billing_email1',$editRow['billing_email1'] ?? '') }}">
							@include('common.elements.error_tag', ['name' => 'billing_email1'])
							<input type="text" name="billing_email2" id="billing_email2" class="form-control c-box--300 u-mt--xs" readonly value="{{ old('billing_email2',$editRow['billing_email2'] ?? '') }}">
							@include('common.elements.error_tag', ['name' => 'billing_email2'])
						</td>
					</tr>
					<tr>
						<th>顧客ランク</th>
						<td>
							<select name="billing_cust_runk_id" id="billing_cust_runk_id" readonly class="form-control c-box--200">
								@foreach($viewExtendData['cust_runk_list']??[] as $elm)
								<option value="{{$elm['m_itemname_types_id']}}" disabled @if ( old('billing_cust_runk_id',$editRow['billing_cust_runk_id'] ?? '') == $elm['m_itemname_types_id']){{'selected'}}@endif>{{$elm['m_itemname_type_name']}}</option>
								@endforeach
							</select>
							@include('common.elements.error_tag', ['name' => 'billing_cust_runk_id'])
						</td>
					</tr>
					<tr>
						<th>要注意区分</th>
						<td>
							<div class="radio-inline">
								<label><input type="radio" name="billing_alert_cust_type" value="0" disabled @if(old('billing_alert_cust_type',$editRow['billing_alert_cust_type'] ?? '') =='0' ){{'checked'}}@endif>通常</label>
							</div>
							<div class="radio-inline">
								<label><input type="radio" name="billing_alert_cust_type" value="1" disabled @if(old('billing_alert_cust_type',$editRow['billing_alert_cust_type'] ?? '') =='1' ){{'checked'}}@endif>要確認</label>
							</div>	
							<div class="radio-inline">
								<label><input type="radio" name="billing_alert_cust_type" value="2" disabled @if(old('billing_alert_cust_type',$editRow['billing_alert_cust_type'] ?? '') =='2' ){{'checked'}}@endif>受注不可</label>
							</div>
						</td>
					</tr>
				</table>
			</div>
			<div class="c-box--800Half">
				<table class="table c-tbl c-tbl--790">
					<tr>
						<th class="must">住所</th>
						<td>
							<div class="d-table c-tbl--400">
								<div class="d-table-cell c-box--100">郵便番号</div>
								<div class="d-table-cell"><input type="text" name="billing_postal" id="billing_postal" class="form-control billing_postal" maxlength="8" readonly value="{{ old('billing_postal',$editRow['billing_postal'] ?? '') }}" onKeyUp="AjaxZip3.zip2addr(this,'','billing_address1','billing_address2','dummy','billing_address3');"></div>
							</div>
							@include('common.elements.error_tag', ['name' => 'billing_postal'])
							<div class="d-table c-tbl--400 u-mt--xs">
								<div class="d-table-cell c-box--100">フリガナ</div>
								<div class="d-table-cell"><input type="text" name="billing_address1_kana" id="billing_address1_kana" class="form-control c-box--full" readonly value="{{ old('billing_address1_kana',$editRow['billing_address1_kana'] ?? '') }}"></div>
							</div>
							@include('common.elements.error_tag', ['name' => 'billing_address1_kana'])
							<div class="d-table c-tbl--400 u-mt--xs">
								<div class="d-table-cell c-box--100">都道府県</div>
								<div class="d-table-cell">
									<select name="billing_address1" id="billing_address1" readonly class="form-control c-box--200">
										<option></option>
										@foreach($viewExtendData['m_prefectures']??[] as $keyId => $keyValue)
										<option value="{{$keyValue}}" disabled @if (old('billing_address1',$editRow['billing_address1']) == $keyValue){{'selected'}}@endif>{{$keyValue}}</option>
										@endforeach
									</select>
								</div>
							</div>
							@include('common.elements.error_tag', ['name' => 'billing_address1'])
							<div class="d-table c-tbl--400 u-mt--xs">
								<div class="d-table-cell c-box--100">フリガナ</div>
								<div class="d-table-cell"><input type="text" name="billing_address2_kana" id="billing_address2_kana" class="form-control c-box--full" readonly value="{{ old('billing_address2_kana',$editRow['billing_address2_kana'] ?? '') }}"></div>
							</div>
							@include('common.elements.error_tag', ['name' => 'billing_address2_kana'])
							<div class="d-table c-tbl--400 u-mt--xs">
								<div class="d-table-cell c-box--100">市区町村</div>
								<div class="d-table-cell"><input type="text" name="billing_address2" id="billing_address2" class="form-control c-box--full check_textbyte" data-item_name="市区町村" data-max_byte="24" readonly value="{{ old('billing_address2',$editRow['billing_address2'] ?? '') }}"></div>
							</div>
							@include('common.elements.error_tag', ['name' => 'billing_address2'])
							<div class="d-table c-tbl--400 u-mt--xs">
								<div class="d-table-cell c-box--100">番地</div>
								<div class="d-table-cell"><input type="text" name="billing_address3" id="billing_address3" class="form-control c-box--full check_textbyte" data-item_name="番地" data-max_byte="32" readonly value="{{ old('billing_address3',$editRow['billing_address3'] ?? '') }}"></div>
							</div>
							@include('common.elements.error_tag', ['name' => 'billing_address3'])
							<div class="d-table c-tbl--400 u-mt--xs">
								<div class="d-table-cell c-box--100">建物名</div>
								<div class="d-table-cell"><input type="text" name="billing_address4" id="billing_address4" class="form-control c-box--full check_textbyte" data-item_name="建物名" data-max_byte="32" readonly value="{{ old('billing_address4',$editRow['billing_address4'] ?? '') }}"></div>
							</div>
							@include('common.elements.error_tag', ['name' => 'billing_address4'])
						</td>
					</tr>
					<tr>
						<th>法人名・団体名</th>
						<td>
							<input type="text" name="billing_corporate_name" id="billing_corporate_name" class="form-control c-box--full" readonly value="{{ old('billing_corporate_name',$editRow['billing_corporate_name'] ?? '') }}">
							@include('common.elements.error_tag', ['name' => 'billing_corporate_name'])
						</td>
					</tr>
					<tr>
						<th>部署名</th>
						<td>
							<input type="text" name="billing_division_name" id="billing_division_name" class="form-control c-box--full" readonly value="{{ old('billing_division_name',$editRow['billing_division_name'] ?? '') }}">
							@include('common.elements.error_tag', ['name' => 'billing_division_name'])
						</td>
					</tr>
					<tr>
						<th>勤務先電話番号</th>
						<td>
							<input type="text" name="billing_corporate_tel" id="billing_corporate_tel" class="form-control u-input--mid" readonly value="{{ old('billing_corporate_tel',$editRow['billing_corporate_tel'] ?? '') }}">
						</td>
					</tr>
					<tr>
						<th>顧客備考</th>
						<td>
							<textarea name="billing_cust_note" id="billing_cust_note" class="form-control c-box--400" rows="5" readonly>{{ old('billing_cust_note',$editRow['billing_cust_note'] ?? '') }}</textarea>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<div id="destination_area" class="c-box--1600">
			<ul class="nav nav-tabs">
			@php
				$destIndex = -1;
			@endphp
			@foreach(old('register_destination',$editRow['register_destination']??[]) as $registerDestination)
				@php
				$destIndex++;
				@endphp
				<li class="destination_tab"><a id="dest_tab_{{$destIndex}}" href="#tabs-{{$destIndex}}" data-toggle="tab">{{$registerDestination['destination_tab_display_name'] ?? ''}}</a></li>
			@endforeach
				<li class="destination_tab_positon">
					<div style="margin-left:5px;">
						<button type="button" class="btn btn-default button_margin btn_circle action_append_destination">＋</button>
						<button type="button" class="btn btn-default button_margin action_destination_copy">コピーして送付先作成</button>
						<button type="button" class="btn btn-success action_destination_copy_customer">注文主情報を自動入力</button>
					</div>
				</li>
			</ul>
			<div class="tab-content tabs-inner destination_tab_body" style="padding:5px;">
				@php
					$destIndex = -1;
				@endphp
				@foreach(old('register_destination',$editRow['register_destination']??[]) as $registerDestination)
					@php
					$destIndex++;
					@endphp
					<!-- tabs-{{$destIndex}} start -->
				<div class="tab-pane destination_tab_data" id="tabs-{{$destIndex}}">
					<div class="c-box--full">
						<p class="c-ttl--02">送付先情報</p>
					</div>
					<input type="hidden" class="order_destination_seq" name="register_destination[{{$destIndex}}][order_destination_seq]" value="{{$registerDestination['order_destination_seq']}}">
					<input type="hidden" class="destination_index" name="register_destination[{{$destIndex}}][destination_index]" value="{{$destIndex}}">
					<input type="hidden" class="destination_tab_display_name" name="register_destination[{{$destIndex}}][destination_tab_display_name]" value="{{$registerDestination['destination_tab_display_name']}}">
					<input type="hidden" class="t_order_destination_id" name="register_destination[{{$destIndex}}][t_order_destination_id]" value="{{$registerDestination['t_order_destination_id'] ?? ''}}">
					<input type="hidden" class="destination_tax_rate_8" data-tax="0.08" id="register_destination_{{$destIndex}}_destination_tax_8" value="0">
					<input type="hidden" class="destination_tax_rate_10" data-tax="0.1" id="register_destination_{{$destIndex}}_destination_tax_10" value="0">
					<input type="hidden" name="register_destination[{{$destIndex}}][standard_fee]" id="register_destination_{{$destIndex}}_standard_fee" value="{{$registerDestination['standard_fee']}}">
					<input type="hidden" name="register_destination[{{$destIndex}}][frozen_fee]" id="register_destination_{{$destIndex}}_frozen_fee" value="{{$registerDestination['frozen_fee']}}">
					<input type="hidden" name="register_destination[{{$destIndex}}][chilled_fee]" id="register_destination_{{$destIndex}}_chilled_fee" value="{{$registerDestination['chilled_fee']}}">
					<input type="hidden" name="register_destination[{{$destIndex}}][destination_id]" id="register_destination_{{$destIndex}}_destination_id" value="{{$registerDestination['destination_id']}}">

					<div class="d-table c-box--full">
						<div class="c-box--800Half">
							<table class="table table-bordered c-tbl c-tbl--790">
								<tr>
									<th class="c-box--150 must">電話番号</th>
									<td>
										<input type="text" name="register_destination[{{$destIndex}}][destination_tel]"
											id="register_destination_{{$destIndex}}_destination_tel" class="form-control u-input--mid" value="{{$registerDestination['destination_tel']}}">
										<button class="btn btn-default action_destination_search" data-index="{{$destIndex}}" type="button">送付先を検索する</button><br>
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.destination_tel'])
									</td>
								</tr>
								<tr>
									<th>フリガナ</th>
									<td>
										<input type="text" name="register_destination[{{$destIndex}}][destination_name_kana]"
											id="register_destination_{{$destIndex}}_destination_name_kana" class="form-control c-box--300" value="{{$registerDestination['destination_name_kana']}}">
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.destination_name_kana'])
									</td>
								</tr>
								<tr>
									<th class="must">名前</th>
									<td>
										<input type="text" name="register_destination[{{$destIndex}}][destination_name]"
											id="register_destination_{{$destIndex}}_destination_name" class="form-control c-box--300 register_destination_name check_textbyte" data-item_name="名前" data-max_byte="32" value="{{$registerDestination['destination_name']}}">
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.destination_name'])
									</td>
								</tr>
								<tr>
									<th class="must">配送方法</th>
									<td>
										<select name="register_destination[{{$destIndex}}][m_delivery_type_id]"
											id="register_destination_{{$destIndex}}_m_delivery_type_id" class="form-control c-box--300 action_change_delivery_type" data-index="{{$destIndex}}">
											@foreach($viewExtendData['delivery_type_list'] as $deliveryType)
											<option data-delivery_type="{{$deliveryType['delivery_type']}}" value="{{$deliveryType['m_delivery_types_id']}}" @if(!empty($registerDestination['m_delivery_type_id']) && $deliveryType['m_delivery_types_id']==$registerDestination['m_delivery_type_id']) selected @endif>{{$deliveryType['m_delivery_type_name']}}</option>
											@endforeach
										</select>
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.m_delivery_type_id'])
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.delivery_name'])
									</td>
								</tr>
								<tr>
									<th>配送希望日</th>
									<td>
										<div class='c-box--218'>
											<div class='input-group date date-picker deli_hope_date_picker'>
												<input name="register_destination[{{$destIndex}}][deli_hope_date]"
													id="register_destination_{{$destIndex}}_deli_hope_date" type='text'
													class="form-control c-box--180" value="{{$registerDestination['deli_hope_date']}}" />
												<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
											</div>
										</div>
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.deli_hope_date'])
									</td>
								</tr>
								<tr>
									<th>配送業者別希望時間帯</th>
									<td>
										<select name="register_destination[{{$destIndex}}][m_delivery_time_hope_id]" id="register_destination_{{$destIndex}}_m_delivery_time_hope_id" class="form-control c-box--300">
											<option value=""></option>
											@foreach($viewExtendData['delivery_hope_timezone_list'] as $deliveryTimehope)
											@if(!empty($registerDestination['m_delivery_time_hope_id']) && $deliveryTimehope['m_delivery_time_hope_id'] == $registerDestination['m_delivery_time_hope_id'])
											<option value="{{$deliveryTimehope['m_delivery_time_hope_id']}}" data-delivery_type="{{$deliveryTimehope->deliveryCompany->delivery_company_cd}}" selected>{{$deliveryTimehope['delivery_company_time_hope_name']}}</option>
											@else
											<option value="{{$deliveryTimehope['m_delivery_time_hope_id']}}" data-delivery_type="{{$deliveryTimehope->deliveryCompany->delivery_company_cd}}">{{$deliveryTimehope['delivery_company_time_hope_name']}}</option>
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
											<div class='input-group date date-picker deli_plan_date_picker'>
												<input type='text' name="register_destination[{{$destIndex}}][deli_plan_date]"
													id="register_destination_{{$destIndex}}_deli_plan_date" class="form-control c-box--180"
													value="{{$registerDestination['deli_plan_date']}}" />
												<span class="input-group-addon"><span
														class="glyphicon glyphicon-calendar"></span></span>
											</div>
										</div>
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.deli_plan_date'])
									</td>
								</tr>
								<tr>
									<th>送り状コメント</th>
									<td>
										<input type="text" name="register_destination[{{$destIndex}}][invoice_comment]"
											id="register_destination_{{$destIndex}}_invoice_comment" class="form-control u-input--full" value="{{$registerDestination['invoice_comment']}}">
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.invoice_comment'])
									</td>
								</tr>
								<tr>
									<th>ピッキングコメント</th>
									<td>
										<textarea class="form-control u-input--full" name="register_destination[{{$destIndex}}][picking_comment]" id="register_destination_{{$destIndex}}_picking_comment" rows="6">{{$registerDestination['picking_comment']}}</textarea>
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.picking_comment'])
									</td>
								</tr>
								<tr>
									<th>分割配送する</th>
									<td>
										<input type="checkbox" name="register_destination[{{$destIndex}}][partial_deli_flg]"
											id="register_destination_{{$destIndex}}_partial_deli_flg" value="1" @if(isset($registerDestination['partial_deli_flg']) && $registerDestination['partial_deli_flg']==1){{'checked'}}@endif>
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.partial_deli_flg'])
									</td>
								</tr>
								<tr>
									<th>キャンペーン対象</th>
									<td>
										<input type="checkbox" name="register_destination[{{$destIndex}}][campaign_flg]"
											id="register_destination_{{$destIndex}}_campaign_flg" value="1" class="action_change_campaign_flg" data-index="{{$destIndex}}" @if(isset($registerDestination['campaign_flg']) && $registerDestination['campaign_flg']==1){{'checked'}}@endif>
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.campaign_flg'])
									</td>
								</tr>

								<tr>
									<th>出荷保留</th>
									<td>
										<input type="checkbox" name="register_destination[{{$destIndex}}][pending_flg]"
											id="register_destination_{{$destIndex}}_pending_flg" value="1" @if(isset($registerDestination['pending_flg']) && $registerDestination['pending_flg']==1){{'checked'}}@endif>
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.pending_flg'])
									</td>
								</tr>
								<tr>
									<th>送り主名</th>
									<td>
										<input type="text" name="register_destination[{{$destIndex}}][sender_name]"
											id="register_destination_{{$destIndex}}_sender_name" class="form-control u-input--full" value="{{$registerDestination['sender_name']}}">
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.sender_name'])
									</td>
								</tr>
								<tr>
									<th>配送種別</th>
									<td>
										<input type="checkbox" name="register_destination[{{$destIndex}}][total_deli_flg]"
											id="register_destination_{{$destIndex}}_total_deli_flg" value="1" class="action_change_total_deli_flg" data-index="{{$destIndex}}"
											@if(isset($registerDestination['total_deli_flg']) && $registerDestination['total_deli_flg']==1){{'checked'}}@endif>
										<label for="register_destination_{{$destIndex}}_total_deli_flg">同梱配送</label>
										<select name="register_destination[{{$destIndex}}][total_temperature_zone_type]"
											id="register_destination_{{$destIndex}}_total_temperature_zone_type" class="form-control u-input--mid action_change_total_temperature_zone_type" data-index="{{$destIndex}}" @if(!(isset($registerDestination['total_deli_flg']) && $registerDestination['total_deli_flg']==1)){{'disabled'}}@endif>
											<option value="0" data-delicomp="0" @if($registerDestination['total_temperature_zone_type'] == 0){{'selected'}}@endif >常温</option>
											<option value="1" data-delicomp="1" @if($registerDestination['total_temperature_zone_type'] == 1){{'selected'}}@endif >冷凍</option>
											<option value="2" data-delicomp="2" @if($registerDestination['total_temperature_zone_type'] == 2){{'selected'}}@endif >冷蔵</option>
										</select>
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.total_deli_flg'])
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.total_temperature_zone_type'])
									</td>
								</tr>
							</table>
						</div>
						<div class="c-box--800Half">
							<table class="table table-bordered c-tbl c-tbl--790">
								<tr>
									<th class="must">住所</th>
									<td>
										<div class="d-table c-tbl--400">
											<div class="d-table-cell c-box--100">郵便番号</div>
											<div class="d-table-cell"><input type="text"
													name="register_destination[{{$destIndex}}][destination_postal]"
													id="register_destination_{{$destIndex}}_destination_postal" data-index="{{$destIndex}}" class="form-control refresh_shipping_fee destination_postal" maxlength="8"
													value="{{$registerDestination['destination_postal']}}"
													onKeyUp="AjaxZip3.zip2addr(this,'','register_destination[{{$destIndex}}][destination_address1]','register_destination[{{$destIndex}}][destination_address2]','dummy','register_destination[{{$destIndex}}][destination_address3]');">
											</div>
											@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.destination_postal'])
										</div>
										<div class="d-table c-tbl--400 u-mt--xs">
											<div class="d-table-cell c-box--100">フリガナ</div>
											<div class="d-table-cell">
												<input type="text" name="register_destination[{{$destIndex}}][destination_address1_kana]"
													id="register_destination_{{$destIndex}}_destination_address1_kana" class="form-control c-box--full"
													value="{{$registerDestination['destination_address1_kana']??''}}" disabled></div>
											@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.destination_address1_kana'])
										</div>
										<div class="d-table c-tbl--400 u-mt--xs">
											<div class="d-table-cell c-box--100">都道府県</div>
											<div class="d-table-cell">
												<select name="register_destination[{{$destIndex}}][destination_address1]"
													id="register_destination_{{$destIndex}}_destination_address1" class="form-control c-box--200 action_change_destination_address1" data-index="{{$destIndex}}">
													<option></option>
													@foreach($viewExtendData['m_prefectures'] as $keyId => $keyValue)
														<option value="{{$keyValue}}" @if ($registerDestination['destination_address1'] == $keyValue){{'selected'}}@endif>{{$keyValue}}</option>
													@endforeach
												</select>
											</div>
											@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.destination_address1'])
										</div>
										<div class="d-table c-tbl--400 u-mt--xs">
											<div class="d-table-cell c-box--100">フリガナ</div>
											<div class="d-table-cell">
												<input type="text" name="register_destination[{{$destIndex}}][destination_address2_kana]"
													id="register_destination_{{$destIndex}}_destination_address2_kana" class="form-control c-box--full"
													value="{{$registerDestination['destination_address2_kana']??''}}" disabled>
											</div>
											@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.destination_address2_kana'])
										</div>
										<div class="d-table c-tbl--400 u-mt--xs">
											<div class="d-table-cell c-box--100">市区町村</div>
											<div class="d-table-cell">
												<input type="text"
													name="register_destination[{{$destIndex}}][destination_address2]"
													id="register_destination_{{$destIndex}}_destination_address2" class="form-control c-box--full check_textbyte" data-item_name="市区町村" data-max_byte="24"
													value="{{$registerDestination['destination_address2']}}">
											</div>
											@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.destination_address2'])
										</div>
										<div class="d-table c-tbl--400 u-mt--xs">
											<div class="d-table-cell c-box--100">番地</div>
											<div class="d-table-cell">
												<input type="text"
													name="register_destination[{{$destIndex}}][destination_address3]"
													id="register_destination_{{$destIndex}}_destination_address3" class="form-control c-box--full check_textbyte" data-item_name="番地" data-max_byte="32"
													value="{{$registerDestination['destination_address3']}}">
											</div>
											@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.destination_address3'])
										</div>
										<div class="d-table c-tbl--400 u-mt--xs">
											<div class="d-table-cell c-box--100">建物名</div>
											<div class="d-table-cell">
												<input type="text"
													name="register_destination[{{$destIndex}}][destination_address4]"
													id="register_destination_{{$destIndex}}_destination_address4" class="form-control c-box--full check_textbyte" data-item_name="建物名" data-max_byte="32"
													value="{{$registerDestination['destination_address4']}}">
											</div>
											@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.destination_address4'])
										</div>
									</td>
								</tr>

								<tr>
									<th>法人名・団体名</th>
									<td>
										<input type="text" name="register_destination[{{$destIndex}}][destination_company_name]"
											id="register_destination_{{$destIndex}}_destination_company_name" class="form-control c-box--full destination_company_name"
											value="{{$registerDestination['destination_company_name']}}">
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.destination_company_name'])
									</td>
								</tr>
								<tr>
									<th>部署名</th>
									<td>
										<input type="text" name="register_destination[{{$destIndex}}][destination_division_name]"
											id="register_destination_{{$destIndex}}_destination_division_name" class="form-control c-box--full"
											value="{{$registerDestination['destination_division_name']}}">
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.destination_division_name'])
									</td>
								</tr>
								<tr>
									<th>ギフトメッセージ</th>
									<td>
										<input type="text" name="register_destination[{{$destIndex}}][gift_message]"
											id="register_destination_{{$destIndex}}_gift_message" class="form-control u-input--full" value="{{$registerDestination['gift_message']??''}}" disabled>
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.gift_message'])
									</td>
								</tr>
								<tr>
									<th>ギフト包装種類</th>
									<td>
										<input type="text" name="register_destination[{{$destIndex}}][gift_wrapping]"
											id="register_destination_{{$destIndex}}_gift_wrapping" class="form-control u-input--full" value="{{$registerDestination['gift_wrapping']??''}}" disabled>
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.gift_wrapping'])
									</td>
								</tr>
								<tr>
									<th>のしタイプ</th>
									<td>
										<input type="text" name="register_destination[{{$destIndex}}][nosi_type]"
											id="register_destination_{{$destIndex}}_nosi_type" class="form-control u-input--full" value="{{$registerDestination['nosi_type']??''}}" disabled>
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.nosi_type'])
									</td>
								</tr>
								<tr>
									<th>のし名前</th>
									<td>
										<input type="text" name="register_destination[{{$destIndex}}][nosi_name]"
											id="register_destination_{{$destIndex}}_nosi_name" class="form-control u-input--full" value="{{$registerDestination['nosi_name']??''}}" disabled>
										@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.nosi_name'])
									</td>
								</tr>
							</table>
						</div>
					</div>
					<div class="u-mt--ss">
						@empty($registerDestination['t_order_destination_id'])
						<button type="button" class="btn btn-danger btn-lg action_remove_destination" data-index="{{$destIndex}}">送付先を削除</button>
						@endempty
						@include('common.elements.error_tag', ['name' => 'submit_deldest.' . $destIndex])
					</div>
					<div id="line-05"></div>
					<div class="c-box--full u-mt--ss">
						<p class="c-ttl--02">受注明細情報</p>
					</div>
					<div class="d-table c-box--full">
						<table class="table table-bordered c-tbl c-tbl--1580 u-mt--ss detail_sell_table">
							<tr class="nowrap">
								<th class="c-box--60">コピー</th>
								<th class="c-box--60"></th>
								<th class="c-box--220 must">販売コード</th>
								<th class="c-box--450 must">販売名</th>
								<th class="c-box--110">販売単価</th>
								<th class="c-box--110 must">数量</th>
								<th class="c-box--110">販売金額</th>
								<th class="c-box--130">在庫状態
									<button type="button" class="btn btn-default btn-xs" name="submit_stockinfo_update[{{$destIndex}}]">
										<span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
									</button>
								</th>
								<th class="c-box--130">クーポンID</th>
								<th class="c-box--110">クーポン金額</th>
								<th class="">種別</th>
							</tr>
							@php 
							$dtlIndex = -1;
							@endphp
							@foreach($registerDestination['register_detail']??[] as $registerDetail)
							@php 
								$dtlIndex++;
							@endphp
							<tr class="detail_area detail_row_{{$destIndex}}_{{$dtlIndex}}" data-index="{{$destIndex}}" data-detail_index="{{$dtlIndex}}">
								<td class="u-vasm u-center" rowspan="3">
									<label for="">
										<input type="checkbox" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][check_copy]"
											id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_check_copy" data-rowid="{{$destIndex}}-{{$dtlIndex}}" data-index="{{$destIndex}}" data-detail_index="{{$dtlIndex}}" class="checkbox check_copy_detail"
											value="1">
									</label>
								</td>
								<td rowspan="3" class="u-center">
									@php
									$is_delete_item = 0;
									@endphp
									@if(!empty($registerDetail['cancel_timestamp']) && str_starts_with($registerDetail['cancel_timestamp'],'0000-00-00') == false)
									<span class="u-center font-FF0000">削除済</span>
									@php
									$is_delete_item = 1;
									@endphp
									@elseif($registerDetail['cancel_flg'] == '1')
									<span class="u-center font-FF0000">削除</span>
									@php
									$is_delete_item = 1;
									@endphp
									@else
									<button type="button" class="btn btn-danger action_remove_register_destination_detail" data-index="{{$destIndex}}" data-detail_index="{{$dtlIndex}}">削除</button>
									@endif
								</td>
								<td class="u-vam">
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][t_order_dtl_sku_id]" value="{{$registerDetail['t_order_dtl_sku_id']}}">
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][t_order_dtl_id]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_t_order_dtl_id" value="{{$registerDetail['t_order_dtl_id']}}">
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_seq]" value="{{$registerDetail['order_dtl_seq']}}" class="order_dtl_seq">
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][t_deli_hdr_id]" value="{{$registerDetail['t_deli_hdr_id']}}"> <!-- not used?-->
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][cancel_timestamp]" value="{{$registerDetail['cancel_timestamp']}}" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_cancel_timestamp" class="register_destination_register_detail_cancel_timestamp">
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][cancel_flg]" value="{{$registerDetail['cancel_flg']}}" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_cancel_flg" class="register_destination_register_detail_cancel_flg">
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][reservation_date]" value="{{$registerDetail['reservation_date']}}"> <!-- not used?-->
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][variation_values]"
										id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_variation_values" value="{{$registerDetail['variation_values']}}">
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][sell_id]"
										id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_sell_id" value="{{$registerDetail['sell_id']}}">
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][sell_checked]" value="{{$registerDetail['sell_checked']}}">
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][sku_data]" value="{{$registerDetail['sku_data']}}">
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][three_temperature_zone_type]" class="three_temperature_zone_type" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_three_temperature_zone_type" value="{{$registerDetail['three_temperature_zone_type']??'0'}}">
									<input type="text" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][sell_cd]" readonly
										id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_sell_cd" class="form-control u-input--mid"
										value="{{$registerDetail['sell_cd']}}">
								</td>
								<td class="u-vam">
									<textarea name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][sell_name]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_sell_name" rows="1"
										class="form-control u-input--mid c-box--full" style="resize: vertical" {{ $is_delete_item ? "disabled":"" }}>{{$registerDetail['sell_name']??''}}</textarea>
								</td>
								<td class="u-vam u-right">
									<span>{{number_format($registerDetail['order_sell_price']??'0')}}</span>
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_sell_price]"
										id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_order_sell_price" data-rowid="{{$destIndex}}-{{$dtlIndex}}"
										class="form-control u-input--small u-right register_destination_register_detail_order_sell_price" value="{{floor($registerDetail['order_sell_price']??'0')}}">
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][tax_rate]"
										id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_tax_rate" data-rowid="{{$destIndex}}-0"
										class="form-control u-input--small register_destination_register_detail_tax_rate" value="{{$registerDetail['tax_rate']??''}}">
								</td>
								<td class="u-vam u-right">
									<input type="text" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_sell_vol]"
										id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_order_sell_vol" data-rowid="{{$destIndex}}-{{$dtlIndex}}"
										class="form-control u-input--small u-right c-box--60 register_destination_register_detail_sell_vol" value="{{$registerDetail['order_sell_vol']??''}}" {{ $is_delete_item ? "disabled":"" }}>
									@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.register_detail.'.$dtlIndex.'.order_sell_vol'])
								</td>
								<td class="u-vam u-right">
									<span class="register_destination_register_detail_sell_amount"></span>
									<input class="register_destination_register_detail_order_sell_amount" type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_sell_amount]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_order_sell_amount">
								</td>
								<td class="u-vam">
									<a id="drawing_status_{{$destIndex}}-{{$dtlIndex}}" data-sellcd="{{$registerDetail['sell_cd']??''}}" data-rowid="{{$destIndex}}-{{$dtlIndex}}" style="cursor: pointer" data-href="{{config('env.app_subsys_url.order')}}order/stockinfo/id/{{$registerDetail['sell_cd'] ?? ''}}/variation/{{$registerDetail['variation_values'] or '__'}}/itemid/0">{{$registerDetail['drawing_status_name'] ?? ''}}</a>
									<span class="register_destination_register_detail_stock_state"></span>
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][drawing_status_name]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_drawing_status_name">
								</td>
								<td class="u-vam">
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_coupon_id]"
										id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_order_dtl_coupon_id" data-rowid="{{$destIndex}}-{{$dtlIndex}}"
										class="form-control u-input--small u-right register_destination_register_detail_order_dtl_coupon_id" value="{{$registerDetail['order_dtl_coupon_id']??''}}">
									<span class="register_destination_register_detail_order_dtl_coupon_id">{{$registerDetail['order_dtl_coupon_id']??''}}</span>
								</td>
								<td class="u-vam u-right">
									<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_coupon_price]"
										id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_order_dtl_coupon_price" data-rowid="{{$destIndex}}-{{$dtlIndex}}"
										class="form-control u-input--small u-right register_destination_register_detail_order_dtl_coupon_price" value="{{$registerDetail['order_dtl_coupon_price']?floor($registerDetail['order_dtl_coupon_price']):''}}">
									<span class="register_destination_register_detail_order_dtl_coupon_price">{{$registerDetail['order_dtl_coupon_price']?number_format($registerDetail['order_dtl_coupon_price']):''}}</span>
								</td>
								<td class="u-vam">
									<select name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][attachment_item_group_id]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_attachment_item_group_id" class="form-control c-box--full action_change_attachment_item_group_id" data-index="{{$destIndex}}" data-detail_index="{{$dtlIndex}}" {{ $is_delete_item ? "disabled":"" }}>
										@foreach($viewExtendData['attachment_group']??[] as $val)
										<option value="{{$val['m_itemname_types_id']}}" @if($val['m_itemname_types_id'] == $registerDetail['attachment_item_group_id']) {{'selected'}}@endif>{{$val['m_itemname_type_name']}}</option>
										@endforeach
									</select>
								</td>
							</tr>
							<tr class="detail_area2 detail_row_{{$destIndex}}_{{$dtlIndex}}">
							<td rowspan="2">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][image_path]"
										id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_image_path" data-rowid="{{$destIndex}}-{{$dtlIndex}}"
										value="{{$registerDetail['image_path']??''}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][m_ami_page_id]"
										id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_m_ami_page_id" data-rowid="{{$destIndex}}-{{$dtlIndex}}"
										value="{{$registerDetail['m_ami_page_id']??''}}">
								<div class="detail_item_thumb">
									@if(($registerDetail['image_path']??'') != '')
										<div class='item-image-preview'>
											<img src="{{'/'.config('filesystems.resources_dir').'/'.$esmSessionManager->getAccountCode().'/image/page/'.$registerDetail['m_ami_page_id'].'/'.$registerDetail['image_path'] }}">
											<span class="item-image-preview-glass action_item_zoom">
												<span class="glyphicon glyphicon-zoom-in" aria-hidden="true"></span>
											</span>
										</div>
									@endif
								</div>
							</td>
							<td rowspan="2">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][page_desc]"
										id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_page_desc" data-rowid="{{$destIndex}}-{{$dtlIndex}}"
										value="{{$registerDetail['page_desc']??''}}">
								<div class="detail_item_html">{!!$registerDetail['page_desc']??''!!}</div>
							</td>
							<th>熨斗</th>
							<td colspan="5">
								<div id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_noshi_html">
								</div>
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_noshi][t_order_dtl_noshi_id]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_t_order_dtl_noshi_id" value="{{$registerDetail['order_dtl_noshi']['t_order_dtl_noshi_id']??''}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_noshi][noshi_id]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_noshi_id" value="{{$registerDetail['order_dtl_noshi']['noshi_id']??''}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_noshi][noshi_detail_id]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_noshi_detail_id" value="{{$registerDetail['order_dtl_noshi']['noshi_detail_id']??''}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_noshi][noshi_detail_name]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_noshi_detail_name" value="{{$registerDetail['order_dtl_noshi']['noshi_detail_name']??''}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_noshi][m_noshi_format_id]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_m_noshi_format_id" value="{{$registerDetail['order_dtl_noshi']['m_noshi_format_id']??$registerDetail['order_dtl_noshi']->noshiDetail['m_noshi_format_id']??''}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_noshi][m_noshi_format_name]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_m_noshi_format_name" value="{{$registerDetail['order_dtl_noshi']['m_noshi_format_name']??''}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_noshi][m_noshi_naming_pattern_id]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_m_noshi_naming_pattern_id" value="{{$registerDetail['order_dtl_noshi']['m_noshi_naming_pattern_id']??''}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_noshi][m_noshi_naming_pattern_name]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_m_noshi_naming_pattern_name" value="{{$registerDetail['order_dtl_noshi']['m_noshi_naming_pattern_name']??''}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_noshi][omotegaki]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_omotegaki" value="{{$registerDetail['order_dtl_noshi']['omotegaki']??''}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_noshi][attach_flg]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_attach_flg" value="{{$registerDetail['order_dtl_noshi']['attach_flg']??'0'}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_noshi][company_name_count]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_company_name_count" value="{{$registerDetail['order_dtl_noshi']['company_name_count']??'0'}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_noshi][section_name_count]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_section_name_count" value="{{$registerDetail['order_dtl_noshi']['section_name_count']??'0'}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_noshi][title_count]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_title_count" value="{{$registerDetail['order_dtl_noshi']['title_count']??'0'}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_noshi][f_name_count]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_f_name_count" value="{{$registerDetail['order_dtl_noshi']['f_name_count']??'0'}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_noshi][name_count]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_name_count" value="{{$registerDetail['order_dtl_noshi']['name_count']??'0'}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_noshi][ruby_count]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_ruby_count" value="{{$registerDetail['order_dtl_noshi']['ruby_count']??'0'}}">
								@for ($i = 1; $i <= 5 ; $i++)
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_noshi][company_name{{$i}}]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_company_name{{$i}}" value="{{$registerDetail['order_dtl_noshi']['company_name'.$i]??''}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_noshi][section_name{{$i}}]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_section_name{{$i}}" value="{{$registerDetail['order_dtl_noshi']['section_name'.$i]??''}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_noshi][title{{$i}}]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_title{{$i}}" value="{{$registerDetail['order_dtl_noshi']['title'.$i]??''}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_noshi][firstname{{$i}}]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_firstname{{$i}}" value="{{$registerDetail['order_dtl_noshi']['firstname'.$i]??''}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_noshi][name{{$i}}]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_name{{$i}}" value="{{$registerDetail['order_dtl_noshi']['name'.$i]??''}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_noshi][ruby{{$i}}]" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_ruby{{$i}}" value="{{$registerDetail['order_dtl_noshi']['ruby'.$i]??''}}">
								@endfor
							</td>
							<td class="u-center">
								<button type="button" class="btn btn-default action_edit_noshi" data-index="{{$destIndex}}" data-detail_index="{{$dtlIndex}}" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_edit_noshi" {{ $is_delete_item ? "disabled":"" }}>編集</button>
							</td>
						</tr>
						<tr class="detail_area3 detail_row_{{$destIndex}}_{{$dtlIndex}}">
							<th>付属品</th>
							<td colspan="5">
								<div id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_attachment_html"></div>
								@php 
								$attachmentIndex = -1;
								@endphp
								@foreach($registerDetail['order_dtl_attachment_item']??[] as $attachmentItem)
								@php 
								$attachmentIndex++;
								@endphp
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_attachment_item][{{$attachmentIndex}}][attachment_index]"
									id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_order_dtl_attachment_item_{{$attachmentIndex}}_attachment_index"
									class="attachment_item_{{$destIndex}}_{{$dtlIndex}} attachment_index" value="{{$attachmentIndex}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_attachment_item][{{$attachmentIndex}}][t_order_dtl_attachment_item_id]"
									id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_order_dtl_attachment_item_{{$attachmentIndex}}_t_order_dtl_attachment_item_id"
									class="attachment_item_{{$destIndex}}_{{$dtlIndex}} order_dtl_attachment_item_id" value="{{$attachmentItem['t_order_dtl_attachment_item_id']??''}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_attachment_item][{{$attachmentIndex}}][t_order_dtl_id]"
									id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_order_dtl_attachment_item_{{$attachmentIndex}}_t_order_dtl_id"
									class="attachment_item_{{$destIndex}}_{{$dtlIndex}} t_order_dtl_id" value="{{$attachmentItem['t_order_dtl_id']??''}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_attachment_item][{{$attachmentIndex}}][display_flg]"
									id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_order_dtl_attachment_item_{{$attachmentIndex}}_display_flg"
									class="attachment_item_{{$destIndex}}_{{$dtlIndex}} display_flg" value="{{$attachmentItem['display_flg']??''}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_attachment_item][{{$attachmentIndex}}][m_ami_attachment_item_id]"
									id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_order_dtl_attachment_item_{{$attachmentIndex}}_m_ami_attachment_item_id"
									class="attachment_item_{{$destIndex}}_{{$dtlIndex}} m_ami_attachment_item_id" value="{{$attachmentItem['m_ami_attachment_item_id']??''}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_attachment_item][{{$attachmentIndex}}][attachment_item_cd]"
									id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_order_dtl_attachment_item_{{$attachmentIndex}}_attachment_item_cd"
									class="attachment_item_{{$destIndex}}_{{$dtlIndex}} attachment_item_cd" value="{{$attachmentItem['attachment_item_cd']??''}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_attachment_item][{{$attachmentIndex}}][attachment_item_name]"
									id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_order_dtl_attachment_item_{{$attachmentIndex}}_attachment_item_name"
									class="attachment_item_{{$destIndex}}_{{$dtlIndex}} attachment_item_name" value="{{$attachmentItem['attachment_item_name']??''}}">
								<input type="hidden" name="register_destination[{{$destIndex}}][register_detail][{{$dtlIndex}}][order_dtl_attachment_item][{{$attachmentIndex}}][attachment_vol]"
									id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_order_dtl_attachment_item_{{$attachmentIndex}}_attachment_vol"
									class="attachment_item_{{$destIndex}}_{{$dtlIndex}} attachment_vol" value="{{$attachmentItem['attachment_vol']??''}}">
								@endforeach
							</td>
							<td class="u-center">
								<button type="button" class="btn btn-default action_edit_attachment_items" data-index="{{$destIndex}}" data-detail_index="{{$dtlIndex}}" id="register_destination_{{$destIndex}}_register_detail_{{$dtlIndex}}_edit_attachment_items" {{ $is_delete_item ? "disabled":"" }}>編集</button>
							</td>
						</tr>
							@endforeach
							<tr class="sell_detail_first">
								<td class="u-vam u-center">
								</td>
								<td class="u-vam">
								</td>
								<td class="u-vam">
									<input type="text" class="form-control u-input--small search_sell_cd" value="">
									<input type="button" data-rowid="{{$destIndex}}-0" class="btn btn-success action_sku_search"
										value="検索">
								</td>
								<td class="u-vam"></td>
								<td class="u-vam u-right">
								<td class="u-vam u-right">
									<input type="text" 	class="form-control u-input--small u-right c-box--60 search_sell_vol" value="1">
								</td>
								<td class="u-vam u-right">
								</td>
								<td class="u-vam u-center">
								</td>
								<td class="u-vam">
								</td>
								<td class="u-vam u-right">
								</td>
								<td class="u-vam u-center font-FF0000">
								</td>
							</tr>
							<tr>
								<td colspan="10" class="u-vam u-right">小計</td>
								<td class="u-vam u-right">
									<span id="sum_destination_sell_total_{{$destIndex}}" class="register_destination_amount">0</span>
								</td>
							</tr>
							<tr>
								<td colspan="10" class="u-vam u-right">送料</td>
								<td class="u-vam u-right">
									<input type="text" name="register_destination[{{$destIndex}}][shipping_fee]" id="register_destination_{{$destIndex}}_shipping_fee" class="form-control u-input--small u-right c-box--full register_destination_shipping_fee" value="{{floor($registerDestination['shipping_fee']??'0')}}">
								</td>
							</tr>
							<tr>
								<td colspan="10" class="u-vam u-right">手数料</td>
								<td class="u-vam u-right">
									<input type="text" name="register_destination[{{$destIndex}}][payment_fee]" id="register_destination_{{$destIndex}}_payment_fee" class="form-control u-input--small u-right c-box--full register_destination_payment_fee" value="{{floor($registerDestination['payment_fee']??'0')}}">
								</td>
							</tr>
							<tr>
								<td colspan="10" class="u-vam u-right">包装料</td>
								<td class="u-vam u-right">
									<input type="text" name="register_destination[{{$destIndex}}][wrapping_fee]" id="register_destination_{{$destIndex}}_wrapping_fee" class="form-control u-input--small u-right c-box--full register_destination_wrapping_fee" value="{{floor($registerDestination['wrapping_fee']??'0')}}">
								</td>
							</tr>					
						</table>
					</div>
				</div>
				@include('common.elements.error_tag', ['name' => 'register_destination.' . $destIndex . '.register_detail'])
				<!-- tabs-{{$destIndex}} end -->
				@endforeach
			</div>		
		</div>		
		<div id="line-06"></div>
			<div class="u-mt--ss c-box--1600">
				<p class="c-ttl--02">金額情報</p>
			</div>
			<div class="u-mt--ss c-box--1600">
				<table class="table table-bordered c-tbl c-tbl--1580 u-mt--ss">
					<tr class="nowrap">
						<th class="c-box--120">商品金額計</th>
						<td class="u-right c-box--100">
							<input type="hidden" name="sell_total_price" id="sell_total_price"  value="{{ old('sell_total_price', $editRow['sell_total_price'] ?? '0') }}">
							<span id="sell_total_price_text">{{ old('sell_total_price', $editRow['sell_total_price'] ?? '0') }}</span>
							@include('common.elements.error_tag', ['name' => 'sell_total_price'])
						</td>
						<th class="c-box--120">消費税(8%)</th>
						<td class="u-right c-box--100">
							<input type="hidden" name="tax_price08" id="tax_price08_val"  value="">
							<input type="hidden" name="taeget_price08" id="target_price08_val"  value="">
							<input type="hidden" name="discount_price08" id="discount_price08_val"  value="">
							<span id="tax_price08"></span>
							@include('common.elements.error_tag', ['name' => 'tax_price08'])
						</td>
						<th class="c-box--120">消費税(10%)</th>
						<td class="u-right c-box--100">
							<input type="hidden" name="tax_price10" id="tax_price10_val"  value="">
							<input type="hidden" name="taeget_price10" id="target_price10_val"  value="">
							<input type="hidden" name="discount_price10" id="discount_price10_val"  value="">
							<span id="tax_price10"></span>
							@include('common.elements.error_tag', ['name' => 'tax_price10'])
						</td>
						<th class="c-box--120">送料</th>
						<td class="u-right c-box--100">
							<input type="hidden" name="shipping_fee" id="shipping_fee"  value="{{ old('shipping_fee', $editRow['shipping_fee'] ?? '0') }}">
							<span id="shipping_fee_text">{{ old('shipping_fee', $editRow['shipping_fee'] ?? '0') }}</span>
							@include('common.elements.error_tag', ['name' => 'shipping_fee'])
						</td>
						<th class="c-box--120">手数料</th>
						<td class="u-right c-box--100">
							<input type="hidden" name="payment_fee" id="payment_fee"  value="{{ old('payment_fee', $editRow['payment_fee'] ?? '0') }}">
							<span id="payment_fee_text">{{ old('payment_fee', $editRow['payment_fee'] ?? '0') }}</span>
							@include('common.elements.error_tag', ['name' => 'payment_fee'])
						</td>
						<th class="c-box--120">包装料</th>
						<td class="u-right -box--100">
							<input type="hidden" name="package_fee" id="package_fee"  value="{{ old('package_fee', $editRow['package_fee'] ?? '0') }}">
							<span id="package_fee_text">{{ old('package_fee', $editRow['package_fee'] ?? '0') }}</span>
							@include('common.elements.error_tag', ['name' => 'package_fee'])
						</td>
						<th class="c-box--120">合計金額</th>
						<td class="u-right c-box--100">
							<input type="hidden" name="total_price" id="total_price"  value="{{ old('total_price', $editRow['total_price'] ?? '0') }}">
							<span id="total_price_text">{{ old('total_price', $editRow['total_price'] ?? '0') }}</span>
						</td>
					</tr>
					<tr>
						<th>割引金額</th>
						<td class="u-right">
							<input type="text" name="discount" id="discount" class="form-control u-input--small u-right font-FF0000" value=" {{ old('discount', floor($editRow['discount'] ?? '0')) }}">
							@include('common.elements.error_tag', ['name' => 'discount'])
						</td>
						<th>ストアクーポン</th>
						<td class="u-right">
							<input type="text" name="use_coupon_store" id="use_coupon_store" class="form-control u-input--small u-right font-FF0000" value="{{ old('use_coupon_store',floor($editRow['use_coupon_store'] ?? '0')) }}">
							@include('common.elements.error_tag', ['name' => 'use_coupon_store'])
						</td>
						<th>モールクーポン</th>
						<td class="u-right">
							<input type="text" name="use_coupon_mall" id="use_coupon_mall" class="form-control u-input--small u-right font-FF0000" value="{{ old('use_coupon_mall',floor($editRow['use_coupon_mall'] ?? '0')) }}">
							@include('common.elements.error_tag', ['name' => 'use_coupon_mall'])
						</td>
						<th>クーポン合計</th>
						<td class="u-right font-FF0000">
							<input type="hidden" name="total_use_coupon" id="total_use_coupon"  value="{{ old('total_use_coupon', $editRow['total_use_coupon'] ?? '0') }}">
							<span id="total_use_coupon_text"><b>{{ old('total_use_coupon', $editRow['total_use_coupon'] ?? '0') }}</b></span>
							@include('common.elements.error_tag', ['name' => 'total_use_coupon'])
						</td>
						<th>利用ポイント</th>
						<td class="u-right">
							<input type="text" name="use_point" id="use_point" class="form-control u-input--small u-right font-FF0000" value="{{ old('use_point',floor($editRow['use_point'] ?? '0')) }}">
							@include('common.elements.error_tag', ['name' => 'use_point'])
						</td>
						<th colspan="2">請求金額</th>
						<td colspan="2" class="u-right font-FF0000">
							<input type="hidden" name="order_total_price" id="order_total_price"  value="{{ old('order_total_price', $editRow['order_total_price'] ?? '0') }}">
							<span id="order_total_price_text"><b>{{ old('order_total_price', $editRow['order_total_price'] ?? '0') }}</b></span>
							@include('common.elements.error_tag', ['name' => 'order_total_price'])
						</td>
					</tr>
				</table>			
			</div>
			<div id="line-07"></div>
			<div class="u-mt--ss">
				<p class="c-ttl--02">決済情報</p>
			</div>
			<table class="table table-bordered c-tbl c-tbl--1200 u-mt--ss">
				<tr>
					<th class="c-box--200 must">支払い方法</th>
					<td>
						<select name="m_pay_type_id" id="m_pay_type_id" @if(!empty($editRow['paytype_readonly']) && $editRow['paytype_readonly']=='readonly' ){{'disabled'}}@endif class="form-control u-input--mid">
							@include('common.elements.NEOSM211_option_list', ['arrayName' => $viewExtendData['m_payment_types'], 'currentId' => (isset($editRow['m_pay_type_id']) ? $editRow['m_pay_type_id'] : '')])
						</select>
						@if(!empty($editRow['paytype_readonly']) && $editRow['paytype_readonly']=='readonly')
						<input type="hidden" name="m_pay_type_id" value="{{ old('m_pay_type_id', $editRow['m_pay_type_id'] ?? '') }}">
						@endif
						@include('common.elements.error_tag', ['name' => 'm_pay_type_id'])
						<input type="hidden" name="transfer_fee" id="transfer_fee" class="form-control c-box--300" value="{{ old('transfer_fee', floor($editRow['transfer_fee'] ?? '0')) }}">
					</td>
				</tr>
				<tr>
					<th class="c-box--200">請求メモ</th>
					<td>
						<textarea name="billing_comment" id="billing_comment" rows="5" class="form-control u-input--mid c-box--full" style="resize: vertical">{{ old('billing_comment', $editRow['billing_comment'] ?? '') }}</textarea>
						@include('common.elements.error_tag', ['name' => 'billing_comment'])
					</td>
				</tr>
				<tr>
					<th class="c-box--200">後払い決済 取引ID</th>
					<td>
						<input type="text" name="payment_transaction_id" id="payment_transaction_id" {{ old('paytype_readonly', $editRow['paytype_readonly'] ?? '') }} class="form-control c-box--300" value="{{ old('payment_transaction_id', $editRow['payment_transaction_id'] ?? '') }}">
						@include('common.elements.error_tag', ['name' => 'payment_transaction_id'])
					</td>
				</tr>
				<tr>
					<th class="c-box--200">支払回数</th>
					<td>
						<input type="text" name="card_pay_times" id="card_pay_times" {{ old('paytype_readonly', $editRow['paytype_readonly'] ?? '') }} class="form-control u-input--small u-right" value="{{ old('card_pay_times', $editRow['card_pay_times'] ?? '') }}">
						@include('common.elements.error_tag', ['name' => 'card_pay_times'])
					</td>
				</tr>
				<tr>
					<th class="c-box--200">後払い決済請求書送付方法</th>
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
			</table>
			@include('common.elements.error_tag', ['name' => 'card_company'])
			@include('common.elements.error_tag', ['name' => 'card_holder'])
			@include('common.elements.error_tag', ['name' => 'tax_rate'])
			@include('common.elements.error_tag', ['name' => 'operator_id'])
			@include('common.elements.error_tag', ['name' => 'order_dtl_coupon_id'])
			@include('common.elements.error_tag', ['name' => 'reservation_skip_flg'])
			@include('common.elements.error_tag', ['name' => 'credit_type'])
			@include('common.elements.error_tag', ['name' => 'payment_type'])

{{--

			<!-- Additional fields -->
			<input type="text" name="sum_sell_total" value="1000">
			<input type="text" name="shipping_fee" value="0">
			<input type="text" name="payment_fee" value="0">
			<input type="text" name="wrapping_fee" value="0">
			<input type="text" name="m_delivery_time_hope_id" value="">
			<input type="text" name="sell_total_price" value="1000">
			<input type="text" name="tax_price" value="100">
			<input type="text" name="discount" value="0">
			<input type="text" name="use_coupon_store" value="0">
			<input type="text" name="use_coupon_mall" value="0">
			<input type="text" name="total_use_coupon" value="0">
			<input type="text" name="use_point" value="0">
			<input type="text" name="order_total_price" value="1000">
			<input type="text" name="package_fee" value="0">

	<hr>

	<!-- Billing Information -->
<div>
    <label for="billing_m_cust_id">billing m cust id</label>
    <input type="text" name="billing_m_cust_id" id="billing_m_cust_id" value="1">
</div>
    <div>
        <label for="billing_address1">Billing Postal</label>
        <input type="text" name="billing_postal" id="billing_postal" value="1001000">
    </div>
    <div>
        <label for="billing_address1">Billing Address 1</label>
        <input type="text" name="billing_address1" id="billing_address1" value="Sample Address 1">
    </div>
    <div>
        <label for="billing_address2">Billing Address 2</label>
        <input type="text" name="billing_address2" id="billing_address2" value="Sample Address 2">
    </div>
    <div>
        <label for="billing_address2">Billing Address 3</label>
        <input type="text" name="billing_address3" id="billing_address3" value="">
    </div>
    <div>
        <label for="billing_address2">Billing Address 4</label>
        <input type="text" name="billing_address4" id="billing_address4" value="">
    </div>
    <div>
        <label for="billing_name">Billing Name</label>
        <input type="text" name="billing_name" id="billing_name" value="John Doe">
    </div>
	<hr>

    <!-- Register Destination -->

	
		    <!-- Order destination -->
			<input type="text" name="register_destination[0][order_destination_seq]" value="1">
    <input type="text" name="register_destination[0][destination_tel]" value="{{ $editRow['order_tel1'] }}">
    <input type="text" name="register_destination[0][destination_name_kana]" value="{{ $editRow['order_name_kana'] }}">
    <input type="text" name="register_destination[0][destination_name]" value="{{ $editRow['order_name'] }}">
    <input type="text" name="register_destination[0][m_delivery_type_id]" value="1">
    <input type="text" name="register_destination[0][destination_postal]" value="{{ $editRow['order_postal'] }}">

    <!-- Register Detail 1 -->
    <input type="text" name="register_destination[0][register_detail][0][t_order_dtl_id]" value="">
    <input type="text" name="register_destination[0][register_detail][0][order_dtl_seq]" value="1">
    <input type="text" name="register_destination[0][register_detail][0][sell_id]" value="1">
    <input type="text" name="register_destination[0][register_detail][0][sell_checked]" value="1">
    <input type="text" name="register_destination[0][register_detail][0][sku_data]" value='{"ecs_id":"1","sell_id":"1","sell_cd":"TEST01","sell_option":"","sell_type":1,"sku_dtl":[{"t_order_dtl_sku_id":null,"item_id":1,"item_cd":"TEST","compose_vol":1}]}'>
    <input type="text" name="register_destination[0][register_detail][0][tax_rate]" value="0.100">
    <input type="text" name="register_destination[0][register_detail][0][sell_cd]" value="TEST01">
    <input type="text" name="register_destination[0][register_detail][0][sell_name]" value="販売名111">
    <input type="text" name="register_destination[0][register_detail][0][order_sell_price]" value="10,000">
    <input type="text" name="register_destination[0][register_detail][0][order_sell_vol]" value="1">
    <input type="text" name="register_destination[0][register_detail][0][btn_delete_visible]" value="1">

    <!-- Register Detail 2 -->
    <input type="text" name="register_destination[0][register_detail][1][t_order_dtl_id]" value="">
    <input type="text" name="register_destination[0][register_detail][1][order_dtl_seq]" value="2">
    <input type="text" name="register_destination[0][register_detail][1][order_sell_vol]" value="1">

    <div>
        <label for="destination_address1">Destination Address 1</label>
        <input type="text" name="register_destination[0][destination_address1]" id="destination_address1" value="Sample Destination Address 1">
    </div>
    <div>
        <label for="destination_address2">Destination Address 2</label>
        <input type="text" name="register_destination[0][destination_address2]" id="destination_address2" value="Sample Destination Address 2">
    </div>
    <div>
        <label for="destination_address1">Destination Address 3</label>
        <input type="text" name="register_destination[0][destination_address3]" id="destination_address3" value="">
    </div>
    <div>
        <label for="destination_address2">Destination Address 4</label>
        <input type="text" name="register_destination[0][destination_address4]" id="destination_address4" value="">
    </div>
    <div>
        <label for="deli_plan_date">出荷予定日</label>
        <input type="text" name="register_destination[0][deli_plan_date]" id="deli_plan_date" value="">
    </div>
    <div>
        <label for="destination_name">Destination Name</label>
        <input type="text" name="register_destination[0][destination_name]" id="destination_name" value="Jane Doe">
    </div>
    <div>
        <label for="delivery_name">Delivery Name</label>
        <input type="text" name="register_destination[0][delivery_name]" id="delivery_name" value="Sample Delivery Name">
    </div>
    <div>
        <label for="shipping_fee">Shipping Fee</label>
        <input type="text" name="register_destination[0][shipping_fee]" id="shipping_fee" value="500">
    </div>
    <div>
        <label for="payment_fee">Payment Fee</label>
        <input type="text" name="register_destination[0][payment_fee]" id="payment_fee" value="200">
    </div>
    <div>
        <label for="wrapping_fee">Wrapping Fee</label>
        <input type="text" name="register_destination[0][wrapping_fee]" id="wrapping_fee" value="100">
    </div>
    <div>
        <label for="wrapping_fee">Wrapping Fee</label>
        <input type="text" name="register_destination[0][wrapping_fee]" id="wrapping_fee" value="100">
    </div>

    <!-- Register Detail 1 -->
    <div>
        <label for="sell_cd_1">Sell Code 1</label>
        <input type="text" name="register_destination[0][register_detail][1][sell_cd]" id="sell_cd_1" value="SAMPLECD1">
    </div>
    <div>
        <label for="sell_name_1">Sell Name 1</label>
        <input type="text" name="register_destination[0][register_detail][1][sell_name]" id="sell_name_1" value="Sample Sell Name 1">
    </div>
    <div>
        <label for="order_sell_price_0">Order Sell Price 0</label>
        <input type="number" name="register_destination[0][register_detail][0][order_sell_price]" id="order_sell_price_0" value="1000">
    </div>
    <div>
        <label for="order_sell_price_1">Order Sell Price 1</label>
        <input type="number" name="register_destination[0][register_detail][1][order_sell_price]" id="order_sell_price_1" value="1500">
    </div>
    <div>
        <label for="tax_rate_1">Tax Rate 1</label>
        <input type="text" name="register_destination[0][register_detail][1][tax_rate]" id="tax_rate_1" value="0.10">
    </div>

    <!-- Noshi Details for Detail 0 -->
    <div>
        <label for="t_order_dtl_noshi_0">T Order Dtl Noshi 0</label>
        <input type="text" name="register_destination[0][register_detail][0][t_order_dtl_noshi]" id="t_order_dtl_noshi_0" value="Noshi 0">
    </div>
    <div>
        <label for="t_order_dtl_noshi_1">T Order Dtl Noshi 1</label>
        <input type="text" name="register_destination[0][register_detail][1][t_order_dtl_noshi]" id="t_order_dtl_noshi_1" value="Noshi 1">
    </div>
    <div>
        <label for="noshi_count_0">Noshi Count 0</label>
        <input type="number" name="register_destination[0][register_detail][0][t_order_dtl_noshi][count]" id="noshi_count_0" value="1">
    </div>
    <div>
        <label for="noshi_count_1">Noshi Count 1</label>
        <input type="number" name="register_destination[0][register_detail][1][t_order_dtl_noshi][count]" id="noshi_count_1" value="1">
    </div>
    <div>
        <label for="noshi_detail_id_0">Noshi Detail ID 0</label>
        <input type="text" name="register_destination[0][register_detail][0][t_order_dtl_noshi][noshi_detail_id]" id="noshi_detail_id_0" value="1">
    </div>
    <div>
        <label for="noshi_detail_id_1">Noshi Detail ID 1</label>
        <input type="text" name="register_destination[0][register_detail][1][t_order_dtl_noshi][noshi_detail_id]" id="noshi_detail_id_1" value="1">
    </div>
    <div>
        <label for="m_noshi_naming_pattern_id_0">Noshi Naming Pattern ID 0</label>
        <input type="text" name="register_destination[0][register_detail][0][t_order_dtl_noshi][m_noshi_naming_pattern_id]" id="m_noshi_naming_pattern_id_0" value="1">
    </div>
    <div>
        <label for="m_noshi_naming_pattern_id_1">Noshi Naming Pattern ID 1</label>
        <input type="text" name="register_destination[0][register_detail][1][t_order_dtl_noshi][m_noshi_naming_pattern_id]" id="m_noshi_naming_pattern_id_1" value="1">
    </div>
    <div>
        <label for="attach_flg">Attach Flag</label>
        <input type="checkbox" name="register_destination[0][register_detail][0][t_order_dtl_noshi][attach_flg]" id="attach_flg" value="1">
    </div>

    <!-- Register Detail SKU -->
    <div>
        <label for="register_detail_sku_0">Register Detail SKU 0</label>
        <input type="text" name="register_destination[0][register_detail][0][register_detail_sku][0]" id="register_detail_sku_0" value="SKU0">
    </div>
    <div>
        <label for="register_detail_sku_1">Register Detail SKU 1</label>
        <input type="text" name="register_destination[0][register_detail][1][register_detail_sku][0]" id="register_detail_sku_1" value="SKU1">
    </div>

	<!-- Register Detail SKU for Detail 0 -->
<div>
    <label for="item_cd_0_0">Item Code 0.0</label>
    <input type="text" name="register_destination[0][register_detail][0][register_detail_sku][0][item_cd]" id="item_cd_0_0" value="ITEMCD0_0">
</div>
<div>
    <label for="item_vol_0_0">Item Volume 0.0</label>
    <input type="number" name="register_destination[0][register_detail][0][register_detail_sku][0][item_vol]" id="item_vol_0_0" value="10">
</div>

<!-- Register Detail SKU for Detail 1 -->
<div>
    <label for="item_cd_1_0">Item Code 1.0</label>
    <input type="text" name="register_destination[0][register_detail][1][register_detail_sku][0][item_cd]" id="item_cd_1_0" value="ITEMCD1_0">
</div>
<div>
    <label for="item_vol_1_0">Item Volume 1.0</label>
    <input type="number" name="register_destination[0][register_detail][1][register_detail_sku][0][item_vol]" id="item_vol_1_0" value="20">
</div>
    <div>
        <label for="attach_flg">キャンペーン対象</label>
        <input type="checkbox" name="register_destination[0][campaign_flg]" id="campaign_flg" value="1" checked>
    </div>

<hr>


		    <!-- Order destination -->
			<input type="text" name="register_destination[1][order_destination_seq]" value="2">
    <input type="text" name="register_destination[1][destination_tel]" value="{{ $editRow['order_tel1'] }}">
    <input type="text" name="register_destination[1][destination_name_kana]" value="{{ $editRow['order_name_kana'] }}">
    <input type="text" name="register_destination[1][destination_name]" value="{{ $editRow['order_name'] }}">
    <input type="text" name="register_destination[1][m_delivery_type_id]" value="1">
    <input type="text" name="register_destination[1][destination_postal]" value="{{ $editRow['order_postal'] }}">

    <!-- Register Detail 1 -->
    <input type="text" name="register_destination[1][register_detail][0][t_order_dtl_id]" value="">
    <input type="text" name="register_destination[1][register_detail][0][order_dtl_seq]" value="1">
    <input type="text" name="register_destination[1][register_detail][0][sell_id]" value="1">
    <input type="text" name="register_destination[1][register_detail][0][sell_checked]" value="1">
    <input type="text" name="register_destination[1][register_detail][0][sku_data]" value='{"ecs_id":"1","sell_id":"1","sell_cd":"TEST01","sell_option":"","sell_type":1,"sku_dtl":[{"t_order_dtl_sku_id":null,"item_id":1,"item_cd":"TEST","compose_vol":1}]}'>
    <input type="text" name="register_destination[1][register_detail][0][tax_rate]" value="0.100">
    <input type="text" name="register_destination[1][register_detail][0][sell_cd]" value="TEST01">
    <input type="text" name="register_destination[1][register_detail][0][sell_name]" value="販売名111">
    <input type="text" name="register_destination[1][register_detail][0][order_sell_price]" value="10,000">
    <input type="text" name="register_destination[1][register_detail][0][order_sell_vol]" value="1">
    <input type="text" name="register_destination[1][register_detail][0][btn_delete_visible]" value="1">

    <!-- Register Detail 2 -->
    <input type="text" name="register_destination[1][register_detail][1][t_order_dtl_id]" value="">
    <input type="text" name="register_destination[1][register_detail][1][order_dtl_seq]" value="2">
    <input type="text" name="register_destination[1][register_detail][1][order_sell_vol]" value="1">

    <!-- Register Destination -->
    <div>
        <label for="destination_address1">Destination Address 1</label>
        <input type="text" name="register_destination[1][destination_address1]" id="destination_address1" value="Sample Destination Address 1">
    </div>
    <div>
        <label for="destination_address2">Destination Address 2</label>
        <input type="text" name="register_destination[1][destination_address2]" id="destination_address2" value="Sample Destination Address 2">
    </div>
    <div>
        <label for="destination_address1">Destination Address 3</label>
        <input type="text" name="register_destination[1][destination_address3]" id="destination_address3" value="">
    </div>
    <div>
        <label for="destination_address2">Destination Address 4</label>
        <input type="text" name="register_destination[1][destination_address4]" id="destination_address4" value="">
    </div>
    <div>
        <label for="deli_plan_date">出荷予定日</label>
        <input type="text" name="register_destination[1][deli_plan_date]" id="deli_plan_date" value="">
    </div>
    <div>
        <label for="destination_name">Destination Name</label>
        <input type="text" name="register_destination[1][destination_name]" id="destination_name" value="Jane Doe">
    </div>
    <div>
        <label for="delivery_name">Delivery Name</label>
        <input type="text" name="register_destination[1][delivery_name]" id="delivery_name" value="Sample Delivery Name">
    </div>
    <div>
        <label for="shipping_fee">Shipping Fee</label>
        <input type="text" name="register_destination[1][shipping_fee]" id="shipping_fee" value="500">
    </div>
    <div>
        <label for="payment_fee">Payment Fee</label>
        <input type="text" name="register_destination[1][payment_fee]" id="payment_fee" value="200">
    </div>
    <div>
        <label for="wrapping_fee">Wrapping Fee</label>
        <input type="text" name="register_destination[1][wrapping_fee]" id="wrapping_fee" value="100">
    </div>
    <div>
        <label for="wrapping_fee">Wrapping Fee</label>
        <input type="text" name="register_destination[1][wrapping_fee]" id="wrapping_fee" value="100">
    </div>

    <!-- Register Detail 1 -->
    <div>
        <label for="sell_cd_1">Sell Code 1</label>
        <input type="text" name="register_destination[1][register_detail][1][sell_cd]" id="sell_cd_1" value="SAMPLECD1">
    </div>
    <div>
        <label for="sell_name_1">Sell Name 1</label>
        <input type="text" name="register_destination[1][register_detail][1][sell_name]" id="sell_name_1" value="Sample Sell Name 1">
    </div>
    <div>
        <label for="order_sell_price_0">Order Sell Price 0</label>
        <input type="number" name="register_destination[1][register_detail][0][order_sell_price]" id="order_sell_price_0" value="1000">
    </div>
    <div>
        <label for="order_sell_price_1">Order Sell Price 1</label>
        <input type="number" name="register_destination[1][register_detail][1][order_sell_price]" id="order_sell_price_1" value="1500">
    </div>
    <div>
        <label for="tax_rate_1">Tax Rate 1</label>
        <input type="text" name="register_destination[1][register_detail][1][tax_rate]" id="tax_rate_1" value="0.10">
    </div>

    <!-- Noshi Details for Detail 0 -->
    <div>
        <label for="t_order_dtl_noshi_0">T Order Dtl Noshi 0</label>
        <input type="text" name="register_destination[1][register_detail][0][t_order_dtl_noshi]" id="t_order_dtl_noshi_0" value="Noshi 0">
    </div>
    <div>
        <label for="t_order_dtl_noshi_1">T Order Dtl Noshi 1</label>
        <input type="text" name="register_destination[1][register_detail][1][t_order_dtl_noshi]" id="t_order_dtl_noshi_1" value="Noshi 1">
    </div>
    <div>
        <label for="noshi_count_0">Noshi Count 0</label>
        <input type="number" name="register_destination[1][register_detail][0][t_order_dtl_noshi][count]" id="noshi_count_0" value="1">
    </div>
    <div>
        <label for="noshi_count_1">Noshi Count 1</label>
        <input type="number" name="register_destination[1][register_detail][1][t_order_dtl_noshi][count]" id="noshi_count_1" value="1">
    </div>
    <div>
        <label for="noshi_detail_id_0">Noshi Detail ID 0</label>
        <input type="text" name="register_destination[1][register_detail][0][t_order_dtl_noshi][noshi_detail_id]" id="noshi_detail_id_0" value="1">
    </div>
    <div>
        <label for="noshi_detail_id_1">Noshi Detail ID 1</label>
        <input type="text" name="register_destination[1][register_detail][1][t_order_dtl_noshi][noshi_detail_id]" id="noshi_detail_id_1" value="1">
    </div>
    <div>
        <label for="m_noshi_naming_pattern_id_0">Noshi Naming Pattern ID 0</label>
        <input type="text" name="register_destination[1][register_detail][0][t_order_dtl_noshi][m_noshi_naming_pattern_id]" id="m_noshi_naming_pattern_id_0" value="1">
    </div>
    <div>
        <label for="m_noshi_naming_pattern_id_1">Noshi Naming Pattern ID 1</label>
        <input type="text" name="register_destination[1][register_detail][1][t_order_dtl_noshi][m_noshi_naming_pattern_id]" id="m_noshi_naming_pattern_id_1" value="1">
    </div>
    <div>
        <label for="attach_flg">Attach Flag</label>
        <input type="checkbox" name="register_destination[1][register_detail][1][t_order_dtl_noshi][attach_flg]" id="attach_flg" value="1" checked>
    </div>

    <!-- Register Detail SKU -->
    <div>
        <label for="register_detail_sku_0">Register Detail SKU 0</label>
        <input type="text" name="register_destination[1][register_detail][0][register_detail_sku][0]" id="register_detail_sku_0" value="SKU0">
    </div>
    <div>
        <label for="register_detail_sku_1">Register Detail SKU 1</label>
        <input type="text" name="register_destination[1][register_detail][1][register_detail_sku][0]" id="register_detail_sku_1" value="SKU1">
    </div>

	<!-- Register Detail SKU for Detail 0 -->
<div>
    <label for="item_cd_0_0">Item Code 0.0</label>
    <input type="text" name="register_destination[1][register_detail][0][register_detail_sku][0][item_cd]" id="item_cd_0_0" value="ITEMCD0_0">
</div>
<div>
    <label for="item_vol_0_0">Item Volume 0.0</label>
    <input type="number" name="register_destination[1][register_detail][0][register_detail_sku][0][item_vol]" id="item_vol_0_0" value="10">
</div>

<!-- Register Detail SKU for Detail 1 -->
<div>
    <label for="item_cd_1_0">Item Code 1.0</label>
    <input type="text" name="register_destination[1][register_detail][1][register_detail_sku][0][item_cd]" id="item_cd_1_0" value="ITEMCD1_0">
</div>
<div>
    <label for="item_vol_1_0">Item Volume 1.0</label>
    <input type="number" name="register_destination[1][register_detail][1][register_detail_sku][0][item_vol]" id="item_vol_1_0" value="20">
</div>
    <div>
        <label for="attach_flg">キャンペーン対象</label>
        <input type="checkbox" name="register_destination[1][campaign_flg]" id="campaign_flg" value="1">
    </div>
<hr>

    <div>
        <label for="campaign_flg">キャンペーンフラグ</label>
        <input type="text" name="campaign_flg" id="campaign_flg" value="0">
    </div>

	@foreach ($errors->all() as $error)
	<li>{{$error}}</li>
	@endforeach
--}}

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

<div id="attachment_dialog_item_template_area" style="display:none;">
	<table>
		<tbody>
		<tr class="attachment_item_detail attachment_item_detail_view_##display_flg##">
			<td>
				<input type="hidden" class="t_order_dtl_attachment_item_id" value="##t_order_dtl_attachment_item_id##">
				<input type="hidden" class="t_order_dtl_id" value="##t_order_dtl_id##">
				<input type="hidden" class="display_flg" value="##display_flg##">
				<input type="hidden" class="m_ami_attachment_item_id" value="##m_ami_attachment_item_id##">
				<input type="text"readonly class="form-control u-input--full attachment_item_cd" value="##attachment_item_cd##">
			</td>
			<td><input type="text" name="attachment_item_name" class="form-control u-input--full  attachment_item_name" value="##attachment_item_name##"></td>
			<td><input type="text" name="attachment_vol" class="form-control u-input--small u-right attachment_vol" value="##attachment_vol##"></td>
			<td class="u-right"><button class="btn btn-danger action_attachment_item_delete">削除</button></td>
		</tr>
		</tbody>
	</table>
</div>
<div id="attachment_item_template_area" class="c-box--1600" style="display:none;">
	<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_attachment_item][##attachmentIndex##][attachment_index]"
		id="register_destination_##index0##_register_detail_##detailindex##_order_dtl_attachment_item_##attachmentIndex##_attachment_index"
		class="attachment_item_##index0##_##detailindex## attachment_index" value="##attachmentIndex##">
	<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_attachment_item][##attachmentIndex##][t_order_dtl_attachment_item_id]"
		id="register_destination_##index0##_register_detail_##detailindex##_order_dtl_attachment_item_##attachmentIndex##_t_order_dtl_attachment_item_id"
		class="attachment_item_##index0##_##detailindex## order_dtl_attachment_item_id" value="##t_order_dtl_attachment_item_id##">
	<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_attachment_item][##attachmentIndex##][t_order_dtl_id]"
		id="register_destination_##index0##_register_detail_##detailindex##_order_dtl_attachment_item_##attachmentIndex##_t_order_dtl_id"
		class="attachment_item_##index0##_##detailindex## t_order_dtl_id" value="##t_order_dtl_id##">
	<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_attachment_item][##attachmentIndex##][display_flg]"
		id="register_destination_##index0##_register_detail_##detailindex##_order_dtl_attachment_item_##attachmentIndex##_display_flg"
		class="attachment_item_##index0##_##detailindex## display_flg" value="##display_flg##">
	<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_attachment_item][##attachmentIndex##][m_ami_attachment_item_id]"
		id="register_destination_##index0##_register_detail_##detailindex##_order_dtl_attachment_item_##attachmentIndex##_m_ami_attachment_item_id"
		class="attachment_item_##index0##_##detailindex## m_ami_attachment_item_id" value="##m_ami_attachment_item_id##">
	<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_attachment_item][##attachmentIndex##][attachment_item_cd]"
		id="register_destination_##index0##_register_detail_##detailindex##_order_dtl_attachment_item_##attachmentIndex##_attachment_item_cd"
		class="attachment_item_##index0##_##detailindex## attachment_item_cd" value="##attachment_item_cd##">
	<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_attachment_item][##attachmentIndex##][attachment_item_name]"
		id="register_destination_##index0##_register_detail_##detailindex##_order_dtl_attachment_item_##attachmentIndex##_attachment_item_name"
		class="attachment_item_##index0##_##detailindex## attachment_item_name" value="##attachment_item_name##">
	<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_attachment_item][##attachmentIndex##][attachment_vol]"
		id="register_destination_##index0##_register_detail_##detailindex##_order_dtl_attachment_item_##attachmentIndex##_attachment_vol"
		class="attachment_item_##index0##_##detailindex## attachment_vol" value="##attachment_vol##">
</div>

<div id="sell_detail_template_area" class="c-box--1600" style="display:none;">
	<table class="table table-bordered c-tbl c-tbl--1580 u-mt--ss detail_sell_table">
		<tbody>
			<tr class="detail_area detail_row_##index0##_##detailindex##" data-index="##index0##" data-detail_index="##detailindex##">
				<td class="u-vasm u-center" rowspan="3">
					<label for="">
						<input type="checkbox" name="register_destination[##index0##][register_detail][##detailindex##][check_copy]"
							id="register_destination_##index0##_register_detail_##detailindex##_check_copy" data-rowid="##index0##-##detailindex##" data-index="##index0##" data-detail_index="##detailindex##" class="checkbox check_copy_detail"
							value="1">
					</label>
				</td>
				<td rowspan="3" class="u-center">
					<button type="button" class="btn btn-danger action_remove_register_destination_detail" data-index="##index0##" data-detail_index="##detailindex##">削除</button>
				</td>
				<td class="u-vam">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][t_order_dtl_sku_id]" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][t_order_dtl_id]" id="register_destination_##index0##_register_detail_##detailindex##_t_order_dtl_id" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_seq]" value="##detailseqnumber##" class="order_dtl_seq">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][t_deli_hdr_id]" value=""> <!-- not used?-->
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][cancel_timestamp]" value="" id="register_destination_##index0##_register_detail_##detailindex##_cancel_timestamp" class="register_destination_register_detail_cancel_timestamp">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][cancel_flg]" value=""  id="register_destination_##index0##_register_detail_##detailindex##_cancel_flg" class="register_destination_register_detail_cancel_flg">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][reservation_date]" value=""> <!-- not used?-->
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][variation_values]"
						id="register_destination_##index0##_register_detail_##detailindex##_variation_values" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][sell_id]"
						id="register_destination_##index0##_register_detail_##detailindex##_sell_id" value="##sell_id##">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][sell_checked]" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][sku_data]" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][three_temperature_zone_type]" class="three_temperature_zone_type" id="register_destination_##index0##_register_detail_##detailindex##_three_temperature_zone_type" value="##three_temperature_zone_type##">
					<input type="text" name="register_destination[##index0##][register_detail][##detailindex##][sell_cd]" readonly
						id="register_destination_##index0##_register_detail_##detailindex##_sell_cd" class="form-control u-input--mid"
						value="##sell_cd##">
				</td>
				<td class="u-vam">
					<textarea name="register_destination[##index0##][register_detail][##detailindex##][sell_name]" id="register_destination_##index0##_register_detail_##detailindex##_sell_name" rows="1"
						class="form-control u-input--mid c-box--full" style="resize: vertical">##sell_name##</textarea>
				</td>
				<td class="u-vam u-right">
					<span>##sales_price_format##</span>
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_sell_price]"
						id="register_destination_##index0##_register_detail_##detailindex##_order_sell_price" data-rowid="##index0##-##detailindex##"
						class="form-control u-input--small u-right register_destination_register_detail_order_sell_price" value="##sales_price##">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][tax_rate]"
						id="register_destination_##index0##_register_detail_##detailindex##_tax_rate" data-rowid="##index0##-0"
						class="form-control u-input--small register_destination_register_detail_tax_rate" value="##tax_rate##">
				</td>
				<td class="u-vam u-right">
					<input type="text" name="register_destination[##index0##][register_detail][##detailindex##][order_sell_vol]"
						id="register_destination_##index0##_register_detail_##detailindex##_order_sell_vol" data-rowid="##index0##-##detailindex##"
						class="form-control u-input--small u-right c-box--60 register_destination_register_detail_sell_vol" value="##order_sell_vol##">
				</td>
				<td class="u-vam u-right">
					<span class="register_destination_register_detail_sell_amount"></span>
					<input class="register_destination_register_detail_order_sell_amount" type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_sell_amount]" id="register_destination_##index0##_register_detail_##detailindex##_order_sell_amount">
				</td>
				<td class="u-vam">
					<span class="register_destination_register_detail_stock_state"></span>
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][drawing_status_name]" id="register_destination_##index0##_register_detail_##detailindex##_drawing_status_name">
					</td>
				<td class="u-vam">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_coupon_id]"
						id="register_destination_##index0##_register_detail_##detailindex##_order_dtl_coupon_id" data-rowid="##index0##-##detailindex##"
						class="form-control u-input--small u-right register_destination_register_detail_order_dtl_coupon_id" value="##order_dtl_coupon_id##">
					<span class="register_destination_register_detail_order_dtl_coupon_id">##order_dtl_coupon_id##</span>
				</td>
				<td class="u-vam">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_coupon_price]"
						id="register_destination_##index0##_register_detail_##detailindex##_order_dtl_coupon_price" data-rowid="##index0##-##detailindex##"
						class="form-control u-input--small u-right register_destination_register_detail_order_dtl_coupon_price" value="##order_dtl_coupon_price##">
					<span class="register_destination_register_detail_order_dtl_coupon_price">##order_dtl_coupon_price_format##</span>
				</td>
				<td class="u-vam">
					<select name="register_destination[##index0##][register_detail][##detailindex##][attachment_item_group_id]" id="register_destination_##index0##_register_detail_##detailindex##_attachment_item_group_id" class="form-control c-box--full action_change_attachment_item_group_id" data-index="##index0##" data-detail_index="##detailindex##">
						@foreach($viewExtendData['attachment_group']??[] as $val)
						<option value="{{$val['m_itemname_types_id']}}">{{$val['m_itemname_type_name']}}</option>
						@endforeach
					</select>
				</td>
			</tr>
			<tr class="detail_area2 detail_row_##index0##_##detailindex##">
				<td rowspan="2">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][image_path]"
						id="register_destination_##index0##_register_detail_##detailindex##_image_path" data-rowid="##index0##-##detailindex##"
						class="" value="##image_path##">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][m_ami_page_id]"
						id="register_destination_##index0##_register_detail_##detailindex##_m_ami_page_id" data-rowid="##index0##-##detailindex##"
						class="" value="##m_ami_page_id##">
					<div class="detail_item_thumb">
						<div class='item-image-preview'>
							<img src="{{'/'.config('filesystems.resources_dir').'/'.$esmSessionManager->getAccountCode().'/image/page/'}}##m_ami_page_id##/##image_path##" class="action_item_zoom">
							<span class="item-image-preview-glass action_item_zoom">
								<span class="glyphicon glyphicon-zoom-in" aria-hidden="true"></span>
							</span>
						</div>
					</div>
				</td>
				<td rowspan="2">
					<div style="height:100px;">
						<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][page_desc]"
							id="register_destination_##index0##_register_detail_##detailindex##_page_desc" data-rowid="##index0##-##detailindex##"
							class="" value="##page_desc_hidden##">
						<div class="detail_item_html">##page_desc##</div>
					</div>
				</td>
				<th>熨斗</th>
				<td colspan="5">
					<div id="register_destination_##index0##_register_detail_##detailindex##_noshi_html"></div>
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][t_order_dtl_noshi_id]" id="register_destination_##index0##_register_detail_##detailindex##_t_order_dtl_noshi_id" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][noshi_id]" id="register_destination_##index0##_register_detail_##detailindex##_noshi_id" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][noshi_detail_id]" id="register_destination_##index0##_register_detail_##detailindex##_noshi_detail_id" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][noshi_detail_name]" id="register_destination_##index0##_register_detail_##detailindex##_noshi_detail_name" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][m_noshi_format_id]" id="register_destination_##index0##_register_detail_##detailindex##_m_noshi_format_id" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][m_noshi_format_name]" id="register_destination_##index0##_register_detail_##detailindex##_m_noshi_format_name" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][m_noshi_naming_pattern_id]" id="register_destination_##index0##_register_detail_##detailindex##_m_noshi_naming_pattern_id" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][m_noshi_naming_pattern_name]" id="register_destination_##index0##_register_detail_##detailindex##_m_noshi_naming_pattern_name" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][omotegaki]" id="register_destination_##index0##_register_detail_##detailindex##_omotegaki" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][attach_flg]" id="register_destination_##index0##_register_detail_##detailindex##_attach_flg" value="0">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][company_name_count]" id="register_destination_##index0##_register_detail_##detailindex##_company_name_count" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][section_name_count]" id="register_destination_##index0##_register_detail_##detailindex##_section_name_count" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][title_count]" id="register_destination_##index0##_register_detail_##detailindex##_title_count" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][f_name_count]" id="register_destination_##index0##_register_detail_##detailindex##_f_name_count" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][name_count]" id="register_destination_##index0##_register_detail_##detailindex##_name_count" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][ruby_count]" id="register_destination_##index0##_register_detail_##detailindex##_ruby_count" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][company_name1]" id="register_destination_##index0##_register_detail_##detailindex##_company_name1" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][company_name2]" id="register_destination_##index0##_register_detail_##detailindex##_company_name2" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][company_name3]" id="register_destination_##index0##_register_detail_##detailindex##_company_name3" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][company_name4]" id="register_destination_##index0##_register_detail_##detailindex##_company_name4" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][company_name5]" id="register_destination_##index0##_register_detail_##detailindex##_company_name5" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][section_name1]" id="register_destination_##index0##_register_detail_##detailindex##_section_name1" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][section_name2]" id="register_destination_##index0##_register_detail_##detailindex##_section_name2" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][section_name3]" id="register_destination_##index0##_register_detail_##detailindex##_section_name3" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][section_name4]" id="register_destination_##index0##_register_detail_##detailindex##_section_name4" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][section_name5]" id="register_destination_##index0##_register_detail_##detailindex##_section_name5" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][title1]" id="register_destination_##index0##_register_detail_##detailindex##_title1" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][title2]" id="register_destination_##index0##_register_detail_##detailindex##_title2" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][title3]" id="register_destination_##index0##_register_detail_##detailindex##_title3" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][title4]" id="register_destination_##index0##_register_detail_##detailindex##_title4" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][title5]" id="register_destination_##index0##_register_detail_##detailindex##_title5" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][firstname1]" id="register_destination_##index0##_register_detail_##detailindex##_firstname1" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][firstname2]" id="register_destination_##index0##_register_detail_##detailindex##_firstname2" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][firstname3]" id="register_destination_##index0##_register_detail_##detailindex##_firstname3" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][firstname4]" id="register_destination_##index0##_register_detail_##detailindex##_firstname4" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][firstname5]" id="register_destination_##index0##_register_detail_##detailindex##_firstname5" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][name1]" id="register_destination_##index0##_register_detail_##detailindex##_name1" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][name2]" id="register_destination_##index0##_register_detail_##detailindex##_name2" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][name3]" id="register_destination_##index0##_register_detail_##detailindex##_name3" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][name4]" id="register_destination_##index0##_register_detail_##detailindex##_name4" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][name5]" id="register_destination_##index0##_register_detail_##detailindex##_name5" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][ruby1]" id="register_destination_##index0##_register_detail_##detailindex##_ruby1" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][ruby2]" id="register_destination_##index0##_register_detail_##detailindex##_ruby2" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][ruby3]" id="register_destination_##index0##_register_detail_##detailindex##_ruby3" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][ruby4]" id="register_destination_##index0##_register_detail_##detailindex##_ruby4" value="">
					<input type="hidden" name="register_destination[##index0##][register_detail][##detailindex##][order_dtl_noshi][ruby5]" id="register_destination_##index0##_register_detail_##detailindex##_ruby5" value="">
				</td>
				<td class="u-center">
					<button type="button" class="btn btn-default action_edit_noshi" data-rowid="##index0##-##detailindex##" data-index="##index0##" data-detail_index="##detailindex##" id="register_destination_##index0##_register_detail_##detailindex##_edit_noshi">編集</button>
				</td>
			</tr>
			<tr class="detail_area3 detail_row_##index0##_##detailindex##">
				<th>付属品</th>
				<td colspan="5">
					<div id="register_destination_##index0##_register_detail_##detailindex##_attachment_html"></div>
				</td>
				<td class="u-center">
					<button type="button" class="btn btn-default action_edit_attachment_items" data-rowid="##index0##-##detailindex##" data-index="##index0##" data-detail_index="##detailindex##" id="register_destination_##index0##_register_detail_##detailindex##_edit_attachment_items">編集</button>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<div id="destination_template_area" class="c-box--1600" style="display:none;">
	<ul class="nav nav-tabs">
		<li class="destination_tab active"><a id="dest_tab_##index0##" href="#tabs-##index0##" data-toggle="tab">送付先##index1##</a></li>
	</ul>
	<div class="tab-content tabs-inner destination_tab_body" style="padding:5px;">
		<div class="tab-pane active destination_tab_data" id="tabs-##index0##">
			<div class="c-box--full">
				<p class="c-ttl--02">送付先情報</p>
			</div>
			<input type="hidden" class="order_destination_seq" name="register_destination[##index0##][order_destination_seq]" value="##seqnumber##">
			<input type="hidden" class="destination_index" name="register_destination[##index0##][destination_index]" value="##index0##">
			<input type="hidden" class="destination_tab_display_name" name="register_destination[##index0##][destination_tab_display_name]" value="送付先##index1##">
			<input type="hidden" class="t_order_destination_id" name="register_destination[##index0##][t_order_destination_id]" value="">
			<input type="hidden" class="destination_tax_rate_8" data-tax="0.08" id="register_destination_##index0##_destination_tax_8" value="0">
			<input type="hidden" class="destination_tax_rate_10" data-tax="0.1" id="register_destination_##index0##_destination_tax_10" value="0">
			<input type="hidden" name="register_destination[##index0##][standard_fee]" id="register_destination_##index0##_standard_fee" value="0">
			<input type="hidden" name="register_destination[##index0##][frozen_fee]" id="register_destination_##index0##_frozen_fee" value="0">
			<input type="hidden" name="register_destination[##index0##][chilled_fee]" id="register_destination_##index0##_chilled_fee" value="0">
			<input type="hidden" name="register_destination[##index0##][destination_id]" id="register_destination_##index0##_destination_id" value="">

			<div class="d-table c-box--full">
				<div class="c-box--800Half">
					<table class="table table-bordered c-tbl c-tbl--790">
						<tr>
							<th class="c-box--150 must">電話番号</th>
							<td>
								<input type="text" name="register_destination[##index0##][destination_tel]"
									id="register_destination_##index0##_destination_tel" class="form-control u-input--mid" value="">
								<button class="btn btn-default action_destination_search" data-index="##index0##" type="button">送付先を検索する</button><br>
							</td>
						</tr>
						<tr>
							<th>フリガナ</th>
							<td>
								<input type="text" name="register_destination[##index0##][destination_name_kana]"
									id="register_destination_##index0##_destination_name_kana" class="form-control c-box--300" value="">
							</td>
						</tr>
						<tr>
							<th class="must">名前</th>
							<td>
								<input type="text" name="register_destination[##index0##][destination_name]"
									id="register_destination_##index0##_destination_name" class="form-control c-box--300 register_destination_name check_textbyte" data-item_name="名前" data-max_byte="32" value="">
							</td>
						</tr>
						<tr>
							<th class="must">配送方法</th>
							<td>
								<select name="register_destination[##index0##][m_delivery_type_id]"
									id="register_destination_##index0##_m_delivery_type_id" class="form-control c-box--300 action_change_delivery_type" data-index="##index0##">
									@foreach($viewExtendData['delivery_type_list'] as $deliveryType)
									<option data-delivery_type="{{$deliveryType['delivery_type']}}" value="{{$deliveryType['m_delivery_types_id']}}">{{$deliveryType['m_delivery_type_name']}}</option>
									@endforeach
								</select>
							</td>
						</tr>
						<tr>
							<th>配送希望日</th>
							<td>
								<div class='c-box--218'>
									<div class='input-group date date-picker deli_hope_date_picker'>
										<input name="register_destination[##index0##][deli_hope_date]"
											id="register_destination_##index0##_deli_hope_date" type='text'
											class="form-control c-box--180" value="" />
										<span class="input-group-addon"><span
												class="glyphicon glyphicon-calendar"></span></span>
									</div>
								</div>
							</td>
						</tr>
						<tr>
							<th>配送業者別希望時間帯</th>
							<td>
								<select name="register_destination[##index0##][m_delivery_time_hope_id]" id="register_destination_##index0##_m_delivery_time_hope_id"
									class="form-control c-box--300">
									<option value=""></option>
									@foreach($viewExtendData['delivery_hope_timezone_list'] as $deliveryTimehope)
										<option value="{{$deliveryTimehope['m_delivery_time_hope_id']}}" data-delivery_type="{{$deliveryTimehope->deliveryCompany->delivery_company_cd}}">{{$deliveryTimehope['delivery_company_time_hope_name']}}</option>
									@endforeach
								</select>
							</td>
						</tr>
						<tr>
							<th>出荷予定日</th>
							<td>
								<div class='c-box--218'>
									<div class='input-group date date-picker deli_plan_date_picker'>
										<input type='text' name="register_destination[##index0##][deli_plan_date]"
											id="register_destination_##index0##_deli_plan_date" class="form-control c-box--180"
											value="" />
										<span class="input-group-addon"><span
												class="glyphicon glyphicon-calendar"></span></span>
									</div>
								</div>
							</td>
						</tr>
						<tr>
							<th>送り状コメント</th>
							<td>
								<input type="text" name="register_destination[##index0##][invoice_comment]"
									id="register_destination_##index0##_invoice_comment" class="form-control u-input--full" value="">
							</td>
						</tr>
						<tr>
							<th>ピッキングコメント</th>
							<td>
								<textarea class="form-control u-input--full" name="register_destination[##index0##][picking_comment]" id="register_destination_##index0##_picking_comment" rows="6"></textarea>
							</td>
						</tr>
						<tr>
							<th>分割配送する</th>
							<td>
								<input type="checkbox" name="register_destination[##index0##][partial_deli_flg]"
									id="register_destination_##index0##_partial_deli_flg" value="1">
							</td>
						</tr>
						<tr>
							<th>キャンペーン対象</th>
							<td>
								<input type="checkbox" name="register_destination[##index0##][campaign_flg]"
									id="register_destination_##index0##_campaign_flg" value="1" class="action_change_campaign_flg" data-index="##index0##">
							</td>
						</tr>
						<tr>
							<th>出荷保留</th>
							<td>
								<input type="checkbox" name="register_destination[##index0##][pending_flg]"
									id="register_destination_##index0##_pending_flg" value="1">
							</td>
						</tr>
						<tr>
							<th>送り主名</th>
							<td>
								<input type="text" name="register_destination[##index0##][sender_name]"
									id="register_destination_##index0##_sender_name" class="form-control u-input--full" value="">
							</td>
						</tr>
						<tr>
							<th>配送種別</th>
							<td>
								<input type="checkbox" name="register_destination[##index0##][total_deli_flg]"
									id="register_destination_##index0##_total_deli_flg" value="1" class="action_change_total_deli_flg" data-index="##index0##">
								<label for="register_destination_##index0##_total_deli_flg">同梱配送</label>
								<select name="register_destination[##index0##][total_temperature_zone_type]"
									id="register_destination_##index0##_total_temperature_zone_type" class="form-control u-input--mid action_change_total_temperature_zone_type" data-index="##index0##">
									<option value="0" data-delicomp="0" >常温</option>
									<option value="1" data-delicomp="1" >冷凍</option>
									<option value="2" data-delicomp="2" >冷蔵</option>
								</select>
							</td>
						</tr>
					</table>
				</div>
				<div class="c-box--800Half">
					<table class="table table-bordered c-tbl c-tbl--790">
						<tr>
							<th class="must">住所</th>
							<td>
								<div class="d-table c-tbl--400">
									<div class="d-table-cell c-box--100">郵便番号</div>
									<div class="d-table-cell"><input type="text"
											name="register_destination[##index0##][destination_postal]"
											id="register_destination_##index0##_destination_postal"  data-index="##index0##" class="form-control refresh_shipping_fee destination_postal" maxlength="8"
											value=""
											onKeyUp="AjaxZip3.zip2addr(this,'','register_destination[##index0##][destination_address1]','register_destination[##index0##][destination_address2]','dummy','register_destination[##index0##][destination_address3]');">
									</div>
								</div>
								<div class="d-table c-tbl--400 u-mt--xs">
									<div class="d-table-cell c-box--100">フリガナ</div>
									<div class="d-table-cell">
										<input type="text" name="register_destination[##index0##][destination_address1_kana]"
											id="register_destination_##index0##_destination_address1_kana" class="form-control c-box--full"
											value="" disabled></div>
								</div>
								<div class="d-table c-tbl--400 u-mt--xs">
									<div class="d-table-cell c-box--100">都道府県</div>
									<div class="d-table-cell">
										<select name="register_destination[##index0##][destination_address1]"
											id="register_destination_##index0##_destination_address1" class="form-control c-box--200 action_change_destination_address1" data-index="##index0##">
											<option></option>
											@foreach($viewExtendData['m_prefectures'] as $keyId => $keyValue)
												<option value="{{$keyValue}}">{{$keyValue}}</option>
											@endforeach
										</select>
									</div>
								</div>
								<div class="d-table c-tbl--400 u-mt--xs">
									<div class="d-table-cell c-box--100">フリガナ</div>
									<div class="d-table-cell">
										<input type="text" name="register_destination[##index0##][destination_address2_kana]"
											id="register_destination_##index0##_destination_address2_kana" class="form-control c-box--full"
											value="" disabled></div>
								</div>
								<div class="d-table c-tbl--400 u-mt--xs">
									<div class="d-table-cell c-box--100">市区町村</div>
									<div class="d-table-cell"><input type="text"
											name="register_destination[##index0##][destination_address2]"
											id="register_destination_##index0##_destination_address2" class="form-control c-box--full check_textbyte" data-item_name="市区町村" data-max_byte="24"
											value=""></div>
								</div>
								<div class="d-table c-tbl--400 u-mt--xs">
									<div class="d-table-cell c-box--100">番地</div>
									<div class="d-table-cell"><input type="text"
											name="register_destination[##index0##][destination_address3]"
											id="register_destination_##index0##_destination_address3" class="form-control c-box--full check_textbyte" data-item_name="番地" data-max_byte="32"
											value=""></div>
								</div>
								<div class="d-table c-tbl--400 u-mt--xs">
									<div class="d-table-cell c-box--100">建物名</div>
									<div class="d-table-cell"><input type="text"
											name="register_destination[##index0##][destination_address4]"
											id="register_destination_##index0##_destination_address4" class="form-control c-box--full check_textbyte" data-item_name="建物名" data-max_byte="32"
											value=""></div>
								</div>
							</td>
						</tr>
						<tr>
							<th>法人名・団体名</th>
							<td>
								<input type="text" name="register_destination[##index0##][destination_company_name]"
									id="register_destination_##index0##_destination_company_name" class="form-control c-box--full destination_company_name"
									value="">
							</td>
						</tr>
						<tr>
							<th>部署名</th>
							<td>
								<input type="text" name="register_destination[##index0##][destination_division_name]"
									id="register_destination_##index0##_destination_division_name" class="form-control c-box--full"
									value="">
							</td>
						</tr>
						<tr>
							<th>ギフトメッセージ</th>
							<td>
								<input type="text" name="register_destination[##index0##][gift_message]"
									id="register_destination_##index0##_gift_message" class="form-control u-input--full" value="" disabled>
							</td>
						</tr>
						<tr>
							<th>ギフト包装種類</th>
							<td>
								<input type="text" name="register_destination[##index0##][gift_wrapping]"
									id="register_destination_##index0##_gift_wrapping" class="form-control u-input--full" value="" disabled>
							</td>
						</tr>
						<tr>
							<th>のしタイプ</th>
							<td>
								<input type="text" name="register_destination[##index0##][nosi_type]"
									id="register_destination_##index0##_nosi_type" class="form-control u-input--full" value="" disabled>
							</td>
						</tr>
						<tr>
							<th>のし名前</th>
							<td>
								<input type="text" name="register_destination[##index0##][nosi_name]"
									id="register_destination_##index0##_nosi_name" class="form-control u-input--full" value="" disabled>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="u-mt--ss">
				<button type="button" class="btn btn-danger btn-lg action_remove_destination" data-index="##index0##">送付先を削除</button>
			</div>
			<div id="line-05"></div>
			<div class="c-box--full u-mt--ss">
				<p class="c-ttl--02">受注明細情報</p>
			</div>
			<div class="d-table c-box--full">
				<table class="table table-bordered c-tbl c-tbl--1580 u-mt--ss detail_sell_table">
					<tr class="nowrap">
						<th class="c-box--60">コピー</th>
						<th class="c-box--60"></th>
						<th class="c-box--220 must">販売コード</th>
						<th class="c-box--450 must">販売名</th>
						<th class="c-box--110 must">販売単価</th>
						<th class="c-box--100 must">数量</th>
						<th class="c-box--100">販売金額</th>
						<th class="c-box--130">在庫状態
							<button type="button" class="btn btn-default btn-xs" name="submit_stockinfo_update[##index0##]">
								<span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
							</button>
						</th>
						<th class="c-box--130">クーポンID</th>
						<th class="c-box--110">クーポン金額</th>
						<th class="">種別</th>
					</tr>
					<tr class="sell_detail_first">
						<td class="u-vam u-center">
						</td>
						<td class="u-vam">
						</td>
						<td class="u-vam">
							<input type="text" class="form-control u-input--small search_sell_cd" value="">
							<input type="button" data-rowid="##index0##-0" class="btn btn-success action_sku_search"
								value="検索">
						</td>
						<td class="u-vam"></td>
						<td class="u-vam u-right">
						<td class="u-vam u-right">
							<input type="text" 	class="form-control u-input--small u-right c-box--60 search_sell_vol" value="1">
						</td>
						<td class="u-vam u-right">
						</td>
						<td class="u-vam u-center">
						</td>
						<td class="u-vam">
						</td>
						<td class="u-vam u-right">
						</td>
						<td class="u-vam u-center font-FF0000">
						</td>
					</tr>
					<tr>
						<td colspan="10" class="u-vam u-right">小計</td>
						<td class="u-vam u-right">
							<span id="sum_destination_sell_total_##index0##" class="register_destination_amount">0</span>
						</td>
					</tr>
					<tr>
						<td colspan="10" class="u-vam u-right">送料</td>
						<td class="u-vam u-right">
							<input type="text" name="register_destination[##index0##][shipping_fee]" id="register_destination_##index0##_shipping_fee" class="form-control u-input--small u-right c-box--full register_destination_shipping_fee" value="0">
						</td>
					</tr>
					<tr>
						<td colspan="10" class="u-vam u-right">手数料</td>
						<td class="u-vam u-right">
							<input type="text" name="register_destination[##index0##][payment_fee]" id="register_destination_##index0##_payment_fee" class="form-control u-input--small u-right c-box--full register_destination_payment_fee" value="0">
						</td>
					</tr>
					<tr>
						<td colspan="10" class="u-vam u-right">包装料</td>
						<td class="u-vam u-right">
							<input type="text" name="register_destination[##index0##][wrapping_fee]" id="register_destination_##index0##_wrapping_fee" class="form-control u-input--small u-right c-box--full register_destination_wrapping_fee" value="0">
						</td>
					</tr>					
				</table>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	(function($) {
		$.extend({
			h: function htmlspecialchars(ch){
				if(typeof ch == "string"){
					ch = ch.replace(/&/g,"&amp;") ;
					ch = ch.replace(/"/g,"&quot;") ;
					ch = ch.replace(/'/g,"&#039;") ;
					ch = ch.replace(/</g,"&lt;") ;
					ch = ch.replace(/>/g,"&gt;") ;
					return ch ;
				} else {
					return ch;
				}
			},
			zipkana: function setAddressKana(zipcode,address1,address2){
				if(zipcode !== ''){
					$.ajax({
						url: '/gfh/order/api/zipcode/info/'+zipcode,
						method: 'GET',
						headers: {
							'Authorization': $('input[name="_token"]').val()
						},
						dataType: 'json',
						async: true,
						success: function(response) {
							$(address1).val(response.postal_prefecture_kana);
							$(address2).val(response.postal_city_kana+response.postal_town_kana);
						},
						error: function(xhr, status, error) {
							// ないので未設定にする
							$(address1).val("");
							$(address2).val("");
						}
					});
				} else {
					$(address1).val("");
					$(address2).val("");
				}
			},
			delivery_day: function getDeliveryday(zipcode,destIndex){
				if(zipcode !== '' && $('#register_destination_' + destIndex + '_deli_hope_date').val() != ''){
					$.ajax({
						url: '/gfh/order/api/delivery-days/'+zipcode,
						method: 'GET',
						headers: {
							'Authorization': $('input[name="_token"]').val()
						},
						dataType: 'json',
						async: true,
						success: function(response) {
							let d = new Date($('#register_destination_' + destIndex + '_deli_hope_date').val());
							d.setDate(d.getDate() - response.delivery_days);
							hope_date = d.toLocaleDateString("ja-JP", {year: "numeric",month: "2-digit", day: "2-digit"});
							$('#register_destination_' + destIndex + '_deli_plan_date').val(hope_date);
						},
						error: function(xhr, status, error) {
						}
					});
				} else if(zipcode !== ''){
					let delivery_readtime = $('#delivery_readtime').val();
					if(Number.isInteger(parseInt(delivery_readtime)) == false){
						delivery_readtime = 0;
					} else {
						delivery_readtime = parseInt(delivery_readtime);
					}
					let d = new Date()
					d.setDate(d.getDate() + delivery_readtime);
					hope_date = d.toLocaleDateString("ja-JP", {year: "numeric",month: "2-digit", day: "2-digit"});
					$('#register_destination_' + destIndex + '_deli_plan_date').val(hope_date);
				}
			},
		});
	})(jQuery);
</script>
<script type="text/javascript">
	function searchCustomer(){
		let formData = $('#search_customer_modal');
		$.ajax({
			url: '/gfh/order/api/customer/list',
			method: 'POST',
			data:  formData.serialize(),
			success: function(response) {
				$('#dialogWindow .dialog_body').html(response.html);
			},
			error: function(xhr, status, error) {
				alert("顧客検索モーダルAPIの呼び出しに失敗しまし。");
			}
		});
	}
	$(document).on('click', '#search_customer_modal .action_search_modal_button', function () {
		$('#search_customer_modal [name="hidden_next_page_no"]').val(1);
		$('#search_customer_modal [name="sorting_column"]').val("");
		$('#search_customer_modal [name="sorting_shift"]').val("");
		searchCustomer();
		return false;
	});
	$(document).on('click', '#search_customer_modal .next_page_link', function () {
		let hidden_next_page_no = $(this).attr('page_no');
		$('#search_customer_modal [name="hidden_next_page_no"]').val(hidden_next_page_no);
		searchCustomer();
		return false;
	});
	$(document).on('change', '#search_customer_modal [name="page_list_count"]', function () {
		$('#search_customer_modal [name="hidden_next_page_no"]').val(1);
		searchCustomer();
		return false;
	});	
	$(document).on('click', '#search_customer_modal .next_sort_link', function () {
		$('#search_customer_modal [name="sorting_column"]').val($(this).attr('sort_column'));
		$('#search_customer_modal [name="sorting_shift"]').val($(this).attr('sort_shift'));
		$('#search_customer_modal [name="hidden_next_page_no"]').val(1);
		searchCustomer();
		return false;
	});
</script>
<script type="text/javascript">
	function searchDestination(){
		let formData = $('#search_destination_modal');
		$.ajax({
			url: '/gfh/order/api/customer/destination/list',
			method: 'POST',
			data:  formData.serialize(),
			success: function(response) {
				$('#dialogWindow .dialog_body').html(response.html);
				$('#dialogWindow').dialog('open');
			},
			error: function(xhr, status, error) {
				alert("送付先検索モーダルAPIの呼び出しに失敗しました。");
			}
		});
	}
	$(document).on('click', '#search_destination_modal .action_search_modal_button', function () {
		$('#search_destination_modal [name="hidden_next_page_no"]').val(1);
		$('#search_destination_modal [name="sorting_column"]').val("");
		$('#search_destination_modal [name="sorting_shift"]').val("");
		searchDestination();
		return false;
	});
	$(document).on('click', '#search_destination_modal .next_page_link', function () {
		let hidden_next_page_no = $(this).attr('page_no');
		$('#search_destination_modal [name="hidden_next_page_no"]').val(hidden_next_page_no);
		searchDestination();
		return false;
	});
	$(document).on('change', '#search_destination_modal [name="page_list_count"]', function () {
		$('#search_destination_modal [name="sorting_column"]').val($(this).attr('sort_column'));
		$('#search_destination_modal [name="sorting_shift"]').val($(this).attr('sort_shift'));
		$('#search_destination_modal [name="hidden_next_page_no"]').val(1);
		searchDestination();
		return false;
	});	
	$(document).on('click', '#search_destination_modal .next_sort_link', function () {
		$('#search_destination_modal [name="sorting_column"]').val($(this).attr('sort_column'));
		$('#search_destination_modal [name="sorting_shift"]').val($(this).attr('sort_shift'));
		$('#search_destination_modal [name="hidden_next_page_no"]').val(1);
		searchDestination();
		return false;
	});
</script>
<script type="text/javascript">
	function searchAmi(){
		let formData = $('#search_ami_modal');
		formData['m_ecs_id'] = $("#m_ecs_id").val();
		$.ajax({
			url: '/gfh/order/api/ami_page/list',
			method: 'POST',
			data:  formData.serialize(),
			success: function(response) {
				$('#dialogWindow .dialog_body').html(response.html);
				$('#dialogWindow .dialog_body .ecs_name').text($("#m_ecs_id option:selected").text());
			},
			error: function(xhr, status, error) {
				alert("商品検索モーダルAPIの呼び出しに失敗しました。");
			}
		});
	}
	$(document).on('click', '#search_ami_modal .action_search_modal_button', function () {
		$('#search_ami_modal [name="hidden_next_page_no"]').val(1);
		$('#search_ami_modal [name="sorting_column"]').val("");
		$('#search_ami_modal [name="sorting_shift"]').val("");
		searchAmi();
		return false;
	});
	$(document).on('click', '#search_ami_modal .next_page_link', function () {
		let hidden_next_page_no = $(this).attr('page_no');
		$('#search_ami_modal [name="hidden_next_page_no"]').val(hidden_next_page_no);
		searchAmi();
		return false;
	});
	$(document).on('change', '#search_ami_modal [name="page_list_count"]', function () {
		$('#search_ami_modal [name="sorting_column"]').val($(this).attr('sort_column'));
		$('#search_ami_modal [name="sorting_shift"]').val($(this).attr('sort_shift'));
		$('#search_ami_modal [name="hidden_next_page_no"]').val(1);
		searchAmi();
		return false;
	});	
</script>
<script type="text/javascript">
	function searchAttachmentItem(){
		let formData = $('#search_attachment_item_modal');
		formData['m_ecs_id'] = $("#m_ecs_id").val();

		$.ajax({
			url: '/gfh/order/api/attachment_item/list',
			method: 'POST',
			data:  formData.serialize(),
			success: function(response) {
				$('#dialogWindow .dialog_body').html(response.html);
			},
			error: function(xhr, status, error) {
				alert("付属品検索モーダルAPIの呼び出しに失敗しました。");
			}
		});
	}
	$(document).on('click', '#search_attachment_item_modal .action_search_modal_button', function () {
		$('#search_attachment_item_modal [name="hidden_next_page_no"]').val(1);
		$('#search_attachment_item_modal [name="sorting_column"]').val("");
		$('#search_attachment_item_modal [name="sorting_shift"]').val("");
		searchAttachmentItem();
		return false;
	});
	$(document).on('click', '#search_attachment_item_modal .next_page_link', function () {
		let hidden_next_page_no = $(this).attr('page_no');
		$('#search_attachment_item_modal [name="hidden_next_page_no"]').val(hidden_next_page_no);
		searchAttachmentItem();
		return false;
	});
	$(document).on('change', '#search_attachment_item_modal [name="page_list_count"]', function () {
		$('#search_attachment_item_modal [name="sorting_column"]').val($(this).attr('sort_column'));
		$('#search_attachment_item_modal [name="sorting_shift"]').val($(this).attr('sort_shift'));
		$('#search_attachment_item_modal [name="hidden_next_page_no"]').val(1);
		searchAttachmentItem();
		return false;
	});	
</script>
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
	function getPrefectureIdByName(name){
		let work = JSON.parse($('#m_prefectures').val());
		for(key in work){
			if(work[key] == name){
				return key;
			}
		}
		return "";
	}
	// 受注情報/注文主情報/請求先情報スクリプト
	function initBillButton(){
		if($("#m_cust_id").val() == $("#m_cust_id_billing").val()){
			$(".action_billing_copy_customer").attr('disabled',true);
		} else {
			$(".action_billing_copy_customer").attr('disabled',false);
		}
	}
	function billingSetReadonly(readonly){
		$('#billing_tel1').attr('readonly',readonly);
		$('#billing_tel2').attr('readonly',readonly);
		$('#billing_fax').attr('readonly',readonly);
		$('#billing_name_kana').attr('readonly',readonly);
		$('#billing_name').attr('readonly',readonly);
		$('#billing_email1').attr('readonly',readonly);
		$('#billing_email2').attr('readonly',readonly);
		$('#billing_postal').attr('readonly',readonly);
		$('#billing_address1_kana').attr('readonly',true);
		$('#billing_address1').attr('readonly',readonly);
		$('#billing_address2_kana').attr('readonly',true);
		$('#billing_address2').attr('readonly',readonly);
		$('#billing_address3').attr('readonly',readonly);
		$('#billing_address4').attr('readonly',readonly);
		$('#billing_corporate_name').attr('readonly',readonly);
		$('#billing_division_name').attr('readonly',readonly);
		$('#billing_cust_runk_id').attr('readonly',readonly);

		$('#billing_alert_cust_type').attr('readonly',readonly);
		$('#billing_corporate_tel').attr('readonly',readonly);
		$('#billing_cust_note').attr('readonly',readonly);
		
		$('#billing_address1 option').prop('disabled',readonly);
		$('#billing_cust_runk_id option').prop('disabled',readonly);
		$('input[name="billing_alert_cust_type"]').prop('disabled',readonly);
	}
	function getTagList(){
		if($('#t_order_hdr_id').val() != ""){
			let html = '';
			$.ajax({
				url: '/gfh/order/api/order-tags/order/'+$('#t_order_hdr_id').val(),
				method: 'GET',
				headers: {
					'Authorization': $('input[name="_token"]').val()
				},
				dataType: 'json',
				success: function(json) {
					for (var key in json) {
						html += '<label>';
						html += '<p data-toggle="tooltip" data-placement="top">';
						html += '<input type="checkbox" class="chk_order_tag" data-m_order_tag_id="' + json[key].m_order_tag_id +'" value="' + json[key].m_order_tag_id + '">&nbsp;';
						html += '<a class="btn ns-orderTag-style" style="background:#' + json[key].tag_color + ';color:#' + json[key].font_color + ';" type="button">';
						if(json[key].deli_stop_flg < 0){
							html += '' + json[key].tag_display_name + '';
						} else {
							html += '<u>' + json[key].tag_display_name + '</u>';
						}
						html += '</a>';
						html += '</p>';
						html += '</label>';
					}
					$('.tag-box').html(html);
				},
				error: function(xhr, status, error) {
					alert("タグ一覧取得APIの呼び出しに失敗しました。");
					$('.tag-box').html(html);
				}
			});
		}
	}
	$(document).ready(function() {
		initBillButton();
		getTagList();
		$.zipkana($('#order_postal').val(),'#order_address1_kana','#order_address2_kana');
		$.zipkana($('#billing_postal').val(),'#billing_address1_kana','#billing_address2_kana');
		$('#submit_register').click(function () {
			if(checkTextByteError() == false){
				alert("入力サイズエラーがあります。");
				return false;
			}
			// disableを解除する
			$("input").prop('disabled', false);
			$("option").prop('disabled', false);
			$("textarea").prop('disabled', false);
			$("select").prop('disabled', false);
			return true;
		});
		// タグ追加
		$(document).on('click', '.action_append_tag', function () {
			let data = JSON.stringify({
				order_hdr_id: $('#t_order_hdr_id').val(),
				order_tag_id: $('#m_order_tag_id').val(),
				_token: $('input[name="_token"]').val()
			})	;
			$.ajax({
				method: 'POST',
				url: '/gfh/order/api/order-tags/add',
				contentType: 'application/json',
				dataType: 'json',
				data: data,
				success: function(json) {
					// タグ部再読み込み
					getTagList();
				},
				error: function(xhr, status, error) {
					alert("受注タグ追加APIの呼び出しに失敗しました。");
				}
			});
		});
		// タグ削除
		$(document).on('click', '.action_remove_tag', function () {
			let ids = [];
			$(".chk_order_tag:checked").each(function(){				
				ids.push($(this).val());
			});
			if(ids.length > 0){
				let data = JSON.stringify({
					order_hdr_id: $('#t_order_hdr_id').val(),
					order_tag_id: ids,
					_token: $('input[name="_token"]').val()
				});
				$.ajax({
					method: 'POST',
					url: '/gfh/order/api/order-tags/remove',
					contentType: 'application/json',
					dataType: 'json',
					data: data,
					success: function(json) {
						// タグ部再読み込み
						getTagList();
					},
					error: function(xhr, status, error) {
						alert("受注タグ削除APIの呼び出しに失敗しました。");
					}
				});
			}
		});
		$(document).on('click', '.billing_customer_selected_action', function () {
			// 顧客検索ダイアログ選択ボタン押下時イベント
			$.ajax({
				url: '/gfh/order/api/customer/'+$(this).attr('data-customer-id'),
				method: 'GET',
				headers: {
					'Authorization': $('input[name="_token"]').val()
				},
				dataType: 'json',
				success: function(json) {
					$('#m_cust_id_billing').val(json.m_cust_id);
					$('#billing_tel1').val(json.tel1);
					$('#billing_tel2').val(json.tel2);
					$('#billing_fax').val(json.fax);
					$('#billing_name_kana').val(json.name_kana);
					$('#billing_name').val(json.name_kanji);
					$('#billing_email1').val(json.email1);
					$('#billing_email2').val(json.email2);

					$('#billing_postal').val(json.postal);
					$('#billing_address1_kana').val("");
					$('#billing_address1').val(json.address1);
					$('#billing_address2_kana').val("");
					$('#billing_address2').val(json.address2);
					$('#billing_address3').val(json.address3);
					$('#billing_address4').val(json.address4);
					$('#billing_corporate_name').val(json.corporate_kanji);
					$('#billing_division_name').val(json.division_name);

					$('#billing_cust_runk_id').val(json.m_cust_runk_id);
					$('input[name="billing_alert_cust_type"]:eq('+json.alert_cust_type+')').prop('checked',true);
					$('#billing_corporate_tel').val(json.corporate_tel);
					$('#billing_cust_note').val(json.note);
					$('#dialogWindow').dialog('close');
					billingSetReadonly(true);
					$(".action_billing_new").attr('disabled',false);
					$(".action_billing_clear_new").attr('disabled',false);
					initBillButton();
					$.zipkana($('#billing_postal').val(),'#billing_address1_kana','#billing_address2_kana');
				},
				error: function(xhr, status, error) {
					alert("顧客情報取得APIの呼び出しに失敗しました。");
				}
			});
		});
		// 顧客を検索する押下処理
		$(".action_billing_search").click(function(){
			$.ajax({
				url: '/gfh/order/api/customer/list',
				method: 'POST',
				data: {
					'page_list_count':{{\Config::get('Common.const.page_limit')}},
					'hidden_next_page_no':1,
					'_token': $('input[name="_token"]').val()
				},
				success: function(response) {
					$('#dialogWindow .dialog_body').html(response.html);
					$('#dialogWindow').dialog('open');
				},
				error: function(xhr, status, error) {
					alert("顧客検索モーダルAPIの呼び出しに失敗しました。");
				}
			});
		});
		// 表示内容を編集し新規登録押下処理
		$(".action_billing_new").click(function(){
			$('#m_cust_id_billing').val('');
			billingSetReadonly(false);
			$(".action_billing_new").attr('disabled',true);
			$(".action_billing_clear_new").attr('disabled',false);
			initBillButton();
		});
		// 情報をクリアし新規登録押下処理
		$(".action_billing_clear_new").click(function(){
			$('#m_cust_id_billing').val('');
			$('#billing_tel1').val('');
			$('#billing_tel2').val('');
			$('#billing_fax').val('');
			$('#billing_name_kana').val('');
			$('#billing_name').val('');
			$('#billing_email1').val('');
			$('#billing_email2').val('');

			$('#billing_postal').val('');
			$('#billing_address1_kana').val('');
			$('#billing_address1').val('');
			$('#billing_address2_kana').val('');
			$('#billing_address2').val('');
			$('#billing_address3').val('');
			$('#billing_address4').val('');
			$('#billing_corporate_name').val('');
			$('#billing_division_name').val('');
			$('#billing_cust_runk_id').val('');
			$('#billing_alert_cust_type').val('');
			$('input[name="billing_alert_cust_type"]:checked').prop('checked',false);
			billingSetReadonly(false);
			$('#billing_corporate_tel').val('');
			$('#billing_cust_note').val('');
			$(".action_billing_new").attr('disabled',true);
			$(".action_billing_clear_new").attr('disabled',true);
			initBillButton();

			$('#billing_area input').removeClass('sizeover-error');
		});
		// 注文主情報を自動入力押下処理
		$(".action_billing_copy_customer").click(function(){
			$('#m_cust_id_billing').val($('#m_cust_id').val());
			$('#billing_tel1').val($('#order_tel1').val());
			$('#billing_tel2').val($('#order_tel2').val());
			$('#billing_fax').val($('#order_fax').val());
			$('#billing_name_kana').val($('#order_name_kana').val());
			$('#billing_name').val($('#order_name').val());
			$('#billing_email1').val($('#order_email1').val());
			$('#billing_email2').val($('#order_email2').val());
			$('#billing_postal').val($('#order_postal').val());
			$('#billing_address1_kana').val($('#order_address1_kana').val());
			$('#billing_address1').val($('#order_address1 option:selected').val());
			$('#billing_address2_kana').val($('#order_address2_kana').val());
			$('#billing_address2').val($('#order_address2').val());
			$('#billing_address3').val($('#order_address3').val());
			$('#billing_address4').val($('#order_address4').val());
			$('#billing_corporate_name').val($('#order_corporate_name').val());
			$('#billing_division_name').val($('#order_division_name').val());
			$('#billing_cust_runk_id').val($('#order_cust_runk_id option:selected').val());
			$('input[name="billing_alert_cust_type"]:checked').prop('checked',false);
			$('input[name="billing_alert_cust_type"]:eq('+$('#alert_cust_type').val()+')').prop('checked',true);
			billingSetReadonly(true);
			$('#billing_corporate_tel').val($('#corporate_tel').val());
			$('#billing_cust_note').val($('#cust_note').val());
			$(".action_billing_new").attr('disabled',false);
			$(".action_billing_clear_new").attr('disabled',false);
			initBillButton();
			$('#billing_area input').removeClass('sizeover-error');
		});
	});
</script>
<script type="text/javascript">
	// 送付先スクリプト
	let deli_type_list = {};
	const tab_header_template = $('#destination_template_area ul').html();
	const tab_body_template = $('#destination_template_area .destination_tab_body').html();
	const sell_detail_template = $('#sell_detail_template_area tbody').html();
	const attachment_item_template = $('#attachment_item_template_area').html();
	const attachment_dialog_item_template = $('#attachment_dialog_item_template_area tbody').html();
	let tabnumber =  $('#destination_area .destination_tab').length;

	async function initTransferFee(){
		$('#transfer_fee').val("0");
		return $.ajax({
			url: '/gfh/order/api/payment_type/'+$('#m_pay_type_id').val(),
			method: 'GET',
			headers: {
					'Authorization': $('input[name="_token"]').val()
				},
				dataType: 'json',
				success: function(json) {
					$('#transfer_fee').val(Math.floor(json['payment_fee']));
				},
				error: function(xhr, status, error) {
					alert("配送方法別手数料APIの呼び出しに失敗しました。");
				}
			});
	}
	async function initDeliType(){
		return $.ajax({
			url: '/gfh/order/api/deli_type/list',
			method: 'GET',
			headers: {
					'Authorization': $('input[name="_token"]').val()
				},
				dataType: 'json',
				success: function(json) {
					for (var key in json) {
						let delivery_fees = {}
						for (var key2 in json[key].delivery_fees) {
						}
						deli_type_list[json[key].m_delivery_types_id] = json[key];
					}
				},
				error: function(xhr, status, error) {
					alert("配送方法リスト取得APIの呼び出しに失敗しました。");
				}
			});

	}
	async function initDestinationData(){
		await initTransferFee();
		//支払い方法の取得
		await initDeliType();

		// 初期化
		if(tabnumber == 0){
			await appendDestination(tabnumber);
		} else {
			$("#destination_area .nav-tabs .destination_tab:last").addClass("active");
			$("#destination_area .destination_tab_body div.tab-pane:last").addClass("active");
		}
		initCalc();
		initZipKana();
		initDeliveryTimeHope();
		initNoshiView();
		if(checkTextByteError() == false){
			alert("入力サイズエラーがあります。");
		}
	}
	function getNextSeqMax(){
		let rv = 0;
		$('#destination_area .order_destination_seq').each(function(){
			if(rv <= $(this).val()){
				rv = $(this).val();
			}
		});
		rv++;
		return rv;
	}
	function getNextDetailSeqMax(index){
		let rv = 0;
		$('#tabs-'+index+ ' .order_dtl_seq').each(function(){
			if(rv <= $(this).val()){
				rv = $(this).val();
			}
		});
		rv++;
		return rv;
	}
	// 送付先追加
	async function appendDestination(idx){
		let index0 = idx;
		let index1 = idx+1;
		// アクティブを解除
		$("#destination_area .nav-tabs .destination_tab").removeClass("active");
		$("#destination_area .destination_tab_body div.tab-pane").removeClass("active");

		// タブヘッダの設定
		let header = tab_header_template;
		header = header.replaceAll("##index0##",index0);
		header = header.replaceAll("##index1##",index1);
		// destination_tab_positon の前に追加
		$("#destination_area .destination_tab_positon").before(header);
		let seq = getNextSeqMax();
		let body = tab_body_template;
		body = body.replaceAll("##index0##",index0);
		body = body.replaceAll("##index1##",index1);
		body = body.replaceAll("##seqnumber##",seq);
		$("#destination_area .destination_tab_body").append(body);

		// 出荷予定日の設定
		let deli_plan_date = new Date();
		let delivery_readtime = $('#delivery_readtime').val();
		if(Number.isInteger(parseInt(delivery_readtime)) == false){
			delivery_readtime = 0;
		} else {
			delivery_readtime = parseInt(delivery_readtime);
		}
		
		$('#register_destination_' + index0 + '_deli_plan_date').val(deli_plan_date.toLocaleDateString("ja-JP", {year: "numeric",month: "2-digit", day: "2-digit"}));

		$('.date-picker').datetimepicker({
			format: 'YYYY/MM/DD'
		});
		// 出荷予定日更新イベント
		$('.deli_hope_date_picker').on("dp.change", (e) => { 
			let destIndex = $(e.target).parents(".destination_tab_data").find(".destination_index").val();
			$.delivery_day($('#register_destination_' + destIndex + '_destination_postal').val(),destIndex);
		});
		// 配送種別の手数料を取得
		await setTemperatureZoneType(index0);
		tabnumber++;
	}
	// アクティブタブID取得
	function getActiveTabIndex(){
		let target = null;
		$("#destination_area .destination_tab_body div.tab-pane").each(function(){
			if($(this).hasClass("active")){
				target = $(this);
			}
		});
		return target.find(".destination_index").val();

	}
	// 商品に紐づく付属品の設定
	function setAttachmentItem(destIndex,detailIndex,page_attachment_item){
		let attachmentIndex = 0;
		let html_attachment = '';
		let t_order_dtl_id = $('#register_destination_' + destIndex + '_register_detail_' + detailIndex + '_t_order_dtl_id').val();
		let group_id = $('#register_destination_' + destIndex + '_register_detail_' + detailIndex + '_attachment_item_group_id').val();
		// 商品数量
		let order_sell_vol = $('#register_destination_' + destIndex + '_register_detail_' + detailIndex + '_order_sell_vol').val();
		$.each(page_attachment_item, function(index, value) {
			if (value.group_id == group_id) {
				let html = attachment_item_template;
				html = html.replaceAll("##index0##",destIndex);
				html = html.replaceAll("##detailindex##",detailIndex);
				html = html.replaceAll("##attachmentIndex##",attachmentIndex);
				html = html.replaceAll("##t_order_dtl_attachment_item_id##","");
				html = html.replaceAll("##t_order_dtl_id##",t_order_dtl_id);
				html = html.replaceAll("##display_flg##",$.h(value.attachment_item.display_flg));
				html = html.replaceAll("##m_ami_attachment_item_id##",$.h(value.attachment_item.m_ami_attachment_item_id));
				html = html.replaceAll("##attachment_item_cd##",$.h(value.attachment_item.attachment_item_cd));
				html = html.replaceAll("##attachment_item_name##",$.h(value.attachment_item.attachment_item_name));
				// 商品数量＊付属品数量
				html = html.replaceAll("##attachment_vol##",$.h(value.item_vol * order_sell_vol));
				attachmentIndex++;
				html_attachment += html;
			}
		});
		$('.attachment_item_'+destIndex+'_'+detailIndex).remove();
		$('#register_destination_' + destIndex + '_register_detail_' + detailIndex + '_attachment_html').after(html_attachment);
		displayAttachmentText(destIndex,detailIndex);
	}
	// 商品明細追加
	function appendSellDetail(data,vol){
		let destIndex = getActiveTabIndex();
		let detal_html = sell_detail_template;
		let detailIndex = 0;
		if($('#tabs-' + destIndex + " .detail_area").length){
			$('#tabs-' + destIndex + " .detail_area").each(function(){
				if(detailIndex <= $(this).data('detail_index')){
					detailIndex =  $(this).data('detail_index');
				}
			});
			detailIndex++;
		}
		let seqnumber =  getNextDetailSeqMax(destIndex);
		detal_html = detal_html.replaceAll("##detailseqnumber##",seqnumber);
		detal_html = detal_html.replaceAll("##sell_id##",$.h(data.m_ami_ec_page_id));
		detal_html = detal_html.replaceAll("##sell_cd##",$.h(data.ec_page_cd));
		detal_html = detal_html.replaceAll("##sell_name##",$.h(data.ec_page_title));
		detal_html = detal_html.replaceAll("##sales_price##",$.h(data.sales_price));
		detal_html = detal_html.replaceAll("##sales_price_format##",Number(data.sales_price).toLocaleString());
		detal_html = detal_html.replaceAll("##tax_rate##",data.tax_rate);
		detal_html = detal_html.replaceAll("##order_sell_vol##",vol); // 数量
		detal_html = detal_html.replaceAll("##order_dtl_coupon_id##",""); // クーポンID
		detal_html = detal_html.replaceAll("##order_dtl_coupon_price##",""); // クーポン金額
		detal_html = detal_html.replaceAll("##order_dtl_coupon_price_format##",""); // クーポン金額
		let three_temperature_zone_type = 0; // 常温
		for (let i = 0; i < data.page.page_sku.length; i++) {
			// 全て同じ温度帯とのことなので０番目のもののみ取得する
			three_temperature_zone_type = data.page.page_sku[i].three_temperature_zone_type
			break;
		}
		detal_html = detal_html.replaceAll("##three_temperature_zone_type##",three_temperature_zone_type); // 温度帯設定
		if(data.page.image_path){
			detal_html = detal_html.replaceAll("##image_path##",data.page.image_path); // イメージ
			detal_html = detal_html.replaceAll("##m_ami_page_id##",data.page.m_ami_page_id); // m_ami_page_id
		} else {
			detal_html = detal_html.replaceAll("##image_path##",""); // イメージ
			detal_html = detal_html.replaceAll("##m_ami_page_id##",""); // m_ami_page_id
		}
		if(data.page.page_desc){
			detal_html = detal_html.replaceAll("##page_desc_hidden##",$.h(data.page.page_desc)); // 説明html
			detal_html = detal_html.replaceAll("##page_desc##",data.page.page_desc); // 説明html
		} else {
			detal_html = detal_html.replaceAll("##page_desc_hidden##",""); // 説明html
			detal_html = detal_html.replaceAll("##page_desc##",""); // 説明html
		}
		detal_html = detal_html.replaceAll("##index0##",destIndex);
		detal_html = detal_html.replaceAll("##detailindex##",detailIndex);
		$('#tabs-' + destIndex + " .sell_detail_first").before(detal_html);

		if(data.page.image_path){
		} else {
			// イメージを消す
			$(".detail_sell_table .detail_row_"+destIndex+"_"+detailIndex+" .detail_item_thumb img").hide();
		}

		// 付属品の設定
		setAttachmentItem(destIndex,detailIndex,data.page.page_attachment_item);
		$('#dialogWindow').dialog('close');

		// 手数料計算
		changeTemperatureZoneType(destIndex);
		calculateDetailPrice(destIndex);
		calculatePrice(true);
		// 検索用の販売コードクリア
		$('#tabs-' + destIndex + " .search_sell_cd").val("");
		$('#tabs-' + destIndex + " .search_sell_vol").val("1");
	}
	// 配送方法から配送温度の手数料を設定する
	async function setTemperatureZoneType(destIndex){
		let deli_type = $('#register_destination_' + destIndex + '_m_delivery_type_id').val();
		let temperature_zone_type = $('#register_destination_' + destIndex + '_total_temperature_zone_type').val();
		let destination_address1 = $('#register_destination_' + destIndex + '_destination_address1').val();
		let prefecture_id = getPrefectureIdByName(destination_address1);
		return await $.ajax({
			url: '/gfh/order/api/deli_type/'+deli_type,
			method: 'GET',
			headers: {
					'Authorization': $('input[name="_token"]').val()
				},
			dataType: 'json',
			async: true,
		}).then(
			function (result) {
				if(!deli_type_list[deli_type]['delivery_fees']){
					deli_type_list[deli_type]['delivery_fees'] = {};
					for(key in result['delivery_fees']){
						deli_type_list[deli_type]['delivery_fees'][result['delivery_fees'][key]['m_prefecture_id']] = result['delivery_fees'][key]['delivery_fee'];
					}
				}
				// ついでに送料の設定も行う
				let payment_fee = 0;
				$('#register_destination_'+ destIndex +'_standard_fee').val(result.standard_fee);
				$('#register_destination_'+ destIndex +'_frozen_fee').val(result.frozen_fee);
				$('#register_destination_'+ destIndex +'_chilled_fee').val(result.chilled_fee);
				if(deli_type_list[deli_type]['delivery_fees'][prefecture_id]){
					$('#register_destination_'+ destIndex +'_shipping_fee').val(deli_type_list[deli_type]['delivery_fees'][prefecture_id]);
				} else {
					$('#register_destination_'+ destIndex +'_shipping_fee').val(0);
				}
				// 手数料計算
				changeTemperatureZoneType(destIndex);
				calculateDetailPrice(destIndex);
				calculatePrice(false);
			},
			function () {
				$('#register_destination_' + destIndex + '_payment_fee').val(0);
				alert("配送方法詳細取得APIの呼び出しに失敗しました。");
			}
   		)
	}
	// 配送種別変更時
	function changeTemperatureZoneType(destIndex){
		let payment_fee = 0;
		if($('#register_destination_' + destIndex + '_total_deli_flg').prop('checked')){
			// 同梱配送がチェックされている
			$('#register_destination_' + destIndex + '_total_temperature_zone_type').prop('disabled',false);
			let temperature_zone_type = $('#register_destination_' + destIndex + '_total_temperature_zone_type').val();
			if(temperature_zone_type == 0){
				payment_fee = Math.ceil($('#register_destination_'+ destIndex +'_standard_fee').val());
			} else if(temperature_zone_type == 1){
				payment_fee = Math.ceil($('#register_destination_'+ destIndex +'_frozen_fee').val());
			} else if(temperature_zone_type == 2){
				payment_fee = Math.ceil($('#register_destination_'+ destIndex +'_chilled_fee').val());
			}
		} else {
			$('#register_destination_' + destIndex + '_total_temperature_zone_type').prop('disabled',true);
			$("#tabs-" + destIndex + " .detail_area").each(function (){
				let cancel_flg = $(this).find(".register_destination_register_detail_cancel_flg").val();
				let cancel_timestamp = $(this).find(".register_destination_register_detail_cancel_timestamp").val();
				let is_delete = (cancel_flg == 1 || (cancel_timestamp != "" && cancel_timestamp.startsWith("0000-00-00") == false))?1:0;
				if(is_delete == 0){
					let three_temperature_zone_type = $(this).find(".three_temperature_zone_type").val();
					let payment_fee_temp = 0;
					if(three_temperature_zone_type == 0){
						payment_fee_temp = Math.ceil($('#register_destination_'+ destIndex +'_standard_fee').val());
					} else if(three_temperature_zone_type == 1){
						payment_fee_temp = Math.ceil($('#register_destination_'+ destIndex +'_frozen_fee').val());
					} else if(three_temperature_zone_type == 2){
						payment_fee_temp = Math.ceil($('#register_destination_'+ destIndex +'_chilled_fee').val());
					}
					if(payment_fee <= payment_fee_temp){
						payment_fee = payment_fee_temp;
					}
				}
			});
		}
		$('#register_destination_' + destIndex + '_payment_fee').val(payment_fee);
	}
	// 送付先の金額計算
	function calculateDetailPrice(destIndex) {
		let amount_detail = 0;
		let amount_tax_rate = {10:0,8:0};
		$("#tabs-" + destIndex + " .detail_area").each(function (){
			let cancel_flg = $(this).find(".register_destination_register_detail_cancel_flg").val();
			let cancel_timestamp = $(this).find(".register_destination_register_detail_cancel_timestamp").val();
			let is_delete = (cancel_flg == 1 || (cancel_timestamp != "" && cancel_timestamp.startsWith("0000-00-00") == false))?1:0;
			let price = $(this).find(".register_destination_register_detail_order_sell_price").val()
			let vol = $(this).find(".register_destination_register_detail_sell_vol").val();
			let amount = 0;
			if(Number.isInteger(parseInt(vol)) == false){
			} else {
				amount = price * vol;
			}
			if(is_delete == 0){
				// 削除していないもの
				$(this).find(".register_destination_register_detail_sell_amount").text(amount.toLocaleString());
				$(this).find(".register_destination_register_detail_order_sell_amount").val(amount);
				let tax_rate = $(this).find(".register_destination_register_detail_tax_rate").val();
				amount_tax_rate[parseInt(tax_rate*100)] += amount;
				amount_detail += amount;
			}
		});
		$("#tabs-" + destIndex).find('.register_destination_amount').text(amount_detail.toLocaleString());
		/*
		// 基本情報
		if($('#register_destination_' + destIndex + '_shipping_fee').val() > 0 && amount_detail >= $('#item_price_for_free_delivery_fee').val()){
			alert('送料を無料にします。');
			$('#register_destination_' + destIndex + '_shipping_fee').val(0);
		} else if($('#register_destination_' + destIndex + '_shipping_fee').val() == 0 && amount_detail < $('#item_price_for_free_delivery_fee').val()){
			alert('送料を基本送料にします。');
			$('#register_destination_' + destIndex + '_shipping_fee').val($('#base_delivery_fee').val());
		}
		*/
		// 消費税の振り分け
		for (let key in amount_tax_rate) {
			$("#tabs-" + destIndex).find('.destination_tax_rate_'+key).val(amount_tax_rate[key]);
		}
	}
	// 請求金額計算
	function calculatePrice(isCalcDiscount){
		// 商品の合計金額
		let register_destination_amount = 0;
		$('#destination_area .register_destination_amount').each(function(){
			if (!isNaN(parseInt($(this).html().replace(/,/g, ""))))
			{
				register_destination_amount += parseInt($(this).html().replace(/,/g, ""));
			}
		});
		// 割引金額
		let discount_value = $('#discount').val();
		if(isCalcDiscount){
			// 割引金額再計算
			let discount_rate = $('#discount_rate').val() / 100;
			discount_value = Math.ceil(discount_rate * register_destination_amount);
			$('#discount').val(discount_value);
		}
		// 商品金額計
		let sell_total_price = register_destination_amount - discount_value;
		$('#sell_total_price').val(sell_total_price);
		$('#sell_total_price_text').html(sell_total_price.toLocaleString());

		// 送料
		let shipping_fee = 0;
		$('#destination_area .register_destination_shipping_fee').each(function(){
			if (!isNaN(parseInt($(this).val().replace(/,/g, ""))))
			{
				shipping_fee += parseInt($(this).val().replace(/,/g, ""));
			}
		});
		$('#shipping_fee').val(shipping_fee);
		$('#shipping_fee_text').html(shipping_fee.toLocaleString());

		// 手数料
		let payment_fee = 0;
		// 支払い方法の手数料
		payment_fee += Math.floor($("#transfer_fee").val());
		$('#destination_area .register_destination_payment_fee').each(function(){
			if (!isNaN(parseInt($(this).val().replace(/,/g, ""))))
			{
				payment_fee += parseInt($(this).val().replace(/,/g, ""));
			}
		});
		$('#payment_fee').val(payment_fee);
		$('#payment_fee_text').html(payment_fee.toLocaleString());

		// 包装料
		let package_fee = 0;
		$('#destination_area .register_destination_wrapping_fee').each(function(){
			if (!isNaN(parseInt($(this).val().replace(/,/g, ""))))
			{
				package_fee += parseInt($(this).val().replace(/,/g, ""));
			}
		});
		$('#package_fee').val(package_fee);
		$('#package_fee_text').html(package_fee.toLocaleString());

		// 8%消費税
		let target_tax8 = 0;
		$('#destination_area .destination_tax_rate_8').each(function(){
			if (!isNaN(parseInt($(this).val().replace(/,/g, ""))))
			{
				target_tax8 += parseInt($(this).val().replace(/,/g, ""));
			}
		});
		let discount_tax8 = 0
		if(register_destination_amount != 0){
			discount_tax8 = discount_value * (target_tax8 / register_destination_amount);
		}
		let tax_8 = Math.floor((target_tax8 - discount_tax8) * 0.08);
		$('#tax_price08').html(tax_8.toLocaleString());
		$('#tax_price08_val').val(tax_8);
		$('#target_price08_val').val(target_tax8);
		$('#discount_price08_val').val(discount_tax8);

		// 10%消費税
		let target_tax10 = 0;
		$('#destination_area .destination_tax_rate_10').each(function(){
			if (!isNaN(parseInt($(this).val().replace(/,/g, ""))))
			{
				target_tax10 += parseInt($(this).val().replace(/,/g, ""));
			}
		});
		let discount_tax10 = 0;
		if(register_destination_amount != 0){
			discount_tax10 = discount_value * (target_tax10 / register_destination_amount);
		}
		let tax_10 = Math.floor((target_tax10 - discount_tax10 + shipping_fee + payment_fee + package_fee) * 0.1);
		$('#tax_price10').html(tax_10.toLocaleString());
		$('#tax_price10_val').val(tax_10);
		$('#target_price10_val').val(target_tax10 + shipping_fee + payment_fee + package_fee);
		$('#discount_price10_val').val(discount_tax10);
		let total_price = sell_total_price + tax_8 + tax_10 + shipping_fee + payment_fee + package_fee;

		$('#total_price').val(total_price);
		$('#total_price_text').html(total_price.toLocaleString());

		let total_use_coupon = 0;
		if (!isNaN(parseInt($('#use_coupon_store').val().replace(/,/g, ""))))
		{
			total_use_coupon += parseInt($('#use_coupon_store').val().replace(/,/g, ""));
		}
		if (!isNaN(parseInt($('#use_coupon_mall').val().replace(/,/g, ""))))
		{
			total_use_coupon += parseInt($('#use_coupon_mall').val().replace(/,/g, ""));
		}
		$('#total_use_coupon').val(total_use_coupon);
		$('#total_use_coupon_text').html(total_use_coupon.toLocaleString());
		let use_point = 0;
		if (!isNaN(parseInt($('#use_point').val().replace(/,/g, ""))))
		{
			use_point += parseInt($('#use_point').val().replace(/,/g, ""));
		}
		let order_total_price = total_price - total_use_coupon - use_point;
		$('#order_total_price').val(order_total_price);
		$('#order_total_price_text').html(order_total_price.toLocaleString());
	}
	function initFee(destIndex){
		let basename = '#register_destination_' + destIndex + '_';
		if($(basename +'m_delivery_type_id').val() != ""){
			let delivery_type_id = $(basename +'m_delivery_type_id').val();
			if(deli_type_list[delivery_type_id]){
				$(basename +'standard_fee').val(deli_type_list[delivery_type_id]['standard_fee']);
				$(basename +'frozen_fee').val(deli_type_list[delivery_type_id]['frozen_fee']);
				$(basename +'chilled_fee').val(deli_type_list[delivery_type_id]['chilled_fee']);
			}
		}
	}
	function initCalc(){
		$('#destination_area .destination_tab_body .destination_index').each(function(){
			initFee($(this).val());
			calculateDetailPrice( $(this).val());
		});
		calculatePrice(false);
	}
	async function readNoshiNamingPattern(id){
		return await $.ajax({
			url: '/gfh/order/api/noshi-naming-pattern/info/'+id,
			method: 'GET',
			headers: {
				'Authorization': $('input[name="_token"]').val()
			},
			dataType: 'json'
    	}).then(
      		function (result) {
				return result;
      		},
      		function () {
				alert("熨斗名入れパターン詳細APIの呼び出しに失敗しました。");
				return false;
			}
   		)		
	}
	async function readNoshiFormatData(id){
		return await $.ajax({
			url: '/gfh/order/api/noshi-format/info/'+id,
			method: 'GET',
			headers: {
				'Authorization': $('input[name="_token"]').val()
			},
			dataType: 'json'
    	}).then(
      		function (result) {
				return result;
      		},
      		function () {
				alert("熨斗種類詳細取得APIの呼び出しに失敗しました。");
				return false;
			}
   		)		
	}
	// 熨斗付属情報の取得
	async function readDetailData(destIndex,detailIndex){
		let basename = "#register_destination_" + destIndex + "_register_detail_" + detailIndex + "_";
		let noshi_id = $(basename+"noshi_id").val();
		let noshi_format_id = $(basename+"m_noshi_format_id").val();
		let noshi_detail_id = $(basename+"noshi_detail_id").val();
		let noshi_naming_pattern_id = $(basename+"m_noshi_naming_pattern_id").val();
		if(noshi_detail_id != ""){
			let format_data =  await readNoshiFormatData(noshi_format_id);
			if(format_data){
				$(basename+'m_noshi_format_name').val(format_data.noshi_format_name);
			}
			let naming_data = await readNoshiNamingPattern(noshi_naming_pattern_id);
			if(naming_data){
				$(basename + 'company_name_count').val(naming_data.company_name_count);
				$(basename + 'section_name_count').val(naming_data.section_name_count);
				$(basename + 'title_count').val(naming_data.title_count);
				$(basename + 'f_name_count').val(naming_data.f_name_count);
				$(basename + 'name_count').val(naming_data.name_count);
				$(basename + 'ruby_count').val(naming_data.ruby_count);
				for(idx=1;idx<=5;idx++){
					if(naming_data.company_name_count >= idx){
						$(basename + 'company_name' + idx).prop('disabled', false);
					} else {
						$(basename + 'company_name' + idx).prop('disabled', true);
					}
					if(naming_data.section_name_count >= idx){
						$(basename + 'section_name' + idx).prop('disabled', false);
					} else {
						$(basename + 'section_name' + idx).prop('disabled', true);
					}
					if(naming_data.title_count >= idx){
						$(basename + 'title' + idx).prop('disabled', false);
					} else {
						$(basename + 'title' + idx).prop('disabled', true);
					}
					if(naming_data.f_name_count >= idx){
						$(basename + 'firstname' + idx).prop('disabled', false);
					} else {
						$(basename + 'firstname' + idx).prop('disabled', true);
					}
					if(naming_data.name_count >= idx){
						$(basename + 'name' + idx).prop('disabled', false);
					} else {
						$(basename + 'name' + idx).prop('disabled', true);
					}
					if(naming_data.ruby_count >= idx){
						$(basename + 'ruby' + idx).prop('disabled', false);
					} else {
						$(basename + 'ruby' + idx).prop('disabled', true);
					}
				}
				$(basename + 'm_noshi_naming_pattern_name').val(naming_data.pattern_name);
			}
			// 熨斗の表示
			displayNoshiText(destIndex,detailIndex);
		}
		// 付属品の表示
		displayAttachmentText(destIndex,detailIndex);
	}
	function initNoshiView(){
		$('#destination_area .destination_tab_body .destination_index').each(function(){
			let destIndex = $(this).val();
			$("#tabs-" + destIndex + " .detail_area").each(function (){
				let detailIndex = $(this).data('detail_index');
				readDetailData(destIndex,detailIndex);
			});
			setTabName(destIndex);
		});
	}
	function initZipKana(){
		$('#destination_area .destination_tab_body .destination_index').each(function(){
			let destIndex = $(this).val();
			$("#tabs-" + destIndex + " .detail_area").each(function (){
				let detailIndex = $(this).data('detail_index');
				$.zipkana($('#register_destination_' + destIndex + '_destination_postal').val(),'#register_destination_' + destIndex + '_destination_address1_kana','#register_destination_' + destIndex + '_destination_address2_kana');
			});
		});
	}
	function setDeliveryTimeHopeOptions(index){
		let delivery_type = $('#register_destination_'+index+'_m_delivery_type_id option:selected').data('delivery_type');
		$('#register_destination_'+index+'_m_delivery_time_hope_id option').show();
		$('#register_destination_'+index+'_m_delivery_time_hope_id option').each(function(){
			if($(this).data('delivery_type') != delivery_type){
				$(this).hide();
				$(this).prop('selected',false);
			}
		});
	}
	function initDeliveryTimeHope(){
		$('#destination_area .destination_tab_body .destination_index').each(function(){
			let destIndex = $(this).val();
			setDeliveryTimeHopeOptions(destIndex);
		});
	}
	/**熨斗編集ダイアログ表示設定 */
	function initNoshiNamingPattern(){
		$('#dialogNoshiWindow tr.company_name input').attr('disabled',true);
		$('#dialogNoshiWindow tr.company_name').hide();
		for(idx=0;idx<$("#dialogNoshiWindow .company_name_count").val();idx++){
			$('#dialogNoshiWindow tr.company_name input.company_name'+(idx+1)).attr('disabled',false);
			$('#dialogNoshiWindow tr.company_name'+(idx+1)).show();
		}
		$('#dialogNoshiWindow tr.section_name input').attr('disabled',true);
		$('#dialogNoshiWindow tr.section_name').hide();
		for(idx=0;idx<$("#dialogNoshiWindow .section_name_count").val();idx++){
			$('#dialogNoshiWindow tr.section_name input.section_name'+(idx+1)).attr('disabled',false);
			$('#dialogNoshiWindow tr.section_name'+(idx+1)).show();
		}
		$('#dialogNoshiWindow tr.title input').attr('disabled',true);
		$('#dialogNoshiWindow tr.title').hide();
		for(idx=0;idx<$("#dialogNoshiWindow .title_count").val();idx++){
			$('#dialogNoshiWindow tr.title input.title'+(idx+1)).attr('disabled',false);
			$('#dialogNoshiWindow tr.title'+(idx+1)).show();
		}
		$('#dialogNoshiWindow tr.firstname input').attr('disabled',true);
		$('#dialogNoshiWindow tr.firstname').hide();
		for(idx=0;idx<$("#dialogNoshiWindow .f_name_count").val();idx++){
			$('#dialogNoshiWindow tr.firstname input.firstname'+(idx+1)).attr('disabled',false);
			$('#dialogNoshiWindow tr.firstname'+(idx+1)).show();
		}
		$('#dialogNoshiWindow tr.name input').attr('disabled',true);
		$('#dialogNoshiWindow tr.name').hide();
		for(idx=0;idx<$("#dialogNoshiWindow .name_count").val();idx++){
			$('#dialogNoshiWindow tr.name input.name'+(idx+1)).attr('disabled',false);
			$('#dialogNoshiWindow tr.name'+(idx+1)).show();
		}
		$('#dialogNoshiWindow tr.ruby input').attr('disabled',true);
		$('#dialogNoshiWindow tr.ruby').hide();
		for(idx=0;idx<$("#dialogNoshiWindow .ruby_count").val();idx++){
			$('#dialogNoshiWindow tr.ruby input.ruby'+(idx+1)).attr('disabled',false);
			$('#dialogNoshiWindow tr.ruby'+(idx+1)).show();
		}
	}
	function displayAttachmentText(index,detail_index){
		let basename = "#register_destination_" + index + "_register_detail_" + detail_index + "_";
		let html = "";
		$('#tabs-' + index + ' .attachment_item_' + index + '_' + detail_index + '.attachment_index').each(function(){
			let attachmentIndex = $(this).val();
			let basename2 = basename + 'order_dtl_attachment_item_' + attachmentIndex + '_';
			if($(basename2 + 'display_flg').val() == 1){
				html += '<span class="noshi_label">付属品コード：</span>';
				html += $.h($(basename2 + 'attachment_item_cd').val());
				html += '<span class="noshi_label"> / </span>';
				html += '<span class="noshi_label">付属品名：</span>';
				html += $.h($(basename2 + 'attachment_item_name').val());
				html += '<span class="noshi_label"> / </span>';
				html += '<span class="noshi_label">数量：</span>';	
				html += $.h($(basename2 + 'attachment_vol').val());
				html += '<br>';
			}
		});
		$(basename + 'attachment_html').html(html);
	}
	function displayNoshiText(index,detail_index){
		let basename = "#register_destination_" + index + "_register_detail_" + detail_index + "_";
		let html = "";
		if($(basename + 'm_noshi_format_id').val() != ""){
			html += '<span class="noshi_label">熨斗種類：</span>';
			html += '<span class="noshi_value">' + $.h($(basename + 'm_noshi_format_name').val()) + '</span>';
			html += '<span class="noshi_label"> / </span>';
			html += '<span class="noshi_label">名入れパターン：</span>';
			html += '<span class="noshi_value">' + $.h($(basename + 'm_noshi_naming_pattern_name').val()) + '</span>';
			html += '<span class="noshi_label"> / </span>';
			html += '<span class="noshi_label">貼付/同梱：</span>';
			if($(basename + 'attach_flg').val() == 1){
				html += '<span class="noshi_value">同梱</span>';
			} else {
				html += '<span class="noshi_value">貼付</span>';
			}
			html += '<span class="noshi_label"> / </span>';
			html += '<br><span class="noshi_label">表書き：</span>';
			html += '<span class="noshi_value">' + $.h($(basename + 'omotegaki').val()) + '</span><br>';
			if(!$(basename + 'company_name1').is(':disabled')){
				html += '<span class="noshi_label"> / </span>';
				html += '<span class="noshi_label">会社名：</span>';
				let ary = [];
				for(idx=1;idx<=5;idx++){
					if(!$(basename + 'company_name'+idx).is(':disabled')){
						ary.push($(basename + 'company_name'+idx).val());
					}
				}
				html += '<span class="noshi_value">';
				html += $.h(ary.join("、"));
				html += '</span>';
			}
			if(!$(basename + 'section_name1').is(':disabled')){
				html += '<span class="noshi_label"> / </span>';
				html += '<span class="noshi_label">部署名：</span>';
				let ary = [];
				for(idx=1;idx<=5;idx++){
					if(!$(basename + 'section_name'+idx).is(':disabled')){
						ary.push($(basename + 'section_name'+idx).val());
					}
				}
				html += '<span class="noshi_value">';
				html += $.h(ary.join("、"));
				html += '</span>';
			}
			if(!$(basename + 'title1').is(':disabled')){
				html += '<span class="noshi_label"> / </span>';
				html += '<span class="noshi_label">肩書：</span>';
				let ary = [];
				for(idx=1;idx<=5;idx++){
					if(!$(basename + 'title'+idx).is(':disabled')){
						ary.push($(basename + 'title'+idx).val());
					}
				}
				html += '<span class="noshi_value">';
				html += $.h(ary.join("、"));
				html += '</span>';
			}
			if(!$(basename + 'firstname1').is(':disabled')){
				html += '<span class="noshi_label"> / </span>';
				html += '<span class="noshi_label">苗字：</span>';
				let ary = [];
				for(idx=1;idx<=5;idx++){
					if(!$(basename + 'firstname'+idx).is(':disabled')){
						ary.push($(basename + 'firstname'+idx).val());
					}
				}
				html += '<span class="noshi_value">';
				html += $.h(ary.join("、"));
				html += '</span>';
			}
			if(!$(basename + 'name1').is(':disabled')){
				html += '<span class="noshi_label"> / </span>';
				html += '<span class="noshi_label">名前：</span>';
				let ary = [];
				for(idx=1;idx<=5;idx++){
					if(!$(basename + 'name'+idx).is(':disabled')){
						ary.push($(basename + 'name'+idx).val());
					}
				}
				html += '<span class="noshi_value">';
				html += $.h(ary.join("、"));
				html += '</span>';
			}
			if(!$(basename + 'ruby1').is(':disabled')){
				html += '<span class="noshi_label"> / </span>';
				html += '<span class="noshi_label">ルビ：</span>';
				let ary = [];
				for(idx=1;idx<=5;idx++){
					if(!$(basename + 'ruby'+idx).is(':disabled')){
						ary.push($(basename + 'ruby'+idx).val());
					}
				}
				html += '<span class="noshi_value">';
				html += $.h(ary.join("、"));
				html += '</span>';
			}
		}
		$(basename + 'noshi_html').html(html);
	}
	// 明細名前変更イベント
	function setTabName(destIndex){
		let name = $('#tabs-' + destIndex + ' .register_destination_name').val().trim();
		let company = $('#tabs-' + destIndex + ' .destination_company_name').val().trim();
		let names = [];
		let tabname = "送付先" + (parseInt(destIndex) + 1);
		if(name != ""){
			names.push(name);
		}
		if(company != ""){
			names.push(company);
			}
		if(names.length > 0){
				tabname = names.join('／');
		}
		$('#dest_tab_'+destIndex).text(tabname);
		$('#tabs-' + destIndex + ' .destination_tab_display_name').val(tabname);
	}
	// 熨斗情報をコピーする
	function copyNoshi(fromIndex,fromDetailIndex,toIndex,toDetailIndex){
		let basefrom = '#register_destination_' + fromIndex + '_register_detail_' + fromDetailIndex + '_';
		let baseto = '#register_destination_' + toIndex + '_register_detail_' + toDetailIndex + '_';
		let id_suf = [
			'noshi_id',
			'noshi_detail_id',
			'noshi_detail_name',
			'm_noshi_format_id',
			'm_noshi_format_name',
			'noshi_detail_name',
			'm_noshi_naming_pattern_id',
			'm_noshi_naming_pattern_name',
			'omotegaki',
			'attach_flg',
			'company_name_count',
			'section_name_count',
			'f_name_count',
			'title_count',
			'name_count',
			'ruby_count',
			'company_name1',
			'section_name1',
			'title1',
			'firstname1',
			'name1',
			'ruby1',
			'company_name2',
			'section_name2',
			'title2',
			'firstname2',
			'name2',
			'ruby2',
			'company_name3',
			'section_name3',
			'title3',
			'firstname3',
			'name3',
			'ruby3',
			'company_name4',
			'section_name4',
			'title4',
			'firstname4',
			'name4',
			'ruby4',
			'company_name5',
			'section_name5',
			'title5',
			'firstname5',
			'name5',
			'ruby5'
		];
		for (let i = 0; i < id_suf.length; i++) {
			$(baseto + id_suf[i]).val($(basefrom+id_suf[i]).val());
			$(baseto + id_suf[i]).attr('disabled',$(basefrom+id_suf[i]).is(':disabled'));
		}

		// 't_order_dtl_noshi_id', 初期化
		$(baseto + "t_order_dtl_noshi_id").val("");
		displayNoshiText(toIndex,toDetailIndex);
	}

	// 付属品情報をコピーする
	function copyAttachment(fromIndex,fromDetailIndex,toIndex,toDetailIndex){
		let html_detail = "";
		$('.attachment_item_' + fromIndex + '_' + fromDetailIndex + '.attachment_index').each(function(){
			let html = attachment_item_template;
			let basefrom = '#register_destination_' + fromIndex + '_register_detail_' + fromDetailIndex + '_order_dtl_attachment_item_' + $(this).val() + '_';
			$('#register_destination_' + fromIndex + '_register_detail_' + fromDetailIndex + '_order_dtl_attachment_item_' + $(this).val() + '_display_flg').val()
			html = html.replaceAll("##index0##",toIndex);
			html = html.replaceAll("##detailindex##",toDetailIndex);
			html = html.replaceAll("##attachmentIndex##",$(this).val());
			html = html.replaceAll("##t_order_dtl_attachment_item_id##",""); // 初期化
			html = html.replaceAll("##t_order_dtl_id##",""); // 初期化
			html = html.replaceAll("##display_flg##",$(basefrom + 'display_flg').val());
			html = html.replaceAll("##m_ami_attachment_item_id##",$.h($(basefrom + 'm_ami_attachment_item_id').val()));
			html = html.replaceAll("##attachment_item_cd##",$.h($(basefrom + 'attachment_item_cd').val()));
			html = html.replaceAll("##attachment_item_name##",$.h($(basefrom + 'attachment_item_name').val()));
			html = html.replaceAll("##attachment_vol##",$.h($(basefrom + 'attachment_vol').val()));
			html_detail += html;
		});
		$('#register_destination_' + toIndex + '_register_detail_' + toDetailIndex + '_attachment_html').after(html_detail);
		displayAttachmentText(toIndex,toDetailIndex);
	}
	// 送付先受注明細をコピーする
	function copyDestinationDetail(fromIndex,fromDetailIndex,toIndex,toDetailIndex){
		let basename = '#register_destination_' + fromIndex + '_register_detail_' + fromDetailIndex + '_';
		let basenameto = '#register_destination_' + toIndex + '_register_detail_' + toDetailIndex + '_';
		let data = {};
		data['m_ami_ec_page_id'] = $(basename + 'sell_id').val();
		data['ec_page_cd'] = $(basename + 'sell_cd').val();
		data['ec_page_title'] = $(basename + 'sell_name').val();
		data['sales_price'] =  $(basename + 'order_sell_price').val();
		data['tax_rate'] =  $(basename + 'tax_rate').val();
		data['three_temperature_zone_type'] = $(basename + 'three_temperature_zone_type').val();
		data['image_path'] = $(basename + 'image_path').val();
		data['m_ami_page_id'] = $(basename + 'm_ami_page_id').val();
		data['page_desc'] = $(basename + 'page_desc').val();
		// 後で設定する
		data['page'] = {'page_attachment_item':[],'m_ami_page_id':$(basename + 'm_ami_page_id').val(),'image_path':$(basename + 'image_path').val(),'page_desc':$(basename + 'page_desc').val(),'page_sku':[{'three_temperature_zone_type':$(basename + 'three_temperature_zone_type').val()}]};
		appendSellDetail(data,$(basename + 'order_sell_vol').val());
		copyNoshi(fromIndex,fromDetailIndex,toIndex,toDetailIndex);
		copyAttachment(fromIndex,fromDetailIndex,toIndex,toDetailIndex);
		$(basenameto+"attachment_item_group_id").val($(basename + 'attachment_item_group_id').val());
	}
	// 送付先をコピーする
	function copyDestination(fromIndex,toIndex){
		let basefrom = '#register_destination_'+fromIndex+'_';
		let baseto = '#register_destination_'+toIndex+'_';
		// テキスト/セレクトのコピー
		let id_suf = [
			'destination_id',
			'destination_tax_8',
			'destination_tax_10',
			'destination_standard_fee',
			'destination_frozen_fee',
			'destination_chilled_fee',
			'destination_tel',
			'destination_name_kana',
			'destination_name',
			'm_delivery_type_id',
			'deli_hope_date',
			'm_delivery_time_hope_id',
			'deli_plan_date',
			'invoice_comment',
			'picking_comment',
			'sender_name',
			'total_temperature_zone_type',
			'destination_postal',
			'destination_address1_kana',
			'destination_address1',
			'destination_address2_kana',
			'destination_address2',
			'destination_address3',
			'destination_address4',
			'destination_company_name',
			'destination_division_name',
			'gift_message',
			'gift_wrapping',
			'nosi_type',
			'nosi_name',
			'shipping_fee',
			'wrapping_fee'
		];
		for (let i = 0; i < id_suf.length; i++) {
			$(baseto + id_suf[i]).val($(basefrom+id_suf[i]).val());
		}
		// チェックのコピー
		id_suf = [
			'partial_deli_flg',
			'pending_flg',
			'total_deli_flg',
		];
		for (let i = 0; i < id_suf.length; i++) {
			$(baseto + id_suf[i]).prop('checked',$(basefrom+id_suf[i]).prop('checked'));
		}
		if($(baseto + 'total_deli_flg').prop('checked')){
			$(baseto + 'total_temperature_zone_type').prop('disabled',false);
		} else {
			$(baseto + 'total_temperature_zone_type').prop('disabled',true);
		}
		setTabName(toIndex);
		// 商品のコピー
		let toDetailIndex = 0;
		$('#tabs-' + fromIndex + ' .check_copy_detail:checked').each(function(){
			copyDestinationDetail(fromIndex,$(this).data("detail_index"),toIndex,toDetailIndex);
			toDetailIndex++;
		});
		changeTemperatureZoneType(toIndex);
		calculateDetailPrice(toIndex);
		calculatePrice(false);
		$('.check_copy_detail').prop('checked',false);
	}
	function setDefaultAttachmentItem(destIndex,detailIndex){
		let basename = '#register_destination_' + destIndex + '_register_detail_' + detailIndex + '_';
		let sell_id = $(basename+'sell_id').val(); // ami_ec_page_id
		$.ajax({
			url: '/gfh/order/api/ami_page/'+sell_id,
			method: 'GET',
			headers: {
				'Authorization': $('input[name="_token"]').val()
			},
			dataType: 'json',
			async: true,
    	}).then(
      		function (result) {
				setAttachmentItem(destIndex,detailIndex,result.page.page_attachment_item);
      		},
      		function () {
				alert("商品取得APIの呼び出しに失敗しました。");
			}
   		)		
	}
	$(document).ready(function() {
		$(document).on('change','#m_pay_type_id',async function(){
			await initTransferFee();
			calculatePrice(false);
		});
		$(document).on('change','#discount',function(){
			// 割引金額変更
			let discount = $(this).val();
			if(Number.isInteger(parseInt(discount)) == false){
				$(this).val(discount);
			}
			calculatePrice(false);
		});
		// 郵便番号から住所が特定できたときに送料等を設定する
		AjaxZip3.onSuccess = async function() {
			let $focused = $(':focus');
			let idname = $focused.attr('id');
			if(idname == 'order_postal'){
				$.zipkana($('#order_postal').val(),'#order_address1_kana','#order_address2_kana');
			} else if(idname == 'billing_postal'){
				$.zipkana($('#billing_postal').val(),'#billing_address1_kana','#billing_address2_kana');
			}
			if($focused.hasClass('refresh_shipping_fee')){
				let index = $focused.data('index');
				$.zipkana($('#register_destination_' + index + '_destination_postal').val(),'#register_destination_' + index + '_destination_address1_kana','#register_destination_' + index + '_destination_address2_kana');
				$.delivery_day($('#register_destination_' + index + '_destination_postal').val(),index);
				await setTemperatureZoneType(index);
			}
		};
		$(document).on('change','.action_change_campaign_flg',function(){
			if($(this).prop('checked')){
				// キャンペーン対象がON
				let index = $(this).data('index');
				$(".action_change_campaign_flg").each(function(){
					$(this).prop('checked',false);
				});
				$('#tabs-'+index+' .action_change_campaign_flg').prop('checked',true);
			}
		});
		$(document).on('change','.action_change_attachment_item_group_id',function(){
			// 熨斗の設定をクリアする
			let destIndex =  $(this).data('index');
			let detailIndex = $(this).data('detail_index');
			let basename = "#register_destination_" + destIndex + "_register_detail_" + detailIndex + "_";
			$(basename + "t_order_dtl_noshi_id").val("");
			$(basename + "noshi_id").val("");
			$(basename + "noshi_detail_id").val("");
			$(basename + "noshi_detail_name").val("");
			$(basename + "m_noshi_format_id").val("");
			$(basename + "t_order_dtl_noshi_id").val("");
			$(basename + "m_noshi_naming_pattern_id").val("");
			$(basename + "m_noshi_naming_pattern_name").val("");
			displayNoshiText(destIndex,detailIndex);
			setDefaultAttachmentItem(destIndex,detailIndex);
		});
		// 配送方法変更時
		$(document).on('change','.action_change_delivery_type',async function(e){
			let index = $(this).data('index');
			setDeliveryTimeHopeOptions(index);
			await setTemperatureZoneType(index);
		});
		$(document).on('change','.action_change_destination_address1',async function(){
			let index = $(this).data('index');
			await setTemperatureZoneType(index);
		});
		// 配送種別変更時
		$(document).on('change','.action_change_total_temperature_zone_type',function(){
			let index = $(this).data('index');
			// 手数料計算
			changeTemperatureZoneType(index);
			calculatePrice(false);
		});
		// 同梱配送変更時
		$(document).on('change','.action_change_total_deli_flg',function(){
			let index = $(this).data('index');
			// 手数料計算
			changeTemperatureZoneType(index);
			calculatePrice(false);
		});
		// 送付先削除ボタン押下
		$(document).on('click','.action_remove_destination',function(){
			let index = $(this).data('index');
			let tabcnt =  $('#destination_area .destination_tab').length;
			if(tabcnt <= 1){
				alert("この送付先は削除できません");
			} else {
				if($('#tabs-'+index+' .detail_area').length > 0){
					alert("明細が存在する配送先は削除できません");
					return;
				}
				$('#dest_tab_'+index).parent().remove();
				$('#tabs-'+index).remove();
				$("#destination_area .nav-tabs .destination_tab:last").addClass("active");
				$("#destination_area .destination_tab_body div.tab-pane:last").addClass("active");
				calculatePrice(true);
			}
			
		});
		// 明細削除ボタン押下
		$(document).on('click','.action_remove_register_destination_detail',function(){
			let index = $(this).data('index');
			let detail_index = $(this).data('detail_index');
			let basename = "#register_destination_" + index + "_register_detail_" + detail_index + "_";
			if($(basename + 't_order_dtl_id').val() == ""){
				// 詳細IDがない場合は削除する
				$('#tabs-' + index + ' .detail_row_' + index + '_' + detail_index).remove();
			} else {
				// 詳細IDがある場合はフラグを立てて削除状態にする
				$(this).parent("td").html('<span class="u-center font-FF0000">削除</span>');
				$(this).hide();
				$(basename + 'cancel_flg').val(1);
				$(basename + 'sell_name').attr('disabled',true);
				$(basename + 'order_sell_vol').attr('disabled',true);
				$(basename + 'attachment_item_group_id').attr('disabled',true);
				$(basename + 'edit_noshi').attr('disabled',true);
				$(basename + 'edit_attachment_items').attr('disabled',true);
			}
			// 手数料計算
			changeTemperatureZoneType(index);
			// 金額再計算
			calculateDetailPrice(index);
			calculatePrice(true);
		});
		$(document).on('change','.register_destination_name',function(){
			let destIndex = $(this).parents(".destination_tab_data").find(".destination_index").val();
			setTabName(destIndex);
		});
		// 明細企業名変更イベント
		$(document).on('change','.destination_company_name',function(){
			let destIndex = $(this).parents(".destination_tab_data").find(".destination_index").val();
			setTabName(destIndex);
		});
		// 送料変更
		$(document).on('change', '.register_destination_shipping_fee', function () {
			calculatePrice(false);
		});
		// 手数料変更
		$(document).on('change', '.register_destination_payment_fee', function () {
			calculatePrice(false);
		});
		// 包装料変更
		$(document).on('change', '.register_destination_wrapping_fee', function () {
			calculatePrice(false);
		});
		// ストアクーポン変更
		$(document).on('change', '#use_coupon_store', function () {
			calculatePrice(false);
		});
		// モールクーポン変更
		$(document).on('change', '#use_coupon_mall', function () {
			calculatePrice(false);
		});
		// 利用ポイント変更
		$(document).on('change', '#use_point', function () {
			calculatePrice(false);
		});
		function setAttachementDefaultVol(destIndex,detailIndex){
			let basename = '#register_destination_' + destIndex + '_register_detail_' + detailIndex + '_';
			let group_id = $(basename+'attachment_item_group_id').val();
			let sell_id = $(basename+'sell_id').val(); // ami_ec_page_id
			// 商品数量
			let order_sell_vol = $(basename+'order_sell_vol').val();
			if(Number.isInteger(parseInt(order_sell_vol)) == false){
				order_sell_vol = 1;
			}
			$.ajax({
				url: '/gfh/order/api/ami_page/'+sell_id,
				method: 'GET',
				headers: {
					'Authorization': $('input[name="_token"]').val()
				},
				dataType: 'json',
				async: true,
			}).then(
				function (result) {
					$.each(result.page.page_attachment_item, function(index, value) {
						if (value.group_id == group_id) {
							$('#tabs-' + destIndex + ' .attachment_item_' + destIndex + '_' + detailIndex + '.attachment_index').each(function(){
								let attachmentIndex = $(this).val();
								let basename2 = basename + 'order_dtl_attachment_item_' + attachmentIndex + '_';
								if($(basename2 + 'm_ami_attachment_item_id').val() == value.attachment_item.m_ami_attachment_item_id){
									// 商品数量＊付属品数量
									$(basename2 + 'attachment_vol').val(value.item_vol * order_sell_vol);
								}
							});
						}
					});
					displayAttachmentText(destIndex,detailIndex);
				},
				function () {
					alert("商品取得APIの呼び出しに失敗しました。");
				}
			);
		}
		// 商品個数変更
		$(document).on('change', '.register_destination_register_detail_sell_vol', function () {
			let destIndex = $(this).parents(".destination_tab_data").find(".destination_index").val();
			let detailIndex = $(this).parents(".detail_area").data("detail_index");
			// 商品に紐づく付属品の数量を再設定
			setAttachementDefaultVol(destIndex,detailIndex);
			calculateDetailPrice(destIndex);
			calculatePrice(true);
		});
		// ＋ボタン押下
		$(".action_append_destination").on("click",async function(){
			let destIndex = tabnumber;
			await appendDestination(tabnumber);
			setDeliveryTimeHopeOptions(destIndex);
		});
		// コピーして送付先作成押下
		$(".action_destination_copy").on("click",async function(){
			let destIndex = getActiveTabIndex();
			await appendDestination(tabnumber);
			let nextIndex = getActiveTabIndex();

			copyDestination(destIndex,nextIndex);
			setDeliveryTimeHopeOptions(nextIndex);
		});
		// 注文主情報を自動入力を押下
		$(".action_destination_copy_customer").on("click",async function(){
			let index = getActiveTabIndex();
			let idbase = '#register_destination_'+index+'_destination_';
			$("#tabs-"+index+" input").removeClass("sizeover-error");
			$(idbase + 'tel').val($('#order_tel1').val());
			$(idbase + 'name_kana').val($('#order_name_kana').val());
			$(idbase + 'name').val($('#order_name').val());
			$(idbase + 'postal').val($('#order_postal').val());
			$(idbase + 'address1_kana').val($('#order_address1_kana').val());
			$(idbase + 'address1').val($('#order_address1 option:selected').val());
			$(idbase + 'address2_kana').val($('#order_address2_kana').val());
			$(idbase + 'address2').val($('#order_address2').val());
			$(idbase + 'address3').val($('#order_address3').val());
			$(idbase + 'address4').val($('#order_address4').val());
			$(idbase + 'company_name').val($('#order_corporate_name').val());
			$(idbase + 'division_name').val($('#order_division_name').val());
			
			if($("#campaign_flg").val() == '1'){
				// キャンペーン対象を立てる。
				$(".action_change_campaign_flg").each(function(){
					$(this).prop('checked',false);
				});
				$('#tabs-'+index+' .action_change_campaign_flg').prop('checked',true);
			}
			$.delivery_day($('#order_postal').val(),index);
			await setTemperatureZoneType(index);
			setTabName(index);
		});
		// 送付先を検索するを押下
		$(document).on('click', '.action_destination_search', function () {
			$.ajax({
				url: '/gfh/order/api/customer/destination/list',
				method: 'POST',
				data: {
					'cust_id': $('#m_cust_id').val(),
					'page_list_count':{{\Config::get('Common.const.page_limit')}},
					'hidden_next_page_no':1,
					'_token': $('input[name="_token"]').val()
				},
				success: function(response) {
					$('#dialogWindow .dialog_body').html(response.html);
					$('#dialogWindow').dialog('open');
				},
				error: function(xhr, status, error) {
					alert("送付先検索モーダルAPIの呼び出しに失敗しました。");
				}
			});
		});
		$(document).on('click', '.destination_selected_action', function () {
			// 送付先モーダル選択ボタン押下時イベント
			let destination_id = $(this).data('m_destination_id');
			$.ajax({
				url: '/gfh/order/api/customer/destination/'+destination_id,
				method: 'GET',
				headers: {
					'Authorization': $('input[name="_token"]').val()
				},
				dataType: 'json',
				success: function(json) {
					let index = getActiveTabIndex();
					let idbase = '#register_destination_'+index+'_destination_';
					$("#tabs-"+index+" input").removeClass("sizeover-error");
					$(idbase + 'id').val(destination_id);
					$(idbase + 'tel').val(json.destination_tel);
					$(idbase + 'name_kana').val(json.destination_name_kana);
					$(idbase + 'name').val(json.destination_name);
					$(idbase + 'postal').val(json.destination_postal);
					$(idbase + 'address1').val(json.destination_address1);
					$(idbase + 'address2').val(json.destination_address2);
					$(idbase + 'address3').val(json.destination_address3);
					$(idbase + 'address4').val(json.destination_address4);
					$(idbase + 'company_name').val(json.destination_company_name);
					$(idbase + 'division_name').val(json.division_name);
					// 後で設定する
					$(idbase + 'address1_kana').val("");
					$(idbase + 'address2_kana').val("");
					$.zipkana($('#register_destination_' + index + '_destination_postal').val(),'#register_destination_' + index + '_destination_address1_kana','#register_destination_' + index + '_destination_address2_kana');
					$.delivery_day(json.destination_postal,index);

					setTemperatureZoneType(index);
					setTabName(index);
					$('#dialogWindow').dialog('close');
				},
				error: function(xhr, status, error) {
					alert("送付先情報取得APIの呼び出しに失敗しました。");
				}
			});
		});		
		// 商品検索を押下
		$(document).on('click', '.action_sku_search', function () {
			let sell_cd = $(this).parents(".detail_sell_table").find(".search_sell_cd").val().trim();
			let vol = $(this).parents(".detail_sell_table").find(".search_sell_vol").val().trim();
			if(Number.isInteger(parseInt(vol)) == false){
				vol = 1;
			}
			if($("#m_ecs_id").val() == ""){
				alert("ECサイトを指定してください");
				return;
			}
			if(sell_cd != ""){
				$.ajax({
					url: '/gfh/order/api/ami_page/search',
					method: 'GET',
					headers: {
						'Authorization': $('input[name="_token"]').val()
					},
					dataType: 'json',
					data: {
						'm_ecs_id': $("#m_ecs_id").val(),
						'ec_page_cd': sell_cd,
					},
					async: true,
    			}).then(
      				function (result) {
						appendSellDetail(result,vol);
      				},
      				function () {
						$.ajax({
							url: '/gfh/order/api/ami_page/list',
							method: 'POST',
							data: {
								'ecs_id':$("#m_ecs_id").val(),
								'page_list_count':{{\Config::get('Common.const.page_limit')}},
								'hidden_next_page_no':1,
								'ec_page_cd':sell_cd,
								'_token': $('input[name="_token"]').val()
							},
							success: function(response) {
								$('#dialogWindow .dialog_body').html(response.html);
								$('#dialogWindow .dialog_body .ecs_name').text($("#m_ecs_id option:selected").text());
								$('#dialogWindow').dialog('open');
							},
							error: function(xhr, status, error) {
								alert("商品検索モーダルAPIの呼び出しに失敗しました。");
							}
						});
      				}
   			 	);
			} else {
				$.ajax({
					url: '/gfh/order/api/ami_page/list',
					method: 'POST',
					data: {
						'm_ecs_id':$("#m_ecs_id").val(),
						'page_list_count':{{\Config::get('Common.const.page_limit')}},
						'hidden_next_page_no':1,
						'_token': $('input[name="_token"]').val()
					},
					success: function(response) {
						$('#dialogWindow .dialog_body').html(response.html);
						$('#dialogWindow .dialog_body .ecs_name').text($("#m_ecs_id option:selected").text());
						$('#dialogWindow').dialog('open');
					},
					error: function(xhr, status, error) {
						alert("商品検索モーダルAPIの呼び出しに失敗しました。");
					}
				});
			}
		});
		// 商品選択を押下
		$(document).on('click', '.sell_selected_action', function () {
			let id = $(this).attr('data-ami_ec_page_id');
			let vol = $(this).parents('tr').find('.sell_vol').val();
			if(Number.isInteger(parseInt(vol)) == false){
				vol = 1;
			}
    		$.ajax({
				url: '/gfh/order/api/ami_page/'+id,
				method: 'GET',
				headers: {
					'Authorization': $('input[name="_token"]').val()
				},
				dataType: 'json',
				async: true,
    		}).then(
      			function (result) {
					appendSellDetail(result,vol);
					calculatePrice(true);
      			},
      			function () {
					alert("商品取得APIの呼び出しに失敗しました。");
				}
   			)
		});	
		// 熨斗編集を押下
		let noshi_edit_running = false;
		$(document).on('click', '.action_edit_noshi', function () {
			if(noshi_edit_running){
				return;
			}
			noshi_edit_running = true;
			let index = $(this).data('index');
			let detail_index = $(this).data('detail_index');
			let basename = "#register_destination_" + index + "_register_detail_" + detail_index + "_";
			// インデックス設定
			$("#dialogNoshiWindow .index").val(index);
			$("#dialogNoshiWindow .detail_index").val(detail_index);
			// 表書き
			$("#dialogNoshiWindow .omotegaki").val($(basename+"omotegaki").val());
			// 名入れ
			for(idx=1;idx<=5;idx++){
				$("#dialogNoshiWindow input.company_name"+idx).val($(basename+"company_name"+idx).val());
				$("#dialogNoshiWindow input.section_name"+idx).val($(basename+"section_name"+idx).val());
				$("#dialogNoshiWindow input.title"+idx).val($(basename+"title"+idx).val());
				$("#dialogNoshiWindow input.firstname"+idx).val($(basename+"firstname"+idx).val());
				$("#dialogNoshiWindow input.name"+idx).val($(basename+"name"+idx).val());
				$("#dialogNoshiWindow input.ruby"+idx).val($(basename+"ruby"+idx).val());
			}
			// 名入れカウント
			$("#dialogNoshiWindow .company_name_count").val("0");
			$("#dialogNoshiWindow .section_name_count").val("0");
			$("#dialogNoshiWindow .title_count").val("0");
			$("#dialogNoshiWindow .f_name_count").val("0");
			$("#dialogNoshiWindow .name_count").val("0");
			$("#dialogNoshiWindow .ruby_count").val("0");
			
			let ami_ec_page_id = $(basename + "sell_id").val();
			let m_noshi_format_id = $(basename + "m_noshi_format_id").val();
			let m_noshi_naming_pattern_id = $(basename + "m_noshi_naming_pattern_id").val();
			$("#dialogNoshiWindow .ami_ec_page_id").val(ami_ec_page_id);

			// これはセレクト
			$("#dialogNoshiWindow .attach_flg").val($(basename + "attach_flg").val());

			// 熨斗種類セレクトの設定
			$('#dialogNoshiWindow .m_noshi_format_id option').remove();	
			$('#dialogNoshiWindow .m_noshi_format_id').append($('<option>').val("").text("熨斗なし"));
			$('#dialogNoshiWindow .m_noshi_naming_pattern_id option').remove();	
			$('#dialogNoshiWindow .m_noshi_naming_pattern_id').append($('<option>').val("").text(""));
			$.ajax({
				url: '/gfh/order/api/noshi-format/list/'+ami_ec_page_id,
				method: 'GET',
				headers: {
					'Authorization': $('input[name="_token"]').val()
				},
				dataType: 'json',
				async: true,
    		}).then(
      			function (result) {
					$('#dialogNoshiWindow .m_noshi_format_id option').remove();	
					$('#dialogNoshiWindow .m_noshi_format_id').append($('<option>').val("").text("熨斗なし"));
					let isSelectedNoshiFormat = false;
					for (var key in result) {
						if($(basename+"attachment_item_group_id").val() != key){
							// 同じグループ種類じゃない場合はスキップ
							continue;
						}
						for (var key2 in result[key]) {
							let isSelected = false;
							if(m_noshi_format_id == result[key][key2]["m_noshi_format_id"]){
								isSelected = true;
								isSelectedNoshiFormat = true;
							}
							let option = $('<option>').val(result[key][key2]["m_noshi_format_id"]).text(result[key][key2]["noshi_format_name"]).prop('selected', isSelected);
						    $('#dialogNoshiWindow .m_noshi_format_id').append(option);
						}
					}
					if(isSelectedNoshiFormat){
						let isSelected = false;
						let isSelectedNoshiNamingPattern = false;
						// 熨斗種類が設定されている場合は名入パターン一覧を取得する
						$.ajax({
							url: '/gfh/order/api/noshi-naming-pattern/list/'+m_noshi_format_id,
							method: 'GET',
							headers: {
								'Authorization': $('input[name="_token"]').val()
							},
							dataType: 'json',
							async: true,
						}).then(
							function (result) {
								$('#dialogNoshiWindow .m_noshi_naming_pattern_id option').remove();	
								$('#dialogNoshiWindow .m_noshi_naming_pattern_id').append($('<option>').val("").text(""));
								for (var key in result) {
									let isSelected = false;
									if(m_noshi_naming_pattern_id == result[key]["m_noshi_naming_pattern_id"]){
										isSelected = true;
										isSelectedNoshiNamingPattern = true;
										$("#dialogNoshiWindow .company_name_count").val(result[key]["company_name_count"]);
										$("#dialogNoshiWindow .section_name_count").val(result[key]["section_name_count"]);
										$("#dialogNoshiWindow .title_count").val(result[key]["title_count"]);
										$("#dialogNoshiWindow .f_name_count").val(result[key]["f_name_count"]);
										$("#dialogNoshiWindow .name_count").val(result[key]["name_count"]);
										$("#dialogNoshiWindow .ruby_count").val(result[key]["ruby_count"]);
									}
									let option = $('<option>').val(result[key]["m_noshi_naming_pattern_id"]).text(result[key]["pattern_name"]).prop('selected', isSelected);
								    $('#dialogNoshiWindow .m_noshi_naming_pattern_id').append(option);
								}
								initNoshiNamingPattern();
								$("#dialogNoshiWindow").dialog("open");
								noshi_edit_running = false;
							},
							function () {
								noshi_edit_running = false;
								alert("熨斗名入れパターン一覧の呼び出しに失敗しました。");
							}
						)
					} else {
						initNoshiNamingPattern();
						$("#dialogNoshiWindow").dialog("open");
						noshi_edit_running = false;
					}
				},
      			function () {
					noshi_edit_running = false;
					alert("熨斗種類一覧APIの呼び出しに失敗しました。");
				}
   			)
		});
		let noshi_detail_data = {};
		// 熨斗種類変更時
		$(document).on('change', '#dialogNoshiWindow .m_noshi_format_id', function () {
			// 名入れパターン一覧取得
			$("#dialogNoshiWindow .noshi_id").val("");
			$("#dialogNoshiWindow .noshi_detail_id").val("");
			$('#dialogNoshiWindow .m_noshi_naming_pattern_id option').remove();	
			$('#dialogNoshiWindow .m_noshi_naming_pattern_id').append($('<option>').val("").text(""));
			$("#dialogNoshiWindow .company_name_count").val("0");
			$("#dialogNoshiWindow .section_name_count").val("0");
			$("#dialogNoshiWindow .title_count").val("0");
			$("#dialogNoshiWindow .f_name_count").val("0");
			$("#dialogNoshiWindow .name_count").val("0");
			$("#dialogNoshiWindow .ruby_count").val("0");
			if($(this).val() != ""){
				$.ajax({
					url: '/gfh/order/api/noshi-naming-pattern/list/'+$(this).val(),
					method: 'GET',
					headers: {
						'Authorization': $('input[name="_token"]').val()
					},
					dataType: 'json',
					async: true,
				}).then(
					function (result) {
						noshi_detail_data = {};
						for (var key in result) {
							let option = $('<option>').val(result[key]["m_noshi_naming_pattern_id"]).text(result[key]["pattern_name"]).prop('selected', false);
						    $('#dialogNoshiWindow .m_noshi_naming_pattern_id').append(option);
							// アクセスしやすいように熨斗パターンID：熨斗詳細IDで保持する
							noshi_detail_data[result[key]["m_noshi_naming_pattern_id"]] = result[key];
						}
						initNoshiNamingPattern();
					},
					function () {
						alert("熨斗名入れパターン一覧APIの呼び出しに失敗しました。");
					}
				)
			} else {
				initNoshiNamingPattern();
			}
		});
		// 名入れパターン変更時
		$(document).on('change', '#dialogNoshiWindow .m_noshi_naming_pattern_id', function () {
			// 熨斗詳細を保存
			$("#dialogNoshiWindow .noshi_id").val("");
			$("#dialogNoshiWindow .noshi_detail_id").val("");
			if(noshi_detail_data[$(this).val()]){
				$("#dialogNoshiWindow .noshi_id").val(noshi_detail_data[$(this).val()]['m_noshi_id']);
				$("#dialogNoshiWindow .noshi_detail_id").val(noshi_detail_data[$(this).val()]['m_noshi_detail_id']);
			}
			$("#dialogNoshiWindow .company_name_count").val("0");
			$("#dialogNoshiWindow .section_name_count").val("0");
			$("#dialogNoshiWindow .title_count").val("0");
			$("#dialogNoshiWindow .f_name_count").val("0");
			$("#dialogNoshiWindow .name_count").val("0");
			$("#dialogNoshiWindow .ruby_count").val("0");
			if($(this).val() != ""){
				$.ajax({
					url: '/gfh/order/api/noshi-naming-pattern/info/'+$(this).val(),
					method: 'GET',
					headers: {
						'Authorization': $('input[name="_token"]').val()
					},
					dataType: 'json',
					async: true,
				}).then(
					function (result) {
						$("#dialogNoshiWindow .company_name_count").val(result["company_name_count"]);
						$("#dialogNoshiWindow .section_name_count").val(result["section_name_count"]);
						$("#dialogNoshiWindow .title_count").val(result["title_count"]);
						$("#dialogNoshiWindow .f_name_count").val(result["f_name_count"]);
						$("#dialogNoshiWindow .name_count").val(result["name_count"]);
						$("#dialogNoshiWindow .ruby_count").val(result["ruby_count"]);
						initNoshiNamingPattern();
					},
					function () {
						alert("熨斗名入れパターン詳細APIの呼び出しに失敗しました。");
					}
				)
			} else {
				initNoshiNamingPattern();
			}
		});
		// 熨斗編集確定
		$(document).on('click', '.action_noshi_setup', function () {
			// チェックはいるか？
			let index = $("#dialogNoshiWindow .index").val();
			let detail_index = $("#dialogNoshiWindow .detail_index").val();
			let basename = "#register_destination_" + index + "_register_detail_" + detail_index + "_";
			// 熨斗ID
			$(basename + 'noshi_id').val($("#dialogNoshiWindow .noshi_id").val());
			// 熨斗詳細ID
			$(basename + 'noshi_detail_id').val($("#dialogNoshiWindow .noshi_detail_id").val());
			// 熨斗種類
			$(basename + 'm_noshi_format_id').val($("#dialogNoshiWindow .m_noshi_format_id").val());
			$(basename + 'm_noshi_format_name').val($("#dialogNoshiWindow .m_noshi_format_id option:selected").text());

			// 名入れパターン
			$(basename + 'm_noshi_naming_pattern_id').val($("#dialogNoshiWindow .m_noshi_naming_pattern_id").val());
			$(basename + 'm_noshi_naming_pattern_name').val($("#dialogNoshiWindow .m_noshi_naming_pattern_id option:selected").text());

			// 表書き
			$(basename + 'omotegaki').val($("#dialogNoshiWindow .omotegaki").val());
			// 貼付/同梱
			$(basename + 'attach_flg').val($("#dialogNoshiWindow select.attach_flg").val());

			// 名入カウント
			$(basename + 'company_name_count').val($("#dialogNoshiWindow .company_name_count").val());
			$(basename + 'section_name_count').val($("#dialogNoshiWindow .section_name_count").val());
			$(basename + 'title_count').val($("#dialogNoshiWindow .title_count").val());
			$(basename + 'f_name_count').val($("#dialogNoshiWindow .f_name_count").val());
			$(basename + 'name_count').val($("#dialogNoshiWindow .name_count").val());
			$(basename + 'ruby_count').val($("#dialogNoshiWindow .ruby_count").val());

			// 入力不可のフィールドは空にする
			$("#dialogNoshiWindow .company_name input:disabled").each(function (){
				$(this).val('');
			});
			$("#dialogNoshiWindow .section_name input:disabled").each(function (){
				$(this).val('');
			});
			$("#dialogNoshiWindow .title input:disabled").each(function (){
				$(this).val('');
			});
			$("#dialogNoshiWindow .firstname input:disabled").each(function (){
				$(this).val('');
			});
			$("#dialogNoshiWindow .name input:disabled").each(function (){
				$(this).val('');
			});
			$("#dialogNoshiWindow .ruby input:disabled").each(function (){
				$(this).val('');
			});
			for(idx=1;idx<=5;idx++){
				$(basename + "company_name"+idx).val($("#dialogNoshiWindow input.company_name"+idx).val());
				$(basename + "company_name"+idx).attr('disabled',$("#dialogNoshiWindow input.company_name"+idx).is(':disabled'));
				$(basename + "section_name"+idx).val($("#dialogNoshiWindow input.section_name"+idx).val());
				$(basename + "section_name"+idx).attr('disabled',$("#dialogNoshiWindow input.section_name"+idx).is(':disabled'));
				$(basename + "title"+idx).val($("#dialogNoshiWindow input.title"+idx).val());
				$(basename + "title"+idx).attr('disabled',$("#dialogNoshiWindow input.title"+idx).is(':disabled'));
				$(basename + "firstname"+idx).val($("#dialogNoshiWindow input.firstname"+idx).val());
				$(basename + "firstname"+idx).attr('disabled',$("#dialogNoshiWindow input.firstname"+idx).is(':disabled'));
				$(basename + "name"+idx).val($("#dialogNoshiWindow input.name"+idx).val());
				$(basename + "name"+idx).attr('disabled',$("#dialogNoshiWindow input.name"+idx).is(':disabled'));
				$(basename + "ruby"+idx).val($("#dialogNoshiWindow input.ruby"+idx).val());
				$(basename + "ruby"+idx).attr('disabled',$("#dialogNoshiWindow input.ruby"+idx).is(':disabled'));
			}
			displayNoshiText(index,detail_index);
			$("#dialogNoshiWindow").dialog("close");
		});
		$(document).on('click', '.action_noshi_setup_cancel', function () {
			$("#dialogNoshiWindow").dialog("close");
		});
		// 付属品編集
		$(document).on('click', '.action_edit_attachment_items', function () {
			let index = $(this).data('index');
			let detail_index = $(this).data('detail_index');
			$('#dialogAttachmentItemWindow .index').val(index);
			$('#dialogAttachmentItemWindow .detail_index').val(detail_index);
			$('#dialogAttachmentItemWindow .attachment_item_detail').remove();
			let basename = "#register_destination_" + index + "_register_detail_" + detail_index + "_";
			$('#dialogAttachmentItemWindow .t_order_dtl_id').val($(basename + 't_order_dtl_id').val());
			
			// 種別を設定
			$('#dialogAttachmentItemWindow .group_id').val($(basename + 'attachment_item_group_id').val());
			
			// 初期化
			$('#dialogAttachmentItemWindow .search_item_cd').val("");
			$('#dialogAttachmentItemWindow .item_name').val("");
			$('#dialogAttachmentItemWindow .vol').val("");

			$('#tabs-' + index + ' .attachment_item_' + index + '_' + detail_index + '.attachment_index').each(function(){
				html = attachment_dialog_item_template;
				let attachmentIndex = $(this).val();
				let basename2 = basename + 'order_dtl_attachment_item_' + attachmentIndex + '_';
				html = html.replaceAll("##t_order_dtl_attachment_item_id##",$.h($(basename2 + 't_order_dtl_attachment_item_id').val()));
				html = html.replaceAll("##t_order_dtl_id##",$.h($(basename2 + 't_order_dtl_id').val()));
				html = html.replaceAll("##display_flg##",$.h($(basename2 + 'display_flg').val()));
				html = html.replaceAll("##m_ami_attachment_item_id##",$.h($(basename2 + 'm_ami_attachment_item_id').val()));
				html = html.replaceAll("##attachment_item_cd##",$.h($(basename2 + 'attachment_item_cd').val()));
				html = html.replaceAll("##attachment_item_name##",$.h($(basename2 + 'attachment_item_name').val()));
				html = html.replaceAll("##attachment_vol##",$.h($(basename2 + 'attachment_vol').val()));
				$('#dialogAttachmentItemWindow .item_detail_first').before(html);
			});
			$("#dialogAttachmentItemWindow").dialog("open");
		});
		// 付属品検索
		$(document).on('click', '.action_attachment_item_search', function () {
			let item_cd = $('#dialogAttachmentItemWindow .search_item_cd').val();
			let vol = $('#dialogAttachmentItemWindow .vol').val();
			let group_id = $('#dialogAttachmentItemWindow .group_id').val();
			if(Number.isInteger(parseInt(vol)) == false){
				vol = 1;
			}
			if(item_cd != ""){
				$.ajax({
					url: '/gfh/order/api/attachment_item/search',
					method: 'GET',
					headers: {
						'Authorization': $('input[name="_token"]').val()
					},
					dataType: 'json',
					data: {
						'item_cd': item_cd,
						'group_id': group_id
					},
					async: true,
    			}).then(
      				function (result) {
						// 付属品が検索できた場合
						html = attachment_dialog_item_template;
						html = html.replaceAll("##display_flg##",1);
						html = html.replaceAll("##t_order_dtl_attachment_item_id##","");
						html = html.replaceAll("##t_order_dtl_id##",$('#dialogAttachmentItemWindow .t_order_dtl_id').val());
						html = html.replaceAll("##m_ami_attachment_item_id##",$.h(result['m_ami_attachment_item_id']));
						html = html.replaceAll("##attachment_item_cd##",$.h(result['attachment_item_cd']));
						html = html.replaceAll("##attachment_item_name##",$.h(result['attachment_item_name']));
						html = html.replaceAll("##attachment_vol##",$.h(vol));
						$('#dialogAttachmentItemWindow .item_detail_first').before(html);
						$('#dialogAttachmentItemWindow .search_item_cd').val("");
						$('#dialogAttachmentItemWindow .vol').val("");
      				},
      				function () {
						// 付属品が検索できないのでダイアログを開く
						$.ajax({
							url: '/gfh/order/api/attachment_item/list',
							method: 'POST',
							data: {
								'page_list_count':{{\Config::get('Common.const.page_limit')}},
								'hidden_next_page_no':1,
								'attachment_item_cd':item_cd,
								'group_id': group_id,
								'_token': $('input[name="_token"]').val()
							},
							success: function(response) {
								$('#dialogWindow .dialog_body').html(response.html);
								$('#dialogWindow').dialog('open');
							},
							error: function(xhr, status, error) {
								alert("付属品検索モーダルAPIの呼び出しに失敗しました。");
							}
						});
      				}
   			 	);
			} else {
				$.ajax({
					url: '/gfh/order/api/attachment_item/list',
					method: 'POST',
					data: {
						'page_list_count':{{\Config::get('Common.const.page_limit')}},
						'hidden_next_page_no':1,
						'group_id': group_id,
						'_token': $('input[name="_token"]').val()
					},
					success: function(response) {
						$('#dialogWindow .dialog_body').html(response.html);
						$('#dialogWindow').dialog('open');
					},
					error: function(xhr, status, error) {
						alert("付属品検索モーダルAPIの呼び出しに失敗しました。");
					}
				});
			}			
		});
		// 付属品選択押下
		$(document).on('click', '.attachment_item_selected_action', function () {
			let id = $(this).data('attachment_item_id');
			let vol = 1;
    		$.ajax({
				url: '/gfh/order/api/attachment_item/'+id,
				method: 'GET',
				headers: {
					'Authorization': $('input[name="_token"]').val()
				},
				dataType: 'json',
				async: true,
    		}).then(
      			function (result) {
					html = attachment_dialog_item_template;
					html = html.replaceAll("##display_flg##",1);
					html = html.replaceAll("##t_order_dtl_attachment_item_id##","");
					html = html.replaceAll("##t_order_dtl_id##",$('#dialogAttachmentItemWindow .t_order_dtl_id').val());
					html = html.replaceAll("##m_ami_attachment_item_id##",$.h(result['m_ami_attachment_item_id']));
					html = html.replaceAll("##attachment_item_cd##",$.h(result['attachment_item_cd']));
					html = html.replaceAll("##attachment_item_name##",$.h(result['attachment_item_name']));
					html = html.replaceAll("##attachment_vol##",$.h(vol));
					$('#dialogAttachmentItemWindow .item_detail_first').before(html);
					$('#dialogWindow').dialog('close');
				},
      			function () {
					alert("付属品詳細APIの呼び出しに失敗しました。");
				}
   			)
		});	
		// 付属品削除押下
		$(document).on('click', '.action_attachment_item_delete', function () {
			$(this).parents('.attachment_item_detail').remove();
		});
		// 付属品編集キャンセル押下
		$(document).on('click', '.action_attachment_item_setup_cancel', function () {
			$("#dialogAttachmentItemWindow").dialog("close");
		});
		// 付属品編集デフォルトに戻す押下
		$(document).on('click', '.action_attachment_item_setup_default', function () {
			let destIndex = $('#dialogAttachmentItemWindow .index').val();
			let detailIndex = $('#dialogAttachmentItemWindow .detail_index').val();
			let basename = '#register_destination_' + destIndex + '_register_detail_' + detailIndex + '_';
			let sell_id = $(basename+'sell_id').val(); // ami_ec_page_id
			let group_id = $(basename+'attachment_item_group_id').val();
			// 商品数量
			let order_sell_vol = $(basename+'order_sell_vol').val();
			$('#dialogAttachmentItemWindow .attachment_item_detail').remove();
			$.ajax({
				url: '/gfh/order/api/ami_page/'+sell_id,
				method: 'GET',
				headers: {
					'Authorization': $('input[name="_token"]').val()
				},
				dataType: 'json',
				async: true,
			}).then(
				function (result) {
					let t_order_dtl_id = $('#register_destination_' + destIndex + '_register_detail_' + detailIndex + '_t_order_dtl_id').val();
					$.each(result.page.page_attachment_item, function(index, value) {
						if (value.group_id == group_id) {
							let html = attachment_dialog_item_template;
							html = html.replaceAll("##t_order_dtl_attachment_item_id##","");
							html = html.replaceAll("##t_order_dtl_id##",t_order_dtl_id);
							html = html.replaceAll("##display_flg##",$.h(value.attachment_item.display_flg));
							html = html.replaceAll("##m_ami_attachment_item_id##",$.h(value.attachment_item.m_ami_attachment_item_id));
							html = html.replaceAll("##attachment_item_cd##",$.h(value.attachment_item.attachment_item_cd));
							html = html.replaceAll("##attachment_item_name##",$.h(value.attachment_item.attachment_item_name));
							// 商品数量＊付属品数量
							html = html.replaceAll("##attachment_vol##",$.h(value.item_vol * order_sell_vol));
							$('#dialogAttachmentItemWindow .item_detail_first').before(html);
						}
					});
				},
				function () {
					alert("商品取得APIの呼び出しに失敗しました。");
				}
			);
		});
		// 付属品編集登録押下
		$(document).on('click', '.action_attachment_item_setup', function () {
			let html_detail = "";
			let attachmentIndex = 0;
			let destIndex = $('#dialogAttachmentItemWindow .index').val();
			let detailIndex = $('#dialogAttachmentItemWindow .detail_index').val();
			$('#dialogAttachmentItemWindow .attachment_item_detail').each(function(){
				let html = attachment_item_template;
				html = html.replaceAll("##index0##",destIndex);
				html = html.replaceAll("##detailindex##",detailIndex);
				html = html.replaceAll("##attachmentIndex##",attachmentIndex);
				html = html.replaceAll("##t_order_dtl_attachment_item_id##",$.h($(this).find('.t_order_dtl_attachment_item_id').val()));
				html = html.replaceAll("##t_order_dtl_id##",$.h($(this).find('.t_order_dtl_id').val()));
				html = html.replaceAll("##display_flg##",$.h($(this).find('.display_flg').val()));
				html = html.replaceAll("##m_ami_attachment_item_id##",$.h($(this).find('.m_ami_attachment_item_id').val()));
				html = html.replaceAll("##attachment_item_cd##",$.h($(this).find('.attachment_item_cd').val()));
				html = html.replaceAll("##attachment_item_name##",$.h($(this).find('.attachment_item_name').val()));
				html = html.replaceAll("##attachment_vol##",$.h($(this).find('.attachment_vol').val()));
				attachmentIndex++;
				html_detail += html;
			});
			$('.attachment_item_'+destIndex+'_'+detailIndex).remove();
			$('#register_destination_' + destIndex + '_register_detail_' + detailIndex + '_attachment_html').after(html_detail);
			displayAttachmentText(destIndex,detailIndex);
			$("#dialogAttachmentItemWindow").dialog("close");
		});

		// 出荷予定日更新イベント
		$('.deli_hope_date_picker').on("dp.change", (e) => { 
			let destIndex = $(e.target).parents(".destination_tab_data").find(".destination_index").val();
			$.delivery_day($('#register_destination_' + destIndex + '_destination_postal').val(),destIndex);
		});
		initDestinationData();
	});
</script>
@include('common.elements.datetime_picker_script')
@push('js')
<script src="{{ esm_internal_asset('js/common/gfh_1207/check_textbyte.js') }}"></script>
<script src="{{ esm_internal_asset('js/order/gfh_1207/NEOSM0211.js') }}"></script>
@endpush
@endsection