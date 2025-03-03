<form name="Form1" method="post" action="" id="search_customer_modal">
{{ csrf_field() }}
	<div class="c-box--1000">
		<table class="table table-bordered c-tbl c-tbl--1000">
		<tbody>
			<tr>
				<th class="c-box--140">電話番号</th>
				<td class="c-box--220">
					<input type="text" name="tel" id="tel" class="form-control c-box--200" value="{{$searchRow['tel'] ?? ''}}">
					<label><input type="checkbox" name="tel_forward_match" value="1" {{isset($searchRow['tel_forward_match']) && ($searchRow['tel_forward_match']=='1') ? 'checked' : ''}}>&nbsp;前方一致</label>
				</td>
				<th class="c-box--140">顧客ID</th>
				<td class="c-box--220">
					<input type="text" name="m_cust_id" class="form-control c-box--200" value="{{$searchRow['m_cust_id'] ?? ''}}">
				</td>
				<th class="c-box--140">顧客コード</th>
				<td class="c-box--220">
					<input type="text" name="cust_cd" class="form-control c-box--200" value="{{$searchRow['cust_cd'] ?? ''}}">
				</td>
			</tr>
			<tr>
				<th>名前</th>
				<td>
					<input type="text" name="name_kanji" class="form-control c-box--200" value="{{$searchRow['name_kanji'] ?? ''}}">
					<label><input type="checkbox" name="name_kanji_fuzzy" value="1" {{isset($searchRow['name_kanji_fuzzy']) && ($searchRow['name_kanji_fuzzy']=='1') ? 'checked' : ''}}>&nbsp;あいまい検索</label>
				</td>
				<th>フリガナ</th>
				<td>
					<input type="text" name="name_kana" class="form-control c-box--200" value="{{$searchRow['name_kana'] ?? ''}}">
					<label><input type="checkbox" name="name_kana_fuzzy" value="1" {{isset($searchRow['name_kana_fuzzy']) && ($searchRow['name_kana_fuzzy']=='1') ? 'checked' : ''}}>&nbsp;あいまい検索</label>
				</td>
				<th>メールアドレス</th>
				<td>
					<input type="text" name="email" id="email" class="form-control c-box--200" value="{{$searchRow['email'] ?? ''}}">
				</td>
			</tr>
			<tr>
				<th>郵便番号</th>
				<td>
					<input type="text" name="postal" class="form-control c-box--200" value="{{$searchRow['postal'] ?? ''}}">
				</td>
				<th>都道府県</th>
				<td>
					<select name="address1" class="form-control c-box--200">
						@foreach($viewExtendData['m_prefectures'] as $keyId => $keyValue)
							<option value="{{$keyValue}}" @if (isset($searchRow['address1']) && $searchRow['address1'] == $keyValue){{'selected'}}@endif>{{$keyValue}}</option>
						@endforeach
					</select>
				</td>
				<th>住所</th>
				<td>
					<input type="text" name="address2" class="form-control c-box--200" value="{{$searchRow['address2'] ?? ''}}">
					<label><input name="address2_forward_match" type="checkbox" value="1" {{ isset($searchRow['address2_forward_match']) && ($searchRow['address2_forward_match']=='1')? 'checked' : '' }}>&nbsp;あいまい検索</label>
				</td>
			</tr>
		</tbody>
		</table>
	</div>
	<input type="submit" name="submit_search" id="submit_search" class="btn btn-success u-mt--ss action_search_modal_button" value="検索">

	<div  class="c-box--1100">
        @include('common.elements.paginator_header_NoEvent2')
        @include('common.elements.page_list_count_NoEvent')
		<br>
        @if(!empty($paginator->count()) > 0)
		<table id="tbl_cust" class="table table-bordered c-tbl table-link">
			<tr>
                <th class="c-box--80">選択</th>
				<th class="c-box--145">名前</th>
				<th class="c-box--145">フリガナ</th>
				<th class="c-box--180">@include('common.elements.sorting_column_name_NoEvent', ['columnName' => 'email1', 'columnViewName' => 'メールアドレス']) </th>
				<th class="c-box--100">電話</th>
				<th class="c-box--80">郵便番号</th>
				<th class="c-box--90">@include('common.elements.sorting_column_name_NoEvent', ['columnName' => 'address1', 'columnViewName' => '都道府県']) </th>
				<th class="c-box--200">住所</th>
				<th class="c-box--80">備考</th>
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
				<td class="u-center"><input type="button" name="btn_select" class="btn btn-default billing_customer_selected_action" data-customer-id="{{$cust['m_cust_id']}}" value="選択"></td>
				<td>{{$cust['name_kanji']}}</td>
				<td>{{$cust['name_kana']}}</td>
				<td>{{$cust['email1']}}</td>
				<td>{{$cust['tel1']}}</td>
				<td>{{$cust['postal']}}</td>
				<td>{{$cust['address1']}}</td>
				<td>{{$cust['address2']}}</td>
				<td title="{{$cust['note_min']}}">{{$cust['note']}}</td>
			</tr>
				@php
					$idx++;
				@endphp
            @endforeach
		</table>
		@else
		<div>
			該当顧客が見つかりません。
		</div>
		@endif
        @include('common.elements.paginator_footer_NoEvent2')
	</div>
	<input type="hidden" name="sorting_column">
	<input type="hidden" name="sorting_shift">
</form>
