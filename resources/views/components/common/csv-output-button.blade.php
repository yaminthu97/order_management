<div>
	チェックした行を
    <input type="submit" name="submit_csv_output" class="btn btn-default" value="CSV出力">
	<input type="submit" name="submit_csv_bulk_output" class="btn btn-default" value="一覧をすべてCSV出力">
	@include('common.elements.error_tag', ['name' => 'csv_output_error'])
</div>
