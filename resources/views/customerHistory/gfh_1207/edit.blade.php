{{-- NECSM0121:顧客対応履歴登録・修正 --}}
@php
    $ScreenCd = 'NECSM0121';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '顧客対応履歴登録・修正')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
    <li>顧客対応履歴登録・修正</li>
@endsection
@section('content')
    <div id="dialogWindow" style="display: none">
        <div class="dialog_body"></div>
    </div>

    @include('common.elements.datetime_picker_script')
    @if (!empty($viewMessage))
        <div class="c-box--1700 c-tbl-border-all u-p--sm sy_notice u-mb--ss">
            @foreach ($viewMessage as $message)
                <p class="icon_sy_notice_03">{{ $message }}</p>
            @endforeach
        </div><!--/sy_notice-->
    @endif
    <form method="POST" action="" name="Form1" id="Form1">
        {{ csrf_field() }}
        <div>
            <table class="table table-bordered c-tbl c-tbl--960">
                @if (isset($editRow->t_cust_communication_id))
                    <tr>
                        <th class="c-box--200">対応履歴ID</th>
                        <td>
                            <input class="form-control" type="text" name="t_cust_communication_id"
                                id="t_cust_communication_id"
                                value="{{ old('t_cust_communication_id', $editRow->t_cust_communication_id ?? '') }}"
                                readonly />
                            @include('common.elements.error_tag', ['name' => 't_cust_communication_id'])
                        </td>
                    </tr>
                @endif
                <tr>
                    <th class="c-box--200">顧客ID</th>

                    <td>
                        <input type="text" name="m_cust_id" id="m_cust_id" class="form-control u-input--mid"
                            value="{{ old('m_cust_id', $editRow->m_cust_id ?? '') }}">&nbsp;
                        <button class="btn btn-default action_billing_search" type="button">顧客を検索する</button><br>
                        @include('common.elements.error_tag', ['name' => 'm_cust_id'])
                    </td>

                </tr>
                <tr>
                    <th class="c-box--200">受注ID</th>
                    <td>
                        <input class="form-control" type="text" name="t_order_hdr_id" id="t_order_hdr_id"
                            value="{{ old('t_order_hdr_id', $editRow->t_order_hdr_id ?? '') }}" />
                        @include('common.elements.error_tag', ['name' => 't_order_hdr_id'])
                    </td>
                </tr>
                <tr>
                    <th class="col-xs-2">商品ページコード</th>
                    <td class="col-xs-4">
                        <input class="form-control" type="text" name="page_cd" id="page_cd"
                            value="{{ old('page_cd', $editRow->page_cd ?? '') }}" />
                        @include('common.elements.error_tag', ['name' => 'page_cd'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">名前</th>
                    <td>
                        <input class="form-control" type="text" name="name_kanji" id="name_kanji"
                            value="{{ old('name_kanji', $editRow->name_kanji ?? '') }}" />
                        @include('common.elements.error_tag', ['name' => 'name_kanji'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">フリガナ</th>
                    <td>
                        <input class="form-control" type="text" name="name_kana" id="name_kana"
                            value="{{ old('name_kana', $editRow->name_kana ?? '') }}" />
                        @include('common.elements.error_tag', ['name' => 'name_kana'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">電話番号</th>
                    <td class="flex-item">
                        <div>
                            <input class="form-control" type="text" name="tel" id="tel"
                                value="{{ old('tel', $editRow->tel ?? '') }}" oninput="validatePhoneNumber()" />
                            @include('common.elements.error_tag', ['name' => 'tel'])

                        </div>
                        <a href="#" class="btn CTI_btn btn-success btn-md ml-20" type="submit" name="submit"
                            id="submit_CTI_call" value="CTI_call" onclick="makeCall(event)">発信</a>
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">メールアドレス</th>
                    <td>
                        <input class="form-control" type="text" name="email" id="email"
                            value="{{ old('email', $editRow->email ?? '') }}" />
                        @include('common.elements.error_tag', ['name' => 'email'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">郵便番号</th>
                    <td>
                        <input class="form-control" type="text" name="postal" id="postal"
                            value="{{ old('postal', $editRow->postal ?? '') }}" />
                        @include('common.elements.error_tag', ['name' => 'postal'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">都道府県</th>
                    <td>
                        <select name="address1" id="address1" class="form-control c-box--200">
                            <option value=""></option>
                            @foreach ($viewExtendData['prefectuals'] as $row)
                                @php($prefValue = $row['prefectual_name'])
                                @php($prefName = $row['prefectual_name'])
                                <option value="{{ $row['prefectual_name'] }}"
                                    {{ old('address1', $editRow->address1 ?? '') == $prefValue ? 'selected' : '' }}>
                                    {{ $prefName }}
                                </option>
                            @endforeach
                        </select>
                        @include('common.elements.error_tag', ['name' => 'address1'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200 col-xs-2">住所</th>
                    <td class="col-xs-4">
                        <input class="form-control" type="text" id="address2" name="address2"
                            value="{{ old('address2', $editRow->address2 ?? '') }}" />
                        @include('common.elements.error_tag', ['name' => 'address2'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">連絡先その他</th>
                    <td>
                        <input class="form-control" type="text" name="note"
                            value="{{ old('note', $editRow->note ?? '') }}" />
                        @include('common.elements.error_tag', ['name' => 'note'])
                    </td>
                </tr>
                <tr>
                    <th class="must c-box--200">タイトル</th>
                    <td>
                        <input class="form-control" type="text" name="title" id="title"
                            value="{{ old('title', $editRow->title ?? '') }}" />
                        @include('common.elements.error_tag', ['name' => 'title'])
                    </td>
                </tr>
                <tr>
                    <th class="col-xs-2">販売窓口</th>
                    <td class="col-xs-4">
                        <select name="sales_channel" class="form-control c-box--200">
                            <option value=""></option>
                            @foreach ($viewExtendData['salesChannel'] as $tableIdName1 => $tableIdValue1)
                                <option value="{{ $tableIdValue1 }}"
                                    {{ old('sales_channel', isset($editRow->sales_channel) ? $editRow->sales_channel : '') == $tableIdValue1 ? 'selected' : '' }}>
                                    {{ $tableIdName1 }}
                                </option>
                            @endforeach
                        </select>
                        @include('common.elements.error_tag', ['name' => 'sales_channel'])
                    </td>
                </tr>
                <tr>
                    <th class="col-xs-2">問合せ内容種別</th>
                    <td class="col-xs-4">
                        <select name="inquiry_type" class="form-control c-box--200">
                            <option value=""></option>
                            @foreach ($viewExtendData['inquiryType'] as $tableIdName1 => $tableIdValue1)
                                <option value="{{ $tableIdValue1 }}"
                                    {{ old('inquiry_type', isset($editRow->inquiry_type) ? $editRow->inquiry_type : '') == $tableIdValue1 ? 'selected' : '' }}>
                                    {{ $tableIdName1 }}
                                </option>
                            @endforeach
                        </select>
                        @include('common.elements.error_tag', ['name' => 'inquiry_type'])
                    </td>
                </tr>
                <tr>
                    <th class="must c-box--200">公開</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" id="open" name="open" value="1"
                                {{ old('open', $editRow['open'] ?? null) === null ? 'checked' : (old('open', $editRow['open'] ?? null) == \App\Modules\Customer\Gfh1207\Enums\OpenFlg::PUBLISH->value ? 'checked' : '') }}>
                            {{ \App\Modules\Customer\Gfh1207\Enums\OpenFlg::PUBLISH->label() }}
                        </label>

                        <label class="radio-inline">
                            <input type="radio" id="open" name="open" value="0"
                                {{ old('open', $editRow['open'] ?? null) === null ? '' : (old('open', $editRow['open'] ?? null) == \App\Modules\Customer\Gfh1207\Enums\OpenFlg::NOT_PUBLISH->value ? 'checked' : '') }}>
                            {{ \App\Modules\Customer\Gfh1207\Enums\OpenFlg::NOT_PUBLISH->label() }}
                        </label>

                        @include('common.elements.error_tag', ['name' => 'open'])
                    </td>
                </tr>
                @if (isset($editRow->t_cust_communication_id))
                    @if (count($custCommunicationDtl) > 0)
                        <tr>
                            <td colspan="2">
                                @foreach ($custCommunicationDtl as $detail)
                                    <div class="collapse-item">
                                        <div class="c-btn--02 u-mt--sl">
                                            <a data-toggle="collapse"
                                                href="#collapse-menu{{ $detail->t_cust_communication_dtl_id }}"
                                                class="collapsed collapsed-link">対応日：{{ (new Carbon\Carbon($detail->update_timestamp))->format('Y/m/d H:i:s') }}</a>
                                        </div>
                                        <div class="collapse"
                                            id="collapse-menu{{ $detail->t_cust_communication_dtl_id }}">
                                            <input type="hidden" name="t_cust_communication_dtl_id"
                                                value="{{ $detail->t_cust_communication_dtl_id }}">
                                            <table class="table table-bordered c-tbl c-tbl--full">
                                                <tr>
                                                    <th class="col-xs-2">連絡方法</th>
                                                    <td>
                                                        @foreach ($viewExtendData['contactWayTypes'] as $tableIdName1 => $tableIdValue1)
                                                            @if ($detail->contact_way_type == $tableIdValue1)
                                                                <p>{{ $tableIdName1 }}</p>
                                                            @endif
                                                        @endforeach
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="col-xs-2">ステータス</th>
                                                    <td>
                                                        @foreach ($viewExtendData['statusList'] as $tableIdName1 => $tableIdValue1)
                                                            @if ($detail->status == $tableIdValue1)
                                                                <p>{{ $tableIdName1 }}</p>
                                                            @endif
                                                        @endforeach
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="col-xs-2">分類</th>
                                                    <td>
                                                        @foreach ($viewExtendData['categoryList'] as $tableIdName1 => $tableIdValue1)
                                                            @if ($detail->category == $tableIdValue1)
                                                                <p>{{ $tableIdName1 }}</p>
                                                            @endif
                                                        @endforeach
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="col-xs-2">受信内容</th>
                                                    <td>
                                                        {{ $detail->receive_detail }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="col-xs-2">受信日時</th>
                                                    <td>
                                                        {{ (new Carbon\Carbon($detail->receive_datetime))->format('Y/m/d') }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="col-xs-2">受信者</th>
                                                    <td>
                                                        @foreach ($viewExtendData['operatorNameList'] as $tableIdName1 => $tableIdValue1)
                                                            @if ($detail->receive_operator_id == $tableIdValue1)
                                                                <p>{{ $tableIdName1 }}</p>
                                                            @endif
                                                        @endforeach
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="col-xs-2">エスカレーション担当者</th>
                                                    <td>
                                                        @foreach ($viewExtendData['operatorNameList'] as $tableIdName1 => $tableIdValue1)
                                                            @if ($detail->escalation_operator_id == $tableIdValue1)
                                                                <p>{{ $tableIdName1 }}</p>
                                                            @endif
                                                        @endforeach
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="col-xs-2">回答内容</th>
                                                    <td>
                                                        {{ $detail->answer_detail }}</textarea>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="col-xs-2">回答日時</th>
                                                    <td>
                                                        {{ (new Carbon\Carbon($detail->answer_datetime))->format('Y/m/d') }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="col-xs-2">回答者</th>
                                                    <td>
                                                        @foreach ($viewExtendData['operatorNameList'] as $tableIdName1 => $tableIdValue1)
                                                            @if ($detail->answer_operator_id == $tableIdValue1)
                                                                <p>{{ $tableIdName1 }}</p>
                                                            @endif
                                                        @endforeach
                                                    </td>
                                                </tr>
                                                @if ($hasAuthority)
                                                    <tr>
                                                        <th class="col-xs-2">
                                                            <button class="btn btn-danger" type="submit" name="submit"
                                                                id="confirm_delete" value="confirm_delete"
                                                                data-toggle="modal" data-target="#delete-modal"
                                                                onclick="openModal(event)">削除</button>
                                                        </th>
                                                        <td class="col-xs-4"></td>
                                                    </tr>
                                                @endif
                                            </table>
                                        </div>
                                    </div>
                                @endforeach
                            </td>
                        </tr>
                    @endif
                @endif
                <tr class="u-mt--sl">
                    <th class="must c-box--200">連絡方法</th>
                    <td>
                        <select name="contact_way_type" id="contact_way_type" class="form-control c-box--200">
                            <option value=""></option>
                            @foreach ($viewExtendData['contactWayTypes'] as $tableIdName1 => $tableIdValue1)
                                <option value="{{ $tableIdValue1 }}"
                                    {{ old('contact_way_type', isset($editRow->contact_way_type) ? $editRow->contact_way_type : '') == $tableIdValue1 ? 'selected' : '' }}>
                                    {{ $tableIdName1 }}
                                </option>
                            @endforeach
                        </select>
                        @include('common.elements.error_tag', ['name' => 'contact_way_type'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">ステータス</th>
                    <td>
                        <select name="status" id="status" class="form-control c-box--200">
                            <option value=""></option>
                            @foreach ($viewExtendData['statusList'] as $tableIdName1 => $tableIdValue1)
                                <option value="{{ $tableIdValue1 }}"
                                    {{ old('status', isset($editRow->status) ? $editRow->status : '') == $tableIdValue1 ? 'selected' : '' }}>
                                    {{ $tableIdName1 }}
                                </option>
                            @endforeach
                        </select>
                        @include('common.elements.error_tag', ['name' => 'status'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">分類</th>
                    <td>
                        <select name="category" id="category" class="form-control c-box--200">
                            <option value=""></option>
                            @foreach ($viewExtendData['categoryList'] as $tableIdName1 => $tableIdValue1)
                                <option value="{{ $tableIdValue1 }}"
                                    {{ old('category', isset($editRow->category) ? $editRow->category : '') == $tableIdValue1 ? 'selected' : '' }}>
                                    {{ $tableIdName1 }}
                            @endforeach
                        </select>
                        @include('common.elements.error_tag', ['name' => 'category'])
                    </td>
                </tr>
                <tr>
                    <th class="must c-box--200">受信内容</th>
                    <td>
                        <textarea class="form-control c-box--full resize-none" rows="5" id="receive_detail" name="receive_detail">{{ old('receive_detail', $editRow->receive_detail ?? '') }}</textarea>
                        @include('common.elements.error_tag', ['name' => 'receive_detail'])
                    </td>
                </tr>
                <tr>
                    <th class="must c-box--200">受信日時</th>
                    <td>
                        <div class='c-box--218'>
                            <div class='input-group date datetime-picker' id='datetimepicker1'>
                                <input type='text' class="form-control c-box--180" name="receive_datetime"
                                    id="receive_datetime"
                                    value="{{ old('receive_datetime', $editRow->receive_datetime ?? '') }}" />
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                        </div>
                        @include('common.elements.error_tag', ['name' => 'receive_datetime'])
                    </td>
                </tr>
                <tr>
                    <th class="must c-box--200">受信者</th>
                    <td>
                        <select name="receive_operator_id" id="receive_operator_id" class="form-control c-box--200">
                            <option value=""></option>
                            @foreach ($viewExtendData['operatorNameList'] as $tableIdName1 => $tableIdValue1)
                                <option value="{{ $tableIdValue1 }}"
                                    {{ old('receive_operator_id', isset($editRow->receive_operator_id) ? $editRow->receive_operator_id : '') == $tableIdValue1 ? 'selected' : '' }}>
                                    {{ $tableIdName1 }}
                                </option>
                            @endforeach
                        </select>
                        @include('common.elements.error_tag', ['name' => 'receive_operator_id'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">エスカレーション担当者</th>
                    <td>
                        <select name="escalation_operator_id" id="escalation_operator_id"
                            class="form-control c-box--200">
                            <option value=""></option>
                            @foreach ($viewExtendData['operatorNameList'] as $tableIdName1 => $tableIdValue1)
                                <option value="{{ $tableIdValue1 }}"
                                    {{ old('escalation_operator_id', isset($editRow->escalation_operator_id) ? $editRow->escalation_operator_id : '') == $tableIdValue1 ? 'selected' : '' }}>
                                    {{ $tableIdName1 }}
                                </option>
                            @endforeach
                        </select>
                        @include('common.elements.error_tag', ['name' => 'escalation_operator_id'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">回答内容</th>
                    <td>
                        <textarea class="form-control c-box--full resize-none" rows="5" id="answer_detail" name="answer_detail">{{ old('answer_detail', $editRow->answer_detail ?? '') }}</textarea>
                        @include('common.elements.error_tag', ['name' => 'answer_detail'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">回答日時</th>
                    <td>
                        <div class='c-box--218'>
                            <div class='input-group date datetime-picker' id='datetimepicker2'>
                                <input type='text' class="form-control c-box--180" name="answer_datetime"
                                    id="answer_datetime"
                                    value="{{ old('answer_datetime', $editRow->answer_datetime ?? '') }}" />
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                        </div>
                        @include('common.elements.error_tag', ['name' => 'answer_datetime'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">回答者</th>
                    <td>
                        <select name="answer_operator_id" id="answer_operator_id" class="form-control c-box--200">
                            <option value=""></option>
                            @foreach ($viewExtendData['operatorNameList'] as $tableIdName1 => $tableIdValue1)
                                <option value="{{ $tableIdValue1 }}"
                                    {{ old('answer_operator_id', isset($editRow->answer_operator_id) ? $editRow->answer_operator_id : '') == $tableIdValue1 ? 'selected' : '' }}>
                                    {{ $tableIdName1 }}
                                </option>
                            @endforeach
                        </select>
                        @include('common.elements.error_tag', ['name' => 'answer_operator_id'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">対応結果</th>
                    <td class="col-xs-4 bdr-line">
                        <select name="resolution_status" class="form-control c-box--200">
                            <option value=""></option>
                            @foreach ($viewExtendData['resStatus'] as $tableIdName1 => $tableIdValue1)
                                <option value="{{ $tableIdValue1 }}"
                                    {{ old('resolution_status', isset($editRow->resolution_status) ? $editRow->resolution_status : '') == $tableIdValue1 ? 'selected' : '' }}>
                                    {{ $tableIdName1 }}
                                </option>
                            @endforeach
                        </select>
                        @include('common.elements.error_tag', ['name' => 'resolution_status'])
                    </td>
                </tr>
            </table>
            <input type="hidden" name="data_key_id" value="{{ $sessionKeyId }}">

            @if (isset($previous_url))
                <input class="btn btn-default btn-lg u-mt--sm" type="button" name="cancel" value="キャンセル"
                    onClick="location.href='{{ $previous_url }}'">
            @else
                <input class="btn btn-default btn-lg u-mt--sm" type="button" name="cancel" value="キャンセル"
                    onClick="location.href='{{ route('cc.customer-history.index') }}'" />
            @endif
            <button class="btn btn-success btn-lg u-mt--sm ml-20" type="submit" name="submit" id="submit_notify"
                value="notify">確認</button>
            <br><br>
            @if (isset($editRow->t_cust_communication_id))
                <button type="submit" name="submit_csv_bulk_output" class="btn btn-default btn-lg u-mt--sm"
                    value="csv_bulk_output" onClick="csvExport(event)"
                    data-output-action="{{ route('cc.customer-history.post-report-output') }}">報告書出力</button>
            @endif
        </div>
        </div>
        <input type="hidden" name="previous_url" value="{{ old('previous_url', $previous_url ?? '') }}">
    </form>
    @push('css')
        <link rel="stylesheet" href="{{ esm_internal_asset('css/customer/gfh_1207/NECSM0121.css') }}">
    @endpush
    @push('js')
        <script src="{{ esm_internal_asset('js/customer/gfh_1207/NECSM0121.js') }}"></script>
    @endpush
@endsection
<div id="delete-modal" class="delete-modal modal fade" tabindex="-1" role="dialog"
    aria-labelledby="common-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="common-modal-title" class="modal-title">

                </h5>
            </div>
            <div id="common-modal-body" class="modal-body">
                {{ __('messages.warning.confirm_delete') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="いいえ">いいえ</button>
                <button class="btn btn-danger btn_delete" type="submit" name="submit" id="submit_delete"
                    value="delete" onClick="deleteCustCommDtl(event)"
                    data-delete-action="{{ route('cc.customer-history-dtl.post-delete') }}">はい</button>
            </div>
        </div>
    </div>
</div>
