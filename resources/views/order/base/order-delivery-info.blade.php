{{-- NEOSM0214:受注照会出荷情報 --}}
{{-- 画面設定 --}}
@php
$ScreenCd='NEOSM0214';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '受注照会出荷情報')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>受注照会出荷情報</li>
@endsection

@section('content')
    @session('messages.error.exception_message')
        <span class="font-FF0000">{{$value}}</span>
    @endsession
	<div class="c-box--full">
		<table class="table table-bordered c-tbl c-tbl--1200 nowrap">
			<tbody>
			<tr>
				<th>顧客ID</th>
				<td class="link-style">
					<a href="{{esm_external_route('cc/cc-customer/info/{customer_id}', ['customer_id' => $delivery->m_cust_id])}}" target="_blank">{{$delivery->m_cust_id}}<i class="fas fa-external-link-alt"></i></a>
				</td>
				<th>配送希望日</th>
				<td>{{$delivery->deli_hope_date}}</td>
			</tr>
			<tr>
				<th>受注ID</th>
				<td class="link-style">
					<a href="{{esm_external_route('order/order/info/{order_id}', ['order_id' => $delivery->t_order_hdr_id])}}" target="_blank">{{$delivery->t_order_hdr_id}}<i class="fas fa-external-link-alt"></i></a>
				</td>
				<th>配送希望時間</th>
				<td>{{$delivery->deli_hope_time_name}}</td>
			</tr>
			<tr>
				<th>配送ID</th>
				<td>
                    {{$delivery->t_delivery_hdr_id}}
                    @if(!empty($delivery->delivery_cancel_date) && $delivery->delivery_cancel_date != '0000-00-00')
                    <font color="red">取消済</font>
                    @endif
                </td>
				<th>出荷予定日</th>
				<td>{{$delivery->deli_plan_date}}</td>
			</tr>
			<tr>
				<th>送付先氏名カナ</th>
				<td>{{$delivery->destination_name_kana}}</td>
				<th>配送方法</th>
				<td>
                    @if($delivery->deliveryType)
                    {{ $delivery->deliveryType->m_delivery_type_name }}
                    @else
                    <div class="error u-mt--xs">
                        配送方法を取得できませんでした。
                    </div>
                    @php(logger()->warning('配送方法を取得できませんでした。', [
                        '出荷ID' => $delivery->t_delivery_hdr_id,
                        '配送方法ID' => $delivery->m_delivery_type_id,
                        ]))
                    @endif
                </td>
			</tr>
			<tr>
				<th>送付先氏名</th>
				<td>{{$delivery->destination_name}}</td>
				<th>出荷指示日</th>
				<td>{{$delivery->deli_instruct_timestamp}}</td>
			</tr>
			<tr>
				<th>郵便番号</th>
				<td>{{$delivery->destination_postal}}</td>
				<th>代引き金額</th>
				<td>{{number_format($delivery->payment_fee)}}</td>
			</tr>
			<tr>
				<th>都道府県</th>
				<td>{{$delivery->destination_address1}}</td>
				<th>ピッキングリスト作成日時</th>
				<td>{{$delivery->order_pick_create_datetime}}</td>
			</tr>
			<tr>
				<th>市区町村</th>
				<td>{{$delivery->destination_address2}}</td>
				<th>納品書作成日時</th>
				<td>{{$delivery->deliveryslip_create_datetime}}</td>
			</tr>
			<tr>
				<th>番地</th>
				<td>{{$delivery->destination_address3}}</td>
				<th>送り状作成日時</th>
				<td>{{$delivery->invoice_create_datetime}}</td>
			</tr>
			<tr>
				<th>建物名</th>
				<td>{{$delivery->destination_address4}}</td>
				<th>出荷確定日</th>
				<td>{{$delivery->deli_decision_date}}</td>
			</tr>
			<tr>
				<th>送り状コメント</th>
				<td>{{$delivery->deli_comment}}</td>
				<th>出荷取消日</th>
				<td>{{$delivery->delivery_cancel_date}}</td>
			</tr>
			<tr>
				<th>ピッキングリストコメント</th>
				<td>{{$delivery->picking_comment}}</td>
				<th>ギフトメッセージ</th>
				<td>{{$delivery->gift_message}}</td>
			</tr>
			<tr>
				<th>納品書コメント１</th>
				<td>{{$delivery->slip_comment1}}</td>
				<th>ギフト包装種類</th>
				<td>{{$delivery->gift_wrapping}}</td>
			</tr>
			<tr>
				<th>納品書コメント２</th>
				<td>{{$delivery->slip_comment2}}</td>
				<th>のしタイプ</th>
				<td>{{$delivery->nosi_type}}</td>
			</tr>
			<tr>
				<th>送り状番号</th>
				<td>
                    @if($delivery->deliveryType)
                        @foreach($delivery->shippingLabels as $shippingLabel)
                            @if(!is_null($shippingLabel->shipping_label_number))
                                <a href="{{$delivery->deliveryType->delivery_tracking_url.$shippingLabel->shipping_label_number}}" target="_blank">{{$shippingLabel->shipping_label_number}}<i class="fas fa-external-link-alt"></i></a>
                            @endif
                        @endforeach
                        @else
                        @foreach($delivery->shippingLabels as $shippingLabel)
                            @if(!is_null($shippingLabel->shipping_label_number))
                                <span>{{$shippingLabel->shipping_label_number}}</span>
                            @endif
                        @endforeach
                        <div class="error u-mt--xs">
                            配送方法を取得できないため、リンクを表示できません。
                        </div>
                        @php(logger()->warning('配送方法を取得できませんでした。', [
                            '出荷ID' => $delivery->t_delivery_hdr_id,
                            '配送方法ID' => $delivery->m_delivery_type_id,
                            ]))
                    @endif
				</td>
				<th>のし名前</th>
				<td>{{$delivery->nosi_name}}</td>
			</tr>
			<tr>
				<th>個口数</th>
				<td>{{$delivery->deli_package_vol}}</td>
				<th></th>
				<td></td>
			</tr>
			</tbody>
		</table>
	</div>
	<div class="c-box--full">
		<table class="table table-bordered c-tbl c-tbl--1200 nowrap">
			<tbody>
			<tr>
				<th>販売コード</th>
				<th>販売名</th>
				<th>受注数</th>
				<th>商品コード</th>
				<th>商品名</th>
				<th>出荷数</th>
			</tr>
			@foreach ($delivery->deliveryDetails as $deliveryDetail)
				@php ($first = true)
				@foreach ($deliveryDetail->deliveryDetailSkus as $deliveryDetailSku)
                <tr>
                    @if ($first == true)
						<td rowspan="{{$deliveryDetail->deliveryDetailSkus->count()}}">
                        @if (empty($authPage))
                            {{$deliveryDetail->sell_cd}}
                        @else
                            <a href="{{esm_external_route('ami/ec-page/shop/edit/{ec_page_id}', ['ec_page_id' => $deliveryDetail->sell_id])}}" target="_blank">{{$deliveryDetail->sell_cd}}<i class="fas fa-external-link-alt"></i></a>
                        @endif
                        </td>
						<td rowspan="{{$deliveryDetail->deliveryDetailSkus->count()}}">{{$deliveryDetail->sell_name}}</td>
						<td rowspan="{{$deliveryDetail->deliveryDetailSkus->count()}}" class="u-right">
                            @isset ($deliveryDetail->order_sell_vol) {{number_format($deliveryDetail->order_sell_vol)}} @endisset
                        </td>
                        @php ($first = false)
                    @endif
                    <td>
                        @if (empty($authItem))
						    {{$sku->sku_cd}}
                        @else
                            <a href="{{esm_external_route('ami/sku/edit/{sku_id}', ['sku_id' => $deliveryDetailSku->item_id])}}" target="_blank">{{$deliveryDetailSku->item_cd}}<i class="fas fa-external-link-alt"></i></a>
                        @endif
                    </td>
					<td>
                        @if($deliveryDetailSku->amiSku)
                            {{$deliveryDetailSku->amiSku->sku_name}}
                        @else
                            <div class="error u-mt--xs">
                                SKU情報を取得できませんでした。
                            </div>
                            @php(logger()->warning('SKU情報を取得できませんでした。', [
                                '出荷詳細SKUID' => $deliveryDetailSku->t_delivery_dtl_sku_id,
                                'SKUID' => $deliveryDetailSku->item_id,
                                ]))
                        @endif
                    </td>
                    <td class="u-right">
						{{number_format($deliveryDetailSku->item_vol)}}
                    </td>
                </tr>
                @endforeach
			@endforeach
			</tbody>
		</table>
	</div>
	<div class="c-box--1200">
        <form method="POST" action="{{route('order.order-delivery.update', [$delivery->t_delivery_hdr_id])}}">
            @csrf
            {{-- <input type="hidden" name="previous_url" value="{{$editRow['previous_url'] ?: ''}}"> --}}
            {{-- <input type="hidden" name="previous_subsys" value="{{$editRow['previous_subsys'] ?: ''}}"> --}}
            {{-- <input type="hidden" name="previous_key" value="{{$editRow['previous_key'] ?: ''}}"> --}}
            <input type="hidden" name="m_cust_id" value="{{$delivery->m_cust_id}}">
            <input type="hidden" name="t_order_hdr_id" value="{{$delivery->t_order_hdr_id}}">
            <input type="hidden" name="t_delivery_hdr_id" value="{{$delivery->t_delivery_hdr_id}}">
            <table class="table table-bordered c-tbl c-tbl--800">
                <tbody>
                <tr>
                    <td>出荷確定日</td>
                    <td>
                        <div class="c-box--218 d-table-cell u-pr--ss">
                            <div class="input-group date date-picker">
                                <input type="text" @class([
                                    'form-control',
                                    'c-box--218',
                                    'error-txtfield' => $errors->has('deli_decision_date')
                                ]) name="deli_decision_date" placeholder="" value="{{$delivery->deli_decision_date}}">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                            @error('deli_decision_date')
                            <div class="error u-mt--xs">
                                {{$message}}
                            </div>
                            @enderror
                        </div>
                        @if (!isset($delivery->deli_decision_date) || strlen($delivery->deli_decision_date) == 0)
                            @if(empty($delivery->delivery_cancel_date) || $delivery->delivery_cancel_date == '0000-00-00')
                                <p class="icon_sy_notice_02">出荷確定日を入力すると出荷済に更新されます</p>
                            @endif
                        @endif
                    </td>
                </tr>
                @foreach($delivery->shippingLabels as $shippingLabel)
                    <tr>
                        <td>送り状番号 {{$loop->iteration}}</td>
                        <td>
                            <div class="d-flex" style="align-items: center;">
                                <input type="text" @class([
                                    'form-control',
                                    'c-box--300',
                                    'error-txtfield' => $errors->has('shipping_label_numbers.' . $shippingLabel->t_shipping_label_id)
                                ]) name="shipping_label_numbers[{{$shippingLabel->t_shipping_label_id}}]" value="{{old('shipping_label_numbers.' . $shippingLabel->t_shipping_label_id)?? $shippingLabel->shipping_label_number}}">
                            </div>
                            @error('shipping_label_numbers.' . $shippingLabel->t_shipping_label_id)
                            <div class="error u-mt--xs">
                                {{$message}}
                            </div>
                            @enderror
                            @error('three_temperature_zone_types.' . $shippingLabel->t_shipping_label_id)
                            <div class="error u-mt--xs">
                                {{$message}}
                            </div>
                            @enderror
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td>個口数</td>
                    <td>
                        <input type="text" @class([
                            'form-control',
                            'c-box--100',
                            'error-txtfield' => $errors->has('deli_package_vol')
                        ]) name="deli_package_vol" value="{{old('deli_package_vol')?? $delivery->deli_package_vol}}">
                        @error('deli_package_vol')
                        <div class="error u-mt--xs">
                            {{$message}}
                        </div>
                        @enderror
                    </td>
                </tr>
                </tbody>
            </table>
            @if (isset($infoRow['previous_url']) && strlen($infoRow['previous_url']) > 0)
            <input class="btn btn-default btn-lg u-mt--sm" type="button" name="back" value="戻る"
                onClick="location.href='{{config('env.app_subsys_url.' . $infoRow['previous_subsys'])}}{{$infoRow['previous_url']}}'" />
            @else
            <input class="btn btn-default btn-lg u-mt--sm" type="button" name="back" value="閉じる" onClick="window.close();" />
            @endif
            &nbsp;&nbsp;
            @if(empty($infoRow['delivery_cancel_date']) || $infoRow['delivery_cancel_date'] == '0000-00-00')
                <input class="btn btn-success btn-lg u-mt--sm" type="submit" name="submit_update" value="出荷情報を更新する"" />
                {{-- @if (!isset($infoRow['deli_decision_date']) || strlen($infoRow['deli_decision_date']) == 0)
                    <input class="btn btn-danger btn-lg u-mt--sm" type="submit" name="submit_cancel" value="出荷取消" />
                @endif --}}
            @endif
        </form>
	</div>
	@include('common.elements.datetime_picker_script')
@endsection
