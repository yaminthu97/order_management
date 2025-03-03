<!-- ソート用部品ここから -->
<input type="hidden" name="sorting_column" id="sorting_column" value="{{ isset($viewExtendData['list_sort']['column_name']) ? $viewExtendData['list_sort']['column_name'] : '' }}">
<input type="hidden" name="sorting_shift" id="sorting_shift" value="{{ isset($viewExtendData['list_sort']['sorting_shift']) ? $viewExtendData['list_sort']['sorting_shift'] : '' }}">
<script>
function setNextSort($sortColumn, $sortShift)
{
	document.getElementById("sorting_column").value = $sortColumn;

	document.getElementById("sorting_shift").value = $sortShift;

	//document.Form1.submit();
    const form = document.getElementById("Form1");
    HTMLFormElement.prototype.submit.call(form);

	return false;
}
</script>
<!-- ソート用部品ここまで -->