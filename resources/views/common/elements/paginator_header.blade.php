<!-- ページャーここから -->
@if(isset($paginator))
<input type="hidden" name="hidden_next_page_no" id="hidden_next_page_no" value="{{$paginator->currentPage()}}">
<script>
function setNextPage($page)
{
	document.getElementById("hidden_next_page_no").value = $page;

	//document.Form1.submit();
    const form = document.getElementById("Form1");
    HTMLFormElement.prototype.submit.call(form);

	return false;

}
</script>
@include('common.elements.paginator_main')
@endif
<!-- ページャーここまで -->