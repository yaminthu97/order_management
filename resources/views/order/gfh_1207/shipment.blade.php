{{-- NEOSM0210:受注検索 --}}
{{-- 画面設定 --}}
@php
$ScreenCd='GFOSMA0010';
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
<form method="POST" action="{{route('order.shipped_search.list.output')}}" name="Form1" id="Form1" enctype="multipart/form-data">
{{ csrf_field() }}
<div>
	
	<table class="table table-bordered c-tbl c-tbl--1200">
		<tbody>
			<tr>
				<th class="c-box--140">受注日</th>
					<td class="c-box--500z">
						<div class="u-mt--xs d-table">
							<div class="c-box--218 d-table-cell">
								<div class='input-group date' id='datetimepicker1'>
									<input type='text' class="form-control c-box--180" name="order_date_from" id="order_date_from" placeholder="" value="{{old('order_date_from', isset($searchInfo['order_date_from'])? $searchInfo['order_date_from']:'')}}" />
									<span class="input-group-addon">
										<span class="glyphicon glyphicon-calendar"></span>
									</span>
									<script type="text/javascript">
										$(function () {
										$('#datetimepicker1').datetimepicker();
										});
									</script>
								</div>
								@include('common.elements.error_tag', ['name' => 'order_date_from'])
							</div>
							<div class="d-table-cell">&nbsp;～&nbsp;</div>
						</div>
						<div class='c-box--218' >
							<div class='input-group date' id='datetimepicker2'>
								<input type='text' class="form-control c-box--180" name="order_date_to" id="order_date_to" placeholder="" value="{{old('order_date_to', isset($searchInfo['order_date_to'])? $searchInfo['order_date_to']:'')}}" />
								<span class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</span>
								<script type="text/javascript">
									$(function () {
									$('#datetimepicker2').datetimepicker();
									});
								</script>
							</div>
							@include('common.elements.error_tag', ['name' => 'order_date_to'])
						</div>
					</td>
					<th class="c-box--140">出荷予定日</th>
				<td class="c-box--500z">
					<div class="u-mt--xs d-table">
						<div class="c-box--218 d-table-cell">
							<div class='input-group date' id='datetimepicker3'>
								<input type='text' class="form-control c-box--180" name="deli_plan_date_from"  id="deli_plan_date_from" placeholder=""  value="{{ old('deli_plan_date_from', isset($searchInfo['deli_plan_date_from']) ? $searchInfo['deli_plan_date_from'] : date('Y/m/d')) }}" />
								<span class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</span>
								<script type="text/javascript">
									$(function () {
										$('#datetimepicker3').datetimepicker();
										$('#deli_plan_date_from').datetimepicker();
									});
								</script>
							</div>
							@include('common.elements.error_tag', ['name' => 'deli_plan_date_from'])
						</div>
						<div class="d-table-cell">&nbsp;～&nbsp;</div>
					</div>
					<div class='c-box--218' >
						<div class='input-group date' id='datetimepicker4'>
							<input type='text' class="form-control c-box--180" name="deli_plan_date_to" id="deli_plan_date_to" placeholder=""  value="{{ old('deli_plan_date_to', isset($searchInfo['deli_plan_date_to']) ? $searchInfo['deli_plan_date_to'] : date('Y/m/d')) }}" />
							<span class="input-group-addon">
								<span class="glyphicon glyphicon-calendar"></span>
							</span>
							<script type="text/javascript">
								$(function () {
									$('#datetimepicker4').datetimepicker();
									$('#deli_plan_date_to').datetimepicker();
								});
							</script>
						</div>
						@include('common.elements.error_tag', ['name' => 'deli_plan_date_to'])
					</div>
				</td>

				<th class="c-box--140">検品日</th>
				<td class="c-box--500z">
					<div class="u-mt--xs d-table">
						<div class="c-box--218 d-table-cell">
							<div class='input-group date' id='datetimepicker5'>
								<input type='text' class="form-control c-box--180" name="inspection_date_from" id="inspection_date_from" placeholder=""  value="{{old('inspection_date_from', isset($searchInfo['inspection_date_from'])? $searchInfo['inspection_date_from']:'')}}" />
								<span class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</span>
								<script type="text/javascript">
									$(function () {
									$('#datetimepicker5').datetimepicker();
									});
								</script>
							</div>
							@include('common.elements.error_tag', ['name' => 'inspection_date_from'])
						</div>
						<div class="d-table-cell">&nbsp;～&nbsp;</div>
					</div>
					<div class='c-box--218' >
						<div class='input-group date' id='datetimepicker6'>
							<input type='text' class="form-control c-box--180" name="inspection_date_to" id="inspection_date_to" placeholder=""   value="{{old('inspection_date_to', isset($searchInfo['inspection_date_to'])? $searchInfo['inspection_date_to']:'')}}" />
							<span class="input-group-addon">
								<span class="glyphicon glyphicon-calendar"></span>
							</span>
							<script type="text/javascript">
								$(function () {
								$('#datetimepicker6').datetimepicker();
								});
							</script>
						</div>
						@include('common.elements.error_tag', ['name' => 'inspection_date_to'])
					</div>
				</td>
		
			</tr>

			<tr>
				<th class="c-box--140">受注ID</th>
					<td class="c-box--500z">
						<div class="u-mt--xs d-table">
							<div class="c-box--218 d-table-cell">
								<div class='' id=''>
									<input type='text' class="form-control " name="order_id_from" placeholder=""  value="{{old('order_id_from', isset($searchInfo['order_id_from'])? $searchInfo['order_id_from']:'')}}" />
									@include('common.elements.error_tag', ['name' => 'order_id_from'])
								</div>
							</div>
							<div class="d-table-cell">&nbsp;～&nbsp;</div>
						</div>
						<div class='c-box--218' >
							<div class='' id='datetimepicker2'>
								<input type='text' class="form-control " name="order_id_to" placeholder=""   value="{{old('order_id_to', isset($searchInfo['order_id_to'])? $searchInfo['order_id_to']:'')}}"/>
								@include('common.elements.error_tag', ['name' => 'order_id_to'])
							</div>
						</div>
					</td>
				<th class="c-box--140">一品一葉</th>
				<td class="c-box--500z">
					<div class="u-mt--xs d-table">
						<div class="c-box--218 d-table-cell">
							<div class='' id=''>
							<input type="hidden" name="one_item_only" value="0"><input type="checkbox"  id="one_item_only" name="one_item_only" value="1" {{ old('one_item_only', isset($searchInfo['one_item_only']) ? $searchInfo['one_item_only'] : '0') == '1' ? 'checked' : '' }}><label style="margin-left: 10px;" for="one_item_only">一品一葉のみ抽出</label>
							@include('common.elements.error_tag', ['name' => 'one_item_only'])
						</div>
						</div>
					</div>
				</td>

				<th class="c-box--140">のし有無</th>
				<td class="c-box--500z">
					<div class="u-mt--xs d-table">
						<div class="c-box--218 d-table-cell">
							<div class='' id=''>
								<select name="has_noshi" id="has_noshi" class="form-control c-box--200" >
										<option value=""></option>
										<option value="0" {{ old('has_noshi', isset($searchInfo['has_noshi']) ? $searchInfo['has_noshi'] : '') == "0" ? 'selected' : '' }}>有のみ</option>
										<option value="1" {{ old('has_noshi', isset($searchInfo['has_noshi']) ? $searchInfo['has_noshi'] : '') == "1" ? 'selected' : '' }}>無のみ</option>
								</select>
								@include('common.elements.error_tag', ['name' => 'has_noshi'])
							</div>
						</div>
					</div>
				</td>
		
			</tr>

			<tr>
				<th class="c-box--140">商品ページコード</th>
					<td class="c-box--500z">
						<div class="u-mt--xs d-table">
							<div class="c-box--218 d-table-cell">
								<div class='' id=''>
									<input type='text' class="form-control " name="page_cd" id="page_cd"  placeholder="" value="{{old('page_cd', isset($searchInfo['page_cd'])? $searchInfo['page_cd']:'')}}" />
									@include('common.elements.error_tag', ['name' => 'page_cd'])
								</div>
							</div>
						</div>
					</td>
				<th class="c-box--140">店舗集計グループ</th>
				<td class="c-box--500z">
					<div class="u-mt--xs d-table">
						<div class="c-box--218 d-table-cell">
							<div class='' id=''>
								<select	select name="store_group" id="store_group" class="form-control c-box--200"  >
									<option value=""></option>
									@foreach($itemNameData as $key  =>$data)
										<option value="{{ $data['m_itemname_type_code'] }}" {{ old('store_group', isset($searchInfo['store_group']) ? $searchInfo['store_group'] : '') == $data['m_itemname_type_code'] ? 'selected' : '' }}>{{$data['m_itemname_type_code']}}</option>
									@endforeach
								</select>
								@include('common.elements.error_tag', ['name' => 'store_group'])
							</div>
						</div>
					</div>
				</td>

				<th class="c-box--140">受注方法</th>
				<td class="c-box--500z">
					<div class="u-mt--xs d-table">
						<div class="c-box--218 d-table-cell">
							<div class='' id=''>
								<select name="order_type" id="order_type" class="form-control c-box--200">
									@if(isset($valueArray['m_ordertypes']) && is_array($valueArray['m_ordertypes']))
										@foreach($valueArray['m_ordertypes'] as $key  =>$orderType)
											<option value="{{ $key }}"  {{ old('order_type', isset($searchInfo['order_type']) ? $searchInfo['order_type'] : '') == $key ? 'selected' : '' }}>{{$orderType}}</option>
										@endforeach
									@else
										<option value=""></option>
									@endif
								</select>
								@include('common.elements.error_tag', ['name' => 'order_type'])
							</div>
						</div>
					</div>
				</td>
		
			</tr>
		</tbody>
	</table>
	
	<div>
		<label class = "middle">　出力選択：　</label>
		<select class="u-input--mid form-control c-box--300" id="process_type" name="process_type">
			<option value="1"  {{ old('process_type', isset($searchInfo['process_type']) ? $searchInfo['process_type'] : '') == 1 ? 'selected' : '' }}>出荷未出荷一覧_出荷予定日別</option>
			<option  value="2" {{ old('process_type', isset($searchInfo['process_type']) ? $searchInfo['process_type'] : '') == 2 ? 'selected' : '' }}>出荷未出荷一覧_セット商品別</option>
			<option  value="3" {{ old('process_type', isset($searchInfo['process_type']) ? $searchInfo['process_type'] : '') == 3 ? 'selected' : '' }}>出荷未出荷一覧_SKU別</option>
		</select>
		@include('common.elements.error_tag', ['name' => 'process_type'])
		<label>　</label>
		<input type="submit" class="btn btn-success btn-lg u-mt--sm js_disabled_button" name="output" value="出力" style="margin-bottom : 18px;">
	</div>

</div>
</form>
@endsection