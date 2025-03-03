<form name="Form1" method="post" action="" id="search_attachment_item_modal" data-url="{{ route('order.api.attachment_item.list') }}">
{{ csrf_field() }}
	<div class="c-box--1000">
		<table class="table table-bordered c-tbl c-tbl--1000">
		<tbody>
			<tr>
				<th class="c-box--140">カテゴリ</th>
				<td class="c-box--220">
					<select name="category_id" class="form-control c-box--200">
						<option value=""></option>
						@foreach($viewExtendData['category_list']??[] as $keyId => $keyValue)
							<option value="{{$keyId}}" @if (isset($searchRow['category_id']) && $searchRow['category_id'] == $keyValue){{'selected'}}@endif>{{$keyValue}}</option>
						@endforeach
					</select>
				</td>
				<th class="c-box--140">付属品コード</th>
				<td class="c-box--220">
					<input type="text" name="attachment_item_cd" class="form-control c-box--200" value="{{$searchRow['attachment_item_cd'] ?? ''}}">
				</td>
				<th class="c-box--140">付属品名称</th>
				<td class="c-box--220">
					<input type="text" name="attachment_item_name" class="form-control c-box--200" value="{{$searchRow['attachment_item_name'] ?? ''}}">
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
		<table id="tbl_cust" class="table table-bordered c-tbl table-link c-tbl--1000">
            <tr>
                <th class="c-box--80 text-center">選択</th>
                <th class="c-box--140 text-center">カテゴリ</th>
                <th class="c-box--150">付属品コード</th>
                <th class="c-box--200">付属品名称</th>
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
                    <td><input type="button" name="btn_select" class="btn btn-default attachment_item_selected_action" data-attachment_item_id="{{$elm['m_ami_attachment_item_id']}}" value="選択"></td>
                    <td>
                        {{$viewExtendData['category_list'][$elm['category_id']]}}
                    </td>
                    <td>
                        {{$elm['attachment_item_cd']}}
                    </td>
                    <td>
                        {{$elm['attachment_item_name']}}
                    </td>
                </tr>
				@php
					$idx++;
				@endphp
            @endforeach
		</table>
		@else
		<div>
			該当付属品が見つかりません。
		</div>
		@endif
        @include('common.elements.paginator_footer_NoEvent2')
	</div>
	<input type="hidden" name="group_id">
	<input type="hidden" name="sorting_column">
	<input type="hidden" name="sorting_shift">
</form>
