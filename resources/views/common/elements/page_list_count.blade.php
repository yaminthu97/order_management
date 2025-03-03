@if(isset($paginator))
@if($paginator->count() > 0)
<!-- 表示件数ここから -->
<select class="form-control c-box--200" name="page_list_count" id="page_list_count" onChange="setNextPage(1)">
@foreach($viewExtendData['page_list_count'] as $pageListCount)
@if(isset($searchRow['page_list_count']) && $searchRow['page_list_count'] == $pageListCount)
<option value="{{$pageListCount}}" selected>{{$pageListCount}}件表示</option>
@else
<option value="{{$pageListCount}}">{{$pageListCount}}件表示</option>
@endif
@endforeach
</select>
<!-- 表示件数ここまで -->
@endif
@endif