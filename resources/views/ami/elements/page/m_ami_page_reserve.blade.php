

<div class="d-table c-box--960">
	<table class="table table-bordered c-tbl c-tbl--960">
        <tr>
            <th class="c-box--300">自由項目１</th>
            <td>
                <input type="text" class="form-control u-input--full" name="remarks1" value="{{ old('remarks1', $form['remarks1'] ?? '') }}">
                @include('common.elements.error_tag', ['name' => 'remarks1'])
            </td>
        </tr>
        <tr>
            <th class="c-box--300">自由項目２</th>
            <td>
                <input type="text" class="form-control u-input--full" name="remarks2" value="{{ old('remarks2', $form['remarks2'] ?? '') }}">
                @include('common.elements.error_tag', ['name' => 'remarks2'])
            </td>
        </tr>
        <tr>
            <th class="c-box--300">自由項目３</th>
            <td>
                <input type="text" class="form-control u-input--full" name="remarks3" value="{{ old('remarks3', $form['remarks3'] ?? '') }}">
                @include('common.elements.error_tag', ['name' => 'remarks3'])
            </td>
        </tr>
        <tr>
            <th class="c-box--300">自由項目４</th>
            <td>
                <input type="text" class="form-control u-input--full" name="remarks4" value="{{ old('remarks4', $form['remarks4'] ?? '') }}">
                @include('common.elements.error_tag', ['name' => 'remarks4'])
            </td>
        </tr>
        <tr>
            <th class="c-box--300">自由項目５</th>
            <td>
                <input type="text" class="form-control u-input--full" name="remarks5" value="{{ old('remarks5', $form['remarks5'] ?? '') }}">
                @include('common.elements.error_tag', ['name' => 'remarks5'])
            </td>
        </tr>
    </table>
</div>
