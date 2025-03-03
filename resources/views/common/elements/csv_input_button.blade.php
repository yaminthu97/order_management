<div class="u-mt--sl c-box c-tbl-border-all c-box--full">
<table class="table table-bordered c-tbl c-tbl--full nowrap">
<tr>
<th>
    {{-- {{$csvName or ''}} --}}
    顧客取込
</th>
</tr>

</table>

<div  class="u-p--ss">
    <div class="u-mt--sm">
        <input type="file" class="u-ib" name="csv_input_file" id="csv_input_file" form="Form1">
        <input type="submit" name="submit_csv_input" class="btn btn-default" value="CSV取込"></div>
        @include('common.elements.error_tag', ['name' => 'csv_input_error'])
    </div>
</div>