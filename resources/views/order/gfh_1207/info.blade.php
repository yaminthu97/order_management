{{-- NEOSM0213:受注照会 --}}
{{-- 画面設定 --}}
@php
$ScreenCd='NEOSM0213';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '受注照会')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>受注照会</li>
@endsection

@php
	// アカウントコードを取得
	$esmSessionManager = new App\Services\EsmSessionManager();
	$accountCode = $esmSessionManager->getAccountCode();
@endphp

@section('content')
	<link rel="stylesheet" href="{{config('env.app_subsys_url.order')}}css/Order/v1_0/NEOSM0213.css">
	<form method="POST" action="">
		{{ csrf_field() }}

		<input type="hidden" name="t_order_hdr_id" value="{{ $order->t_order_hdr_id ?? '' }}">
		<input type="hidden" name="ec_order_num" value="{{ $order->ec_order_num ?? '' }}">
		{{-- 倉庫引当ボタンクリック時に対象のSKU_IDがセットされる --}}
		<input type="hidden" name="t_order_dtl_sku_id" id="t_order_dtl_sku_id" value="">

		<div class="c-box--1600">
			@isset( $order->register_message )
				<div class="c-box--full">
					<span class="font-FF0000">{{ $order->register_message ?? '' }}</span>
				</div>
			@endisset
			<div id="line-01"></div>
			<p class="c-ttl--02">ステータス</p>
			<div class="u-mt--xs">
				進捗区分：{{ !empty($order->progress_type_self_change) ? '手動' : '自動' }}
			</div>
			<div class="u-mt--xs">
				<div class="d-inline-block">
					<ol class="stepBar step9">
						<li @class(['step', "current" => $order->progress_type == \App\Enums\ProgressTypeEnum::PendingConfirmation->value]) >確認待</li>
						<li @class(['step', "current" => $order->progress_type == \App\Enums\ProgressTypeEnum::PendingCredit->value]) >与信待</li>
						<li @class(['step', "current" => $order->progress_type == \App\Enums\ProgressTypeEnum::PendingPrepayment->value]) >前払入金待</li>
						<li @class(['step', "current" => $order->progress_type == \App\Enums\ProgressTypeEnum::PendingAllocation->value]) >引当待</li>
						<li @class(['step', "current" => $order->progress_type == \App\Enums\ProgressTypeEnum::PendingShipment->value]) >出荷待</li>
						<li @class(['step', "current" => $order->progress_type == \App\Enums\ProgressTypeEnum::Shipping->value]) >出荷中</li>
						<li @class(['step', "current" => $order->progress_type == \App\Enums\ProgressTypeEnum::Shipped->value]) >出荷済み</li>
						<li @class(['step', "current" => $order->progress_type == \App\Enums\ProgressTypeEnum::PendingPostPayment->value]) >後払入金待</li>
						<li @class(['step', "current" => $order->progress_type == \App\Enums\ProgressTypeEnum::Completed->value]) >完了</li>
					</ol>
				</div>
				<div class="d-inline-block u-ml--ss">
					<ol class="stepBar step2">
						<li @class(['step-nomal', "current" => $order->progress_type == \App\Enums\ProgressTypeEnum::Cancelled->value]) >キャンセル</li>
						<li @class(['step-nomal', "current" => $order->progress_type == \App\Enums\ProgressTypeEnum::Returned->value]) >返品</li>
					</ol>
				</div>
			</div>
			<div class="u-mt--xs">
				<div class="d-inline-block c-box--100">
					変更先進捗区分
				</div>
				<select class="form-control u-input--mid u-mr--xs" name='status_progress_type' @disabled( $order->progress_type == \App\Enums\ProgressTypeEnum::Returned->value )>
					@foreach ($progress_info as $key => $name)
						@if( $key != \App\Enums\ProgressTypeEnum::Cancelled->value && $key != \App\Enums\ProgressTypeEnum::Returned->value )
							<option value="{{ $key }}" @selected( isset($order->progress_type) && ($order->progress_type == $key) )>{{ $name }}</option>
						@endif
					@endforeach
				</select>
				<button type="submit" class="btn btn-success u-mr--xs" name="submit" id="submit_status_progress_edit" value="status_progress_edit" @disabled( $order->progress_type == \App\Enums\ProgressTypeEnum::Returned->value )>進捗区分変更</button>
				@include('common.elements.error_tag', ['name' => 'status_progress_edit'])
				@include('common.elements.error_tag', ['name' => 'status_progress_edit_detail'])

				{{-- 設定済みタグ --}}
				@if( isset($order_tag_master_info) && isset($order->orderTags) && count($order->orderTags) > 0 )
					<div class="u-mt--xs">
						<div class="tag-box c-tbl-border-all">
							@foreach ($order->orderTags as $orderTag)
								@foreach( $order_tag_master_info as $masterTag )
									@if( $orderTag['m_order_tag_id'] != $masterTag['m_order_tag_id'] )
										@continue
									@endif
									<label class="checkbox-inline u-ma--5-10-5-5">
										<input class="u-mt--10" type="checkbox" name="status_delete_tags[]" value="{{ $orderTag['m_order_tag_id'] }}">&nbsp;
										<a class="btn ns-orderTag-style" type="button" style="background:#{{ $masterTag['tag_color'] }}!important;color:#{{ $masterTag['font_color'] }}!important;text-decoration: none;">
											@if( blank($masterTag['deli_stop_flg']) || $masterTag['deli_stop_flg'] < 0 )
												{{ $masterTag['tag_display_name'] }}
											@else
												<u>{{ $masterTag['tag_display_name'] }}</u>
											@endif
										</a>
									</label>
								@endforeach
							@endforeach
						</div><!-- /.tag-box-->
					</div>
				@endif
				@include('common.elements.error_tag', ['name' => 'status_delete_tag'])
			</div>
			<div class="u-mt--xs">
				<div class="d-inline-block c-box--100">
					タグ追加
				</div>
				<select class="form-control u-input--mid u-mr--xs u-mt--xs" name="status_regist_tags[]">
					@isset( $order_tag_master_info )
						@foreach( $order_tag_master_info as $masterTag )
							<option value="{{ $masterTag['m_order_tag_id'] }}">{{ $masterTag['tag_display_name'] }}</option>
						@endforeach
					@endisset
				</select>
				<button type="submit" class="btn btn-success u-mr--xs" name="submit" id="submit_status_regist_tag" value="status_regist_tag">タグ追加</button>
				<button type="submit" class="btn btn-danger" name="submit" id="submit_status_delete_tag" value="status_delete_tag">チェックしたタグを削除</button>
				@include('common.elements.error_tag', ['name' => 'status_regist_tag'])
			</div>

			<table class="table c-tbl c-tbl--1200 c-tbl-border-all u-mt--xs">
				<thead>
					<tr>
						<th class="c-box--200 u-vam">備考</th>
						<td class="c-box--400 u-vam">{!! nl2br(e($order->order_comment ?? '')) !!}</td>
						<th class="c-box--200 u-vam">社内メモ</th>
						<td class="c-box--400 u-vam">
							<textarea class="form-control c-box--400" rows="3" id="operator_comment" name="operator_comment">@isset( $order->orderMemo ){{ $order->orderMemo->operator_comment ?? '' }}@endisset</textarea>
							@include('common.elements.error_tag', ['name' => 'operator_comment'])
						</td>
					</tr>
				</thead>
			</table>

			<table class="c-tbl c-tbl--1200 u-mt--none">
				<thead>
					@if( $order->comment_check_type != \App\Enums\CommentCheckTypeEnum::CONFIRMED->value && $order->comment_check_type != \App\Enums\CommentCheckTypeEnum::EXCLUDED->value )
						<tr>
							<td class="c-box--200 tag-box">
								<button type="submit" class="btn btn-success u-mr--xs" name="submit" id="submit_comment_check" value="comment_check">備考確認済</button>
							</td>
							<td class="c-box--400"></td>
							<td class="c-box--200 tag-box">
								<button type="submit" class="btn btn-success u-mr--xs" name="submit" id="submit_update_operator_comment" value="update_operator_comment">社内メモを更新する</button>
							</td>
							<td class="c-box--400"></td>
						</tr>
					@endif
					<tr>
						<td class="c-box--200 tag-box">
							<div class="u-mt--xs">
								@empty( $order->previous_url )
									<button type="button" class="btn btn-default u-mr--xs closeWindow">閉じる</button>
								@else
									<button type="button" class="btn btn-default u-mr--xs" onClick="location.href='{{ config('env.app_subsys_url.' . $order->previous_subsys) }}{{ $order->previous_url }}'">戻る</button>
								@endempty
								@if( $order->progress_type < \App\Enums\ProgressTypeEnum::Shipping->value )
									<button type="button" class="btn btn-success u-mr--xs" onClick="location.href='{{ route('order.order.edit', ['id' => $order->t_order_hdr_id]) }}'">受注の編集</button>
								@endif
							</div>
						</td>
						<td class="c-box--400"></td>
						<td class="c-box--200">
							@if( $order->comment_check_type == \App\Enums\CommentCheckTypeEnum::CONFIRMED->value || $order->comment_check_type == \App\Enums\CommentCheckTypeEnum::EXCLUDED->value )
								<button type="submit" class="btn btn-success u-mr--xs" name="submit" id="submit_update_operator_comment" value="update_operator_comment">社内メモを更新する</button>
							@endif
						</td>
						<td class="c-box--400"></td>
					</tr>
				</thead>
			</table>
		</div>

		<div class="c-box--1600 u-mt--ms">
			<div id="line-02"></div>
			<p class="c-ttl--02">受注情報</p>
			<div class="d-table c-box--1600">
				<div class="c-box--three_col">
					<table class="table c-tbl c-tbl--520">
						<tr>
							<th class="c-box--200">受注ID</th>
							<td class="c-box--320">
								<a href="">{{ $order->t_order_hdr_id }}</a>
							</td>
						</tr>
						<tr>
							<th>受注日時</th>
							<td>
								@if( !empty( $order->order_datetime ) )
									{{ date( 'Y/m/d H:i', strtotime( $order->order_datetime ) ) }}
								@endif
							</td>
						</tr>
						<tr>
							<th>受注担当者</th>
							<td>
								@isset( $order->entryOperator )
									{{ $order->entryOperator->m_operator_name ?? '' }}
								@endisset
							</td>
						</tr>
						<tr>
							<th>受注方法</th>
							<td>
								@isset( $viewExtendData['order_type_list'] )
									@foreach( $viewExtendData['order_type_list'] as $orderType )
										@if( $order->order_type == $orderType->m_itemname_types_id )
											{{ $orderType->m_itemname_type_name }}
											@break
										@endif
									@endforeach
								@endisset
							</td>
						</tr>
						<tr>
							<th>ECサイト</th>
							<td>
								@isset( $order->ecs )
									{{ $order->ecs->m_ec_name ?? '' }}
								@endisset
							</td>
						</tr>
						<tr>
							<th>ECサイト注文ID</th>
							<td class="link-style">
								@if( isset($order->ecs) && !empty($order->ecs->m_ec_url) && !empty($order->ec_order_num) )
									<a href="{{ $order->ecs->m_ec_url }}{{ $order->ec_order_num  }}" target="_blank">
										{{ $order->ec_order_num }}<i class="fas fa-external-link-alt"></i>
									</a>
								@else
									{{ $order->ec_order_num ?? '' }}
								@endif
							</td>
						</tr>
						<tr>
							<th>販売窓口</th>
							<td>
								@isset( $viewExtendData['m_sales_counter_list'] )
									@foreach( $viewExtendData['m_sales_counter_list'] as $salesStore )
										@if( $order->sales_store == $salesStore->m_itemname_types_id )
											{{ $salesStore->m_itemname_type_name }}
											@break
										@endif
									@endforeach
								@endisset
							</td>
						</tr>
						<tr>
							<th>支払い方法</th>
							<td>
								@isset( $order->paymentTypes )
									{{ $order->paymentTypes->m_payment_types_name ?? '' }}
								@endisset
							</td>
						</tr>
						<tr>
							<th>後払い決済 請求書送付方法</th>
							<td>
								{{ \App\Enums\CbBilledTypeEnum::tryfrom( $order->cb_billed_type )?->label() }}
							</td>
						</tr>
						<tr>
							<th>後払い決済 取引ID</th>
							<td>{{ $order->payment_transaction_id ?? '' }}</td>
						</tr>
						<tr>
							<th>見積</th>
							<td>
								{{ \App\Enums\EstimateFlgEnum::tryfrom( $order->estimate_flg )?->label() }}
							</td>
						</tr>
						<tr>
							<th>領収書</th>
							<td>
								{{ \App\Enums\ReceiptTypeEnum::tryfrom( $order->receipt_type )?->label() }}
							</td>
						</tr>
						<tr>
							<th>領収書宛名</th>
							<td>
								<textarea class="form-control c-box--300" name="receipt_direction" id="receipt_direction" rows="6">{{ $order->receipt_direction ?? '' }}</textarea>
								@include('common.elements.error_tag', ['name' => 'receipt_direction'])
							</td>
						</tr>
						<tr>
							<th>領収書但し書き</th>
							<td>
								<div class="c-box--300 d-flex" style="align-items: center; justify-content: space-between">
									<span>但し</span>
									<input type="text" name="receipt_proviso" id="receipt_proviso" class="form-control" value="{{ $order->receipt_proviso ?? '' }}">
									<span>代として</span>
								</div>
								@include('common.elements.error_tag', ['name' => 'receipt_proviso'])
							</td>
						</tr>
						<tr>
							<td style="padding-left: 0px;">
								<button type="submit" class="btn btn-success" name="submit" id="submit_receipt_direction_and_proviso" value="receipt_direction_and_proviso">領収書宛名・但し書き登録</button>
							</td>
						</tr>
					</table>
				</div>

				<div class="c-box--three_col">
					<table class="table c-tbl c-tbl--520">
						<tr>
							<th class="c-box--200">要注意顧客区分</th>
							<td @class(["c-box--320", "font-FF0000" => ( $order->alert_cust_check_type != \App\Enums\AlertCustCheckTypeEnum::CONFIRMED->value )])>
								{{ \App\Enums\AlertCustCheckTypeEnum::tryfrom( $order->alert_cust_check_type )?->label() }}
							</td>
						</tr>
						<tr>
							<th>住所エラー区分</th>
							<td @class(["font-FF0000" => ( $order->address_check_type != \App\Enums\AddressCheckTypeEnum::CONFIRMED->value )])>
								{{ \App\Enums\AddressCheckTypeEnum::tryfrom( $order->address_check_type )?->label() }}
							</td>
						</tr>
						<tr>
							<th>配達指定日エラー区分</th>
							<td @class(["font-FF0000" => ( $order->deli_hope_date_check_type != \App\Enums\DeliHopeDateCheckTypeEnum::CONFIRMED->value )])>
								{{ \App\Enums\DeliHopeDateCheckTypeEnum::tryfrom( $order->deli_hope_date_check_type )?->label() }}
							</td>
						</tr>
						<tr>
							<th>与信区分</th>
							<td @class(["font-FF0000" => ( $order->credit_type != \App\Enums\CreditTypeEnum::CREDIT_OK->value )])>
								{{ \App\Enums\CreditTypeEnum::tryfrom( $order->credit_type )?->label() }}
							</td>
						</tr>
						<tr>
							<th>入金区分</th>
							<td @class(["font-FF0000" => ( $order->payment_type != \App\Enums\PaymentTypeEnum::PAID->value )])>
								{{ \App\Enums\PaymentTypeEnum::tryfrom( $order->payment_type )?->label() }}
							</td>
						</tr>
						<tr>
							<th>在庫引当区分</th>
							<td @class(["font-FF0000" => ( $order->reservation_type != \App\Enums\ReservationTypeEnum::RESERVED->value )])>
								{{ \App\Enums\ReservationTypeEnum::tryfrom( $order->reservation_type )?->label() }}
							</td>
						</tr>
						<tr>
							<th>出荷指示区分</th>
							<td @class(["font-FF0000" => ( $order->deli_instruct_type != \App\Enums\DeliInstructTypeEnum::INSTRUCTED->value )])>
								{{ \App\Enums\DeliInstructTypeEnum::tryfrom( $order->deli_instruct_type )?->label() }}
							</td>
						</tr>
						<tr>
							<th>出荷確定区分</th>
							<td @class(["font-FF0000" => ( $order->deli_decision_type != \App\Enums\DeliDecisionTypeEnum::DECIDED->value )])>
								{{ \App\Enums\DeliDecisionTypeEnum::tryfrom( $order->deli_decision_type )?->label() }}
							</td>
						</tr>
						<tr>
							<th>決済売上区分</th>
							<td @class(["font-FF0000" => ( $order->settlement_sales_type != \App\Enums\SettlementSalesTypeEnum::RECORDED->value )])>
								{{ \App\Enums\SettlementSalesTypeEnum::tryfrom( $order->settlement_sales_type )?->label() }}
							</td>
						</tr>
						<tr>
							<th>ECステータス区分</th>
							<td @class(["font-FF0000" => ( $order->sales_status_type != \App\Enums\SalesStatusTypeEnum::RECORDED->value )])>
								{{ \App\Enums\SalesStatusTypeEnum::tryfrom( $order->sales_status_type )?->label() }}
							</td>
						</tr>
					</table>
				</div>
				<div class="c-box--three_col">
					<table class="table c-tbl c-tbl--520">
						<tr>
							<th class="c-box--200">即日配送</th>
							<td class="c-box--320">
								@empty( $order->immediately_deli_flg )
									しない
								@else
									する
								@endempty
							</td>
						</tr>
						<tr>
							<th>楽天スーパーDEAL</th>
							<td>
								@empty( $order->rakuten_super_deal_flg )
									利用しない
								@else
									利用する
								@endempty
							</td>
						</tr>
						<tr>
							<th>ギフトフラグ</th>
							<td>
								@empty( $order->gift_flg )
									通常受注
								@else
									ギフト受注
								@endempty
							</td>
						</tr>
						<tr>
							<th>警告注文</th>
							<td>
								@empty( $order->alert_order_flg )
									対象外
								@else
									警告対象
								@endempty
							</td>
						</tr>
						<tr>
							<th>強制出荷</th>
							<td>
								@empty( $order->forced_deli_flg )
									しない
								@else
									する
								@endempty
							</td>
						</tr>
						<tr>
							<th>キャンペーン</th>
							<td>
								{{ \App\Enums\CampaignFlgEnum::tryfrom( $order->campaign_flg )?->label() }}
							</td>
						</tr>
						<tr>
							<th>請求メモ</th>
							<td>
								<textarea class="form-control c-box--300" name="billing_comment" id="billing_comment" rows="6" readonly>@isset( $order->orderMemo ){{ $order->orderMemo->billing_comment ?? '' }}@endisset</textarea>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>

		<div class="c-box--1600 u-mt--ms">
			<div id="line-03"></div>
			<p class="c-ttl--02">注文主情報</p>
			<div class="d-table c-box--1600">
				<div class="c-box--800Half">
					<table class="table c-tbl c-tbl--790">
						<tr>
							<th class="c-box--200">顧客ID</th>
							<td>
								@if( !empty($order->m_cust_id) )
									<a href="{{ route('cc.cc-customer.info', ['id' => $order->m_cust_id]) }}" target="_blank">{{ $order->m_cust_id }}<i class="fas fa-external-link-alt"></i></a>
								@endif
							</td>
						</tr>
						<tr>
							<th>電話番号</th>
							<td>
								@isset( $order->order_tel1 )
									<div>
										{{ $order->order_tel1 ?? '' }}
										<a class="btn btn-success btn-primary" href="callto:{{ $order->order_tel1 }}">発信</a>
									</div>
								@endisset
								@isset( $order->order_tel2 )
									<div @class([ "u-mt--xs" => !empty($order->order_tel1) ])>
										{{ $order->order_tel2 ?? '' }}
										<a class="btn btn-success btn-primary" href="callto:{{ $order->order_tel2 }}">発信</a>
									</div>
								@endisset
							</td>
						</tr>
						<tr>
							<th>FAX番号</th>
							<td>{{ $order->order_fax ?? '' }}</td>
						</tr>
						<tr>
							<th>フリガナ</th>
							<td>{{ $order->order_name_kana ?? '' }}</td>
						</tr>
						<tr>
							<th>名前</th>
							<td>{{ $order->order_name ?? '' }}</td>
						</tr>
						<tr>
							<th>メールアドレス</th>
							<td>
								<div>{{ $order->order_email1 ?? '' }}</div>
								<div>{{ $order->order_email2 ?? '' }}</div>
							</td>
						</tr>
						<tr>
							<th>顧客ランク</th>
							<td>
								@isset( $order_cust_info->custRunk )
									{{ $order_cust_info->custRunk->m_itemname_type_name }}
								@endisset
							</td>
						</tr>
						<tr>
							<th>要注意区分</th>
							<td>
								{{ \App\Enums\AlertCustTypeEnum::tryfrom( $order_cust_info->alert_cust_type ?? 0 )?->label() }}
							</td>
						</tr>
						<tr>
							<th>要注意コメント</th>
							<td>
								{!! nl2br(e( $order_cust_info->alert_cust_comment ?? '' )) !!}
							</td>
						</tr>
					</table>
					<div>
						@if( $order->alert_cust_check_type != \App\Enums\AlertCustCheckTypeEnum::CONFIRMED->value && $order->alert_cust_check_type != \App\Enums\AlertCustCheckTypeEnum::EXCLUDED->value )
							<button type="submit" class="btn btn-success" name="submit" value="alert_cust_check">要注意顧客確認済</button>
						@endif
					</div>
				</div>
				<div class="c-box--800Half">
					<table class="table c-tbl c-tbl--790">
						<tr>
							<th class="c-box--200">郵便番号</th>
							<td>
								@isset( $order->order_postal )
									{{ substr($order->order_postal, 0, 3)}}-{{substr($order->order_postal, 3, 4) }}
								@endisset
							</td>
						</tr>
						<tr>
							<th>都道府県</th>
							<td>{{ $order->order_address1 ?? '' }}</td>
						</tr>
						<tr>
							<th>市区町村</th>
							<td>{{ $order->order_address2 ?? '' }}</td>
						</tr>
						<tr>
							<th>番地</th>
							<td>{{ $order->order_address3 ?? '' }}</td>
						</tr>
						<tr>
							<th>建物名</th>
							<td>{{ $order->order_address4 ?? '' }}</td>
						</tr>
						<tr>
							<th>法人名・団体名</th>
							<td>{{ $order->order_corporate_name ?? '' }}</td>
						</tr>
						<tr>
							<th>部署名</th>
							<td>{{ $order->order_division_name ?? '' }}</td>
						</tr>
						<tr>
							<th>勤務先電話番号</th>
							<td>{{ $order_cust_info->corporate_tel ?? '' }}</td>
						</tr>
						<tr>
							<th>顧客備考</th>
							<td>{!! nl2br(e( $order_cust_info->note ?? '' )) !!}</td>
						</tr>
					</table>
				</div>
			</div>
		</div>

		<div class="c-box--1600 u-mt--ms">
			<div id="line-04"></div>
			<p class="c-ttl--02">請求先情報</p>
			<div class="d-table c-box--1600">
				<div class="c-box--800Half">
					<table class="table c-tbl c-tbl--790">
						<tr>
							<th class="c-box--200">顧客ID</th>
							<td>
								@if( !empty($order->m_cust_id_billing) )
									<a href="{{ route('cc.cc-customer.info', ['id' => $order->m_cust_id_billing]) }}" target="_blank">{{ $order->m_cust_id_billing }}<i class="fas fa-external-link-alt"></i></a>
								@endif
							</td>
						</tr>
						<tr>
							<th>電話番号</th>
							<td>
								<div>{{ $order->billing_tel1 ?? '' }}</div>
								<div>{{ $order->billing_tel2 ?? '' }}</div>
							</td>
						</tr>
						<tr>
							<th>FAX番号</th>
							<td>{{ $order->billing_fax ?? '' }}</td>
						</tr>
						<tr>
							<th>フリガナ</th>
							<td>{{ $order->billing_name_kana ?? '' }}</td>
						</tr>
						<tr>
							<th>名前</th>
							<td>{{ $order->billing_name ?? '' }}</td>
						</tr>
						<tr>
							<th>メールアドレス</th>
							<td>
								<div>{{ $order->billing_email1 ?? '' }}</div>
								<div>{{ $order->billing_email2 ?? '' }}</div>
							</td>
						</tr>
						<tr>
							<th>顧客ランク</th>
							<td>
								@isset( $order->billingCust )
									@isset( $order->billingCust->custRunk )
										{{ $order->billingCust->custRunk->m_itemname_type_name }}
									@endisset
								@endisset
							</td>
						</tr>
						<tr>
							<th>要注意区分</th>
							<td>
								@isset( $order->billingCust )
									{{ \App\Enums\AlertCustTypeEnum::tryfrom( $order->billingCust->alert_cust_type )?->label() }}
								@endisset
							</td>
						</tr>
						<tr>
							<th>要注意コメント</th>
							<td>
								@isset( $order->billingCust )
									{!! nl2br(e( $order->billingCust->alert_cust_comment ?? '' )) !!}
								@endisset
							</td>
						</tr>
					</table>
				</div>
				<div class="c-box--800Half">
					<table class="table c-tbl c-tbl--790">
						<tr>
							<th class="c-box--200">郵便番号</th>
							<td>
								@isset( $order->billing_postal )
									{{ substr($order->billing_postal, 0, 3)}}-{{substr($order->billing_postal, 3, 4) }}
								@endisset
							</td>
						</tr>
						<tr>
							<th>都道府県</th>
							<td>{{ $order->billing_address1 ?? '' }}</td>
						</tr>
						<tr>
							<th>市区町村</th>
							<td>{{ $order->billing_address2 ?? '' }}</td>
						</tr>
						<tr>
							<th>番地</th>
							<td>{{ $order->billing_address3 ?? '' }}</td>
						</tr>
						<tr>
							<th>建物名</th>
							<td>{{ $order->billing_address4 ?? '' }}</td>
						</tr>
						<tr>
							<th>法人名・団体名</th>
							<td>{{ $order->billing_corporate_name ?? '' }}</td>
						</tr>
						<tr>
							<th>部署名</th>
							<td>{{ $order->billing_division_name ?? '' }}</td>
						</tr>
						<tr>
							<th>勤務先電話番号</th>
							<td>
								@if( isset($order->billingCust) && isset($order->billingCust->corporate_tel) )
									{{ $order->billingCust->corporate_tel ?? '' }}
								@endif
							</td>
						</tr>
						<tr>
							<th>顧客備考</th>
							<td>
								@if( isset($order->billingCust) && isset($order->billingCust->note) )
									{!! nl2br( e( $order->billingCust->note ) ) !!}
								@endif
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>

		{{-- 受注明細の販売ページと商品情報の編集ページリンク可否フラグ --}}
		@php 
			$authItem = false; // SKUマスタ編集権限
			$authPage = false; // ECページ編集権限
			$operatorInfo = session()->get('OperatorInfo');
			if( !empty($operatorInfo) && isset($operatorInfo['operation_authority_detail']) ){
				foreach( $operatorInfo['operation_authority_detail'] as $authInfo ){
					if( $authInfo['menu_type'] == \App\Enums\MenuTypeEnum::CLAIM->value && $authInfo['available_flg'] == 1 ){
						$authItem = true;
					}
					if( $authInfo['menu_type'] == \App\Enums\MenuTypeEnum::SHIPPING->value && $authInfo['available_flg'] == 1 ){
						$authPage = true;
					}
				}
			}
			// 
			$ecUri = null;
			if( isset($order->ecs) ){
				$ecUri = \App\Enums\EcSiteInfoEnum::tryfrom( $order->ecs->m_ec_type )?->uri();
			}
			// 履歴欄で帳票出力のパラメータにする配送IDリストを初期化
			$deliHdrIds = [];
		@endphp

		{{-- 送付先・受注明細 --}}
		<div class="u-mt--ms">
			<div id="line-05"></div>
			@isset( $order->orderDestination )
				<div class="collapse in" id="collapse-menu" aria-expanded="true" style="">
					<div id="tabs">
						{{-- タブ部分 --}}
						<div class="c-box--full">
							<ul>
								@foreach( $order->orderDestination as $orderDest )
									<li><a href="#tabs-{{ $orderDest->order_destination_seq }}">{{ $orderDest->destination_name ?? '' }}</a></li>
								@endforeach
							</ul>
						</div>
						{{-- 送付先タブ --}}
						<div class="tabs-inner c-box--1600">
							@foreach( $order->orderDestination as $orderDestIdx => $orderDest )
								@php
									$subtotal = 0; // 送付先単位の小計
								@endphp
								<div id="tabs-{{ $orderDest->order_destination_seq }}" class="destTabs">
									{{-- 送付先情報--}}
									<div class="c-box--1580"><p class="c-ttl--02">送付先情報</p></div>
									<div class="d-table c-box--1580">
										<div class="c-box--790Half">
											<table class="table c-tbl c-tbl--780">
												<tr>
													<th class="c-box--200">電話番号</th>
													<td>
														{{ $orderDest->destination_tel ?? '' }}
														@if( $orderDest->partial_deli_flg == 1 )
															&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
															分割配送
														@endif
													</td>
												</tr>
												<tr>
													<th>フリガナ</th>
													<td>{{ $orderDest->destination_name_kana ?? '' }}</td>
												</tr>
												<tr>
													<th>名前</th>
													<td>{{ $orderDest->destination_name ?? '' }}</td>
												</tr>
												<tr>
													<th>配送方法</th>
													<td>
														@isset( $viewExtendData['delivery_type_list'] )
															@foreach( $viewExtendData['delivery_type_list'] as $deliveryType )
																@if( $orderDest->m_delivery_type_id == $deliveryType->m_delivery_types_id )
																	{{ $deliveryType->m_delivery_type_name }}
																	@break
																@endif
															@endforeach
														@endisset
													</td>
												</tr>
												<tr>
													<th>配送希望日</th>
													<td>
														@if( isset($orderDest->deli_hope_date) && !empty( $orderDest->deli_hope_date ) )
															{{ date('Y/m/d', strtotime($orderDest->deli_hope_date) ) }}
														@endif
													</td>
												</tr>
												<tr>
													<th>配送希望時間帯</th>
													<td>{{ $orderDest->deli_hope_time_name ?? ''}}</td>
												</tr>
												<tr>
													<th>出荷予定日</th>
													<td>
														@if( isset($orderDest->deli_plan_date) && !empty($orderDest->deli_plan_date) )
															{{ date('Y/m/d', strtotime($orderDest->deli_plan_date) ) }}
														@endif
													</td>
												</tr>
												<tr>
													<th>送り状コメント</th>
													<td>
														{{ $orderDest->invoice_comment ?? '' }}
													</td>
												</tr>
												<tr>
													<th>ピッキングコメント</th>
													<td>
														{{ $orderDest->picking_comment ?? '' }}
													</td>
												</tr>
												<tr>
													<th>分割配送する</th>
													<td>{{ $orderDest->partial_deli_flg == 1 ? 'する' : 'しない' }}</td>
												</tr>
												<tr>
													<th>キャンペーン対象</th>
													<td>
														{{ \App\Enums\CampaignFlgEnum::tryfrom( $orderDest->campaign_flg )?->label() }}
													</td>
												</tr>
												<tr>
													<th>出荷保留</th>
													<td>
														{{ $orderDest->pending_flg == 1 ? 'あり' : 'なし' }}
													</td>
												</tr>
												<tr>
													<th>送り主名</th>
													<td>{{ $orderDest->sender_name ?? '' }}</td>
												</tr>
												<tr>
													<th>配送種別</th>
													<td>
														@if( $orderDest->total_deli_flg == 1 )
															同梱出荷
															：{{ \App\Enums\ThreeTemperatureZoneTypeEnum::tryfrom( $orderDest->total_temperature_zone_type ?? null )?->label() }}
														@endif
													</td>
												</tr>
											</table>
										</div>
										<div class="c-box--790Half">
											<table class="table c-tbl c-tbl--780">
												<tr>
													<th class="c-box--200">郵便番号</th>
													<td class="zipcode">
														@isset( $orderDest->destination_postal )
															{{ substr($orderDest->destination_postal, 0, 3)}}-{{ substr($orderDest->destination_postal, 3, 4) }}
														@endisset
													</td>
												</tr>
												<tr>
													<th>フリガナ</th>
													<td class="address1_kana"></td>
												</tr>
												<tr>
													<th>都道府県</th>
													<td>
														{{ $orderDest->destination_address1 ?? '' }}
													</td>
												</tr>
												<tr>
													<th>フリガナ</th>
													<td class="address2_kana"></td>
												</tr>
												<tr>
													<th>市区町村</th>
													<td>{{ $orderDest->destination_address2 ?? '' }}</td>
												</tr>
												<tr>
													<th>番地</th>
													<td>{{ $orderDest->destination_address3 ?? '' }}</td>
												</tr>
												<tr>
													<th>建物名</th>
													<td>{{ $orderDest->destination_address4 ?? '' }}</td>
												</tr>
												<tr>
													<th>法人名・団体名</th>
													<td>{{ $orderDest->destination_company_name ?? ''}}</td>
												</tr>
												<tr>
													<th>部署名</th>
													<td>{{ $orderDest->destination_division_name ?? ''}}</td>
												</tr>
												<tr>
													<th>ギフトメッセージ</th>
													<td>{{ $orderDest->gift_message ?? '' }}</td>
												</tr>
												<tr>
													<th>ギフト包装種類</th>
													<td>{{ $orderDest->gift_wrapping ?? '' }}</td>
												</tr>
												<tr>
													<th>のしタイプ</th>
													<td>{{ $orderDest->nosi_type ?? '' }}</td>
												</tr>
												<tr>
													<th>のし名前</th>
													<td>{{ $orderDest->nosi_name ?? '' }}</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="c-btn--02 u-mt--ss c-box--1580">
										@if( $order->address_check_type != \App\Enums\AddressCheckTypeEnum::CONFIRMED->value && $order->address_check_type != \App\Enums\AddressCheckTypeEnum::EXCLUDED->value )
											<button type="submit" class="btn btn-success" name="submit" value="address_check">住所確認済</button>
										@endif
										@if( $order->deli_hope_date_check_type != \App\Enums\DeliHopeDateCheckTypeEnum::CONFIRMED->value && $order->deli_hope_date_check_type != \App\Enums\DeliHopeDateCheckTypeEnum::EXCLUDED->value )
											<button type="submit" class="btn btn-success" name="submit" value="deli_hope_date_check">配送指定日確認済</button>
										@endif
									</div>
									{{-- 受注明細情報 --}}
									<div class="u-mt--ms">
										<div class="c-box--1580"><p class="c-ttl--02">受注明細情報</p></div>
										@isset( $orderDest->orderDtls )
											@foreach( $orderDest->orderDtls as $orderDtlIdx => $orderDtl )
												@php
													// 販売金額（単価 * 数量)
													$orderPrice = ( $orderDtl->order_sell_price ?? 0 ) * ( $orderDtl->order_sell_vol ?? 0 );
													// 削除以外の場合は小計に加算
													if( empty( $orderDtl->cancel_timestamp ) || str_starts_with( $orderDtl->cancel_timestamp, '0000-00-00' ) ){
														$subtotal += $orderPrice;
													}
												@endphp
												<table class="table table-bordered c-tbl c-tbl--1580 nomargin">
													<tr>
														<td class="c-box--30 text-center">{{ $orderDtlIdx + 1 }}</td>
														<td class="c-box--60 text-center">
															@if( !empty( $orderDtl->cancel_timestamp ) && str_starts_with( $orderDtl->cancel_timestamp, '0000-00-00' ) == false )
																<span class="u-center font-FF0000">削除済</span>
															@endif
														</td>
														<td class="nopadding">
															<table class="table table-bordered c-tbl c-tbl--1490 nomargin">
																<tr>
																	<th class="c-box--250">販売コード</th>
																	<th class="c-box--600">販売名</th>
																	<th class="c-box--100">販売単価</th>
																	<th class="c-box--100">数量</th>
																	<th class="c-box--100">販売金額</th>
																	<th class="c-box--100">在庫状態</th>
																	<th class="c-box--150">クーポンID</th>
																	<th class="c-box--100">クーポン金額</th>
																	<th>種別</th>
																</tr>
																<tr>
																	<td>
																		@if( $authPage && !empty( $ecUri ) )
																			<a href="{{ esm_external_route( '/ami/ec-page/' . $ecUri . '/edit/{id}', ['id' => $orderDtl->sell_id] ) }}" target="_blank">{{ $orderDtl->sell_cd ?? '' }}<i class="fas fa-external-link-alt"></i></a>
																		@else
																			{{ $orderDtl->sell_cd }}
																		@endif
																	</td>
																	<td>{{ $orderDtl->sell_name ?? '' }}</td>
																	<td class="text-right">{{ number_format($orderDtl->order_sell_price) ?? '' }}</td>
																	<td class="text-right">{{ number_format($orderDtl->order_sell_vol) ?? '' }}</td>
																	<td class="text-right">{{ number_format( $orderPrice ) }}</td>
																	<td class="text-center">
																		@php 
																			$statusName = '';
																			if( !empty( $orderDtl->reservation_date ) ){
																				$statusName = '引当済';
																			} else {
																				$statusName = '引当前';
																				if( isset($orderDtl->orderDtlSku) ){
																					foreach( $orderDtl->orderDtlSku as $orderDtlSku ){
																						if( empty( $orderDtlSku->temp_reservation_flg ) ){
																							$statusName = '未引当';
																							break;
																						}
																					}
																				}
																			}
																		@endphp
																		<a style="cursor: pointer"
																			id="drawing_status_{{ $orderDestIdx . '-' . $orderDtlIdx }}" 
																			data-sellcd="{{ $orderDtl->sell_cd ?? '' }}" 
																			data-rowid="{{ $orderDestIdx . '-' . $orderDtlIdx }}"
																			data-href="{{ 
																				config('env.app_subsys_url.order')
																				. 'order/stockinfo/id/'
																				. ( $orderDtl->sell_cd ?? '' )
																				. '/variation/__/itemid/0'
																			}}"
																			style="cursor: pointer"
																		>
																			{{ $statusName }}
																		</a>
																	</td>
																	<td class="text-center">{{ $orderDtl->order_dtl_coupon_id ?? '' }}</td>
																	<td class="text-right">{{ number_format( $orderDtl->order_dtl_coupon_price ?? 0 ) }}</td>
																	<td class="text-center">
																		@isset( $orderDtl->itemGroup )
																			{{ $orderDtl->itemGroup->m_itemname_type_name ?? '' }}
																		@endisset
																	</td>
																</tr>
															</table>
															<table class="table table-bordered c-tbl c-tbl--1490 nomargin">
																<tr>
																	<td class="c-box--250 nopadding" rowspan="2">
																		@if( isset( $orderDtl->amiEcPage ) && isset( $orderDtl->amiEcPage->page ) && !empty( $orderDtl->amiEcPage->page->image_path ) )
																			<div class='item-image-preview'>
																				<img src="{{
																					'/' 
																					. config('filesystems.resources_dir') 
																					. '/' 
																					. $accountCode 
																					. '/image/page/' 
																					. $orderDtl->amiEcPage->page->m_ami_page_id 
																					. '/' 
																					. $orderDtl->amiEcPage->page->image_path
																				}}" class="item_image_preview">
																				<span class="item-image-preview-glass action_item_zoom">
																					<span class="glyphicon glyphicon-zoom-in" aria-hidden="true"></span>
																				</span>
																			</div>
																		@endif
																	</td>
																	<td class="c-box--600 c-box-height--250" rowspan="2">
																		<div class="c-box-height--250 overflow-auto-y">
																			@if( isset( $orderDtl->amiEcPage ) && isset( $orderDtl->amiEcPage->page ) && !empty( $orderDtl->amiEcPage->page->page_desc ) )
																				{!! $orderDtl->amiEcPage->page->page_desc !!}
																			@endif
																		</div>
																	</td>
																	<th class="c-box--100">熨斗</th>
																	<td class="c-box-height--125">
																		<div class="c-box-height--125 overflow-auto-y">
																			@isset( $orderDtl->orderDtlNoshi )
																				<div>
																					@isset( $orderDtl->orderDtlNoshi->noshiDetail )
																						@isset( $orderDtl->orderDtlNoshi->noshiDetail->noshiFormat )
																							<span class="font-757575">熨斗種類：</span>{{ $orderDtl->orderDtlNoshi->noshiDetail->noshiFormat->noshi_format_name ?? '' }}
																						@endisset
																					@endisset
																					@isset( $orderDtl->orderDtlNoshi->noshiNamingPattern )
																						<span class="font-757575">&nbsp;&nbsp;/&nbsp;&nbsp;名入れパターン：</span>{{ $orderDtl->orderDtlNoshi->noshiNamingPattern->pattern_name ?? '' }}
																					@endisset
																					<span class="font-757575">&nbsp;&nbsp;/&nbsp;&nbsp;貼付/同梱：</span>{{ \App\Enums\AttachFlgEnum::tryfrom( $orderDtl->orderDtlNoshi->attach_flg )?->label() }}
																				</div>
																				<div class="u-mt--xs">
																					<span class="font-757575">表書き：</span>{{ $orderDtl->orderDtlNoshi->omotegaki ?? '' }}
																				</div>
																				<div class="u-mt--xs">
																					<span class="font-757575">お名前：</span>
																					@php
																						$names = [];
																						if( !empty( $orderDtl->orderDtlNoshi->name1 ) ) $names[] = $orderDtl->orderDtlNoshi->name1;
																						if( !empty( $orderDtl->orderDtlNoshi->name2 ) ) $names[] = $orderDtl->orderDtlNoshi->name2;
																						if( !empty( $orderDtl->orderDtlNoshi->name3 ) ) $names[] = $orderDtl->orderDtlNoshi->name3;
																						if( !empty( $orderDtl->orderDtlNoshi->name4 ) ) $names[] = $orderDtl->orderDtlNoshi->name4;
																						if( !empty( $orderDtl->orderDtlNoshi->name5 ) ) $names[] = $orderDtl->orderDtlNoshi->name5;
																					@endphp
																					{{ implode("，", $names) }}
																				</div>
																			@endisset
																		</div>
																	</td>
																</tr>
																<tr>
																	<th>付属品</th>
																	<td class="c-box-height--125">
																		<div class="c-box-height--125 overflow-auto-y">
																			@isset( $orderDtl->orderDtlAttachmentItem )
																				@foreach( $orderDtl->orderDtlAttachmentItem as $orderItem )
																					<div class="u-mb--xs">
																						<span class="font-757575">付属品コード：</span>{{ $orderItem->attachment_item_cd ?? '' }}
																						<span class="font-757575">&nbsp;&nbsp;/&nbsp;&nbsp;付属品名：</span>{{ $orderItem->attachment_item_name ?? '' }}
																						<span class="font-757575">&nbsp;&nbsp;/&nbsp;&nbsp;数量：</span>{{ $orderItem->attachment_vol ?? '' }}
																					</div>
																				@endforeach
																			@endisset
																		</div>
																	</td>
																</tr>
															</table>
															<table class="table table-bordered c-tbl c-tbl--1490 nomargin">
																<tr>
																	<th class="c-box--200">商品コード</th>
																	<th class="c-box--320">商品名</th>
																	<th class="c-box--80">数量</th>
																	<th class="c-box--100">引当</th>
																	<th class="c-box--100">引当可能数</th>
																	<th class="c-box--120">倉庫</th>
																	<th class="c-box--100">配送ID</th>
																	<th class="c-box--100">配送区分</th>
																	<th class="c-box--100">出荷確定日</th>
																	<th>送り状番号</th>
																</tr>
																@isset( $orderDtl->orderDtlSku )
																	@foreach( $orderDtl->orderDtlSku as $orderDtlSku )
																		<tr>
																			<td>
																				@isset( $orderDtlSku->amiSku )
																					@if( $authItem )
																						<a href="{{ esm_external_route( '/ami/sku/edit/{id}', ['id' => $orderDtlSku->amiSku->m_ami_sku_id] ) }}" target="_blank">
																							{{ $orderDtlSku->amiSku->sku_cd ?? '' }}
																							<i class="fas fa-external-link-alt"></i>
																						</a>
																					@else
																						{{ $orderDtlSku->amiSku->sku_cd ?? '' }}
																					@endif
																				@endisset
																			</td>
																			<td>
																				@isset( $orderDtlSku->amiSku )
																					{{ $orderDtlSku->amiSku->sku_name ?? '' }}
																				@endisset
																			</td>
																			<td>
																				{{ number_format( $orderDtlSku->item_vol ?? 0 ) }}
																				<input 
																					type="hidden" 
																					id="register_destination_{{ $orderDestIdx }}_register_detail_{{ $orderDtlIdx }}_order_sell_vol" 
																					value="{{ $orderDtlSku->item_vol ?? 0 }}"
																				>
																			</td>
																			<td>
																				@php 
																					$statusName = '';
																					if( !empty( $orderDtlSku->reservation_date ) ){
																						$statusName = '引当済';
																					} else {
																						$statusName = '引当前';
																						if( empty( $orderDtlSku->temp_reservation_flg ) ){
																							$statusName = '引当予定';
																						}
																					}
																				@endphp
																				<a style="cursor: pointer"
																					id="drawing_status_{{ $orderDestIdx . '-' . $orderDtlIdx }}" 
																					data-sellcd="{{ $orderDtl->sell_cd ?? '' }}" 
																					data-rowid="{{ $orderDestIdx . '-' . $orderDtlIdx }}"
																					data-href="{{ 
																						config('env.app_subsys_url.order')
																						. 'order/stockinfo/id/'
																						. ( $orderDtl->sell_cd ?? '' )
																						. '/variation/__/itemid/'
																						. ( $orderDtlSku->item_id ?? '' )
																					}}"
																					style="cursor: pointer"
																				>
																					{{ $statusName }}
																				</a>
																			</td>
																			<td>
																				{{-- 引当可能数 --}}
																				@isset( $viewExtendData['reservation_able_vol'] )
																					{{ number_format( $viewExtendData['reservation_able_vol'] ) }}
																				@endisset
																			</td>
																			<td>
																				@isset( $orderDtlSku->warehouse )
																					{{ $orderDtlSku->warehouse->m_warehouse_name ?? '' }}
																					@empty( $orderDtlSku->reservation_date )
																						@empty( $orderDtl->cancel_operator_id )
																							<button type="button" name="btn_warehouse_change" class="btn btn-success btn-xs" 
																							data-skuid="{{ $orderDtlSku->t_order_dtl_sku_id ?? '' }}" 
																							data-href="{{
																								config('env.app_subsys_url.order')
																								. 'order/drawing/warehouse-change/'
																								. ( $order->t_order_hdr_id ?? '') 
																								. '/'
																								. ( $orderDtlSku->t_order_dtl_sku_id ?? '' )
																							}}">
																							<span class="glyphicon glyphicon-transfer" aria-hidden="true"></span>
																							</button>
																						@endempty
																					@endempty
																				@endisset
																			</td>
																			<td>
																				@if( !empty( $orderDtlSku->t_deli_hdr_id ) )
																					@php $deliHdrIds[] = $orderDtlSku->t_deli_hdr_id; @endphp
																					<a href="{{ route('order.order-delivery.info', ['id' => $orderDtlSku->t_deli_hdr_id]) }}" target="_blank">
																						{{ $orderDtlSku->t_deli_hdr_id ?? ''}}<i class="fas fa-external-link-alt"></i>
																					</a>
																				@endif
																			</td>
																			<td>
																				@if( !empty( $orderDtlSku->deli_decision_date ) )
																					確定済
																				@elseif( !empty( $orderDtlSku->deli_instruct_date ) )
																					指示済
																				@else
																					未指示
																				@endif
																			</td>
																			<td>
																				@if( !empty($orderDtlSku->deli_decision_date) )
																					{{ date('Y/m/d', strtotime($orderDtlSku->deli_decision_date)) }}
																				@endif
																			</td>
																			<td>
																				@isset( $orderDest->deliHdr )
																					@foreach( $orderDest->deliHdr as $deliHdr )
																						@isset( $deliHdr->shippingLabels )
																							@foreach( $deliHdr->shippingLabels as $shippingLabel )
																								@if( $shippingLabel->t_order_dtl_id == $orderDtl->t_order_dtl_id && !empty( $shippingLabel->shipping_label_number ) )
																									@if( isset($orderDest->deliveryType) && !empty($orderDest->deliveryType->delivery_tracking_url) )
																										<a href="{{ $orderDest->deliveryType->delivery_tracking_url ?? '' }}{{ $shippingLabel->shipping_label_number }}" target="_blank">{{ $shippingLabel->shipping_label_number }}<i class="fas fa-external-link-alt"></i></a>&nbsp;
																									@else
																										{{ $shippingLabel->shipping_label_number }}&nbsp;
																									@endif
																								@endif
																							@endforeach
																						@endisset
																					@endforeach
																				@endisset
																			</td>
																		</tr>
																	@endforeach
																@endisset
															</table>
														</td>
													</tr>
												</table>
											@endforeach
										@endisset
										<table class="table table-bordered c-tbl c-tbl--1580 nomargin">
											<tr>
												<td class="text-right"><b>小計</b></td>
												<td class="c-box--150 text-right">
													{{ number_format( $subtotal ) }}
												</td>
											</tr>
											<tr>
												<td class="text-right"><b>送料</b></td>
												<td class="c-box--150 text-right">
													{{ number_format( ( $orderDest->shipping_fee ?? 0 ) ) }}
												</td>
											</tr>
											<tr>
												<td class="text-right"><b>手数料</b></td>
												<td class="c-box--150 text-right">
													{{ number_format( ( $orderDest->payment_fee ?? 0 ) ) }}
												</td>
											</tr>
											<tr>
												<td class="text-right"><b>包装料</b></td>
												<td class="c-box--150 text-right">
													{{ number_format( ( $orderDest->wrapping_fee ?? 0 ) ) }}
												</td>
											</tr>
										</table>
									</div>
								</div>
							@endforeach
							<div class="u-pl--ss u-pb--ss">
								@include('common.elements.error_tag', ['name' => 'order_dtl_drawing'])
								@include('common.elements.error_tag', ['name' => 'order_dtl_forced_deli'])
								<div class="u-mt--xs">
									@if( $order->progress_type < \App\Enums\ProgressTypeEnum::Shipping->value )
										@if( $order->reservation_type != \App\Enums\ReservationTypeEnum::EXCLUDED->value )
											@if( $order->reservation_type != \App\Enums\ReservationTypeEnum::RESERVED->value )
												<button type="submit" class="btn btn-success u-mr--xs" name="submit" value="entry_drawing">在庫引当する</button>
											@endif
											@if( $order->reservation_type != \App\Enums\ReservationTypeEnum::NOT_RESERVED->value )
												<button type="submit" class="btn btn-success u-mr--xs" name="submit" value="cancel_drawing">在庫引当解除する</button>
											@endif
										@endif
										<button type="submit" class="btn btn-success u-mr--xs" name="submit" value="forced_deli">強制出荷</button>
										<button type="submit" class="btn btn-success u-mr--xs" name="submit" value="order_bundle">他注文を同梱する</button>
									@endif
								</div>
							</div>
						</div>
					</div>
				</div>
			@endisset
		</div>

		<div class="c-box--1600 u-mt--ms">
			<div id="line-06"></div>
			<p class="c-ttl--02">金額情報</p>
			<table class="table table-bordered c-tbl c-tbl--1580 u-mt--xs">
				<tbody>
					<tr>
						<th class="c-box--110">商品金額計</th>
						<td class="u-right c-box--110">
							{{ number_format( $order->sell_total_price ?? 0 ) }}
						</td>
						<th class="c-box--110">消費税(8%)</th>
						<td class="u-right c-box--110">
							{{ number_format( $order->reduce_tax_price ?? 0 ) }}
						</td>
						<th class="c-box--110">消費税(10%)</th>
						<td class="u-right c-box--110">
							{{ number_format( $order->standard_tax_price ?? 0 ) }}
						</td>
						<th class="c-box--110">送料</th>
						<td class="u-right c-box--110">
							{{ number_format( $order->shipping_fee ?? 0 ) }}
						</td>
						<th class="c-box--110">手数料</th>
						<td class="u-right c-box--110">
							{{ number_format( $order->payment_fee ?? 0 ) }}
						</td>
						<th class="c-box--110">梱包料</th>
						<td class="u-right c-box--110">
							{{ number_format( $order->package_fee ?? 0 ) }}
						</td>
						<th class="c-box--110">合計金額</th>
						<td class="u-right">
							{{ number_format(
                                ( $order->sell_total_price ?? 0 )
                                + ( $order->reduce_tax_price ?? 0 )
                                + ( $order->standard_tax_price ?? 0 )
                                + ( $order->shipping_fee ?? 0 )
                                + ( $order->payment_fee ?? 0 )
                                + ( $order->package_fee ?? 0 )								
							) }}
						</td>
					</tr>
					<tr>
						<th>割引金額</th>
						<td class="u-right">
							@isset( $order->discount )
								<span class="font-FF0000">{{ number_format( ( $order->discount ?? 0 ) * -1 ) }}</span>
							@endisset
						</td>
						<th>ストアクーポン</th>
						<td class="u-right">
							@isset( $order->use_coupon_store )
								<span class="font-FF0000">{{ number_format( ( $order->use_coupon_store ?? 0 ) * -1 ) }}</span>
							@endisset
						</td>
						<th>モールクーポン</th>
						<td class="u-right">
							@isset( $order->use_coupon_mall )
								<span class="font-FF0000">{{ number_format( ( $order->use_coupon_mall ?? 0 ) * -1 ) }}</span>
							@endisset
						</td>
						<th>クーポン合計</th>
						<td class="u-right">
							@isset( $order->total_use_coupon )
								<span class="font-FF0000">{{ number_format( ( $order->total_use_coupon ?? 0 ) * -1 ) }}</span>
							@endisset
						</td>
						<th>利用ポイント</th>
						<td class="u-right">
							@isset( $order->use_point )
								<span class="font-FF0000">{{ number_format( ( $order->use_point ?? 0 ) * -1 ) }}</span>
							@endisset
						</td>
						<th colspan="2">請求金額</th>
						<td colspan="2" class="u-right">
							@isset( $order->order_total_price )
								<b>{{ number_format( $order->order_total_price ) }}</b>
							@endisset
						</td>
					</tr>
				</tbody>
			</table>
		</div>	

		<div class="c-box--1600 u-mt--ms">
			<div id="line-07"></div>
			<p class="c-ttl--02">受注操作</p>
			@include('common.elements.error_tag', ['name' => 'order_operation_error'])
			<div class="u-mt--xs">
				<div class="d-table">
					<div class="d-table-cell u-pr--sl">
						@empty( $order->previous_url )
							<button type="button" class="btn btn-default closeWindow">閉じる</button>
						@else
							<button type="button" class="btn btn-default" onClick="location.href='{{ config('env.app_subsys_url.' . $order->previous_subsys) }}{{ $order->previous_url }}'">戻る</button>
						@endempty
					</div>
					@if( $order->progress_type < \App\Enums\ProgressTypeEnum::Shipping->value )
						<div class="d-table-cell c-box--100 u-pr--mm">
							<button type="button" class="btn btn-success" id="edit_order" onClick="location.href='{{ route('order.order.edit', ['id' => ( $order->t_order_hdr_id ?? '' )]) }}'">受注の編集</button>
						</div>
					@endif
					@if( $order->progress_type > \App\Enums\ProgressTypeEnum::Shipping->value && $order->progress_type < \App\Enums\ProgressTypeEnum::Cancelled->value )
						<div class="d-table-cell c-box--100 u-pr--mm">
							<button type="submit" class="btn btn-danger" name="submit" value="order_return" formtarget="_blank">返品登録</button>
						</div>
					@endif
					@if( $order->progress_type < \App\Enums\ProgressTypeEnum::Shipping->value )
						<div class="d-table-cell u-pr--ss">キャンセル理由</div>
						<div class="d-table-cell u-pr--sl">
							<select class="form-control u-input--mid c-box--300" name="cancel_type" id="cancel_type">
								@foreach ($cancel_type_info as $cancelType)
									<option value="{{ $cancelType['m_itemname_types_id'] }}">{{ $cancelType['m_itemname_type_name'] }}</option>
								@endforeach
							</select>
						</div>

						<div class="d-table-cell u-pr--ss">キャンセル備考</div>
						<div class="d-table-cell u-pr--sl">
							<input class="form-control u-input--mid c-box--650" type="text" name="cancel_note" id="cancel_note">
						</div>

						<div class="d-table-cell">
							<button type="submit" class="btn btn-danger" name="submit" value="order_cancel">受注をキャンセルする</button>
						</div>
					@endif
				</div>
			</div>
			@include('common.elements.error_tag', ['name' => 'cancel_type'])
			@include('common.elements.error_tag', ['name' => 'cancel_note'])
		</div>

		{{-- 各種履歴と操作 --}}
		<div class="c-box--1600 u-mt--ms">
			<div id="line-08"></div>
			<p class="c-ttl--02">各種履歴と操作</p>
			@include('common.elements.error_tag', ['name' => 'history_progress_edit'])
			@include('common.elements.error_tag', ['name' => 'history_delete_tag'])
			@include('common.elements.error_tag', ['name' => 'history_regist_tag'])
			@include('common.elements.error_tag', ['name' => 'settlement_history_error'])
			@include('common.elements.error_tag', ['name' => 'payment_history_error'])
			@include('common.elements.error_tag', ['name' => 'report_history_report_error'])
			@include('common.elements.error_tag', ['name' => 'report_history_delivery_error'])
			<div id="tabs2" class="u-mt--xs">
				<div class="c-box--full">
					<ul>
						<li><a href="#tabs-1">対応履歴</a></li>
						<li><a href="#tabs-2">進捗区分変更履歴</a></li>
						<li><a href="#tabs-3">タグ変更履歴</a></li>
						<li><a href="#tabs-4">決済履歴</a></li>
						<li><a href="#tabs-5">入金履歴</a></li>
						<li><a href="#tabs-6">データ・帳票出力履歴</a></li>
						<li><a href="#tabs-7">ECサイト連携履歴</a></li>
						<li><a href="#tabs-8">メール送信履歴</a></li>
					</ul>
				</div>
				<div class="tabs-inner">
					{{-- tabs-1 対応履歴 --}}
					<div id="tabs-1">
						<div class="c-box--1580">
							<table class="table table-bordered c-tbl c-tbl--1570">
								<tr>
									<th class="nowrap c-tbl--70">連絡方法</th>
									<th class="nowrap c-tbl--150">タイトル</th>
									<th class="nowrap c-tbl--90">ステータス</th>
									<th class="nowrap c-tbl--60">分類</th>
									<th class="nowrap c-tbl--250">受信内容</th>
									<th class="nowrap c-tbl--140">最新受信日時</th>
									<th class="nowrap c-tbl--140">初回受信日時</th>
									<th class="nowrap c-tbl--80">受信者</th>
									<th class="nowrap c-tbl--250">回答内容</th>
									<th class="nowrap c-tbl--140">回答日時</th>
									<th class="nowrap c-tbl--80">回答者</th>
									<th class="nowrap">エスカレーション</th>
								</tr>
								@isset( $communication_history )
									@foreach( $communication_history as $history )
										<tr>
											<td style="height:5em">
												{{ $viewExtendData['customer_contact'][$history->contact_way_type] ?? '' }}
											</td>
											<td>
												<a href="{{ route('cc.customer-history.edit', ['id' => $history->t_cust_communication_id]) }}" target="_blank">
													{{ $history->title }}
													<i class="fas fa-external-link-alt"></i>
												</a>
											</td>
											<td>{{ $viewExtendData['customer_support'][$history->status] ?? '' }}</td>
											<td>{{ $viewExtendData['customer_support_type'][$history->category] ?? '' }}</td>
											<td>
												<div class="communication_detail">
													{!! nl2br(e($history->receive_detail)) !!}
												</div>
											</td>
											<td>
												@if( isset($history->receive_datetime) && !empty($history->receive_datetime) )
													{{ date('Y/m/d H:i:s', strtotime($history->receive_datetime)) }}
												@endif
											</td>
											<td>
												{{ date('Y/m/d H:i:s', strtotime($history->entry_timestamp)) }}
											</td>
											<td>
												@isset( $history->receiveOperator )
													{{ $history->receiveOperator->m_operator_name }}
												@endisset
											</td>
											<td>
												<div class="communication_detail">
													{!! nl2br(e($history->answer_detail)) !!}
												</div>
											</td>
											<td>
												@if( isset($history->answer_datetime) && !empty($history->answer_datetime) )
													{{ date('Y/m/d H:i:s', strtotime($history->answer_datetime)) }}
												@endif
											</td>
											<td>
												@isset( $history->answerOperator )
													{{ $history->answerOperator->m_operator_name }}
												@endisset
											</td>
											<td>
												@isset( $history->escalationOperator )
													{{ $history->escalationOperator->m_operator_name }}
												@endisset
											</td>
										</tr>
									@endforeach
								@endisset
							</table>
							<button type="submit" class="btn btn-success" name="submit" value="customer_history_new">対応履歴の登録</button>
						</div>
					</div>
					{{-- tabs-2 進捗区分変更履歴 --}}
					<div id="tabs-2">
						<div class="c-box--1580">
							<table class="table table-bordered c-tbl c-tbl--800">
								<tr>
									<th class="nowrap c-tbl--200">変更元</th>
									<th class="nowrap c-tbl--200">変更先</th>
									<th class="nowrap c-tbl--200">変更者</th>
									<th class="nowrap c-tbl--200">変更日時</th>
								</tr>
								@isset ($order_hdr_history )
									@foreach ($order_hdr_history as $history)
										<tr>
											<td>
												{{ \App\Enums\ProgressTypeEnum::tryfrom( $history->progress_type_from )?->label() }}
											</td>
											<td>
												{{ \App\Enums\ProgressTypeEnum::tryfrom( $history->progress_type_to )?->label() }}
											</td>
											<td>
												@isset( $history->entryOperator )
													{{ $history->entryOperator->m_operator_name }}
												@endisset
											</td>
											<td>
												@isset( $history->entry_timestamp )
													{{ date('Y/m/d H:i:s', strtotime($history->entry_timestamp)) }}
												@endisset
											</td>
										</tr>
									@endforeach
								@endisset
							</table>
							変更先進捗区分
							<select class="form-control u-input--mid u-mr--xs u-mt--xs" name='history_progress_type'>
								@foreach ($progress_info as $key => $name)
									<option value="{{ $key }}" @selected( isset($order->progress_type) && ($order->progress_type == $key) )>{{ $name }}</option>
								@endforeach
							</select>
							<button type="submit" class="btn btn-success u-mr--xs" name="submit" id="submit_history_progress_edit" value="history_progress_edit">進捗区分変更</button>
						</div>
					</div>
					{{-- tabs-3 タグ変更履歴 --}}
					<div id="tabs-3">
						<div class="c-box--1580">
							<div class="u-mt--xs">
								<table class="table table-bordered c-tbl c-tbl--1000">
									<tr>
										<th>タグ名称</th>
										<th class="nowrap c-tbl--200">付与日</th>
										<th class="nowrap c-tbl--200">削除日</th>
									</tr>
									@isset ($order_tag_history)
										@foreach ($order_tag_history as $history)
											@foreach( $order_tag_master_info as $masterTag )
												@if( $masterTag['m_order_tag_id'] != $history->m_order_tag_id )
													@continue
												@endif
												<tr>
													<td>
														<span 
															class="ns-orderTag-style" 
															style="background:#{{ $masterTag['tag_color'] }}!important;color:#{{ $masterTag['font_color'] }}!important;text-decoration: none;">
															{{ $masterTag['tag_display_name'] ?? '' }}
														</span>
													</td>
													<td>
														@if( !empty($history->entry_timestamp) )
															{{ date('Y/m/d H:i:s', strtotime($history->entry_timestamp) ) }}
														@endif
													</td>
													<td>
														@if( strpos( $history->cancel_timestamp, '00' ) != 0 )
															{{ date('Y/m/d H:i:s', strtotime($history->cancel_timestamp) ) }}
														@endif
													</td>
												</tr>
											@endforeach
										@endforeach
									@endisset
								</table>
							</div>
							@if( isset($order_tag_master_info) && isset($order->orderTags) && count($order->orderTags) > 0 )
								<div class="u-mt--xs">
									<div class="tag-box c-tbl-border-all">
										@foreach( $order->orderTags as $orderTag )
											@foreach( $order_tag_master_info as $masterTag )
												@if( $orderTag->m_order_tag_id != $masterTag['m_order_tag_id'] )
													@continue
												@endif
												<label class="checkbox-inline u-ma--5-10-5-5">
													<input class="u-mt--10" type="checkbox" name="history_delete_tags[]" value="{{ $orderTag->m_order_tag_id }}">&nbsp;
													<a class="btn ns-orderTag-style" type="button" style="background:#{{ $masterTag['tag_color'] }}!important;color:#{{ $masterTag['font_color'] }}!important;text-decoration: none;">
														@if( blank( $masterTag['deli_stop_flg']) || $masterTag['deli_stop_flg'] < 0 )
															{{ $masterTag['tag_display_name'] }}
														@else
															<u>{{ $masterTag['tag_display_name'] }}</u>
														@endif
													</a>
												</label>
											@endforeach
										@endforeach
									</div>
								</div><!-- /.tag-box-->
							@endif
							<div class="u-mt--xs">
								<div class="d-inline-block c-box--100">
									タグ追加
								</div>
								<select class="form-control u-input--mid u-mr--xs u-mt--xs" name="history_regist_tags[]">
									@isset( $order_tag_master_info )
										@foreach( $order_tag_master_info as $tag )
											<option value="{{ $tag['m_order_tag_id'] }}">{{ $tag['tag_display_name'] }}</option>
										@endforeach
									@endisset
								</select>
								<button type="submit" class="btn btn-success u-mr--xs" name="submit" value="history_regist_tag">タグ追加</button>
								<button type="submit" class="btn btn-danger" name="submit" value="history_delete_tag">チェックしたタグを削除</button>
							</div>
						</div>
					</div>
					{{-- tabs-4 決済履歴 --}}
					<div id="tabs-4">
						<div class="c-box--1580">
							<table class="table table-bordered c-tbl c-tbl--1570">
								<tr>
									<th>決済先</th>
									<th>連携方法</th>
									<th>タイトル</th>
									<th>備考</th>
									<th class="c-tbl--140">完了日時</th>
									<th>エラーコード</th>
									<th>エラーメッセージ</th>
								</tr>
								@isset( $settlement_history )
									@foreach( $settlement_history as $history)
										<tr>
											<td>{{ $history->settlement_target ?? '' }}</td>
											<td>{{ $history->settlement_type ?? '' }}</td>
											<td>{{ $history->settlement_title ?? '' }}</td>
											<td>{{ $history->settlement_note ?? '' }}</td>
											<td>
												@if( !empty( $history->settlement_timestamp ) )
													{{ date( 'Y/m/d H:i:s', strtotime( $history->settlement_timestamp ) ) }}
												@endif
											</td>
											<td>{{ $history->settlement_error_cd ?? '' }}</td>
											<td>{{ $history->settlement_error_message ?? '' }}</td>
										</tr>
									@endforeach
								@endisset
							</table>
							@if( $order->credit_type != \App\Enums\CreditTypeEnum::CREDIT_OK->value && $order->credit_type != \App\Enums\CreditTypeEnum::EXCLUDED->value )
								<div class="d-table u-mt--ss">
									<div class="d-table-cell">
										<button type="submit" class="btn btn-success u-mr--xs" name="submit" value="credit_check">与信OKにする（非連携）</button>
									</div><!-- d-table-cell -->
								</div><!-- d-table -->
							@endif
							@if( $order->progress_type > \App\Enums\ProgressTypeEnum::Shipping->value )
								<div class="d-table u-mt--ss">
									<div class="d-table-cell">
										<button type="submit" class="btn btn-success u-mr--xs" name="submit" value="regist_sales">手動売上登録</button>
									</div><!-- d-table-cell -->
								</div><!-- d-table -->
								<div class="d-table u-mt--ss">
									<div class="d-table-cell">
										<button type="submit" class="btn btn-danger u-mr--xs" name="submit" value="cancel_sales">手動売上取消（返品）</button>
									</div><!-- d-table-cell -->
									<div class="d-table-cell">取消理由</div>
									<div class="d-table-cell u-pr--ss">
										<select class="form-control u-input--mid u-mr--xs" name="sales_cancel_type" id="sales_cancel_type">
											@foreach( $cancel_type_info as $cancelType )
												<option value="{{ $cancelType['m_itemname_types_id'] }}">{{ $cancelType['m_itemname_type_name'] }}</option>
											@endforeach
										</select>
									</div>
									<div class="d-table-cell">取消備考</div>
									<div class="d-table-cell u-pr--ss">
										<input class="form-control u-input--mid c-box--500" type="text" name="sales_cancel_note" id="sales_cancel_note">
									</div>
								</div><!-- d-table -->
								@include('common.elements.error_tag', ['name' => 'sales_cancel_type'])
								@include('common.elements.error_tag', ['name' => 'sales_cancel_note'])
							@endif
						</div>
					</div>
					{{-- tabs-5 入金履歴 --}}
					<div id="tabs-5">
						<div class="c-box--1580">
							<table class="table table-bordered c-tbl c-tbl--1570">
								<tr>
									<th class="nowrap c-tbl--140">入金科目</th>
									<th class="nowrap c-tbl--140">入金登録日</th>
									<th class="nowrap c-tbl--140">顧客入金日</th>
									<th class="nowrap c-tbl--140">口座入金日</th>
									<th class="nowrap c-tbl--200">入金額</th>
									<th>入金メモ</th>
								</tr>
								@php $billBalance = ( $order->order_total_price ?? 0 );	@endphp
								@isset( $payment_history )
									@foreach( $payment_history as $history )
										@php $billBalance -= ( $history->payment_price ?? 0 ); @endphp
										<tr>
											<td>
												@isset( $viewExtendData['payment_paytype_list'] )
													@foreach( $viewExtendData['payment_paytype_list'] as $subject)
														@if( $subject['m_itemname_types_id'] == $history->payment_subject )
															{{ $subject['m_itemname_type_name'] }}
															@break;
														@endif
													@endforeach
												@endisset
											</td>
											<td>
												@isset( $history->payment_entry_date )
													{{ date('Y/m/d', strtotime( $history->payment_entry_date ) ) }}
												@endisset
											</td>
											<td>
												@isset( $history->cust_payment_date )
													{{ date('Y/m/d', strtotime( $history->cust_payment_date ) ) }}
												@endisset
											</td>
											<td>
												@isset( $history->account_payment_date )
													{{ date('Y/m/d', strtotime( $history->account_payment_date ) ) }}
												@endisset
											</td>
											<td class="u-right">
												@isset( $history->payment_price )
													{{ number_format( $history->payment_price ) }}
												@endisset
											</td>
											<td>{{ $history->payment_comment ?? '' }}</td>
										</tr>
									@endforeach
								@endisset
							</table>
							<div class="d-table u-mt--ss">
								<div class="d-table-cell u-pr--ss">入金登録</div>
								<div class="d-table-cell u-pr--ms">
									<select class="form-control" name="payment_subject_id">
										@isset( $viewExtendData['payment_paytype_list'] )
											@foreach( $viewExtendData['payment_paytype_list'] as $subject)
												<option value="{{ $subject['m_itemname_types_id'] }}">{{ $subject['m_itemname_type_name'] }}</option>
											@endforeach
										@endisset
									</select>
								</div>
								<div class="d-table-cell u-pr--ss">顧客入金日</div>
								<div class="d-table-cell u-pr--ms">
									<div class='d-table-cell u-pr--ss'>
										<div class='input-group date date-picker'>
											<input type='text' class="form-control c-box--100" value="" name="cust_payment_date">
											<span class="input-group-addon">
												<span class="glyphicon glyphicon-calendar"></span>
											</span>
										</div>
									</div>
								</div>
								<div class="d-table-cell u-pr--ss">口座入金日</div>
								<div class="d-table-cell u-pr--ms">
									<div class='d-table-cell u-pr--ss'>
										<div class='input-group date date-picker'>
											<input type='text' class="form-control c-box--100" value="" name="account_payment_date">
											<span class="input-group-addon">
												<span class="glyphicon glyphicon-calendar"></span>
											</span>
										</div>
									</div>
								</div>
								<div class="d-table-cell u-pr--ss">入金額</div>
								<div class="d-table-cell">
									<input class="form-control c-box--120 u-right" type="text" maxlength="9" name="payment_price" value="">
								</div>
								<div class="d-table-cell u-pr--ms">
									<button type="button" class="btn btn-info" id="setPaymentPrice">全額</button>
								</div>
								<div class="d-table-cell">
									<button type="submit" class="btn btn-success" name="submit" value="payment">入金登録</button>
								</div>
								<input type="hidden" name="bill_balance" value="{{ $billBalance > 0 ? $billBalance : '' }}">
							</div><!-- d-table -->
							@include('common.elements.error_tag', ['name' => 'cust_payment_date'])
							@include('common.elements.error_tag', ['name' => 'account_payment_date'])
							@include('common.elements.error_tag', ['name' => 'payment_price'])
						</div>
					</div>
					{{-- tabs-6 データ・帳票出力履歴 --}}
					<div id="tabs-6">
						<div class="c-box--1580">
							<table class="table table-bordered c-tbl c-tbl--1570">
								<tr>
									<th class="nowrap c-tbl--250">帳票・データ出力日時</th>
									<th>帳票・データ名</th>
									<th class="c-box--100">配送ID</th>
									<th>出力者</th>
								</tr>
								@isset( $report_history )
									@foreach( $report_history as $history)
										<tr>
											<td>
												@isset( $history->output_timestamp )
													{{ date('Y/m/d H:i:s', strtotime( $history->output_timestamp ) ) }}
												@endisset
											</td>
											<td>{{ $history->report_type ?? '' }}</td>
											<td>
												@if( !empty( $history->t_deli_hdr_id ) )
													<a href="{{ route('order.order-delivery.info', ['id' => $history->t_deli_hdr_id]) }}" target="_blank">
														{{ $history->t_deli_hdr_id }}
														<i class="fas fa-external-link-alt"></i>
													</a>
												@endif
											</td>
											<td>
												@isset( $history->entryOperator )
													{{ $history->entryOperator->m_operator_name ?? '' }}
												@endisset
											</td>
										</tr>
									@endforeach
								@endisset
							</table>
							<div class="u-mt--xs d-table">
								<div class="d-table-cell c-box--130">帳票出力</div>
								<div class="d-table-cell c-box--250">
									<select class="form-control c-box--220" name="output_queue_report" id="output_queue_report">
										@foreach( $output_queue_report as $key => $name )
											<option value="{{ $key }}">{{ $name }}</option>
										@endforeach
									</select>
								</div>
								<div class="d-table-cell c-box--130">
									<input type="hidden" name="queue_report_ids" value="{{ implode(',', $deliHdrIds) }}">
									<button type="submit" class="btn btn-info" name="submit" value="queue_report">出力</button>
								</div>
								<div class="d-table-cell c-box--130">出荷データ形式</div>
								<div class="d-table-cell c-box--250">
									<select class="form-control c-box--220" name="output_queue_delivery">
										@foreach( $output_queue_delivery as $invSys )
											@empty( $invSys['use_m_account_id'] )
												<option value="{{ $invSys['invoice_system_cd'] }}">{{ $invSys['invoice_system_name'] }}</option>
											@endempty
										@endforeach
									</select>
								</div>
								<div class="d-table-cell c-box--130">
									<input type="hidden" name="queue_delivery_ids" value="{{ implode(',', $deliHdrIds) }}">
									<p class="d-table-cell">
										<button type="submit" class="btn btn-info" name="submit" value="queue_delivery">出力</button>
									</p>
								</div>
							</div>
						</div>
					</div>
					{{-- tabs-7 ECサイト連携履歴 --}}
					<div id="tabs-7">
						<div class="c-box--1580">
							<table class="table table-bordered c-tbl c-tbl--1580">
								<tr>
									<th>連携種類</th>
									<th>結果</th>
									<th class="nowrap c-tbl--140">処理日時</th>
									<th>エラーコード</th>
									<th>エラーメッセージ</th>
								</tr>
								@isset( $cooper_history )
									@foreach( $cooper_history as $history)
										<tr>
											<td>{{ $history->cooperation_title ?? '' }}</td>
											<td>{{ $history->cooperation_note ?? '' }}</td>
											<td>
												@isset( $history->cooperation_timestamp )
													{{ date('Y/m/d H:i:s', strtotime( $history->cooperation_timestamp ) ) }}
												@endisset
											<td>{{ $history->cooperation_error_cd ?? '' }}</td>
											<td>{{ $history->cooperation_error_message ?? '' }}</td>
										</tr>
									@endforeach
								@endisset
							</table>
						</div>
					</div>
					{{-- tabs-8 メール送信履歴 --}}
					<div id="tabs-8">
						<table class="table table-bordered c-tbl c-tbl--1580">
							<tr>
								<th class="nowrap c-tbl--140">送信日時</th>
								<th>タイトル</th>
								<th class="c-tbl--600">本文</th>
								<th>送信者</th>
								<th>送信状況</th>
							</tr>
							@isset( $mail_history )
								@foreach( $mail_history as $history)
									<tr>
										<td>
											@isset( $history->mail_send_timestamp )
												{{ date('Y/m/d H:i:s', strtotime( $history->mail_send_timestamp ) ) }}
											@endisset
										</td>
										<td>
											<a href="{{ esm_external_route( '/cc/cc-customer-mail/info/{id}', ['id' => $history->t_mail_send_history_id] ) }}" target="_blank">
												{{ $history->mail_title ?? '' }}
												<i class="fas fa-external-link-alt"></i>
											</a>
										</td>
										<td>
											<div class="communication_detail">
												{!! nl2br( ( $history->mail_text ?? '' ) ) !!}
											</div>
										</td>
										<td>
											@isset( $history->entryOperator )
												{{ $history->entryOperator->m_operator_name ?? '' }}
											@endisset
										</td>
										<td>
											{{ \App\Enums\MailSendStatusEnum::tryfrom( $history->mail_send_status )?->label() }}
										</td>
									</tr>
								@endforeach
							@endisset
						</table>
						<button type="submit" class="btn btn-success u-mr--xs" name="submit" value="mail_send" formtarget="_blank">メール送信</button>
						@isset( $viewExtendData['shop_list'] )
							@foreach( $viewExtendData['shop_list'] as $shop )
								@if( $order->m_account_id == $shop->m_account_id && $shop->maildealer_display_type == 1 && !empty( $shop->maildealer_api_url ) && !empty( $shop->maildealer_mailbox_number ) )
									@php
										$address = [];
										if( !empty( $order->order_email1 ) ) $address[] = $order->order_email1;
										if( !empty( $order->order_email2 ) ) $address[] = $order->order_email2;
									@endphp
									<div class="frameBox u-mt--xs" style="height: 525px">
										<iframe width="100%" height="100%"
											src="{{ $shop->maildealer_api_url }}?md_mode=maillist&md_box={{ $shop->maildealer_mailbox_number }}&md_crmmail={{ implode(',', $address) }}">
										</iframe>
									</div>
									@break
								@endif
							@endforeach
						@endisset
					</div>
				</div><!-- tabs-inner -->
			</div><!-- tabs -->
		</div>

		<div class="u-mt--sl">
			@isset( $order->previous_url )
				<button type="button" class="btn btn-default btn-lg"
			@else
				<button type="button" class="btn btn-default btn-lg" onClick="window.close();">閉じる</button>
			@endif
		</div>
	</form>
	{{-- 受注明細の商品在庫状態クリック --}}
	<div id="frameWindow" style="display: none">
		<iframe id="iframeDiv"></iframe>
	</div>

	<p id="float_edit_wrapper" class="footer-button-p" style="right:70px;">
		<a id="float_edit" class="footer-button-a" style="color: #fff;" href="{{ route('order.order.edit', ['id' => ( $order->t_order_hdr_id ?? '' )]) }}">受注編集</a>
	</p>

	@include('order.base.image-modal')
	@push('css')<link rel="stylesheet" href="{{ esm_internal_asset('css/order/gfh_1207/app.css') }}">@endpush
	@push('js')<script src="{{ esm_internal_asset('js/order/gfh_1207/info.js') }}"></script>@endpush
	@include('common.elements.datetime_picker_script')
@endsection
