@if( !empty($paginator->count()) > 0)
	@if(isset($viewExtendData['list_sort']))
		@if( $viewExtendData['list_sort']['column_name'] == $columnName )
			@if($viewExtendData['list_sort']['sorting_shift'] == 'desc')
			<a href="javascript:void(0);" class="next_sort_link" sort_column="{{ $columnName }}" sort_shift="asc" class="th-arrow">
                {{$columnViewName}}▼
			</a>
			@else
			<a href="javascript:void(0);" class="next_sort_link" sort_column="{{ $columnName }}" sort_shift="desc" class="th-arrow">
				{{$columnViewName}}▲
			</a>
			@endif
		@else
			<a href="javascript:void(0);" class="next_sort_link" sort_column="{{ $columnName }}" sort_shift="asc" class="th-arrow">
				{{$columnViewName}}
			</a>
        @endif
    @else
    	{{$columnViewName}}
    @endif
@else
	{{$columnViewName}}
@endif
