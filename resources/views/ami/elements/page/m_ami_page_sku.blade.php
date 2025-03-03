{{-- SKU情報 --}}
<div class="c-box--960"><p class="c-ttl--02">SKU情報</p></div>
<div class="d-table c-box--960">
	<table class="table table-bordered c-tbl c-tbl--960">
		<tr>
            <th class="c-box--300">SKUコード</th>
            <td>
                <a href="{{ \Config::get('const.action_url') }}sku/edit/{{ old('m_ami_sku_id', $form['m_ami_sku_id'] ?? '') }}" target="_blank">
                    {{ old('sku_cd', $form['sku_cd'] ?? '') }}
                    <i class="fas fa-external-link-alt"></i>
                </a>
            </td>
        </tr>
        <tr>
            <th class="c-box--300">SKU名</th>
            <td>{{ old('sku_name', $form['sku_name'] ?? '') }}</td>
        </tr>
		{{-- 単品の場合は数量を 1 に設定する --}}
		<input hidden type="text" name="sku_vol" value="1">
        <input type="hidden" name="sku_cd" value="{{ old('sku_cd', $form['sku_cd'] ?? '') }}">
		<input hidden type="text" name="m_ami_sku_id" value="{{ old('m_ami_sku_id', $form['m_ami_sku_id'] ?? '') }}">
	</table>
</div>
