{{-- ページ基本情報 --}}
<div class="c-box--960"><p class="c-ttl--02">ページ基本情報</p></div>
<div class="d-table c-box--960">
	<table class="table table-bordered c-tbl c-tbl--960">
		<tr>
			<th class="c-box--300">ページコード</th>
			<td>
				{{$form['page_cd'] ?? ''}}
				<input type="hidden" name="page_cd" value="{{ old('page_cd', $form['page_cd'] ?? '') }}">
				<input type="hidden" name="m_ami_page_id" value="{{ old('m_ami_page_id', $form['m_ami_page_id'] ?? '') }}">
                @include('common.elements.error_tag', ['name' => 'page_cd'])
			</td>
		</tr>
		<tr>
			<th class="c-box--300 must">ページタイトル</th>
			<td>
				<input type="text" class="form-control u-input--full" name="page_title" value="{{ old('page_title', $form['page_title'] ?? '') }}">
                @include('common.elements.error_tag', ['name' => 'page_title'])
			</td>
		</tr>
		<tr>
			<th class="c-box--300 must">販売価格</th>
			<td>
				<input type="number" class="form-control u-input--long" name="sales_price" min="0" value="{{ old('sales_price', $form['sales_price'] ?? '') }}">
                @include('common.elements.error_tag', ['name' => 'sales_price'])
			</td>
		</tr>
		<tr>
			<th class="must">消費税率</th>
            <td>
                <div class="radio-inline">
                    <label><input type="radio" name="tax_rate" value="0.1" {{ old('tax_rate', $form['tax_rate'] ?? 0.1) == 0.1 ? 'checked' : '' }}>10%</label>
                </div>
                <div class="radio-inline">
                    <label><input type="radio" name="tax_rate" value="0.08" {{ old('tax_rate', $form['tax_rate'] ?? 0.1) == 0.08 ? 'checked' : '' }}>8%</label>
                </div>
                @include('common.elements.error_tag', ['name' => 'tax_rate'])
            </td>
		</tr>
		<tr>
			<th class="c-box--300">伝票印刷用ECページ名</th>
			<td>
				<input type="text" class="form-control u-input--full" name="print_page_title" value="{{ old('print_page_title', $form['print_page_title'] ?? '') }}">
                @include('common.elements.error_tag', ['name' => 'print_page_title'])
			</td>
		</tr>
		<tr>
			<th>販売開始日時</th>
			<td>
				<div class="c-box--218" id="amiPageDate">
					<div class="input-group date" id="datetimepicker">
						<input type="text" class="form-control c-box--180" name="sales_start_datetime" value="{{ old('sales_start_datetime', $form['sales_start_datetime'] ?? '') }}">
						<span class="input-group-addon">
							<span class="glyphicon glyphicon-calendar"></span>
						</span>
						<script type="text/javascript">
					$(function () {
					$('#datetimepicker').datetimepicker({
					    format:"YYYY-MM-DD HH:mm"
					});
					});
						</script>
					</div>
				</div>
                @include('common.elements.error_tag', ['name' => 'sales_start_datetime'])
			</td>
		</tr>
		<tr>
			<th class="must">表示区分</th>
            <td>
                <div class="radio-inline">
                    <label><input type="radio" name="search_result_display_flg" value="{{ \App\Enums\DisplayFlg::VISIBLE->value }}" {{ old('search_result_display_flg', $form['search_result_display_flg'] ?? \App\Enums\DisplayFlg::VISIBLE->value) == \App\Enums\DisplayFlg::VISIBLE->value ? 'checked' : '' }}>
                        {{ \App\Enums\DisplayFlg::VISIBLE->label() }}
                    </label>
                </div>
                <div class="radio-inline">
                    <label><input type="radio" name="search_result_display_flg" value="{{ \App\Enums\DisplayFlg::HIDDEN->value }}" {{ old('search_result_display_flg', $form['search_result_display_flg'] ?? \App\Enums\DisplayFlg::VISIBLE->value) == \App\Enums\DisplayFlg::HIDDEN->value ? 'checked' : '' }}>
                        {{ \App\Enums\DisplayFlg::HIDDEN->label() }}
                    </label>
                </div>
                @include('common.elements.error_tag', ['name' => 'search_result_display_flg'])
			</td>
		</tr>
		<tr>
			<th>説明文</th>
			<td id="amiPageDescDiv">
                <textarea id="amiPageDesc"  class="form-control u-input--full" name="page_desc" rows="10" style="resize: vertical;">{{ old('page_desc', $form['page_desc'] ?? '') }}</textarea>
                @include('common.elements.error_tag', ['name' => 'page_desc'])
			</td>
		</tr>
        <tr>
			<th>商品画像</th>
            <td>
                <input type="file" id="fileInput" name="product_img" accept=".jpg,.png" style="display: none;" onchange="selectAmiPageImg(this)">
                <button type="button" class="btn btn-light btn-sm" style="border:1px solid #cbd3db;" onclick="document.getElementById('fileInput').click()">ファイルを選択</button>
                <span id="imgNameDisplay" style="margin: 14px">
                    @php
                        $imagePath = old('image_path', $form['image_path'] ?? '');
                        $isS3Exist = false;
                        if ($imagePath) {
                            $s3Path = $form['resourcesDir'].'/'.$form['accountCode'] . '/image/page/' . $form['m_ami_page_id'] . '/' . $imagePath;
                            try {
                                $imageUrl = Storage::disk('s3')->url($s3Path);

                                // Check existence of the file
                                $isS3Exist = Storage::disk('s3')->exists($s3Path);
                            } catch (\Exception $e) {
                                // Log the error for debugging
                                Log::error(__('messages.error.upload_s3_failed'));
                            }
                        }
                    @endphp
                    @if ($imagePath && $isS3Exist)
                        <a href="{{ $imageUrl }}" target="_blank">{{ $imagePath }}</a>
                    @else
                        未登録
                    @endif
                </span>
                <button type="button" class="btn btn-danger btn-sm" id="imgDeleteBtn" style="display: {{ ($imagePath  && $isS3Exist) ? 'inline-block' : 'none' }};"  onclick="deleteAmiPageImg(event)">削除</button>
                <input type="hidden" name="is_delete_ami_page_img" value="" id="is_delete_ami_page_img">
                <div class="imagePreviewContainer" style="margin: 8px 0px 5px 0px;">
                    @if ($imagePath && $isS3Exist)
                        <img id="imagePreview"
                            src="{{ route('ami.page.image', ['m_ami_page_id' => $form['m_ami_page_id']]) }}"
                            style="max-width: 150px; display: inline-block;">
                    @else
                        <img id="imagePreview" style="display: none; max-width: 150px;">
                    @endif
                </div>
                @include('common.elements.error_tag', ['name' => 'product_img'])
            </td>

		</tr>
        <tr>
			<th>付属品</th>
			<td>
            	<table class="table table-bordered c-tbl">
                    <thead>
                        <tr>
                            <th>グループ</th>
                            <th>カテゴリ</th>
                            <th>コード</th>
                            <th class="text-left">名称</th>
                            <th class="text-right">数量</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="attachmentTable">
                        @if(!empty($form['ami_attachment']))
                            @foreach($form['ami_attachment'] as $key => $attachment)
                                @php $key++; @endphp
                                <tr class="attachment_row_{{$key}}">
                                    <input type="hidden" name="m_ami_page_attachment_item_id_{{$key}}" value="{{ old('m_ami_page_attachment_item_id', $attachment['m_ami_page_attachment_item_id'] ?? '') }}" id="m_ami_attachment_item_id_1">
                                    <input type="hidden" name="m_ami_attachment_item_id_{{$key}}" value="{{ old('m_ami_page_attachment_item_id', $attachment['m_ami_page_attachment_item_id'] ?? '') }}" id="m_ami_attachment_item_id_1">
                                    <td id="attachment_item_group_id_1" name="attachment_item_group_id_{{$key}}" class="c-box--100">
                                        <select name="attachment_item_group_id_{{$key}}" class="form-control">
                                            @foreach($form['attachment_group'] as $groupName => $groupId)
                                                <option value="{{$groupId}}" @if(isset($attachment['group_id']) && $attachment['group_id'] == $groupId) {{'selected'}} @endif >
                                                    {{$groupName}}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="attachment_item_category_name_{{$key}}" value="{{ old('attachment_item_name', $attachment['attachment_item_category_name'] ?? '') }}" class="attachment_input" id="attachment_item_category_name_1">
                                        <input type="hidden" name="attachment_item_category_id_{{$key}}" value="{{ old('category_id', $attachment['category_id'] ?? '') }}" id="attachment_item_category_id_1">
                                    </td>
                                    <td>
                                        <input type="text" name="attachment_item_cd_{{$key}}" value="{{ old('attachment_item_cd', $attachment['attachment_item_cd'] ?? '') }}" class="attachment_input" id="attachment_item_cd_1">
                                    </td>
                                    <td class="c-box--100">
                                        <input type="text" name="attachment_item_name_{{$key}}" value="{{ old('attachment_item_name', $attachment['attachment_item_name'] ?? '') }}" class="attachment_input" id="attachment_item_name_1">
                                    </td>
                                    <td class="c-box--100">
                                        <input type="number" name="attachment_item_vol_{{$key}}" value="{{ old('item_vol', $attachment['item_vol'] ?? '') }}" class="form-control u-input--small" id="attachment_item_vol_1">
                                    </td>
                                    <td>
                                        <button class="btn btn-danger btn-sm" type="button" id="deleteBtn_{{$key}}" del-data-id="{{ old('m_ami_page_attachment_item_id', $attachment['m_ami_page_attachment_item_id'] ?? '') }}">削除</button>
                                    </td>
                                </tr>
                            @endforeach
                            {{-- want to increase row from the actual record row --}}
                            <?php $attachmentCount = count($form['ami_attachment']) + 1  ?>
                            <tr class="attachment_row_{{$attachmentCount}}">
                                <input type="hidden" name="m_ami_attachment_item_id_{{$attachmentCount}}" id="m_ami_attachment_item_id_{{$attachmentCount}}">
                                <td name="attachment_item_group_id_{{$attachmentCount}}" id="attachment_item_group_id_{{$attachmentCount}}" class="c-box--100"></td>
                                <td>
                                    <input type="text" name="attachment_item_category_name_{{$attachmentCount}}" class="attachment_input" id="attachment_item_category_name_{{$attachmentCount}}">
                                    <input type="hidden" name="attachment_item_category_id_{{$attachmentCount}}" id="attachment_item_category_id_{{$attachmentCount}}">
                                </td>
                                <td>
                                    <input type="text" name="attachment_item_cd_{{$attachmentCount}}" class="attachment_input" id="attachment_item_cd_{{$attachmentCount}}">
                                </td>
                                <td class="c-box--100">
                                    <input type="text" name="attachment_item_name_{{$attachmentCount}}" class="attachment_input" id="attachment_item_name_{{$attachmentCount}}">
                                </td>
                                <td class="c-box--100">
                                    <input type="number" name="attachment_item_vol_{{$attachmentCount}}" class="form-control u-input--small" id="attachment_item_vol_{{$attachmentCount}}">
                                </td>
                                <td>
                                    <button class="btn btn-success btn-sm action_attachment_search" data-url="{{ route('order.api.attachment_item.list') }}" id="addBtn_{{$attachmentCount}}" type="button">追加</button>
                                    <button class="btn btn-danger btn-sm" type="button" id="deleteBtn_{{$attachmentCount}}" style="display: none;">削除</button>
                                </td>
                            </tr>
                        @else
                            <tr class="attachment_row_1">
                                <input type="hidden" name="m_ami_attachment_item_id_1" id="m_ami_attachment_item_id_1">
                                <td name="attachment_item_group_id_1" id="attachment_item_group_id_1" class="c-box--100"></td>
                                <td>
                                    <input type="text" name="attachment_item_category_name_1" class="attachment_input" id="attachment_item_category_name_1">
                                    <input type="hidden" name="attachment_item_category_id_1" id="attachment_item_category_id_1">
                                </td>
                                <td>
                                    <input type="text" name="attachment_item_cd_1" class="attachment_input" id="attachment_item_cd_1">
                                </td>
                                <td class="c-box--100">
                                    <input type="text" name="attachment_item_name_1" class="attachment_input" id="attachment_item_name_1">
                                </td>
                                <td class="c-box--100">
                                    <input type="number" name="attachment_item_vol_1" class="form-control u-input--small" id="attachment_item_vol_1">
                                </td>
                                <td>
                                    <button class="btn btn-success btn-sm action_attachment_search" data-url="{{ route('order.api.attachment_item.list') }}" id="addBtn_1" type="button">追加</button>
                                    <button class="btn btn-danger btn-sm" type="button" id="deleteBtn_1" style="display: none;">削除</button>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
			</td>
            <input type="hidden" name="deleted_attachment_ids" id="deleted_attachment_ids">
		</tr>

	</table>
    <div id="dialogWindow" style="display: none">
        <div class="dialog_body"></div>
    </div>

