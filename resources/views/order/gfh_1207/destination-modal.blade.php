<form name="Form1" method="post" action="" id="search_destination_modal">
{{ csrf_field() }}
	<div class="c-box--1000">
		<table class="table table-bordered c-tbl c-tbl--1000">
		<tbody>
			<tr>
				<th class="c-box--140">電話番号</th>
				<td class="c-box--220">
					<input type="text" name="destination_tel" id="tel" class="form-control c-box--200" value="{{$searchRow['destination_tel'] ?? ''}}">
					<label><input type="checkbox" name="destination_tel_forward_match" value="1" {{isset($searchRow['destination_tel_forward_match']) && ($searchRow['destination_tel_forward_match']=='1') ? 'checked' : ''}}>&nbsp;前方一致</label>
				</td>
				<td class="" colspan="4">
				</td>
			</tr>
			<tr>
				<th>名前</th>
				<td>
					<input type="text" name="destination_name" class="form-control c-box--200" value="{{$searchRow['destination_name'] ?? ''}}">
					<label><input type="checkbox" name="destination_name_fuzzy" value="1" {{isset($searchRow['destination_name_fuzzy']) && ($searchRow['destination_name_fuzzy']=='1') ? 'checked' : ''}}>&nbsp;あいまい検索</label>
				</td>
				<th>フリガナ</th>
				<td>
					<input type="text" name="destination_name_kana" class="form-control c-box--200" value="{{$searchRow['destination_name_kana'] ?? ''}}">
					<label><input type="checkbox" name="destination_name_kana_fuzzy" value="1" {{isset($searchRow['destination_name_kana_fuzzy']) && ($searchRow['destination_name_kana_fuzzy']=='1') ? 'checked' : ''}}>&nbsp;あいまい検索</label>
				</td>
				<td colspan="2"></td>
			</tr>
			<tr>
				<th>郵便番号</th>
				<td>
					<input type="text" name="destination_postal" class="form-control c-box--200" value="{{$searchRow['destination_postal'] ?? ''}}">
				</td>
				<th>都道府県</th>
				<td>
					<select name="destination_address1" class="form-control c-box--200">
						<option></option>
						@foreach($viewExtendData['pref']??[] as $value)
							<option value="{{$value['prefectual_name']}}" @if (isset($searchRow['destination_address1']) && $searchRow['destination_address1'] == $value['prefectual_name']){{'selected'}}@endif>{{$value['prefectual_name']}}</option>
						@endforeach
					</select>
				</td>
				<th>住所</th>
				<td>
					<input type="text" name="destination_address2" class="form-control c-box--200" value="{{$searchRow['destination_address2'] ?? ''}}">
					<label><input name="destination_address2_forward_match" type="checkbox" value="1" {{ isset($searchRow['destination_address2_forward_match']) && ($searchRow['destination_address2_forward_match']=='1')? 'checked' : '' }}>&nbsp;あいまい検索</label>
				</td>
			</tr>
		</tbody>
		</table>
	</div>
	<input type="hidden" name="cust_id" value="{{$searchRow['cust_id'] ?? ''}}">
	<input type="submit" name="submit_search" id="submit_search" class="btn btn-success u-mt--ss action_search_modal_button" value="検索">

	<div  class="c-box--1000">
        @include('common.elements.paginator_header_NoEvent2')
        @include('common.elements.page_list_count_NoEvent')
		<br>
        @if(!empty($paginator->count()) > 0)
		<table id="tbl_cust" class="table table-bordered c-tbl table-link nowrap">
			<tr>
                <th class="c-box--80">選択</th>
				<th class="c-box--145">名前</th>
				<th class="c-box--145">フリガナ</th>
				<th class="c-box--100">電話</th>
				<th class="c-box--80">郵便番号</th>
				<th class="c-box--90">@include('common.elements.sorting_column_name_NoEvent', ['columnName' => 'destination_address1', 'columnViewName' => '都道府県']) </th>
				<th class="c-box--200">住所</th>
			</tr>
			@php
			$idx = $paginator->firstItem();
			$disp_limit_max = \Config::get('Common.const.disp_limit_max');
			$toIidx = $paginator->lastItem() > $disp_limit_max?$disp_limit_max:$paginator->lastItem();
			@endphp
            @foreach($paginator as $cust)
				@php
				if($idx > $toIidx){
					break;
				}
				@endphp
			<tr>
				<td><input type="button" name="btn_select" class="btn btn-default destination_selected_action" data-m_destination_id="{{$cust['m_destination_id']}}" value="選択"></td>
				<td>{{$cust['destination_name']}}</td>
				<td>{{$cust['destination_name_kana']}}</td>
				<td>{{$cust['destination_tel']}}</td>
				<td>{{$cust['destination_postal']}}</td>
				<td>{{$cust['destination_address1']}}</td>
				<td>{{$cust['destination_address2']}}</td>
			</tr>
				@php
					$idx++;
				@endphp
            @endforeach
		</table>
		@else
		<div>
			該当送付先が見つかりません。
		</div>
		@endif
        @include('common.elements.paginator_footer_NoEvent2')
	</div>
	<input type="hidden" name="sorting_column">
	<input type="hidden" name="sorting_shift">
</form>
