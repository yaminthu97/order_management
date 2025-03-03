@php $column_name = "check_t_order_destination_id" @endphp
@include('common.elements.all_check_script_paginator')
@include('common.elements.paginator_header_NoEvent')
@include('common.elements.page_list_count_NoEvent')
<!-- <div class="c-box--full u-mt--sm"> -->
<div class="c-box--1600Overflow u-mt--sm">
	<table class="table table-bordered c-tbl table-link nowrap">
		<thead>
			<tr>
				<th><label for=""><input type="checkbox" id="check_all" value="" onclick="checkAll()"></label> 全選択</th>
				@foreach( $viewExtendData['order_disp'] as $dispColumn )
					{{-- ソート対象項目 --}}
					@if( in_array($dispColumn['m_column_name'], ['m_ecs_id', 'ec_order_num', 't_order_hdr_id', 'progress_type', 'order_datetime', 'order_email', 'order_total_price', 'deli_hope_date', 'deli_plan_date', 'update_timestamp'], true ) )
						@php $columnName = ( $dispColumn['m_column_name'] == 'order_email' ? 'order_email1' : $dispColumn['m_column_name'] ); @endphp
						<th>@include('common.elements.sorting_column_name_NoEvent', ['columnName' => $columnName, 'columnViewName' => $dispColumn['m_column_disp_name']]) </th>
					{{-- ECサイト種類 --}}
					@elseif( $dispColumn['m_column_name'] === 'm_ec_type' ) 
						<th>&nbsp;</th>
					{{-- 配送条件 --}}
					@elseif( $dispColumn['m_column_name'] === 'delivery_condition' )
						<th>与信区分</th>
						<th>前払入金区分</th>
					@else
						<th>{{$dispColumn['m_column_disp_name']}}</th>
					@endif
				@endforeach
			</tr>
		</thead>
		@if( isset($paginator) )
			@if(!empty($paginator->count()) > 0)
				@foreach($paginator as $order)
					<tr class="color_class {{ $order['color_class'] }}">
						<td class="u-center" width="100">
							<label for=""><input type="checkbox" name="check_t_order_destination_id[]" id="check_t_order_destination_id[]" value="{{ isset($order['t_order_destination_id']) ? $order['t_order_destination_id'] : '0'}}"></label>
						</td>
						@foreach($viewExtendData['order_disp'] as $dispColumn)
							@switch( $dispColumn['m_column_name'] )
								{{-- ECサイト種別 --}}
								@case( 'm_ec_type' )
									<td>
										@if( isset( $order['ecs'] ) && isset( $order['ecs']['m_ec_type'] ) )
											@switch( $order['ecs']['m_ec_type'] )
												@case(1)
													<img src="{{config('env.design_path')}}images/common/icon_yahoo_06.png" alt="Yahoo!ショッピング">
													@break
												@case(2)
													<img src="{{config('env.design_path')}}images/common/icon_yahoo_06.png" alt="ヤフオク！">
													@break
												@case(3)
													<img src="{{config('env.design_path')}}images/common/icon_rakuten_06.png" alt="楽天">
													@break
												@case(4)
													<img src="{{config('env.design_path')}}images/common/icon_amazon_06.png" alt="amazon">
													@break
												@case(5)
													<img src="{{config('env.design_path')}}images/common/icon_wowma_06.png" alt="Wowma">
													@break
												@case(7)
													<img src="{{config('env.design_path')}}images/common/icon_futureshop_06.png" alt="futureshop">
													@break
											@endswitch
										@endif
									</td>
									@break
								{{-- ECサイト名 --}}
								@case( 'm_ecs_name' )
									<td>
										@if( isset( $order['ecs'] ) && isset( $order['ecs']['m_ec_name'] ) )
											{{ isset( $order['ecs']['m_ec_name'] ) ? $order['ecs']['m_ec_name'] : '' }}
										@endif
									</td>
									@break
								{{-- ECサイト注文ID --}}
								@case( 'ec_order_num' )
									<td>
										{{ isset($order['ec_order_num']) ? $order['ec_order_num'] : '' }}
									</td>
									@break
								{{-- 受注ID --}}
								@case( 't_order_hdr_id' )
									<td>
										@if( isset($order['t_order_hdr_id']) )
											<a href="{{ route('order.order.info', ['id' => $order['t_order_hdr_id']] ) }}" target="_blank">{{ $order['t_order_hdr_id'] }}<i class="fas fa-external-link-alt"></i></a>
										@endif
									</td>
									@break
								{{-- 受注編集 --}}
								@case( 'order_edit' )
									<td>
										@if( isset($order['t_order_hdr_id']) && $order['progress_type'] <= 40 )
											<a href="{{ route('order.order.edit', ['id' => $order['t_order_hdr_id']] ) }}">編集</a>
										@endif
									</td>
									@break
								{{-- 配No --}}
								@case( 'order_destination_seq' )
									<td>
										{{ isset($order['order_destination_seq']) ? $order['order_destination_seq'] : '' }}
									</td>
									@break
								{{-- 注文主 --}}
								@case( 'order_name' )
									<td>
										{{ isset($order['order_name']) ? $order['order_name'] : '' }}
									</td>
									@break
								{{-- 配送先 --}}
								@case( 'destination_name' )
									<td>
										{{ isset($order['destination_name']) ? $order['destination_name'] : '' }}
									</td>
									@break
								{{-- 進捗区分 --}}
								@case( 'progress_type_name' )
									<td>
										@switch( $order['progress_type'] )
											@case( \App\Enums\ProgressTypeEnum::PendingConfirmation->value )
												{{ isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingConfirmation->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingConfirmation->value] : \App\Enums\ProgressTypeEnum::PendingConfirmation->label() }}
												@break
											@case( \App\Enums\ProgressTypeEnum::PendingCredit->value )
												{{ isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingCredit->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingCredit->value] : \App\Enums\ProgressTypeEnum::PendingCredit->label() }}
												@break
											@case( \App\Enums\ProgressTypeEnum::PendingPrepayment->value )
												{{ isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingPrepayment->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingPrepayment->value] : \App\Enums\ProgressTypeEnum::PendingPrepayment->label() }}
												@break
											@case( \App\Enums\ProgressTypeEnum::PendingAllocation->value )
												{{ isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingAllocation->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingAllocation->value] : \App\Enums\ProgressTypeEnum::PendingAllocation->label() }}
												@break
											@case( \App\Enums\ProgressTypeEnum::PendingShipment->value )
												{{ isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingShipment->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingShipment->value] : \App\Enums\ProgressTypeEnum::PendingShipment->label() }}
												@break
											@case( \App\Enums\ProgressTypeEnum::Shipping->value )
												{{ isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::Shipping->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::Shipping->value] : \App\Enums\ProgressTypeEnum::Shipping->label() }}
												@break
											@case( \App\Enums\ProgressTypeEnum::Shipped->value )
												{{ isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::Shipped->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::Shipped->value] : \App\Enums\ProgressTypeEnum::Shipped->label() }}
												@break
											@case( \App\Enums\ProgressTypeEnum::PendingPostPayment->value )
												{{ isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingPostPayment->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::PendingPostPayment->value] : \App\Enums\ProgressTypeEnum::PendingPostPayment->label() }}
												@break
											@case( \App\Enums\ProgressTypeEnum::Completed->value )
												{{ isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::Completed->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::Completed->value] : \App\Enums\ProgressTypeEnum::Completed->label() }}
												@break
											@case( \App\Enums\ProgressTypeEnum::Cancelled->value )
												{{ isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::Cancelled->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::Cancelled->value] : \App\Enums\ProgressTypeEnum::Cancelled->label() }}
												@break
											@case( \App\Enums\ProgressTypeEnum::Returned->value )
												{{ isset($viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::Returned->value]) ? $viewExtendData['progress_type_list'][\App\Enums\ProgressTypeEnum::Returned->value] : \App\Enums\ProgressTypeEnum::Returned->label() }}
												@break
										@endswitch
									</td>
									@break
								{{-- 警告 --}}
								@case( 'order_alert' )
									<td class="{{ $order['order_alert_class'] }}">
										{{ isset($order['order_alert']) ? $order['order_alert'] : '' }}
									</td>
									@break
								{{-- タグ --}}
								@case( 'order_tag' )
									<td>
										@if( isset($order['orderTags']) && !empty($order['orderTags']) )
											@foreach($order['orderTags'] as $Tag)
												<span style="background-color:#{{ isset($Tag['orderTag']['tag_color']) ? $Tag['orderTag']['tag_color'] : 'FFFFFF'}};color:#{{ isset($Tag['orderTag']['font_color']) ? $Tag['orderTag']['font_color'] : '000000' }};">{{ isset($Tag['orderTag']['tag_display_name']) ? $Tag['orderTag']['tag_display_name'] : '' }}</span>
											@endforeach
										@endif
									</td>
									@break
								{{-- 受注日時 --}}
								@case( 'order_datetime' )
									<td>
										{{ isset($order['order_datetime']) ? str_replace('-', '/', $order['order_datetime']) : '' }}
									</td>
									@break
								{{-- 電話 --}}
								@case( 'order_tel' )
									<td>
										{{ isset($order['order_tel1']) ? $order['order_tel1'] : '' }}
									</td>
									@break
								{{-- メールアドレス --}}
								@case( 'order_email' )
									<td>
										{{ isset($order['order_email1']) ? $order['order_email1'] : '' }}
									</td>
									@break
								{{-- 支払方法 --}}
								@case( 'm_payment_types_name' )
									<td>
										@if( $viewExtendData['m_paytype_list'] )
											@foreach( $viewExtendData['m_paytype_list'] as $payType )
												@if( $payType['m_payment_types_id'] == $order['m_payment_types_id'] )
													{{ $payType['m_payment_types_name'] }}
												@endif
											@endforeach
										@endif
									</td>
									@break
								{{-- 請求金額 --}}
								@case( 'order_total_price' )
									<td class="u-right">
										{{ isset($order['order_total_price']) ? number_format($order['order_total_price']) : 0 }}
									</td>
									@break
								{{-- 配送方法 --}}
								@case( 'm_delivery_type_name' )
									<td>
										@if( $viewExtendData['delivery_type_list'] )
											@foreach( $viewExtendData['delivery_type_list'] as $deliveryType )
												@if( $deliveryType['m_delivery_types_id'] == $order['m_delivery_type_id'] )
													{{ $deliveryType['m_delivery_type_name'] }}
												@endif
											@endforeach
										@endif
									</td>
									@break
								{{-- 送付先都道府県 --}}
								@case( 'destination_address1' )
									<td>
										{{ isset($order['destination_address1']) ? $order['destination_address1'] : '' }}
									</td>
									@break
								{{-- 配送希望日 --}}
								@case( 'deli_hope_date' )
									<td>
										{{ isset($order['deli_hope_date']) ? str_replace('-', '/', $order['deli_hope_date']) : '' }}
									</td>
									@break
								{{-- 時間帯 --}}
								@case( 'deli_hope_time_name' )
									<td>
										{{ isset($order['deli_hope_time_name']) ? $order['deli_hope_time_name'] : '' }}
									</td>
									@break
								{{-- 備考 --}}
								@case( 'order_comment' )
									<td class="c-td--ovfh">
										<p data-toggle="tooltip" data-placement="top" title="{{ isset($order['order_comment']) ? $order['order_comment'] : '' }}">{{ isset($order['order_comment']) ? $order['order_comment'] : '' }}</p>
									</td>
									@break
								{{-- 社内メモ --}}
								@case( 'operator_comment' )
									<td class="c-td--ovfh">
										<p data-toggle="tooltip" data-placement="top" title="{{ isset($order['orderMemo']['operator_comment']) ? $order['orderMemo']['operator_comment'] : '' }}">{{ isset($order['orderMemo']['operator_comment']) ? $order['orderMemo']['operator_comment'] : '' }}</p>
									</td>
									@break
								{{-- 要注意顧客 --}}
								@case( 'alert_cust_check_type' )
									<td>
										@if( isset($order['alert_cust_check_type']) )
											@switch( $order['alert_cust_check_type'] )
												@case( 0 )
													<span class="glyphicon glyphicon-remove" title="未確認"></span>
													@break
												@case( 2 )
													<span class="glyphicon glyphicon-ok-sign" title="確認済み"></span>
													@break
												@case( 9 )
													<span class="glyphicon glyphicon-minus" title="対象外"></span>
													@break
											@endswitch
										@endif
									</td>
									@break
								{{-- 住所エラー --}}
								@case( 'address_check_type' )
									<td>
										@if( isset($order['address_check_type']) )
											@switch( $order['address_check_type'] )
												@case( 0 )
													<span class="glyphicon glyphicon-remove" title="未確認"></span>
													@break
												@case( 2 )
													<span class="glyphicon glyphicon-ok-sign" title="確認済み"></span>
													@break
												@case( 9 )
													<span class="glyphicon glyphicon-minus" title="対象外"></span>
													@break
											@endswitch
										@endif
									</td>
									@break
								{{-- 指定日エラー --}}
								@case( 'deli_hope_date_check_type' )
									<td>
										@if( isset($order['deli_hope_date_check_type']) )
											@switch( $order['deli_hope_date_check_type'] )
												@case( 0 )
													<span class="glyphicon glyphicon-remove" title="未確認"></span>
													@break
												@case( 2 )
													<span class="glyphicon glyphicon-ok-sign" title="確認済み"></span>
													@break
												@case( 9 )
													<span class="glyphicon glyphicon-minus" title="対象外"></span>
													@break
											@endswitch
										@endif
									</td>
									@break
								{{-- 与信区分 --}}
								{{-- 前払入金区分 --}}
								@case( 'delivery_condition' )
									@if( isset($order['paymentTypes']) && isset($order['paymentTypes']['delivery_condition']) )
										@switch( $order['paymentTypes']['delivery_condition'] )
											@case( 1 )
												<td><span class="glyphicon glyphicon-minus" title="対象外"></span></td>
												<td>
													@if( isset($order['payment_type']) )
														@switch( $order['payment_type'] )
															@case( 0 )
																<span class="glyphicon glyphicon-remove" title="未入金"></span>
																@break
															@case( 1 )
																<span class="glyphicon glyphicon-ok-circle" title="一部入金"></span>
																@break
															@case( 2 )
																<span class="glyphicon glyphicon-ok-sign" title="入金済"></span>
																@break
															@case( 9 )
																<span class="glyphicon glyphicon-minus" title="対象外"></span>
																@break
														@endswitch
													@endif
												</td>
												@break
											@case( 2 )
												<td>
													@if( isset($order['credit_type']) )
														@switch( $order['credit_type'] )
															@case( 0 )
																<span class="glyphicon glyphicon-remove" title="未処理"></span>
																@break
															@case( 1 )
																<span class="glyphicon glyphicon-ok-circle" title="与信NG"></span>
																@break
															@case( 2 )
																<span class="glyphicon glyphicon-ok-sign" title="与信OK"></span>
																@break
															@case( 9 )
																<span class="glyphicon glyphicon-minus" title="対象外"></span>
																@break
														@endswitch
													@endif
												</td>
												<td><span class="glyphicon glyphicon-minus" title="対象外"></span></td>
												@break
											@case(3)
												<td>
													@if( isset($order['credit_type']) )
														@switch( $order['credit_type'] )
															@case( 0 )
																<span class="glyphicon glyphicon-remove" title="未処理"></span>
																@break
															@case( 1 )
																<span class="glyphicon glyphicon-ok-circle" title="与信NG"></span>
																@break
															@case( 2 )
																<span class="glyphicon glyphicon-ok-sign" title="与信OK"></span>
																@break
															@case( 9 )
																<span class="glyphicon glyphicon-minus" title="対象外"></span>
																@break
														@endswitch
													@endif
												</td>
												<td><span class="glyphicon glyphicon-minus" title="対象外"></span></td>
												@break
											@default
												<td><span class="glyphicon glyphicon-minus" title="対象外"></span></td>
												<td><span class="glyphicon glyphicon-minus" title="対象外"></span></td>
										@endswitch
										@break
									@else
										<td><span class="glyphicon glyphicon-minus" title="対象外"></span></td>
										<td><span class="glyphicon glyphicon-minus" title="対象外"></span></td>
									@endif
									@break
								{{-- 引当区分 --}}
								@case( 'reservation_type' )
									<td>
										@if( isset($order['reservation_type']) )
											@switch( $order['reservation_type'] )
												@case( 0 )
													<span class="glyphicon glyphicon-remove" title="未引当"></span>
													@break
												@case( 1 )
													<span class="glyphicon glyphicon-ok-circle" title="一部引当済"></span>
													@break
												@case( 2 )
													<span class="glyphicon glyphicon-ok-sign" title="引当済"></span>
													@break
												@case( 9 )
													<span class="glyphicon glyphicon-minus" title="対象外"></span>
													@break
											@endswitch
										@endif
									</td>
									@break
								{{-- 出荷予定日 --}}
								@case( 'deli_plan_date' )
									<td>
										{{ isset($order['deli_plan_date']) ? str_replace('-', '/', $order['deli_plan_date']) : '' }}
									</td>
									@break
								{{-- 出荷指示区分 --}}
								@case( 'deli_instruct_type' )
									<td>
										@if( isset($order['deli_instruct_type']) )
											@switch( $order['deli_instruct_type'] )
												@case( 0 )
													<span class="glyphicon glyphicon-remove" title="未指示"></span>
													@break
												@case( 1 )
													<span class="glyphicon glyphicon-ok-circle" title="一部指示済"></span>
													@break
												@case( 2 )
													<span class="glyphicon glyphicon-ok-sign" title="指示済"></span>
													@break
												@case( 9 )
													<span class="glyphicon glyphicon-minus" title="対象外"></span>
													@break
											@endswitch
										@endif
									</td>
									@break
								{{-- 出荷確定区分 --}}
								@case('deli_decision_type')
									<td>
										@if( isset($order['deli_decision_type']) )
											@switch( $order['deli_decision_type'] )
												@case( 0 )
													<span class="glyphicon glyphicon-remove" title="未確定"></span>
													@break
												@case( 1 )
													<span class="glyphicon glyphicon-ok-circle" title="一部確定済"></span>
													@break
												@case( 2 )
													<span class="glyphicon glyphicon-ok-sign" title="確定済"></span>
													@break
												@case( 9 )
													<span class="glyphicon glyphicon-minus" title="対象外"></span>
													@break
											@endswitch
										@endif
									</td>
									@break
								{{-- 後払入金区分 --}}
								@case( 'postpay_condition' )
									<td>
										@if( isset($order['paymentTypes']) && isset($order['paymentTypes']['delivery_condition']) && $order['paymentTypes']['delivery_condition'] == 1 )
											<span class="glyphicon glyphicon-minus" title="対象外"></span>
										@else
											@if( isset($order['payment_type']) )
												@switch( $order['payment_type'] )
													@case( 0 )
														<span class="glyphicon glyphicon-remove" title="未入金"></span>
														@break
													@case( 1 )
														<span class="glyphicon glyphicon-ok-circle" title="一部入金"></span>
														@break
													@case( 2 )
														<span class="glyphicon glyphicon-ok-sign" title="入金済"></span>
														@break
													@case( 9 )
														<span class="glyphicon glyphicon-minus" title="対象外"></span>
														@break
												@endswitch
											@endif
										@endif
									</td>
									@break
								{{-- 最終更新日時 --}}
								@case( 'update_timestamp' )
									<td>{{ date('Y/m/d H:i:s', strtotime($order['update_timestamp'])) }}</td>
									@break
							@endswitch
						@endforeach
					</tr>
				@endforeach
			@else
				<tr>
					<td colspan="11">該当受注が見つかりません。</td>
				</tr>
			@endif
		@endif
	</table>
</div>
@include('common.elements.paginator_footer_NoEvent')
