{{-- 備考情報 --}}
<div class="c-box--960">
	<div class="c-btn--02"><a data-toggle="collapse" href="#collapse-menu-variation-etc" class="collapsed">熨斗設定</a></div>
	<!-- 詳細アコーディオンここから -->
	<div class="collapse" id="collapse-menu-variation-etc">
        <table class="table table-bordered c-tbl c-tbl--960 link-style">
            <tr>
                <th style="background: #eee;">熨斗タイプ</th>
                <td style="background: #eee;">熨斗種類</td>
            </tr>
    		@if(!empty($form['noshi_format']))
                @foreach($form['noshi_format'] as $formats)
                    @php
                        $selectedFormatId = '';
                        $amiNoshiId = '';
                        if($form['mode'] == "edit") {
                            foreach ($form['ami_noshi'] as $ami) {
                                if (isset($formats[0]['m_noshi_id']) && $ami['m_noshi_id'] == $formats[0]['m_noshi_id']) {
                                    $selectedFormatId = $ami['m_noshi_format_id'];
                                    $amiNoshiId = $ami['m_ami_page_noshi_id'];
                                    break;
                                }
                            }
                        }
                    @endphp
                    <tr>
                        <th class="c-box--300">
                            <input type="text" name="noshi_type_{{ $formats[0]['m_noshi_id'] }}" value="{{ $formats[0]['noshi_type'] }}" class="attachment_input">
                            <input type="hidden" name="m_noshi_id_{{ $formats[0]['m_noshi_id'] }}" value="{{ $formats[0]['m_noshi_id'] }}" class="attachment_input">
                            <input type="hidden" name="m_ami_page_noshi_id_{{ $formats[0]['m_noshi_id'] }}" value="{{ !empty($amiNoshiId) ? $amiNoshiId : $formats[0]['m_noshi_id'] }}" class="attachment_input">
                            <input type="hidden" name="old_m_ami_page_noshi_id_{{ $formats[0]['m_noshi_id'] }}" value="{{ $amiNoshiId ? 'old' : 'new'}}" class="attachment_input">
                        </th>
                        <td>
                            <select name="m_noshi_format_id_{{ $formats[0]['m_noshi_id'] }}" class="form-control c-box--200">
                                <option value=""></option>
                               @foreach($formats as $format)
                                    <option value="{{ $format['m_noshi_format_id'] }}" {{ $format['m_noshi_format_id'] == $selectedFormatId ? 'selected' : '' }} >
                                        {{ $format['noshi_format_name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="2" style="margin: 0; text-align: center;">
                        熨斗種類データが見つかりません。
                    </td>
                </tr>
            @endif
        </table>

	</div>
</div>

