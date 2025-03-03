<form name="Form1" method="post" action="" id="search_ami_modal">
{{ csrf_field() }}
	<div class="c-box--1000">
		<table class="table table-bordered c-tbl c-tbl--1000">
		<tbody>
			<tr>
				<th class="c-box--140">ECサイト</th>
				<td class="c-box--220">
					<input type="hidden" name="m_ecs_id" class="form-control c-box--200" value="{{$searchRow['m_ecs_id'] ?? ''}}">
					<span class="ecs_name"></span>
				</td>
				<th class="c-box--140">販売コード</th>
				<td class="c-box--220">
					<input type="text" name="ec_page_cd" class="form-control c-box--200" value="{{$searchRow['ec_page_cd'] ?? ''}}">
				</td>
				<th class="c-box--140">販売名</th>
				<td class="c-box--220">
					<input type="text" name="ec_page_title" class="form-control c-box--200" value="{{$searchRow['ec_page_title'] ?? ''}}">
					<label><input type="checkbox" name="ec_page_title_fuzzy" value="1" {{isset($searchRow['ec_page_title_fuzzy']) && ($searchRow['ec_page_title_fuzzy']=='1') ? 'checked' : ''}}>&nbsp;あいまい検索</label>
				</td>
			</tr>
			<tr>
				<th>説明文</th>
				<td>
					<input type="text" name="page_desc" class="form-control c-box--200" value="{{$searchRow['page_desc'] ?? ''}}">
				</td>
				<th>販売価格</th>
				<td colspan="4" class="form-inline">
					<input type="text" name="sales_price_from" class="form-control c-box--200" value="{{$searchRow['sales_price_from'] ?? ''}}"><span>～</span>
					<input type="text" name="sales_price_to" class="form-control c-box--200" value="{{$searchRow['sales_price_to'] ?? ''}}">
				</td>
			</tr>
		</tbody>
		</table>
	</div>
	<input type="submit" name="submit_search" id="submit_search" class="btn btn-success u-mt--ss action_search_modal_button" value="検索">
	<div  class="c-box--1000">
        @include('common.elements.paginator_header_NoEvent2')
        @include('common.elements.page_list_count_NoEvent')
		<br>
        @if(!empty($paginator->count()) > 0)
		<table id="tbl_cust" class="table table-bordered c-tbl table-link">
			<tr>
                <th class="c-box--80">選択</th>
                <th class="c-box--100">注文数</th>
                <th class="c-box--200">販売コード</th>
                <th class="c-box--200">販売名</th>
                <th class="c-box--100">項目選択肢</th>
                <th class="c-box--100">販売価格</th>
                <th class="c-box--100">販売可能数</th>
                <th class="c-box--200">説明文</th>
			</tr>
			@php
			$idx = $paginator->firstItem();
			$disp_limit_max = \Config::get('Common.const.disp_limit_max');
			$toIidx = $paginator->lastItem() > $disp_limit_max?$disp_limit_max:$paginator->lastItem();
			@endphp
            @foreach($paginator as $elm)
				@php
				if($idx > $toIidx){
					break;
				}
				@endphp
            <tr>
                <td class="u-center"><input type="button" name="btn_select" class="btn btn-default sell_selected_action" data-ami_ec_page_id="{{$elm['m_ami_ec_page_id']}}" value="選択"></td>
                <td><input type="text"  class="form-control u-input--small u-right sell_vol" value=""></td>
                <td>
                    {{$elm['ec_page_cd']}}
                </td>
                <td>
                    {{$elm['ec_page_title']}}
                </td>
                <td></td>
                <td class="u-right">{{$elm['sales_price']}}</td>
				@php
				$sell_limit_stock = 0;
				@endphp
				@foreach($elm['page']['pageSku'] ?? [] as $elm2)
				@php
				if($sell_limit_stock == 0 || $elm2['sku']['sell_limit_stock'] >= $sell_limit_stock){
					$sell_limit_stock = $elm2['sku']['sell_limit_stock'];
				}
				@endphp
				@endforeach
                <td class="u-right">{{$sell_limit_stock}}</td>
                <td>{{$elm['page_desc']}}</td>
            </tr>
				@php
					$idx++;
				@endphp
            @endforeach
		</table>
		@else
		<div>
			該当商品が見つかりません。
		</div>
		@endif
        @include('common.elements.paginator_footer_NoEvent2')
	</div>
	<input type="hidden" name="sorting_column">
	<input type="hidden" name="sorting_shift">
</form>
