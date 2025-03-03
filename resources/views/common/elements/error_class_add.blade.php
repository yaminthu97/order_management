@if(!empty($errorResult))
<script>
window.addEventListener('load', function(){
	@foreach($errorResult as $columnName => $errorMessage)
		document.getElementById("{{$columnName}}").classList.add("error-txtfield");
	@endforeach
})
</script>
@endif
@section('message')
@if(!empty($errorResult) || !empty($viewMessage))
<div class="c-box--1200 c-tbl-border-all u-p--sm sy_notice u-mb--ss">
@if(!empty($errorResult))
<p class="icon_sy_notice_01">＜異常＞入力にエラーがあります。</p>
@endif
@if(!empty($viewMessage))
	@foreach($viewMessage as $message)
		<p class="icon_sy_notice_03">{{$message}}</p>
	@endforeach
@endif
</div><!--/sy_notice-->
@endif
@if(!empty($searchResult['search_record_count']))
	@if($searchResult['search_record_count'] < $searchResult['total_record_count'])
	<div class="c-box--1200 c-tbl-border-all u-p--sm sy_notice u-mb--ss">
		<p class="icon_sy_notice_02">検索結果の件数が表示可能な件数を超えています。</p>
	</div><!--/sy_notice-->
	@endif
@endif
@endsection