</div>
@push('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-bs4.min.css" rel="stylesheet">
<link rel="stylesheet" href="{{ esm_internal_asset('css/ami/gfh_1207/app.css') }}">

@endpush

@push('js')
<script type="text/javascript">
    // 付属品 append row
    $(document).on('click', '.attachment_item_selected_action', function () {
        // 付属品検索ダイアログ選択ボタン押下時イベント
        $.ajax({
            url: '/gfh/order/api/attachment_item/' + $(this).attr('data-attachment_item_id'),
            method: 'GET',
            headers: {
                'Authorization': $('input[name="_token"]').val()
            },
            dataType: 'json',
            success: function (json) {
                // get initial tr row item
                const tbody = document.getElementById("attachmentTable");   // get tbody
                const lastRow = tbody.querySelector("tr:last-child");       // get the last tr row
                const lastClassName = lastRow.className;    // get className of last tr row
                const lastNumber = parseInt(lastClassName.split("_").pop(), 10); // get number from className
                // get last row of 追加, 削除 button
                const addButton = document.getElementById(`addBtn_${lastNumber}`);
                const deleteButton = document.getElementById(`deleteBtn_${lastNumber}`);
                // data row is created, 追加 button is hide, 削除 button is show
                addButton.style.display = "none";
                deleteButton.style.display = "inline-block";

                // get current row of parent tag (tr)
                const attachmentRow = addButton.parentElement.parentElement;
                const attachmentRowClass = attachmentRow.className;

                // get category list from controller data
                const categoryList = @json($form['attachment_category']);
                const invertedCategoryList = Object.fromEntries(
                    Object.entries(categoryList).map(([key, value]) => [value, key])
                );

                // get group list from controller data (for select box - グループ )
                const groupList = @json($form['attachment_group']);
                const invertedGroupList = Object.fromEntries(
                    Object.entries(groupList).map(([key, value]) => [value, key])
                );
                // create select box - グループ
                let selectHTML = `<select name="attachment_item_group_id_${lastNumber}" class="form-control">`;
                Object.entries(invertedGroupList).forEach(([key, value]) => {
                    selectHTML += `<option value="${key}">${value}</option>`;
                });
                selectHTML += `</select>`;
                document.getElementById(`attachment_item_group_id_${lastNumber}`).innerHTML = selectHTML;

                // fill data in <tr> row, use data from the selected ( 付属品 ) modal box
                $(`#m_ami_attachment_item_id_${lastNumber}`).val(json.m_ami_attachment_item_id);
                $(`#attachment_item_category_name_${lastNumber}`).val(invertedCategoryList[json.category_id]);
                $(`#attachment_item_category_id_${lastNumber}`).val(json.category_id);
                $(`#attachment_item_cd_${lastNumber}`).val(json.attachment_item_cd);
                $(`#attachment_item_name_${lastNumber}`).val(json.attachment_item_name);
                $(`#attachment_item_vol_${lastNumber}`).val(1);

                // after data bind in initial row, create new row
                const newNumber = lastNumber + 1;
                const newRow = document.createElement("tr");
                newRow.className = `attachment_row_${newNumber}`;
                newRow.innerHTML = `
                    <tr class="attachment_row_${newNumber}">
                        <input type="hidden" name="m_ami_attachment_item_id_${newNumber}" id="m_ami_attachment_item_id_${newNumber}">
                        <td id="attachment_item_group_id_${newNumber}" name="attachment_item_group_id_${newNumber}" class="c-box--100"></td>
                        <td>
                            <input type="text" name="attachment_item_category_name_${newNumber}" id="attachment_item_category_name_${newNumber}" class="attachment_input">
                            <input type="hidden" name="attachment_item_category_id_${newNumber}" id="attachment_item_category_id_${newNumber}" class="attachment_input">
                        </td>
                        <td>
                            <input type="text" name="attachment_item_cd_${newNumber}" id="attachment_item_cd_${newNumber}" class="attachment_input">
                        </td>
                        <td class="c-box--100">
                            <input type="text" name="attachment_item_name_${newNumber}" id="attachment_item_name_${newNumber}" class="attachment_input">
                        </td>
                        <td class="c-box--100">
                            <input type="number" name="attachment_item_vol_${newNumber}" id="attachment_item_vol_${newNumber}" class="form-control u-input--small">
                        </td>
                        <td>
                            <button class="btn btn-success btn-sm action_attachment_search" data-url="{{ route('order.api.attachment_item.list') }}" id="addBtn_${newNumber}" type="button">追加</button>
                            <button class="btn btn-danger btn-sm" type="button" id="deleteBtn_${newNumber}" style="display: none;">削除</button>
                        </td>
                    </tr>
                `;
                tbody.appendChild(newRow);
                // 付属品 dialog is close
                $('#dialogWindow').dialog('close');
            },
            error: function (xhr, status, error) {
                alert("付属品情報取得APIの呼び出しに失敗しました。");
            }
        });
    });

</script>

<script src="{{ esm_internal_asset('js/ami/gfh_1207/app.js') }}"></script>
<script src="{{ esm_internal_asset('js/ami/gfh_1207/summernote-bs4.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/lang/summernote-ja-JP.min.js"></script>
@endpush


