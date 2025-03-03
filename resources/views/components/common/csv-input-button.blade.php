<div class="u-mt--sl c-box--800">
    <table class="table table-bordered c-tbl c-tbl--800 nowrap">
        <tr>
            <th>{{ $displayName }}取込</th>
        </tr>
        <tr>
            <td>
                <div class="u-mt--sm">
                    <input type="file" class="u-ib" name="csv_input_file" id="csv_input_file" form="Form1">
                    <input type="submit" name="submit_csv_input" class="btn btn-default" value="CSV取込">
                </div>
                @include('common.elements.error_tag', ['name' => 'csv_input_error'])
            </td>
        </tr>
    </table>
</div>
