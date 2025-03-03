@if(!empty($searchResult['search_record_count']))
	@if(isset($viewExtendData['list_sort']))
		@if($viewExtendData['list_sort']['column_name'] == $columnName)
			@if($viewExtendData['list_sort']['sorting_shift'] == 'desc')
			<a href="javascript:void(0);" onClick="setNextSort('{{$columnName}}', 'asc')" class="th-arrow">
                {{$columnViewName}}▼
			</a>
			@else
			<a href="javascript:void(0);" onClick="setNextSort('{{$columnName}}', 'desc')" class="th-arrow">
				{{$columnViewName}}▲
			</a>
			@endif
		@else
		<a href="javascript:void(0);" onClick="setNextSort('{{$columnName}}', 'asc')" class="th-arrow">
            {{$columnViewName}}
		</a>
        @endif
    @else
    {{$columnViewName}}
    @endif
@else
{{$columnViewName}}
@endif