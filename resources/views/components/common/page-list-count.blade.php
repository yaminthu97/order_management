<!-- 表示件数ここから -->
<select class="form-control c-box--200" name="page_list_count" id="page_list_count" onChange="setNextPage(1)">
    @foreach ($pageCountList as $pageCount)
            <option value="{{ $pageCount }}"
            @selected($pageListCount == $pageCount)>
                {{ $pageCount }}件表示
            </option>
    @endforeach
</select>
<!-- 表示件数ここまで -->
