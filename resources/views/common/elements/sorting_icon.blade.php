@if(!empty($searchResult['search_record_count']))
	@if(isset($viewExtendData['list_sort']))
		<a href="javascript:void(0);" onClick="setNextSort('{{$columnName}}', 'asc')" class="th-arrow"><img src="{{config('env.design_path')}}v1_0/images/common/table_arrow_asc.png" alt></a>
		<a href="javascript:void(0);" onClick="setNextSort('{{$columnName}}', 'desc')" class="th-arrow"><img src="{{config('env.design_path')}}v1_0/images/common/table_arrow_desc.png" alt></a>
	@endif
@endif