{{-- GFOSMB0010:出荷系帳票出力 --}}
{{-- 画面設定 --}}
@php
$ScreenCd='GFOSMB0010';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '出荷未出荷一覧')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>出荷未出荷一覧</li>
@endsection

@section('content')
<style>
	.align-content-center{
		align-content: center;
	}
	.display-flex{
		display: flex;
	}
	.ml-20{
		margin-left: 20px; 
	}
	.ml-50{
		margin-left: 50px; 
	}
	.hand-carried-inner{
		margin-left: 20px !important; 
	}
	table {
		border-collapse: collapse;
		width: 100%;
	}
	td, th {
		border: 1px solid #ddd;
		padding: 8px;
	}
	.submit-btn{
		text-align: end;
		margin-right: 75px;
		margin-bottom: 10px;
	}
	.width-0{
		width: 0 !important;
	}
</style>
<form method="POST" action="{{ route('order.shipment_reports.list.output') }}" name="Form1" id="Form1" enctype="multipart/form-data">
{{ csrf_field() }}

	<div class="d-table c-box--1200">
		<div class="c-box--800">
			<table class="table table-bordered c-tbl c-tbl--800">
				<tbody>
					<tr>
						<th class="c-box--140">手提げ出荷未出荷一覧・出荷予定日別手提げ枚数</th>
					</tr>
					<td class="c-box--500z">
						<div class='ml-20 u-mt--sl' id='hand-carried'>
							<input class="form-check-input" type="radio" name="hand_carried_radio" id="list_hand_carried_item" value="0" {{ old('hand_carried_radio', isset($searchInfo['hand_carried_radio']) ? $searchInfo['hand_carried_radio'] : '') == '0' ? 'checked' : '' }} checked>
							<label class="form-check-label" for="list_hand_carried_item">手提げ出荷未出荷一覧</label>
							<input class="form-check-input hand-carried-inner" type="radio" name="hand_carried_radio" id="number_hand_carried_items" value="1" {{ old('hand_carried_radio', isset($searchInfo['hand_carried_radio']) ? $searchInfo['hand_carried_radio'] : '') == '1' ? 'checked' : '' }}>
							<label class="form-check-label " for="number_hand_carried_items">出荷予定日別手提げ枚数</label>
							@include('common.elements.error_tag', ['name' => 'hand_carried_radio'])
						</div>
						<div class="display-flex u-mt--sl ml-20">
							<label class="">出荷予定日</label>
							<div class="ml-20">
								<div class="u-mt--xs d-table">
									<div class="c-box--218 d-table-cell">
										<div class='input-group date' id='datetimepicker1'>
											<input type='text' class="form-control c-box--180" name="deli_plan_date_from" id="deli_plan_date_from" placeholder="" value="{{old('deli_plan_date_from', isset($searchInfo['deli_plan_date_from'])? $searchInfo['deli_plan_date_from']: now()->startOfMonth()->format('Y-m-d'))}}" />
											<span class="input-group-addon">
												<span class="glyphicon glyphicon-calendar"></span>
											</span>
											<script type="text/javascript">
												$(function () {
												$('#datetimepicker1').datetimepicker();
												});
											</script>
										</div>
										@include('common.elements.error_tag', ['name' => 'deli_plan_date_from'])
									</div>
									<div class="d-table-cell">&nbsp;～&nbsp;</div>
								</div>
								<div class='c-box--218' >
									<div class='input-group date' id='datetimepicker2'>
										<input type='text' class="form-control c-box--180" name="deli_plan_date_to" id="deli_plan_date_to" placeholder="" value="{{old('deli_plan_date_to', isset($searchInfo['deli_plan_date_to'])? $searchInfo['deli_plan_date_to']: now()->format('Y-m-d'))}}" />
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar"></span>
										</span>
										<script type="text/javascript">
											$(function () {
											$('#datetimepicker2').datetimepicker();
											});
										</script>
									</div>
									@include('common.elements.error_tag', ['name' => 'deli_plan_date_to'])
								</div>
							</div>
							<label class="ml-50">顧客ランク</label>
							<div class="ml-20">
								<div class="u-mt--xs d-table">
									<div class="c-box--218 d-table-cell">
										<div class='' id=''>
											<select name="cust_runk_id" id="cust_runk_id" class="form-control c-box--200">
												<option value=""></option>
												@if(isset($compact['custRank']) && is_array($compact['custRank']))
													@foreach($compact['custRank'] as $key  =>$custRank)
														<option value="{{ $custRank['m_itemname_types_id'] }}"   {{ old('cust_runk_id', isset($searchInfo['cust_runk_id']) ? $searchInfo['cust_runk_id'] : '') == $custRank['m_itemname_types_id'] ? 'selected' : '' }}>{{$custRank['m_itemname_type_name']}}</option>
													@endforeach
												@endif
											</select>
											@include('common.elements.error_tag', ['name' => 'cust_runk_id'])
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="submit-btn">
							<button class="btn btn-default" type="submit" name="submit" value="hand_carried_submit">出力</button>
						</div>
					</td>	
				</tbody>
			</table>

			<table class="table table-bordered c-tbl c-tbl--800 u-mt--sl">
				<tbody>
					<tr>
						<th class="c-box--140">段ボール作業日別使用枚数一覧</th>
					</tr>
					<td class="c-box--500z">
						<div class="display-flex u-mt--sl ml-20">
							<label class="">検品日</label>
							<div class="ml-50">
								<div class="u-mt--xs d-table">
									<div class="c-box--218 d-table-cell">
										<div class='input-group date' id='datetimepicker3'>
											<input type='text' class="form-control c-box--180" name="deli_inspection_date_from" id="deli_inspection_date_from" placeholder="" value="{{old('deli_inspection_date_from', isset($searchInfo['deli_inspection_date_from'])? $searchInfo['deli_inspection_date_from']: now()->startOfMonth()->format('Y-m-d') )}}" />
											<span class="input-group-addon">
												<span class="glyphicon glyphicon-calendar"></span>
											</span>
											<script type="text/javascript">
												$(function () {
												$('#datetimepicker3').datetimepicker();
												});
											</script>
										</div>
										@include('common.elements.error_tag', ['name' => 'deli_inspection_date_from'])
									</div>
									<div class="d-table-cell">&nbsp;～&nbsp;</div>
								</div>
								<div class='c-box--218' >
									<div class='input-group date' id='datetimepicker4'>
										<input type='text' class="form-control c-box--180" name="deli_inspection_date_to" id="deli_inspection_date_to" placeholder="" value="{{old('deli_inspection_date_to', isset($searchInfo['deli_inspection_date_to'])? $searchInfo['deli_inspection_date_to']: now()->format('Y-m-d') )}}" />
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar"></span>
										</span>
										<script type="text/javascript">
											$(function () {
											$('#datetimepicker4').datetimepicker();
											});
										</script>
									</div>
									@include('common.elements.error_tag', ['name' => 'deli_inspection_date_to'])
								</div>
							</div>
						</div>
						<div class="submit-btn">
							<button class="btn btn-default" type="submit" name="submit" value="cardboard_submit">出力</button>
						</div>
					</td>	
				</tbody>
			</table>

			<table class="table table-bordered c-tbl c-tbl--800 u-mt--sl">
				<tbody>
					<tr>
						<th class="c-box--140">出荷ステータスPG</th>
					</tr>
					<td class="c-box--500z">
						<div class="display-flex u-mt--sl ml-20">
							<label class="">出荷予定日</label>
							<div class="ml-20">
								<div class="u-mt--xs d-table">
									<div class="c-box--218 d-table-cell">
										<div class='input-group date' id='datetimepicker5'>
											<input type='text' class="form-control c-box--180" name="deli_plan_date_from1" id="deli_plan_date_from1" placeholder="" value="{{old('deli_plan_date_from1', isset($searchInfo['deli_plan_date_from1'])? $searchInfo['deli_plan_date_from1']: now()->startOfMonth()->format('Y-m-d') )}}" />
											<span class="input-group-addon">
												<span class="glyphicon glyphicon-calendar"></span>
											</span>
											<script type="text/javascript">
												$(function () {
												$('#datetimepicker5').datetimepicker();
												});
											</script>
										</div>
										@include('common.elements.error_tag', ['name' => 'deli_plan_date_from1'])
									</div>
									<div class="d-table-cell">&nbsp;～&nbsp;</div>
								</div>
								<div class='c-box--218' >
									<div class='input-group date' id='datetimepicker6'>
										<input type='text' class="form-control c-box--180" name="deli_plan_date_to1" id="deli_plan_date_to1" placeholder="" value="{{old('deli_plan_date_to1', isset($searchInfo['deli_plan_date_to1'])? $searchInfo['deli_plan_date_to1']: now()->format('Y-m-d'))}}" />
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar"></span>
										</span>
										<script type="text/javascript">
											$(function () {
											$('#datetimepicker6').datetimepicker();
											});
										</script>
									</div>
									@include('common.elements.error_tag', ['name' => 'deli_plan_date_to1'])
								</div>
							</div>
							<label class="ml-50">受注方法</label>
							<div class="ml-20">
								<div class="u-mt--xs d-table">
									<div class="c-box--218 d-table-cell">
										<div class='' id=''>
											<select name="order_type" id="order_type" class="form-control c-box--220">
												@if(isset($compact['orderType']) && is_array($compact['orderType']))
													@foreach($compact['orderType']['m_ordertypes'] as $key  =>$orderType)
														<option value="{{ $key }}"  {{ old('order_type', isset($searchInfo['order_type']) ? $searchInfo['order_type'] : '') == $key ? 'selected' : '' }}>{{$orderType}}</option>
													@endforeach
												@endif
											</select>
											@include('common.elements.error_tag', ['name' => 'order_type'])
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="display-flex u-mt--sl ml-20">
							<label style="margin-left: 14px;">支払方法</label>
							<div class="ml-20">
								<div class="u-mt--xs d-table">
									<div class="c-box--218 d-table-cell">
										<div class='' id=''>
											<select name="payment_type" id="payment_type" class="form-control c-box--220">
												<option value=""></option>
												@if(isset($compact['paymentType']) && is_array($compact['paymentType']))
													@foreach($compact['paymentType'] as $key  =>$paymentType)
														<option value="{{ $paymentType['m_payment_types_id'] }}"  {{ old('payment_type', isset($searchInfo['payment_type']) ? $searchInfo['payment_type'] : '') == $paymentType['m_payment_types_id'] ? 'selected' : '' }}>{{$paymentType['m_payment_types_name']}}</option>
													@endforeach
												@endif
											</select>
											@include('common.elements.error_tag', ['name' => 'payment_type'])
										</div>
									</div>
								</div>
							</div>
							<label class="ml-50" style="margin-left: 70px;">配送方法</label>
							<div class="ml-20">
								<div class="u-mt--xs d-table">
									<div class="c-box--218 d-table-cell">
										<div class='' id=''>
											<select name="shipping_method" id="shipping_method" class="form-control c-box--220">
												<option value=""></option>
												@if(isset($compact['deliveryMethod']) && is_array($compact['deliveryMethod']))
													@foreach($compact['deliveryMethod'] as $key  =>$shippingMethod)
														<option value="{{ $shippingMethod['m_delivery_types_id'] }}"  {{ old('shipping_method', isset($searchInfo['shipping_method']) ? $searchInfo['shipping_method'] : '') == $shippingMethod['m_delivery_types_id'] ? 'selected' : '' }}>{{$shippingMethod['m_delivery_type_name']}}</option>
													@endforeach
												@endif
											</select>
											@include('common.elements.error_tag', ['name' => 'shipping_method'])
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="display-flex u-mt--sl ml-20">
							<label style="margin-left: 14px;margin-top: 7px;"">出荷指示</label>
							<div class="ml-20">
								<div class="u-mt--xs d-table">
									<div class="c-box--218 d-table-cell">
										<div class='' id=''>
											<label><input type="radio" name="deli_instruct" value="1" {{ old('deli_instruct', isset($searchInfo['deli_instruct']) ? $searchInfo['deli_instruct'] : '') == '1' ? 'checked' : ''}} checked>&nbsp;あり</label>
											<label class="ml-20"><input type="radio" name="deli_instruct" value="2" {{ old('deli_instruct', isset($searchInfo['deli_instruct']) ? $searchInfo['deli_instruct'] : '') == '2' ? 'checked' : '' }}>&nbsp;なし</label>
										</div>
									</div>
								</div>
							
							</div>
						</div>
						<div class="submit-btn u-mt--sl">
							<button class="btn btn-default" type="submit" name="submit" value="shipping_pg_submit">出力</button>
						</div>
					</td>	
				</tbody>
			</table>

			<table class="table table-bordered c-tbl c-tbl--800 u-mt--sl">
				<tbody>
					<tr>
						<th class="c-box--140">出荷検品チェックリスト</th>
					</tr>
					<td class="c-box--500z">
						<div class='ml-20 u-mt--sl' id='hand-carried'>
							<input class="form-check-input" type="radio" name="shipping_inspection_checklist" id="uninspected" value="1" {{ old('shipping_inspection_checklist', isset($searchInfo['shipping_inspection_checklist']) ? $searchInfo['shipping_inspection_checklist'] : '') == '1' ? 'checked' : '' }} checked>
							<label class="form-check-label" for="uninspected">未検品</label>
							<input class="form-check-input hand-carried-inner" type="radio" name="shipping_inspection_checklist" id="inspected" value="2" {{ old('shipping_inspection_checklist', isset($searchInfo['shipping_inspection_checklist']) ? $searchInfo['shipping_inspection_checklist'] : '') == '2' ? 'checked' : '' }}>
							<label class="form-check-label " for="inspected">検品済み</label>
							<input class="form-check-input hand-carried-inner" type="radio" name="shipping_inspection_checklist" id="all" value="3" {{ old('shipping_inspection_checklist', isset($searchInfo['shipping_inspection_checklist']) ? $searchInfo['shipping_inspection_checklist'] : '') == '3' ? 'checked' : '' }}>
							<label class="form-check-label " for="all">すべて</label>
							@include('common.elements.error_tag', ['name' => 'shipping_inspection_checklist'])
						</div>
						<div class="display-flex u-mt--sl ml-20">
							<label class="">出荷予定日</label>
							<div class="ml-20">
								<div class="u-mt--xs d-table">
									<div class="c-box--218 d-table-cell">
										<div class='input-group date' id='datetimepicker7'>
											<input type='text' class="form-control c-box--180" name="deli_plan_date_from3" id="deli_plan_date_from3" placeholder="" value="{{old('deli_plan_date_from3', isset($searchInfo['deli_plan_date_from3'])? $searchInfo['deli_plan_date_from3']: now()->startOfMonth()->format('Y-m-d') )}}" />
											<span class="input-group-addon">
												<span class="glyphicon glyphicon-calendar"></span>
											</span>
											<script type="text/javascript">
												$(function () {
												$('#datetimepicker7').datetimepicker();
												});
											</script>
										</div>
										@include('common.elements.error_tag', ['name' => 'deli_plan_date_from3'])
									</div>
									<div class="d-table-cell">&nbsp;～&nbsp;</div>
								</div>
								<div class='c-box--218' >
									<div class='input-group date' id='datetimepicker8'>
										<input type='text' class="form-control c-box--180" name="deli_plan_date_to3" id="deli_plan_date_to3" placeholder="" value="{{old('deli_plan_date_to3', isset($searchInfo['deli_plan_date_to3'])? $searchInfo['deli_plan_date_to3']: now()->format('Y-m-d') )}}" />
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar"></span>
										</span>
										<script type="text/javascript">
											$(function () {
											$('#datetimepicker8').datetimepicker();
											});
										</script>
									</div>
									@include('common.elements.error_tag', ['name' => 'deli_plan_date_to3'])
								</div>
							</div>
							<label class="ml-50">受注ID</label>
							<div class="ml-20">
								<div class="u-mt--xs d-table">
									<div class="c-box--218 d-table-cell width-0">
										<div class='' id=''>
											<input type='text' class="form-control " name="order_id_from" placeholder=""  value="{{old('order_id_from', isset($searchInfo['order_id_from'])? $searchInfo['order_id_from']:'')}}" />
											@include('common.elements.error_tag', ['name' => 'order_id_from'])
										</div>
									</div>
									<div class="d-table-cell">&nbsp;～&nbsp;</div>
								</div>
								<div class='c-box--218' >
									<div class='' id=''>
										<input type='text' class="form-control " name="order_id_to" placeholder=""  value="{{old('order_id_to', isset($searchInfo['order_id_to'])? $searchInfo['order_id_to']:'')}}" />
										@include('common.elements.error_tag', ['name' => 'order_id_to'])
									</div>
								</div>
							</div>
						</div>
						<div class="submit-btn u-mt--mm">
							<button class="btn btn-default" type="submit" name="submit" value="shipping_inspection_checklist_submit">出力</button>
						</div>
					</td>	
				</tbody>
			</table>

			<table class="table table-bordered c-tbl c-tbl--800 u-mt--sl">
				<tbody>
					<tr>
						<th class="c-box--140">出荷検品データ作成</th>
					</tr>
					<td class="c-box--500z">
						<div class="display-flex u-mt--sl ml-20">
							<label style="margin-left: 14px;">条件</label>
						</div>
						<div class="display-flex ml-20 u-mt--ss">
							<div class="ml-50">
								<div class="u-mt--xs d-table">
									<div class="c-box--218 d-table-cell">
										<div class='' id=''>
											<label><input type="checkbox" name="is_email" value="1" {{ old('is_email', isset($searchInfo['is_email']) ? $searchInfo['is_email'] : '') == '1' ? 'checked' : '' }}>&nbsp;メール送信あり</label>
											<label class="ml-20"><input type="checkbox" name="is_yesterday" value="1"  {{ old('is_yesterday', isset($searchInfo['is_yesterday']) ? $searchInfo['is_yesterday'] : '') == '1' ? 'checked' : '' }}>&nbsp;前日日付</label>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="display-flex u-mt--sl ml-20 ">
							<label style="margin-left: 14px;">抽出区分</label>
						</div>
						<div class="display-flex u-mt--sl ml-20">
							<label style="margin-left: 48px;margin-top: 7px;"">作成区分</label>
							<div class="ml-20">
								<div class="u-mt--xs d-table">
									<div class="c-box--400 d-table-cell">
										<div class='ml-20 ' id='hand-carried'>
											<input class="form-check-input" type="radio" name="shipping-inspection-checklist" id="新規作成" value="1" {{ old('shipping-inspection-checklist', isset($searchInfo['shipping-inspection-checklist']) ? $searchInfo['shipping-inspection-checklist'] : '') == '1' ? 'checked' : '' }} checked>
											<label class="form-check-label" for="新規作成">新規作成</label>
											<input class="form-check-input hand-carried-inner" type="radio" name="shipping-inspection-checklist" id="再作成" value="2" {{ old('shipping-inspection-checklist', isset($searchInfo['shipping-inspection-checklist']) ? $searchInfo['shipping-inspection-checklist'] : '') == '2' ? 'checked' : '' }}>
											<label class="form-check-label " for="再作成">再作成</label>
											@include('common.elements.error_tag', ['name' => 'shipping-inspection-checklist'])
										</div>
									</div>
								</div>
							
							</div>
						</div>
						<div class="display-flex u-mt--sl ml-20">
							<label style="margin-left: 48px;">処理日時</label>
							<div class="ml-20">
								<div class="u-mt--xs d-table">
									<div class="c-box--218 d-table-cell">
										<div class='' id=''>
											<select name="process_date" id="process_date" class="form-control c-box--220">
												<option value=""></option>
												@if(isset($compact['processDateTime']) && is_array($compact['processDateTime']))
													@foreach($compact['processDateTime'] as $key  =>$processDateTime)
														<option value="{{ $processDateTime['process_timestamp'] }}"  {{ old('process_date', isset($searchInfo['process_date']) ? $searchInfo['process_date'] : '') == $processDateTime['process_timestamp'] ? 'selected' : '' }}>{{$processDateTime['process_timestamp']}}</option>
													@endforeach
												@endif
											</select>
											@include('common.elements.error_tag', ['name' => 'process_date'])
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="submit-btn u-mt--sl">
							<button class="btn btn-default" type="submit" name="submit" value="shipping_inspection_data_creation_submit">出力</button>
						</div>
					</td>	
				</tbody>
			</table>
	</div>
</div>

</form>
<script>
	document.addEventListener("DOMContentLoaded", function() {
		const newCreateRadio = document.getElementById("新規作成");
		const reCreateRadio = document.getElementById("再作成");
		const processDateSelect = document.getElementById("process_date");

		function toggleProcessDate() {
			if (newCreateRadio.checked) {
				processDateSelect.disabled = true;
				processDateSelect.value = ""; 
			} else {
				processDateSelect.disabled = false;
			}
		}
		toggleProcessDate();

		newCreateRadio.addEventListener("change", toggleProcessDate);
		reCreateRadio.addEventListener("change", toggleProcessDate);
	});
</script>
@endsection